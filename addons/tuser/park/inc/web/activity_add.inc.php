<?php

global $_W, $_GPC;

if (checksubmit()) {
    //字段验证, 并获得正确的数据$dat
    $data = array(
        'title' => $_GPC['title'],
        'shop_id' => $_GPC['shop_id'],
        'uniacid' => $_W['uniacid'],
        'img' => $_GPC['img'],
        'issign' => $_GPC['issign'],
        'desc' => $_GPC['desc'],
        'link' => $_GPC['link'],
        'create_at' => time(),
    );
    if (pdo_insert('wxz_shoppingmall_activity', $data)) {
        message('添加成功', $this->createWebUrl('activity'));
    } else {
        message('添加失败', $this->createWebUrl('activity_add'));
    }
}
//获取所有商铺
require_once WXZ_SHOPPINGMALL . '/source/Shop.class.php';
$shops = Shop::getShopList();
load()->web('tpl');
include $this->template('web/activity_add');
?>
