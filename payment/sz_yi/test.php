<?php
	header('Content-Type:text/html;charset=utf-8');
	define('IN_MOBILE', true);
	define('PDO_DEBUG', true);
	define("KEYS","f8ee27742a68418da52de4fca59b999e");
	define("KEYS1","b4f16b4526b046c580e363fcfcd07c82");
	require_once '../../framework/bootstrap.inc.php';
	require_once '../../app/common/bootstrap.app.inc.php';
	//load()->app('common');
	load()->app('template');
	//加载发送消息函数  diysend
	load()->func('diysend');
	global $_W;
	global $_GPC;
	

	$postdata['tid'] 	= 'GG20180817104814244868';
    $postdata['fee'] 	= '31.50';
    $postdata['title']  = '加拿大Christie';
    $postdata['token'] 	= 'wechat';
    
    //钜铭
    /*$postdata['openid']  = 'ov3-btyLPTGwIduBvEXdiGSnpUK4';
    $postdata['account'] = '403510118203';
    $postdata['key'] 	 = '8724d2f3f59f303866a7dacf70dfa8f7';*/
    
    //喜柏
    $postdata['openid']  = 'oR-IB0t4Yc9zmV-K-_5NRB-u5k4U';
    $postdata['account'] = '101570223660';
    //$postdata['key'] 	 = '85be68fc58b1badfc7580bd988ed4f54';
    $postdata['key'] 	 = 'b4f16b4526b046c580e363fcfcd07c82';
    
    //请求地址
    $url = 'http://shop.gogo198.cn/payment/sz_yi/Payments.php';
	//转换Json格请求
	$jsonStr = json_encode($postdata);
	//请求数据
	$res = JsonPost($url,$jsonStr);
	//打印数据
	//print_r($res);
	//ssssss
	print_r($result = json_decode($res,TRUE));

	
	function JsonPost($url,$post_data) {
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // 跳过证书检查
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, true);  // 从证书中检查SSL加密算法是否存在
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($ch, CURLOPT_POSTFIELDS,$post_data);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Content-Length: ' . strlen($post_data)));
		$result = curl_exec($ch);
		curl_close($ch);
		return $result;
	}

?>