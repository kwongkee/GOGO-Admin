<?php
define('IN_MOBILE', true);
define('PDO_DEBUG', true);
require_once '../../framework/bootstrap.inc.php';
require_once '../../app/common/bootstrap.app.inc.php';
load()->app('common');
load()->app('template');
load()->func('diysend');
global $_W;
global $_GPC;

/**
 * 扣费步骤：
 * 1、首先请求入场
 * 2、请求扣费
 * 
 * 1：入场需要参数；
 * 	Token = Parking   只能使用停车场场景
 * 	$inType:停车场景：PARKING SPACE 停车位；PARKING 停车场；
 */

if(!empty($_POST['Token']))
{
	file_put_contents('./log/getDatas.txt', print_r($_POST,TRUE),FILE_APPEND);
	$Token = trim($_POST['Token']);//请求类型；
	$inType = trim($_POST['inType']);//停入类型；停车场：PARKING；停车位：PARKING SPACE;
	$orderNum = trim($_POST['orderSn']);//订单编号
	
	$uniacid = trim($_POST['uniacid']);//公众号ID
	$downloaDate = trim($_POST['loaDate']);//下载对账日期
	
	//停车，扣费，解密；查询，下载对账;
	$type = ['inPark','Fee','Aesdecrypt','Query','loadBill','Test'];
	if(in_array($Token,$type))//检测Token;
	{
		$frx = Frx::getInstance();//实例化；
		//停车订单，车辆进入，收费；
		if($orderNum != '' && ($Token == 'inPark' || $Token == 'Fee' || $Token == 'Query'))
		{
			//  公众号ID		交易金额		描述		入场时间		出时间	 停车时长	车牌号  	车位编号
			$filed = 'a.uniacid,a.user_id,a.ordersn,a.pay_account,a.body,a.business_name,b.starttime,b.endtime,b.duration,b.number,b.CarNo';
			$find = array(':ordersn' => $orderNum, ':pay_status' => 0,':paystatus' => 2);
			
			$inDatas = pdo_fetchall("SELECT ".$filed." FROM ".tablename('foll_order')." a LEFT JOIN ".tablename('parking_order')." b ON a.ordersn = b.ordersn WHERE a.ordersn = :ordersn AND (a.pay_status = :pay_status OR a.pay_status = :paystatus) LIMIT 1",$find);
			$inData = $inDatas['0'];
			//公众号ID
			$uniacids = $inData['uniacid'];
			//查询订单；
//			if($Token == 'Query') {
//				$find = array(':ordersn' => $orderNum,':pay_status' => 1);
//				$inData = pdo_fetch("SELECT ".$filed.	" FROM ".tablename('foll_order')." a LEFT JOIN ".tablename('parking_order')." b ON a.ordersn = b.ordersn WHERE a.ordersn = :ordersn AND a.pay_status = :pay_status LIMIT 1",$find);
//			}

			file_put_contents('./log/inData.txt', print_r($inData,TRUE),FILE_APPEND);
			//数据查询为空；
			if(!empty($inData) && is_array($inData)) {
				//设置公众号ID
//				$uniacidd = $uniacid ? $uniacid : $uniacids;
				//设置数据缓存；
				/*$key = 'Frxs_ID:'.$uniacidd;
				if(!empty($res = cache_load($key))) {//不为空，取缓存数据；
					$config = [
						'mchid'=>$res['mchid'],
						'openkey'=>$res['openkey']
					];
				} else {//如果为空；则添加配置；
					$config = pdo_get('pay_config',['uniacid'=>$uniacidd],['config']);
					if(!$config) {
						//反序列化
						$config = unserialize($config['config']);
						
						$config = [
							'mchid'=>$config['wx']['mchid'],
							'openkey'=>$config['wx']['openkey']
							//'mchid'		=>	'000201507100239351',
							//'openkey'	=>	'da7c3ab0a510b873dd4159d1823460a9',
						];
					}
					cache_write($key,$config);
				}*/
		
			
				//配置信息
				$config = [
					'mchid'		=>	'000201507100239351',
					'openkey'	=>	'da7c3ab0a510b873dd4159d1823460a9',
				];
			
				switch($Token)
				{
					//车辆入场
					case "inPark"://1. PARKING：车场停车场景 ；2. PARKING SPACE 车位停车场景
						$res = $frx->in_parking($inData,$inType,$config);
						file_put_contents('./log/in_parking.txt', print_r($res,TRUE),FILE_APPEND);
						if($res['retCode'] == 'SUCCESS') {
							echo json_encode(['status'=>1,'msg'=>$res['message']]);exit();
						} else {
							echo json_encode(['status'=>0,'msg'=>$res['message']]);exit();
						}
						
					break;
					
					case "Fee"://车辆离场扣费；
						$res = $frx->FeeDeduction($inData,$inType,$config);
						file_put_contents('./log/Fee.txt', print_r($res,TRUE),FILE_APPEND);
						if($res['retCode'] == 'SUCCESS') {
							echo json_encode(['status'=>1,'msg'=>$res['message']]);exit();
						} else {
							echo json_encode(['status'=>0,'msg'=>$res['message']]);exit();
						}
						
					break;
					
					case "Aesdecrypt"://数据解密；
						$str = "960F30189CD2097F723A85C267570BD9EC3B811CA4DBEB68A7098090BBD8B64537269395FFFAC14584BE3A08F568BC5B717B19A5135C79903C76EC85F25408F85129B958222EE8919B34AC6B7D6781262C2839F6DEA1833AD72174509DB992CB5A2BE3D4A00380C2F8D2C14EBB5D1B93D0865F6CD03A328A3AD9FEE02C91AE44A3386BD0339991135A8C724F645C1778E06A95C4BDB092E4F326D5744D3F0301358712DF063E03C171CB1EBBA72234BF";
						$res = $frx->AESDecryptResponse($OPENKEY,$str);
						file_put_contents('./log/Aesdecrypt.txt', print_r(json_decode($res,true),TRUE),FILE_APPEND);
						echo '<pre>';
						print_r($res);
					break;
					
					case "Query"://查询订单   订单号；
						$res = $frx->doQuery($inData['ordersn'],$inType,$config);
						file_put_contents('./log/Query.txt', print_r($res,TRUE),FILE_APPEND);
						if($res['retCode'] == 'SUCCESS') {
							echo json_encode(['status'=>1,'msg'=>$res['message'],'data'=>$res]);
						} else {
							echo json_encode(['status'=>0,'msg'=>$res['message']]);
						}
					break;
					
					case "loadBill"://下载对账单，查询日期；时间不能是当前时间；
						$res = $frx->downloadBill($downloaDate,$config);
						file_put_contents('../../crontab/wx/loadBill'.date('Ymd',$downloaDate).'.txt',$res['date'],FILE_APPEND);
						if($res['retCode'] == 'SUCCESS') {
							echo json_encode(['status'=>1,'msg'=>$res['msg']]); 
							exit();
						} else {
							echo json_encode(['status'=>0,'msg'=>$res['msg']]);
							exit();
						}
					break;
				}
		
			}  else {
				echo json_encode(['status'=>0,'msg'=>'data is null']);
				die;
			}
		
		}  else {
			echo json_encode(['status'=>0,'msg'=>'No type']);
			die;
		}
		
		
	} else {
		echo json_encode(['status'=>0,'msg'=>'No In Token']);
		die;
	}
	
	
} else {
	echo 'Token is Null';
	die;
}



/**
 * 丰瑞祥 微信代扣接口
 */
class Frx {
	//保存类的实例的静态成员变量
	static private $_instance = null;
//	public $ip = 'http://114.242.25.239:8200/';//114.242.25.239:8101
	public $ip = 'http://jiekou.xiangfubao.com.cn/';
//	static private $mchid ='003020051110012';
//	static private $key = 'ZROXL6DLtFBHEsIJuutl*%dByyTT@EnL';

//	static private $mchid = null;
//	static private $key = null;
	static private $iv = '0102030405060708';//偏移量；加解密使用；
	//私有的构造方法
	private function __construct(){}
	
	//用于访问类的实例的公共静态方法
	static public function getInstance() 
	{
		if(!self::$_instance instanceof Frx) {
			//实例化
			self::$_instance = new self;
		}
		return self::$_instance;
	}
	//设置对象属性
	static public function setpro($mchid,$key) {
		self::$mchid = $mchid;
		self::$key = $key;
	}
	
	/**
	 * 2018-04-25
	 * 喜柏停车：appid: wx01af4897eca4527e
	 * 用户入场通知接口
	 * 一
	 */
	public function in_parking(&$inData = null , $inType = 'PARKING',$config) {
		
		if($inType == 'PARKING') {
			
			$inParking = [
				//1.PARKING:停场停车场景，2.PARKING SPACE 车位停车场景；
				'tradeScene'	=>$inType,
				//进入时间：yyyyMMddHHmmss  TRUE
				'startTime'		=>date('YmdHis',$inData['starttime']),//'20180426151211',
				//车牌号。仅包括省份+车牌，不包括特殊字符
				'plateNumber'	=> $inData['CarNo'],//'粤YGB098',
				//停车场名称  false			 
				'parkingName'	=> $inData['business_name'],//'伦教停车',
				//免费时长，单位秒；
				'freeTime'		=>'1',
				//停车车辆的类型，可选值：大型车、小型车
				'carType'		=>'小型车',
			];
			
		} else if($inType == 'PARKING SPACE') {
			
			$inParking = [
				//1.PARKING:停场停车场景，2.PARKING SPACE 车位停车场景；
				'tradeScene'=> $inType,
				//进入时间：yyyyMMddHHmmss  TRUE
				'startTime'=> date('YmdHis',$inData['starttime']),//'20180426151211',
				//车辆类型：大型车，小型车  FALSE
				'carType'=>'小型车',
				//停车场名称  false
				'parkingName'=> $inData['business_name'],//'伦教停车',
				//免费时长，单位秒；
				'freeTime'=>'1',
				//商户 appid 下的唯一标识
				'openid'=> $inData['user_id'],//'oR-IB0t4Yc9zmV-K-_5NRB-u5k4U',//   oR-IB0ssN_p-54H9fxuWpUSDUx8w
				//车位编码
				'spaceNumber'=> $inData['number'],//'100018',
			];
		}
		
		$insertData = [
			'payMoney'	=>	($inData['pay_account']*1),//交易金额
			'ordersn'	=>	$inData['ordersn'],//订单编号
			'payType'	=>	'Fwechat',//微信免密
			'body'		=>	$inData['body'],//商品描述
			'openid'	=>	$inData['user_id'],//用户ID
			'create_time'=>	time(),//创建时间
			'account'	=>	$config['mchid'],//收款商户
			'inType'	=>	$inType
		];
		pdo_insert('pay_old',$insertData);
		
		//数据转json  后进行 AES 加密 再 进行2进制转16进制；
		$strData = json_encode($inParking);
		$datas = $this->AESEncryptRequest($config['openkey'],$strData);
		
		$sendData['merchantNo'] = $config['mchid'];//商户号
		$sendData['data'] 		= $datas;//数据
		//数据加密；
		$signs 					= $this->sign($sendData,$config['openkey']);
		//A-z排序后sha1加密，后md5加密；
		$sendData['sign'] 		= $signs;
		//请求地址
		$url 					= $this->ip .'parking/doParking/doNotifyication';
		$res 					= $this->post_json($url,$sendData,true);
//		$res					= json_decode($res,true);
		if($res['retCode'] == 'SUCCESS') {
			//数据解密；
			$decrypt = $this->AESDecryptResponse($config['openkey'],$res['data']);
			unset($res['data']);//删除data
			//解析 data 返回 返回的解密数据
			$Result = array_merge($res,json_decode($decrypt,true));
			//返回数据；
			return $Result;
		} else {
			return $res;
		}

		
		
		/**
		 * 返回参数
		 * appid	微信支付分配的公众账号 id
		 * mchId	微信支付分配的商户号
		 * userState	NORMAL：正常用户，
		 * 已开通车主服务，发入场通知  BLOCKED:丌符合免密规则用户
		 * OVERDUE: 用户欠费状态，提示用户到微信
		 * openid	用户在商户 appid 下的唯一标识，当用户入驻车主平台时进行返回
		 */
	}
	
	/**
	 * 2018-04-25
	 * 申请扣款接口
	 * 1. PARKINGSPACE 车位停车场景
	 * 二
	 */
	public function FeeDeduction($sendArr = null ,$parkType = 'PARKING SPACE',$config = null ) {
		
		//目前支持 ：1. PARKING：车场停车场景 ；2. PARKINGSPACE 车位停车场景；3.GAS 加油场景；4.HIGHWAY 高速场景
		if($parkType == "PARKING" ) {//停车场
			
			$feedata = [
				//'parking',//商品或支付单简要描述	true
				'body'			=>$sendArr['body'],
				//'send12345678900',//商户系统内部的订单号,32个字符内、可包含字母  true
				'outTradeNo'	=>$sendArr['ordersn'],
				//'1',订单总金额，单位为分，只能为整数  true
				'totalFee'		=>($sendArr['pay_account']*100),
				//true，停车场景；
				'tradeScene'	=> $parkType,
				//调用微信支付 API 的机器 IP  true
				'spbillCreateIp'=>'120.78.202.118',
				
				//'20180426151211',//true 即用户进入停车时间，格式为 yyyyMMddHHmmss，该值催缴时会向微信用户进行展示
				'startTime'		=>date('YmdHis',$sendArr['starttime']),
				//False 即用户出停车场时间，格式为 yyyyMMddHHmmss，该值催缴时会向微信用户进行展示
				'endTime'		=>date('YmdHis',$sendArr['endtime']),//'20180426151311',
				//'3600',//true 计费的时间长。单位为秒
				'chargingTime'	=>($sendArr['duration']*60),
				//'粤YGB098',//车牌号
				'plateNumber'	=>$sendArr['CarNo'],
				//'伦教停车',//所在停车场的名称
				'parkingName'	=>$sendArr['business_name'],
				//False 停车车辆的类型，可选值：大型车、小型车
				'carType'		=>'小型车',
			];
			
		} else if($parkType == "PARKING SPACE"){
			//停车位
			$feedata = [
				//parkingspace 车位停车部分；
				//'parking',//商品或支付单简要描述	true
				'body'			=>$sendArr['body'],
				//'send12345678900',//商户系统内部的订单号,32个字符内、可包含字母  true
				'outTradeNo'	=>$sendArr['ordersn'],
				//'1',订单总金额，单位为分，只能为整数  true
				'totalFee'		=> ($sendArr['pay_account']*100),
				//true，停车场景；
				'tradeScene'	=> $parkType,
				//调用微信支付 API 的机器 IP  true
				'spbillCreateIp'=>'120.78.202.118',
				
				//'20180426151211'1123,//true 即用户进入停车时间，格式为 yyyyMMddHHmmss，该值催缴时会向微信用户进行展示
				'startTime'		=>date('YmdHis',$sendArr['starttime']),
				//'20180426151311',//False 即用户出停车场时间，格式为 yyyyMMddHHmmss，该值催缴时会向微信用户进行展示
				'endTime'		=>date('YmdHis',$sendArr['endtime']),
				//true 计费的时间长。单位为秒
				'chargingTime'	=>($sendArr['duration']*60),
				'carType' 		=> '小型车',
				//'伦教停车',//False 所在停车场的名称
				'parkingName'	=>$sendArr['business_name'],
				//'oR-IB0t4Yc9zmV-K-_5NRB-u5k4U',//'oR-IB0ssN_p-54H9fxuWpUSDUx8w',//用户在商户 appid 下的唯一标识	true
				'openid'		=>$sendArr['user_id'],
				//'100018'//用户停车的车位编号	true
				'spaceNumber'	=>$sendArr['number'],
			];
		}

		file_put_contents('./log/FeeData.txt', print_r($feedata,TRUE),FeeDeduction);
		//加密后的data 数据拼接 open_key
		$strData = json_encode($feedata);
		//数据转json  后进行 AES 加密 再 进行2进制转16进制；
		$datas 					= $this->AESEncryptRequest($config['openkey'],$strData);
		$sendData['merchantNo'] = $config['mchid'];//商户号
		$sendData['data'] 		= $datas;//数据
		//数据加密；
		$signs 					= $this->sign($sendData,$config['openkey']);
		//A-z排序后sha1加密，后md5加密；
		$sendData['sign'] 		= $signs;
		$url 					= $this->ip .'parking/doParking/dopayapply';
		//请求
		$res 					= $this->postJsonFee($url,$sendData);
		if($res['retCode'] == 'SUCCESS') {
			//数据解密；
			$decrypt = $this->AESDecryptResponse($config['openkey'],$res['data']);
			unset($res['data']);//删除data
			//解析 data 返回 返回的解密数据
			$Result = array_merge($res,json_decode($decrypt,true));
			//返回数据；
			return $Result;
		} else {
			return $res;
		}
	}
	
	/**
	 * 2018-04-25
	 *  查询订单明细
	 * 三
	 */
	public function doQuery($orderNum = '',$inType,$config)
	{
		if($inType == 'orderNum') {//支付时，平台返回的流水号
			$data = [
				'orderNum'=> $orderNum,
			];			
		}elseif($inType == 'streamNo'){//开发者流水号，确认同一门店内唯一
			$data = [
				'streamNo'=> $orderNum,
			];
			
		} else {
			echo json_encode(['code'=>0,'msg'=>'inType is Null']);
		}
		
		//数据转json
		$strData = json_encode($data);
		//后进行 AES 加密 再 进行2进制转16进制；
		$datas = $this->AESEncryptRequest($config['openkey'],$strData);
		//商户号
		$sendData['merchantNo'] = $config['mchid'];
		//data数据
		$sendData['data'] = $datas;
		//数据加密；
		$signs = $this->sign($sendData,$config['openkey']);
		//A-z排序后sha1加密，后md5加密；
		$sendData['sign'] = $signs;
		//请求地址；
		$url 	= $this->ip .'common/doQuery';
		//数据请求
		$res 	= $this->post_json($url,$sendData,true);
//		$res	= json_decode($res,true);
		//返回成功
		if($res['retCode'] == 'SUCCESS') {
			//数据解密
			$decrypt = $this->AESDecryptResponse($config['openkey'],$res['data']);
			unset($res['data']);//删除data
			//解析 data 返回 返回的解密数据
			$Result = array_merge($res,json_decode($decrypt,true));
			//返回数据
			return $Result;
		}
		//返回结果
		return $res;
	}
	
	/**
	 *  2018-04-25
	 *  下载商户对账单
	 *  四
	 */
	public function downloadBill($date = '',$config)
	{	
		$date = $date?(date('Y-m-d',$date)):date('Y-m-d',time());
		$data = [
			'day' => $date,//日期(YYYY-MM-DD)
		];
		//数据转json
		$strData = json_encode($data);
		//后进行 AES 加密 再 进行2进制转16进制；
		$datas = $this->AESEncryptRequest($config['openkey'],$strData);
		//商户号
		$sendData['merchantNo'] = $config['mchid'];
		//data数据
		$sendData['data'] = $datas;
		//数据加密；
		$signs = $this->sign($sendData,$config['openkey']);
		//A-z排序后sha1加密，后md5加密；
		$sendData['sign'] = $signs;
		//请求地址
		$url = $this->ip .'download/downloadBill';
		//数据请求
		$res = $this->post_jsont($url,$sendData,true);
//		$res	= json_decode($res,true);
		//返回结果
//		file_put_contents('../../crontab/wx/loadBill'.$date.'.txt',$res,FILE_APPEND);
//		file_put_contents("./log/loadBill".$date.'.txt',$res);
//		if($res){
//			
//		}
		return ['retCode'=>'SUCCESS','msg'=>'SUCCESS','date'=>$res];
//		return $res;
	}
	/**
	 * 返回示例
	 * 416557357653295104,1,200,Fri Feb 23 11:31:10 CST 2018,1,2,
	 * 416557644480774144,5,100,Fri Feb 23 11:31:55 CST 2018,1,1,416557357653295104
	 */
	
	
	/**
	 * 通过AES加密 请求数据
	 * @param $encryptKey  秘钥
	 * @param array $query 加密字串；
	 * @return string; 
	 */
	public function AESEncryptRequest($encryptKey,$query)
	{
		return $this->encrypt_pass($query,$encryptKey);
	}
	
	/**
	 * AES 加密
	 * @param $input 加密字串
	 * @param $key   密码；
	 */
	public function encrypt_pass($input,$key)
	{
		$size = mcrypt_get_block_size(MCRYPT_RIJNDAEL_128,MCRYPT_MODE_ECB);
		$input = $this->pkcs5_pad($input,$size);//计算补码数量；
		
		$td = mcrypt_module_open(MCRYPT_RIJNDAEL_128,'',MCRYPT_MODE_ECB,'');
		mcrypt_generic_init($td,$key,self::$iv);
		$data = mcrypt_generic($td,$input);
		
		mcrypt_generic_deinit($td);
		mcrypt_module_close($td);
		
		$data = bin2hex($data);//2进制转16进制
		return $data;		
	}
	
	/**
	 * 加密填充
	 */
	public function pkcs5_pad($text,$blocksize)
	{
		$pad = $blocksize - (strlen($text) % $blocksize);
		return $text . str_repeat(chr($pad),$pad);
	}
	
	/**
	 * 通过AES 解密 请求数据
	 * @param $encryptKey	秘钥
	 * @param $data	需解密数据
	 * @return string;
	 */
	public function AESDecryptResponse($encryptKey,$data)
	{
		return $this->decrypt_pass($data,$encryptKey);	
	}
	
	//解密
	public function decrypt_pass($sStr,$skey)
	{
//		$iv = '0102030405060708';
		$Str = hex2bin($sStr);
		$decrypted = mcrypt_decrypt(MCRYPT_RIJNDAEL_128,$skey,$Str,MCRYPT_MODE_ECB,self::$iv);
		$dec_s = strlen($decrypted);
		
		$padding = ord($decrypted[$dec_s-1]);
		
		$decrypted = substr($decrypted,0,-$padding);
		
		return $decrypted;
	}
	
	public function pkcs5_unpad($text) 
	{
	    $pad = ord($text{strlen($text)-1}); 
	    if ($pad > strlen($text)) return false; 
	    if (strspn($text, chr($pad), strlen($text) - $pad) != $pad) return false; 
	    return substr($text, 0, -1 * $pad); 
	}
	
	/**
	 * 上传数据部分加密；
	 */
	public function sign($data = null ,$keys)
	{
		ksort($data);
		foreach($data as $key => $val)
		{
			$str .= $key .'='.$val.'&';
		}
		$str = $str .'open_key='.$keys;
		$sign = strtolower(sha1($str));
		$sign = strtolower(md5($sign));
		return 	$sign;
	}
	
	/**
	 * 发送post请求  json 数据；
	 * CURL post 
	 * @param $url: 请求地址
	 * @param $data: 请求数据
	 * @param $json: 是否json 数据请求；
	 */
	public function post_json($url,$data = null,$json=false) 
	{
		$curl = curl_init();
		curl_setopt($curl,CURLOPT_SSL_VERIFYPEER,false);
		curl_setopt($curl,CURLOPT_SSL_VERIFYHOST,false);
		curl_setopt($curl,CURLOPT_HEADER,0); //头文件信息做数据流输出
		curl_setopt($curl,CURLOPT_URL,$url);
		if(!empty($data)) {
			
			if($json && is_array($data)){
				$data = json_encode($data);
			}
			
			curl_setopt($curl,CURLOPT_POST,1);
			curl_setopt($curl,CURLOPT_POSTFIELDS,$data);
			
			if($json) {//发送JSON数据；
				
				curl_setopt($curl,CURLOPT_HEADER,0);
				curl_setopt($curl,CURLOPT_HTTPHEADER,array(
					'Content-Type:text/html;charset=utf-8',
					'Content-Length:'.strlen($data)
				));
			}
		}
		curl_setopt($curl,CURLOPT_RETURNTRANSFER,1);
		$res = curl_exec($curl);
		$errorno = curl_errno($curl);
		if($errorno) {//错误
			return ['errorno'=>false,'errmsg'=>$errorno];
		}
		curl_close($curl);
		return json_decode($res,true);
//		return $res;
	}
	
	
	
	public function post_jsont($url,$data = null,$json=false) 
	{
		$curl = curl_init();
		curl_setopt($curl,CURLOPT_SSL_VERIFYPEER,false);
		curl_setopt($curl,CURLOPT_SSL_VERIFYHOST,false);
		curl_setopt($curl,CURLOPT_HEADER,0); //头文件信息做数据流输出
		curl_setopt($curl,CURLOPT_URL,$url);
		if(!empty($data)) {
			
			if($json && is_array($data)){
				$data = json_encode($data);
			}
			curl_setopt($curl,CURLOPT_POST,1);
			curl_setopt($curl,CURLOPT_POSTFIELDS,$data);
			if($json) {//发送JSON数据；
				curl_setopt($curl,CURLOPT_HEADER,0);
				curl_setopt($curl,CURLOPT_HTTPHEADER,array(
					'Content-Type:text/html;charset=utf-8',
					'Content-Length:'.strlen($data)
				));
			}
		}
		curl_setopt($curl,CURLOPT_RETURNTRANSFER,1);
		$res = curl_exec($curl);
		$errorno = curl_errno($curl);
		if($errorno) {//错误
			return ['errorno'=>false,'errmsg'=>$errorno];
		}
		curl_close($curl);
		return $res = json_decode($res,true);
	}
	
	//请求扣费  2018-06-07
	public function postJsonFee($url,$dataArr = null)
	{
		$data_string = json_encode($dataArr);
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($ch, CURLOPT_POSTFIELDS,$data_string);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
		    'Content-Type:application/json;charset=utf-8',
		    'Content-Length:'.strlen($data_string))
		);
		
		$result = curl_exec($ch);
		curl_close($curl);
		return $res = json_decode($result,true);
	}
	
	//请求成返回数据   给阿新
	public function postCredit($ordersn) {
		$postData = [
			'ordersn'=>$ordersn,
			'type'=>'wp'
		];
		$curl = curl_init();
		curl_setopt_array($curl, array(
		  	CURLOPT_URL => "http://shop.gogo198.cn/foll/public/?s=api/pullOnlinePayStatusApi",
		  	CURLOPT_RETURNTRANSFER => true,
		  	CURLOPT_ENCODING => "",
		  	CURLOPT_MAXREDIRS => 10,
		  	CURLOPT_TIMEOUT => 30,
		  	CURLOPT_CUSTOMREQUEST => "POST",
		  	CURLOPT_POSTFIELDS => json_encode($postData),
		  	CURLOPT_HTTPHEADER => array(
			    "Cache-Control: no-cache",
			    "Content-Type: application/json",
		  	),
		));
		
		$response = curl_exec($curl);
		$err = curl_error($curl);
		curl_close($curl);
	}
}
?>