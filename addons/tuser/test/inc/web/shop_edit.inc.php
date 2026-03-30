<?php

/**
 * 商铺编辑
 */
global $_W, $_GPC;
require_once WXZ_SHOPPINGMALL . '/source/Shop.class.php';

$id = $_GPC['id'];
$shop_info_sql = "SELECT * FROM " . tablename('wxz_shoppingmall_shop') . " WHERE id={$id}";
$shop_info = pdo_fetch($shop_info_sql);

if (!$shop_info) {
    message('优惠券不存在', $this->createWebUrl('shop'));
}


if (checksubmit()) {
    //字段验证, 并获得正确的数据$dat
    $data = array(
        'img_list' => $_GPC['img_list'],
        'name' => $_GPC['name'],
        'floor' => $_GPC['floor'],
        'category' => $_GPC['category'],
        'img' => $_GPC['img'],
        'logo' => $_GPC['logo'],
        'tel' => $_GPC['tel'],
        'good_num' => $_GPC['good_num'],
        'desc' => $_GPC['desc'],
    );

    if (pdo_update('wxz_shoppingmall_shop', $data, array('id' => $id))) {
        message('更新成功', $this->createWebUrl('shop'));
    } else {
        message('更新失败', $this->createWebUrl('shop_edit', array('id' => $id)));
    }
}
load()->web('tpl');
$shopCategorys = Shop::getCategorys();
include $this->template('web/shop_edit');
?>
