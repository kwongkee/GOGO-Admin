<?php

//活动奖品列表
global $_W, $_GPC;
include dirname(__FILE__) . '/permission.php';

$modulePublic = '../addons/' . $_GPC['m'] . '/static/';

if (!$user['mobile']) {
    header('Location: ' . $this->createMobileUrl('reg'));
    exit;
}

//广告
$index_info_sql = "SELECT * FROM " . tablename('wxz_shoppingmall_index') . " WHERE uniacid={$_W['uniacid']} AND type=2 limit 1";
$index_info = pdo_fetch($index_info_sql);

$banner_num = 6;
$index_info['banners'] = array();

for ($i = 1; $i <= $banner_num; $i++) {
    if ($index_info["banner{$i}"]) {
        $index_info["banners"][] = explode(',', $index_info["banner{$i}"]);
    } else {
        unset($index_info["banner{$i}"]);
    }
}

$outSort = $_GPC['sort'] == 'up' ? 'down' : 'up'; //反向排序
$sort = $outSort == 'up' ? 'ASC' : 'DESC';
$type = $_GPC['type'] == 'coupon' ? 'coupon' : 'award'; //反向排序
$couponList = $awardList = array();
$order = $_GPC['order'] ? $_GPC['order'] : 'id';

//优惠券列表
if ($type == 'coupon') {
    $nowDate = date('Y-m-d H:i:s');
    $condition = "`uniacid`=:uniacid AND isdel=0";
    $pars = array();
    $pars[':uniacid'] = $_W['uniacid'];

    $sql = "SELECT * FROM " . tablename('wxz_shoppingmall_coupon') . " WHERE {$condition} ORDER BY $order $sort";
    $couponList = pdo_fetchall($sql, $pars);
}

//产品列表
if ($type == 'award') {
    $condition = '`uniacid`=:uniacid AND isdel=0 AND num>0';
    $pars = array();
    $pars[':uniacid'] = $_W['uniacid'];
    $sql = "SELECT * FROM " . tablename('wxz_shoppingmall_award') . " WHERE {$condition} ORDER BY $order $sort";
    $awardList = pdo_fetchall($sql, $pars);
}

include $this->template('award_list');
?>
