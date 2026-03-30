<?php

//兑换奖品
global $_W, $_GPC;
include dirname(__FILE__) . '/permission.php';

$modulePublic = '../addons/' . $_GPC['m'] . '/static/';

if (!$user['mobile']) {
    header('Location: ' . $this->createMobileUrl('reg'));
    exit;
}

$award_id = $_GPC['id'];
$award_info_sql = "SELECT * FROM " . tablename('wxz_shoppingmall_award') . " WHERE id={$award_id} AND uniacid={$_W['uniacid']}";
$award_info = pdo_fetch($award_info_sql);
if (!$award_info) {
    message("积分奖品不存在或库存不足", $this->createMobileUrl('award_list_credit'));
}

$shop_info_sql = "SELECT * FROM " . tablename('wxz_shoppingmall_shop') . " WHERE uniacid={$_W['uniacid']} AND id={$award_info['shop_id']}"; //店面列表
$shop_info = pdo_fetch($shop_info_sql);

include $this->template('award_credit_detail');
?>
