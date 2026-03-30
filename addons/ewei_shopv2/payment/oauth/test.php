<?php

error_reporting(0);
define('IN_MOBILE', true);
require dirname(__FILE__) . '/../../../../framework/bootstrap.inc.php';
require IA_ROOT . '/addons/ewei_shopv2/defines.php';
require IA_ROOT . '/addons/ewei_shopv2/core/inc/functions.php';
require IA_ROOT . '/addons/ewei_shopv2/core/inc/plugin_model.php';
require IA_ROOT . '/addons/ewei_shopv2/core/inc/com_model.php';

	$user = pdo_get('account', array('uniacid' => 3), array('hash'));
	print_r($user);








	$url = 'http://shop.gogo198.cn/app/index.php?i=14&c=entry&m=ewei_shopv2&do=mobile&r=order.tgpay.getOpenid&openids=121asdf';
//	header('Location:'.$url);
//	echo "string";




?>