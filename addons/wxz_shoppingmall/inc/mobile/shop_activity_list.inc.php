<?php

//商户，商场活动列表
global $_W, $_GPC;
include dirname(__FILE__) . '/permission.php';

$modulePublic = '../addons/' . $_GPC['m'] . '/static/';

if (!$user['mobile']) {
    header('Location: ' . $this->createMobileUrl('reg'));
    exit;
}

$type = $_GPC['type'] ? (int) $_GPC['type'] : 1;

$shop_activity_list_sql = "SELECT * FROM " . tablename('wxz_shoppingmall_shop_activity') . " WHERE uniacid={$_W['uniacid']} AND type='$type' AND isdel=0 order by create_at desc"; //店面列表
$list = pdo_fetchall($shop_activity_list_sql);

require_once WXZ_SHOPPINGMALL . '/source/Shop.class.php';
$date_status = Shop::$activityDateStatus;
foreach ($list as $k => $row) {
    $shop_info_sql = "SELECT * FROM " . tablename('wxz_shoppingmall_shop') . " WHERE id={$row['shop_id']}";
    $list[$k]['shop'] = pdo_fetch($shop_info_sql);
    $list[$k]['date_status'] = Shop::getDateStatus($row['expiry_date_start'], $row['expiry_date_end']);
}

include $this->template("shop_activity_list{$type}");
?>
