<?php

header('Access-Control-Allow-Origin: *');

// 模块LTD提供
if (!defined('IN_IA')) {
	exit('Access Denied');
}

global $_W;
global $_GPC;
@session_start();
setcookie('preUrl', $_W['siteurl']);
$operation = (!empty($_GPC['op']) ? $_GPC['op'] : 'display');
$openid = m('user')->getOpenid();
$popenid = m('user')->islogin();
$openid = ($openid ? $openid : $popenid);
$member = m('member')->getMember($openid);
$uniacid = $_W['uniacid'];
$this->yzShopSet = m('common')->getSysset('shop');

$enterprise_members = pdo_fetch('select * from ' . tablename('enterprise_members') . ' where openid=:openid and uniacid=:uniacid limit 1', array(':uniacid' => $_W['uniacid'], ':openid' => $_W['openid']));

if(!$enterprise_members['mobile'])
{
    $url = 'Location:/app/index.php?i=' . $_W['uniacid'] . '&c=entry&do=enterprise&m=sz_yi&p=register';
	return header($url);
}

$ent_basic_info = pdo_fetch('select * from ' . tablename('enterprise_basicinfo') . ' where member_id=:member_id limit 1', array(':member_id' => $enterprise_members['id']));
if($ent_basic_info['id']>0)
{
    $url = 'Location:/app/index.php?i=' . $_W['uniacid'] . '&c=entry&do=enterprise&m=sz_yi&p=finish&op=finish';
    return header($url);
}

$is_check = pdo_fetch('select * from ' . tablename('enterprise_question') . ' where member_id=:member_id limit 1', array(':member_id' => $enterprise_members['id']));
if( $is_check )
{
    $url = 'Location:/app/index.php?i=' . $_W['uniacid'] . '&c=entry&do=enterprise&m=sz_yi&p=finish&ok=1';
	return header($url);
}

if (($operation == 'save_enterprise_info') && $_W['ispost']) {
    // 工商照面
    // 查询是否存在信息
    $basic_info_data = $_GPC['basic_info'];

    $basic_info = pdo_fetch('select * from ' . tablename('enterprise_basicinfo') . ' where member_id=:member_id and enterprise_id=:enterprise_id limit 1', array(':member_id' => $enterprise_members['id'], ':enterprise_id' => $basic_info_data['enterprise_id']));
    
    if($basic_info)
    {
        pdo_update('enterprise_basicinfo',
            $basic_info_data,
            array(
                'id' => $basic_info['id']
            )
        );
    }else{
        $basic_info_data['create_at'] = time();
        pdo_insert('enterprise_basicinfo', $basic_info_data);
    }
    
    // 实际受益人
    $beneficiaries_info = pdo_fetch('select * from ' . tablename('enterprise_beneficiaries') . ' where info_id=:info_id  limit 1', array(':info_id' => $basic_info_data['enterprise_id']));
    $beneficiaries_data = $_GPC['beneficiaries'];
    if($beneficiaries_info)
    {
        foreach ($beneficiaries_data as $k => $v) {
            pdo_update('enterprise_beneficiaries',
                $beneficiaries_data[$k],
                array(
                    'id' => $beneficiaries_info['id']
                )
            );
        }
        
    }else{
        foreach ($beneficiaries_data as $k => $v) {
            $beneficiaries_data[$k]['info_id'] = $basic_info_data['enterprise_id'];
            $beneficiaries_data[$k]['create_at'] = time();
            pdo_insert('enterprise_beneficiaries', $beneficiaries_data[$k]);
        }
        
    }

    // 纳税人类型
    $taxpayerlist_info = pdo_fetch('select * from ' . tablename('enterprise_taxpayerlist') . ' where info_id=:info_id  limit 1', array(':info_id' => $basic_info_data['enterprise_id']));
    $taxpayerlist_data = $_GPC['taxpayerlist'];
    if($taxpayerlist_info)
    {
        foreach ($taxpayerlist_data as $k => $v) {
            pdo_update('enterprise_taxpayerlist',
                $taxpayerlist_data[$k],
                array(
                    'id' => $taxpayerlist_info['id']
                )
            );
        }
        
    }else{
        foreach ($taxpayerlist_data as $k => $v) {
            $taxpayerlist_data[$k]['info_id'] = $basic_info_data['enterprise_id'];
            $taxpayerlist_data[$k]['name'] = $basic_info_data['name'];
            $taxpayerlist_data[$k]['create_at'] = time();
            pdo_insert('enterprise_taxpayerlist', $taxpayerlist_data[$k]);
        }
    }

    // 海关登记
    $customslist_info = pdo_fetch('select * from ' . tablename('enterprise_customslist') . ' where info_id=:info_id  limit 1', array(':info_id' => $basic_info_data['enterprise_id']));
    $customslist_data = $_GPC['customslist'];
    if($customslist_info)
    {
        foreach ($customslist_data as $k => $v) {
            pdo_update('enterprise_customslist',
                $customslist_data[$k],
                array(
                    'id' => $customslist_info['id']
                )
            );
        }
        
    }else{
        foreach ($customslist_data as $k => $v) {
            $customslist_data[$k]['info_id'] = $basic_info_data['enterprise_id'];
            $customslist_data[$k]['create_at'] = time();
            pdo_insert('enterprise_customslist', $customslist_data[$k]);
        }
        
    }

    show_json(1, array('msg' => '保存成功'));

}else if(($operation == 'save_question') && $_W['ispost']) {

    $question = pdo_fetch('select * from ' . tablename('enterprise_question') . ' where member_id=:member_id limit 1', array(':member_id' => $enterprise_members['id']));

    if( $question )
    {
        show_json(0, array('msg' => '您已尽职调查过！'));
        return false;
    }

    $data['member_id'] = $enterprise_members['id'];
    $data['question_1'] = $_GPC['question_1'];
    $data['question_2'] = $_GPC['question_2'];
    $data['question_3'] = $_GPC['question_3'];
    $data['question_4'] = $_GPC['question_4'];
    $data['question_5'] = $_GPC['question_5'];
    $data['question_6'] = $_GPC['question_6'];
    $data['question_7'] = $_GPC['question_7'];
    $data['question_8'] = $_GPC['question_8'];
    $data['question_9'] = $_GPC['question_9'];
    $data['question_10'] = $_GPC['question_10'];
    $data['question_11'] = $_GPC['question_11'];
    $data['question_12'] = $_GPC['question_12'];
    $data['question_13'] = $_GPC['question_13'];
    $data['question_14'] = $_GPC['question_14'];
    $data['question_15'] = $_GPC['question_15'];
    $data['create_at'] = time();
    pdo_insert('enterprise_question', $data);
    // 发送尽职调查结果

    $messages = array(
        'first' => array('value' => '用户：'.$enterprise_members['nickname'].'提交了尽职调查', 'color' => '#73a68d'),
        'keyword1' => array('value' => $enterprise_members['mobile'], 'color' => '#73a68d'),
        'keyword2' => array('value' => date('Y-m-d H:i:s',time()), 'color' => '#73a68d'),
        'remark' => array('value' => '请点查看结果！', 'color' => '#73a68d')
    );
    $boss_openid = 'ov3-bt8keSKg_8z9Wwi-zG1hRhwg';
    $template_id = '8vqI7z2VqXks8H9uTcl8tkR2v9wYi-tBQZjOrrQOuwI';
    $check_url = $this->createMobileUrl('member/account_check',array('uid'=>$_GPC['uid'],'op'=>'result'));
    m('message')->sendTplNotice($boss_openid, $template_id, $messages, $check_url);

    show_json(1, array('msg' => '提交成功'));
}else if($operation == 'confirm2'){
    include $this->template('enterprise/confirm2');
}else{
    include $this->template('enterprise/confirm');
}


?>
