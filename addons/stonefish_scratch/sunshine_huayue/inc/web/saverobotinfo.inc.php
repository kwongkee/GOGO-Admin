<?php

global $_W,$_GPC;

$robot_nickname = $_GPC['robot_nickname'];
$robot_sex = $_GPC['robot_sex'];
$robot_age = $_GPC['robot_age'];
$robot_images = $_GPC['robot_images'];


$info = MemberModel::info('i_robot');
if($info) {
	$userinfo = array();
	$userinfo['uniacid'] = $_W['uniacid'];
	$userinfo['openid'] = 'i_robot';
	$userinfo['nickname'] = $robot_nickname;
	$userinfo['age'] = $robot_age;
	$userinfo['sex'] = $robot_sex;
	$userinfo['headimgurl'] = tomedia($robot_images);
	$userinfo['add_time'] = date("Y-m-d H:i:s");

	$res = pdo_update('sunshine_huayue_member',$userinfo,array('openid'=>'i_robot'));
}else {
	$userinfo = array();
	$userinfo['uniacid'] = $_W['uniacid'];
	$userinfo['openid'] = 'i_robot';
	$userinfo['nickname'] = $robot_nickname;
	$userinfo['age'] = $robot_age;
	$userinfo['sex'] = $robot_sex;
	$userinfo['headimgurl'] = tomedia($robot_images);
	$userinfo['add_time'] = date("Y-m-d H:i:s");
	$res = pdo_insert('sunshine_huayue_member',$userinfo);
}


if($res) {
	echoJson(array('res'=>100,'msg'=>'success'));
}else {
	echoJson(array('res'=>101,'msg'=>'失败'));
}

