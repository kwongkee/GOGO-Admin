<?php
/**
 * 邦付宝
 * @author 赵金如
 * @date   2018-06-05
 */
namespace app\declares\controller;

use think\Controller;
use Util\data\Sysdb;
use Util\data\Redis;
use think\Session;
use think\Request;
use think\Loader;
use think\log;
use CURLFile;
use PHPExcel_IOFactory;
use PHPExcel;

class Helpay extends BaseAdmin
{
	public $partenrId;
	public $pkey;
	public $admin;//用户数据
	public function __construct()
	{
		parent::__construct();
		//实例化数据库
		$this->db = new Sysdb;
		$this->admin = session('admin');//登录数据信息
		
		//测试账号
		//$this->partnerId = '10000000182';
		//$this->pkey = '30820122300d06092a864886f70d01010105000382010f003082010a0282010100cc26d29f5f95612b8557bc3db217c2d67022595943f2648006b76863d6770b0ee5e627cf279c17df75e188f5f270092d376fed846c470cb05e10fa7688180afbf807f5165cfae82a732ff5a3f63e4b43292720ef16039af2be7c2ebfc8f49c40f835f3da50d8934011d42497c9684134e52f9396c60e038bed33cb8734c58114f825e4a51e533871b7a514afae20af045a0a53099418ec6a7a8c3a4ea0d858ba1f5b2efa9a05c9539661941f6ab76f171c2febb0b68b6fb26bd5da885c5e857f700605b4dfd8525924ba58e99560aa7403e77c5e1e82a6ddd38b18eb92369abe6747d7efcb2177cda5093f77400f647aebf529f45ae81d228adbbcd3793c4b9f0203010001';
		
		//正式账号
		$this->partnerId = '10000002473';
		$this->pkey 	 = '30820122300d06092a864886f70d01010105000382010f003082010a0282010100ad710bc122ce76cad43df144049fba2cfee73f8625ef1505a22bceec20072792b52efb12e372dad11e1406afd52c5fad80dbbfbb0c6db60eb4a93b48f70be09344ef2e1ecfcaddb0aa87bd8e7c3d2e73a5db783a8728c3c4fb78e709ce31ca11aa571a09f371002a01fa94ffa1213b2ef16b74204c26cedf15e86747a0abb5bcf6b7a445d0f99d548d553f81efe4c0a9eec853b343ecdb11283f392a6025edd8d1355c791fbc667b22c04e7fb2c719fdef2da27cf3d7e424eda84d552cfc9f566c414aee471010edda4914280e614aa2a36fc11b15cfdc44c346de9fc235fbe647cbc0509b08fd281a94e660db06a8881bac0bb5d341d697cd40bc00ee1011130203010001';
		
		
		$config = [
			'host'	=> '127.0.0.1',
			'port'	=> '6379',
			'auth'	=> '123456',
		];
		$attr = [
			//连接超时时间，redis配置文件中默认为300秒
			'timeout'=>300,
			//选择数据库
			'db_id'=>6,
		];
		
		$this->redis = null;
		//实例化 Redis 缓存类
		$this->redis = Redis::getINstance($config,$attr);
		$this->rs = $this->redis->getRedis();
		
	}
	
	//监控身份验证
	public function MonitorRealName() {
		
		
		//$k = 'key';
		/*for($i = 0;$i<10;$i++) {
			
			$a['name'] = "names:{$i}";
			$a['age']  = "2{$i}";
			if($i%2 == 0){
				$a['code'] = 1;
			} else {
				$a['code'] = 2;
			}
			$temp = json_encode($a);
			
			$this->rs->rpush($k,$temp);
		}*/
		
		//出栈
		//$this->rs->lpop($id);ss
		
		/*echo '<hr>';
		echo '<pre>';
		$keys = 'RealNames';//删除啊
		$res = $this->redis->lRange($keys,1,-1);
		//$this->rs->lpop($keys);11
		echo '<hr>';
		//通过
		print_r($res);*/
		
		
		/*$count = count($res);
		//请求执行
		if($count > 0) {
			$data = json_decode($res[0],true);
			if($data['code'] == 2) {
				//删除数组头元素宿舍
				$this->rs->lpop($k);
			} else {
				print_r($data);
			}
		}//变啊
		
		$this->rs->lpop($k);*/
		
		/*while($count > 0) {//判断执行
			$data = json_decode($res[0],true);
			if($data['code'] == 2) {
				//删除数组头元素
				//$this->rs->lpop($k);
				array_shift($res)
			} else {
				print_r($data);
			}
		}*/
		
		/*echo '<hr>';
		//通过
		print_r($res);
		
		die;*/
		
		/*echo '身份证验证监控';
		$wher = [
			'submitTime'=>['egt','20180814145411'],
		];
		$wher1 = [
			'submitTime'=>['egt','20180814145411'],
			'resultCode'=>'0000'
		];
		$count = $this->db->table('foll_payment_userinfo')->where($wher)->counts();
		$countok = $this->db->table('foll_payment_userinfo')->where($wher1)->counts();
		
		echo '订单提交监控  开始时间20180-8-14 00：02：00';
		echo '<hr>';
		echo '验证总数：'.$count;
		echo '<hr>';
		echo '验证成功数: '.$countok;
		echo '<hr>';
		echo '验证失败数: '.($count-$countok);*/
		
		
		//导出身份验证失败的
		$pici = input('get.pici');
		$pici = trim($pici);
		
		$email = $this->admin['user_email'];
		$p = $this->db->table('foll_realname_error')->where(['title'=>$pici])->item();
		if(empty($p)) {
			echo '不存在该批次号，请检查！';
			die;
		} else {
			//导出
			$res = $this->Excelport($pici,$email);
			print_r($res);
		}
		
	}
	
	/**
	 * 导出功能
	 */
	protected function Excelport($pici,$email) {
		//获取验证失败
    	$order = $this->db->table('foll_realname_error')->where(['title'=>$pici,'resultCode'=>['neq','0000'],'resultMsg'=>['neq','信息一致，认证成功']])->lists();
    	if(empty($order)){
    		return json(['code'=>0,'data'=>'没有验证失败数据']);
    	}
    	//$order = $this->db->table('foll_realname_error')->where(['title'=>$pici])->lists();
    	$PHPExcel = new PHPExcel(); //实例化PHPExcel类，类似于在桌面上新建一个Excel表格
  		$PHPSheet = $PHPExcel->getActiveSheet(); //获得当前活动sheet的操作对象
  		$PHPSheet->setTitle('身份验证信息'); //给当前活动sheet设置名称
  		$PHPSheet->setCellValue('A1','导入批次号')
  				 ->setCellValue('B1','请求订单编号')
  				 ->setCellValue('C1','提交时间')
  				 ->setCellValue('D1','用户姓名')
  				 ->setCellValue('E1','用户身份证')
  				 ->setCellValue('F1','物流订单编号')
  				 ->setCellValue('G1','返回结果码')
  				 ->setCellValue('H1','返回信息')
  				 ->setCellValue('I1','验证类型');
  		//给当前活动sheet填充数据，数据填充是按顺序一行一行填充的，假如想给A1留空，可以直接setCellValue(‘A1’,’’);
  		$count = count($order)-1;
  		$num = 0;
  		for($i=0; $i <= $count; $i++) {
  			$num = 2+$i;
  			$PHPSheet->setCellValue("A".$num,"\t".$order[$i]['title']."\t")
  				 ->setCellValue('B'.$num,"\t".$order[$i]['orderId']."\t")
  				 ->setCellValue('C'.$num,date('Y-m-d H:i:s',strtotime($order[$i]['submitTime'])))
  				 ->setCellValue('D'.$num,$order[$i]['userName'])
  				 ->setCellValue('E'.$num,"\t".$order[$i]['userId']."\t")
  				 ->setCellValue('F'.$num,"\t".$order[$i]['WaybillNo']."\t")
  				 ->setCellValue('G'.$num,$order[$i]['resultCode'])
  				 ->setCellValue('H'.$num,$order[$i]['resultMsg'])
  				 ->setCellValue('I'.$num,($order[$i]['status']=='A'?'平台本地验证':'平台接口验证'));
  		}
  		
  		$PHPWriter = PHPExcel_IOFactory::createWriter($PHPExcel,'Excel2007');//按照指定格式生成Excel文件，‘Excel2007’表示生成2007版本的xlsx，‘Excel5’表示生成2003版本Excel文件
  		$fileName  = "UserInfo".date('Y-m-d',time()).'.xlsx';
  		$path      = dirname(__FILE__).'/'.$fileName;
		$PHPWriter->save($path);
		$Result    = $this->sendEmail($path,$email);
		unlink($path);
		if(!$Result) {
			return json(['code'=>1,'msg'=>'发送失败']);
		}
	    return json(['code'=>0,'data'=>'发送成功']);
    }
	
	
	//监控身份验证
	public function MonitorOrder(){
		/*$wher = [
			'submitTime'=>['egt','20180814145411'],
		];
		$wher1 = [
			'submitTime'=>['egt','20180814145411'],
			'resultCode'=>'0000',
			'chkMark'	=>2
		];
		$count = $this->db->table('foll_payment_order')->where($wher)->counts();
		$countok = $this->db->table('foll_payment_order')->where($wher1)->counts();
		
		echo '订单提交监控  开始时间20180-8-14 00：02：00';
		echo '<hr>';
		echo '支付提交总数：'.$count;
		echo '<hr>';
		echo '支付回调数: '.$countok;
		echo '<hr>';
		echo '支付未回调数: '.($count-$countok);
		echo '<hr>';
		echo '查询时间：'.date('Y-m-d H:i:s',time());*/
		
		echo '<pre>';
		$keys = 'RealNames';//
		$res = $this->redis->lRange($keys,1,-1);
		//$this->rs->lpop($keys);11
		echo '<hr>';
		//通过
		print_r($res);
		
		//导出数据
		/*echo '<pre>';
		$wher2 = [
			'submitTime'=>['egt','20180814000200'],
		];
		$orders  = $this->db->table('foll_payment_order')->where($wher2)->lists();
		//print_r($order);  导出支付单
		$res = $this->exportOrder($orders);
		print_r($res);*/
	}
	
	/**
	 * 导出身份验证信息 2018-08-13
	 */
	public function exportOrder($order=[]) {
		
		//$times = isset($_GET['time']) ? trim($_GET['time']) : '20180813222156';
		
		//->where([''])
		//$userinfo = $this->db->table('foll_payment_userinfo')->where(['submitTime'=>['gt',$times]])->lists();
		//批次
		//$Number   = count($order);
		//统计；提交日期，批次数，应收，应付；
		
		$PHPExcel = new PHPExcel(); //实例化PHPExcel类，类似于在桌面上新建一个Excel表格
  		$PHPSheet = $PHPExcel->getActiveSheet(); //获得当前活动sheet的操作对象
  		$PHPSheet->setTitle('支付单信息'); //给当前活动sheet设置名称
  		$PHPSheet->setCellValue('A1','请求订单编号')
  				 ->setCellValue('B1','提交时间')
  				 ->setCellValue('C1','用户手机号')
  				 ->setCellValue('D1','用户姓名')
  				 ->setCellValue('E1','用户身份证')
  				 ->setCellValue('F1','报关订单编号')
  				 ->setCellValue('G1','物流订单编号')
  				 ->setCellValue('H1','报关金额')
  				 ->setCellValue('I1','支付单号')
  				 ->setCellValue('J1','报关状态')
  				 ->setCellValue('K1','处理成功时间')
  				 ->setCellValue('L1','返回结果码')
  				 ->setCellValue('M1','返回状态')
  				 ->setCellValue('N1','返回信息');
  		//给当前活动sheet填充数据，数据填充是按顺序一行一行填充的，假如想给A1留空，可以直接setCellValue(‘A1’,’’);
  		$count = count($order)-1;
  		$num = 0;
  		for($i=0; $i <= $count; $i++) {
  			$num = 2+$i;
  			$PHPSheet->setCellValue("A".$num,"\t".$order[$i]['orderId']."\t")
  				 ->setCellValue('B'.$num,date('Y-m-d H:i:s',strtotime($order[$i]['submitTime'])))
  				 ->setCellValue('C'.$num,$order[$i]['payerPhoneNumber'])
  				 ->setCellValue('D'.$num,$order[$i]['payerName'])
  				 ->setCellValue('E'.$num,"\t".$order[$i]['paperNumber']."\t")
  				 ->setCellValue('F'.$num,"\t".$order[$i]['orderNo']."\t")
  				 ->setCellValue('G'.$num,"\t".$order[$i]['WaybillNo']."\t")
  				 ->setCellValue('H'.$num,$order[$i]['payAmount'])
  				 ->setCellValue('I'.$num,"\t".$order[$i]['payTransactionNo']."\t")
  				 ->setCellValue('J'.$num,$order[$i]['chkMark'])
  				 ->setCellValue('K'.$num,"\t".$order[$i]['completeTime']."\t")
  				 ->setCellValue('L'.$num,$order[$i]['resultCode'])
  				 ->setCellValue('M'.$num,$order[$i]['resultMsg'])
  				 ->setCellValue('N'.$num,$order[$i]['failInfo']);
  		}
  		
  		$PHPWriter = PHPExcel_IOFactory::createWriter($PHPExcel,'Excel2007');//按照指定格式生成Excel文件，‘Excel2007’表示生成2007版本的xlsx，‘Excel5’表示生成2003版本Excel文件
  		$fileName  = "OrderExport".date('Y-m-d',time()).'.xlsx';
  		$path      = dirname(__FILE__).'/'.$fileName;
		$PHPWriter->save($path);
		$Result    = $this->sendEmail($path,'805929498@qq.com');
		unlink($path);
		if(!$Result) {
			return json(['code'=>1,'msg'=>'发送失败']);
		}
	    return json(['code'=>0,'data'=>'发送成功']);
		
	}
	
	
	/**
	 * 导出身份验证信息 2018-08-13
	 */
	public function export() {
		
		$times = isset($_GET['time']) ? trim($_GET['time']) : '20180813222156';
		
		//->where([''])
		$userinfo = $this->db->table('foll_payment_userinfo')->where(['submitTime'=>['gt',$times]])->lists();
		//批次
		$Number   = count($userinfo);
		//统计；提交日期，批次数，应收，应付；
		
		$PHPExcel = new PHPExcel(); //实例化PHPExcel类，类似于在桌面上新建一个Excel表格
  		$PHPSheet = $PHPExcel->getActiveSheet(); //获得当前活动sheet的操作对象
  		$PHPSheet->setTitle('身份证验证信息'); //给当前活动sheet设置名称
  		$PHPSheet->setCellValue('A1','认证订单号')
  				 ->setCellValue('B1','认证提交时间')
  				 ->setCellValue('C1','用户手机号')
  				 ->setCellValue('D1','用户姓名')
  				 ->setCellValue('E1','用户身份证')
  				 ->setCellValue('F1','认证处理时间')
  				 ->setCellValue('G1','认证处理结果')
  				 ->setCellValue('H1','认证状态');
  		//给当前活动sheet填充数据，数据填充是按顺序一行一行填充的，假如想给A1留空，可以直接setCellValue(‘A1’,’’);
  		$count = count($userinfo)-1;
  		$num = 0;
  		for($i=0; $i <= $count; $i++) {
  			$num = 2+$i;
  			$PHPSheet->setCellValue("A".$num,"\t".$userinfo[$i]['orderId']."\t")
  				 ->setCellValue('B'.$num,date('Y-m-d H:i:s',strtotime($userinfo[$i]['submitTime'])))
  				 ->setCellValue('C'.$num,$userinfo[$i]['phone'])
  				 ->setCellValue('D'.$num,$userinfo[$i]['userName'])
  				 ->setCellValue('E'.$num,"\t".$userinfo[$i]['userId']."\t")
  				 ->setCellValue('F'.$num,$userinfo[$i]['completeTime'])
  				 ->setCellValue('G'.$num,$userinfo[$i]['resultCode'])
  				 ->setCellValue('H'.$num,$userinfo[$i]['resultMsg']);
  		}
  		
  		$PHPWriter = PHPExcel_IOFactory::createWriter($PHPExcel,'Excel2007');//按照指定格式生成Excel文件，‘Excel2007’表示生成2007版本的xlsx，‘Excel5’表示生成2003版本Excel文件
  		$fileName  = "RealNameExport".date('Y-m-d',time()).'.xlsx';
  		$path      = dirname(__FILE__).'/'.$fileName;
		$PHPWriter->save($path);
		$Result    = $this->sendEmail($path,'805929498@qq.com');
		unlink($path);
		if(!$Result) {
			return json(['code'=>1,'msg'=>'发送失败']);
		}
	    return json(['code'=>0,'data'=>'发送成功']);
		
	}
	
		//发送电子邮件给商户
	public function sendEmail($path,$email,$subject = '请及时登录查看') {
		$name    = '系统管理员';
		$time = date('Y-m-d H:i:s',time());
		$content = "提示：您有身份信息导出  时间：{$time}</a>";
    	if($path == 'true'){//没有数据发送
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
	
	//实名认证窗口
	public function realnames()
	{
		return $this->fetch();
	}
	
	//跨境实名认证
	public function RealName()
	{	
		$newInfo = null;
		$info = $_POST['domain'];//表单数据
		foreach($info as $key=>$val) 
		{
			$newInfo[$key] = trim($val);
			if(trim($val) == '')
			{
				echo json_encode(['code'=>0,'msg'=>'表单信息不能为空,请检查!']);
				die;
			}
		}
		
		$flag = false;
		$info = $this->db->table('foll_payment_userinfo')->where(['userId'=>$newInfo['userId'],'resultCode'=>'0000'])->order(' id desc ')->item();
		if(!$info)
		{
			$flag = true;
		} else {
			echo json_encode(['code'=>1,'msg'=>'该信息已认证，请勿重复认证!']);
			die;
		}
		
		if($flag) {//用户身份认证
			
			//报文部分
			$user = $sendArr = [
				'version'		=>	'1.0',
				//订单号(32)纯数字
				'orderId'		=>	date('YmdHis',time()).mt_rand(1111,9999),
				//订单提交时间
				'submitTime'	=>	date('YmdHis',time()),
				//商户 ID  会员ID
				'partnerId'		=>	$this->partnerId,
				/**
				 * 认证信息
				 */
				//姓名
				'userName'		=>	$newInfo['userName'],
				//身份证号码
				'userId'		=>	$newInfo['userId'],
				/**
				 * 安全信息
				 */
				//扩展字段
				'remark'		=>	'remark',
				//编码方式 1：UTF-8 
				'charset'		=>	1,
				//1：RSA 方式  2：MD5 方式
				'signType'		=>	2,
			];
			
			$user['uid'] = $this->admin['id'];//当前操作用户ID
			$this->db->table('foll_payment_userinfo')->insert($user);
			//数据拼接
			$signStr 			= $this->Splicing($sendArr);
			//数据加密
			$signMsg 			= $signStr."&pkey=".$this->pkey;
			$sendArr['signMsg'] = md5($signMsg);
			//发送报文信息
			
			//$url = "http://140.143.36.105:8085/webgate/realNameAuthentication.htm";
			$url = 'https://www.cfmtec.com/webgate/realNameAuthentication.htm';
			//明细文件上传  数据提交
			$res = $this->postData($url,$sendArr);
			//Url=a&b=c 转换成数组
			parse_str($res,$DataInfo);
			//写入日志  返回信息
			file_put_contents("./paylog/Helpay/RealName.txt", json_encode($DataInfo)."\r\n",FILE_APPEND);
			
			$update = [
				'sysOrderNo'	=>	$DataInfo['sysOrderNo']?$DataInfo['sysOrderNo']:'',
				'completeTime'	=>	$DataInfo['completeTime'],
				'fee'			=>	$DataInfo['fee'],
				'resultCode'	=>	$DataInfo['resultCode'],
				'resultMsg'		=>	$DataInfo['resultMsg'],
			];
			
			$Rupdate = $this->db->table('foll_payment_userinfo')->where(['orderId'=>$DataInfo['orderId']])->update($update);
				
			//如果返回结果代码0001 并且消息返回请求已受理
			if($DataInfo['resultCode'] == '0000' && $DataInfo['resultMsg'] == '信息一致，认证成功')
			{
				echo json_encode(['code'=>1,'msg'=>'认证成功!','data'=>$DataInfo]);
				die;
			}
			
			echo json_encode(['code'=>0,'msg'=>'认证失败!','data'=>$DataInfo]);
			die;
		}
		
	}
	
	/**
	 * 身份证批量验证
	 */
	//身份验证导入 2018-08-14
	public function ImportRealName(Request $request ) {
		
		if ( $request->isGet() ) {
            return view('helpay/decl_order');
        }
        if ( $request->isPost() ) {
            $this->loadWithFileAndRequest($request);
        }
	}
	
	/*
     * 获取页面提交上传文件
     * return true/false  2018-08-14
     */
    protected function loadWithFileAndRequest (Request $request )
    {
    	$config = [
			'host'	=> '127.0.0.1',
			'port'	=> '6379',
			'auth'	=> '123456',
		];
		$attr = [
			//连接超时时间，redis配置文件中默认为300秒
			'timeout'=>300,
			//选择数据库
			'db_id'=>6,
		];
		
		$this->redis = null;
		//实例化 Redis 缓存类
		$this->redis = Redis::getINstance($config,$attr);
		$this->rs = $this->redis->getRedis();
		$keys = 'RealNames';//缓存key
		$res = $this->redis->lRange($keys,1,-1);
		
		if(!empty($res)) {
			//第一个数据
			$dataArr = json_decode($res[0],true);
			//电子邮箱
			$pici    = $dataArr['title'];//批次一
			$this->error("您有批次号为：{$pici}的身份验证正在执行，请稍后再导入!", Url('helpay/Import'));
		}
    	
    	if ( empty($request->post('ImportName')) ) {
            $this->error('本次导入标题不能为空', Url('helpay/Import'));
        }
        
    	$title = $request->post('ImportName');
    	$titles = $this->db->table('foll_realname_general')->field('rid')->where(['title'=>$title])->item();
    	if(!empty($titles)) {//判断标题
    		$this->error('该标题已存在，请重新填写！', Url('helpay/Import'));
    	}
    	
        $dirPath = ROOT_PATH . '/public/uploads/realname/';
        $fileObj = $request->file('file');
       
        if ( !is_object($fileObj) ) {
            $this->error('未知错误', Url('helpay/Import'));
        }
        
        $moveInfo = $fileObj->move($dirPath);
        $err   = $this->handleFile($dirPath . $moveInfo->getSaveName(), $request->post());
         
        if ( !$moveInfo ) {
            $this->error($fileObj->getError(), Url('helpay/Import'));
        }
        
	    if(!$err) {
	        $this->error('数据有误！',Url('helpay/Import'));
	    }
        
        $this->success('导入完成，请耐心等待验证！',Url('helpay/Import'));
        
    }
    
    
    //导入身份验证信息
    protected function handleFile ( $file, $inData )
    {
        
        try {
            $this->objReaders = PHPExcel_IOFactory::createReader(PHPExcel_IOFactory::identify($file));
            $this->objReaders->setReadDataOnly(true);
            $this->PHPExcel = $this->objReaders->load($file);
            
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
        //获取 excel 表数据
        $d  = $this->fetchHeadInfo($inData);
        
        unset($this->objReaders,$this->PHPExcel);
        @unlink($file);
        
        return $d;
    }
    
    /**
     * 读取身份信息前往验证   阿里云接口
     * 2018-08-14
     */
    protected function fetchHeadInfo ( $hid )
    {
        $currSheet = $this->PHPExcel->getSheet(0);
        $row       = $currSheet->getHighestRow();
        $col       = $currSheet->getHighestColumn();
        $data      = [];
        
        $time      = time();
       
       	$signStr   = '';
       	$signMsg   = '';
		$uid   = $this->admin['id'];//当前操作用户ID
		$email = $this->admin['user_email'];//邮箱
		$partenrIds = $this->partnerId;//商户号
		$pkey       = $this->pkey;//秘钥
		
		//订单时间
		$d = date('YmdHi',time());
		
        for ($i = 2; $i <= $row; $i++) {
        	
        	$insert  	= [];
            $roInfo     = [];
            $insert['uid']      	= $uid;//导入用户的ID
            $roInfo['version']   	= '1.0';
            $insert['orderId'] 	  	= $roInfo['orderId']		= date('YmdHis',time()).mt_rand(1111,9999).mt_rand(1000,9999);
            $insert['submitTime'] 	= $roInfo['submitTime']		= $d.mt_rand(10,59);
            $insert['partnerId']    = $roInfo['partnerId']		= $partenrIds;
            $insert['userName'] 	= $roInfo['userName']     	= trim($currSheet->getCell('B'. $i)->getValue());
            $insert['userId'] 		= $roInfo['userId']     	= strtoupper(trim($currSheet->getCell('C'. $i)->getValue()));
            $roInfo['remark']		= $email;
            $roInfo['charset']		= 1;
            $roInfo['signType']		= 2;
            
			//$signStr 				= $this->Splicing($roInfo);//数据拼接
			//$signMsg 				= $signStr.'&pkey='.$pkey;//数据加密
			$roInfo['signMsg']  	= 'no';//md5($signMsg);
            
            $insert['title']  	 = $roInfo['title'] = trim($hid['ImportName']);//批次标题
            $insert['WaybillNo'] = trim($currSheet->getCell('A' . $i)->getValue());
            
            $this->db->table('foll_realname_error')->insert($insert);
            $insert['remark']		= $email;
            $this->db->table('foll_realname_general')->insert($insert);
            
            //先写表；查本地，请求数据  A 本地验证成功   接口验证成功  接口或者远程验证失败
            $keys = 'RealNames';
			$temp = json_encode($roInfo);
			//加入缓存
			$this->rs->rpush($keys,$temp);
        }
        //return $data;
        return true;
    }
	
	
	/**
     * 读取身份信息前往验证
     * 2018-08-14
     * 备份
     */
    protected function fetchHeadInfoBack ( $hid )
    {
        $currSheet = $this->PHPExcel->getSheet(0);
        $row       = $currSheet->getHighestRow();
        $col       = $currSheet->getHighestColumn();
        $data      = [];
        
        $time      = time();
       
       	$signStr   = '';
       	$signMsg   = '';
		$uid   = $this->admin['id'];//当前操作用户ID
		$email = $this->admin['user_email'];//邮箱
		$partenrIds = $this->partnerId;//商户号
		$pkey       = $this->pkey;//秘钥
		
		//订单时间
		$d = date('YmdHi',time());
		
        for ($i = 2; $i <= $row; $i++) {
        	
        	$insert  	= [];
            $roInfo     = [];
            $insert['uid']      	= $uid;//导入用户的ID
            $roInfo['version']   	= '1.0';
            $insert['orderId'] 	  	= $roInfo['orderId']		= date('YmdHis',time()).mt_rand(1111,9999).mt_rand(1000,9999);
            $insert['submitTime'] 	= $roInfo['submitTime']		= $d.mt_rand(10,59);
            $insert['partnerId']    = $roInfo['partnerId']		= $partenrIds;
            $insert['userName'] 	= $roInfo['userName']     	= trim($currSheet->getCell('B'. $i)->getValue());
            $insert['userId'] 		= $roInfo['userId']     	= strtoupper(trim($currSheet->getCell('C'. $i)->getValue()));
            $roInfo['remark']		= $email;
            $roInfo['charset']		= 1;
            $roInfo['signType']		= 2;
            
			$signStr 				= $this->Splicing($roInfo);//数据拼接
			$signMsg 				= $signStr.'&pkey='.$pkey;//数据加密
			$roInfo['signMsg']  	= md5($signMsg);
            
            $insert['title']  	 = $roInfo['title'] = trim($hid['ImportName']);//批次标题
            $insert['WaybillNo'] = trim($currSheet->getCell('A' . $i)->getValue());
            
            $this->db->table('foll_realname_error')->insert($insert);
            $insert['remark']		= $email;
            $this->db->table('foll_realname_general')->insert($insert);
            
            //先写表；查本地，请求数据  A 本地验证成功   接口验证成功  接口或者远程验证失败
            $keys = 'RealNames';
			$temp = json_encode($roInfo);
			//加入缓存
			$this->rs->rpush($keys,$temp);
        }
        //return $data;
        return true;
    }
	
	/**
	 * 将提交上来的用户身份信息进行认证
	 */
	public function AuthName($dataArr = null)
	{	
		$newInfo = null;
		foreach($dataArr as $key=>$val) 
		{
			$newInfo[$key] = trim($val);
			if(trim($val) == '')
			{
				return $res = ['code'=>0,'msg'=>'Data Is Null'];
			}
		}
		
		//报文部分
		$user = $sendArr = [
			'version'		=>	'1.0',
			//订单号(32)纯数字
			'orderId'		=>	date('YmdHis',time()).mt_rand(1111,9999),
			//订单提交时间
			'submitTime'	=>	date('YmdHis',time()),
			//商户 ID  会员ID
			'partnerId'		=>	$this->partnerId,
			/**
			 * 认证信息
			 */
			//姓名
			'userName'		=>	trim($newInfo['userName']),
			//身份证号码
			'userId'		=>	trim($newInfo['userId']),
			/**
			 * 安全信息
			 */
			//扩展字段
			'remark'		=>	'remark',
			//编码方式 1：UTF-8 
			'charset'		=>	1,
			//1：RSA 方式  2：MD5 方式
			'signType'		=>	2,
		];
		$user['uid'] = $this->admin['id'];//当前操作用户ID
		$this->db->table('foll_payment_userinfo')->insert($user);
		
		//数据拼接
		$signStr 			= $this->Splicing($sendArr);
		//数据加密
		$signMsg 			= $signStr."&pkey=".$this->pkey;
		$sendArr['signMsg'] = md5($signMsg);
		//发送报文信息
		
		$url = 'https://www.cfmtec.com/webgate/realNameAuthentication.htm';
		//明细文件上传  数据提交
		$res = $this->postData($url,$sendArr);
		//Url=a&b=c 转换成数组
		parse_str($res,$DataInfo);
		//写入日志  返回信息
		file_put_contents("./paylog/Helpay/RealName2.txt", json_encode($DataInfo)."\r\n",FILE_APPEND);
		
		$update = [
			'sysOrderNo'	=>	$DataInfo['sysOrderNo'] ? $DataInfo['sysOrderNo']: '' ,
			'completeTime'	=>	$DataInfo['completeTime'],
			'fee'			=>	$DataInfo['fee'],
			'resultCode'	=>	$DataInfo['resultCode'],
			'resultMsg'		=>	$DataInfo['resultMsg'],
		];
		
		$Rupdate = $this->db->table('foll_payment_userinfo')->where(['orderId'=>$DataInfo['orderId']])->update($update);
			
		//如果返回结果代码0001 并且消息返回请求已受理
		if($DataInfo['resultCode'] == '0000' && $DataInfo['resultMsg'] == '信息一致，认证成功')
		{
			return $res = ['code'=>1,'msg'=>'success','data'=>$DataInfo];
		}
		
		return $res = ['code'=>0,'msg'=>'error','data'=>$DataInfo];
	}

	
	/**
	 * 报关订单提交   正式用
	 * 2018-06-10
	 */
	public function Ordersubmits($dataArr = null)
	{
		/**
		 * 开发步骤：
		 * 1、根据当前登录用户的ID获取对应的配置信息  一维数组
		 * 2、利用传送过来的二维数组数据组装数据  (循环)
		 * 3、组装好的数据写入数据库
		 * 4、组装好的数据加密
		 * 5、上传订单；
		 */
		//用户数据参数一维数组
		//$userInfo = $this->admin;
		//1、查询配置报关信息等  报关信息：customs  检验检疫信息：inspection  支付配置：payconfig
		/*$info = $this->db->table('foll_cross_border')->field('payconfig')->where(['uid'=>$userInfo['id']])->item();
		if(empty($info['payconfig'])) {
			return $res = ['code'=>0,'msg'=>'请先填写报关信息,或检验检疫信息或支付配置信息!!!'];
		}
		//支付配置
		$payconfig = json_decode($info['payconfig'],true);*/
		file_put_contents("./paylog/Helpay/getData1.txt",print_r($dataArr,true),FILE_APPEND);
		if(empty($dataArr)) {
			return $msg = ['code'=>0,'msg'=>'Data is Null'];
		}
		
		$times = strtotime($dataArr['datas']['OrderList']['OrderDate']);
		
		/**
		 * 获取用户身份证号码，姓名！进行认证
		 */
		$userInfos = [
			'phone'         => $dataArr['datas']['OrderList']['OrderDocTel'],//手机号
			//姓名
			'userName'		=>	$dataArr['datas']['OrderList']['OrderDocName'],//姓名
			//身份证号码
			'userId'		=>	$dataArr['datas']['OrderList']['OrderDocId'],
		];
		
		/**
		 * 获取用户信息查询foll_payemnt_userinfo 是否有数据
		 * 有数据，不再提交验证；没有数据提交验证
		 * 
		 * 判断身份是否通过：
		 * 在判断状态是否等于0000	  验证过直接走；
		 * 判断状态不等于 0000  直接返回身份信息错误
		 * 查询该用户信息是否认证
		 * 1,已验证，或认证通过；提交信息
		 * 2、认证失败，停止提交；
		 * 3、没有认证，进行认证；通过认证提交数据
		 */
		$flag = false;
		$info = $this->db->table('foll_payment_userinfo')->where(['userId'=>$userInfos['userId'],'resultCode'=>'0000','resultMsg'=>'信息一致，认证成功'])->order(' id desc ')->item();
		if(empty($info))
		{
			$flag = true;
		}
		
		if($flag) {
			//用户身份认证
			$auth = $this->AuthName($userInfos);
			if($auth['code'] <=0)
			{
				return $msg = ['code'=>0,'msg'=>'Auth is Error'];
			}
		}
		
		return $msg = ['code'=>0,'msg'=>'Insert Error'];
		
		/*//Auth Card  End
		$money  = sprintf("%2.f",$dataArr['datas']['OrderList']['OrderGoodTotal']);
		$OrderGoodTotal = ($money *100);

		$Type = '';//申报类型 
		switch($dataArr['Head']['FunctionCode']) {
			case 'CUS':// 1：单向海关申报
				$Type = '1';
			break;
			case 'CIQ':// 2: 单向国检申报
				$Type = '2';
			break;
			case 'BOTH':// 3：海关、国检同时申报。
				$Type = '3';
			break;
		}
		
		//报文部分  不需要循环部分
		$Userinfo['version'] 		= $sendArr['version']		=	'1.0';//必填
		//商户号
		$Userinfo['partnerId'] 		= $sendArr['partnerId']		=	$this->partnerId;//必填
			
		//订单提交时间
		$Userinfo['submitTime'] 	= $sendArr['submitTime']	=	date('YmdHis',strtotime($dataArr['datas']['OrderHead']['DeclTime']));//必填
		//$Userinfo['submitTime'] 	= $sendArr['submitTime']	=	date('YmdHis',$times);//必填
			
		/**
		* 业务信息
		 */
		//电子口岸代码
		/*$Userinfo['eportCode'] 		= $sendArr['eportCode']		=	'06';//必填
		//电商平台备案号      电商平台在电子口岸的备案号
		$Userinfo['eCompanyCode'] 	= $sendArr['eCompanyCode']	=	$dataArr['datas']['OrderHead']['EBPEntNo'];//必填
		//电商平台备案名称  电商平台在电子口岸的备案名称
		$Userinfo['eCompanyName'] 	= $sendArr['eCompanyName']	=	$dataArr['datas']['OrderHead']['EBPEntName'];//必填
			
		//支付币种  支付平台的支付币种(目前固定值：CNY)
		$Userinfo['currCode'] 		= $sendArr['currCode']		=	'CNY';//必填
		//支付时间
		//$Userinfo['payTimeStr'] 	= $sendArr['payTimeStr']	=	date('Y-m-d H:i:s');//选填
		//$Userinfo['payTimeStr'] 	= $sendArr['payTimeStr']	=	date('Y-m-d H:i:s',$times);//选填
		$Userinfo['payTimeStr'] 	= $sendArr['payTimeStr']	=	date('Y-m-d H:i:s',strtotime($dataArr['datas']['OrderHead']['DeclTime']));//选填
			
		//支付人证件类型
		$Userinfo['paperType'] 		= $sendArr['paperType']		=	'01';//必填  身份证
		//支付税款  无默认0
		$Userinfo['payTaxAmount'] 	= $sendArr['payTaxAmount']	=	0;//选填
		//支付运费  没有运费默认为0
		$Userinfo['payFeeAmount'] 	= $sendArr['payFeeAmount']	=	0;//选填
		//支付保费  无默认0
		$Userinfo['payPremiumAmount'] = $sendArr['payPremiumAmount']  =	0;//选填
			//报关回调通知地址  //必填
		$Userinfo['noticeUrl'] 		  = $sendArr['noticeUrl']		=	'http://shop.gogo198.cn/foll/public/?s=notify/submitNotify';

		//申报类型  1：单向海关申报 2: 单向国检申报 3：海关、国检同时申报。默认：1。备注：对于暂时不支持国检的电子口岸，只做海关申报。
		$Userinfo['declareType'] 	  = $sendArr['declareType'] 	= $Type;//必填
		/**
		* 国检信息
		*/
		//进口类型  1：保税进口  2:直邮进口  申报类型为2，3时，必填
		/*$Userinfo['intype'] 		  = $sendArr['intype'] 			= '2';//选填
		//检验检疫支付币种         申报类型为2，3时，必填。参考：5.5
		$Userinfo['ciqCurrency'] 	  = $sendArr['ciqCurrency'] 	= 'CNY';
		//检验检疫电商企业代码  申报类型为2，3时，//选填
		$Userinfo['cbeCode'] 		  = $sendArr['cbeCode'] 		= '1500000260';
		//检验检疫机构代码  	申报类型为2，3时，//选填
		$Userinfo['ciqOrgCode'] 	  = $sendArr['ciqOrgCode'] 		= $dataArr['datas']['OrderHead']['CIQOrgCode'];//'440100',
		//海关关区代码  		申报类型为2，3时，//选填
		$Userinfo['customsCode'] 	  = $sendArr['customsCode'] 	= $dataArr['datas']['OrderHead']['CustomsCode'];//'5100',
		/**
		* 安全信息
		*/
		/*$Userinfo['remark'] 		  = $sendArr['remark']		=	'remark';//扩展字段
		$Userinfo['charset'] 		  = $sendArr['charset']		=	1;//编码方式  必填
		$Userinfo['signType'] 		  = $sendArr['signType']	=	2;//签名类型   必填
		//支付人姓名
		$Userinfo['payerName'] 		  = $sendArr['payerName']  		 =	$dataArr['datas']['OrderList']['OrderDocName'];//必填
		//支付人手机号
		$Userinfo['payerPhoneNumber'] = $sendArr['payerPhoneNumber'] =	$dataArr['datas']['OrderList']['OrderDocTel'];//必填
		//支付人证件号码
		$Userinfo['paperNumber'] 	  = $sendArr['paperNumber']		 =	$dataArr['datas']['OrderList']['OrderDocId'];//必填
        /**
         * 以下数据需要循环添加  赋值   订单详细
         */
        //商户报关订单号  商户提交给支付平台的报关订单号
        /*$Userinfo['orderNo'] 		  =	$sendArr['orderNo'] 		=	$dataArr['datas']['OrderList']['EntOrderNo'];//必填
        //商品名称   订单货品的名称
        $Userinfo['goodsName'] 		  = $sendArr['goodsName'] 		=	$dataArr['datas']['OrderList']['GoodsName'];//必填
        //商品数量  订单货品的数量
        $Userinfo['goodsCount'] 	  = $sendArr['goodsCount']		=	$dataArr['datas']['OrderList']['Qty'];//必填
        //支付金额
        $Userinfo['payAmount'] 		  =	$sendArr['payAmount']  		=	$OrderGoodTotal;//必填  分为单位  * 100
        //支付货款
        $Userinfo['payGoodsAmount']   = $sendArr['payGoodsAmount']  =	$OrderGoodTotal;//选填 分为单位  * 100
        //外部订单号
        $Userinfo['orderId'] 	   	  = $sendArr['orderId']		    =   date('YmdHis',time()).mt_rand(1111,9999);//必填

        //数据拼接
        $signStr 				= 	$this->Splicing($sendArr);
        //数据加密
        $signMsg 				= 	$signStr."&pkey=".$this->pkey;
        $Userinfo['signMsg'] 	= 	$sendArr['signMsg'] = md5($signMsg);
        //发送报文信息
        file_put_contents("./paylog/Helpay/submitArr1.txt",json_encode($Userinfo)."\r\n",FILE_APPEND);
        //测试地址
		//$url = 'http://140.143.36.105:8085/webgate/customsClearance.htm';
        $url = 'https://www.cfmtec.com/webgate/customsClearance.htm';
		$Userinfo['uid'] = $this->admin['id'];//当前操作用户ID
        
       	//数据提交
        $xml = $this->postData($url,$sendArr);
       	//解析XML数据变成对象
        $result =$this->xmlToArray($xml);
        file_put_contents("./paylog/Helpay/submitRessTs.txt",json_encode($result)."\r\n",FILE_APPEND);
        //请求成功写入数据库
        if($result['resultCode'] == '0000' && $result['resultMsg'] == '请求处理成功'  && $result['chkMark'] == '1') {
        	//数据写入ims_foll_payment_order表内  报关订单表
        	$ins = $this->db->table('foll_payment_order')->insert($Userinfo);
        	return $msg = ['code'=>1,'msg'=>$result];
        } else {
        	return $msg = ['code'=>0,'msg'=>'Insert Error'];
        }*/
        
        
	}
	
	
	
	/**
	 * 报关订单提交   正式用
	 * 2018-06-10
	 */
	public function Ordersubmits2($dataArr = null)
	{
		/**
		 * 开发步骤：
		 * 1、根据当前登录用户的ID获取对应的配置信息  一维数组
		 * 2、利用传送过来的二维数组数据组装数据  (循环)
		 * 3、组装好的数据写入数据库
		 * 4、组装好的数据加密
		 * 5、上传订单；
		 */
		//用户数据参数一维数组
		//$userInfo = $this->admin;
		//1、查询配置报关信息等  报关信息：customs  检验检疫信息：inspection  支付配置：payconfig
		/*$info = $this->db->table('foll_cross_border')->field('payconfig')->where(['uid'=>$userInfo['id']])->item();
		if(empty($info['payconfig'])) {
			return $res = ['code'=>0,'msg'=>'请先填写报关信息,或检验检疫信息或支付配置信息!!!'];
		}
		//支付配置
		$payconfig = json_decode($info['payconfig'],true);*/
		file_put_contents("./paylog/Helpay/getData1.txt",print_r($dataArr,true),FILE_APPEND);
		if(empty($dataArr)) {
			return $msg = ['code'=>0,'msg'=>'Data is Null'];
		}
		
		//订单时间
		$times = strtotime($dataArr['datas']['OrderList']['OrderDate']);
		
		/**
		 * 获取用户身份证号码，姓名！进行认证
		 */
		$userInfos = [
			'phone'         => $dataArr['datas']['OrderList']['OrderDocTel'],//手机号
			//姓名
			'userName'		=>	$dataArr['datas']['OrderList']['OrderDocName'],//姓名
			//身份证号码
			'userId'		=>	$dataArr['datas']['OrderList']['OrderDocId'],
		];
		
		/**
		 * 获取用户信息查询foll_payemnt_userinfo 是否有数据
		 * 有数据，不再提交验证；没有数据提交验证
		 * 
		 * 判断身份是否通过：
		 * 在判断状态是否等于0000	  验证过直接走；
		 * 判断状态不等于 0000  直接返回身份信息错误
		 */
		
		/**
		 * 查询该用户信息是否认证
		 * 1,已验证，或认证通过；提交信息
		 * 2、认证失败，停止提交；
		 * 3、没有认证，进行认证；通过认证提交数据
		 */
		$where = [
			'userName'=>$userInfos['userName'],
			'phone'	  =>$userInfos['phone'],
			'userId'  =>$userInfos['userId'],
		];
		$UserInfo = $this->db->table('foll_payment_userinfo')->where($where)->order(' id desc ')->item();
		if(empty($UserInfo)){//没有数据
			
			$auth = $this->AuthName($userInfos);
			if(!$auth['code'])
			{
				return $msg = ['code'=>0,'msg'=>'Auth is Error'];
			}
			
		} else {//有数据
			
			//验证的数据有问题
			if(isset($UserInfo['resultCode']) && ($UserInfo['resultCode'] != '0000')) {
				//直接返回
				return $msg = ['code'=>0,'msg'=>'Auth is Error'];
			}
			
		}
		
		

		$Type = '';//申报类型 
		switch($dataArr['Head']['FunctionCode']) {
			case 'CUS':// 1：单向海关申报
				$Type = '1';
			break;
			case 'CIQ':// 2: 单向国检申报
				$Type = '2';
			break;
			case 'BOTH':// 3：海关、国检同时申报。
				$Type = '3';
			break;
		}
		
		//报文部分  不需要循环部分
		$Userinfo['version'] 		= $sendArr['version']		=	'1.0';//必填
		//商户号
		$Userinfo['partnerId'] 		= $sendArr['partnerId']		=	$this->partnerId;//必填
			
		//订单提交时间
		//$Userinfo['submitTime'] 	= $sendArr['submitTime']	=	date('YmdHis',strtotime($dataArr['datas']['OrderHead']['DeclTime']));//必填
		$Userinfo['submitTime'] 	= $sendArr['submitTime']	=	date('YmdHis',$times);//必填
			
		/**
		* 业务信息
		 */
		//电子口岸代码
		$Userinfo['eportCode'] 		= $sendArr['eportCode']		=	'06';//必填
		//电商平台备案号      电商平台在电子口岸的备案号
		$Userinfo['eCompanyCode'] 	= $sendArr['eCompanyCode']	=	$dataArr['datas']['OrderHead']['EBPEntNo'];//必填
		//电商平台备案名称  电商平台在电子口岸的备案名称
		$Userinfo['eCompanyName'] 	= $sendArr['eCompanyName']	=	$dataArr['datas']['OrderHead']['EBPEntName'];//必填
			
		//支付币种  支付平台的支付币种(目前固定值：CNY)
		$Userinfo['currCode'] 		= $sendArr['currCode']		=	'CNY';//必填
		//支付时间
		//$Userinfo['payTimeStr'] 	= $sendArr['payTimeStr']	=	date('Y-m-d H:i:s');//选填
		$Userinfo['payTimeStr'] 	= $sendArr['payTimeStr']	=	date('Y-m-d H:i:s',$times);//选填
		//$Userinfo['payTimeStr'] 	= $sendArr['payTimeStr']	=	date('Y-m-d H:i:s',strtotime($dataArr['datas']['OrderHead']['DeclTime']));//选填
			
		//支付人证件类型
		$Userinfo['paperType'] 		= $sendArr['paperType']		=	'01';//必填  身份证
		//支付税款  无默认0
		$Userinfo['payTaxAmount'] 	= $sendArr['payTaxAmount']	=	0;//选填
		//支付运费  没有运费默认为0
		$Userinfo['payFeeAmount'] 	= $sendArr['payFeeAmount']	=	0;//选填
		//支付保费  无默认0
		$Userinfo['payPremiumAmount'] = $sendArr['payPremiumAmount']  =	0;//选填
			//报关回调通知地址  //必填
		$Userinfo['noticeUrl'] 		  = $sendArr['noticeUrl']		=	'http://shop.gogo198.cn/foll/public/?s=notify/submitNotify';

		//申报类型  1：单向海关申报 2: 单向国检申报 3：海关、国检同时申报。默认：1。备注：对于暂时不支持国检的电子口岸，只做海关申报。
		$Userinfo['declareType'] 	  = $sendArr['declareType'] 	= $Type;//必填
		/**
		* 国检信息
		*/
		//进口类型  1：保税进口  2:直邮进口  申报类型为2，3时，必填
		$Userinfo['intype'] 		  = $sendArr['intype'] 			= '2';//选填
		//检验检疫支付币种         申报类型为2，3时，必填。参考：5.5
		$Userinfo['ciqCurrency'] 	  = $sendArr['ciqCurrency'] 	= 'CNY';
		//检验检疫电商企业代码  申报类型为2，3时，//选填
		$Userinfo['cbeCode'] 		  = $sendArr['cbeCode'] 		= '1500000260';
		//检验检疫机构代码  	申报类型为2，3时，//选填
		$Userinfo['ciqOrgCode'] 	  = $sendArr['ciqOrgCode'] 		= $dataArr['datas']['OrderHead']['CIQOrgCode'];//'440100',
		//海关关区代码  		申报类型为2，3时，//选填
		$Userinfo['customsCode'] 	  = $sendArr['customsCode'] 	= $dataArr['datas']['OrderHead']['CustomsCode'];//'5100',
		/**
		* 安全信息
		*/
		$Userinfo['remark'] 		  = $sendArr['remark']		=	'remark';//扩展字段
		$Userinfo['charset'] 		  = $sendArr['charset']		=	1;//编码方式  必填
		$Userinfo['signType'] 		  = $sendArr['signType']	=	2;//签名类型   必填
		//支付人姓名
		$Userinfo['payerName'] 		  = $sendArr['payerName']  		 =	$dataArr['datas']['OrderList']['OrderDocName'];//必填
		//支付人手机号
		$Userinfo['payerPhoneNumber'] = $sendArr['payerPhoneNumber'] =	$dataArr['datas']['OrderList']['OrderDocTel'];//必填
		//支付人证件号码
		$Userinfo['paperNumber'] 	  = $sendArr['paperNumber']		 =	$dataArr['datas']['OrderList']['OrderDocId'];//必填
        /**
         * 以下数据需要循环添加  赋值   订单详细
         */
        //商户报关订单号  商户提交给支付平台的报关订单号
        $Userinfo['orderNo'] 		  =	$sendArr['orderNo'] 		=	$dataArr['datas']['OrderList']['EntOrderNo'];//必填
        //商品名称   订单货品的名称
        $Userinfo['goodsName'] 		  = $sendArr['goodsName'] 		=	$dataArr['datas']['OrderList']['GoodsName'];//必填
        //商品数量  订单货品的数量
        $Userinfo['goodsCount'] 	  = $sendArr['goodsCount']		=	$dataArr['datas']['OrderList']['Qty'];//必填
        //支付金额
        $Userinfo['payAmount'] 		  =	$sendArr['payAmount']  		=	($dataArr['datas']['OrderList']['OrderGoodTotal'] * 100);//必填  分为单位  * 100
        //支付货款
        $Userinfo['payGoodsAmount']   = $sendArr['payGoodsAmount']  =	($dataArr['datas']['OrderList']['OrderGoodTotal'] * 100);//选填 分为单位  * 100
        //外部订单号
        $Userinfo['orderId'] 	   	  = $sendArr['orderId']		    =   date('YmdHis',time()).mt_rand(1111,9999);//必填

        //数据拼接
        $signStr 				= 	$this->Splicing($sendArr);
        //数据加密
        $signMsg 				= 	$signStr."&pkey=".$this->pkey;
        $Userinfo['signMsg'] 	= 	$sendArr['signMsg'] = md5($signMsg);
        //发送报文信息
        file_put_contents("./paylog/Helpay/submitArr.txt",json_encode($Userinfo),FILE_APPEND);
        //测试地址
		//$url = 'http://140.143.36.105:8085/webgate/customsClearance.htm';
        $url = 'https://www.cfmtec.com/webgate/customsClearance.htm';
		$Userinfo['uid'] = $this->admin['id'];//当前操作用户ID
        
       	//数据提交
        $xml = $this->postData($url,$sendArr);
       	//解析XML数据变成对象
        $result =$this->xmlToArray($xml);
        file_put_contents("./paylog/Helpay/submitRessTs.txt",json_encode($result)."\r\n",FILE_APPEND);
        //请求成功写入数据库
        if($result['resultCode'] == '0000' && $result['resultMsg'] == '请求处理成功'  && $result['chkMark'] == '1') {
        	//数据写入ims_foll_payment_order表内  报关订单表
        	$ins = $this->db->table('foll_payment_order')->insert($Userinfo);
        	return $msg = ['code'=>1,'msg'=>$result];
        } else {
        	return $msg = ['code'=>0,'msg'=>'Insert Error'];
        }
        
	}
	
	

	/**
	 * 以下是工具方法：==================
	 */
	//数据拼接
	public function Splicing(&$val)
	{
		$str = '';
		ksort($val);
		if(is_array($val))
		{
			foreach($val as $k=>$v)
			{
				if($v !== null && trim($v) !== '' && $k !== 'signMsg')
				{
					$str.= $k.'='.$v.'&';
				}
			}
		}
		return substr($str,0,-1);
	}
	
	//XML数据转数组
	public function xmlToArray($xml)
	{
		libxml_disable_entity_loader(true);
		$xmlstring = simplexml_load_string($xml,'SimpleXMLElement',LIBXML_NOCDATA);
		$val = json_decode(json_encode($xmlstring),true);
		return $val;
	}
	
	/**
	 * 将数组转换为xml
	 * @param array $data    要转换的数组
	 * @param bool $root     是否要根节点
	 * @return string         xml字符串
	 * @author Json
	 */
	public function ArrToXmls($data,$root=true)
	{
		$str = '';
		if($root) $str .= "<xml>";
		foreach($data as $key=>$val)
		{
			if(is_array($val))
			{
				$child = $this->ArrToXmls($val,false);
				$str .= "<$key>$child</$key>";
			} else {
				$str .= "<$key>[$val]</$key>";
			}
		}
		if($root) $str .= "</xml>";
		return $str;
	}
	
	//curl post 请求
	public function submitPost($url,$param)
	{
		try{
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $param);
			$result = curl_exec($ch);
			curl_close($ch);
//			return $result;//返回结果
			echo $result;
		}catch(Exception $e){
			file_put_contents('./paylog/MyError.txt', print_r($e,TRUE),FILE_APPEND);
		}
	}
	
	//post 请求
    public function doPost($url,$post_data)
    {
    	$ch = curl_init();
        curl_setopt($ch,CURLOPT_URL,$url);
        // 执行后不直接打印出来
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
        // 设置请求方式为post
        curl_setopt($ch,CURLOPT_POST,true);
        // post的变量
        curl_setopt($ch,CURLOPT_POSTFIELDS,$post_data);
        $output = curl_exec($ch);
        curl_close($ch);
        return $output;
    }
    
    //文件上传 2018-05-28
    /**
     * $url  请求地址
     * $file  需上传文件路径与文件名称
     * $fileName  文件名称
     * $post_dat  上传参数
     */
    public function upload($url,$file,$fileName,$post_data)
    {
    	$obj = new CurlFile($file);
    	$obj->setMImeType('txt');//设置后缀
    	$obj->setPostFilename($fileName);//设置文件名
    	$post_data['fileObj'] = $obj;
    	$ch = curl_init();
    	curl_setopt($ch,CURLOPT_URL,$url);
    	curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
    	curl_setopt($ch,CURLOPT_POST,1);
    	curl_setopt($ch,CURLOPT_POSTFIELDS,$post_data);
    	$output = curl_exec($ch);
    	curl_close($ch);
    	return $output;
    }
    
    
    //文件上传 2018-06-05
    /**
     * $url  请求地址
     * $post_dat  上传参数
     */
    public function postData($url,$post_data)
    {
    	$ch = curl_init();
    	curl_setopt($ch,CURLOPT_URL,$url);
    	curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
    	curl_setopt($ch,CURLOPT_POST,1);
    	curl_setopt($ch,CURLOPT_POSTFIELDS,$post_data);
    	$output = curl_exec($ch);
    	curl_close($ch);
    	return $output;
    }
    
    
    //发送文件
    /** php 发送流文件
	* @param  String  $url  接收的路径
	* @param  String  $file 要发送的文件
	* @return boolean
	*/
    public function sendStreamFile($url,$file)
    {
    	if(file_exists($file))
    	{
    		$opts = [
    			'http'=>[
    				'method'=>'POST',
    				'header'=>'content-type:application/x-www-form-urlencoded',
    				'content'=>file_get_contents($file)
    			],
    		];
    		$context = stream_context_create($opts);
    		$res = fopen($url,'rb',false,$context);
    		$response = file_get_contents($url,false,$context);
    		$ret = json_decode($response,true);
			return $res;
    	} else {
    		return false;
    	}
    }
    
}
?>