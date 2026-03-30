<?php

//活动报名
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


include $this->template('activity_sign');
?>
