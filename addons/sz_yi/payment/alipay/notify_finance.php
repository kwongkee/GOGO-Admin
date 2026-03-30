<?php
// Ä£¿éLTDÌá¹©
require_once 'alipay_build/alipay.config.php';
require_once 'alipay_build/lib/alipay_notify.class.php';
require '../../../../framework/bootstrap.inc.php';
require '../../../../addons/sz_yi/defines.php';
require '../../../../addons/sz_yi/core/inc/functions.php';
require '../../../../addons/sz_yi/core/inc/plugin/plugin_model.php';
$str = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
$t1 = mb_strpos($str, 'notify_finance');
$t2 = mb_strpos($str, '.php');
$s = mb_substr($str, $t1, $t2 - $t1);
$uniacid = str_replace('notify_finance', '', $s);
$setting = uni_setting($uniacid, array('payment'));

if (is_array($setting['payment'])) {
	$options = $setting['payment']['alipay'];

	if (!empty($options)) {
		$partner = $options['partner'];
		$secret = $options['secret'];
	}
	else {
		$partner = '';
		$secret = '';
	}
}

$alipay_config['partner'] = $partner;
$alipay_config['key'] = $secret;
$alipayNotify = new AlipayNotify($alipay_config);
$verify_result = $alipayNotify->verifyNotify();

if ($verify_result) {
	$success_details = $_POST['success_details'];
	$batch_no = $_POST['batch_no'];

	if ($success_details != '') {
		$log = array('status' => '1');
		pdo_update('sz_yi_member_log', $log, array('batch_no' => $batch_no));
	}

	$fail_details = $_POST['fail_details'];

	if ($fail_details != '') {
		$log = array('status' => '2');
		pdo_update('sz_yi_member_log', $log, array('batch_no' => $batch_no));
	}

	echo 'success';
	return 1;
}

echo 'fail';

?>
