<?php

//店面详情页面
global $_W, $_GPC;
include dirname(__FILE__) . '/permission.php';
require_once WXZ_SHOPPINGMALL . '/source/Page.class.php';

$modulePublic = '../addons/' . $_GPC['m'] . '/static/';

if (!$user['mobile']) {
    header('Location: ' . $this->createMobileUrl('reg'));
    exit;
}

$id = $_GPC['id'];
$shop_activity_info_sql = "SELECT * FROM " . tablename('wxz_shoppingmall_shop_activity') . " WHERE uniacid={$_W['uniacid']} AND shop_id={$id} AND isdel=0";
$shop_activitys = pdo_fetchall($shop_activity_info_sql); //店面优惠券活动

$shop_coupon_info_sql = "SELECT * FROM " . tablename('wxz_shoppingmall_coupon') . " WHERE uniacid={$_W['uniacid']} AND shop_id={$id} AND isdel=0";
$shop_coupons = pdo_fetchall($shop_coupon_info_sql); //店面优惠券

$shop_info_sql = "SELECT * FROM " . tablename('wxz_shoppingmall_shop') . " WHERE uniacid={$_W['uniacid']} AND id={$id}"; //店面列表
$shop_info = pdo_fetch($shop_info_sql);

$shopingMallAdd = Page::getPage(1);
$config = $this->module['config'];
include $this->template('shop_detail');
?>
