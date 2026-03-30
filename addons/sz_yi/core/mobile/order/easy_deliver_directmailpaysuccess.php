<?php

// 模块LTD提供
if (!defined('IN_IA')) {
    exit('Access Denied');
}
global $_W;
global $_GPC;


$orderId = pdo_get('customs_directpostage_order',['ordersn'=>$_GPC['oid'],'status'=>1],['oid']);
if (empty($orderId)){
    message('请完成支付');
}
$orderSn = pdo_get('sz_yi_order',['id'=>$orderId['oid']],['ordersn','logistics_order_declinfo']);
$orderSn['logistics_order_declinfo'] = json_decode($orderSn['logistics_order_declinfo'],true);
$recipient = pdo_get('member_family',['id'=>$orderSn['logistics_order_declinfo']['recipientId']]);
$goods = pdo_fetch('select sum(total)as total from '.tablename('sz_yi_order_goods').' where orderid='.$orderId['oid']);

include $this->template('order/pay/directmail_pay_success');
