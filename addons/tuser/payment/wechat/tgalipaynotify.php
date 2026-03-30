<?php
define('IN_MOBILE', true);

$input = file_get_contents('php://input');
//接收后台回调参数，并转换json格式
$receive = json_decode($input,TRUE);
file_put_contents('./log/alipaylog.txt', print_r($receive,TRUE),FILE_APPEND);

if (!empty($receive)) {

	$answer = array(
		'lowOrderId'=>$receive['lowOrderId'],//下游系统流水号，必须唯一
		'merchantId'=>$receive['merchantId'],//商户进件账号
		'upOrderId'=>$receive['upOrderId'],//上游流水号
	);
	
	if ($receive['state'] == 0) {
		//是否接收到回调  SUCCESS表示成功
		$answer['finished'] = 'SUCCESS';
		
	} else if ($receive['state'] == 1) {
		
		$answer['finished'] = 'FAIL';
		
	}
	$str = tostring($answer);
	
	$str = $str .'&key=5f61d7f65b184d19a1e006bc9bfb6b2f';
	
	$answer['sign'] = strtoupper(md5($str));
	
	file_put_contents('./log/alijsons.txt', print_r($answer,TRUE),FILE_APPEND);
	//将数据转换成json数据返回
	echo json_encode($answer);

} else {
	
	echo '无参数...';
}

/**
 * 字符串拼接
 */
function tostring($arrs) {
	ksort($arrs, SORT_STRING);
	$str = '';
	foreach ($arrs as $key => $v ) {
		if (empty($v)) {
			continue;
		}
		$str .= $key . '=' . $v . '&';
	}
	$str = trim($str,'&');
	return $str;
}
?>