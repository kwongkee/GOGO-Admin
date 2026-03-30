<?php

/**
 * 添加商铺活动
 */
global $_W, $_GPC;

if (checksubmit()) {
    //字段验证, 并获得正确的数据$dat
    $data = array(
        'uniacid' => $_W['uniacid'],
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
    if (pdo_insert('wxz_shoppingmall_shop_activity', $data)) {
        message('添加成功', $this->createWebUrl('shop_activity'));
    } else {
        message('添加失败', $this->createWebUrl('shop_activity_add'));
    }
}
load()->web('tpl');

//获取所有商铺
require_once WXZ_SHOPPINGMALL . '/source/Shop.class.php';
$shops = Shop::getShopList();

include $this->template('web/shop_activity_add');
?>
