<?php
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

/*
 * 平台对账
 * @author 赵金如
 * 2018-07-17
 */
class Platform extends BaseAdmin
{
	public function __construct()
	{
		parent::__construct();
		//实例化数据库
		$this->db = new Sysdb;
		$this->admin = session('admin');//登录数据信息
	}
	
	//商户已经确认对账    已经确认
	public function index()
	{
		//分页配置
        $config = [
            'type' 		 =>'Layui',//分页类名
            'query'		 =>['s'=>'reconcil/index'],//url额外参数
            'var_page'	 =>'pages',//分页变量
            'newstyle'	 =>true,
        ];

		$order['queryOk'] = $this->db->table('foll_goodconfirm')->where(['uid'=>$this->admin['id'],'status'=>1])->order('accountDay desc')->pagess(11,$config);

		$order['title']   = '商户确认对账';
		$order['newDate'] = date('Y-m',time());//当前月份
		$list = $order['queryOk']['lists'];
		
		/*echo '<pre>';
		$tmp = [];
		$tmps = [0];
		$num = 0;
		foreach($list as $key=>$val){
			if($key < $order['queryOk']['total']){
				//当前对账时间  是否等于下一个对账时间
				$accountDay = $list[$key++]['accountDay'];
				if($val['accountDay'] == $accountDay){
					$tmp[$num] = $accountDay;
				} else {
					$num++;
				}
				//$tmp[$num] = $val;
			}
			array_push($tmps,$tmp);
		}
		//$dbinfo = Db::table('ims_foll_goodconfirm')->where(['uid'=>$this->admin['id'],'status'=>1])->group('accountDay')->select();
		print_r($tmps);
		die;*/
		$this->assign('order',$order);
		return view('platform/index');
	}
	
	
	//查看获取商户已对账信息
	public function Seeinfo()
	{
		//作为条件查询goodconfirm 表 confirmID 字段
		$uid   = input('post.uid')?input('post.uid'):1;//获取商户ID
		//对账日
		$accountDay = input('post.accountDay')?input('post.accountDay'):date('Y-m-d',time());
		//对账类型   goods：备案订单表，pay:支付订单，auth:
		$type   = input('post.type')?input('post.type'):'';
		//已完成对账ID
		$confirmId = input('post.confirmId')?input('post.confirmId'):1;
		//每页条数
		$limit  = input('post.limit') ? input('post.limit')  : 8;
		//接收页码
		$pages  = input('post.pages') ? input('post.pages')  : 1;
		//分页数
		$page   = ($pages-1)*$limit;
		$w = [
			'uid'=>$uid,
			'accountDay'=>$accountDay,
			'type'=>$type
		];
		$payfee = $this->db->table('foll_goodconfirm')->where($w)->field('number,serverfee,checkingfees,totalfees,user_name')->item();
		
		/**
		 * 方法有两个，第一：直接用传过来的已对账ID，第二：用传过来的对账条件
		 */
		//分流执行
		switch($type){
			case 'orders':
				$countData = Db::table('ims_foll_elec_order_head_copy')->alias('a')->join('ims_foll_elec_order_detail_copy b','a.id = b.head_id')->where(['b.id'=>['in',$confirmId]])->select();
				$count 	 = count($countData);
		        //计算总页数 
				$pageNum = ($count % $limit) == 0 ? (int)($count / $limit) : ceil($count / $limit);
				//数据集合
				$userinfo = Db::table('ims_foll_elec_order_head_copy')->alias('a')->join('ims_foll_elec_order_detail_copy b','a.id = b.head_id')->where(['b.id'=>['in',$confirmId]])->limit($page,$limit)->select();
			break;
			case 'goods':
				$countData = Db::table('ims_foll_goodsreghead')->alias('a')->join('ims_foll_goodsreglist b','a.id = b.head_id')->where(['b.id'=>['in',$confirmId]])->select();
				$count 	 = count($countData);
		        //计算总页数 
				$pageNum = ($count % $limit) == 0 ? (int)($count / $limit) : ceil($count / $limit);
				//数据集合
				$userinfo = Db::table('ims_foll_goodsreghead')->alias('a')->join('ims_foll_goodsreglist b','a.id = b.head_id')->where(['b.id'=>['in',$confirmId]])->limit($page,$limit)->select();
			break;
			case 'pay':
				$countData = Db::name('foll_payment_order')->where(['id'=>['in',$confirmId]])->select();
				$count 	 = count($countData);
		        //计算总页数 
				$pageNum = ($count % $limit) == 0 ? (int)($count / $limit) : ceil($count / $limit);
				//数据集合
				$userinfo = Db::name('foll_payment_order')->where(['id'=>['in',$confirmId]])->limit($page,$limit)->select();
			break;
			case 'auth':
				$countData = Db::name('foll_payment_userinfo')->where(['id'=>['in',$confirmId]])->field('completeTime,orderId,fee,userName')->select();
				$count 	 = count($countData);
		        //计算总页数 
				$pageNum = ($count % $limit) == 0 ? (int)($count / $limit) : ceil($count / $limit);
				//数据集合
				$userinfo = Db::name('foll_payment_userinfo')->where(['id'=>['in',$confirmId]])->field('completeTime,orderId,fee,userName')->limit($page,$limit)->select();
			break;
		}
		
		$order['ServerMoney'] = $payfee['serverfee'];//服务费用
		$order['Money']	   	  = $payfee['checkingfees'];//验核费用
		$order['Total']	   	  = $payfee['totalfees'];//合计总额
		$order['count']	   	  = $payfee['number'];//$count;//统计条数  人数
		$order['username']    = $payfee['user_name'];//商户名
        
		return json([
			'code'		=>1,
			'data' 		=>$userinfo,//数据列表
			'pages'		=>$pages,//分页数  1，2，3
			'tongji'	=>$order,//统计的数据
			'limit'		=>$limit,//每页显示条数
			'pageNum' 	=>$pageNum,//分页总数
			'uid'		=>$uid,//商户ID
			'accountDay'=>$accountDay,//对账日
			'type'		=>$type,//数据查收类型
			'confirmId' =>$confirmId,//数据ID
			'payf'		=>$payfee,
		]);
	}
	
	
	
	/**
	 * 有误待核
	 * 商户有误订单
	 * 2018-07-17
	 */
	public function Mistaken()
	{
		//分页配置
        $config = [
            'type' 		 =>'Layui',//分页类名
            'query'		 =>['s'=>'reconcil/Mistaken'],//url额外参数
            'var_page'	 =>'pages',//分页变量
            'newstyle'	 =>true,
        ];
		$order['queryOk'] = $this->db->table('foll_goodconfirm')->where(['uid'=>$this->admin['id'],'status'=>2])->order('accountDay desc')->pagess(11,$config);
		$order['title']   = '商户有误对账';
		$order['newDate'] = date('Y-m',time());//当前月份
		$this->assign('order',$order);
		return view('platform/mistaken');//渲染模板
	}
	
	/**
	 * 查看对应有误的商户账单
	 */
	public function Mistakensee(){
		//作为条件查询goodconfirm 表 confirmID 字段
		$uid   = input('post.uid')?input('post.uid'):1;//获取商户ID
		//对账日
		$accountDay = input('post.accountDay')?input('post.accountDay'):date('Y-m-d',time());
		//对账类型   goods：备案订单表，pay:支付订单，auth:
		$type   = input('post.type')?input('post.type'):'';
		//已完成对账ID
		$confirmId = input('post.confirmId')?input('post.confirmId'):1;
		//每页条数
		$limit  = input('post.limit') ? input('post.limit')  : 8;
		//接收页码
		$pages  = input('post.pages') ? input('post.pages')  : 1;
		//分页数
		$page   = ($pages-1)*$limit;
		//return json(['uid'=>$uid,'accountDay'=>$accountDay,'type'=>$type,'confirmId'=>$confirmId,'limit'=>$limit,'page'=>$pages]);
		$w = [
			'uid'=>$uid,
			'accountDay'=>$accountDay,
			'type'=>$type
		];
		$payfee = $this->db->table('foll_goodconfirm')->where($w)->field('number,serverfee,checkingfees,totalfees,user_name,startDate,endDate')->item();
		if(empty($payfee)){
			return json(['code'=>1,'msg'=>'数据为空']);
		}
		/**
		 * 方法有两个，第一：直接用传过来的已对账ID，第二：用传过来的对账条件
		 */
		//分流执行
		switch($type){
			case 'orders':
				$where = [
					'a.uid'=>$uid,
					'a.DeclTime'=> [
						['egt',$payfee['startDate']],//大于等于开始时间
						['elt',$payfee['endDate']],//并且小于等于结束时间
					]
				];
				
				$countData = Db::table('ims_foll_elec_order_head_copy')->alias('a')->join('ims_foll_elec_order_detail_copy b','a.id = b.head_id')->where($where)->select();
				$count 	 = count($countData);
		        //计算总页数 
				$pageNum = ($count % $limit) == 0 ? (int)($count / $limit) : ceil($count / $limit);
				//数据集合
				$userinfo = Db::table('ims_foll_elec_order_head_copy')->alias('a')->join('ims_foll_elec_order_detail_copy b','a.id = b.head_id')->where($where)->limit($page,$limit)->select();
				
			break;
			case 'goods':
				$where = [
					'a.uid'=>$uid,
					'a.InputDate'	=> [
						['egt',$payfee['startDate']],//大于等于开始时间
						['elt',$payfee['endDate']],//并且小于等于结束时间
					]
				];
				$countData = Db::table('ims_foll_goodsreghead')->alias('a')->join('ims_foll_goodsreglist b','a.id = b.head_id')->where($where)->select();
				$count 	 = count($countData);
		        //计算总页数 
				$pageNum = ($count % $limit) == 0 ? (int)($count / $limit) : ceil($count / $limit);
				//数据集合
				$userinfo = Db::table('ims_foll_goodsreghead')->alias('a')->join('ims_foll_goodsreglist b','a.id = b.head_id')->where($where)->limit($page,$limit)->select();
			break;
			case 'pay':
				$start = strtotime($payfee['startDate']);
				$end   = strtotime($payfee['endDate']);
				$where = [
		        	'uid'=>$uid,
			       	'submitTime'=>[
		        		['egt',date("YmdHis",$start)],
		        		['elt',date("YmdHis",$end)]
		        	]
		        ];
				$countData = Db::name('foll_payment_order')->where($where)->select();
				$count 	 = count($countData);
		        //计算总页数 
				$pageNum = ($count % $limit) == 0 ? (int)($count / $limit) : ceil($count / $limit);
				//数据集合
				$userinfo = Db::name('foll_payment_order')->where($where)->limit($page,$limit)->select();
			break;
			case 'auth':
				$start = strtotime($payfee['startDate']);
				$end   = strtotime($payfee['endDate']);
				$where = [
		        	'uid'=>$uid,
			       	'submitTime'=>[
		        		['egt',date("YmdHis",$start)],
		        		['elt',date("YmdHis",$end)]
		        	]
		        ];
				$countData = Db::name('foll_payment_userinfo')->where($where)->field('completeTime,orderId,fee,userName')->select();
				$count 	 = count($countData);
		        //计算总页数 
				$pageNum = ($count % $limit) == 0 ? (int)($count / $limit) : ceil($count / $limit);
				//数据集合
				$userinfo = Db::name('foll_payment_userinfo')->where($where)->field('completeTime,orderId,fee,userName')->limit($page,$limit)->select();
			break;
		}
		
		$order['ServerMoney'] = $payfee['serverfee'];//服务费用
		$order['Money']	   	  = $payfee['checkingfees'];//验核费用
		$order['Total']	   	  = $payfee['totalfees'];//合计总额
		$order['count']	   	  = $payfee['number'];//$count;//统计条数  人数
		$order['username']    = $payfee['user_name'];//商户名
		$order['startDate']	  = $payfee['startDate'];//开始时间
		$order['endDate']	  = $payfee['endDate'];//结束时间
        
		return json([
			'code'		=>1,
			'data' 		=>$userinfo,//数据列表
			'pages'		=>$pages,//分页数  1，2，3
			'tongji'	=>$order,//统计的数据
			'limit'		=>$limit,//每页显示条数
			'pageNum' 	=>$pageNum,//分页总数
			'uid'		=>$uid,//商户ID
			'accountDay'=>$accountDay,//对账日
			'type'		=>$type,//数据查收类型
			'confirmId' =>$confirmId,//数据ID
			'payf'		=>$payfee,
		]);
	}
	
	
	/**
	 * 编辑修改数据
	 */
	public function edit()
	{
		$str = input('get.gid');
		if(isset($str)) {
			//字符串分割数组
			$dataArr = explode("=",$str);
			$set['id'] = $id = $dataArr[0];
			$set['type'] = $type = $dataArr[1];
			$set['uid']  = $uid  = $dataArr[2];
			//print_r($dataArr);
			
			//分流执行
			switch($type){
				case 'goods':
					$userinfo = $this->db->table('foll_goodsreglist')->where(['id'=>$id])->item();
					$userinfo['RegPrice'] = sprintf("%.2f",$userinfo['RegPrice']);
				break;
				case 'pay':
					$userinfo = $this->db->table('foll_payment_order')->where(['id'=>$id])->item();
					$userinfo['payAmount'] = ($userinfo['payAmount']/100); 
				break;
				case 'auth':
					$userinfo = $this->db->table('foll_payment_userinfo')->where(['id'=>$id])->item();
					$userinfo['fee'] = ($userinfo['fee']/100);
				break;
			}
		}
		$this->assign('set',$set);
		$this->assign('info',$userinfo);
		return view('platform/edit');
	}
	
	/**
	 * 修改保存
	 */
	public function editSave(){
		$data = $_POST;
		if(isset($data)){
			$id = trim($data['id']);
			$type = trim($data['type']);
			$flag = false;
			
			switch($type){
				case 'goods':
					$money = trim($data['RegPrice']);
					$flag = $this->db->table('foll_goodsreglist')->where(['id'=>$id])->update(['RegPrice'=>$money]);
				break;
				case 'auth':
					$money = trim($data['fee'])*100;
					$flag = $this->db->table('foll_payment_userinfo')->where(['id'=>$id])->update(['fee'=>$money]);
				break;
				case 'pay':
					$money = trim($data['payAmount'])*100;
					$flag = $this->db->table('foll_payment_order')->where(['id'=>$id])->update(['payAmount'=>$money]);
				break;
			}
			
			if(!$flag){
				return json(['code'=>0,'msg'=>'数据更新错误，请重新操作！']);
			}
			return json(['code'=>1,'msg'=>'数据更新成功！']);
			
		} else {
			return json(['code'=>0,'msg'=>'数据有误，请重新操作！']);
		}
	}
	
	/**
	 * 已全部核查 
	 * @date  2018-07-18 17:46
	 */
	public function queryOk(){
		$uid   = trim(input('post.uid'));
		$type  = trim(input('post.type'));
		$accountDay = trim(input('post.accountDay'));
		$startDate  = trim(input('post.startDate'));
		$endDate    = trim(input('post.endDate'));
		
		$userinfo = $this->db->table('foll_business_admin')->where(['id'=>$uid])->field('user_email')->item();
		if(empty($userinfo)){
			return json(['code'=>0,'msg'=>'没有查到该用户的邮箱，请前往编辑填写！']);
		}
		$where = [
			'uid'		=>$uid,
			'type'		=>$type,
			'accountDay'=>$accountDay,
			'startDate'	=>$startDate,
			'endDate'	=>$endDate
		];
		$updata = [
			'status'	=>3,
			'confirmId'	=>null,
			'number'	=>null,
			'serverfee'	=>null,
			'checkingfees'=>null,
			'totalfees'	=>null
		];
		
		$up  = $this->db->table('foll_goodconfirm')->where($where)->update($updata);
		if($up){
			$this->sendEmail('true',$userinfo['user_email'],'管理员已核查，请重新对账！');
			return json(['code'=>1,'msg'=>'发送成功']);
		} else {
			return json(['code'=>0,'msg'=>'核查失败，请重新点击']);
		}
	}
	
	
	/**
	 * 管理员下载有误对账单;
	 * @author 赵金如
	 * @date   2018-07-18 16:18
	 */
	public function upload()
	{
		$uid   = trim(input('post.uid'));
		$type  = trim(input('post.type'));
		$accountDay = trim(input('post.accountDay'));
		$startDate  = trim(input('post.startDate'));
		$endDate    = trim(input('post.endDate'));
		$countData  = null;
		$countData['type'] = $type;
		//分流执行
		switch($type){
			case 'goods':
				$where = [
					'a.uid'=>$uid,
					'a.InputDate'	=> [
						['egt',$startDate],//大于等于开始时间
						['elt',$endDate],//并且小于等于结束时间
					]
				];
				$countData['data'] = Db::table('ims_foll_goodsreghead')->alias('a')->join('ims_foll_goodsreglist b','a.id = b.head_id')->where($where)->select();			
			break;
			case 'pay':
				$start = strtotime($startDate);
				$end   = strtotime($endDate);
				$where = [
		        	'uid'=>$this->admin['id'],
			       	'submitTime'=>[
		        		['egt',date("YmdHis",$start)],
		        		['elt',date("YmdHis",$end)]
		        	]
		        ];
				$countData['data'] = Db::name('foll_payment_order')->where($where)->select();
			break;
			case 'auth':
				$start = strtotime($startDate);
				$end   = strtotime($endDate);
				$where = [
		        	'uid'=>$this->admin['id'],
			       	'submitTime'=>[
		        		['egt',date("YmdHis",$start)],
		        		['elt',date("YmdHis",$end)]
		        	]
		        ];
				$countData['data'] = Db::name('foll_payment_userinfo')->where($where)->field('completeTime,orderId,fee,userName')->select();
			break;
		}
		
		return $this->Excels($countData);
		
		/*echo '<pre>';
		print_r($countData);*/
	}
	
	//导出Excel功能  2018-07-18 17:27
	public function Excels($dataArr = [])
	{
		if(empty($dataArr)){
			return json(['code'=>0,'msg'=>'数据不能为空!']);
		}
		$res = null;
		switch($dataArr['type']){
			case 'goods':
				$res = $this->Excelgoods($dataArr);
			break;
			case 'auth':
				$res = $this->Excelauth($dataArr);
			break;
			case 'pay':
				$res = $this->Excelpay($dataArr);
			break;			
		}
		return $res;
	}
	
	
	//备案商品  有误导出
	public function Excelgoods($Arr=[]){
		$data = $Arr['data'];
		//导出excel 数据
		$PHPExcel = new PHPExcel(); //实例化PHPExcel类，类似于在桌面上新建一个Excel表格
  		$PHPSheet = $PHPExcel->getActiveSheet(); //获得当前活动sheet的操作对象
  		$PHPSheet->setTitle('备案商品订单列表'); //给当前活动sheet设置名称
  		$PHPSheet->setCellValue('A1','备案时间')
  				 ->setCellValue('B1','备案编号')
  				 ->setCellValue('C1','备案金额')
  				 ->setCellValue('D1','备案商品名称');
  		//给当前活动sheet填充数据，数据填充是按顺序一行一行填充的，假如想给A1留空，可以直接setCellValue(‘A1’,’’);
  		$count = count($data)-1;
  		for($i=0; $i <= $count; $i++) {
  			$num = 2+$i;
  			$PHPSheet->setCellValue("A".$num,$data[$i]['OpTime'])
  				 ->setCellValue('B'.$num,"\t".$data[$i]['EntGoodsNo']."\t")
  				 ->setCellValue('C'.$num,(sprintf("%.2f",$data[$i]['RegPrice'])).'元')
  				 ->setCellValue('D'.$num,$data[$i]['GoodsName']);
  		}
  		$PHPWriter = PHPExcel_IOFactory::createWriter($PHPExcel,'Excel2007');//按照指定格式生成Excel文件，‘Excel2007’表示生成2007版本的xlsx，‘Excel5’表示生成2003版本Excel文件
  		$fileName  = "Goods".date('Y-m-d',time()).'.xlsx';
  		$path      = dirname(__FILE__).'/'.$fileName;
		$PHPWriter->save($path);
		$subject = '备案商品有误账单';
		$content = "提示：您好！有误账单已经发送到您的邮箱，请查收！";
		$Result    = $this->sendEmail($path,'805929498@qq.com',$subject,$content);
		unlink($path);
		
		if(!$Result) {
			//记录商户已对账
			return json(['code'=>0,'msg'=>'发送失败']);
		}
		//记录商户已对账
	    return json(['code'=>1,'msg'=>'发送成功']);
	}
	
	//实名认证  有误导出
	public function Excelauth($Arr=[]){
			$data = $Arr['data'];
		//导出excel 数据
		$PHPExcel = new PHPExcel(); //实例化PHPExcel类，类似于在桌面上新建一个Excel表格
  		$PHPSheet = $PHPExcel->getActiveSheet(); //获得当前活动sheet的操作对象
  		$PHPSheet->setTitle('实名认证订单列表'); //给当前活动sheet设置名称
  		$PHPSheet->setCellValue('A1','认证时间')
  				 ->setCellValue('B1','认证编号')
  				 ->setCellValue('C1','认证金额')
  				 ->setCellValue('D1','认证人名称');
  		//给当前活动sheet填充数据，数据填充是按顺序一行一行填充的，假如想给A1留空，可以直接setCellValue(‘A1’,’’);
  		$count = count($data)-1;
  		for($i=0; $i <= $count; $i++) {
  			$num = 2+$i;
  			$PHPSheet->setCellValue("A".$num,$data[$i]['completeTime'])
  				 ->setCellValue('B'.$num,"\t".$data[$i]['orderId']."\t")
  				 ->setCellValue('C'.$num,(sprintf("%.2f",$data[$i]['fee']/100)).'元')
  				 ->setCellValue('D'.$num,$data[$i]['userName']);
  		}
  		$PHPWriter = PHPExcel_IOFactory::createWriter($PHPExcel,'Excel2007');//按照指定格式生成Excel文件，‘Excel2007’表示生成2007版本的xlsx，‘Excel5’表示生成2003版本Excel文件
  		$fileName  = "Auth".date('Y-m-d',time()).'.xlsx';
  		$path      = dirname(__FILE__).'/'.$fileName;
		$PHPWriter->save($path);
		$subject = '实名认证有误账单';
		$content = "提示：您好！有误账单已经发送到您的邮箱，请查收！";
		$Result    = $this->sendEmail($path,'805929498@qq.com',$subject,$content);
		unlink($path);
		
		if(!$Result) {
			//记录商户已对账
			return json(['code'=>0,'msg'=>'发送失败']);
		}
		//记录商户已对账
	    return json(['code'=>1,'msg'=>'发送成功']);
	}
	
	//支付单  有误导出
	public function Excelpay($Arr=[]){
		$data = $Arr['data'];
		//导出excel 数据
		$PHPExcel = new PHPExcel(); //实例化PHPExcel类，类似于在桌面上新建一个Excel表格
  		$PHPSheet = $PHPExcel->getActiveSheet(); //获得当前活动sheet的操作对象
  		$PHPSheet->setTitle('跨境支付订单列表'); //给当前活动sheet设置名称
  		$PHPSheet->setCellValue('A1','订单时间')
  				 ->setCellValue('B1','订单编号')
  				 ->setCellValue('C1','订单金额')
  				 ->setCellValue('D1','购买人名称');
  		//给当前活动sheet填充数据，数据填充是按顺序一行一行填充的，假如想给A1留空，可以直接setCellValue(‘A1’,’’);
  		$count = count($data)-1;
  		for($i=0; $i <= $count; $i++) {
  			$num = 2+$i;
  			$strtime = strtotime($data[$i]['submitTime']);
  			$datatime = date('Y-m-d H:i:s',$strtime);
  			$PHPSheet->setCellValue("A".$num,$datatime)
  				 ->setCellValue('B'.$num,"\t".$data[$i]['orderId']."\t")
  				 ->setCellValue('C'.$num,(sprintf("%.2f",$data[$i]['payAmount']/100)).'元')
  				 ->setCellValue('D'.$num,$data[$i]['payerName']);
  		}
  		$PHPWriter = PHPExcel_IOFactory::createWriter($PHPExcel,'Excel2007');//按照指定格式生成Excel文件，‘Excel2007’表示生成2007版本的xlsx，‘Excel5’表示生成2003版本Excel文件
  		$fileName  = "Pay".date('Y-m-d',time()).'.xlsx';
  		$path      = dirname(__FILE__).'/'.$fileName;
		$PHPWriter->save($path);
		$subject = '跨境支付有误账单';
		$content = "提示：您好！有误账单已经发送到您的邮箱，请查收！";
		$Result    = $this->sendEmail($path,'805929498@qq.com',$subject,$content);
		unlink($path);
		
		if(!$Result) {
			//记录商户已对账
			return json(['code'=>0,'msg'=>'发送失败']);
		}
		//记录商户已对账
	    return json(['code'=>1,'msg'=>'发送成功']);
	}
	
	
	/**
	 * 平台应付   
	 * @author 赵金如
	 * @date   2018-07-20 14:33
	 */
	public function Payables(){
		$Money['title'] = '平台应付总额';
		$this->assign('Money',$Money);
		return view('payable');
	}
	
	public function Payable()
	{
		/**
		 * 开发步骤
		 *1、 已昨天时间为统计条件
		 *2、获取管理员设置的收费标准
		 *3、根据不同的标准获取不同的接口数据，计算
		 *4、最后显示总金额；
		 */
		//条件
		
		$start = input('post.startDate')?input('post.startDate'):date('Y-m-d 00:00:00',(time()-86400));
		$end   = $start?date("Y-m-d H:i:s",(strtotime($start)+86399)):date('Y-m-d H:i:s',(strtotime(date('Y-m-d 00:00:00',time()))-1));
		$new   = date("Y-m-d",strtotime($start));
		
		$settl = $this->db->table('foll_admin_settlement')->item();
		
		//$where1 = ['completeTime'=>['between',"{$start},{$end}"]];
		$where1 = [
			'completeTime'=>[
				['egt',$start],
				['elt',$end]
		]];
		//实名认证对账
		$auth = $this->db->table('foll_payment_userinfo')->where($where1)->field('fee')->lists();
		$Money['feeMoney']  = 0.00;
		if(!empty($auth)) {
			$feeMoney = 0;
			foreach($auth as $key=>$val) {
				$feeMoney += $val['fee'];
			}
			$Money['feeMoney'] = sprintf("%.2f",($feeMoney/100)*$settl['authPtaiMoney']);
		}
		
		//支付订单对账
		$subTimes = strtotime($start);
		$subTimes = date("YmdHis",$subTimes);
		$subTimed = strtotime($end);
		$subTimed = date("YmdHis",$subTimed);
		$where2 = ['submitTime'=>['between',"{$subTimes},{$subTimed}"]];
		$payOrder = $this->db->table('foll_payment_order')->where($where2)->field('payAmount')->lists();
		$Money['payMoney']  = 0.00;
		if(!empty($payOrder)) {
			$payMoney = 0;
			foreach($payOrder as $key=>$val) {
				$payMoney += $val['payAmount'];
			}
			$Money['payMoney'] = sprintf("%.2f",($payMoney/100)*$settl['payPtaiMoney']);
		}
		
		
		//备案商品对账
		$where3 = ['InputDate'=>[['egt',$start],['elt',$end]]];
		//备案订单头 
		$keepRecord = Db::table('ims_foll_goodsreghead')->where($where3)->select();	
		$Money['keepMoney']  = 0.00;
		if(!empty($keepRecord)) {
			if($settl['setMothod'] == 'pici'){
				$count      = count($keepRecord);
				$Money['keepMoney'] = sprintf("%.2f",($count * $settl['banMoney']));
				
			}else if($settl['setMothod'] == 'good') {
				$where3 = ['a.InputDate'=>[['egt',$start],['elt',$end]]];
				$keegood = Db::table('ims_foll_goodsreghead')->alias('a')->join('ims_foll_goodsreglist b','a.id = b.head_id')->where($where3)->select();
				$count  = count($keegood);
				$Money['keepMoney'] = sprintf("%.2f",($count*$settl['banMoney']));
			}
		}
		
		//电子订单
		$where4 = ['a.DeclTime'=>[['egt',$start],['elt',$end]]];
		$orderRecord = Db::table('ims_foll_elec_order_head_copy')->alias('a')->join('ims_foll_elec_order_detail_copy b','a.id = b.head_id')->where($where4)->select();
		$Money['orderMoney']  = 0.00;
		if(!empty($orderRecord)) {
			//订单数量
			if($settl['pserMothod'] == 'Element'){
				$count       = count($orderRecord);
				$Money['orderMoney']  = sprintf("%.2f",($count * $settl['pserMoney']));
				
				//订单金额的百分比
			}else if($settl['pserMothod'] == 'Percentage') {
				$money = 0;
				foreach($orderRecord as $key=>$val) {
					$money += $val['Price'];
				}
				$Money['orderMoney'] = sprintf("%.2f",($money * $settl['pserMoney']));
			}
		}
		//总费用
		$Money['total'] = ($Money['feeMoney']+$Money['payMoney']+$Money['keepMoney']+$Money['orderMoney']);
		$title = '平台应付总额';
		//$data[] = $Money;
		$this->assign('new',$new);
		$this->assign('title',$title);
		$this->assign('Money',$Money);
		return view('payable');
		//echo '实名认证费：'.$feeMoney.'元  支付订单费用：'.$payMoney.'元  商品备案费用：'.$keepMoney .'元  电子订单费用'.$orderMoney.'元  总费用: '.$total;
		//return json(['code'=>1,'msg'=>'','count'=>1,'data'=>$data]);
	}
	
	//发送电子邮件给商户
	public function sendEmail($path,$email,$subject = '备案商品对账文件已发送到您的邮箱',$content = "提示：您的对账已经发送到邮箱，请查收！,您可请登录后台。<a href='http://shop.gogo198.cn/foll/public/?s=account/login'>点击前往登录后台</a>") {
		$name    = '系统管理员';
		if($path == 'true'){
			$status  = send_mail($email,$name,$subject,$content);
		} else {
			$status  = send_mail($email,$name,$subject,$content,['0'=>$path]);
		}
		if($status) {
			return true;
		} else {
			return false;
		}
	}
	
}