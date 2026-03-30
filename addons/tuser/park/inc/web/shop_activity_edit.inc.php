<?php

/**
 * 商铺编辑
 */
global $_W, $_GPC;

$id = $_GPC['id'];
$shop_activity_info_sql = "SELECT * FROM " . tablename('wxz_shoppingmall_shop_activity') . " WHERE id={$id}";
$shop_activity_info = pdo_fetch($shop_activity_info_sql);

if (!$shop_activity_info) {
    message('优惠券不存在', $this->createWebUrl('shop_activity'));
}


if (checksubmit()) {
    //字段验证, 并获得正确的数据$dat
    $data = array(
        'shop_id' => $_GPC['shop_id'],
        'title' => $_GPC['title'],
        'sub_title' => $_GPC['sub_title'],
        'img' => $_GPC['img'],
        'expiry_date_start' => $_GPC['expiry_date_start'],
        'expiry_date_end' => $_GPC['expiry_date_end'],
        'type' => $_GPC['type'],
        'issign' => $_GPC['issign'],
        'can_sign_num' => $_GPC['can_sign_num'],
        'desc' => $_GPC['desc'],
        'create_at' => time(),
    );

    if (pdo_update('wxz_shoppingmall_shop_activity', $data, array('id' => $id))) {
        message('更新成功', $this->createWebUrl('shop_activity'));
    } else {
        message('更新失败', $this->createWebUrl('shop_activity_edit', array('id' => $id)));
    }
}

//获取所有商铺
require_once WXZ_SHOPPINGMALL . '/source/Shop.class.php';
$shops = Shop::getShopList();
load()->web('tpl');
include $this->template('web/shop_activity_edit');
?>
