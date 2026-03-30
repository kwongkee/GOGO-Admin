<?php
// 模块LTD提供
if (!defined('IN_IA')) {
	exit('Access Denied');
}

global $_W;
global $_GPC;
$mc = $_GPC['memberdata'];
$op = (empty($_GPC['op']) ? 'sendcode' : trim($_GPC['op']));
session_start();

if ($op == 'sendcode') {
	$mobile = $_GPC['mobile'];

	if (empty($mobile)) {
		show_json(0, '请填入手机号');
	}

	$info = pdo_fetch('select * from ' . tablename('enterprise_members') . ' where mobile=:mobile limit 1', array(':mobile' => $mobile));
		if (!empty($info)) {
			show_json(0, '该手机号已被注册！不能获取验证码。');
		}

	$code = rand(1000, 9999);
//    $code = 6666;
	$_SESSION['codetime'] = time();
	$_SESSION['code'] = $code;
	$_SESSION['code_mobile'] = $mobile;
	$issendsms = $this->sendSms($mobile, $code);
//    $issendsms['result']['success']=1;
	$set = m('common')->getSysset();

	if ($set['sms']['type'] == 1) {
		if ($issendsms['SubmitResult']['code'] == 2) {
			show_json(1);
			return 1;
		}

		show_json(0, $issendsms['SubmitResult']['msg']);
		return 1;
	}

	if (isset($issendsms['result']['success'])) {
		show_json(1);
		return 1;
	}

	if (!$issendsms) {
		show_json(1);
		return 1;
	}else{
		show_json(0, $issendsms['msg'] . '/' . $issendsms['sub_msg']);
		return 1;
	}
}

if ($op == 'bindmobilecode') {
	$mobile = $_GPC['mobile'];

	if (empty($mobile)) {
		show_json(0, '请填入手机号');
	}

	$isbindmobile = pdo_fetchcolumn('select count(*) from ' . tablename('enterprise_members') . ' where  mobile =:mobile and uniacid=:uniacid ', array(':uniacid' => $_W['uniacid'], ':mobile' => $mobile));

	if (!empty($isbindmobile)) {
		show_json(0, '该手机已经绑定其它微信号了!');
	}

	$code = rand(1000, 9999);
//    $code = 6666;
	$_SESSION['codetime'] = time();
	$_SESSION['code'] = $code;
	$_SESSION['code_mobile'] = $mobile;
	$issendsms = $this->sendSms($mobile, $code);
//    $issendsms['result']['success']=1;
	$set = m('common')->getSysset();
	if ($set['sms']['type'] == 1) {
		if ($issendsms['SubmitResult']['code'] == 2) {
			show_json(1);
			return 1;
		}

		show_json(0, $issendsms['SubmitResult']['msg']);
		return 1;
	}

	if (isset($issendsms['result']['success'])) {
		show_json(1);
		return 1;
	}

	if (!$issendsms) {
		show_json(1);
		return 1;
	}else{
		show_json(0, $issendsms['msg']);
		return 1;
	}
}

if ($op == 'checkcode') {
	$code = $_GPC['code'];

//	if (($_SESSION['codetime'] + (60 * 10)) < time()) {
//		show_json(0, '验证码已过期,请重新获取');
//	}
//
//	if ($_SESSION['code'] != $code) {
//		show_json(0, '验证码错误,请重新获取');
//	}

	show_json(1);
	return 1;
}

if ($op == 'ismobile') {
	$mobile = $_GPC['mobile'];

	if (empty($mobile)) {
		show_json(0, '请填入手机号');
	}
	
	//xj 只要未绑定微信的手机号都可以在微信端查找绑定 20170508
	$info = pdo_fetch('select * from ' . tablename('enterprise_members') . ' where mobile=:mobile and uniacid=:uniacid limit 1', array(':uniacid' => $_W['uniacid'], ':mobile' => $mobile));

	if (!empty($info)) {
		show_json(0, '该手机号已被注册！');
		return 1;
	}

	show_json(1);
}