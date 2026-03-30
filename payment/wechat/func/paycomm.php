<?php
/**
 * 通莞微信公众号支付。
 * @params 订单支付信息
 * @config  配置信息
 */
function Tgwechat_public($params, $config) {

	global $_W;
	$wOpt = array();
	$package = array();
	$package['account'] = $config['account'];
	$package['payMoney'] = $params['payMoney'];
	$package['lowOrderId'] = $params['lowOrderId'];
	$package['body'] = $params['body'];
	$package['returnUrl'] = 'http://shop.gogo198.cn/app/index.php?i=14&c=entry&m=ewei_shopv2&do=mobile&r=parking.msg';//成功回调地址；
	$package['notifyUrl'] = $params['notifyUrl'];
	$package['openId'] = $params['openId'];
	//转换key=value&key=value;
	$str = tostring($package);
	//拼接加密字串
	$str .= '&key=' . $config['key'];
	//MD5加密字串
	$sign = md5($str);
	//返回加密字串转换成大写字母
	$package['sign'] = strtoupper($sign);
	//数据包转换成json格式
	$data =  json_encode($package);
	//数据请求地址，post形式传输
	$url = 'http://tgjf.833006.biz/tgPosp/payApi/wxJspay';
	//数据请求地址，post形式传输
	$response = ihttp_post($url,$data);
	//解析json数据
	$response = json_decode($response);
	//直接返回支付URL地址
	return $response->pay_url;
}



/**
 * 通莞微信公众号原生支付。
 * @params 订单信息数组
 * @config  配置数据
 * 
 */
function Tgwechat_jsapi($params, $config) {

	global $_W;
	$wOpt = array();
	$package = array();
	$package['account'] = $config['account'];
	$package['payMoney'] = $params['payMoney'];
	$package['lowOrderId'] = $params['lowOrderId'];
	$package['body'] = $params['body'];
	$package['notifyUrl'] = $params['notifyUrl'];
	$package['openId'] = $params['openId'];
	//转换key=value&key=value;
	$str = tostring($package);
	//拼接加密字串
	$str .= '&key=' . $config['key'];
	//MD5加密字串
	$sign = md5($str);
	//返回加密字串转换成大写字母
	$package['sign'] = strtoupper($sign);
	//数据包转换成json格式
	$data =  json_encode($package);
	//数据请求地址，post形式传输
	$url = 'http://tgjf.833006.biz/tgPosp/payApi/wxJspay';
	//数据请求地址，post形式传输
	$response = ihttp_post($url,$data);
	//解析json数据
	$response = json_decode($response,TRUE);
	//解析pay_info json数据！
	$payinfo = json_decode($response['pay_info'],TRUE);
	
	$wOpt['appId'] = $payinfo['appId'];
	$wOpt['timeStamp'] = $payinfo['timeStamp'];
	$wOpt['nonceStr'] = $payinfo['nonceStr'];
	$wOpt['package'] = $payinfo['package'];
	$wOpt['signType'] = 'MD5';
	$wOpt['paySign'] = $payinfo['paySign'];
	//返回支付参数数组
	return $wOpt;
}

/**
 * 订单查询
 * @parmas  订单号
 * @config  配置信息
 * 
 */
function orderQuery($params,$config){
	$query = array(
		'account'=>$config['account'],
		'lowOrderId'=>$params['lowOrderId'],
	);
	//转换key=value&key=value;
	$str = tostring($query);
	//拼接加密字串
	$str .= '&key=' . $config['key'];
	//字串加密
	$sign = md5($str);
	//加密字串转大写
	$query['sign'] = strtoupper($sign);
	//数组数据编码为json格式数据
	$query = json_encode($query);
	//查询请求地址
	$url = 'http://tgjf.833006.biz/tgPosp/services/payApi/orderQuery';
	//发送请求
	$result = ihttp_post($url, $query);
	//
	$result = json_decode($result,TRUE);
	
	return $result;	
}


/**
 * 通莞微信扫码支付。
 * @params 订单支付信息
 * @config  配置信息
 */
function Tgwechat_scode($params, $config) {

	global $_W;
	$wOpt = array();
	$package = array();
	$package['account'] = $config['account'];
	$package['payMoney'] = $params['payMoney'];
	$package['lowOrderId'] = $params['lowOrderId'];
	$package['body'] = $params['body'];
	$package['notifyUrl'] = $params['notifyUrl'];
	$package['payType'] = '0';
	//转换key=value&key=value;
	$str = tostring($package);
	//拼接加密字串
	$str .= '&key=' . $config['key'];
	//MD5加密字串
	$sign = md5($str);
	//返回加密字串转换成大写字母
	$package['sign'] = strtoupper($sign);
	//数据包转换成json格式
	$data =  json_encode($package);
	//数据请求地址，post形式传输
	$url = 'http://tgjf.833006.biz/tgPosp/services/payApi/unifiedorder';
	//数据请求地址，post形式传输
	$response = ihttp_post($url,$data);
	//解析json数据
	$response = json_decode($response);
	//返回json数据参数（sign,message,status,codeUrl,account,orderID,lowOrderId)
	return $response;
}



/**
 * 通莞支付宝扫码支付。
 * @params 订单支付信息
 * @config  配置信息
 */
function Tgalipay_scode($params, $config) {

	global $_W;
	$wOpt = array();
	$package = array();
	$package['account'] = $config['account'];
	$package['payMoney'] = $params['payMoney'];
	$package['lowOrderId'] = $params['lowOrderId'];
	$package['body'] = $params['body'];
	$package['notifyUrl'] = $params['notifyUrl'];
	$package['payType'] = '1';
	//转换key=value&key=value;
	$str = tostring($package);	
	//拼接加密字串
	$str .= '&key=' . $config['key'];
	//MD5加密字串
	$sign = md5($str);
	//返回加密字串转换成大写字母
	$package['sign'] = strtoupper($sign);
	//数据包转换成json格式
	$data =  json_encode($package);
	//数据请求地址，post形式传输
	$url = 'http://tgjf.833006.biz/tgPosp/services/payApi/unifiedorder';
	//数据请求地址，post形式传输
	$response = ihttp_post($url,$data);
	//解析json数据
	$response = json_decode($response);
	//返回json数据参数（sign,message,status,codeUrl,account,orderID,lowOrderId)
	return $response;
}


/**
 * 签约银行卡服务
 */
function Sign($data) {
 	$Togrant = array(
		'Message' => array(
			'Plain' =>array(
				'TransId' =>'60001',//业务代码，固定60301
				'MerDate' => date('YMD',time()),//交易记录日期
				'OutTradeNo' => 'GGP'.date('YMD',time()),mt_rand(1, time()),//交易流水号
				'CarNo' => '粤A88S92',//车牌号
				'ParkCode' => '',//商户代码
				'InTime' => date('Y-M-DTH:m:s',time()),//进场停车时间
				'OutTime' => date('Y-M-DTH:m:s',time()),//出场停车时间
				'Amount' => '1',//优惠前停车费金额
				'PayAmount' => '2',//优惠后停车费金额，实际需要交纳的金额
				'NoticeUrl' => '',//通知地址：扣款成功后，需要通知停车场。
				'ParkType' => '01',//00;银联无感支付平台的停车场代码，01：接入方的停车场代码，
				'ParkNum' => '123456',//停车场代码
				
				
				
				'AccessCode' => '7000000000000004',//接入方代码，固定不变的配置
				'Tel' => $_GET['Tel'],//手机号码
				'CardNo' => $_GET['CardNo'],//银行卡号
				'UserName' => $_GET['UserName'],//用户姓名
	//			'CertType' => '1',//身份证类型 ，1身份证
				'CertType' => $_GET['CertType'],//身份证类型 ，1身份证
				'CertNo' => $_GET['CertNo'],//证件号
				'Phone' => $_GET['Phone'],//银行预留手机号，真实预留在银行卡上的手机号。
				'NotifyUrl' => 'https://www.baidu.com',//异步通知URL，签约成功会往改地址发送通知报文
			),
			'Signature' => array(
				'SignatureValue' => '1',
			),
		),
	);
	//key
	$key = 'kBL1dICpPBNxomAR';
	
 }



/**
 * @数据请求提交POST json
 * @$url:请求地址
 * @post_data:请求数据
 */
function ihttp_post($url,$post_data){
	//初始化	 
	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
	curl_setopt($ch, CURLOPT_POSTFIELDS,$post_data);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Content-Length: ' . strlen($post_data)));
	$result = curl_exec($ch);
	curl_close($ch);
	return $result;
}

/**
 * 字符串拼接
 * @arrs :数组数据
 */
function tostring($arrs){
	ksort($arrs, SORT_STRING);
	$str = '';
	foreach ($arrs as $key => $v ) {
		if ($v=='' || $v == null) {
			continue;
		}
		$str .= $key . '=' . $v . '&';
	}
	$str = trim($str,'&');
	return $str;
}
?>