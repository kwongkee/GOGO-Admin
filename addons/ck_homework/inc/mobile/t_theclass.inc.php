<?php
if (!defined('IN_IA')) {
    exit('Access Denied');
}

global $_W, $_GPC;
load()->func('tpl');

require "common.php";
require "public.php";
require "t_common.php";
require 'weixin.class.php';
$op = empty($_GPC['op'])?0:intval($_GPC['op']);

$urltk = $this->createMobileUrl('t_theclass');
//获取公众号配置信息
$srdm = pdo_get('account_wechats', array('uniacid' => $_W['uniacid']));
$appid = $srdm['key'];
$appsecret = $srdm['secret'];
$access_token_odl = $srdm['token'];
$mb_config = $config;

//科目
$category = pdo_fetchall("SELECT * FROM " . tablename('onljob_class') . " WHERE weid = '{$_W['uniacid']}' and pid = '0' and type = 'xk' ORDER BY listorder ASC, cid DESC", array(), 'cid');

//年级
$category_nj = pdo_fetchall("SELECT * FROM " . tablename('onljob_class') . " WHERE weid = '{$_W['uniacid']}' and pid = '0' and type = 'nj' ORDER BY listorder ASC, cid DESC", array(), 'cid');

//毕业
if($_GPC['ob'] === 'graduation'){
	$id = empty($_GPC['id'])?0:intval($_GPC['id']);
	$srdb = pdo_get('onljob_theclass', array('id' => $id,'uid' => $_W['member']['uid'],'weid' => $_W['uniacid']));
	if (!empty($srdb)) {
		pdo_update('onljob_theclass', array('state' => 1), array('id' => $id,'uid' => $_W['member']['uid'],'weid' => $_W['uniacid']));
		message_app('当前班级毕业成功了！', array($urltk), 'success');
	}else{
		message_app('毕业操作失败！', '', 'error');
	}
}

//班级转让
if($_GPC['ob'] === 'transfer'){
	$id = empty($_GPC['id'])?0:intval($_GPC['id']);
	$srdb = pdo_get('onljob_theclass', array('id' => $id,'uid' => $_W['member']['uid'],'weid' => $_W['uniacid']));
	if (empty($srdb)) {
		message_app('参数错误！访问失败！', '', 'error');
	}
	
	$where = '';
	$keyword = trim($_GPC['keyword']);
	if (!empty($keyword)) {
		$where .= " AND usernumber LIKE '%{$keyword}%'";
		$user_list = pdo_fetchall("SELECT * FROM ".tablename('onljob_user')." WHERE weid = '{$_W['uniacid']}' and type = '1' {$where} ORDER BY id DESC");
	}
	
	include template_app('t_theclass_transfer');
	exit();
}

//申请转让
if($_GPC['ob'] === 'transfer_add'){
	$id = empty($_GPC['id'])?0:intval($_GPC['id']);
	$zuid = intval($_GPC['zuid']);
	$srdb = pdo_get('onljob_theclass', array('id' => $id,'uid' => $_W['member']['uid'],'weid' => $_W['uniacid']));
	if (empty($srdb) || empty($zuid)) {
		message_app('参数错误！转入操作失败！', '', 'error');
	}
	
	if($zuid == $_W['member']['uid']){
		message_app('抱歉！不能转让给自己！', '', 'error');
	}
	
	pdo_update('onljob_theclass', array('zuid' => $zuid), array('id' => $id,'uid' => $_W['member']['uid'],'weid' => $_W['uniacid']));
	message_app('转移班级已经提交，请等候接收老师同意接收。', array($urltk), 'success');
}

//撤销转出
if($_GPC['ob'] === 'transfer_undo'){
	$id = empty($_GPC['id'])?0:intval($_GPC['id']);
	$srdb = pdo_get('onljob_theclass', array('id' => $id,'uid' => $_W['member']['uid'],'weid' => $_W['uniacid']));
	if (empty($srdb)) {
		message_app('参数错误！撤销转出失败！', '', 'error');
	}
	
	pdo_update('onljob_theclass', array('zuid' => 0), array('id' => $id,'uid' => $_W['member']['uid'],'weid' => $_W['uniacid']));
	message_app('该班级撤销成功！', array($urltk), 'success');
}

//拒绝转入
if($_GPC['ob'] === 'refused'){
	$id = empty($_GPC['id'])?0:intval($_GPC['id']);
	$srdb = pdo_get('onljob_theclass', array('id' => $id,'weid' => $_W['uniacid']));
	if (empty($srdb)) {
		message_app('参数错误！拒绝转入失败！', '', 'error');
	}
	
	pdo_update('onljob_theclass', array('zuid' => 0), array('id' => $id,'weid' => $_W['uniacid']));
	message_app('拒绝转入该班级成功！', array($urltk), 'success');
}

//同意转入
if($_GPC['ob'] === 'accept'){
	$id = empty($_GPC['id'])?0:intval($_GPC['id']);
	$srdb = pdo_get('onljob_theclass', array('id' => $id,'weid' => $_W['uniacid']));
	if (empty($srdb)) {
		message_app('参数错误！同意转入失败！', '', 'error');
	}
	
	pdo_update('onljob_theclass', array('zuid' => 0,'uid' => $_W['member']['uid']), array('id' => $id,'weid' => $_W['uniacid']));
	//并转入相应发布的作业
	pdo_update('onljob_work', array('uid' => $_W['member']['uid']), array('bjid' => $id,'weid' => $_W['uniacid']));
	
	message_app('同意转入该班级成功！', array($urltk), 'success');
}

//学生----------------------
//拒绝
if($_GPC['ob'] === 'xsrefused'){
	$id = empty($_GPC['id'])?0:intval($_GPC['id']);
	$srdb = pdo_get('onljob_theclass_apply', array('id' => $id,'weid' => $_W['uniacid']));
	$srdb_theclass = pdo_get('onljob_theclass', array('id' => $srdb['bjid'],'weid' => $_W['uniacid']));
	if (empty($srdb)) {
		message_app('参数错误！拒绝转入失败！', '', 'error');
	}
	pdo_delete('onljob_theclass_apply', array('id' => $id,'weid' => $_W['uniacid']));
	
	//发送审核结果模板消息---------------------
		if(!empty($mb_config['mb_open']) && !empty($appid) && !empty($appsecret)){
		    $uniacid = $_W['uniacid'];
			$access_token = moban($appid,$appsecret,$access_token_odl,$uniacid);
			//获取openid
			$user_openid = pdo_get('mc_mapping_fans', array('uniacid' => $_W['uniacid'],'uid' =>$srdb['uid'],'follow' => 1));	
			$first = "你的审核请求已经处理。";
			$url = $_W['siteroot'] . "app/index.php?i=".$_GPC['i']."&c=".$_GPC['c']."&m=".$_GPC['m']."&do=theclass";
			$shDateTime = date('Y-m-d H:i:s',time());
			$template = array(
				'touser'=> trim($user_openid['openid']),
				'template_id'=> trim($mb_config['mbid5']), 
				'url'=> $url,
				'topcolor'=>"#FF0000",
				'data'=>array(
				'first'=>array('value'=>urlencode($first),'color'=>"#00008B"),    
				'keyword1'=>array('value'=>urlencode("加入'{$srdb_theclass['titlename']}'班级"),'color'=>"#00008B"),    //审核事项keyword1     
				'keyword2'=>array('value'=>urlencode("审核未通过，老师拒绝加入"),'color'=>'#00008B'),        //审核状态keyword2  
				'keyword3'=>array('value'=>urlencode($shDateTime),'color'=>'#00008B'),        // 审核时间keyword3
				'remark'=>array('value'=>urlencode("点击查看详情。"),'color'=>'#00008B'),
				)
			);		
			$data = urldecode(json_encode($template));
			$send_result = send_template_message($data,$access_token);		
		}
		//---------------------
	
	message_app('拒绝该学生加入成功！', array($urltk), 'success');
}

//同意
if($_GPC['ob'] === 'xsaccept'){
	$id = empty($_GPC['id'])?0:intval($_GPC['id']);
	$srdb = pdo_get('onljob_theclass_apply', array('id' => $id,'weid' => $_W['uniacid']));
	$srdb_theclass = pdo_get('onljob_theclass', array('id' => $srdb['bjid'],'weid' => $_W['uniacid']));
	if (empty($srdb)) {
		message_app('参数错误！同意转入失败！', '', 'error');
	}
	pdo_update('onljob_theclass_apply', array('state' => 1), array('id' => $id,'weid' => $_W['uniacid']));
	
	//发送审核结果模板消息---------------------
		if(!empty($mb_config['mb_open']) && !empty($appid) && !empty($appsecret)){
            $uniacid = $_W['uniacid'];
			$access_token = moban($appid,$appsecret,$access_token_odl,$uniacid);
			//获取openid
			$user_openid = pdo_get('mc_mapping_fans', array('uniacid' => $_W['uniacid'],'uid' =>$srdb['uid'],'follow' => 1));	
			$first = "你的审核请求已经处理。";
			$url = $_W['siteroot'] . "app/index.php?i=".$_GPC['i']."&c=".$_GPC['c']."&m=".$_GPC['m']."&do=m_theclass";
			$shDateTime = date('Y-m-d H:i:s',time());
			$template = array(
				'touser'=> trim($user_openid['openid']),
				'template_id'=> trim($mb_config['mbid6']), 
				'url'=> $url,
				'topcolor'=>"#FF0000",
				'data'=>array(
				'first'=>array('value'=>urlencode($first),'color'=>"#00008B"),    
				'keyword1'=>array('value'=>urlencode("加入'{$srdb_theclass['titlename']}'班级"),'color'=>"#00008B"),    //审核事项keyword1     
				'keyword2'=>array('value'=>urlencode("审核已通过，老师同意加入"),'color'=>'#00008B'),        //审核状态keyword2  
				'keyword3'=>array('value'=>urlencode($shDateTime),'color'=>'#00008B'),        // 审核时间keyword3
				'remark'=>array('value'=>urlencode("点击查看详情。"),'color'=>'#00008B'),
				)
			);		
			$data = urldecode(json_encode($template));
			$send_result = send_template_message($data,$access_token);		
		}
		//---------------------
	
	message_app('同意该学生加入成功！', array($urltk), 'success');
}
//--------------

//列表-------------------------
$pindex = max(1, intval($_GPC['page']));
$psize = empty($_GPC['psize'])?0:intval($_GPC['psize']);
if(!in_array($psize, array(20,50,100))) $psize = 20;

if($op == 2){
	//学生申请
	$total = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('onljob_theclass_apply')." AS a LEFT JOIN ".tablename('onljob_user')." AS b ON a.uid = b.uid LEFT JOIN ".tablename('onljob_theclass')." AS c ON a.bjid = c.id WHERE a.weid = '{$_W['uniacid']}' and a.tuid = '{$_W['member']['uid']}' and a.state = '0' {$where}");
	if($total){
		$list = pdo_fetchall("SELECT a.id as sqid,c.*,b.name FROM ".tablename('onljob_theclass_apply')." AS a LEFT JOIN ".tablename('onljob_user')." AS b ON a.uid = b.uid LEFT JOIN ".tablename('onljob_theclass')." AS c ON a.bjid = c.id WHERE a.weid = '{$_W['uniacid']}' and a.tuid = '{$_W['member']['uid']}' and a.state = '0' {$where} ORDER BY a.id DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize);
	}
}else{
	
	//班级管理
	$where = '';
	if($op == 1){
		//在校
		$where .= " and a.uid = '{$_W['member']['uid']}' and a.state = '0'";
	}elseif($op == 3){
	   //转出中的班级
	   $where .= " and a.uid = '{$_W['member']['uid']}' and a.zuid != '0'";
	}elseif($op == 4){
	   //转入审核
	   $where .= " and a.zuid = '{$_W['member']['uid']}'";
	}elseif($op == 5){
		//毕业
		$where .= " and a.uid = '{$_W['member']['uid']}' and a.state = '1'";
	}else{
		$where .= " and (a.uid = '{$_W['member']['uid']}' or a.zuid = '{$_W['member']['uid']}')";
	}
	
	$total = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('onljob_theclass')." AS a LEFT JOIN ".tablename('onljob_user')." AS b ON a.uid = b.uid WHERE a.weid = '{$_W['uniacid']}' {$where}");
	if($total){
		$list = pdo_fetchall("SELECT a.*,b.name FROM ".tablename('onljob_theclass')." AS a LEFT JOIN ".tablename('onljob_user')." AS b ON a.uid = b.uid WHERE a.weid = '{$_W['uniacid']}' {$where} ORDER BY a.id DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize);
	}
	
}
$pager = pagination($total, $pindex, $psize,'', array('before' => 0, 'after' => 0));
include template_app('t_theclass');