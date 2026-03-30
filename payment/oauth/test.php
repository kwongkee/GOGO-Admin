<?php 

	$ch = curl_init();  
    curl_setopt($ch,CURLOPT_URL,'http://shop.gogo198.cn/payment/oauth/oauth.php');  
    curl_setopt($ch,CURLOPT_HEADER,0);  
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1 );  
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);  
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    $res = curl_exec($ch);
    curl_close($ch);
    $json_obj = json_decode($res,true);
	var_dump($json_obj);

