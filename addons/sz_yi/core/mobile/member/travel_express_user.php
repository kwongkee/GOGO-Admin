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
    include $this->template('member/travel_express/user');
}else if($operation == 'submit' && $_W['ispost']) {
    
    $data['realname'] = $_GPC['realname'];
    $data['mobile'] = $_GPC['mobile'];
    $data['email'] = $_GPC['email'];
    $data['gender'] = $_GPC['gender'];
    pdo_update('sz_yi_member', $data, array('id' => $member['id'], 'uniacid' => $_W['uniacid']));

    show_json(1, array('msg' => '修改成功'));
}
