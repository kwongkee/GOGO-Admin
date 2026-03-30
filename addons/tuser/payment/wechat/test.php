<?php
header('Content-Type:text/html;charset=utf-8');
$url = 'http://shop.gogo198.cn/payment/wechat/Tgpay.php';
$postdata = array(
	'ordersn'=>'GGP'.date('Ymd',time()).rand(1, time()),
	'Openid'=>'om44I1X2wIlTWdN3pSsgoJkfMLtI',
	'copyName'=>'喜柏停车',
	'body'=>'停车费',
	'payMoney'=>'0.01',
	'Type'=>'alipay',
	'payTime'=>time(),
);

$str = toreceive($postdata);
$sign = md5($str);
$postdata['sign'] = strtoupper($sign);


$res = ihttp_post($url, $postdata);
//echo '<pre>';
//print_r($res);

header("location:".$res);


function toreceive($arrs){
	krsort($arrs);
	$str = '';
	foreach($arrs as $key=>$val){
		$str .= $key . '=' . $val . '&';
	}
	$str = trim($str,'&');
	return $str;
}

function ihttp_post($url,$post_data) {
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
	$res = curl_exec($ch);
	curl_close($ch);
	return $res;
}
?>