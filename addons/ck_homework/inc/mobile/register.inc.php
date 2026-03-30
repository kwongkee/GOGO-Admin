<?php

if (!defined('IN_IA')) {
    exit('Access Denied');
}

global $_W, $_GPC;
session_start();
require "common.func.php";

$templateurl = "../addons/{$_GPC['m']}/template/mobile/";

$url = $_W['siteroot'] . "app/index.php?i=".$_GPC['i']."&c=entry&do=index&m=".$_GPC['m'];
$user_show = pdo_get('onljob_user', array('weid' => $_W['uniacid'],'uid' => $_W['member']['uid']));
if(!empty($user_show)){
	header('location: ' . $url);
	exit;
}

$tab = empty($_GPC['tab'])?0:intval($_GPC['tab']);

$urltk = $this->createMobileUrl('register');
if($tab){
	$urltk .= '&tab=' . $tab;
	$type = $tab - 1;
}else{
	//选择身份
	include template_app('register_index');
	exit();
}

//学科分类
$category = pdo_fetchall("SELECT * FROM " . tablename('onljob_class') . " WHERE weid = '{$_W['uniacid']}' and pid = '0' and type = 'xk' ORDER BY listorder ASC, cid DESC", array(), 'cid');

if(checksubmit('do_submit')){
	
	//判断手机号是否不注册
	$phone = trim($_GPC['phone']);
	if (!empty($phone)) {
		$mc_members = pdo_get('onljob_user', array('weid' => $_W['uniacid'],'phone' => $phone));
		if(!empty($mc_members) && $mc_members['uid'] != $_W['member']['uid']){
			message_app('抱歉！你输入手机号已经被注册，请从新更换一个！', array($urltk), 'error');
		}
	}
	
	//判断邮箱是否不注册
	$email = trim($_GPC['email']);
	if (!empty($email)) {
		$mc_members1 = pdo_get('onljob_user', array('weid' => $_W['uniacid'],'email' => $email));
		if(!empty($mc_members1) && $mc_members1['uid'] != $_W['member']['uid']){
			message_app('抱歉！你输入的邮箱已经被注册，请从新更换一个！', array($urltk), 'error');
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
	
	$data = array(
		'weid' => $_W['uniacid'],
		'uid' => $_W['member']['uid'],
		'name' => trim($_GPC['name']),
		'sex' => intval($_GPC['sex']),
		'birthday' => trim($_GPC['birthday']),
		'phone' => trim($_GPC['phone']),
		'email' => trim($_GPC['email']),
		'address' => trim($_GPC['address']),
		'qq' => trim($_GPC['qq']),
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
	
	//获取头像
	$mc_members = pdo_get('mc_members', array('uniacid' => $_W['uniacid'],'uid' => $_W['member']['uid']));
	if (!empty($mc_members['avatar'])) {
		$data['headimg'] = $mc_members['avatar'];
	}
	
	$result = pdo_insert('onljob_user', $data);
	if (!empty($result)) {
		$id = pdo_insertid();
		$numberlent = strlen($id);
		$canumb = 9 - $numberlent;
		$usernumber = random($canumb, true) . $id;
		pdo_update('onljob_user', array('usernumber' => $usernumber), array('uid' => $_W['member']['uid'],'weid' => $_W['uniacid']));
		
		if($_SESSION['back_act'] && $type == '0'){
			message_app('保存成功!', array($_SESSION['back_act']), 'success');
		}else{
			message_app('保存成功!', array($url), 'success');
		}
	}else{
		message_app('添加失败', array($url), 'error');
	}
	
}

include template_app('register');