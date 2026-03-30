<?php

if (!defined('IN_IA')) {
    exit('Access Denied');
}

global $_W, $_GPC;
session_start();
require "common.func.php";
load()->model('mc');
$default_groupid = cache_load("defaultgroupid:{$_W['uniacid']}");
if(empty($default_groupid)){
	$default_groupid = $_W['uniacid'];
}
$templateurl = "../addons/{$_GPC['m']}/template/mobile/";
$config = pdo_get('onljob_config', array('weid' => $_W['uniacid']));

$url = $_W['siteroot'] . "app/index.php?i=".$_GPC['i']."&c=entry&do=index&m=".$_GPC['m'];
$user_show = pdo_get('onljob_user', array('weid' => $_W['uniacid'],'uid' => $_W['member']['uid']));
if(!empty($user_show) && !empty($_W['member']['uid'])){
	header('location: ' . $url);
	exit;
}

$tab = empty($_GPC['tab'])?0:intval($_GPC['tab']);

$urltk = $this->createMobileUrl('registered');
$url_sms = $this->createMobileUrl('sms');

if($tab){
	$urltk .= '&tab=' . $tab;
	$type = $tab - 1;
}else{
	//选择身份
	include template_app('registered_index');
	exit();
}

if(checksubmit('do_submit')){
	
	$mobile_code = trim($_GPC['mobile_code']);
	//判断手机号是否不注册
	$phone = trim($_GPC['phone']);
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
	
	$password = trim($_GPC['password']);
	if(!empty($password)) {
		if(strlen($password) < 6) {
			message_app('密码不能少于6位', array($urltk), 'error');
		}
		
		$salt = random(8);
		$password = md5($password . $salt . $_W['config']['setting']['authkey']);
	}
	
	$datap = array(
		'uniacid' => $_W['uniacid'],
		'email' => md5($salt).'@we7.cc',
		'salt' => $salt,
		'groupid' => $default_groupid,
		'password' => $password
	);

	$result = pdo_insert('mc_members', $datap, true);
	if(!$result){
        message_app('注册失败！', array($urltk), 'error');
    }
	$_W['member']['uid'] = pdo_insertid();
	
	$data = array(
		'weid' => $_W['uniacid'],
		'uid' => $_W['member']['uid'],
		'name' => trim($_GPC['yname']),
		'phone' => trim($_GPC['phone']),
		'subjectid' => intval($_GPC['subjectid']),
		'password' => $password,
		'salt' => $salt,
		'type' => $type
	);
	
	//获取孩子UID
	$hzname = trim($_GPC['hzname']);
	$hzphone = trim($_GPC['hzphone']);
	if ($data['type'] == 2) {
		$hz_members = pdo_get('onljob_user', array('weid' => $_W['uniacid'],'name' => $hzname,'phone' => $hzphone), array('uid'));
		if(empty($hz_members['uid'])){
			message_app('抱歉！你输入孩子信息错误！请从新输入！', array($urltk), 'error');
		}
		
		$data['hzuid'] = $hz_members['uid'];
	}
	
	$result = pdo_insert('onljob_user', $data);
	if (!empty($result)) {
		$id = pdo_insertid();
		$numberlent = strlen($id);
		$canumb = 9 - $numberlent;
		$usernumber = random($canumb, true) . $id;
		pdo_update('onljob_user', array('usernumber' => $usernumber), array('uid' => $_W['member']['uid'],'weid' => $_W['uniacid']));
		
		//清空
		$_SESSION['mobile'] = '';
		$_SESSION['mobile_code'] = '';
		
		if($_SESSION['back_act'] && $type == '0'){
			message_app('注册成功!', array($_SESSION['back_act']), 'success');
		}else{
			message_app('注册成功!', array($this->createMobileUrl('login')), 'success');
		}
	}else{
            message_app('注册失败！！', array($urltk), 'error');
	}
	
}

//随机生成
$_SESSION['send_code'] = random(6,1);

include template_app('registered');