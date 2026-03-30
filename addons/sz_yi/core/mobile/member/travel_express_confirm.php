<?php

defined('IN_IA') or exit('Access Denied');

global $_W;
global $_GPC;

$operation = (!empty($_GPC['op']) ? $_GPC['op'] : 'display');
$openid = m('user')->getOpenid();
$popenid = m('user')->islogin();
$openid = ($openid ? $openid : $popenid);
$member = m('member')->getMember($openid);

if ($operation == 'display') {
    $id = intval($_GPC['id']);
    $order = pdo_fetch('select * from ' . tablename('customs_travelexpress_order_info') . ' where id=:id ORDER BY `id` ASC limit 1 ', array(':id' => $id));

    $condition = ' and openid=:openid and uniacid=:uniacid and ordersn=:ordersn ';
    $params = array('openid'=>$openid, 'uniacid'=>$_W['uniacid'], 'ordersn'=>$order['ordersn']);
    $sql = 'SELECT * FROM ' . tablename('customs_travelexpress_order') . ' where 1 ' . $condition . ' ORDER BY `id` ASC ';
    $orders = pdo_fetchall($sql, $params);
    foreach ($orders as $k => $v){
        $cates = pdo_fetch('select * from ' . tablename('customs_travelexpress_cates') . ' where id=:id ORDER BY `id` ASC limit 1 ', array(':id' => $v['cates_c']));
        $orders[$k]['cates_text'] = $cates['name'];
    }
    include $this->template('member/travel_express/confirm');
}else if($operation == 'submit' && $_W['ispost']) {
    $id = intval($_GPC['id']);

    $old_data =  pdo_fetch('select * from ' . tablename('customs_travelexpress_order_info') . ' where id=:id ORDER BY `id` ASC limit 1 ', array(':id' => $id));

    if($old_data['status']==3)
    {
        show_json(1, array('msg' => '该订单已确认，请勿重复提交！'));
    }
    $data['status'] = 3;
    pdo_update('customs_travelexpress_order_info', $data, array('id' => $id, 'uniacid' => $_W['uniacid']));
    show_json(1, array('msg' => '您已提交确认！'));
}