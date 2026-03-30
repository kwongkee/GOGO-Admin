<?php
// 模块LTD提供
function yeepay_build($params, $yeepay = array(), $openid = '')
{
	global $_W;
	include IA_ROOT . '/addons/sz_yi/core/inc/plugin/vendor/yeepay/wy/yeepayCommon.php';
	$setdata = pdo_fetch('select * from ' . tablename('sz_yi_sysset') . ' where uniacid=:uniacid limit 1', array(':uniacid' => $_W['uniacid']));
	$set = unserialize($setdata['sets']);
	$merchantaccount = $set['pay']['merchantaccount'];
	$merchantPublicKey = $set['pay']['merchantPublicKey'];
	$merchantPrivateKey = $set['pay']['merchantPrivateKey'];
	$yeepayPublicKey = $set['pay']['yeepayPublicKey'];
	$p1_MerId = $set['pay']['merchantaccount'];
	$merchantKey = $set['pay']['merchantKey'];
	$tid = $params['tid'];
	$source = '../addons/sz_yi/payment/yeepay/wy_notify.php';
	$dest = '../addons/sz_yi/payment/yeepay/' . $_W['uniacid'] . '/wy_notify.php';
	moveFile($source, $dest);
	$reurl = $_W['siteroot'] . 'app/index.php?i=' . $_W['uniacid'] . '&c=entry&m=sz_yi&do=order&p=pay&op=returnyeepay_wy&openid=' . $openid;
	$data = array();
	$data['p0_Cmd'] = 'Buy';
	$data['p1_MerId'] = $p1_MerId;
	$data['p2_Order'] = $tid;
	$data['p3_Amt'] = floatval($params['fee']);
	$data['p4_Cur'] = 'CNY';
	$data['p5_Pid'] = '';
	$data['p6_Pcat'] = '';
	$data['p7_Pdesc'] = '';
	$data['p8_Url'] = $reurl;
	$data['p9_SAF'] = '';
	$data['pa_MP'] = '';
	$data['pd_FrpId'] = '';
	$data['pm_Period'] = '7';
	$data['pn_Unit'] = 'day';
	$data['pr_NeedResponse'] = '1';
	$data['pt_UserName'] = '';
	$data['pt_PostalCode'] = '';
	$data['pt_Address'] = '';
	$data['pt_TeleNo'] = '';
	$data['pt_Mobile'] = '';
	$data['pt_Email'] = '';
	$data['pt_LeaveMessage'] = '';
	$hmac = HmacMd5(implode($data), $merchantKey);
	$sHtml = '<form id=\'yeepay\' name=\'yeepay\' action=\'' . $reqURL_onLine . '\' method=\'post\'>';

	foreach ($data as $k => $v) {
		$sHtml .= '<input type="hidden" name="' . $k . '" value="' . $v . "\" />\n";
	}

	$sHtml .= '<input type="hidden" name="hmac" value="' . $hmac . "\" />\n";
	$sHtml = $sHtml . '</form>';
	$sHtml = $sHtml . '<script>document.forms[\'yeepay\'].submit();</script>';
	echo $sHtml;
	exit();
}

function moveFile($source, $dest)
{
	if (!is_dir(dirname($dest))) {
		@mkdir(dirname($dest), 511, true);
	}

	@copy($source, $dest);
}

if (!defined('IN_IA')) {
	exit('Access Denied');
}

global $_W;
global $_GPC;
$operation = (!empty($_GPC['op']) ? $_GPC['op'] : 'display');
$openid = m('user')->getOpenid();

if (empty($openid)) {
	$openid = $_GPC['openid'];
}

$member = m('member')->getMember($openid);
$uniacid = $_W['uniacid'];
$orderid = intval($_GPC['orderid']);
$logid = intval($_GPC['logid']);
$shopset = m('common')->getSysset('shop');

if (!empty($orderid)) {
	$order = pdo_fetch('select * from ' . tablename('sz_yi_order') . ' where id=:id and uniacid=:uniacid and openid=:openid limit 1', array(':id' => $orderid, ':uniacid' => $uniacid, ':openid' => $openid));

	if (empty($order)) {
		show_json(0, '订单未找到!');
	}

	$order_price = pdo_fetchcolumn('select price from ' . tablename('sz_yi_order') . ' where ordersn_general=:ordersn_general and uniacid=:uniacid and openid=:openid limit 1', array(':ordersn_general' => $order['ordersn_general'], ':uniacid' => $uniacid, ':openid' => $openid));
	$log = pdo_fetch('SELECT * FROM ' . tablename('core_paylog') . ' WHERE `uniacid`=:uniacid AND `module`=:module AND `tid`=:tid limit 1', array(':uniacid' => $uniacid, ':module' => 'sz_yi', ':tid' => $order['ordersn_general']));
	
	//xj 20170810 异常订单
	if(empty($log)) {
		show_json(0, '异常订单!');
	}
	//xj 20170810 异常订单
	
	if (!empty($log) && ($log['status'] != '0')) {
		show_json(0, '订单已支付, 无需重复支付!');
	}

	$param_title = $shopset['name'] . '订单: ' . $order['ordersn_general'];
	$yeepay = array('success' => false);
	$params = array();
	$params['tid'] = $log['tid'];
	$params['user'] = $openid;
	$params['fee'] = $order_price;
	$params['title'] = $param_title;
	$params['name'] = $shopset['name'];
	load()->func('communication');
	load()->model('payment');
	yeepay_build($params, array(), $openid);
}

?>
