<?php

//兑换奖品
global $_W, $_GPC;
include dirname(__FILE__) . '/permission.php';

$modulePublic = '../addons/' . $_GPC['m'] . '/static/';

if (!$user['mobile']) {
    header('Location: ' . $this->createMobileUrl('reg'));
    exit;
}

$id = $_GPC['id'];
$log_sql = "SELECT * FROM " . tablename('wxz_shoppingmall_credit_log') . " WHERE id={$id} AND uniacid={$_W['uniacid']} AND fans_id={$user['uid']}";
$log_info = pdo_fetch($log_sql);

if (!$log_info) {
    message("优惠券不存在", $this->createMobileUrl('user_award_list_coupon'));
}

$award_id = $log_info['award_coupon_id'];

$award_info_sql = "SELECT * FROM " . tablename('wxz_shoppingmall_coupon') . " WHERE id={$award_id} AND uniacid={$_W['uniacid']}";
$award_info = pdo_fetch($award_info_sql);

if (!$award_info) {
    message("优惠券不存在或已作废", $this->createMobileUrl('user_award_list_coupon'));
}

$shop_info_sql = "SELECT * FROM " . tablename('wxz_shoppingmall_shop') . " WHERE id={$award_info['shop_id']}"; //店面列表
$shop_info = pdo_fetch($shop_info_sql);

include $this->template('user_coupon_verification');
?>
