<?php
	//header('Content-Type:text/html;charset=utf-8');
error_reporting(0);
define('IN_MOBILE', true);
require dirname(__FILE__) . '/../../../../framework/bootstrap.inc.php';
require IA_ROOT . '/addons/ewei_shopv2/defines.php';
require IA_ROOT . '/addons/ewei_shopv2/core/inc/functions.php';
require IA_ROOT . '/addons/ewei_shopv2/core/inc/plugin_model.php';
require IA_ROOT . '/addons/ewei_shopv2/core/inc/com_model.php';
global $_W;
global $_GPC;

file_put_contents('./code.txt', print_r($_GPC,TRUE),FILE_APPEND);

if(!empty($_GPC["code"])) {
		
//	$params = unserialize($_SESSION['params']);
		
	$paramss = cache_load('params');
	$params = unserialize($paramss);
	
	$appid = 'wx76d541cc3e471aeb';
	$secret = '3e3d16ccb63672a059d387e43ec67c95';
    $code = $_GET["code"];
    $get_token_url = 'https://api.weixin.qq.com/sns/oauth2/access_token?appid='.$appid.'&secret='.$secret.'&code='.$code.'&grant_type=authorization_code';

    $ch = curl_init();  
    curl_setopt($ch,CURLOPT_URL,$get_token_url);  
    curl_setopt($ch,CURLOPT_HEADER,0);  
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1 );  
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);  
    $res = curl_exec($ch);  
    curl_close($ch);  
    $json_obj = json_decode($res,true);
	
//	print_r($json_obj);
	file_put_contents('./notify.txt', print_r($json_obj,TRUE),FILE_APPEND);

		
	if(isset($json_obj['expires_in'])) {
			
		$user_data = array(
		
			'newOpenid'=>$json_obj['openid']
		
		);

		$result = pdo_update('ewei_shop_member', $user_data, array('openid' => $params['openid'],'uniacid'=>$params['uniacid']));
		
		if (!empty($result)) {
				
				$url = 'http://shop.gogo198.cn/app/index.php?i=14&c=entry&m=ewei_shopv2&do=mobile&r=order.tgpay.tgpay&';
				$params['newOpenid'] = $json_obj['openid'];
				//字符串拼接
				$str = http_build_query($params);
				
				$url = $url.$str;
				file_put_contents('./cookie.txt', print_r($params,TRUE),FILE_APPEND);
				header("Location:".$url);
				
//				//ov3-btyLPTGwIduBvEXdiGSnpUK4
//				$ch = curl_init();
//				curl_setopt($ch, CURLOPT_URL, $url);
//				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
//				curl_setopt($ch, CURLOPT_POST, 1);
//				curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
//				$output = curl_exec($ch);
//				curl_close($ch);
//				print_r($output);
//				echo "<pre>";
//				print_r($params);
				
			}
	}
}

//$paramss = cache_load('params');
//$params = unserialize($paramss);
//echo "<pre>";
//print_r($params);
//die;

?>