<?php

//用户兑换奖品列表
global $_W, $_GPC;
include dirname(__FILE__) . '/permission.php';

$modulePublic = '../addons/' . $_GPC['m'] . '/static/';

if (!$user['mobile']) {
    header('Location: ' . $this->createMobileUrl('reg'));
    exit;
}

$tableLog = tablename('wxz_shoppingmall_credit_log');
$tableAward = tablename('wxz_shoppingmall_award');
$nowDate = date('Y-m-d H:i:s');
$condition = "$tableLog.uniacid={$_W['uniacid']} AND fans_id={$user['uid']} AND event_type=1 AND $tableAward.isdel=0";

if($_GPC['status']) {
    $condition .= " AND $tableLog.status={$_GPC['status']}";
}

$sql = "SELECT *,$tableLog.status log_status,$tableLog.id log_id FROM $tableLog left join $tableAward on $tableLog.award_credit_id=$tableAward.id   WHERE {$condition} ORDER BY $tableLog.status asc,$tableLog.`id` DESC";
$list = pdo_fetchall($sql, $pars);

include $this->template('user_award_list_credit');
?>
