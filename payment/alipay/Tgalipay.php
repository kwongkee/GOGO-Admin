<?php
define('IN_MOBILE', true);
require '../../framework/bootstrap.inc.php';
require '../../app/common/bootstrap.app.inc.php';
require './func/alipaycomm.php';
load()->app('common');
load()->app('template');
load()->func('pdo');//加载数据库操作函数

//组装数据
$params = array(
	'payMoney'=> '0.01',
	'lowOrderId'=>'GG'.date('Ymd',time()).rand(1, time()),
	'body'=>'gogo',
	'notifyUrl'=>'http://shop.gogo198.cn/payment/wechat/tgnotify.php',
	'openId'=>'om44I1X2wIlTWdN3pSsgoJkfMLtI',
);

//配置
$config = array(
	'account'=>'13974747474',
	'key'=>'5f61d7f65b184d19a1e006bc9bfb6b2f',
);


	$payurl = Tgalipay_scode($params,$config);
	$pay = $payurl->codeUrl;
	echo "<script language='javascript' type='text/javascript'>";
	echo "location.href = '{$pay}'";
	echo "</script>";
	echo '<pre>';print_r($payurl);die;

?>