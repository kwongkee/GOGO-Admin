<?php

global $_W;
global $_GPC;
/**
 * 小票生成
 */
class GenerateSmallTicket{
    public function getOrderInfo($ordersn)
    {
        return pdo_get('sz_yi_order',['ordersn'=>$ordersn],['id','ordersn','price','logistics_order_declinfo']);
    }
    public function getGoodsInfo($orderid)
    {
        return pdo_fetchall('select a1.total,a1.price,a2.title from '.tablename('sz_yi_order_goods').' as a1 left join '.tablename('sz_yi_goods').' as a2 on a1.goodsid=a2.id where a1.orderid='.$orderid);
    }
    public function getMerchant($sid)
    {
        $user= pdo_get('sz_yi_perm_user',['uid'=>$sid],['realname']);
        return pdo_get('total_merchant_account',['company_name'=>$user['realname']],['company_name','company_tel','address']);
    }
}
$ticket= new GenerateSmallTicket();
$order = $ticket->getOrderInfo($_GPC['ordersn']);
$order['logistics_order_declinfo']=json_decode($order['logistics_order_declinfo'],true);
$goods = $ticket->getGoodsInfo($order['id']);
$merchant = $ticket->getMerchant($order['logistics_order_declinfo']['shipperId']);
foreach ($goods as $item){
    $order['totalGoodsNum']+=$item['total'];
}
include $this->template('ticket1');