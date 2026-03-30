<?php

/**
 * 添加商铺
 */
global $_W, $_GPC;
require_once WXZ_SHOPPINGMALL . '/source/Shop.class.php';

if (checksubmit()) {
    //字段验证, 并获得正确的数据$dat
    $data = array(
        'uniacid' => $_W['uniacid'],
        'img_list' => $_GPC['img_list'],
        'name' => $_GPC['name'],
        'floor' => $_GPC['floor'],
        'category' => $_GPC['category'],
        'img' => $_GPC['img'],
        'logo' => $_GPC['logo'],
        'tel' => $_GPC['tel'],
        'good_num' => $_GPC['good_num'],
        'desc' => $_GPC['desc'],
        'create_at' => time(),
    );

    if (pdo_insert('wxz_shoppingmall_shop', $data)) {
        $id = pdo_insertid();
        $data = array(
            'order' => $id,
        );
        pdo_update('wxz_shoppingmall_shop', $data, array('id' => $id));
        message('添加成功', $this->createWebUrl('shop'));
    } else {
        message('添加失败', $this->createWebUrl('shop_add'));
    }
}
load()->web('tpl');
$shopCategorys = Shop::getCategorys();
include $this->template('web/shop_add');
?>
