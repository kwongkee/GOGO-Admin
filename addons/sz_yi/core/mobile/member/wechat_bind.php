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
    if( $_GPC['uid'] == '' || $_GPC['type'] == '' )
    {
        echo '缺少参数';
    }else{
        $uri_info = [
            'uid' => $_GPC['uid'],
            'type' => $_GPC['type']
        ];
        include $this->template('member/wechat_bind/bind');
    }
}else if($operation == 'submit' && $_W['ispost']) {
    
    $data['openid'] = $openid;
    $data['wechat_uid'] = $member['id'];
    
    $type = $_GPC['type'];
    switch ($type) {
        case 'agents' :
            pdo_update('customs_agents_admin', $data, array('id' => $_GPC['uid'] ));
        break;
    }
    // 发送通知
    show_json(1, array('msg' => '绑定成功,请关闭页面'));
}
