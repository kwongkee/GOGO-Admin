<?php

if (!defined('IN_IA')) {
    exit('Access Denied');
}

global $_W, $_GPC;
require "common.php";
require "public.php";
require "jz_common.php";

$op = trim($_GPC['op']);

$urlt = $this->createMobileUrl('jz_hzuserlist');


if(checksubmit('do_submit')){
	
	//获取孩子UID
	$hzname = trim($_GPC['hzname']);
	$hzphone = trim($_GPC['hzphone']);
	
	$hz_members = pdo_get('onljob_user', array('weid' => $_W['uniacid'],'name' => $hzname,'phone' => $hzphone), array('uid'));
	if(empty($hz_members['uid'])){
		message_app('抱歉！你输入孩子信息错误！请从新输入！', array($urltk), 'error');
	}
	
	$arr_user = explode(',', $user_show['hzuid']);
	if(@in_array($hz_members['uid'], $arr_user, TRUE)){
	    message_app('抱歉！该孩子您已经添加有！请从新输入！', array($urltk), 'error');
	}
	
	if(!empty($user_show['hzuid'])){
		$hzuid_all = $user_show['hzuid'].','.$hz_members['uid'];
	}else{
		$hzuid_all = $hz_members['uid'];
	}
	
	pdo_update('onljob_user', array('hzuid' => $hzuid_all), array('uid' => $_W['member']['uid'],'weid' => $_W['uniacid']));
	
	message_app('添加成功!', array($urlt), 'success');
	
}

if($op == 'add'){
	include template_app('jz_hzuserlist_add');
	exit();
}

//获取孩子列表---------------------
if(!empty($user_show['hzuid'])){
	$list = pdo_fetchall("SELECT uid,name FROM ".tablename('onljob_user')." WHERE weid = '{$_W['uniacid']}' and uid in (" . $user_show['hzuid'] . ") ORDER BY id DESC");
}

include template_app('jz_hzuserlist');
?>