<?php

// 模块LTD提供
if (!defined('IN_IA')) {
    exit('Access Denied');
}

global $_W;
global $_GPC;
$title = '订购详情';

if (isset($_GPC['oid'])) {
    $order = pdo_get('sz_yi_order', ['ordersn' => $_GPC['oid'], 'openid' => $_W['openid']], ['id', 'ordersn', 'price', 'createtime', 'offline_pay_price', 'logistics_status', 'logistics_time']);
    $goods = pdo_fetchall('SELECT a1.total, a1.price, a2.title, a3.value
FROM
    ims_sz_yi_order_goods AS a1
        LEFT JOIN
    ims_sz_yi_goods AS a2 ON a1.goodsid = a2.id
        LEFT JOIN
    ims_sz_yi_goods_param AS a3 ON a1.goodsid = a3.goodsid
WHERE
    a1.orderid = :orderid AND a3.title = "型号规格";', [':orderid' => $order['id']]);
} else {
    $order = pdo_get('sz_yi_order', ['id' => $_GPC['id'], 'openid' => $_W['openid']], ['id', 'ordersn', 'price', 'createtime', 'offline_pay_price', 'logistics_status', 'logistics_time']);
    $goods = pdo_fetchall('SELECT a1.total, a1.price, a2.title, a3.value
FROM
    ims_sz_yi_order_goods AS a1
        LEFT JOIN
    ims_sz_yi_goods AS a2 ON a1.goodsid = a2.id
        LEFT JOIN
    ims_sz_yi_goods_param AS a3 ON a1.goodsid = a3.goodsid
WHERE
    a1.orderid = :orderid AND a3.title = "型号规格";', [':orderid' => $order['id']]);
}

include $this->template('order/easy_deliver_orderdetail');