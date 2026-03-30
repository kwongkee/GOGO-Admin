<?php

// 模块LTD提供
if (!defined('IN_IA')) {
    exit('Access Denied');
}

global $_W;
global $_GPC;





//获取订单
// $user = pdo_get('sz_yi_member', ['openid' => $_W['openid']], ['unionid']);
// $allUser = pdo_getall('sz_yi_member', ['unionid' => $user['unionid']], ['openid']);
$uid = "'".$_W['openid']."'";
// foreach ($allUser as $val) {
//     $uid .= "'".$val['openid'] . "',";
// }
// $uid = trim($uid, ',');
if(!$_W['isajax']){
    $allOrder = pdo_fetchall("select a1.ordersn,a2.clear_type,a2.pack_ordersn from " . tablename('sz_yi_order') . " as a1 left join ".tablename('customs_packing_list')." as a2 on a1.ordersn=a2.shop_ordersn where a1.logistics_order_type=20 and a1.order_print_status=10 and a1.openid in(".$uid.")");
    $title = '运单列印';
    include $this->template('order/easy_deliver_order_print');
}else{
    if (empty($_GPC['orderlist'])){
        show_json(0,'请选择订单');
    }
    foreach ($_GPC['orderlist'] as $value){
        $type = pdo_get('customs_packing_list',['shop_ordersn'=>$value],['clear_type']);
        if ($type['clear_type']=='BC'){
            pdo_insert('customs_elec_order_queue',[
                'queue'=>'electronicFace',
                'payload'=>$value,
                'create_time'=>time()
            ]);
        }
    }
    show_json(0,'已提交打印');
}
