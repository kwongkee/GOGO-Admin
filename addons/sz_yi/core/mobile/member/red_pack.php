<?php

defined('IN_IA') or exit('Access Denied');

global $_W;
global $_GPC;

$operation = (!empty($_GPC['op']) ? $_GPC['op'] : 'display');
$openid = m('user')->getOpenid();
$popenid = m('user')->islogin();
$openid = ($openid ? $openid : $popenid);
$member = m('member')->getMember($openid);
$signPackage = $_W['account']['jssdkconfig'];
// 红包页面
if ($operation == 'display') {
    if( $_GPC['pack_id'] == '' )
    {
        echo '缺少参数';
    }else{
        $red_pack = pdo_get('customs_agents_redpack',array('id'=>$_GPC['pack_id']));
        $shareCon = array(
            'title' => '您有一个红包可以领取', 
            'link' => $this->createMobileUrl('member/red_pack', array('pack_id' => $_GPC['pack_id'])),
            'imgUrl' => 'https://shop.gogo198.cn/addons/sz_yi/static/red_pack/images/red_pack.jpeg', 
            'desc' => $red_pack['agent_name']
        );
        include $this->template('member/red_pack/index');
    }
}elseif($operation == 'view') {
    if( $_GPC['pack_id'] == '' )
    {
        echo '缺少参数';
    }else{
        // 判断是否已注册
        $red_pack = pdo_get('decl_user',array('openid'=>$_W['openid']));
        if(!$red_pack)
        {
            header('Location: ' . $this->createMobileUrl('member/red_pack', array('pack_id' => $_GPC['pack_id'])));
        }

        // 判断是否领取
        // 记录领取
        $is_lingqu = pdo_get('customs_agents_redpack_log',array('pack_id'=>$_GPC['pack_id'],'openid'=>$_W['openid']));
        if(!$is_lingqu)
        {
            $is_red_pack['openid'] = $_W['openid'];
            $is_red_pack['pack_id'] = $_GPC['pack_id'];
            $is_red_pack['is_use'] = 0;
            $is_red_pack['create_at'] = time();
            pdo_insert('customs_agents_redpack_log', $is_red_pack);
            echo "<script>alert('领取成功！');</script>";
        }else{
            echo "<script>alert('您已领取过！');</script>";
        }

        $red_pack = pdo_get('customs_agents_redpack',array('id'=>$_GPC['pack_id']));
        $cost_info = pdo_get('customs_agents_cost',array('id'=>$red_pack['cost_id']));
        if($red_pack['type'] == 1)
        {
            $red_pack['moneys'] = $red_pack['money'];
            $red_pack['type_text'] = '该红包可抵扣'.$cost_info['name'].$red_pack['moneys'].'/单';
        }else{
            $red_pack['moneys'] = $red_pack['money_rate'].'%';
            $red_pack['type_text'] = '该红包可抵扣交易应付费的'.$red_pack['moneys'];
        }
        $shareCon = array(
            'title' => '您有一个红包可以领取', 
            'link' => $this->createMobileUrl('member/red_pack', array('pack_id' => $_GPC['pack_id'])),
            'imgUrl' => 'https://shop.gogo198.cn/addons/sz_yi/static/red_pack/images/red_pack.jpeg', 
            'desc' => $red_pack['agent_name']
        );
        include $this->template('member/red_pack/view');
    }
}elseif($operation == 'checkuser') {
    $red_pack = pdo_get('decl_user',array('openid'=>$openid));
    if($red_pack)
    {
        // 判断是否领取
        show_json(1);
    }else{
        show_json(0);
    }
}