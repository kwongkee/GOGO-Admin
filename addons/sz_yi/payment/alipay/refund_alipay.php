<?php
// Ä£¿éLTDÌá¹©
require_once 'alipay_build/alipay.config.php';
require_once 'alipay_build/lib/alipay_notify.class.php';
require '../../../../framework/bootstrap.inc.php';
require '../../../../addons/sz_yi/defines.php';
require '../../../../addons/sz_yi/core/inc/functions.php';
require '../../../../addons/sz_yi/core/inc/plugin/plugin_model.php';
$str = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
$t1 = mb_strpos($str, 'refund_alipay');
$t2 = mb_strpos($str, '.php');
$s = mb_substr($str, $t1, $t2 - $t1);
$uniacid = str_replace('refund_alipay', '', $s);
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
	$result_details = $_POST['result_details'];
	$result_details = explode('^', $result_details);
	$batch_no = $_POST['batch_no'];

	if ($result_details[2] == 'SUCCESS') {
		$order_refund = pdo_fetch('select * from ' . tablename('sz_yi_order_refund') . ' where batch_no=' . $batch_no);
		$refund = array('status' => '1', 'refundtype' => '3', 'price' => $result_details[1], 'refundtime' => time());
		pdo_update('sz_yi_order_refund', $refund, array('batch_no' => $batch_no));
		m('notice')->sendOrderMessage($order_refund['orderid'], true);
		$order = array('refundstate' => '0', 'status' => '-1', 'refundtime' => time());
		pdo_update('sz_yi_order', $order, array('id' => $order_refund['orderid']));
	}
	else {
		$refund = array('status' => '3');
		pdo_update('sz_yi_order_refund', $refund, array('batch_no' => $batch_no));
	}

	echo 'success';
	return 1;
}

echo 'fail';

?>
