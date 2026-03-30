<?php

if (!defined('IN_IA')) {
    exit('Access Denied');
}

global $_W, $_GPC;
require "common.php";
require "public.php";
require "m_common.php";

$xkid = empty($_GPC['xkid'])?0:intval($_GPC['xkid']);
$op = empty($_GPC['op'])?0:intval($_GPC['op']);
$tab = trim($_GPC['tab']);

$urlt = $this->createMobileUrl('m_zuowen');
$urltk = $this->createMobileUrl('m_zuowen') . "&tab=" . $tab;

//科目
$category = pdo_fetchall("SELECT * FROM " . tablename('onljob_class') . " WHERE weid = '{$_W['uniacid']}' and pid = '0' and type = 'xk' ORDER BY listorder ASC, cid DESC", array(), 'cid');

//年级
$category_nj = pdo_fetchall("SELECT * FROM " . tablename('onljob_class') . " WHERE weid = '{$_W['uniacid']}' and pid = '0' and type = 'nj' ORDER BY listorder ASC, cid DESC", array(), 'cid');

//查看批注
if($tab == 'notationt'){
	
	$qid = empty($_GPC['qid'])?0:intval($_GPC['qid']);
	$fid = empty($_GPC['fid'])?0:intval($_GPC['fid']);
	$wid = empty($_GPC['wid'])?0:intval($_GPC['wid']);
	$bjid = empty($_GPC['bjid'])?0:intval($_GPC['bjid']);
	
	$urlt .= '&fid=' . $fid . '&wid=' . $wid . '&bjid=' . $bjid;
	
	$answer_show = pdo_get('onljob_work_answer', array('weid' => $_W['uniacid'],'uid' => $_W['member']['uid'],'wid' => $wid,'bjid' => $bjid,'fid' => $fid,'qid' => $qid));
	if (empty($answer_show)) {
		message_app('不存在或是已经被删除', '', 'error');
	}
	
	//获取作业信息
	$rsdb_que = pdo_get('onljob_questions', array('weid' => $_W['uniacid'],'id' => $qid), array('titlename','type','zqanswer','content'));
	
	//批注列表
	$notes_list = pdo_fetchall("SELECT * FROM ".tablename('onljob_work_answer_pz')." WHERE weid = '{$_W['uniacid']}' and uid = '{$_W['member']['uid']}' and wid = '{$wid}' and bjid = '{$bjid}' and fid = '{$fid}' and qid = '{$qid}' ORDER BY id DESC");
	
	include template_app('m_homework_notation');
	exit();
}

//查看答案批改详情
if($tab == 'show'){

	$qid = empty($_GPC['qid'])?0:intval($_GPC['qid']);
	$fid = empty($_GPC['fid'])?0:intval($_GPC['fid']);
	$wid = empty($_GPC['wid'])?0:intval($_GPC['wid']);
	$bjid = empty($_GPC['bjid'])?0:intval($_GPC['bjid']);
	
	$urlt .= '&fid=' . $fid . '&wid=' . $wid . '&bjid=' . $bjid;
	
	//获取作业信息
	$rsdb_work = pdo_get('onljob_work', array('weid' => $_W['uniacid'],'wid' => $wid,'bjid' => $bjid));
	
	//显示
	$answer_show = pdo_fetchall("SELECT a.*,b.titlename,b.type,b.parentid,b.answer as q_answer,b.zqanswer as q_zqanswer,b.content FROM ".tablename('onljob_work_answer')." AS a LEFT JOIN ".tablename('onljob_questions')." AS b ON a.qid = b.id WHERE a.weid = '{$_W['uniacid']}' and a.uid = '{$_W['member']['uid']}' and a.wid = '{$wid}' and a.bjid = '{$bjid}' and a.fid = '{$fid}' and a.qid = {$qid} ORDER BY a.qid DESC LIMIT 0,1");
	
	if($answer_show[0]['type']==2){
		$hdanswer = explode('、',$answer_show[0]['answer']);
	}else{
		$hdanswer = $answer_show[0]['answer'];
	}
	
	$answer_arll = explode('\n', $answer_show[0]['q_answer']);
	
	if($answer_show[0]['type'] == 2){
		$zqanswerarr = explode('、', $answer_show[0]['q_zqanswer']);
	}
	
	//判断是否有批注
	$total_answer_pg = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('onljob_work_answer_pz')." WHERE weid = '{$_W['uniacid']}' and uid = '{$_W['member']['uid']}' and wid = '{$wid}' and bjid = '{$bjid}' and fid = '{$fid}' and qid = '{$qid}' ");
	
	include template_app('m_homework_answer');
	exit();
}


//列表------------------
$pindex = max(1, intval($_GPC['page']));
$psize = empty($_GPC['psize'])?0:intval($_GPC['psize']);
if(!in_array($psize, array(20,50,100))) $psize = 20;

$where = '';

//类型
if(!empty($op)){
	//获取学科id
	if($op == 1){
		$classname = "英语";
	}elseif($op == 2){
		$classname = "语文";
	}
	
	$totalxk = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('onljob_class') . " WHERE weid = '{$_W['uniacid']}' and pid = '0' and type = 'xk' and name = '{$classname}'");
	if($totalxk){
		$where .= " AND a.bjid in (SELECT d.bjid FROM ".tablename('onljob_theclass_apply')." AS d LEFT JOIN ".tablename('onljob_theclass')." AS c ON d.bjid = c.id WHERE b.weid = '{$_W['uniacid']}' and d.uid = '{$_W['member']['uid']}' and c.xkid in (SELECT cid FROM " . tablename('onljob_class') . " WHERE weid = '{$_W['uniacid']}' and pid = '0' and type = 'xk' and name = '{$classname}' ))";
	}
}

if (!empty($_GPC['keyword'])) {
	$where .= " AND b.titlename LIKE '%{$_GPC['keyword']}%'";
}


$total = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('onljob_work_answer')." AS a LEFT JOIN ".tablename('onljob_questions')." AS b ON a.qid = b.id WHERE a.weid = '{$_W['uniacid']}' and a.uid = '{$_W['member']['uid']}' and b.type = 6 {$where} ");
if($total){
	$list = pdo_fetchall("SELECT a.*,b.titlename,b.type,b.parentid FROM ".tablename('onljob_work_answer')." AS a LEFT JOIN ".tablename('onljob_questions')." AS b ON a.qid = b.id WHERE a.weid = '{$_W['uniacid']}' and a.uid = '{$_W['member']['uid']}' and b.type =6 {$where} ORDER BY a.qid DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize);
}

$pager = pagination($total, $pindex, $psize,'', array('before' => 0, 'after' => 0));

include template_app('m_zuowen');
?>