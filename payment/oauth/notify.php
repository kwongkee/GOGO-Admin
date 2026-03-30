<?php
		//header('Content-Type:text/html;charset=utf-8');
		define('IN_MOBILE', true);
		require_once '../../framework/bootstrap.inc.php';
		require_once '../../app/common/bootstrap.app.inc.php';
		load()->app('common');
		load()->app('template');
		load()->func('diysend');
		global $_W;
		global $_GPC;
		session_start();
		if(!empty($_GET["code"])) {
			
			$params = unserialize($_SESSION['params']);
			
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
		
//			print_r($json_obj);
			file_put_contents('./notify.txt', print_r($json_obj,TRUE),FILE_APPEND);
			file_put_contents('./gets.txt', print_r($_GET,TRUE),FILE_APPEND);
			file_put_contents('./cookie.txt', print_r($params,TRUE),FILE_APPEND);
			
			if(isset($json_obj['expires_in'])) {
				
					$user_data = array(
					
	   	 			'newOpenid'=>$json_obj['openid']
	   	 			
					);
					
					$result = pdo_update('ewei_shop_member', $user_data, array('openid' => $params['openid'],'uniacid'=>$params['uniacid']));
					
					if (!empty($result)) {
							
							$url = 'http://shop.gogo198.cn/app/index.php?i=14&c=entry&m=ewei_shopv2&do=mobile&r=order.tgpay.tgpay';
							$params['newOpenid'] = $json_obj['openid'];
							$ch = curl_init();
							curl_setopt($ch, CURLOPT_URL, $url);
							curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
							curl_setopt($ch, CURLOPT_POST, 1);
							curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
							$output = curl_exec($ch);
							curl_close($ch);
							print_r($output);
					}
			}
}

	echo '<a href="http://shop.gogo198.cn/app/index.php?i=14&c=entry&m=ewei_shopv2&do=mobile&r=order.tgpay.tgpay">点击跳转</a>';

	
    //根据openid和access_token查询用户信息  
   /* $access_token = $json_obj['access_token'];  
    $openid = $json_obj['openid'];  
    $get_user_info_url = 'https://api.weixin.qq.com/sns/userinfo?access_token='.$access_token.'&openid='.$openid.'&lang=zh_CN';  
      
    $ch = curl_init();  
    curl_setopt($ch,CURLOPT_URL,$get_user_info_url);  
    curl_setopt($ch,CURLOPT_HEADER,0);  
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1 );  
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);  
    $res = curl_exec($ch);  
    curl_close($ch);
      
    //解析json  
    $user_obj = json_decode($res,true);
	  file_put_contents('./notify_1.txt', print_r($user_obj,TRUE),FILE_APPEND);  
    $_SESSION['user'] = $user_obj; */
	


?>