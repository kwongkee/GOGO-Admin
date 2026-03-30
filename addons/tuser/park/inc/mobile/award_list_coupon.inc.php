<?php

//活动奖品列表
global $_W, $_GPC;
include dirname(__FILE__) . '/permission.php';

$modulePublic = '../addons/' . $_GPC['m'] . '/static/';

if (!$user['mobile']) {
    header('Location: ' . $this->createMobileUrl('reg'));
    exit;
}

header('Location: ' . $this->createMobileUrl('award_list'));
exit;

$nowDate = date('Y-m-d H:i:s');
$condition = "`uniacid`=:uniacid AND isdel=0";
$pars = array();
$pars[':uniacid'] = $_W['uniacid'];

$sql = "SELECT * FROM " . tablename('wxz_shoppingmall_coupon') . " WHERE {$condition} ORDER BY `id` DESC";
$list = pdo_fetchall($sql, $pars);

include $this->template('award_list_coupon');
?>
