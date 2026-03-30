<?php

if (!defined('IN_IA')) {
    exit('Access Denied');
}

global $_W, $_GPC;
require "common.php";
require "public.php";
require "jz_common.php";

$op = trim($_GPC['op']);
$lid = empty($_GPC['lid'])?0:intval($_GPC['lid']);
$urlt = $this->createMobileUrl('jz_problem');

//添加
if(checksubmit('add_submit')){
	
	$urlt .= '&op=show&lid='.$lid;
	$data = array(
		'weid' => $_W['uniacid'],
		'uid' => $_W['member']['uid'],
		'lid' => $lid,
		'content' => trim($_GPC['content']),
		'addtime' => time()
	);
	   
	$resultp = pdo_insert('onljob_message_content', $data, true);
	if (!empty($resultp)) {
		
		pdo_update('onljob_message', array('showls' => 1,'addtime' => time()), array('lid' => $lid,'weid' => $_W['uniacid']));
		
		header("Location: ".$urlt."");
		exit(); 
	}else{
		message_app('发布失败！', array($urlt), 'error');
	}

}

//生产聊天记录----------
$lsuid = intval($_GPC['lsuid']);
if(!empty($lsuid)){
	
	//判断
	$srdb_ly = pdo_get('onljob_message', array('uid' => $_W['member']['uid'],'lsuid' => $lsuid,'weid' => $_W['uniacid']));
	if (empty($srdb_ly)) {
		
		//新
		$user_ls = pdo_get('onljob_user', array('weid' => $_W['uniacid'],'uid' => $lsuid), array('name'));
		$data = array(
			'weid' => $_W['uniacid'],
			'uid' => $_W['member']['uid'],
			'name' => trim($user_show['name']),
			'lsuid' => $lsuid,
			'lsname' => trim($user_ls['name']),
			'showt' => 0,
			'showls' => 0,
			'addtime' => time()
		);
		$result = pdo_insert('onljob_message', $data, true);
		$lid = pdo_insertid();
		
	}
	
	$op = 'show';
}

//获取留言列表----------
if($op == 'show'){
	
	$srdb = pdo_get('onljob_message', array('uid' => $_W['member']['uid'],'lid' => $lid,'weid' => $_W['uniacid']));
	if (empty($srdb)) {
		message_app('抱歉！参数错误！禁止提问！', '', 'error');
	}
	
	pdo_update('onljob_message', array('showt' => 0), array('lid' => $srdb['lid'],'weid' => $_W['uniacid']));
	
	//列表
	$list_content = pdo_fetchall("SELECT a.*,b.name,b.headimg FROM ".tablename('onljob_message_content')." AS a LEFT JOIN ".tablename('onljob_user')." AS b ON a.uid = b.uid WHERE a.weid = '{$_W['uniacid']}' and a.lid = '{$lid}' ORDER BY a.addtime ASC");
	
	include template_app('jz_problem_show');
	exit();
}

//列表---------------------
$list = pdo_fetchall("SELECT * FROM ".tablename('onljob_message')." WHERE weid = '{$_W['uniacid']}' and uid = '{$_W['member']['uid']}' ORDER BY addtime DESC");

include template_app('jz_problem');
?>