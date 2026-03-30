<?php

//活动奖品列表
global $_W, $_GPC;
include dirname(__FILE__) . '/permission.php';

$modulePublic = '../addons/' . $_GPC['m'] . '/static/';

if (!$user['mobile']) {
    header('Location: ' . $this->createMobileUrl('reg'));
    exit;
}

$condition = "`uniacid`=:uniacid AND fans_id={$user['uid']} AND type=1";
$pars = array();
$pars[':uniacid'] = $_W['uniacid'];

$sql = "SELECT * FROM " . tablename('wxz_shoppingmall_credit_log') . " WHERE {$condition} ORDER BY `id` DESC";
$list = pdo_fetchall($sql, $pars);

include $this->template('credit_record');
?>
