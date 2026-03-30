<?php
// Ä£¿éLTDÌá¹©
require_once 'alipay.config.php';
require_once 'lib/alipay_notify.class.php';
$alipayNotify = new AlipayNotify($alipay_config);
$verify_result = $alipayNotify->verifyNotify();

if ($verify_result) {
	$success_details = $_POST['success_details'];
	$fail_details = $_POST['fail_details'];
	echo 'success';
	return 1;
}

echo 'fail';

?>
