<?php
namespace app\declares\controller;
use think\Controller;
use Util\data\Sysdb;
use think\Session;
//use think\Sms;
use think\Request;
use think\Loader;
use think\log;
class Payment extends Controller
{
	public $lajp_ip;
	public $lajp_port;
	public $param_type_error;
	public $socket_error;
	public $java_exception;
	public function __construct()
	{
		parent::__construct();
		//实例化数据库
		$this->db = new Sysdb;
		$this->lajp_ip = '127.0.0.1';//java 端IP
		$this->lajp_port = '21230';
		$this->param_type_error = 101;
		$this->socket_error = 102;
		$this->java_exception = 104;
	}
	
	//链接
	public function connect($token,$getData)
	{
//		$type = input('post.token');
		$inArray = [
			'exchangeRateQuery',//汇率查询
			'nativeCashierOrder',//人民币收银台支付
			'exchangeCashierOrder',//外币收银台支付
			'orderQuery',//订单查询API
			'waybillUpdate',//运单更新
			'eportOrder',//报关订单
			'feeQuery',//费率查询
			'waybillAdd',//运单新增
		];
		
		if(!in_array($token,$inArray)){
			exit(json_encode(['code'=>0,'msg'=>'请求类型错误']));
		}
		
		switch($token)
		{
			//汇率查询
			case 'exchangeRateQuery':
				$res = exchangeRateQuery($getData);
				return $res;
			break;
			
			//人民币收银台支付
			case 'nativeCashierOrder':
				$res = nativeCashierOrder($getData);
				return $res;
			break;
			
			//外币收银台支付
			case 'exchangeCashierOrder':
				$res = exchangeCashierOrder($getData);
				return $res;
			break;
			
			//订单查询API
			case 'orderQuery':
				$res = orderQuery($getData);
				return $res;
			break;
			
			//运单更新
			case 'waybillUpdate':
				$res = waybillUpdate($getData);
				return $res;
			break;
			
			//报关订单
			case 'eportOrder':
				$res = eportOrder($getData);
				return $res;
			break;
			
			//费率查询
			case 'feeQuery':
				$res = feeQuery($getData);
				return $res;
			break;
			
			//运单新增
			case 'waybillAdd':
				$res = waybillAdd($getData);
				return $res;
			break;
		}		
	}
	//测试方法  签名方法
	public function test($signType = 2,$sendArr='')
	{
		$sendArr = [
			0=>[
				'version'		=>'1.0',			//版本 
				'partnerId'		=>'10000003761',	//商户ID
				'platformId'	=>'',				//平台商ID
				'orderId'		=>'1526961006073',	//报关流水
				'submitTime'	=>'20180522115006',	//提交时间
				'eportCode'		=>'',				//电子口岸代码
				'orderNo'		=>'1460533120366',	//商户支付订单号
				'eCompanyCode'	=>'686868688',		//商户电子口岸备案号 
				'eCompanyName'	=>'购购网',		//电商平台备案名称
				'payTransactionNo'	=>'',	//交易支付号
				'currCode'			=>'',	//支付币种
				'payAmount'			=>'',	//支付金额
				'payTimeStr'		=>'',	//支付时间
				'payerName'			=>'',	//支付人姓名
				'paperType'			=>'',	//支付证件类型
				'paperNumber'		=>'',	//支付证件号
				'payGoodsAmount'	=>'',	//支付货款
				'payTaxAmount'		=>'',	//支付税款 
				'payFeeAmount'		=>'',	//支付运费
				'bankName'			=>'',	//发卡行
				'bankNo'			=>'',	//银行卡号
				'payAccount'		=>'',	//账户
				'payerPhoneNumber'	=>'',	//支付人手机号
				'noticeUrl'			=>'',	//商户回调地址
				'remark'			=>'扩展字段',//扩展字段
				'charset'			=>1,		//编码方式
				'signType'			=>'2',		//加密方式：1：RSA 方式  2：MD5 方式
			],
			
			/*bankName=b&
			bankNo=c&
			charset=1&
			currCode=2&
			eCompanyCode=686868688&
			eCompanyName=测试商户1&
			eportCode=01&
			noticeUrl=http://localhost/gatewayTest/notify&
			orderNo=1460533120366&
			paperNumber=7&
			paperType=6&
			partnerId=10000003761&
			payAccount=d&
			payAmount=3&
			payFeeAmount=a&
			payGoodsAmount=8&
			payTaxAmount=9&
			payTimeStr=4&
			payTransactionNo=1&
			payerName=5&
			payerPhoneNumber=e&
			
			remark=扩展字段&
			signType=2&
			
			version=1.0*/
			
			1=>[
				'version'=>'1.0',
				'partnerId'=>'10000003761',
				'platformId'=>'10000003abc',
				'orderId'=>'1526902647121',
				'submitTime'=>'20180521193727',
			],
		];
		$signStr = [];
		foreach($sendArr as $key=>$val)
		{
			$signStr[$key] = $this->Splicing($val);//字串拼接
		}
		
//		print_r($signStr);
		
		$signMsg=[];
//		if('2' == $signType)
//		{
			foreach($signStr as $keys=>$vStr)
			{
				// MD5签名，pkey为商户的RSA公钥
				$pkey = "30820122300d06092a864886f70d01010105000382010f003082010a028201010092e81c940f74efb4e5e25f2941619baffdcd4c702f252be78319a561e12b051b3a9d60f6ed813bf816d65fa0e331118fbe6477ad6ebc31b7ff2b50ad370527d59a45de5ecf3e308a6553e1b30373bf6ebaf7c76b7bb6b7839a8bb6f7805f04875ad36baf40b5dd020e492161f3d59bcb6cb87f613711e84b2b983405e9f4b378bf52d6db83c9022311b62b47eda53b5a70f03dd7ed54398d99ffe5ddad6fdae5be0519e30b0a82514ebf9b01af312d4c0b61c656cc308bf0d35da487eba44f126375702a9976bee4c792607d1bdf970ad08b933a34fd5a9016a44f74f2d87cec75d390b1371ece1b96951f77e5842011e926faec5b57ae7c954def8ade950e310203010001";
				$signMsg = $vStr."&pkey=".$pkey;
				$signMsg[$keys] =  md5($signMsg);
			}
//		}
//		if("1" == $signType)
//		{
//			foreach($signStr as $keyd=>$vStr)
//			{
//				// 商户RSA私钥加签 （需先配置并启动好lajp服务的.bat或.sh脚本，具体使用步骤请参照 “`RSA签名方案\php接入简明手册.txt”）
//				$signMsg[$keyd] = $this->lajp_call("com.hnapay.gateway.client.php.ClientSignature::genSignByRSA", $vStr, "UTF8");
//			}
//		}
		
		
		//post 请求数据
		$url = 'http://112.65.105.228:8192/webgate/customsClearance.htm';
		$signStr .= '&signMsg='.$signMsg;
		//请求数据
		$respStr = $this->submitPost($url,$signStr);
		echo '<pre>';
		print_r($respStr);
	}
	
	
	
	//人民币收银台支付 视图  2018-05-23
	public function nativepay()
	{
		return $this->fetch();
	}
	
	//人民币收银台支付 处理数据2018-05-23
	public function nativePost()
	{
		$info = $_POST;
		foreach($info as $key=>$val){
			$infos[$key] = trim($val);
		}
		
		$money = number_format((double)$infos['orderAmount'],2);//表单提交的金额
//		$money = (double)$infos['orderAmount'];//表单提交的金额
		$money1 = (string)($money * 100);//订单提交金额 乘以100  需要提交的金额
		
		//交易金额已分为单位：1*100 = 100 = 1元；
		$infos['orderAmount'] = $money1;//需要提交的金额
		
		//字串拼接
		$signStr = $this->Splicing($infos);
		//数据加密
		$pkey = '30820122300d06092a864886f70d01010105000382010f003082010a0282010100d8559af15dfe44bf2144ded0933a988cd80da94a2710e2a171d3da2ae731d757ce4e36815f7cecafba37f73898c7f7607117035ce2af171229347c31bd76124abe127cb1729da9fa97c84e5f3ee5b06973bf22e1cb1ff544060f96a3191faaadf4935aaa55660b697f4472d8eeca26c3055221dc99cb7e0bf506a5bc7100ec673ca155e6c596a42e28fde3775cde8de2d1edc15c045a6b59a40643d06e4c1fe3620f281c87daac09005ec5d410b6dedae0437beb9f13a11b4bddc2cb466db6fe7f9ec3d134266229deb958d9ef0f46271d505fb67ed83f83987d0ecd3ca0fd6774b4222cbc5d66e58f896a3bd1419e91b32655d2e0ff264697a9de3ca23885ef0203010001';
		$signMsg = $signStr."&pkey=".$pkey;
		$signMsg = md5($signMsg);
		
		$infos['orderAmount'] = $money;//实际金额
		$infos['create_time'] = date('Y-m-d H:i:s',time());//订单创建时间
		
		//数据写入表中foll_payment_nativepay
		$res = $this->db->table('foll_payment_nativepay')->insert($infos);

		if(!$res) {
			echo json_encode(['code'=>0,'msg'=>'签名错误']);
		}
		//返回签名
		echo json_encode(['code'=>1,'msg'=>$signMsg,'money'=>$money1]);
	}
	
	//人民币收银台支付
	public function nativeCashierOrder()
	{
		$sendArr = [
			'version'		=>'1.0',			//版本 
			'orderId'		=>'1526963706919',	//订单号
//			'displayName'	=>'云商跨境',		//下单商户显示名称  N
			'goodsName'		=>"Surface NoteBook 15'",	//商品名称
			'goodsCount'	=>'1',				//商品数量
			'goodsType'		=>'01',				//商品类型
			'submitTime'	=>'20180522123506',	//订单提交时间 
//			'failureTime'	=>'',				//订单失效时间	N
			'customerIp'	=>'120.78.202.118',		//客户下单域名及IP
			'siteId'		=>'www.usolvpay.com',	//网站域名 10000000050 siteId填 www.usolvpay.com
			'orderAmount'	=>'1000',				//订单金额
			'orderCurrencyCode'	=>'CNY',		//订单币种
			/**
			 * 支付类型：0001-担保交易,0002-即时付款,0003-跨境支付,0004-货物贸易,
			 * 0005-酒店住宿,0006-机票旅游,0007-留学教育,0008-国际会展,0009-国际会议,
			 * 0010-物流支付,0011-国际汇款,0012-平台交易
			 */
			'tradeType'			=>'0003',	//交易类型
//			'payerAccount'			=>'18621758498',	//付款方账户号	N
			'payType'			=>'ALL',	//付款方式
//			'orgCode'		=>'',			//目标资金机构代码			N
			'currencyCode'		=>'CNY',		//交易币种
			'settlementCurrencyCode' =>'CNY',	//结算币种  
			'directFlag'		=>'0',			//是否直连0：非直连 （默认）,1：直连
			'borrowingMarked'	=>'0',			//资金来源借贷标识 0：无特殊要求（默认）,1：只借记,2：只贷记
//			'couponFlag'		=>'1',			//优惠券标识1：可用 （默认）,0：不可用		N
			'shareFlag'			=>'0',			//分账标识0：不分账（默认）, 1：分账（仅当交易类型(tradeType)为0004、或0012时可选）
//			'subMerchantOrderDetails'	=>'',	//分账订单信息 当分账标示（shareFlag）为1时，必填	N
//			'platformId'		=>'',			//平台商ID		N
			'returnUrl'			=>'http://shop.gogo198.cn/foll/public/index.php?s=payment/returnUrl',		//商户回调地址
			'noticeUrl'			=>'http://shop.gogo198.cn/foll/public/index.php?s=payment/noticeUrl',		//商户通知地址
			'partnerId'			=>'10000000050',		//商户ID
			'tradeDetailFlag'	=>'1',
			'charset'	=>'1',	//编码方式
			'signType'	=>'2',	//1：RSA 方式  2：MD5 方式
		];
		//测试地址
		$url = 'https://uwebgatetest.hnapay.com/webgate/nativepay.htm';
		//生产地址
		$urls = 'https://uwebgate.hnapay.com/webgate/nativepay.htm';
		
		//字串拼接
		$signStr = $this->Splicing($sendArr);
		//数据加密
		$pkey = '30820122300d06092a864886f70d01010105000382010f003082010a0282010100d8559af15dfe44bf2144ded0933a988cd80da94a2710e2a171d3da2ae731d757ce4e36815f7cecafba37f73898c7f7607117035ce2af171229347c31bd76124abe127cb1729da9fa97c84e5f3ee5b06973bf22e1cb1ff544060f96a3191faaadf4935aaa55660b697f4472d8eeca26c3055221dc99cb7e0bf506a5bc7100ec673ca155e6c596a42e28fde3775cde8de2d1edc15c045a6b59a40643d06e4c1fe3620f281c87daac09005ec5d410b6dedae0437beb9f13a11b4bddc2cb466db6fe7f9ec3d134266229deb958d9ef0f46271d505fb67ed83f83987d0ecd3ca0fd6774b4222cbc5d66e58f896a3bd1419e91b32655d2e0ff264697a9de3ca23885ef0203010001';
		$signMsg = $signStr."&pkey=".$pkey;
		$signMsg =  md5($signMsg);
		$sendArr['signMsg'] = $signMsg;
		ksort($sendArr);
		
		file_put_contents('./paylog/sendArr.txt', print_r($sendArr,TRUE));
		
		file_put_contents('./paylog/sign.txt', print_r($signMsg,TRUE));
		
		$res = $this->submitPost($url,$sendArr);
		
		file_put_contents('./paylog/'.date('Y-m-d').'.txt', print_r($res,TRUE),FILE_APPEND);
		
		echo '<pre>';
		print_r($res);
	}
	
	//支付请求  接收二维数组
	public function eportOrder($getData='')
	{
		/**
		 * 开发步骤：
		 * 1、字串拼接
		 * 2、加密
		 * 3、请求
		 */
		
//		log::write('Mys:我要写入日志信息');
//		echo 'payMent';
	}
	
	//商户回调地址  后台 
	public function noticeUrl()
	{
		file_put_contents('./paylog/returnUrl.txt', print_r($_POST,TRUE),FILE_APPEND);
		$postInfo = $_POST;
		unset($_POST);
		if(!empty($postInfo) && ($postInfo['resultMsg'] == '交易成功') && $postInfo['resultCode'] == '1004') 
		{
//			try{
				//开启事务 startTrans
//				$this->db->startTrans();
					
					$update = [
						'pay_status'=>$postInfo['resultCode'],
						'resultMsg'=>$postInfo['resultMsg'],
						'pay_time'=>$postInfo['completeTime']
					];
					
					$this->db->table('foll_payment_nativepay')->update($update)->where(['orderId'=>$postInfo['orderId']]);
				
				//事务提交 commit()
//				$this->db->commit();
//			}catch(\Exception $e){
				//事务回滚
//				$this->db->rollback();
//			}
		}
		
	}
	
	//商户通知地址 前端
	public function returnUrl() 
	{
		file_put_contents('./paylog/noticeUrl.txt', print_r($_POST,TRUE),FILE_APPEND);
		$info = $_POST;
		unset($_POST);
		if(!empty($info) && $info['resultCode'] == '1004') {
			echo "<h2 style='color:green;'>".$info['resultMsg']."</h2>";
		} else {
			echo "<h2 style='color:red;'>交易失败</h2>";
		}
	}
	
	
	//数据拼接
	public function Splicing(&$val)
	{
		$str = '';
//		foreach($data as $val)
//		{
			ksort($val);
			if(is_array($val))
			{
				foreach($val as $k=>$v)
				{
					if($v !== null && trim($v) !== '' && $v !== 'signMsg')
					{
						$str.= $k.'='.$v.'&';
					}
				}
			}
//		}
		return substr($str,0,-1);
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
	
	//
	public function lajp_call()
	{
		//参数数量
		$args_len = func_num_args();
		//参数数组
		$arg_array = func_get_args();
		
		//参数数量不能小于1
		if($args_len < 1){
			throw new Exception("[LAJP Error] lajp_call function arguments length < 1 ",PARAM_TYPE_ERROR);
		}
		
		//第一个参数是java类,方法名称，必须是string类型
		if(!is_string($arg_array[0])){
			throw new Exception("[LAJP Error] lajp_call function's first argument must be string \"class_name::method_name\".", PARAM_TYPE_ERROR);
		}
		
		if(($socket = socket_create(AF_INET,SOCK_STREAM,0)) === false){
			throw new Exception("[LAJP Error] socket create error.", SOCKET_ERROR);
		}
		
		if(socket_connect($socket,$this->lajp_ip,$this->lajp_port) === false){
			throw new Exception("[LAJP Error] socket connect error.", SOCKET_ERROR);
		}
		
		//消息体序列化
		$request = serialize($arg_array);//数组序列化
		$req_len = strlen($request);//取序列化长度
		
		$send_len = 0;
		
		do{
			//发送
			if(($sends = socket_write($socket,$request,strlen($request))) === false){
				throw new Exception("[LAJP Error] socket write error.",SOCKET_ERROR);
			}
			$send_len += $sends;
			$request = substr($request,$sends);
		}while($send_len < $req_len);
		
		
		//接收
		$response = '';
		while(true){
			$recv = '';
			if(($recv = socket_read($socket,1400)) === false){
				throw new Exception("[LAJP Error] socket read error.", SOCKET_ERROR);
			}
			
			if($recv == ''){
				break;
			}
			
			$response .= $recv;
		}
		
		//关闭
		socket_close($socket);
		
		$rsp_stat = substr($response,0,1);	//返回类型，'S'：成功，'F':异常
		$rsp_msg = substr($response,1);		//返回信息
		
		if($rsp_stat == 'F') {
			//异常信息不用反序列化
			throw new Exception("[LAJP Error] Receive Java exception: ".$rsp_msg, JAVA_EXCEPTION);
		} else {
			if($rsp_msg != 'N')//返回非void
			{
				//反序列
				return unserialize($rsp_msg);
			}
		}
	}
	
}
?>