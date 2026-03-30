<?php

global $_W, $_GPC;

$id = $_GPC['id'];
$activity_info = pdo_fetch($activity_info_sql);
if (!$activity_info) {
    message('奖品不存在', $this->createWebUrl('activity'));
}
load()->web('tpl');
if (checksubmit()) {
    //字段验证, 并获得正确的数据$dat
    $data = array(
        'title' => $_GPC['title'],
        'shop_id' => (int) $_GPC['shop_id'],
        'issign' => (int) $_GPC['issign'],
        'img' => $_GPC['img'],
        'desc' => $_GPC['desc'],
        'link' => $_GPC['link'],
    );

    if (pdo_update('wxz_shoppingmall_activity', $data, array('id' => $id))) {
        message('更新成功', $this->createWebUrl('activity'));
    } else {
        message('更新失败', $this->createWebUrl('activity_add'));
    }
}
//获取所有商铺
require_once WXZ_SHOPPINGMALL . '/source/Shop.class.php';
$shops = Shop::getShopList();
include $this->template('web/activity_edit');
?>
