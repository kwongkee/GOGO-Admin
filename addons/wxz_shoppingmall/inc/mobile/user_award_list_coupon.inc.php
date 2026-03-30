<?php

//用户兑换优惠券列表
global $_W, $_GPC;
include dirname(__FILE__) . '/permission.php';

$modulePublic = '../addons/' . $_GPC['m'] . '/static/';

if (!$user['mobile']) {
    header('Location: ' . $this->createMobileUrl('reg'));
    exit;
}

$tableLog = tablename('wxz_shoppingmall_credit_log');
$tableAward = tablename('wxz_shoppingmall_coupon');
$nowDate = date('Y-m-d H:i:s');
$condition = "$tableLog.uniacid={$_W['uniacid']} AND $tableAward.isdel=0 AND fans_id={$user['uid']} AND event_type=2 AND expiry_date_start<='{$nowDate}' AND expiry_date_end>='{$nowDate}'";

if ($_GPC['status']) {
    $condition .= " AND $tableLog.status={$_GPC['status']}";
}

$sql = "SELECT *,$tableLog.status log_status,$tableLog.id log_id FROM $tableLog left join $tableAward on $tableLog.award_coupon_id=$tableAward.id   WHERE {$condition} ORDER BY $tableLog.status asc,$tableLog.`id` DESC";
$list = pdo_fetchall($sql, $pars);

foreach ($list as $k => $row) {
    $shop_info_sql = "SELECT * FROM " . tablename('wxz_shoppingmall_shop') . " WHERE uniacid={$_W['uniacid']} AND id={$row['shop_id']}";
    $list[$k]['shop_info'] = pdo_fetch($shop_info_sql);
}
include $this->template('user_award_list_coupon');
?>
