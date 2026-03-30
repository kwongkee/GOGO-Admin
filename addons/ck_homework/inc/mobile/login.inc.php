<?php
if (!defined('IN_IA')) {
    exit('Access Denied');
}

global $_W, $_GPC;
require "common.func.php";
$templateurl = "../addons/{$_GPC['m']}/template/mobile/";

$url = $_W['siteroot'] . "app/index.php?i=".$_GPC['i']."&c=entry&do=index&m=".$_GPC['m'];
// if(!empty($_W['member']['uid'])) {
// 	header('location: ' . $url);
// 	exit;
// }

$urltk = $this->createMobileUrl('login');

if(checksubmit('do_submit')){
	$username = trim($_GPC['username']);
	$password = trim($_GPC['password']);
	$mode = trim($_GPC['mode']);
	if (empty($username)) {
		message_app('手机号/姓名不能为空', '', 'error');
	}
	if (empty($password)) {
		message_app('密码不能为空', '', 'error');
	}

	$sql = 'SELECT `id`,`uid`,`salt`,`password`,`type` FROM ' . tablename('onljob_user') . ' WHERE `weid`=:weid';
	$pars = array();
	$pars[':weid'] = $_W['weid'];
	
	if (preg_match(REGULAR_MOBILE, $username)) {
		$sql .= ' AND `phone`=:phone';
		$pars[':phone'] = $username;
	} else {
		$sql .= ' AND `name`=:name';
		$pars[':name'] = $username;
	}
	
	$user = pdo_fetch($sql, $pars);
	$hash = md5($password . $user['salt'] . $_W['config']['setting']['authkey']);
	if ($user['password'] != $hash) {
		message_app('密码错误', array($urltk), 'error');
	}

	if (empty($user)) {
		message_app('该帐号尚未注册', array($urltk), 'error');
	}
    if ($user['uid']==0){
        pdo_update('onljob_user',['uid'=>$_W['member']['uid']],['id'=>$user['id']]);
        $user['uid'] = $_W['member']['uid'];
    }
	$parsp = array();
	$parsp[':uniacid'] = $_W['uniacid'];
	$parsp[':uid'] = $user['uid'];
	$userp = pdo_fetch('SELECT `uid`,`salt`,`password` FROM ' . tablename('mc_members') . ' WHERE `uniacid`=:uniacid and `uid`=:uid', $parsp);
	
	if (_mc_login($userp)) {

		//模块会员信息
		$user_show = pdo_get('onljob_user', array('weid' => $_W['uniacid'],'uid' => $userp['uid']), array('type'));
		if($user_show['type'] == '1'){
			//老师中心
			$url_mem = $this->createMobileUrl('t_index');
		}elseif($user_show['type'] == '2'){
			//家长中心
			$url_mem = $this->createMobileUrl('jz_index');	
		}else{
			//学生中心
			$url_mem = $this->createMobileUrl('m_index');
		}
		
		message_app('登录成功！', array($url_mem), 'success');
	}
	message_app('未知错误导致登录失败', array($urltk), 'error');
	
}

include template_app('login');