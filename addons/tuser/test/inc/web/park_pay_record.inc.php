<?php

/**
 * 停车记录
 */
global $_W, $_GPC;

$filters = array();
$filters['uniacid'] = $_W['uniacid'];
$filters['nickname'] = $_GPC['nickname'];

$pindex = intval($_GPC['page']);
$pindex = max($pindex, 1);
$psize = 15;

require_once WXZ_SHOPPINGMALL . '/source/Order.class.php';

$orderTypes = Order::$types;
$status = Order::$status;
$park_pay_types = Order::$park_pay_types;

$start = ($pindex - 1) * $psize;
$condition = '`uniacid`=:uniacid AND type=1';
$pars = array();
$pars[':uniacid'] = $_W['uniacid'];

$sql = "SELECT count(*) as num FROM " . tablename('wxz_shoppingmall_order') . " WHERE {$condition}";
$total = pdo_fetchcolumn($sql, $pars);

$sql = "SELECT * FROM " . tablename('wxz_shoppingmall_order') . " WHERE {$condition} ORDER BY `create_at` DESC limit $start , $psize";
$list = pdo_fetchall($sql, $pars);
$pager = pagination($total, $pindex, $psize);

foreach ($list as $i => $_row) {
    $fans_info_sql = "SELECT username,mobile FROM " . tablename('wxz_shoppingmall_fans') . " WHERE uid={$_row['fans_id']}";
    $fans_info = pdo_fetch($fans_info_sql);
    $list[$i]['fans_info'] = $fans_info;
}

include $this->template('web/park_pay_record');
?>
