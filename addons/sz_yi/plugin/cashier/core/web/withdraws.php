<?php
// 模块LTD提供
if (!defined('IN_IA')) {
	exit('Access Denied');
}

global $_W;
global $_GPC;
$operation = ($_GPC['op'] ? $_GPC['op'] : 'display');
$uniacid = $_W['uniacid'];

if ($operation == 'display') {
	ca('cashier.withdraw.view');
	$pindex = max(1, intval($_GPC['page']));
	$psize = 20;
	$condition = ' and s.uniacid=:uniacid and w.money<>0';
	$params = array(':uniacid' => $uniacid);

	if (!empty($_GPC['name'])) {
		$_GPC['name'] = trim($_GPC['name']);
		$condition .= ' and (s.name like :name or s.contact like :name or s.mobile like :name)';
		$params[':name'] = '%' . $_GPC['name'] . '%';
	}

	if (empty($starttime) || empty($endtime)) {
		$starttime = strtotime('-1 month');
		$endtime = time();
	}

	if (!empty($_GPC['time'])) {
		$starttime = strtotime($_GPC['time']['start']);
		$endtime = strtotime($_GPC['time']['end']);

		if ($_GPC['searchtime'] == '1') {
			$condition .= ' AND w.create_time >= :starttime AND w.create_time <= :endtime ';
			$params[':starttime'] = $_GPC['time']['start'];
			$params[':endtime'] = $_GPC['time']['end'];
		}
	}

	$sql = 'select w.id, s.name, m.nickname, m.avatar, m.weixin, w.withdraw_no, w.money, w.create_time, w.status from ' . tablename('sz_yi_cashier_store') . ' s left join ' . tablename('sz_yi_cashier_withdraw') . ' w on s.id=w.cashier_store_id  left join ' . tablename('sz_yi_member') . ' m on m.id=s.member_id where 1 ' . $condition . ' and w.status=1 or w.status=2';
	$list = pdo_fetchall($sql, $params);
	$total = pdo_fetchcolumn('select count(*) from ' . tablename('sz_yi_cashier_store') . ' s left join ' . tablename('sz_yi_cashier_withdraw') . ' w on s.id=w.cashier_store_id left join ' . tablename('sz_yi_member') . ' m on m.id=s.member_id where 1 ' . $condition . ' and w.status=0 or w.status=2', $params);
	$pager = pagination($total, $pindex, $psize);
}

load()->func('tpl');
include $this->template('withdraw');

?>
