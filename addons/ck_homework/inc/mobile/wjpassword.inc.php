<?php
if (!defined('IN_IA')) {
    exit('Access Denied');
}

global $_W, $_GPC;
require "common.func.php";
$templateurl = "../addons/{$_GPC['m']}/template/mobile/";
session_start();
$op = empty($_GPC['op'])?0:intval($_GPC['op']);
$urlt = $this->createMobileUrl('wjpassword');
$url_sms = $this->createMobileUrl('sms');
$url_email = $this->createMobileUrl('email');
$config = pdo_get('onljob_config', array('weid' => $_W['uniacid']));

if(checksubmit('do_submit')){

	if($op == 1){
		//验证身份
		$uid = intval($_GPC['uid']);
		if (empty($uid)) {
			message_app('抱歉！会员ID为空！请从新获取！', array($urlt), 'error');
		}
		
		//手机号验证
		if(trim($_GPC['type']) == 'moblie'){
			
			$phone = intval($_GPC['phone']);
			$mobile_code = trim($_GPC['mobile_code']);
			
			//手机验证码
			if($phone != $_SESSION['mobile'] or $mobile_code != $_SESSION['mobile_code'] or empty($phone) or empty($mobile_code)){
				message_app('手机验证码输入错误！', array($urlt.'&op=1&phone='.$phone.'&uid='.$uid), 'error');
			}
			
			//清空
			$_SESSION['mobile'] = '';
			$_SESSION['mobile_code'] = '';
		}
		
		$op = $op + 1;
	}elseif($op == 2){
		//重置密码
		$uid = intval($_GPC['uid']);
		if (empty($uid)) {
			message_app('抱歉！会员ID为空！请从新获取！', array($urlt), 'error');
		}
		
		$password = trim($_GPC['password']);
		$newpassword = trim($_GPC['newpassword']);
		
		if(strlen($password) < 6) {
			message_app('密码不能少于6位', '', 'error');
		}
		if($password != $newpassword){
			message_app('两次密码不一致!', '', 'error');
		}
		
		$salt = random(8);
		$password = md5($password . $salt . $_W['config']['setting']['authkey']);
		
		$data['password'] = $password;
		$data['salt'] = $salt;

		pdo_update('onljob_user', $data, array('uid' => $uid,'weid' => $_W['uniacid']));
		
		$op = $op + 1; 
	}else{
		//查找用户名
		$username = trim($_GPC['username']);
		if (empty($username)) {
			message_app('用户名不能为空', array($urlt), 'error');
		}
	
		$sql = 'SELECT `uid`,`salt`,`password`,`type` FROM ' . tablename('onljob_user') . ' WHERE `weid`=:weid';
		$pars = array();
		$pars[':weid'] = $_W['weid'];
		
		if (preg_match(REGULAR_MOBILE, $username)) {
			$sql .= ' AND `phone`=:phone';
			$pars[':phone'] = $username;
			$typename = '_moblie';
		}else{
			message_app('抱歉！您输入的手机号格式错误！', array($urlt), 'error');
		}
		
		$user = pdo_fetch($sql, $pars);
		if (empty($user)) {
			message_app('抱歉！未找到您输入的用户！请重新输入！', array($urlt), 'error');
		}
		$_GPC['uid'] = $user['uid'];
		$_GPC['phone'] = $username;
		$op = $op + 1;
		
		//随机生成
		$_SESSION['send_code'] = random(6,1);
		$_SESSION['send_code1'] = random(6,1);
	}
	
}

$ack = $op + 1;
include template_app('wjpassword' . $ack . $typename);