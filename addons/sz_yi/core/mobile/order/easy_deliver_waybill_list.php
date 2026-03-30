<?php


if (!defined('IN_IA')) {
    exit('Access Denied');
}
global $_W;
global $_GPC;
$title = '运单列表';
$page = $_GPC['page']!=""?$_GPC['page']:1;
$limit = 10;
$page = ($page-1)*$limit;
$row = pdo_fetchall("select expresssn from ".tablename('sz_yi_order')." where openid=:openid and logistics_order_type=10 and order_print_status=20 limit {$page},{$limit}",[":openid"=>$_W['openid']]);
if (!$_W['isajax']){
    include $this->template('order/easy_deliver_waybill_list');
}else{
    show_json(0,$row);
}
