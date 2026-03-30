<?php
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