<?php

require_once WXZ_SHOPPINGMALL . '/source/Fans.class.php';

global $_W, $_GPC;
$_W['module_setting'] = $this->module['config'];
$levels = Fans::getLevels();

if (checksubmit()) {
    //字段验证, 并获得正确的数据$dat
    $data = array(
        'name' => $_GPC['name'],
        'shop_id' => $_GPC['shop_id'],
        'uniacid' => $_W['uniacid'],
        'img' => $_GPC['img'],
        'desc' => $_GPC['desc'],
        'store' => $_GPC['store'],
        'level' => $_GPC['level'],
        'expiry_date_end' => $_GPC['expiry_date_end'],
        'total_num' => $_GPC['total_num'],
        'num' => $_GPC['total_num'],
        'create_at' => time(),
    );
    if (pdo_insert('wxz_shoppingmall_coupon', $data)) {
        message('添加成功', $this->createWebUrl('coupon'));
    } else {
        message('添加失败', $this->createWebUrl('coupon_add'));
    }
}

//获取所有商铺
require_once WXZ_SHOPPINGMALL . '/source/Shop.class.php';
$shops = Shop::getShopList();
load()->web('tpl');
include $this->template('web/coupon_add');
?>
