<?php

defined('IN_IA') or exit('Access Denied');

global $_W;
global $_GPC;

$operation = (!empty($_GPC['op']) ? $_GPC['op'] : 'display');
$openid = m('user')->getOpenid();
$popenid = m('user')->islogin();
$openid = ($openid ? $openid : $popenid);
$member = m('member')->getMember($openid);

function GetRandStr($length){
    $str='abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $len=strlen($str)-1;
    $randstr='';
    for($i=0;$i<$length;$i++){
        $num=mt_rand(0,$len);
        $randstr .= $str[$num];
    }
    return $randstr;
}

if ($operation == 'display') {
    if( $_GPC['uid'] == '' )
    {
        echo '缺少参数';
    }else{
        // 代理商
        $agents = pdo_fetchall('select * from ' . tablename('customs_agents_admin') . ' where pid = 0 ');
        $merchant = pdo_get('total_merchant_account',array('id'=>$_GPC['uid']));
        // 尽职调查结果
        $question = pdo_get('enterprise_question',array('member_id'=>$merchant['enterprise_id']));
        include $this->template('member/account_check/check');
    }
}elseif ($operation == 'agents_set') {
    if( $_GPC['uid'] == '' )
    {
        echo '缺少参数';
    }else{
        // 商户信息
        $merchant = pdo_get('decl_user',array('id'=>$_GPC['uid']));
        // 收费项目
        $cost = pdo_fetchall('select * from ' . tablename('customs_agents_cost') . ' where is_delete = 0 ');
        $decl_user = pdo_get('decl_user',array('id'=>$_GPC['uid']));
        if( $decl_user['cost_rule'] != '')
        {
            $decl_cost = json_decode($decl_user['cost_rule'],true);
            foreach ($decl_cost as $k => $v) {
                $decl_cost[$k]['ids1'] = GetRandStr(5);
                $decl_cost[$k]['ids2'] = GetRandStr(5);
            }
        }
        
        include $this->template('member/account_check/agents_set2');
    }
}elseif (($operation == 'agents_set_save') && $_W['ispost']) {
    $id = intval($_GPC['id']);
    $cost_type = $_GPC['cost_type'];
    $type = $_GPC['type'];
    $cost_name = $_GPC['cost_name'];
    $cost = $_GPC['cost'];
    $cost_id = $_GPC['cost_id'];

    foreach ($cost_type as $k => $v) {
        $cost_rule[] = [
            'type' => $type[$k],
            'cost_type' => $v,
            'cost_name' => $cost_name[$k],
            'cost' => $cost[$k] == '' ? '0.00' : sprintf("%.2f",$cost[$k]),
            'cost_id' => $cost_id[$k]
        ];
    }

    // var_dump( json_encode($cost_rule));
    $data['cost_rule'] = json_encode($cost_rule);
    pdo_update('decl_user', $data, array('id' => $id));
    $decl_user = pdo_get('decl_user',array('id'=>$id));
    // 通知会员
    if($decl_user['openid'] && $decl_user['is_set_rule'] == 0)
    {
        pdo_update('decl_user', array('is_set_rule' => 1), array('id' => $id));
        $messages = array(
            'first' => array('value' => '商户审核结果通知', 'color' => '#73a68d'),
            'keyword1' => array('value' => $decl_user['user_name'], 'color' => '#73a68d'),
            'keyword2' => array('value' => $decl_user['user_tel'], 'color' => '#73a68d'),
            'keyword3' => array('value' => date('Y-m-d H:i:s',time()), 'color' => '#73a68d'),
            'remark' => array('value' => '请点击登录账号！', 'color' => '#73a68d')
        );
        $boss_openid = $decl_user['openid'];
        $template_id = 'tKp53WIg8puJ_u3jIyZasBvVonPEtUeNlXKnmi7r9UM';
        $check_url = 'http://declare.gogo198.cn/mobile/login';
        m('message')->sendTplNotice($boss_openid, $template_id, $messages, $check_url);
    }
    

    show_json(1, array('msg' => '设置成功'));
}elseif ($operation == 'check2') {
    if( $_GPC['uid'] == '' )
    {
        echo '缺少参数';
    }else{
        
        // 代理商
        $agents = pdo_fetchall('select * from ' . tablename('customs_agents_admin') . ' where pid = 0 ');
        $merchant = pdo_get('total_merchant_account',array('id'=>$_GPC['uid']));
        // 尽职调查结果
        $question = pdo_get('enterprise_question',array('member_id'=>$merchant['enterprise_id']));
        
        include $this->template('member/account_check/check2');
    }
}elseif ($operation == 'result') {
    $decl_user = pdo_get('decl_user',array('id'=>$_GPC['uid']));
    $merchant = pdo_get('total_merchant_account',array('mobile'=>$decl_user['user_tel']));
    // 尽职调查结果
    $question = pdo_get('enterprise_question',array('member_id'=>$merchant['enterprise_id']));
    
    include $this->template('member/account_check/result');
}
