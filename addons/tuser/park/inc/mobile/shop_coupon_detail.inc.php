<?php

//店面优惠券详情页面
global $_W, $_GPC;
include dirname(__FILE__) . '/permission.php';

$modulePublic = '../addons/' . $_GPC['m'] . '/static/';

if (!$user['mobile']) {
    header('Location: ' . $this->createMobileUrl('reg'));
    exit;
}

$id = $_GPC['id'];

$shop_coupon_info_sql = "SELECT * FROM " . tablename('wxz_shoppingmall_coupon') . " WHERE uniacid={$_W['uniacid']} AND id={$id}"; //店面列表
$shop_coupon_info = pdo_fetch($shop_coupon_info_sql);

$shop_info_sql = "SELECT * FROM " . tablename('wxz_shoppingmall_shop') . " WHERE uniacid={$_W['uniacid']} AND id={$shop_coupon_info['shop_id']}"; //店面列表
$shop_info = pdo_fetch($shop_info_sql);

$_W['module_setting'] = $this->module['config'];
$levels = Fans::getLevels();

//判断优惠券是否在有效期内
$starttime = strtotime($shop_coupon_info['expiry_date_start']);
$endtime = strtotime($shop_coupon_info['expiry_date_end']);
$isValid = true;
if (($endtime && time() > $endtime) || ($starttime && time() < $starttime)) {
    $isValid = false;
}

include $this->template('shop_coupon_detail');
?>
