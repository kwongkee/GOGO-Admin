<?php

//活动奖品列表
global $_W, $_GPC;
include dirname(__FILE__) . '/permission.php';

$modulePublic = '../addons/' . $_GPC['m'] . '/static/';

if (!$user['mobile']) {
    header('Location: ' . $this->createMobileUrl('reg'));
    exit;
}

$condition = "`uniacid`=:uniacid AND fans_id={$user['uid']} AND type=2";
$pars = array();
$pars[':uniacid'] = $_W['uniacid'];

$sql = "SELECT * FROM " . tablename('wxz_shoppingmall_credit_log') . " WHERE {$condition} ORDER BY `id` DESC";
$list = pdo_fetchall($sql, $pars);

foreach ($list as &$row) {
    switch ($row['event_type']) {
        case 1:
            $row['icon'] = $modulePublic . 'credit/img/index6-1/dhcp.png';
            $row['exchange'] = '产品兑换';
            break;
        case 2:
            $row['icon'] = $modulePublic . 'credit/img/index6-1/dhyhq.png';
            $row['exchange'] = '优惠券兑换';
            break;
    }
}

include $this->template('credit_exchange_record');
?>
