<?php
if (!defined('IN_IA')) {
    exit('Access Denied');
}

global $_W, $_GPC;
load()->func('tpl');

require "common.php";
require "public.php";
require "m_common.php";

$urltk = $this->createMobileUrl('m_profile');

//提交处理
if(checksubmit('save_submit')){
	
	if (empty ($_GPC['name'])) {
		message_app('姓名不能为空！', '', 'error');
	}
	if (empty ($_GPC['sex'])) {
		message_app('请选着性别！', '', 'error');
	}
	if (empty ($_GPC['birthday'])) {
		message_app('请选择出生日期！', '', 'error');
	}
	if (empty ($_GPC['address'])) {
		message_app('现居住地址不能为空！', '', 'error');
	}
	
	$data = array(
		'headimg' => trim($_GPC['headimg']),
		'name' => trim($_GPC['name']),
		'sex' => intval($_GPC['sex']),
		'birthday' => trim($_GPC['birthday']),
		'email' => trim($_GPC['email']),
		'address' => trim($_GPC['address']),
		'qq' => trim($_GPC['qq'])
	);
	
	//判断手机号是否不注册
	$phone = trim($_GPC['phone']);
	if (!empty($phone) && $user_show['phone'] != $phone) {
		$mc_members = pdo_get('onljob_user', array('weid' => $_W['uniacid'],'phone' => $phone));
		if(!empty($mc_members) && $mc_members['uid'] != $_W['member']['uid']){
			message_app('抱歉！你输入手机号已经被注册，请从新更换一个！', array($urltk), 'error');
		}
		$data['phone'] = $phone;
	}
	
	//判断邮箱是否不注册
	$email = trim($_GPC['email']);
	if (!empty($email) && $user_show['email'] != $email) {
		$mc_members1 = pdo_get('onljob_user', array('weid' => $_W['uniacid'],'email' => $email));
		if(!empty($mc_members1) && $mc_members1['uid'] != $_W['member']['uid']){
			message_app('抱歉！你输入的邮箱已经被注册，请从新更换一个！', array($urltk), 'error');
		}
		$data['email'] = $email;
	}
	
	$password = trim($_GPC['password']);
	if(!empty($password)) {
		if(strlen($password) < 6) {
			message_app('密码不能少于6位', array($urltk), 'error');
		}
		
		$salt = random(8);
		$password = md5($password . $salt . $_W['config']['setting']['authkey']);
		
		$data['password'] = $password;
		$data['salt'] = $salt;
	}
	
	//修改
	if(!empty($user_show['id'])){
		pdo_update('onljob_user', $data, array('id' => $user_show['id'],'weid' => $_W['uniacid']));
		message_app('修改成功！', array($this->createMobileUrl('m_index')), 'success');
	}else{
		message_app('修改失败！', array($this->createMobileUrl('m_index')), 'error');
	}
	
}
	
include template_app('m_profile');