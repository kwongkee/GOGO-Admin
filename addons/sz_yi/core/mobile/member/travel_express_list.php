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
    //获取未提交的订单
    $condition = ' and openid=:openid and uniacid=:uniacid and status=0 ';
    $params = array('openid'=>$openid, 'uniacid'=>$_W['uniacid']);
    $sql = 'SELECT * FROM ' . tablename('customs_travelexpress_order') . ' where 1 ' . $condition . ' ORDER BY `id` ASC ';
    $order = pdo_fetchall($sql, $params);
    
    $condition_a = ' and openid=:openid and deleted=0 and  `uniacid` = :uniacid  ';
    $params_a = array(':uniacid' => $_W['uniacid'], ':openid' => $openid);
    $sql_a = 'SELECT * FROM ' . tablename('sz_yi_member_address') . ' where 1 ' . $condition_a . ' ORDER BY `id` DESC ';
    $address = pdo_fetchall($sql_a, $params_a);
    include $this->template('member/travel_express/list');
}else if($operation == 'submit' && $_W['ispost']) {
    $data['openid'] = $openid;
    $data['uniacid'] = $_W['uniacid'];
    $data['ordersn'] = $_GPC['ordersn'];
    $data['select_type'] = $_GPC['select_type'];
    $data['smart_code'] = $_GPC['smart_code'];
    $data['warehouse'] = $_GPC['warehouse'];
    $data['address'] = $_GPC['address'];
    $data['collect_address'] = $_GPC['collect_address'];
    $data['collect_id'] = $_GPC['collect_id'];
    
    $old_data = pdo_fetch('select * from ' . tablename('customs_travelexpress_order_info') . ' where ordersn=:ordersn and uniacid=:uniacid ORDER BY `id` ASC limit 1 ', array(':uniacid' => $_W['uniacid'],'ordersn' =>$data['ordersn'] ));
    //查询原有数据
    if($old_data)
    {
        switch ($old_data['status'])
        {
            case 0:
                show_json(0, array('msg' => '该订单已经提交审核'));
                break;  
            case 1:
                show_json(0, array('msg' => '该订单已经审核通过'));
                break;
            case 2:
                $data['status'] = 0;
                pdo_update('customs_travelexpress_order_info', $data, array('id' => $old_data['id'], 'uniacid' => $_W['uniacid']));
                pdo_fetch('UPDATE ' . tablename('customs_travelexpress_order') . ' SET status=1 where ordersn ='.$data['ordersn']);
                show_json(1, array('msg' => '提交成功'));
                break;
        }
    }else{
        $data['create_time'] = time();
        pdo_insert('customs_travelexpress_order_info', $data);
        pdo_fetch('UPDATE ' . tablename('customs_travelexpress_order') . ' SET status=1 where ordersn ='.$data['ordersn']);
        show_json(1, array('msg' => '提交成功'));
    }
}else if($operation == 'del' && $_W['ispost']) {
    $id = $_GPC['id'];
    pdo_delete('customs_travelexpress_order', array('id' => $id));
    show_json(1, array('msg' => '删除成功'));
}
