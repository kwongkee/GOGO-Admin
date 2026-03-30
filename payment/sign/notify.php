<?php
//银联无感间接跳转输码页面
define('IN_MOBILE', true);
require '../../framework/bootstrap.inc.php';
require '../../app/common/bootstrap.app.inc.php';
load()->app('common');
load()->app('template');

header("Location:http://shop.gogo198.cn/app/index.php?i=14&c=entry&m=ewei_shopv2&do=mobile&r=parking.info");
exit();

/*$input = file_get_contents('php://input');

file_put_contents('./log/wecahtpaylog.txt', print_r('$receive',TRUE),FILE_APPEND);

$isxml = true;

if (!empty($input)) {
	$obj = isimplexml_load_string($input, 'SimpleXMLElement', LIBXML_NOCDATA);
	$data = json_decode(json_encode($obj), true);
	file_put_contents('./notify2.txt', print_r($data,TRUE));
} else {
	echo 'error';
}*/
?>