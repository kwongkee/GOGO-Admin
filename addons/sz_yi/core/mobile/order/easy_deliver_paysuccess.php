<?php


// 模块LTD提供
if (!defined('IN_IA')) {
    exit('Access Denied');
}

global $_W;
global $_GPC;
$title = '支付结果';
if (!isset($_GPC['oid'])||$_GPC['oid']==""){
    message('无权限请求');
}
$_GPC['oid'] = trim($_GPC['oid']);
$order =pdo_get('sz_yi_order',['ordersn'=>$_GPC['oid'],'openid'=>$_W['openid']],['id','ordersn','price','offline_pay_price','createtime','status']);
if (empty($order)){
    message('暂无订单号');
}
if ($order['status']<1){
    message('请完成支付');
}
include $this->template('order/easy_deliver_paysuccess');