<?php

/**
 * 订单列表
 */
global $_W, $_GPC;

$filters = array();
$filters['uniacid'] = $_W['uniacid'];
$filters['nickname'] = $_GPC['nickname'];

$pindex = intval($_GPC['page']);
$pindex = max($pindex, 1);
$psize = 15;

require_once WXZ_SHOPPINGMALL . '/source/Order.class.php';
require_once WXZ_SHOPPINGMALL . '/source/Fans.class.php';

$orderTypes = Order::$types;
$status = Order::$status;
$park_pay_types = Order::$park_pay_types;

$start = ($pindex - 1) * $psize;
$condition = '`uniacid`=:uniacid';

if ($_GPC['mobile']) {
    $fans = new Fans();
    $fansInfo = $fans->getByMobile($_GPC['mobile']);
    if (!$fansInfo) {
        $condition .= " AND 1!=1";
    } else {
        $condition .= " AND fans_id={$fansInfo['uid']}";
    }
}

if ($_GPC['status']) {
    $condition .= " AND status={$_GPC['status']}";
}

if ($_GPC['type']) {
    $condition .= " AND type={$_GPC['type']}";
}

//开始时间，结束时间
if ($_GPC['order_time_start']) {
    $order_time_start = strtotime($_GPC['order_time_start'] . ' 00:00:00');
    $condition .= " AND success_at>='{$order_time_start}'";
}
//结束时间
if ($_GPC['order_time_end']) {
    $order_time_end = strtotime($_GPC['order_time_end'] . ' 00:00:00');
    $condition .= " AND success_at>='{$order_time_end}'";
}



$pars = array();
$pars[':uniacid'] = $_W['uniacid'];

$sql = "SELECT count(*) as num FROM " . tablename('wxz_shoppingmall_order') . " WHERE {$condition}";
$total = pdo_fetchcolumn($sql, $pars);

$sql = "SELECT * FROM " . tablename('wxz_shoppingmall_order') . " WHERE {$condition} ORDER BY `create_at` DESC limit $start , $psize";
$list = pdo_fetchall($sql, $pars);
$pager = pagination($total, $pindex, $psize);

include $this->template('web/order_list');
?>
