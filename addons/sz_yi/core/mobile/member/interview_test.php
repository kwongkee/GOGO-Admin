<?php

defined('IN_IA') or exit('Access Denied');

global $_W;
global $_GPC;

session_start();
$operation = (!empty($_GPC['op']) ? $_GPC['op'] : 'display');
$openid = m('user')->getOpenid();
$popenid = m('user')->islogin();
$openid = ($openid ? $openid : $popenid);
$member = m('member')->getMember($openid);

if ($operation == 'display') {
    include $this->template('member/interview_test/home');
}else if($operation == 'start') {
    include $this->template('member/interview_test/start');
}else if($operation == 'choose') {
    include $this->template('member/interview_test/choose');
}else if($operation == 'check_index') {
    include $this->template('member/interview_test/check_index');
}else if($operation == 'finish') {
    include $this->template('member/interview_test/finish');
}else if($operation == 'read_info') {
    $data = pdo_fetch('select * from ' . tablename('sz_yi_member_interview') . ' where id=:id ORDER BY `id` ASC limit 1 ', array(':id' => $_GPC['id'] ));
    if( !$data )
    {
        exit('参数错误!');
    }
    include $this->template('member/interview_test/read_info');
}else if($operation == 'read_result') {
    $data = pdo_fetch('select * from ' . tablename('sz_yi_member_interview') . ' where id=:id ORDER BY `id` ASC limit 1 ', array(':id' => $_GPC['id'] ));
    if( !$data )
    {
        exit('参数错误!');
    }
    $result = explode(',',$data['question']);
    include $this->template('member/interview_test/read_result');
}else if($operation == 'question' && $_W['ispost']) {
    $user_d = pdo_fetch('select * from ' . tablename('sz_yi_member_interview') . ' where openid=:openid and uniacid=:uniacid ORDER BY `id` ASC limit 1 ', array(':uniacid' => $_W['uniacid'],'openid' =>$openid ));
    if( strlen($user_d['question']) == 0 )
    {
        $data['question'] = $_GPC['question'];
        $data['test_time'] = time();
        pdo_update('sz_yi_member_interview', $data, array('id' => $user_d['id'], 'uniacid' => $_W['uniacid'], 'openid' => $openid));
        // 微信提醒
        $messages = array(
            'keyword1' => array('value' => '面试测评通知', 'color' => '#73a68d'),
            'keyword2' => array('value' => $user_d['realname'].'向您提交了一份面试测评，点击查看', 'color' => '#73a68d')
        );
        $url = $this->createMobileUrl('member/interview_test',array('op'=>'read_info','id'=>$user_d['id']));
        m('message')->sendCustomNotice('ov3-bt8keSKg_8z9Wwi-zG1hRhwg', $messages, $url );

        show_json(1, array('msg' => '提交成功！'));
    }else{
        show_json(0, array('msg' => '您已测评过！'));
    }
}else if($operation == 'check' && $_W['ispost']) {
    $user_d = pdo_fetch('select * from ' . tablename('sz_yi_member_interview') . ' where openid=:openid and uniacid=:uniacid and test_time > 0 ORDER BY `id` ASC limit 1 ', array(':uniacid' => $_W['uniacid'],'openid' =>$openid ));
    if( $user_d )
    {
        show_json(1, array('msg' => '已测评'));
    }else{
        show_json(0, array('msg' => '未测评'));
    }
}else if($operation == 'verify' && $_W['ispost']) {
    
    $code = $_GPC['code'];

    if (($_SESSION['codetime'] + (60 * 5)) < time()) {
        show_json(0, '验证码已过期,请重新获取');
    }

    if ($_SESSION['code'] != $code) {
        show_json(0, '验证码错误,请重新获取');
    }

    if ($_SESSION['code_mobile'] != $_GPC['mobile']) {
        show_json(0, '注册手机号与验证码不匹配！');
    }

    $data['realname'] = $_GPC['realname'];
    $data['mobile'] = $_GPC['mobile'];
    $data['openid'] = $openid;
    $data['create_at'] = time();
    $data['uniacid'] = $_W['uniacid'];

    $user_d = pdo_fetch('select * from ' . tablename('sz_yi_member_interview') . ' where openid=:openid and uniacid=:uniacid ORDER BY `id` ASC limit 1 ', array(':uniacid' => $_W['uniacid'],'openid' =>$openid ));

    if( $user_d )
    {
        if( strlen($user_d['question']) > 0 )
        {
            show_json(0, array('msg' => '您已测评过！'));
        }else{
            show_json(1, array('msg' => '验证成功！'));
        }
    }else{
        pdo_insert('sz_yi_member_interview', $data);
    }
}
