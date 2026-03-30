<?php

if (!(defined('IN_IA'))) {
	exit('Access Denied');
}
/**
 *
 */
class Video_EweiShopV2Page extends mobilePage
{
	
    function main(){
    	$username = pdo_get('wechat_attachment', array('id' => 629));
    	echo '123'.'<br>';
    	print_r($username);
    	include $this->template();
    }
}
