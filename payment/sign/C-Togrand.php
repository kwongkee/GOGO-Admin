<?php
/**
 * 银联无感信用卡支付
 */
define('IN_MOBILE', true);
define('PDO_DEBUG', true);
define('KEYS','a98ff5ff2c424e13f630800913eaa155');
require_once '../../framework/bootstrap.inc.php';
require_once '../../app/common/bootstrap.app.inc.php';
load()->app('common');
load()->app('template');
load()->func('diysend');
global $_W;
global $_GPC;
/**  FCreditCard
 * 聚合支付：wechat：微信,alipay：支付宝,UnionPay：银联，翼支付：bestpay
 * 免密授权：Fwechat:微信,Falipay：支付宝,FCreditCard：信用卡,FAgro：农商行
 * 
 * 微信：wechat,支付宝：alipay,银联闪付：unionpay，翼支付：bestpay,无感信用卡：park  2018-04-04
 * 
 * 无感支付操作页面；
 */


//获取支付配置参数  根据公众号ID获取
//设置时区
date_default_timezone_set('Asia/Shanghai');
file_put_contents('./logs/getData.txt', print_r($_POST,TRUE),FILE_APPEND);

if(!empty($_POST) && isset($_POST['token'])) {
	//批量过滤POST,GET敏感数据
	
	$type = ['Sign','Surrender','CheckSign','CheckSigns','Parks','FCreditCard','Coupon','Binding','Unbundling','upConfig','checkConfig','Refund','RefundQuery'];//请求类型数组
	if(!in_array($_POST['token'],$type)) {
		exit(json_encode(['code'=>0,'msg'=>'Type is Null']));
	}
	
	switch($_POST['token']) {
		case 'Sign':
			Sign($_POST);//信用卡签约
			break;
		case 'Surrender'://解约银行卡服务
			Surrender($_POST);
			break;
		case 'CheckSign':
			CheckSign($_POST);//查询用户授权
			break;
		case 'CheckSigns'://查询用户授权  外部访问
			CheckSigns($_POST);
			break;
		case 'Parks':
			Parks($_POST);//停车扣费
			break;
		case 'FCreditCard':
			Parks($_POST);//  信用卡 代扣
			break;
		case 'Coupon':
			Coupon($_POST);//优惠券
			break;
		case 'Binding':
			Binding($_POST);//绑定车牌号
		break;
		case 'Unbundling':
			Unbundling($_POST);//解绑车牌
			break;
		case 'upConfig'://序列化配置
			upConfig($_POST);
			break;
		case 'checkConfig'://查看配置信息
			checkConfig();
			break;
		case 'Refund'://退款接口
			Refund($_POST);
			break;
		case 'RefundQuery'://退款查询接口
			RefundQuery($_POST);
			break;
	}
//	create  table ims_parking_authorize
	
}else {
	
	$data['msg'] = 'errord';
	echo json_encode($data);
}

//序列化配置
function upConfig($uniacid) {
	// 序列化数据配置
	$update=[
		'tg' =>[
			'mchid'=>'101570223660',
			'key'=>'b4f16b4526b046c580e363fcfcd07c82',
		],
		'wg' =>[
			'AccessCode' => '7000000000000049',//接入方代码
			'ParkCode' =>'1000000000000007',//商户代码
			'ParkNum' => 'PN00000700000002',//停车场代码
			'key' =>'a98ff5ff2c424e13f630800913eaa155',//秘钥
		],
		'wx' =>[
			'mchid' => '000201507100239351',
			'openkey' =>'da7c3ab0a510b873dd4159d1823460a9'
		],
	];
	// 序列化。
	$str = json_encode($update);
	$datas = [
		'config' =>$str,
	];
	
	echo '<pre>';
	print_r($datas);
	
	
	//返序列化。
//	$config = unserialize($datas['config']);
	
//	$res = pdo_update('pay_config', $datas, array('uniacid' =>$uniacid));
//	if($res) {
//		echo "更新成功！";
//	}else {
//		echo '更新失败！';
//	}

	/**
	 * 开发步骤：
	 * 1、查询表pay_config 中字段config是否有值；条件：公众号ID
	 * 2、把新的配置更新入数据库；
	 * 3、更新成功或失败
	 */
	
	
	/*$configInfo = pdo_get('pay_config',['uniacid'=>$uniacid['uniacid']],['config']);
	
	if($configInfo) {//有数据；
//		echo '<pre>';
		$configData = unserialize($configInfo['config']);
		$configData[$uniacid['name']] = $uniacid['data'];
		//序列化数据；
		$configs = serialize($configData);
		//更新数据
		$res = pdo_update('pay_config',['config'=>$configs],['uniacid'=>$uniacid['uniacid']]);
		if(!$res) {
			exit('更新失败');
		}
		echo '更新成功！';
	} else {
		
		$configData['uniacid'] = $uniacid['uniacid'];
		$str = $config[$uniacid['name']] = serialize($uniacid['data']);
		$configData['config'] = serialize($str);
		
		$res = pdo_insert('pay_config',$configData);
		if(!$res) {
			exit('配置失败');
		}
		echo '配置成功';
	}*/
}

/**
 * 查看配置信息
 */
function checkConfig() {
	$configs = pdo_get('pay_config',array('uniacid'=>14),array('config'));
	$str = unserialize($configs['config']);
	echo '<pre>';
	print_r($str);
}

// 无感签约接口
function Sign($sendArr)
{
	//输入车牌号查询；是否授权
	$check = CheckSign($sendArr);
	//$check = CheckSign($sendArr['CarNo']);

	file_put_contents('./logs/CheckTograndSing.txt', print_r($check,TRUE),FILE_APPEND);
	//查询成功该用户已授权直接修改状态；
	if(is_array($check) && $check['Result']['ResultCode'] == '00') {
		
		$upData = [
			'CardNo' => trim($sendArr['CardNo'],' '),//银行卡号
			'CarNo' => $check['Info']['CarNo'],//车牌号
			'color' => trim($sendArr['Color'],' '),//车牌颜色
			'CustId' => $check['Info']['CustId'],//用户唯一ID
			'auth_status' => '1',//授权状态：0 未授权 1已授权;
			'auth_type' => serialize([
				'wg' => 'FCreditCard',//Credit_Card
			]),
		];
		
		//用户手机号码。
		$where = $check['Info']['Tel'];
		$user_info = pdo_get('parking_authorize', array('mobile' => $where), array('id','openid','mobile'));
		
		//授权成功修改auth_status 状态为1  parking_authorize
		if(is_array($user_info)) {
			//更新用户数据到表中，签约
			pdo_update('parking_authorize', $upData, array('id' => $user_info['id']));
		}
		
		$data['msg'] = 'success';
		//数组转换Json格式！
		echo json_encode($data);
//		exit(json_encode($data));
		/*
		 * 13809703680
		 * 粤YGB098
		 */

	} else {//否则用户没有授权
	
		$Togrant = array(
			'Message' => array(
				'Plain' =>array(
					'TransId'    =>'60301',//业务代码，固定60301
					'AccessCode' => '7000000000000049',//接入方代码，固定不变的配置
					'Tel' 		 => trim($sendArr['Tel'],' '),//手机号码
					'CardNo' 	 => trim($sendArr['CardNo'],' '),//银行卡号
					'UserName' 	 => trim($sendArr['UserName'],' '),//用户姓名
					'CertType' 	 => trim($sendArr['CertType'],' '),//身份证类型 ，1身份证
					'CertNo' 	 => trim($sendArr['CertNo'],' '),//证件号
					'Phone'  	 => trim($sendArr['Phone'],' '),//银行预留手机号，真实预留在银行卡上的手机号。
					'CarNo' 	 => trim($sendArr['CarNo'],' '),//车牌号
					//异步通知URL，签约成功会往改地址发送通知报文
					'NotifyUrl' => 'http://shop.gogo198.cn/payment/sign/sign.php',
					//签约成功回调地址   2018-06-01
//					'ReturnUrl' => urlencode('http://shop.gogo198.cn/app/index.php?i=14&c=entry&m=ewei_shopv2&do=mobile&r=parking.info'),
					//间接跳转上面的地址 2018-06-04
					'ReturnUrl' => 'http://shop.gogo198.cn/payment/sign/notify.php',
				),
				'Signature' => array(
					'SignatureValue' => '1',
				),
			),
		);
		
		/**
		 * 开发步骤：
		 * 1.判断ims_parking_authorize 表中是否存在用户手机号（无不能授权）
		 * 2.组装数据（按手机号更新到表中）
		 * 3、授权成功，状态改为1;
		 */
		$upData = [
			'credit_accout' => trim($sendArr['CardNo'],' '),//信用卡号
//			'credit_account' => trim($sendArr['CardNo'],' '),//信用卡号
			'name'		  	=> $sendArr['UserName'],
			'CarNo' 		=> trim($sendArr['CarNo'],' '),//车牌号
			'color' 		=> trim($sendArr['Color'],' '),//车牌颜色
			'CertNo' 		=> trim($sendArr['CertNo'],' '),//证件号
			'auth_status' 	=> '0',//授权状态：0 未授权 1已授权;
			'auth_type' 	=> serialize([
				'wg'=>'FCreditCard',
			]),
		];
		//查询签约手机
		$user_info = pdo_get('parking_authorize', array('mobile' => trim($sendArr['Tel'],' ')), array('id','openid','mobile'));
		try{
			pdo_begin();//开启事务
			
			//授权成功修改auth_status 状态为1  parking_authorize
			if(!empty($user_info)) {
				//更新用户数据到表中，签约
				pdo_update('parking_authorize', $upData, array('id' => $user_info['id']));
			}
			
			pdo_commit();//提交事务			
		}catch(PDOException $e) {
			pdo_rollback();//执行失败，事务回滚
		}
		
		//$key = 'kBL1dICpPBNxomAR';
		//$key = 'a98ff5ff2c424e13f630800913eaa155';
		$key = KEYS;
		//数组转换XML  头标签Plain 加密部分
		$Tostring = arrayToXmls($Togrant['Message']['Plain']);
		//转换GBK编码   对xml数据拼接.key。
		$byte = charsetToGBK($Tostring.$key);
		// sha1加密返回字节
		$byte = sha1($byte,TRUE);
		//base64 安全的URL编码
		$sign = urlsafe_b64encode($byte);
		//转码后的字符串，赋值给SignatureValue
		$Togrant['Message']['Signature']['SignatureValue'] = $sign;
		//把所有数组转换成 XML报文
		$Togrants = arrayToXml($Togrant);
		//对所有xml报文进行，base64
		$data['msg'] = base64_encode($Togrants);
		file_put_contents('./logs/ToSignArr.txt', print_r($Togrant,TRUE),FILE_APPEND);
		//数组转换Json格式！
		file_put_contents('./logs/Sign.txt', print_r($data,TRUE));
		exit(json_encode($data));
	}
}

//查询是否有授权。传入车牌号
function CheckSign($CarNo) {
	$CarNo = trim($CarNo['CarNo']);
	$Togrant = array(
		'Message' => array(
			'Plain' =>array(
				'TransId' =>'70101',//业务代码，固定60301
				'AccessCode' => '7000000000000049',//接入方代码，固定不变的配置
				'CarNo' => $CarNo,//车牌号码
//				'CarNo' => $CarNo,//车牌号码
			),'Signature' => array(
				'SignatureValue' => '1',
			),
		),
	);
//	$key = 'kBL1dICpPBNxomAR';
//	$key = 'a98ff5ff2c424e13f630800913eaa155';
	$key = KEYS;
	//数组转换XML  头标签Plain 加密部分
	$Plain = charsetToGBK($Togrant['Message']['Plain']);
//	print_r($Plain);die;
	$Tostring = arrayToXmls($Plain);
	//对xml数据拼接.key。  转换GBK编码
	$byte = charsetToGBK($Tostring.$key);
	// sha1加密返回字节
	$byte = sha1($byte,TRUE);
	//base64 安全的URL编码
	$sign = urlsafe_b64encode($byte);	
	//转码后的字符串，赋值给SignatureValue
	$Togrant['Message']['Signature']['SignatureValue'] = $sign;
	//把所有数组转换成 XML报文
	$Togrants = arrayToXml($Togrant);
	//PACKET = XML 发送xml数据
	$data['PACKET'] = $Togrants;
	//请求地址
//	$url = 'http://ilazypay.com:8080/access/park';
	$url = 'http://ilazypay.com/access/park';
	//实例化
	$curlpost = new Curl;
	//发送报文
	$result = $curlpost->post($url,$data);
	//解析XML
	$obj = isimplexml_load_string($result->response, 'SimpleXMLElement', LIBXML_NOCDATA);
	//返回Json格式数据
//	echo json_encode($obj);

	$val = json_decode(json_encode($obj),true);	
	file_put_contents('./logs/CheckSign.txt', print_r($val,TRUE),FILE_APPEND);
	
	return $val['Message']['Plain'];
}



//查询是否有授权。 外用
/**
 * 传入：车牌号码
 */
function CheckSigns($CarNo) {
	
	$Togrant = array(
		'Message' => array(
			'Plain' =>array(
				'TransId' =>'70101',//业务代码，固定60301
				'AccessCode' => '7000000000000049',//接入方代码，固定不变的配置
				'CarNo' => $CarNo['CarNo'],//手机号码
//				'CarNo' => $CarNo,//车牌号码
			),'Signature' => array(
				'SignatureValue' => '1',
			),
		),
	);
//	$key = 'kBL1dICpPBNxomAR';
//	$key = 'a98ff5ff2c424e13f630800913eaa155';
	$key = KEYS;
//数组转换XML  头标签Plain 加密部分
	$Plain = charsetToGBK($Togrant['Message']['Plain']);
//	print_r($Plain);die;
	$Tostring = arrayToXmls($Plain);
	//对xml数据拼接.key。  转换GBK编码
	$byte = charsetToGBK($Tostring.$key);
	// sha1加密返回字节
	$byte = sha1($byte,TRUE);
	//base64 安全的URL编码
	$sign = urlsafe_b64encode($byte);	
	//转码后的字符串，赋值给SignatureValue
	$Togrant['Message']['Signature']['SignatureValue'] = $sign;
	//把所有数组转换成 XML报文
	$Togrants = arrayToXml($Togrant);	
	//PACKET = XML 发送xml数据
	$data['PACKET'] = $Togrants;
	//请求地址
//	$url = 'http://ilazypay.com:8080/access/park';
	$url = 'http://ilazypay.com/access/park';
	//实例化
	$curlpost = new Curl;
	//发送报文
	$result = $curlpost->post($url,$data);
	//解析XML
	$obj = isimplexml_load_string($result->response, 'SimpleXMLElement', LIBXML_NOCDATA);
	//返回Json格式数据
	echo json_encode($obj);
	//格式化json 数据,转换成功数组；
	$val = json_decode(json_encode($obj),true);
	
	$Results = $val['Message']['Plain'];
//	print_r($Results);
	file_put_contents('./logs/CheckSign1.txt', print_r($Results,TRUE),FILE_APPEND);
//	return $Results;
	//授权成功修改auth_status 状态为1  parking_authorize
//	if($Results['Result']['ResultCode']=='00' && $Results['Result']['ResultMsg']=='处理成功') {
		//更新用户数据到表中，签约
//		pdo_update('parking_authorize', array('CarNo'=>$CarNo['CarNo'],'auth_status'=>1), array('mobile' => $CarNo['mobile']));
//		$Msg['msg'] = $Results['Result']['ResultMsg'];
		
//	}else {		
//		$Msg['msg'] = $Results['Result']['ResultMsg'];
//	}
//	print_r($Results);
	return $Results;
}

/**
 * 绑定车牌
 * Tel: 电话号码
 * CarNo:车牌号
 */
function Binding($CarNo) {
	
	$Togrant = array(
		'Message' => array(
			'Plain' =>array(
				'TransId' =>'60501',//业务代码，固定60301
				'AccessCode' => '7000000000000049',//接入方代码，固定不变的配置
//				'AccessCode' => '7000000000000004',//接入方代码，固定不变的配置
				'Tel' => $CarNo['mobile'],//手机号码
				'CarNo' => $CarNo['CarNo'],//车牌号码
				'OperaType' => 1,//操作类型  1:绑定，2：解绑
			),'Signature' => array(
				'SignatureValue' => '1',
			),
		),
	);
	
//	$key = 'kBL1dICpPBNxomAR';
//	$key = 'a98ff5ff2c424e13f630800913eaa155';
	$key = KEYS;
//数组转换XML  头标签Plain 加密部分
	$Plain = charsetToGBK($Togrant['Message']['Plain']);
//	print_r($Plain);die;
	$Tostring = arrayToXmls($Plain);
	//对xml数据拼接.key。  转换GBK编码
	$byte = charsetToGBK($Tostring.$key);
	// sha1加密返回字节
	$byte = sha1($byte,TRUE);
	//base64 安全的URL编码
	$sign = urlsafe_b64encode($byte);
	//转码后的字符串，赋值给SignatureValue
	$Togrant['Message']['Signature']['SignatureValue'] = $sign;
	//把所有数组转换成 XML报文
	$Togrants = arrayToXml($Togrant);	
	//PACKET = XML 发送xml数据
	$data['PACKET'] = $Togrants;
	//请求地址
//	$url = 'http://ilazypay.com:8080/access/park';
	$url = 'http://ilazypay.com/access/park';
	//实例化
	$curlpost = new Curl;
	//发送报文
	$result = $curlpost->post($url,$data);
	//解析XML
	$obj = isimplexml_load_string($result->response, 'SimpleXMLElement', LIBXML_NOCDATA);
	//返回Json格式数据
	$val = json_decode(json_encode($obj),true);
	file_put_contents('./logs/Binding.txt', print_r($val,TRUE),FILE_APPEND);
//	返回处理信息部分。
	$Results = $val['Message']['Plain'];
	//授权成功修改auth_status 状态为1  parking_authorize
	if($Results['Result']['ResultCode']=='00' && $Results['Result']['ResultMsg']=='处理成功') {
		//更新用户数据到表中，签约
		pdo_update('parking_authorize', array('CarNo'=>$CarNo['CarNo'],'auth_status'=>1), array('mobile' => $CarNo['mobile']));
		$Msg['msg'] = $Results['Result']['ResultMsg'];
		
	}else {		
		$Msg['msg'] = $Results['Result']['ResultMsg'];
	}
//	print_r($Msg);
	exit(json_encode($Msg));
}

/**
 * 解绑车牌
 * Tel: 电话号码
 * CarNo:车牌号
 */
function Unbundling($CarNo) {
	
	$Togrant = array(
		'Message' => array(
			'Plain' =>array(
				'TransId' =>'60501',//业务代码，固定60301
				'AccessCode' => '7000000000000049',//接入方代码，固定不变的配置
				'Tel' => $CarNo['mobile'],//手机号码
				'CarNo' => $CarNo['CarNo'],//车牌号码
				'OperaType' => 2,//操作类型  1:绑定，2：解绑
			),'Signature' => array(
				'SignatureValue' => '1',
			),
		),
	);
//	$key = 'kBL1dICpPBNxomAR';
//	$key = 'a98ff5ff2c424e13f630800913eaa155';
	$key = KEYS;
//数组转换XML  头标签Plain 加密部分
	$Plain = charsetToGBK($Togrant['Message']['Plain']);
//	print_r($Plain);die;
	$Tostring = arrayToXmls($Plain);
	//对xml数据拼接.key。  转换GBK编码
	$byte = charsetToGBK($Tostring.$key);
	// sha1加密返回字节
	$byte = sha1($byte,TRUE);
	//base64 安全的URL编码
	$sign = urlsafe_b64encode($byte);	
	//转码后的字符串，赋值给SignatureValue
	$Togrant['Message']['Signature']['SignatureValue'] = $sign;
	//把所有数组转换成 XML报文
	$Togrants = arrayToXml($Togrant);	
	//PACKET = XML 发送xml数据
	$data['PACKET'] = $Togrants;
	//请求地址
//	$url = 'http://ilazypay.com:8080/access/park';
	$url = 'http://ilazypay.com/access/park';
	//实例化
	$curlpost = new Curl;
	//发送报文
	$result = $curlpost->post($url,$data);
	//解析XML
	$obj = isimplexml_load_string($result->response, 'SimpleXMLElement', LIBXML_NOCDATA);
	//返回Json格式数据
	$val = json_decode(json_encode($obj),true);	
	file_put_contents('./logs/Unbundling.txt', print_r($val,TRUE),FILE_APPEND);	
//	return $val['Message']['Plain'];
//	print_r($val['Message']['Plain']);
	
	//	返回处理信息部分。
	$Results = $val['Message']['Plain'];
	//授权成功修改auth_status 状态为1  parking_authorize
	if($Results['Result']['ResultCode']=='00' && $Results['Result']['ResultMsg']=='处理成功') {
		//更新用户数据到表中，签约   
		pdo_update('parking_authorize', array('CarNo'=>$CarNo['CarNo'],'auth_status'=>0), array('mobile' => $CarNo['mobile']));
		$Msg['msg'] = $Results['Result']['ResultMsg'];
		
	}else {		
		$Msg['msg'] = $Results['Result']['ResultMsg'];
	}
//		print_r($Msg);
	exit(json_encode($Msg));
}


//解约银行卡服务
function Surrender($sendArr) {
	//获取用户ID
	$custId = trim($sendArr['CustId'],' ');
	
	$dataArr = array(
		'Message' => array(
			'Plain' =>array(
				'TransId' =>'60401',//业务代码，固定60401
				'AccessCode' => '7000000000000049',//接入方代码，固定不变的配置
				'CustId' => $custId,//用户签约银行卡唯一标识。
			),
			'Signature' => array(
				'SignatureValue' => '1',
			),
		),
	);
	
//	file_put_contents('./logs/Surrender.txt', print_r($dataArr,TRUE),FILE_APPEND);
	
//	$key = 'kBL1dICpPBNxomAR';
//	$key = 'a98ff5ff2c424e13f630800913eaa155';
	$key = KEYS;
	//将字符编码 utf-8转GBK
	$Plain = charsetToGBK($dataArr['Message']['Plain']);
	//数组转换XML  头标签Plain 加密部分
	$Tostring = arrayToXmls($Plain);
	//对xml数据拼接.key。  转换GBK编码
	$byte = charsetToGBK($Tostring.$key);
	// sha1加密返回字节
	$byte = sha1($byte,TRUE);	
	//base64 安全的URL编码
	$sign = urlsafe_b64encode($byte);	
	//转码后的字符串，赋值给SignatureValue
	$dataArr['Message']['Signature']['SignatureValue'] = $sign;
	//把所有数组转换成 XML报文
	$Togrants = arrayToXml($dataArr);
	//PACKET = XML 发送xml数据
	$data['PACKET'] = $Togrants;
	//请求地址
//	$url = 'http://ilazypay.com:8080/access/park';
	$url = 'http://ilazypay.com/access/park';
	//实例化
	$curlpost = new Curl;
	//发送报文
	$result = $curlpost->post($url,$data);
	//解析XML
	$obj = isimplexml_load_string($result->response, 'SimpleXMLElement', LIBXML_NOCDATA);
	
	$json = json_decode(json_encode($obj),TRUE);
	
	//将返回的XML数据写入日志中
	file_put_contents('./logs/Surrenderpacket.txt', print_r($json,TRUE),FILE_APPEND);
//	$json = json_encode($json);
//	echo $json;
	exit(json_encode($json));
}


//停车退款
/**
 * @param ordersn 订单编号
 * @param token Refund
 */
function Refund($parArr) {
	/**
	 * 接收token 与 ordersn 订单编号
	 * SELECT * FROM ims_parking_order a LEFT JOIN ims_foll_order b ON(a.ordersn = b.ordersn) WHERE a.ordersn = $parArr['ordersn'];
	 */
	$filed = 'a.uniacid,a.pay_account,a.ordersn,b.upOrderId,a.pay_time,a.user_id,b.IsWrite';
	$find = array(':ordersn' => $parArr['ordersn']);
	$parms = pdo_fetch("SELECT ".$filed." FROM ".tablename('foll_order')." a LEFT JOIN ".tablename('parking_order')." b ON a.ordersn = b.ordersn WHERE a.ordersn = :ordersn ORDER BY a.id desc LIMIT 1",$find);
	file_put_contents('./logs/RefundData.txt', print_r($parms,TRUE));
	//查询退款表中的ID，条件：订单号一致，IsWrite状态等于1  代表退款成功！
	if($parms['IsWrite'] == 1) {
		exit(json_encode(['code'=>0,'msg'=>'该笔订单已退款，请勿重复操作！']));
	}
	
	//获取支付配置参数  根据公众号ID获取
	$configs = pdo_get('pay_config',array('uniacid'=>$parms['uniacid']),array('config'));
	//反序列获取数组；
//	$config = unserialize($configs['config']);
	$config = json_decode($configs['config'],true);
	//退款金额： 有传入使用传入的，没有使用订单的；
	$RefundMoney = trim($parArr['Money']) ? (round(trim($parArr['Money']),2)*100) : (round($parms['pay_account'],2)*100);
//	exit(json_encode(['code'=>0,'msg'=>$RefundMoney]));
	/*'wg' =>[
//			'AccessCode' => '7000000000000049',//接入方代码
//			'ParkCode' =>'1000000000000007',//商户代码
//			'ParkNum' => 'PN00000700000002',//停车场代码
//			'key' =>'a98ff5ff2c424e13f630800913eaa155',//秘钥
//		],*/
	//组装数组数据
	$dataArr = array(
		'Message' =>array(
			'Plain'=>array(
				'TransId'	=>   '60201',//业务代码：固定：60201
				'ParkCode'=>     $config['wg']['ParkCode'],//商户代码
				'MerDate'	=>	 date('Ymd',$parms['pay_time']),//停车场接入方退款交易日期 商户系统订单日期，如：YYYYMMDD
				'OutTradeNo'=>   $parms['ordersn'],//停车场接入方退款交易流水号
				'OrgTradeNo'=>   $parms['upOrderId'],//原平台订单号
				'Money'=>   	 $RefundMoney,//退款金额
				'Remark'=>  	 '退款',
			),
			'Signature'=>array(
				'SignatureValue' =>' ',
			),			
		),
	);
	
	//查询退款表中是否存在该数据
	$refund = pdo_get('parking_refund',array('ordersn'=>$parms['ordersn']),array('id'));
	if(!$refund)
	{
		$refundArr = [
			'account'    =>		$config['wg']['AccessCode'],//商户ID
			'uniacid'    =>		$parms['uniacid'],//公众号ID
			'openid'     =>		$parms['user_id'],//用户Openid
			'type'       => 	'FCreditCard',//退款接口类型
			'ordersn'    =>		$parms['ordersn'],//订单编号
			'upOrderId'  =>		$parms['upOrderId'],//上游订单号
			'refundMoney'=> 	($RefundMoney/100),//退款金额
			'payMoney'   =>		$parms['pay_account'],//原来订单金额
			'create_date'=>		time(),//退款创建时间 
			'status'	 =>		'99',//退款状态   退款失败：99，成功：100，订单已退款：101，已撤销：102，重复操作：103
		];
		
		/**
		 * 将支付订单写入parking_refund表中
		 */
		$old = pdo_insert('parking_refund', $refundArr);
		$oldid = pdo_insertid();
	}

	
	//秘钥
	$key = $config['wg']['key'];	
	//将字符编码UTF-8转换为GBK编码
	$Plain = charsetToGBK($dataArr['Message']['Plain']);
	//数组转换XML  头标签Plain 加密部分
	$Tostring = arrayToXmls($Plain);
	//对xml数据拼接.key。  转换GBK编码
	$byte = charsetToGBK($Tostring.$key);
	// sha1加密返回字节
	$byte = sha1($byte,TRUE);
	//base64 安全的URL编码
	$sign = urlsafe_b64encode($byte);	
	//转码后的字符串，赋值给SignatureValue
	$dataArr['Message']['Signature']['SignatureValue'] = $sign;
	//把所有数组转换成 XML报文
	$Togrants = arrayToXml($dataArr);
//	file_put_contents('./logs/dataArr.txt', print_r($dataArr,TRUE),FILE_APPEND);
	//PACKET = XML 发送xml数据
	$data['PACKET'] = $Togrants;
	
	//请求地址
	$url = 'http://ilazypay.com/access/park';
	//实例化
	$curlpost = new Curl;
	//发送报文
	$result = $curlpost->post($url,$data);
	//解析XML
	$obj = isimplexml_load_string($result->response, 'SimpleXMLElement', LIBXML_NOCDATA);
//	file_put_contents('./logs/ParkRefund1.txt', print_r($obj),FILE_APPEND);
	//将返回的XML数据写入日志中
//	$str = mb_convert_encoding($obj,'UTF-8','UTF-8,GBK,GB2313,BIG5');
	$val = json_decode(json_encode($obj),true);
	file_put_contents('./logs/ParkRefund.txt', print_r($val,TRUE),FILE_APPEND);
	
	/**
	 * 判断是否处理成功；
	 * 更新订单表字段 ；parking_order  IsWrite退款状态：1退款成功，WriteSeq 退款流水号
	 * 更新：parking_refund  status  up_date   msg  
	 */
	$Result = $val['Message']['Plain']['Result'];//退款状态
	$TradeNo = $val['Message']['Plain']['TradeNo'];//退款流水号
	$OutTradeNo = $val['Message']['Plain']['OutTradeNo'];//退款流水号
	if($Result['ResultCode'] == 00 && $Result['ResultMsg'] == '处理成功')//退款成功
	{
		try{
			
			pdo_begin();
				pdo_update('parking_order',array('IsWrite'=>1,'WriteSeq'=>$TradeNo),array('ordersn'=>$OutTradeNo));
				
				if($oldid) {
					pdo_update('parking_refund',array('status'=>100,'msg'=>'处理成功','up_date'=>time()),array('id'=>$oldid));
				}
				
			pdo_commit();
			
			exit(json_encode(['code'=>1,'msg'=>$Result['ResultMsg']]));
			
		}catch(PDOException $e){
			pdo_rollback();
		}
		
	} else {//退款失败
		//返回Json格式数据
		exit(json_encode(['code'=>0,'msg'=>$Result['ResultMsg']]));
	}
	
	//返回Json格式数据
//	echo json_encode($obj);
}



//停车退款查询
/**
 * @param ordersn 订单编号
 * @param token RefundQuery
 */
function RefundQuery($parArr) {
	/**
	 * 接收token 与 ordersn 订单编号
	 * SELECT * FROM ims_parking_order a LEFT JOIN ims_foll_order b ON(a.ordersn = b.ordersn) WHERE a.ordersn = $parArr['ordersn'];
	 */
	$filed = 'a.uniacid,a.ordersn,a.pay_time,a.user_id';
	$find = array(':ordersn' => $parArr['ordersn']);
	$parms = pdo_fetch("SELECT ".$filed." FROM ".tablename('foll_order')." a LEFT JOIN ".tablename('parking_order')." b ON a.ordersn = b.ordersn WHERE a.ordersn = :ordersn ORDER BY a.id desc LIMIT 1",$find);
	
	//获取支付配置参数  根据公众号ID获取
	$configs = pdo_get('pay_config',array('uniacid'=>$parms['uniacid']),array('config'));
	//反序列获取数组；
//	$config = unserialize($configs['config']);
	$config = json_decode($configs['config'],true);
	/*'wg' =>[
		'AccessCode' => '7000000000000004',
		'ParkCode' =>'1000000000000009', 
		'key' =>'kBL1dICpPBNxomAR',
	],*/
	//组装数组数据
	$dataArr = array(
		'Message' =>array(
			'Plain'=>array(
				'TransId'	=>   '70301',//业务代码：固定：60001，每个交易类型的唯一标识
				'AccessCode'=>   $config['wg']['AccessCode'],//接入方代码
				'MerDate'	=>	 date('Ymd',$parms['pay_time']),//商户系统订单日期，如：YYYYMMDD
				'OutTradeNo'=>   $parms['ordersn'],//每笔订单记录的唯一标识
			),
			'Signature'=>array(
				'SignatureValue' =>' ',
			),			
		),
	);

	//秘钥
	$key = $config['wg']['key'];	
	//将字符编码UTF-8转换为GBK编码
	$Plain = charsetToGBK($dataArr['Message']['Plain']);
	//数组转换XML  头标签Plain 加密部分
	$Tostring = arrayToXmls($Plain);
	//对xml数据拼接.key。  转换GBK编码
	$byte = charsetToGBK($Tostring.$key);
	// sha1加密返回字节
	$byte = sha1($byte,TRUE);
	//base64 安全的URL编码
	$sign = urlsafe_b64encode($byte);	
	//转码后的字符串，赋值给SignatureValue
	$dataArr['Message']['Signature']['SignatureValue'] = $sign;
	//把所有数组转换成 XML报文
	$Togrants = arrayToXml($dataArr);
//	file_put_contents('./logs/dataArr.txt', print_r($dataArr,TRUE),FILE_APPEND);
	//PACKET = XML 发送xml数据
	$data['PACKET'] = $Togrants;
	
	//请求地址
	$url = 'http://ilazypay.com/access/park';
	//实例化
	$curlpost = new Curl;
	//发送报文
	$result = $curlpost->post($url,$data);
	//解析XML
	$obj = isimplexml_load_string($result->response, 'SimpleXMLElement', LIBXML_NOCDATA);
	
	//将返回的XML数据写入日志中
	$val = json_decode(json_encode($obj),true);
	file_put_contents('./logs/RefundQuery.txt', print_r($val,TRUE));
	
	//返回Json格式数据
//	echo json_encode($obj);
	exit(json_encode($obj));
}

//停车扣费
/**
 * @param ordersn 订单编号
 * @param token Parks
 */
function Parks($parArr) {
	/**
	 * 接收token 与 ordersn 订单编号
	 * SELECT * FROM ims_parking_order a LEFT JOIN ims_foll_order b ON(a.ordersn = b.ordersn) WHERE a.ordersn = $parArr['ordersn'];
	 */
	$filed = 'a.id,a.uniacid,a.ordersn,a.pay_time,a.total,a.pay_account,a.body,a.create_time,a.user_id,b.starttime,b.endtime';
	$find = array(':ordersn' => trim($parArr['ordersn']), ':pay_status' => 0);
	$parms = pdo_fetch("SELECT ".$filed." FROM ".tablename('foll_order')." a LEFT JOIN ".tablename('parking_order')." b ON a.ordersn = b.ordersn WHERE a.ordersn = :ordersn AND a.pay_status = :pay_status ORDER BY a.id desc LIMIT 1",$find);
	file_put_contents('./logs/packetData.txt', print_r($parms,TRUE),FILE_APPEND);
	
	if(!$parms['id']){
		exit(json_encode(['code'=>0,'msg'=>'该订单不存在，请检查!']));
	}
	
	//获取支付配置参数  根据公众号ID获取
	$configs = pdo_get('pay_config',array('uniacid'=>$parms['uniacid']),array('config'));
	
	if(!$configs) {
		exit(json_encode(['code'=>0,'msg'=>'配置信息不正确，请检查!']));
	}
	
	//反序列获取数组；
//	$config = unserialize($configs['config']);
	$config = json_decode($configs['config'],true);
	/*'wg' =>[
		'AccessCode' => '7000000000000004',
		'ParkCode' =>'1000000000000009', 
		'key' =>'kBL1dICpPBNxomAR',
	],*/
	//组装数组数据
	$dataArr = array(
		'Message' =>array(
			'Plain'=>array(
				'TransId'	=>  '60001',//业务代码：固定：60001，每个交易类型的唯一标识
				'MerDate'	=>	date('Ymd',$parms['create_time']),//停车场接入方交易日期：交易记录日期，如：YYYYMMDD
				'OutTradeNo'=>	$parms['ordersn'],//停车场接入方交易流水号：每笔交易记录的唯一标识，由停车场系统生成维护
				'CarNo'		=>  trim($parArr['CarNo']),//'A88S92',//车牌号码：如：粤A88S92
				'ParkCode'	=>	$config['wg']['ParkCode'],//商户代码：由银联无感支付平台分配，商户唯一标识
				'InTime'	=>  mb_substr(date('c',$parms['starttime']), 0, strpos(date('c',$parms['starttime']),"+")),//进停车场时间：如：YYYY-MM-DDTHH:mm:ss
				'OutTime'	=> 	mb_substr(date('c',$parms['endtime']), 0, strpos(date('c',$parms['endtime']),"+")),//出停车场时间：如：YYYY-MM-DDTHH:mm:ss
				'Amount'	=>  trim(round($parms['total'])*100),//优惠前停车费金额
				'PayAmount'	=> 	trim(round($parms['pay_account'],2)*100),//优惠后停车费金额：实际需要缴纳的金额  单位:分
				'NoticeUrl'	=> 	'http://shop.gogo198.cn/payment/sign/parks.php',//通知地址：扣款成功后，需要通知停车场
				'ParkType'	=>  '00',//停车场代码类别： 00：银联无感支付平台的停车场代码  01：接入方的停车场代码
//				'ParkNum'=>   $config['wg']['ParkCode'],//停车场代码 
				'ParkNum'	=>  $config['wg']['ParkNum'],//停车场代码 
			),
			'Signature'=>array(
				'SignatureValue' =>' ',
			),			
		),
	);

	/**
	 * 订单存入数据库中 
	 * 添加数据到order表中
	 */
	 //组装par_order表中数据
	$receive = array(
		'uniacid' => $parms['uniacid'],//公众号ID
		'payTime'=> $parms['create_time'],//订单时间
		'payMoney'=> $parms['pay_account'],//交易金额		
		'ordersn'=> $parms['ordersn'],//订单编号
		'body'=> $parms['body'],//商品描述
		/**
		 * 交易类型    聚合支付：wechat：微信,alipay：支付宝,UnionPay：银联，翼支付：bestpay;
		 * 免密授权：Fwechat:微信,Falipay：支付宝,FCreditCard：信用卡,FAgro：农商行
		 */
		'payType'=> 'FCreditCard',
		'openid'=> $parms['user_id'],//用户Openid
		'status' => 0,
		'returnUrl' => '123',//前端同步回调地址
	);
	
	$order = pdo_insert('pay_order', $receive);
	if (!empty($order)) {
	    $orderid = pdo_insertid();   
	}else {
		exit(json_encode(['code'=>0,'msg'=>'sqlError']));
	}
	
	/**
	 * 将支付订单写入pay_old表中
	 */
	 //组装数据  支付订单
	$params = array(
		'payMoney'=> $receive['payMoney'],//支付金额
		'ordersn'=>$receive['ordersn'],//支付订单号
		'payType'=> $receive['payType'],//支付类型
		'body'=>$receive['body'],//商品描述
		'openid'=> $receive['openid'],//用户Openid   测试：om44I1X2wIlTWdN3pSsgoJkfMLtI				
		'create_time' => time(),//订单提交时间
		'status' => 0,//支付状态  0订单已提交，1支付成功，2支付失败
		'account' => $config['wg']['ParkCode'],
	);
	$old = pdo_insert('pay_old', $params);
	if (!empty($old)) {
	    $oldID = pdo_insertid();
	}else {
		exit(json_encode(['code'=>0,'msg'=>'sqlError']));
	}

	//秘钥
	$key = $config['wg']['key'];	
	//将字符编码UTF-8转换为GBK编码
	$Plain = charsetToGBK($dataArr['Message']['Plain']);
	//数组转换XML  头标签Plain 加密部分
	$Tostring = arrayToXmls($Plain);
	//对xml数据拼接.key。  转换GBK编码
	$byte = charsetToGBK($Tostring.$key);
	// sha1加密返回字节
	$byte = sha1($byte,TRUE);
	//base64 安全的URL编码
	$sign = urlsafe_b64encode($byte);	
	//转码后的字符串，赋值给SignatureValue
	$dataArr['Message']['Signature']['SignatureValue'] = $sign;
	//把所有数组转换成 XML报文
	$Togrants = arrayToXml($dataArr);
//	file_put_contents('./logs/dataArr.txt', print_r($dataArr,TRUE),FILE_APPEND);
	//PACKET = XML 发送xml数据
	$data['PACKET'] = $Togrants;
	
	//请求地址
//	$url = 'http://ilazypay.com:8080/access/park';
	$url = 'http://ilazypay.com/access/park';
	//实例化
	$curlpost = new Curl;
	//发送报文
	$result = $curlpost->post($url,$data);
	//解析XML
	$obj = simplexml_load_string($result->response, 'SimpleXMLElement', LIBXML_NOCDATA);
	
	//将返回的XML数据写入日志中
	$val = json_decode(json_encode($obj),true);
	file_put_contents('./logs/packets.txt', print_r($val,TRUE),FILE_APPEND);
	
	//返回Json格式数据
//	echo json_encode($obj);
	exit(json_encode($obj));
//	exit(json_encode(['code'=>0,'msg'=>'sqlError']));
}




/**
 * 优惠券查询。
 * @param $search['mobile']  签约手机号码 * 
 */
function Coupon($search) {
	
	$dataArr = array(
		'Message' =>array(		
			'Plain'=>array(
				'TransId'=>'70501',//业务代码：固定：60001，每个交易类型的唯一标识
				'AccessCode'=>'7000000000000049',//接入方代码
//				'Tel' =>'13702613232',//手机号码
				'Tel' => $search['mobile'],//用手机号查询用户的优惠券
				'Status' =>'01',//00：所有，01：未使用，02：已失效
				'PageSize' =>'12',//分页大小
				'PageNo' =>'01',//页码
			),
			'Signature'=>array(
				'SignatureValue' =>'1',
			),
		),
	);

//	$key = 'kBL1dICpPBNxomAR';
//	$key = 'a98ff5ff2c424e13f630800913eaa155';
	$key = KEYS;
	//数组转换XML  头标签Plain 加密部分
	$Plain = charsetToGBK($dataArr['Message']['Plain']);
	$Tostring = arrayToXmls($Plain);
	//对xml数据拼接.key。  转换GBK编码
	$byte = charsetToGBK($Tostring.$key);
	// sha1加密返回字节
	$byte = sha1($byte,TRUE);
	//base64 安全的URL编码
	$sign = urlsafe_b64encode($byte);
	//转码后的字符串，赋值给SignatureValue
	$dataArr['Message']['Signature']['SignatureValue'] = $sign;
	//把所有数组转换成 XML报文
	$Togrants = arrayToXml($dataArr);
	//PACKET = XML 发送xml数据
	$data['PACKET'] = $Togrants;
	//请求地址
//	$url = 'http://ilazypay.com:8080/access/park';
	$url = 'http://ilazypay.com/access/park';
	//实例化
	$curlpost = new Curl;
	//发送报文
	$result = $curlpost->post($url,$data);
	//解析XML
	$obj = isimplexml_load_string($result->response, 'SimpleXMLElement', LIBXML_NOCDATA);
	//返回Json格式数据
//	$res1 = json_decode(json_encode($obj),TRUE);
	$val = json_decode(json_encode($obj),true);
	file_put_contents('./logs/Coupon.txt', print_r($val,TRUE),FILE_APPEND);
	
	exit(json_encode($obj));	
}

//字符串转换编码，UTF-8 转 GKB;
function charsetToGBK($mixed) {
    if (is_array($mixed)) {
        foreach ($mixed as $k => $v) {
            if (is_array($v)) {
                $mixed[$k] = charsetToGBK($v);
            } else {
                $encode = mb_detect_encoding($v, array('ASCII', 'UTF-8', 'GB2312', 'GBK', 'BIG5'));
                if ($encode == 'UTF-8') {
                    $mixed[$k] = iconv('UTF-8', 'GBK', $v);
                }
            }
        }
    } else {
        $encode = mb_detect_encoding($mixed, array('ASCII', 'UTF-8', 'GB2312', 'GBK', 'BIG5'));
        //var_dump($encode);
        if ($encode == 'UTF-8') {
            $mixed = iconv('UTF-8', 'GBK', $mixed);
        }
    }
    return $mixed;
}

//base64 安全的URL编码
function urlsafe_b64encode($string) {
	$data = base64_encode($string);
	$data = str_replace(array('+','/','='), array('-','_',''), $data);
	return $data;
}

//数组转 XML ： MessageSuit
function arrayToXml($arr,$dom=0,$item=0){ 
	if (!$dom){ 
		$dom = new DOMDocument('1.0','GBK');
	} 
	if(!$item){
		$item = $dom->createElement("MessageSuit");
		$dom->appendChild($item); 
	} 
	foreach ($arr as $key=>$val) {
		
		$itemx = $dom->createElement(is_string($key)?$key:"item");
		
		$item->appendChild($itemx); 
		
		if (!is_array($val)){ 
			$text = $dom->createTextNode($val); 
			$itemx->appendChild($text);
		}else { 
			arrayToXml($val,$dom,$itemx); 
		} 
	}
	return $dom->saveXML();
}

//数组转XML
function arrayToXmls($arr)
{
    $xml = "<Plain>";
    foreach ($arr as $key=>$val)
    {
        if (is_numeric($val)){
            $xml.="<".$key.">".$val."</".$key.">";
        }else{
             $xml.="<".$key.">".$val."</".$key.">";
        }
    }
    $xml.="</Plain>";
    return $xml;
}

//提交XML信息
function postXml($arrs,$url){
	$ch = curl_init();
	$timeout = 30;
	$header = array(
		'Content-Type:text/xml;charset=gbk',
		'Content-Length:'.strlen($arrs),
	);
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_POST, TRUE);
	curl_setopt($ch, CURLOPT_HEADER, $header);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 0);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $arrs);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
	curl_setopt($ch, CURLOPT_HEADER, 0);
	$result = curl_exec($ch);
	curl_close($ch);
	return $result;
}

?>