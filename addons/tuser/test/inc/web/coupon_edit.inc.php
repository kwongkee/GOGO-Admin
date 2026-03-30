<?php

global $_W, $_GPC;
require_once WXZ_SHOPPINGMALL . '/source/Fans.class.php';
$_W['module_setting'] = $this->module['config'];
$levels = Fans::getLevels();
$id = $_GPC['id'];
$coupon_info_sql = "SELECT * FROM " . tablename('wxz_shoppingmall_coupon') . " WHERE id={$id}";
$coupon_info = pdo_fetch($coupon_info_sql);

if (!$coupon_info) {
    message('优惠券不存在', $this->createWebUrl('coupon'));
}
load()->web('tpl');
$_GPC['total_num'] = $_GPC['total_num'] > 0 ? intval($_GPC['total_num']) : 0;
if (checksubmit()) {
    //字段验证, 并获得正确的数据$dat
    $data = array(
        'name' => $_GPC['name'],
        'img' => $_GPC['img'],
        'shop_id' => $_GPC['shop_id'],
        'desc' => $_GPC['desc'],
        'store' => $_GPC['store'],
        'expiry_date_end' => $_GPC['expiry_date_end'],
        'total_num' => $_GPC['total_num'],
        'status' => $_GPC['status'],
    );

    if ($_GPC['total_num'] < $coupon_info['cashed']) {
        message('总数量不能小于已兑换数量', $this->createWebUrl('coupon_edit', array('id' => $id)));
    }

    //加库存
    if ($_GPC['total_num'] > $coupon_info['total_num']) {
        $data['num'] = $coupon_info['num'] + ($_GPC['total_num'] - $coupon_info['total_num']);
    } else {
        //减库存
        $data['num'] = $coupon_info['num'] - ($coupon_info['total_num'] - $_GPC['total_num']);
    }

    if (pdo_update('wxz_shoppingmall_coupon', $data, array('id' => $id))) {
        message('更新成功', $this->createWebUrl('coupon'));
    } else {
        message('更新失败', $this->createWebUrl('coupon_edit', array('id' => $id)));
    }
}
//获取所有商铺
require_once WXZ_SHOPPINGMALL . '/source/Shop.class.php';
$shops = Shop::getShopList();
include $this->template('web/coupon_edit');
?>
