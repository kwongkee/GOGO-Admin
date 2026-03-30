<?php
// 模块LTD提供
if (!defined('IN_IA')) {
	exit('Access Denied');
}

global $_W;
global $_GPC;
$operation = (!empty($_GPC['op']) ? $_GPC['op'] : 'display');
$page = 'withdraw';
$openid = m('user')->getOpenid();
$member = m('member')->getInfo($openid);
$id = ($_GPC['storeid'] ? $_GPC['storeid'] : '0');
$store = $this->model->getInfo($id);
$totalprices = $this->model->getTotalPrice($id);
$totalwithdraws = $this->model->getWithdrawed($id);
$totalwithdrawprice = $this->model->getRealPrice($id);
$totalprices = $totalwithdrawprice - $totalwithdraws;
$totalpricess = number_format($totalprices, '2');
if (($operation == 'display') && $_W['isajax']) {
	$store['totalprices'] = $totalpricess;
	show_json(1, array('store' => $store, 'noinfo' => empty($member['realname'])));
}
else {
	if (($operation == 'submit') && $_W['ispost']) {
		$money = floatval($_GPC['money']);

		if (empty($money)) {
			show_json(0, '申请金额为空!');
		}

		if ($money <= 0) {
			show_json(0, '提现金额不能小于0元!');
		}

		if ($totalprices < $money) {
			show_json(0, '提现金额过大!');
		}

		$withdraw_no = m('common')->createNO('store_withdraw', 'withdraw_no', 'SW');
		$data = array('uniacid' => $_W['uniacid'], 'withdraw_no' => $withdraw_no, 'openid' => $openid, 'store_id' => $id, 'money' => $money, 'status' => 0);
		pdo_insert('sz_yi_store_withdraw', $data);
		$message = array(
			'keyword1' => array('value' => '门店提现成功通知', 'color' => '#73a68d'),
			'keyword2' => array('value' => '【商户名称】' . $cashier_stores['name'], 'color' => '#73a68d'),
			'remark'   => array('value' => '恭喜,您的提现申请已经成功提交!')
			);
		m('message')->sendCustomNotice($openid, $message);
		show_json(1);
	}
}

include $this->template('withdraw');

?>
