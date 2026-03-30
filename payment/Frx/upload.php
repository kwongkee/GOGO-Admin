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
	$_POST['time'] = date('Y-m-d H:i:s',time());
	file_put_contents('./log/upload.txt', print_r($_POST,TRUE));
	
	$Token 		 = trim($_POST['Token']);//请求类型
	$downloaDate = trim($_POST['loaDate']);//下载对账日期
	
	$Check['plateNumber'] = trim($_GPC['CarNo']);//车牌号
	$Check['openid']	  = trim($_GPC['openid']);//用户openid
	$Check['inType']	  = trim($_GPC['inType']);//入场方式
	
	
	//停车，扣费，解密；查询，下载对账;
	$type = ['loadBill','Sign'];
//	if($Token == 'loadBill' )//检测Token;
	if(in_array($Token,$type))//检测Token;
	{
		$frx = Frx::getInstance();//实例化；
		//配置信息
		$config = [
			'mchid'		=>	'000201507100239351',
			'openkey'	=>	'da7c3ab0a510b873dd4159d1823460a9',
		];
		
		switch($Token){
			
			case 'loadBill':
				$res = $frx->downloadBill($downloaDate,$config);				
				$day = date('Ymd',$downloaDate);
				$path = "/www/web/default/crontab/wx/loadBill{$day}.txt";
				//写入文件  保存
				file_put_contents($path,$res['data'],FILE_APPEND);
				if($res['retCode'] == 'SUCCESS') {
					echo json_encode(['status'=>1,'msg'=>$res['msg'],'data'=>$res['data']]);die;
				} else {
					echo json_encode(['status'=>0,'msg'=>$res['msg'],'data'=>$res['data'],'ds'=>$res]);die;
				}
			break;
			
			case 'Sign':
				$res = $frx->CheckCarNoSign($Check,$Check['inType'],$config);
				//return $res;
				echo json_encode(['status'=>1,'msg'=>$res['msg'],'data'=>$res['data']]);
			break;
		}
		
	} else {
		echo json_encode(['status'=>0,'msg'=>'No In Token']);die;
	}
	
} else {
	echo json_encode(['status'=>0,'msg'=>'Token is Null']);die;
}


/**
 * 丰瑞祥 微信代扣接口
 */
class Frx {
	//保存类的实例的静态成员变量
	static private $_instance = null;
	//public $ip = 'http://114.242.25.239:8200/';//114.242.25.239:8101
	//public $ip = 'http://jiekou.xiangfubao.com.cn/';
	public $ip = 'http://download.xiangfubao.com.cn/';
	//download.xiangfubao.com.cn/downloadBill
	//static private $mchid ='003020051110012';
	//static private $key = 'ZROXL6DLtFBHEsIJuutl*%dByyTT@EnL';

	//static private $mchid = null;
	//static private $key = null;
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
		self::$key   = $key;
	}
	
	/**
	 * 2018-04-25
	 * 喜柏停车：appid: wx01af4897eca4527e
	 * 用户入场通知接口
	 * 一
	 */
	public function in_parking($inData = null , $inType = 'PARKING',$config) {
		
		if($inType == 'PARKING') {
			
			$inParking = [
				//1.PARKING:停场停车场景，2.PARKING SPACE 车位停车场景；
				'tradeScene'	=> $inType,
				//进入时间：yyyyMMddHHmmss  TRUE
				'startTime'		=> date('YmdHis',$inData['starttime']),//'20180426151211',
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
		//数据加密
		$signs 					= $this->sign($sendData,$config['openkey']);
		//A-z排序后sha1加密，后md5加密；
		$sendData['sign'] 		= $signs;
		//请求地址
		$url 					= $this->ip .'parking/doParking/doNotifyication';
		$res 					= $this->post_json($url,$sendData,true);
		if($res['retCode'] == 'SUCCESS') {
			//数据解密；
			$decrypt = $this->AESDecryptResponse($config['openkey'],$res['data']);
			//unset($res['data']);//删除data
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
	 * 查询车主签约状态
	 */
	public function CheckCarNoSign($inData = null , $inType = 'PARKING',$config) {
		
		if($inType == 'PARKING') {
			
			$inParking = [
				//1.PARKING:停场停车场景，2.PARKING SPACE 车位停车场景；
				'tradeScene'	=> $inType,
				//进入时间：yyyyMMddHHmmss  TRUE
//				'startTime'		=> date('YmdHis',$inData['starttime']),//'20180426151211',
				//车牌号。仅包括省份+车牌，不包括特殊字符
				'plateNumber'	=> $inData['plateNumber'],//'粤YGB098',
				'openid'		=> $inData['openid'],
			];
			
		} else if($inType == 'PARKING SPACE') {
			
			$inParking = [
				//1.PARKING:停场停车场景，2.PARKING SPACE 车位停车场景；
				'tradeScene'	=> $inType,
				//进入时间：yyyyMMddHHmmss  TRUE
//				'startTime'		=> date('YmdHis',$inData['starttime']),//'20180426151211',
				//车牌号。仅包括省份+车牌，不包括特殊字符
				'plateNumber'	=> $inData['plateNumber'],//'粤YGB098',
				'openid'		=> $inData['openid'],
			];
		}
		
		//数据转json  后进行 AES 加密 再 进行2进制转16进制；
		$strData = json_encode($inParking);
		$datas = $this->AESEncryptRequest($config['openkey'],$strData);
		
		$sendData['merchantNo'] = $config['mchid'];//商户号
		$sendData['data'] 		= $datas;//数据
		//数据加密
		$signs 					= $this->sign($sendData,$config['openkey']);
		//A-z排序后sha1加密，后md5加密；
		$sendData['sign'] 		= $signs;
		//请求地址
//		$url 					= $this->ip .'parking/doParking/getUsrStat';
		$url 					= 'http://jiekou.xiangfubao.com.cn/common/doQuery';
		$res 					= $this->post_json($url,$sendData,true);
		if($res['retCode'] == 'SUCCESS') {
			//数据解密；
			$decrypt = $this->AESDecryptResponse($config['openkey'],$res['data']);
			//unset($res['data']);//删除data
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
		
		//折扣计算
		//$m = FeeOk($sendArr);
		
		//折扣计算部分；
		$m = 0;
		$isFree = false;
		$opera = pdo_getall('parking_operate',['uniacid'=>$sendArr['uniacid'],'status' => 2],['discount','startDate','endDate']);
		if(!empty($opera) && ($sendArr['total'] > 0))//1
		{
			$m = $sendArr['total'];//折扣率后的金额
			$j = '0.';//折扣率 例如：0.5
			$times = time();//当前时间搓
			foreach($opera as $key=>$val) {//2
				if(($times >= $val['startDate']) && ($times <= $val['endDate'])) {//3
					
					if($val['discount'] == '0'){
						//标志为true;
						$isFree = true;
						//交易金额等于总金额*0 = 0元;
						$m = 0;
					break;
					} else {
						//查找符合条件的折扣率 转换小数 * 待转换的折扣金额
						$m = ($m * (float)($j=$j.$val['discount']));
						break;
					}
				}
			}
			
			//1  更新表状态；
			pdo_update('foll_order',['pay_account'=>$m,'pay_status'=> 1,'pay_time'=>time(),'pay_type'=>'Fwechat'],['ordersn'=>$sendArr['ordersn']]);
			pdo_update('parking_order', array('status'=>'已结算'), array('ordersn' => $sendArr['ordersn']));
			
			//为真时！折扣为0折  直接修改数据库中字段  发送信息；返回支付状态给咪表
			if($isFree) {
				/**
				 * 开发步骤：
				 * 1、修改表交易金额；
				 * 2、发送消息给用户
				 * 3、发送成功信息给设备
				 */
				//计算停车时长返回数组形式	2
				$T = timediff($sendArr['starttime'],$sendArr['endtime']);
				 
				//sendMassages  配置消息发送数据
				$sendArrMsg = array(
					'body' 		=> $sendArr['body'],//商品描述		a
					'paytime' 	=> date('Y-m-d H:i:s',time()),//消费时间
					'touser' 	=> $sendArr['user_id'],//接收消息的用户	a
					'uniacid' 	=> $sendArr['uniacid'],//公众号ID	a
					'parkTime' 		=> $T['day'].'天'.$T['hour'].'小时'.$T['min'].'分钟',//停车时长		
					'realTime' 		=> $sendArr['duration'],//实计时长		b
					'payableMoney' 	=> sprintf('%.2f',$sendArr['total']),//应付金额	a
					'deducMoney' 	=> sprintf('%.2f',($sendArr['total']- $m)),//抵扣金额	a
					'payMoney' 		=> sprintf('%.2f',$m),//交易金额  实付金额	a
				);
				
				//发送成功数据
				$this->postCredit($sendArr['ordersn']);
				
				//发送模板消息
				$sendArrMsg['first'] = '您好，您的停车服务费扣费成功！';
				$sendArrMsg['remark'] = '欢迎您再次使用智能无感路内停车服务！';
				
				//sendMsgSuccess($sendArrMsg);//支付成功发送消息      2018-07-04 16:06  消息发送失败，重复发送一次！
				$sendmsg['msg'] = $flag = sendMsgSuccess($sendArrMsg);//支付成功发送消息
				/*if($flag !='success'){
					$sendmsg['msg1'] = sendMsgSuccess($sendArrMsg);//支付成功发送消息
				}*/
				
				$sendmsg['time'] = date('Y-m-d H:i:s',time());
				file_put_contents('./log/sendmsg.txt', print_r($sendmsg,TRUE),FILE_APPEND);
				//$results = ['status'=>1,'msg'=>'扣费成功','retCode'=>'SUCCESS'];
				//echo json_encode(['status'=>1,'msg'=>'扣费成功']);
				//return $results;
				return ['retCode'=>'SUCCESS','message'=>'SUCCESS'];
				exit;
			}
		}
		//支付交易金额
		$m = $m > 0 ? (round($m,2)*100):(round($sendArr['pay_account'],2)*100);
		//折扣计算结束
		
		
		//目前支持 ：1. PARKING：车场停车场景 ；2. PARKINGSPACE 车位停车场景；3.GAS 加油场景；4.HIGHWAY 高速场景
		if($parkType == "PARKING" ) {//停车场
			
			$feedata = [
				//'parking',//商品或支付单简要描述	true
				'body'			=>$sendArr['body'],
				//'send12345678900',//商户系统内部的订单号,32个字符内、可包含字母  true
				'outTradeNo'	=>$sendArr['ordersn'],
				//'1',订单总金额，单位为分，只能为整数  true
				'totalFee'		=> $m,//($sendArr['pay_account']*100),
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
				'totalFee'		=> $m,//($sendArr['pay_account']*100),
				//true，停车场景；
				'tradeScene'	=> $parkType,
				//调用微信支付 API 的机器 IP  true
				'spbillCreateIp'=> '120.78.202.118',
				
				//'20180426151211'1123,//true 即用户进入停车时间，格式为 yyyyMMddHHmmss，该值催缴时会向微信用户进行展示
				'startTime'		=> date('YmdHis',$sendArr['starttime']),
				//'20180426151311',//False 即用户出停车场时间，格式为 yyyyMMddHHmmss，该值催缴时会向微信用户进行展示
				'endTime'		=> date('YmdHis',$sendArr['endtime']),
				//true 计费的时间长。单位为秒
				'chargingTime'	=> ($sendArr['duration']*60),
				'carType' 		=> '小型车',
				//'伦教停车',//False 所在停车场的名称
				'parkingName'	=> $sendArr['business_name'],
				//'oR-IB0t4Yc9zmV-K-_5NRB-u5k4U',//'oR-IB0ssN_p-54H9fxuWpUSDUx8w',//用户在商户 appid 下的唯一标识	true
				'openid'		=> $sendArr['user_id'],
				//'100018'//用户停车的车位编号	true
				'spaceNumber'	=> $sendArr['number'],
			];
		}

		file_put_contents('./log/FeeData.txt', print_r($feedata,TRUE),FILE_APPEND);
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
	 * 2018-06-21
	 * 微信车主免密支付退款接口
	 * @param  $params      参 数
	 * @param  $RefundMoney 退款金额
	 * @param  $config    配置信息
	 */
	public function Refund($params,$config,$RefundMoney=0)
	{
		//'orderNum'       => $params['upOrderId'],
		
		$RefundM = $RefundMoney > 0 ? ($RefundMoney*100):($params['pay_account']*100);
		
		/**
		 * 退款订单把数据写入退款订单表中
		 */
		$Refund['account'] = $config['mchid'];
		$Refund['uniacid'] = $params['uniacid'];
		$Refund['openid']  = $params['user_id'];
		$Refund['type']    = 'Fwechat';
		$Refund['ordersn'] = $params['ordersn'];
		$Refund['upOrderId']   = $params['upOrderId'];
		$Refund['refundMoney'] = ($RefundM/100);
		$Refund['payMoney']    = $params['pay_account'];
		$Refund['create_date'] = time();
		//插入数据库
		pdo_insert('parking_refund',$Refund);
		
		$data['streamNo']	     = $params['ordersn'];
		$data['refundStreamNo']  = 'Re'.date('YmdHis',time()).mt_rand(1111,9999).'8'.mt_rand(11111,99999);//开发者退款流水号
		$data['amt']			 = $RefundM;//退款金额 分为单位

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
		$url 	= $this->ip .'common/doRefund';
		//数据请求
		$res 	= $this->post_json($url,$sendData,true);
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
	 * 2018-04-25
	 *  查询订单明细
	 * 三
	 */
	public function doQuery($orderNum = '',$inType = 'streamNo',$config)
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
	 *  $date  时间搓
	 */
	public function downloadBill($date = 0,$config)
	{	
		//如果传有时间过来就用传过来的数据，没有则默认为昨天时间；
		$day  = $date >0 ? (date('Y-m-d',$date)):date('Y-m-d',strtotime("-1 day"));
		$data = [
			'day' => $day,//日期(YYYY-MM-DD)
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
		//file_put_contents('./log/downloadBillData.txt', print_r($sendData,TRUE));
		//请求地址
		//$url = $this->ip .'download/downloadBill';
		$url = $this->ip .'downloadBill';
		//download.xiangfubao.com.cn/downloadBill
		//数据请求
		$res = $this->post_jsont($url,$sendData,true);
		if($res) {
			file_put_contents('./log/downloadBill'.$day.'.txt', print_r($res,TRUE));
			//$res	= json_decode($res,true);
			//返回结果
			return ['retCode'=>'SUCCESS','msg'=>'SUCCESS','data'=>$res,'loadDate'=>$data];
		} else {
			return ['retCode'=>'error','msg'=>'SUCCESS','data'=>$res];
		}
		//写入日志
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
		$str = null;
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
				$datas = json_encode($data);
			}
			curl_setopt($curl,CURLOPT_POST,1);
			curl_setopt($curl,CURLOPT_POSTFIELDS,$datas);
			if($json) {//发送JSON数据；
				curl_setopt($curl,CURLOPT_HEADER,0);
				curl_setopt($curl,CURLOPT_HTTPHEADER,array(
					'Content-Type:text/html;charset=utf-8',
					'Content-Length:'.strlen($datas)
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
		//return $res = json_decode($res,true);
		return $res;
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
		curl_close($ch);
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