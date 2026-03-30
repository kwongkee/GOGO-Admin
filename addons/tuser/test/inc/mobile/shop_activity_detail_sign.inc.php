<?php

//店面活动详情，可报名页面
global $_W, $_GPC;
include dirname(__FILE__) . '/permission.php';

$modulePublic = '../addons/' . $_GPC['m'] . '/static/';

if (!$user['mobile']) {
    header('Location: ' . $this->createMobileUrl('reg'));
    exit;
}

$id = $_GPC['id'];

$shop_activity_info_sql = "SELECT * FROM " . tablename('wxz_shoppingmall_shop_activity') . " WHERE uniacid={$_W['uniacid']} AND id={$id} AND isdel=0"; //店面列表
$shop_activity_info = pdo_fetch($shop_activity_info_sql);

$shop_info_sql = "SELECT * FROM " . tablename('wxz_shoppingmall_shop') . " WHERE uniacid={$_W['uniacid']} AND id={$shop_activity_info['shop_id']} AND isdel=0"; //店面列表
$shop_info = pdo_fetch($shop_info_sql);

$sign_info_sql = "SELECT * FROM " . tablename('wxz_shoppingmall_activity_sign') . " WHERE fans_id={$user['uid']} AND activity_id={$id}"; 
$sign_info = pdo_fetch($sign_info_sql);

include $this->template('shop_activity_detail_sign');
?>
