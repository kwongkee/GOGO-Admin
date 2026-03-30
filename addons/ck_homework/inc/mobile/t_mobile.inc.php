<?php
if (!defined('IN_IA')) {
    exit('Access Denied');
}

global $_W, $_GPC;
load()->func('tpl');

require "common.php";
require "public.php";
require "t_common.php";
session_start();

$urltk = $this->createMobileUrl('t_mobile');
$url_sms = $this->createMobileUrl('sms');

//提交处理
if(checksubmit('save_submit')){
	
	$urlt = $this->createMobileUrl('t_index');

	$phone = intval($_GPC['phone']);
	$mobile_code = trim($_GPC['mobile_code']);
	
	//判断手机号是否不注册
	if (!empty($phone)) {
		$mc_members = pdo_get('onljob_user', array('weid' => $_W['uniacid'],'phone' => $phone));
		if(!empty($mc_members) && $mc_members['uid'] != $_W['member']['uid']){
			message_app('抱歉！你输入手机号已经被注册，请从新更换一个！', array($urltk), 'error');
		}
	}
	
	//手机验证码
	if($config['sms_open']) {
		if($phone!=$_SESSION['mobile'] or $mobile_code!=$_SESSION['mobile_code'] or empty($phone) or empty($mobile_code)){
			message_app('手机验证码输入错误！', array($urltk), 'error');
		}
	}
	
	pdo_update('onljob_user', array('phone' => $phone), array('id' => $user_show['id'],'weid' => $_W['uniacid']));
	
	//清空
	$_SESSION['mobile'] = '';
	$_SESSION['mobile_code'] = '';
	
	message_app('修改成功！', array($urlt), 'success');
	
}

//随机生成
$_SESSION['send_code'] = random(6,1);
	
include template_app('t_mobile');