<?php
global $_GPC,$_W;
// 口令校验
if(!$_W['isfounder'])
{
	echoJson(array('res'=>'111','msg'=>'无权限！请联系站长管理员'));
}

if($_GPC['dataType'] != 'opt_uniacid' || $_GPC['setval'] != 'handled2')
{
	echoJson(array('res'=>'101','msg'=>'无权限！请联系站长管理员!'));
}


pdo_begin();
$data = array();
$data['name'] = 'opt_uniacid';
$data['value'] = 'handled2';
$data['add_time'] = date("Y-m-d H:i:s");
$info = pdo_insert("sunshine_huayue_setting",$data);

if(!$info)
{
	pdo_rollback();
	echoJson(array('res'=>'103','msg'=>'无权限！请联系站长管理员'));
}

pdo_query("update ims_sunshine_huayue_address set uniacid='{$_W['uniacid']}'");
pdo_query("update ims_sunshine_huayue_setting set uniacid='{$_W['uniacid']}'");
pdo_query("update ims_sunshine_huayue_album set uniacid='{$_W['uniacid']}'");
pdo_query("update ims_sunshine_huayue_growth set uniacid='{$_W['uniacid']}'");
pdo_query("update ims_sunshine_huayue_credit set uniacid='{$_W['uniacid']}'");
pdo_query("update ims_sunshine_huayue_chatroom set uniacid='{$_W['uniacid']}'");
pdo_query("update ims_sunshine_huayue_chatroom_log set uniacid='{$_W['uniacid']}'");
pdo_query("update ims_sunshine_huayue_multisend set uniacid='{$_W['uniacid']}'");
pdo_query("update ims_sunshine_huayue_greets set uniacid='{$_W['uniacid']}'");
pdo_query("update ims_sunshine_huayue_chat set uniacid='{$_W['uniacid']}'");
pdo_query("update ims_sunshine_huayue_chatmessage set uniacid='{$_W['uniacid']}'");

pdo_commit();
echoJson(array('res'=>'100','msg'=>'success'));

