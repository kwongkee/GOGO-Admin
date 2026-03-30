<?php
/**
 * 邦付宝
 * @author 赵金如
 * @date   2018-06-12
 * 对账文件显示与导出
 */
namespace app\declares\controller;
use think\Controller;
use Util\data\Sysdb;
use think\Session;
use think\Request;
use think\Db;
use think\Loader;
use think\log;
use CURLFile;
use PHPExcel_IOFactory;
use PHPExcel;

class Reconcil extends BaseAdmin
{
	public function __construct()
	{
		parent::__construct();
		//实例化数据库
		$this->db = new Sysdb;
		$this->admin = session('admin');//登录数据信息
	}
	
	//验证数据表
	public function Auth() {
		/**
		 * 如果是超级管理员，可查看全部订单，选择（商户，时间查询商户数据），
		 * 商户只能看自己的订单（需上一个账单对账完，才能对账下一个）；
		 */
		
		//分页配置
        $config = [
            'type' 		 =>'Layui',//分页类名
            'query'		 =>['s'=>'reconcil/auth','uid'=>'20'],//url额外参数
            'var_page'	 =>'page',//分页变量
            'newstyle'	 =>true,
        ];
        
		if($this->admin['role'] == 2) {//管理员
			
			if(input('post.userid') != '') {
				$order['uid']  = $uid  = input('post.userid');
				$wheres = ['uid'=>$uid];
			}
			
			if((input('post.startDate') != '') && (input('post.endDate') != '')) {
				$wheres = [
					'submitTime'  => [
						['egt',input('post.startDate')?input('post.startDate'):''],
						['elt',input('post.endDate')?input('post.endDate'):'']
					],
				];
			}
			
			if(!empty($wheres) && ($wheres['uid']!='' || $wheres['submitTime']!='')) {
				$order['auth'] = $this->db->table('foll_payment_userinfo')->where($wheres)->pages(6,$config);
			} else {
				$order['auth'] = $this->db->table('foll_payment_userinfo')->pages(6,$config);
			}
			
			//管理员配置   用于计算费用
			$adminConfig = $this->db->table('foll_admin_settlement')->item();
			$order['userlist']  = $this->db->table('foll_business_admin')->lists();
			
			$order['role'] = 1;//管理员标志
			
		} else { //商户
			
			//昨天的开始时间搓；从0000开始至235959结束
			$begin = mktime(0,0,0,date('m'),date('d')-1,date('Y'));
			$end   = mktime(0,0,0,date('m'),date('d'),date('Y'))-1;
			
			
			$bg    = strtotime(date('Ymd',time()));//当前开始时间  0000
			$ed    = strtotime(date('Ymd',time()))+86399;//当前结束时间 2359
			/**
			 * 先查记录表中是否有数据，按ID 从达到小排序   对账要求，没有对账的数据要先对账！
			 * 1、记录没有数据：获取到第一条数据的创建时间，转换成作为条件转成时间搓作为条件，并且这个时间需小于等于昨天时间；
			 * 2、记录有数据：
			 * 		记录有数据，则获取记录数的最后一条数据时间+1天作为查询条件，并且这个时间需小于等于昨天时间；
			 */
			//查询数据条件
			
			//查询是否有管理员确认的有误对账账单 2018-07-09
			$queryOk = $this->db->table('foll_goodconfirms')->where(['uid'=>$this->admin['id'],'type'=>'auth','status'=>1])->order('id desc')->item();
			if(!empty($queryOk)) {
				
				$begins  = strtotime($queryOk['startDate']);
				$ends 	 = strtotime($queryOk['endDate']);
				$Checkwhere = [
					'submitTime'=>[
						['egt',date('YmdHis',$begins)],
						['elt',date('YmdHis',$ends)]
					],
					'uid' => $this->admin['id'],
				];
					
			} else {
				
				$dataWhere1=[//查询对账记录
					'uid'=>$this->admin['id'],
					'type'=>'auth',
					'status'=>1,
				];
				
				//查询记录表数据，按用户ID、对账类型type = auth(实名认证)   获取最后一条对账记录
				$check = $this->db->table('foll_goodconfirm')->where($dataWhere1)->order('id desc')->item();
				if(!empty($check)) {//有对账记录，则获取对账记录时间+1作为条件查询
					$begins  = strtotime($check['startDate']) + 86400;
					$ends 	 = strtotime($check['endDate'])   + 86400;
					
					if(($begins < $bg) && ($ends < $ed)) {
						$Checkwhere = [
							'submitTime'=>[
								['egt',date('YmdHis',$begins)],
								['elt',date('YmdHis',$ends)]
							],
							'uid'	=>$this->admin['id'],
						];
					} else {
						echo '暂无数据';die;
					}
					
				} else {//没有数据，则查询表中数据的第一条数据
					$dataWhere=[//查询订单数据
						'uid'=> $this->admin['id'],
					];
					$jiesuan1   = $this->db->table('foll_payment_userinfo')->where($dataWhere)->order('id asc')->item();
					/**
					 * 需要把这个时间分别转换成 开始时间0000  结束时间2359 作为查询数据条件
					 */
					$begins = strtotime(date('Ymd',strtotime($jiesuan1['submitTime'])));//开始时间
					$ends   = $begins + 86399;//结束时间
					
					if(($begins < $bg) && ($ends < $ed)) {
						$Checkwhere = [
							'submitTime'=>[
								['egt',date('YmdHis',$begins)],
								['elt',date('YmdHis',$ends)]
							],
							'uid'	=>$this->admin['id'],
						];
					} else {
						echo '暂无数据';die;	
					}				
				}
			}
			
			//商户配置    用于计算费用
			$businessConfig = $this->db->table('foll_business_settlement')->where(['uid'=>$this->admin['id']])->item();
			//验核数据
			$flagInfo	 = false;//没有数据
			$jiesuan	 = $this->db->table('foll_payment_userinfo')->where($Checkwhere)->lists();
			$peopleNum   = 0;//验证人数，默认0人;
			$ServerMoney = 0;//服务费用
			$money		 = 0;//验核费用
			$info = $this->db->table('foll_payment_userinfo')->where($Checkwhere)->pages(6,$config);
			if(!empty($info['lists'])) {
				$order	  = $info;
				$flagInfo = true;//有数据
				$peopleNum = count($jiesuan);
				$money 	   = sprintf("%.2f",($peopleNum * $businessConfig['authCopyMoney']));
				if($businessConfig['cserMothod'] == 'Percentage'){//按订单处理金额的 XX%
					$sum = 0;//订单总额
					foreach($jiesuan as $key=>$val) {//循环对账日数据
						$sum += ($val['fee']/100);//分为单位
					}
					$ServerMoney = ($sum * $businessConfig['cserMoney']);
					
				} else if($businessConfig['cserMothod'] == 'Element'){//按订单处理订单数：XX 元/订单
					$ServerMoney = ($peopleNum * $businessConfig['cserMoney']);
				}
				
			} else {
				echo '暂无数据';die;
			}
			
			$order['jiesuan']	  = $jiesuan;//对账总数据  用于前端表单提交
			$order['email']		  = $this->admin['user_email'];//商户邮箱
			$order['ServerMoney'] = $ServerMoney;//服务费用
			$order['Money']	   	  = $money;//验核费用
			$order['Total']	   	  = ($ServerMoney + $money);//合计总额
			$order['count']	   	  = $peopleNum;//统计条数  人数
			$order['flagInfo'] 	  = $flagInfo;//是否有数据标识
			$order['role']     	  = 3;//管理员标志
			$order['startDate']	  = $begins;//对账日期 开始
			$order['endDate']	  = $ends;//对账日期      结束
			
		}
		$this->assign('order',$order);
		return view('reconcil/Auth');
	}
	
	//确认下载对账文件
	public function Authuploads()
	{
		//对账数据ID
		$dataId = implode(',',$_POST['userid']);
		//对账统计数据
		$sendIf = $_POST['userinfo'];
		//获取所有对账数据
		$data = $this->db->table('foll_payment_userinfo')->where(['id'=>['in',$dataId]])->lists();
		if(empty($data)) {//没有数据返回空
			return json(['code'=>1,'msg'=>'暂无数据']);
		}
		//导出excel 数据
		$PHPExcel = new PHPExcel(); //实例化PHPExcel类，类似于在桌面上新建一个Excel表格
  		$PHPSheet = $PHPExcel->getActiveSheet(); //获得当前活动sheet的操作对象
  		$PHPSheet->setTitle('实名认证订单列表'); //给当前活动sheet设置名称
  		$PHPSheet->setCellValue('A1','订单时间')
  				 ->setCellValue('B1','订单编号')
  				 ->setCellValue('C1','认证人名称')
  				 ->setCellValue('D1','认证金额')
  				 ->setCellValue('E1','认证状态');
  		//给当前活动sheet填充数据，数据填充是按顺序一行一行填充的，假如想给A1留空，可以直接setCellValue(‘A1’,’’);
  		$count = count($data)-1;
  		for($i=0; $i <= $count; $i++) {
  			$num = 2+$i;
  			$PHPSheet->setCellValue("A".$num,$data[$i]['completeTime'])
  				 ->setCellValue('B'.$num,"\t".$data[$i]['orderId']."\t")
  				 ->setCellValue('C'.$num,$data[$i]['userName'])
  				 ->setCellValue('D'.$num,($data[$i]['fee']/100).'元')
  				 ->setCellValue('E'.$num,($data[$i]['resultMsg']));
  		}
  		$PHPSheet->setCellValue('A'.($num + 1),'合计')
  				 ->setCellValue('B'.($num + 1),"服务费用：".sprintf("%.2f",$sendIf['ServerMoney']).'元')
  				 ->setCellValue('C'.($num + 1),"验核费用：".sprintf("%.2f",$sendIf['Money']).'元')
  				 ->setCellValue('D'.($num + 1),"合计总额：".sprintf("%.2f",$sendIf['Total']).'元')
  				 ->setCellValue('E'.($num + 1),"验核人数：".$sendIf['count'].'人');
  		
  		$PHPWriter = PHPExcel_IOFactory::createWriter($PHPExcel,'Excel2007');//按照指定格式生成Excel文件，‘Excel2007’表示生成2007版本的xlsx，‘Excel5’表示生成2003版本Excel文件
  		$fileName  = "Userinfo".date('Y-m-d',time()).'.xlsx';
  		$path      = dirname(__FILE__).'/'.$fileName;
		$PHPWriter->save($path);
		$Result    = $this->sendEmail($path,$sendIf['email']);
		unlink($path);
		
		$info['uid']    	= $this->admin['id'];
		$info['user_name']  = $this->admin['user_name'];
		$info['user_email'] = $this->admin['user_email'];
		
		//昨天的开始时间搓；从0000开始至235959结束
		//$begin = mktime(0,0,0,date('m'),date('d')-1,date('Y'));
		//$end   = mktime(0,0,0,date('m'),date('d'),date('Y'))-1;
		
		$info['startDate'] = date('Y-m-d H:i:s',$sendIf['startDate']);//对账开始时间
		$info['endDate']   = date('Y-m-d H:i:s',$sendIf['endDate']);//对账结束时间
		$info['status']	   = '1';
		$info['c_time']	   = date('Y-m-d H:i:s',time());
		$info['type']	   = 'auth';
		
		if(!$Result) {
			$info['status']	   = '3';
			//记录商户已对账
			$this->db->table('foll_goodconfirm')->insert($info);
			return json(['code'=>1,'msg'=>'发送失败']);
		}
		//记录商户已对账
		$this->db->table('foll_goodconfirm')->insert($info);
	    return json(['code'=>0,'msg'=>'发送成功']);
	}
	
	//该笔订单有误，提交给后台管理员
	public function Autherror()
	{
		//对账数据ID
		$dataId = implode(',',$_POST['userid']);
		//对账统计数据
		$sendIf = $_POST['userinfo'];
		
		echo $dataId;
		echo '<br>';
		echo $sendIf;
	}
	
	
	
	//报关订单表
	public function Orders()
	{
		/**
		 * 如果是超级管理员，可查看全部订单，
		 * 用户只能看自己的订单；
		 */
        $config = [
            'type' =>'Layui',
            'query'=>['s'=>'reconcil/Orders'],
            'var_page'=>'page',
            'newstyle'=>true
        ];
        
		if($this->admin['role'] == 1){
			$order = $this->db->table('foll_payment_order')->pages(5,$config);
		} else {
			$order = $this->db->table('foll_payment_order')->where(['uid'=>$this->admin['id']])->pages(5,$config);
		}
		
		$this->assign('order',$order);
		return view('reconcil/Orders');
	}
	
	/**
	 * 商品详细
	 */
	public function Statistic() {
		$id = (int)input('get.id');
		// 商品详细
		$data['item'] = $this->db->table('foll_goodsreglist')->where(['id'=>$id])->item();
		$this->assign('data',$data['item']);
		return view('reconcil/Statistics');
	}
	
	/**
	 * 备案商品统计
	 * 2018-06-25
	 */
	public function Statistics(Request $request)
	{
		$userId    = $this->admin['id'];
		$userEmail = $this->admin['user_email'];
		$page = trim($request->get('page')) ? trim($request->get('page')) : 1 ;
		
		//昨天的开始时间搓；从0000开始至235959结束
		$begin = mktime(0,0,0,date('m'),date('d')-1,date('Y'));
		$end   = mktime(0,0,0,date('m'),date('d'),date('Y'))-1;
		
		$ConWhere = [
			'uid'		=> $userId,
			'user_email'=> $userEmail,
			'startDate' => date('Y-m-d H:i:s',$begin),
			'endDate' 	=> date('Y-m-d H:i:s',$end),
			'type'		=> 'good'
		];
		//检查是否已经对账
		$Confirm = $this->db->table('foll_goodconfirm')->where($ConWhere)->order('id desc')->item();
		if(!empty($Confirm)) {
			$order['status'] = $Confirm['status'];
		} else {
			$order['status'] = 3;//状态：1、已确认，2、有误、3、未对账（显示两个按钮）
		}
		
		/**
		 * 查相关数据
		 * 先查当前用户的配置数据   计算配置数据；
		 */
		//配置数据
		$payConfig = $this->db->table('foll_business_settlement')->where(['uid'=>$userId])->item();
		
		$config = [
            'type'    => 'Layui',
            'query'   => ['s'=>'reconcil/Statistics'],
            'var_page'=> 'page',
            'newstyle'=> true
        ];
		//备案商品数量   暂时先查一条数据
		$goodWhere = [
			'uid'		=> $userId,
			'InputDate'	=> [
				['egt',date('Y-m-d H:i:s',$begin)],//大于等于开始时间
				['elt',date('Y-m-d H:i:s',$end)],//并且小于等于结束时间
			]
		];
		
		/**
		 * Notes
		 * OpType
		 * DeclEntNo
		 */
		$goodFlag  = 1;
		$goodMoney = 0;
		if($payConfig['setMothod'] == 'good')//商品数量计算费用
		{
			$find = 'a.InputDate,b.*';
			$order['goodInfo'] = $goodInfo = $this->db->table('foll_goodsreghead a')->join(['foll_goodsreglist b','a.id=b.head_id'])->field($find)->where($goodWhere)->pagesJoin(8,$config);
			if($goodInfo['total'] <= 0 ){//没有数据，提示暂无数据
				echo "<h2>您昨天没有备案申请，暂无统计！</h2>";
				die;
			}
			//计算前天商品数量总和的服务费用
			$order['goodMoney'] = $goodMoney =  sprintf("%.2f",($goodInfo['total'] * $payConfig['banMoney']));
			$order['goodFlag']  = $goodFlag = 2;
			
		} else if($payConfig['setMothod'] == 'pici'){//商品批次计算
			$find = 'a.*';
			$order['goodInfo'] = $goodInfo = $this->db->table('foll_goodsreghead a')->where($goodWhere)->pages(8,$config);
			if($goodInfo['total'] <= 0 ){//没有数据，提示暂无数据
				echo "<h2>您昨天没有备案申请，暂无统计！</h2>";
				die;
			}
			//计算昨天备案数据批次的服务费用总和
			$order['goodMoney'] = $goodMoney =  sprintf("%.2f",($goodInfo['total'] * $payConfig['banMoney']));
			$order['goodFlag']  = $goodFlag  = 1;
		}
		
		$this->assign('order',$order);
		return view('reconcil/A-Statistics');
	}
	/**
	 * 确认对账，下载备案文件显示页面
	 */
	public function Confirm()
	{
		$data['user_id']    = $this->admin['id'];
		$data['user_name']  = $this->admin['user_name'];
		$data['user_email'] = $this->admin['user_email'];
		$this->assign('data',$data);
		return view('reconcil/Confirm');
	}
	//确认下载
	public function Confirms()
	{
		$info['uid']    	= (int)input('post.uid');
		$info['user_name']  = input('post.user_name');
		$info['user_email'] = input('post.user_email');
		
		//昨天的开始时间搓；从0000开始至235959结束
		$begin = mktime(0,0,0,date('m'),date('d')-1,date('Y'));
		$end   = mktime(0,0,0,date('m'),date('d'),date('Y'))-1;
		
		$info['startDate'] = date('Y-m-d H:i:s',$begin);//对账开始时间
		$info['endDate']   = date('Y-m-d H:i:s',$end);//对账结束时间
		$info['status']	   = '1';
		$info['c_time']	   = date('Y-m-d H:i:s',time());
		$ifno['type']	   = 'good';
		
		//备案商品数量   暂时先查一条数据
		$goodWhere = [
			'uid'=>$info['uid'],
			'InputDate'=>[
				['egt',date('Y-m-d H:i:s',$begin)],//大于等于开始时间
				['elt',date('Y-m-d H:i:s',$end)],//并且小于等于结束时间
			]
		];
		
		$result = $this->db->table('foll_goodsreghead a')->join(['foll_goodsreglist b','a.id=b.head_id'])->field('a.InputDate,b.*')->where($goodWhere)->listsJoin();
		
		
		$PHPExcel = new PHPExcel(); //实例化PHPExcel类，类似于在桌面上新建一个Excel表格
  		$PHPSheet = $PHPExcel->getActiveSheet(); //获得当前活动sheet的操作对象
  		$PHPSheet->setTitle('备案商品列表'); //给当前活动sheet设置名称
  		$PHPSheet->setCellValue('A1','企业商品自编号')
  				 ->setCellValue('B1','检验检疫商品备案编号')
  				 ->setCellValue('C1','海关正式备案编号')
  				 ->setCellValue('D1','账册号')
  				 ->setCellValue('E1','保税账册里的项号')
  				 ->setCellValue('F1','电商平台上的商品名称')
  				 ->setCellValue('G1','商品综合分类表(NCAD)')
  				 ->setCellValue('H1','HS编码')
  				 ->setCellValue('I1','商品条形码')	  				  
  				 ->setCellValue('J1','商品名称')
  				 ->setCellValue('K1','商品规格')
  				 ->setCellValue('L1','商品品牌')
  				 ->setCellValue('M1','计量单位')
  				 ->setCellValue('N1','第一法定计量单位')
  				 ->setCellValue('O1','第二法定计量单位')
  				 ->setCellValue('P1','单价')
  				 ->setCellValue('Q1','原产国')
  				 ->setCellValue('R1','商品品质及说明')
  				 ->setCellValue('S1','品质证明说明')
  				 ->setCellValue('T1','生产厂家或供应商')
  				 ->setCellValue('U1','净重')
  				 ->setCellValue('V1','毛重')
  				 ->setCellValue('W1','备案状态')
  				 ->setCellValue('X1','国检审核备注')
  				 ->setCellValue('Y1','跨境平台备案后的企业编号')
  				 ->setCellValue('Z1','商品备案申请号');
  		//给当前活动sheet填充数据，数据填充是按顺序一行一行填充的，假如想给A1留空，可以直接setCellValue(‘A1’,’’);
  		$count = count($result)-1;
  		for($i=0; $i <= $count; $i++) {
  			$num = 2+$i;
  			$PHPSheet->setCellValue("A".$num,$result[$i]['EntGoodsNo'])
  				 ->setCellValue('B'.$num,"\t".$result[$i]['CIQGoodsNo']."\t")
  				 ->setCellValue('C'.$num,$result[$i]['CusGoodsNo'])
  				 ->setCellValue('D'.$num,$result[$i]['EmsNo'])
  				 ->setCellValue('E'.$num,$result[$i]['ItemNo'])
  				 ->setCellValue('F'.$num,$result[$i]['ShelfGName'])
  				 ->setCellValue('G'.$num,$result[$i]['NcadCode'])
  				 ->setCellValue('H'.$num,$result[$i]['HSCode'])
  				 ->setCellValue('I'.$num,$result[$i]['BarCode'])
  				 ->setCellValue('J'.$num,$result[$i]['GoodsName'])
  				 ->setCellValue('K'.$num,$result[$i]['GoodsStyle'])
  				 ->setCellValue('L'.$num,$result[$i]['Brand'])
  				 ->setCellValue('M'.$num,$result[$i]['GUnit'])
  				 ->setCellValue('N'.$num,$result[$i]['StdUnit'])
  				 ->setCellValue('O'.$num,$result[$i]['SecUnit'])
  				 ->setCellValue('P'.$num,$result[$i]['RegPrice'])
  				 ->setCellValue('Q'.$num,$result[$i]['OriginCountry'])
  				 ->setCellValue('R'.$num,$result[$i]['Quality'])
  				 ->setCellValue('S'.$num,$result[$i]['QualityCertify'])
  				 ->setCellValue('T'.$num,$result[$i]['Manufactory'])
  				 ->setCellValue('U'.$num,$result[$i]['NetWt'])
  				 ->setCellValue('V'.$num,$result[$i]['GrossWt'])
  				 ->setCellValue('W'.$num,$result[$i]['CIQGRegStatus'] == 'C' ? '备案成功' : '备案失败' )
  				 ->setCellValue('X'.$num,$result[$i]['CIQNotes'])
  				 ->setCellValue('Y'.$num,$result[$i]['DeclEntNo'])
  				 ->setCellValue('Z'.$num,"\t".$result[$i]['EPortGoodsNo']."\t");
  		}
  		$PHPWriter = PHPExcel_IOFactory::createWriter($PHPExcel,'Excel2007');//按照指定格式生成Excel文件，‘Excel2007’表示生成2007版本的xlsx，‘Excel5’表示生成2003版本Excel文件
  		$fileName  = "GoodList".date('Y-m-d',time()).'.xlsx';
  		$path      = dirname(__FILE__).'/'.$fileName;
		$PHPWriter->save($path);
		$Result    = $this->sendEmail($path,$info['user_email']);
		//unlink($path);
		if(!$Result) {
			$info['status']	   = '3';
			//记录商户已对账
			$this->db->table('foll_goodconfirm')->insert($info);
			return json(['code'=>1,'msg'=>'发送失败']);
		}
		//记录商户已对账
		$this->db->table('foll_goodconfirm')->insert($info);
	    return json(['code'=>0,'data'=>'发送成功']);
	}
	
	//发送电子邮件给商户
	public function sendEmail($path,$email,$subject = '备案商品对账文件已发送到您的邮箱') {
		$name    = '系统管理员';
		$content = "提示：您的对账已经发送到邮箱，请查收！,您可请登录后台，商品备案》商品备案报送查看全部备案。<a href='http://shop.gogo198.cn/foll/public/?s=account/login'>点击前往登录后台</a>";
    	$status  = send_mail($email,$name,$subject,$content,['0'=>$path]);
		if($status) {
			return true;
		} else {
			return false;
		}
	}
	//点击有误按钮
	public function Mistaken()
	{
		$info['uid']    	= $this->admin['id'];
		$info['user_name']  = $this->admin['user_name'];
		$info['user_email'] = $this->admin['user_email'];
		
		//昨天的开始时间搓；从0000开始至235959结束
		$begin = mktime(0,0,0,date('m'),date('d')-1,date('Y'));
		$end   = mktime(0,0,0,date('m'),date('d'),date('Y'))-1;
		
		$info['startDate'] = date('Y-m-d H:i:s',$begin);//对账开始时间
		$info['endDate']   = date('Y-m-d H:i:s',$end);//对账结束时间
		$info['status']	   = '2';//1、已确认，2、有误，3、未对账
		$info['c_time']	   = date('Y-m-d H:i:s',time());
		$info['type']	   = input('post.token');//计算类型：商品数量good，批次数pici,企业订单order
		//记录商户已对账
		$res = $this->db->table('foll_goodconfirm')->insert($info);
		if(!$res){
			//1有误
			return json(['code'=>1,'msg'=>'数据写入有误！']);
		}
		$this->sendAdmin();//发送邮件给管理员
		//0正确
		return json(['code'=>0,'msg'=>'已通知管理员,请耐心等待！']);
	}
	
	//备案批次详细
	public function piciInfo()
	{
		$id = (int)input('get.id');
		// 商品详细
		$data['item'] = $this->db->table('foll_goodsreghead')->where(['id'=>$id])->item();
		$this->assign('data',$data['item']);
		return view('reconcil/piciInfo');
	}
	
	//发送邮件给管理员
	public function sendAdmin()
	{
		$toemail = '805929498@qq.com';//'198@gogo198.net';
		$name    = '系统管理员';
		$subject = '商户对账有误';
		$content = "提示：商户对账有误,请登录后台核查：<a href='http://shop.gogo198.cn/foll/public/?s=account/login'>点击前往登录核查</a>";
    	$status  = send_mail($toemail,$name,$subject,$content);
	}
	
	//下载批量统计页面
	public function Confirma()
	{
		$data['user_id']    = $this->admin['id'];
		$data['user_name']  = $this->admin['user_name'];
		$data['user_email'] = $this->admin['user_email'];
		$this->assign('data',$data);
		return view('reconcil/Confirma');
	}
	
	//批量统计  2018-06-29  确认下载；
	public function batch() {
		/**
		 * 查出对账日的数据；还有配置计算
		 */
		$userId    = $this->admin['id'];
		$userEmail = $this->admin['user_email'];
		$info['uid']    	= (int)input('post.uid');
		$info['user_name']  = trim(input('post.user_name'));
		$info['user_email'] = trim(input('post.user_email'));
		
		//昨天的开始时间搓；从0000开始至235959结束
		$begin = mktime(0,0,0,date('m'),date('d')-1,date('Y'));
		$end   = mktime(0,0,0,date('m'),date('d'),date('Y'))-1;
		
		$info['startDate'] = date('Y-m-d H:i:s',$begin);//对账开始时间
		$info['endDate']   = date('Y-m-d H:i:s',$end);//对账结束时间
		$info['status']	   = '1';
		$info['c_time']	   = date('Y-m-d H:i:s',time());
		$info['type']	   = 'pici';
		
		$ConWhere = [
			'uid'		=> $userId,
			'user_email'=> $userEmail,
			'startDate' => date('Y-m-d H:i:s',$begin),
			'endDate' 	=> date('Y-m-d H:i:s',$end),
			'type'		=> 'pici',
		];
		//检查是否已经对账
		$Confirm = $this->db->table('foll_goodconfirm')->where($ConWhere)->order('id desc')->item();
		if(!empty($Confirm)) {
			$order['status'] = $Confirm['status'];
		} else {
			$order['status'] = 3;//状态：1、已确认，2、有误、3、未对账（显示两个按钮）
		}
		
		/**
		 * 查相关数据
		 * 先查当前用户的配置数据   计算配置数据；
		 */
		//配置数据
		$payConfig = $this->db->table('foll_business_settlement')->where(['uid'=>$userId])->item();
		
		//备案商品数量   暂时先查一条数据
		$goodWhere = [
			'uid'		=> $userId,
			'InputDate'	=> [
				['egt',date('Y-m-d H:i:s',$begin)],//大于等于开始时间
				['elt',date('Y-m-d H:i:s',$end)],//并且小于等于结束时间
			]
		];
		$goodInfo = $this->db->table('foll_goodsreghead')->where($goodWhere)->lists();
		//批次
		$Number   = count($goodInfo);
		//计算批次服务费
		$Money    = ($Number*$payConfig['banMoney']);
		//统计；提交日期，批次数，应收，应付；
		
		$PHPExcel = new PHPExcel(); //实例化PHPExcel类，类似于在桌面上新建一个Excel表格
  		$PHPSheet = $PHPExcel->getActiveSheet(); //获得当前活动sheet的操作对象
  		$PHPSheet->setTitle('备案批次统计'); //给当前活动sheet设置名称
  		$PHPSheet->setCellValue('A1','提交日期')
  				 ->setCellValue('B1','批次数')
  				 ->setCellValue('C1','应收服务费')
  				 ->setCellValue('D1','应付服务费');
  		//给当前活动sheet填充数据，数据填充是按顺序一行一行填充的，假如想给A1留空，可以直接setCellValue(‘A1’,’’);
  		$count = count($goodInfo)-1;
  		$num = 0;
  		for($i=0; $i <= $count; $i++) {
  			$num = 2+$i;
  			$PHPSheet->setCellValue("A".$num,$goodInfo[$i]['InputDate'])
  				 ->setCellValue('B'.$num,'1')
  				 ->setCellValue('C'.$num,sprintf("%.2f",($payConfig['banMoney'] * 1)).'元')
  				 ->setCellValue('D'.$num,sprintf("%.2f",($payConfig['banMoney'] * 1)).'元');
  		}
  			$PHPSheet->setCellValue('A'.($num + 1),'统计')
  				 ->setCellValue('B'.($num + 1),"总批次数：{$Number}次")
  				 ->setCellValue('C'.($num + 1),"应收服务费总额：".sprintf("%.2f",$Money).'元')
  				 ->setCellValue('D'.($num + 1),"应付服务费总额：".sprintf("%.2f",$Money).'元');
  		
  		$PHPWriter = PHPExcel_IOFactory::createWriter($PHPExcel,'Excel2007');//按照指定格式生成Excel文件，‘Excel2007’表示生成2007版本的xlsx，‘Excel5’表示生成2003版本Excel文件
  		$fileName  = "HeadList".date('Y-m-d',time()).'.xlsx';
  		$path      = dirname(__FILE__).'/'.$fileName;
		$PHPWriter->save($path);
		$Result    = $this->sendEmail($path,$info['user_email']);
		unlink($path);
		if(!$Result) {
			$info['status']	   = '3';
			//记录商户已对账
			$this->db->table('foll_goodconfirm')->insert($info);
			return json(['code'=>1,'msg'=>'发送失败']);
		}
		//记录商户已对账
		$this->db->table('foll_goodconfirm')->insert($info);
	    return json(['code'=>0,'data'=>'发送成功']);
	}
	
	/**
	 * 订单统计与收付结算
	 */
	//企业应付
	public function Copy() {
		
		$userId    = $this->admin['id'];
		$userEmail = $this->admin['user_email'];
		//查询配置   用于计算收费；
		$payConfig = $this->db->table('foll_business_settlement')->where(['uid'=>$userId])->item();
		
		//昨天的开始时间搓；从0000开始至235959结束
		$begin = mktime(0,0,0,date('m'),date('d')-1,date('Y'));
		$end   = mktime(0,0,0,date('m'),date('d'),date('Y'))-1;
		//统计计算服务费  按电子订单数据计算
		$elecWhere = [
			'uid'=>$userId,
			'DeclTime'=>[
				['egt',date('Y-m-d H:i:s',$begin)],//大于等于开始时间
				['elt',date('Y-m-d H:i:s',$end)]//并且小于等于结束时间
			],
		];
		
		$elecMoney = 0;//计算所有订单总额
		$number    = 0;//订单数量
		$sunNum	   = 0;//服务费用
		//获取电子订单  计算服务费用
		$elec  = $this->db->table('foll_elec_order_head a')->join(['foll_elec_order_detail b','a.id=b.head_id'])->field('a.DeclTime,b.OrderGoodTotal')->where($elecWhere)->listsJoin();
		if(!empty($elec)) {//有数据
			foreach($elec as $key=>$val) {
				$elecMoney += $val['OrderGoodTotal'];
				$number++;
			}
		
			if($payConfig['cserMothod'] == 'Percentage'){//按订单金额计算
				
				$sunNum = ($elecMoney * $payConfig['cserMoney']);
				
			} else if($payConfig['cserMothod'] == 'Element'){//按订单数量结算
				
				$sunNum = ($number * $payConfig['cserMoney']);
			}
			//应付金额
			$sunNum = sprintf("%.2f",$sunNum);
		}
		
		$Server['sunNum'] = $sunNum;//服务费用
		
		//验核条件
		$realWhere = [
			'uid'=>$userId,
			'submitTime'=>[
				['egt',date('YmdHis',$begin)],//大于等于开始时间
				['elt',date('YmdHis',$end)]//并且小于等于结束时间
			],
			
		];
		$RealNum   = 0;//验核人数
		$RealMoney = 0;//验核费用
		//获取实名认证订单，计算服务费用
		$RealName = $this->db->table('foll_payment_userinfo')->where($realWhere)->field('id')->lists();
		if(!empty($RealName)) {
			$RealNum   = count($RealName);
			$RealMoney = sprintf("%.2f",($RealNum * $payConfig['authCopyMoney']));
		}
		$Server['RealMoney'] = $RealMoney;//验核费用
		
		//支付单费用
		$payWhere = [
			'uid'=>$userId,
			'submitTime'=>[
				['egt',date('YmdHis',$begin)],
				['elt',date('YmdHis',$end)]
			],
		];
		$payMoney = 0;//交易费用
		$payCount = 0;//订单总额
		//订单时间、订单编号、订单金额、订单买家、服务费用、验核费用、交易费用、应付总额
		$fild     = 'id,submitTime,orderNo,payAmount,payerName';
		$config = [
            'type' =>'Layui',
            'query'=>['s'=>'reconcil/Copy'],
            'var_page'=>'page',
            'newstyle'=>true
        ];
        
		$Server['info'] = $payOrder = $this->db->table('foll_payment_order')->where($payWhere)->field($fild)->pages(6,$config);
		if(!empty($payOrder['lists'])) {
			foreach($payOrder['lists'] as $key=>$val) {
				//$payCount += $val['payAmount'];
				$Server['info']['lists'][$key]['submitTime'] = date('Y-m-d H:i:s',strtotime($val['submitTime']));
				$Server['info']['lists'][$key]['payAmount']  = sprintf('%.2f',($val['payAmount']/100));
			}
			$payMoney = sprintf("%.2f",(($payCount/100) * $payConfig['payCopyMoney']));
		}
		
		$payOrders = $this->db->table('foll_payment_order')->where($payWhere)->field($fild)->lists();
		if(!empty($payOrders)) {
			foreach($payOrders as $key=>$val) {
				$payCount += $val['payAmount'];
			}
			$payMoney = sprintf("%.2f",(($payCount/100) * $payConfig['payCopyMoney']));
		}
		
		$Server['payMoney'] = $payMoney;//交易费用
		//应付总额
		$Total = ($sunNum+$RealMoney+$payMoney);
		$Server['Total'] = $Total;//应付总额
		
		
		$ConWhere = [
			'uid'		=> $userId,
			'user_email'=> $userEmail,
			'startDate' => date('Y-m-d H:i:s',$begin),
			'endDate' 	=> date('Y-m-d H:i:s',$end),
		];
		//检查是否已经对账
		$Confirm = $this->db->table('foll_goodconfirm')->where($ConWhere)->order('id desc')->item();
		if(!empty($Confirm)) {
			$Server['status'] = $Confirm['status'];
		} else {
			$Server['status'] = 3;//状态：1、已确认，2、有误、3、未对账（显示两个按钮）
		}
		
		$this->assign('data',$Server);
		return view('reconcil/copy');
	}
	
	//查看详情  订单统计与收付结算的
	public function Checkdetail() {
		$id = input('get.id');
		$detail = $this->db->table('foll_payment_order')->where(['id'=>$id])->item();
		
		$this->assign('data',$detail);
		return view('reconcil/Checkdetail');
	}
	
	//确认订单按钮
	public function CheckBtn(){
		$info['user_id']	= $this->admin['id'];
		$info['user_name']  = $this->admin['user_name'];
		$info['user_email'] = $this->admin['user_email'];
		
		$this->assign('data',$info);
		return view('reconcil/Checkbtn');
	}
	
	//确认下载
	public function Checkok() {
		
		$userId    = $this->admin['id'];
		$userEmail = $this->admin['user_email'];
		$userName  = trim(input('post.user_name'));
		$userEmails = trim(input('post.user_email')) ? trim(input('post.user_email')) : $this->admin['user_email'];
		//查询配置   用于计算收费；
		$payConfig = $this->db->table('foll_business_settlement')->where(['uid'=>$userId])->item();
		
		//昨天的开始时间搓；从0000开始至235959结束
		$begin = mktime(0,0,0,date('m'),date('d')-1,date('Y'));
		$end   = mktime(0,0,0,date('m'),date('d'),date('Y'))-1;
		
		$ConWhere = [
			'uid'		=> $userId,
			'user_email'=> $userEmail,
			'startDate' => date('Y-m-d H:i:s',$begin),
			'endDate' 	=> date('Y-m-d H:i:s',$end),
			'type'		=> 'order',
		];
		//检查是否已经对账
		$Confirm = $this->db->table('foll_goodconfirm')->where($ConWhere)->order('id desc')->item();
		if(!empty($Confirm) && $Confirm['status'] == 1) {
			exit('您的账单已确认，请勿重复操作!');			
		}
		
		//统计计算服务费  按电子订单数据计算
		$elecWhere = [
			'uid'=>$userId,
			'DeclTime'=>[
				['egt',date('Y-m-d H:i:s',$begin)],//大于等于开始时间
				['elt',date('Y-m-d H:i:s',$end)]//并且小于等于结束时间
			],
		];
		
		$elecMoney = 0;//计算所有订单总额
		$number    = 0;//订单数量
		$sunNum	   = 0;//服务费用
		//获取电子订单  计算服务费用
		$elec  = $this->db->table('foll_elec_order_head a')->join(['foll_elec_order_detail b','a.id=b.head_id'])->field('a.DeclTime,b.OrderGoodTotal')->where($elecWhere)->listsJoin();
		if(!empty($elec)) {//有数据
			foreach($elec as $key=>$val) {
				$elecMoney += $val['OrderGoodTotal'];
				$number++;
			}
		
			if($payConfig['cserMothod'] == 'Percentage'){//按订单金额计算
				
				$sunNum = ($elecMoney * $payConfig['cserMoney']);
				
			} else if($payConfig['cserMothod'] == 'Element'){//按订单数量结算
				
				$sunNum = ($number * $payConfig['cserMoney']);
			}
			//应付金额
			$sunNum = sprintf("%.2f",$sunNum);
		}
		
		$Server['sunNum'] = $sunNum;//服务费用
		
		//验核条件
		$realWhere = [
			'uid'=>$userId,
			'submitTime'=>[
				['egt',date('YmdHis',$begin)],//大于等于开始时间
				['elt',date('YmdHis',$end)]//并且小于等于结束时间
			],
			
		];
		$RealNum   = 0;//验核人数
		$RealMoney = 0;//验核费用
		//获取实名认证订单，计算服务费用
		$RealName = $this->db->table('foll_payment_userinfo')->where($realWhere)->field('id')->lists();
		if(!empty($RealName)) {
			$RealNum   = count($RealName);
			$RealMoney = sprintf("%.2f",($RealNum * $payConfig['authCopyMoney']));
		}
		$Server['RealMoney'] = $RealMoney;//验核费用
		
		//支付单费用
		$payWhere = [
			'uid'=>$userId,
			'submitTime'=>[
				['egt',date('YmdHis',$begin)],
				['elt',date('YmdHis',$end)]
			],
		];
		$payMoney = 0;//交易费用
		$payCount = 0;//订单总额
		//订单时间、订单编号、订单金额、订单买家、服务费用、验核费用、交易费用、应付总额
		$fild     = 'id,submitTime,orderNo,payAmount,payerName';
		$config = [
            'type' =>'Layui',
            'query'=>['s'=>'reconcil/Copy'],
            'var_page'=>'page',
            'newstyle'=>true
        ];
        
		$Server['info'] = $payOrder = $this->db->table('foll_payment_order')->where($payWhere)->field($fild)->lists();
		if(!empty($payOrder)) {
			foreach($payOrder as $key=>$val) {
				$payCount += $val['payAmount'];
				$Server['info'][$key]['submitTime'] = date('Y-m-d H:i:s',strtotime($val['submitTime']));
				$Server['info'][$key]['payAmount']  = sprintf('%.2f',($val['payAmount']/100));
			}
			$payMoney = sprintf("%.2f",(($payCount/100) * $payConfig['payCopyMoney']));
		}
		
		$Server['payMoney'] = $payMoney;//交易费用
		//应付总额
		$Server['Total'] = $Total = ($sunNum+$RealMoney+$payMoney);
		
		$PHPExcel = new PHPExcel(); //实例化PHPExcel类，类似于在桌面上新建一个Excel表格
  		$PHPSheet = $PHPExcel->getActiveSheet(); //获得当前活动sheet的操作对象
  		$PHPSheet->setTitle('订单统计与收付结算'); //给当前活动sheet设置名称
  		$PHPSheet->setCellValue('A1','订单时间')
  				 ->setCellValue('B1','订单编号')
  				 ->setCellValue('C1','订单金额')
  				 ->setCellValue('D1','订单买家');
  		//给当前活动sheet填充数据，数据填充是按顺序一行一行填充的，假如想给A1留空，可以直接setCellValue(‘A1’,’’);
  		$count = count($Server['info'])-1;
  		$num = 0;
  		for($i=0; $i <= $count; $i++) {
  			$num = 2+$i;
  			$PHPSheet->setCellValue("A".$num,$Server['info'][$i]['submitTime'])
  				 ->setCellValue('B'.$num,"\t".$Server['info'][$i]['orderNo']."\t")
  				 ->setCellValue('D'.$num,$Server['info'][$i]['payerName'])
  				 ->setCellValue('C'.$num,sprintf("%.2f",($Server['info'][$i]['payAmount'] * 1)).'元');
  		}
  			$PHPSheet->setCellValue('A'.($num + 1),'服务费用:'.sprintf("%.2f",$sunNum).'元')
  				 ->setCellValue('B'.($num + 1),'验核费用'.sprintf("%.2f",$RealMoney).'元')
  				 ->setCellValue('C'.($num + 1),"交易费用：".sprintf("%.2f",$payMoney).'元')
  				 ->setCellValue('D'.($num + 1),"应付总额：".sprintf("%.2f",$Total).'元');
  		
  		$PHPWriter = PHPExcel_IOFactory::createWriter($PHPExcel,'Excel2007');//按照指定格式生成Excel文件，‘Excel2007’表示生成2007版本的xlsx，‘Excel5’表示生成2003版本Excel文件
  		$fileName  = "PortOrder".date('Y-m-d',time()).'.xlsx';
  		$path      = dirname(__FILE__).'/'.$fileName;
		$PHPWriter->save($path);
		$Result    = $this->sendEmail($path,$userEmails,'订单统计与收付结算');
		unlink($path);
		//记录商户确认账单
		$ConWhere['user_email'] = $userEmails;
		$ConWhere['user_name']  = $userName;
		$ConWhere['c_time']		= date('Y-m-d H:i:s',time());
		$ConWhere['status']	    = '1';
		if(!$Result) {
			$ConWhere['status'] = '3';
			//记录商户已对账
			$this->db->table('foll_goodconfirm')->insert($ConWhere);
			return json(['code'=>1,'msg'=>'发送失败']);
		}
		//记录商户已对账
		$this->db->table('foll_goodconfirm')->insert($ConWhere);
	    return json(['code'=>0,'data'=>'发送成功']);
	}
	
	
	
	/**
	 * 2018-07-03
	 * @author 赵金如
	 * 管理员：修改统计数据 
	 */
	public function Errors()
	{
		$config = [
            'type' 		=>'Layui',
            'query'		=>['s'=>'reconcil/Errors'],
            'var_page'	=>'page',
            'newstyle'	=>true
        ];
		$ErrorOrder = $this->db->table('foll_goodconfirm')->where(['status'=>2])->pages(8,$config);
		
		$this->assign('data',$ErrorOrder);
		return view('reconcil/Errors');
	}
}
?>