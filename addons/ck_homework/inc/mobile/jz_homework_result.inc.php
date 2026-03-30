<?php

if (!defined('IN_IA')) {
    exit('Access Denied');
}

global $_W, $_GPC;
require "common.php";
require "public.php";
require "jz_common.php";
load()->func('tpl');

$fid = empty($_GPC['fid'])?0:intval($_GPC['fid']);
$wid = empty($_GPC['wid'])?0:intval($_GPC['wid']);
$bjid = empty($_GPC['bjid'])?0:intval($_GPC['bjid']);

//地址
$urlt = $this->createMobileUrl('jz_homework_result') . '&fid=' . $fid . '&wid=' . $wid . '&bjid=' . $bjid;

//自己提交的作业 
$rsdb = pdo_get('onljob_work_fen', array('weid' => $_W['uniacid'],'uid' => $_SESSION['hzuid'],'wid' => $wid,'bjid' => $bjid,'fid' => $fid));
if (empty($rsdb)) {
	message_app('不存在或是已经被删除', '', 'error');
}

//做题用时---
//秒
$poortimes = $rsdb['dateline'] -  $rsdb['stratimes'];
//分
$poortimes_f = floor($poortimes/60);
//-------
	
//获取作业信息
$rsdb_work = pdo_get('onljob_work', array('weid' => $_W['uniacid'],'wid' => $wid,'bjid' => $bjid));

//查看批注
if(trim($_GPC['tab']) == 'notationt'){
	
	$qid = empty($_GPC['qid'])?0:intval($_GPC['qid']);
	$answer_show = pdo_get('onljob_work_answer', array('weid' => $_W['uniacid'],'uid' => $_SESSION['hzuid'],'wid' => $wid,'bjid' => $bjid,'fid' => $fid,'qid' => $qid));
	if (empty($answer_show)) {
		message_app('不存在或是已经被删除', '', 'error');
	}
	
	$answer_show['answer'] = explode(',', $answer_show['answer']);
	
	//获取作业信息
	$rsdb_que = pdo_get('onljob_questions', array('weid' => $_W['uniacid'],'id' => $qid), array('titlename','type','zqanswer','content'));
	
	//批注列表
	$notes_list = pdo_fetchall("SELECT * FROM ".tablename('onljob_work_answer_pz')." WHERE weid = '{$_W['uniacid']}' and uid = '{$_SESSION['hzuid']}' and wid = '{$wid}' and bjid = '{$bjid}' and fid = '{$fid}' and qid = '{$qid}' ORDER BY id DESC");
	
	include template_app('jz_homework_notation');
	exit();
}

//查看答案批改详情
if(trim($_GPC['tab']) == 'show'){
	$qid = empty($_GPC['qid'])?0:intval($_GPC['qid']);
	
	$answer_show = pdo_fetchall("SELECT a.*,b.titlename,b.type,b.parentid,b.answer as q_answer,b.zqanswer as q_zqanswer,b.content FROM ".tablename('onljob_work_answer')." AS a LEFT JOIN ".tablename('onljob_questions')." AS b ON a.qid = b.id WHERE a.weid = '{$_W['uniacid']}' and a.uid = '{$_SESSION['hzuid']}' and a.wid = '{$wid}' and a.bjid = '{$bjid}' and a.fid = '{$fid}' and a.qid = {$qid} ORDER BY a.qid DESC LIMIT 0,1");
	
	if($answer_show[0]['type']==2){
		$hdanswer = explode('、',$answer_show[0]['answer']);
	}elseif($answer_show[0]['type'] > 4){
		$hdanswer = explode(',',$answer_show[0]['answer']);
	}else{
		$hdanswer = $answer_show[0]['answer'];
	}
	
	$answer_arll = explode('\n', $answer_show[0]['q_answer']);
	
	if($answer_show[0]['type'] == 2){
		$zqanswerarr = explode('、', $answer_show[0]['q_zqanswer']);
	}
	
	//判断是否有批注
	$total_answer_pg = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('onljob_work_answer_pz')." WHERE weid = '{$_W['uniacid']}' and uid = '{$_SESSION['hzuid']}' and wid = '{$wid}' and bjid = '{$bjid}' and fid = '{$fid}' and qid = '{$qid}' ");
	  
	//上题
	$answer_on = pdo_fetchall("SELECT qid FROM ".tablename('onljob_work_answer')." WHERE weid = '{$_W['uniacid']}' and uid = '{$_SESSION['hzuid']}' and wid = '{$wid}' and bjid = '{$bjid}' and fid = '{$fid}' and qid < {$qid} ORDER BY qid DESC LIMIT 0,1");
	//下题
	$answer_up = pdo_fetchall("SELECT qid FROM ".tablename('onljob_work_answer')." WHERE weid = '{$_W['uniacid']}' and uid = '{$_SESSION['hzuid']}' and wid = '{$wid}' and bjid = '{$bjid}' and fid = '{$fid}' and qid > {$qid} ORDER BY qid ASC LIMIT 0,1");
	
	include template_app('jz_homework_answer');
	exit();
}

//总题数
$total_z = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('onljob_work_answer')." WHERE weid = '{$_W['uniacid']}' and uid = '{$_SESSION['hzuid']}' and wid = '{$wid}' and bjid = '{$bjid}' and fid = '{$fid}' ");
//错题数
$total_cw = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('onljob_work_answer')." WHERE weid = '{$_W['uniacid']}' and uid = '{$_SESSION['hzuid']}' and wid = '{$wid}' and bjid = '{$bjid}' and fid = '{$fid}' and stateh = '1' ");

//获取答题列表-----------------------
$pindex = max(1, intval($_GPC['page']));
$psize = empty($_GPC['psize'])?0:intval($_GPC['psize']);
if(!in_array($psize, array(20,50,100))) $psize = 20;
	
$where = '';
if(trim($_GPC['tab']) == 'wrong'){
	$where .= " and a.stateh = '1'";
}

$total = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('onljob_work_answer')." AS a LEFT JOIN ".tablename('onljob_questions')." AS b ON a.qid = b.id WHERE a.weid = '{$_W['uniacid']}' and a.uid = '{$_SESSION['hzuid']}' and a.wid = '{$wid}' and a.bjid = '{$bjid}' and a.fid = '{$fid}' {$where} ");
if($total){
	$list = pdo_fetchall("SELECT a.*,b.titlename,b.type,b.parentid FROM ".tablename('onljob_work_answer')." AS a LEFT JOIN ".tablename('onljob_questions')." AS b ON a.qid = b.id WHERE a.weid = '{$_W['uniacid']}' and a.uid = '{$_SESSION['hzuid']}' and a.wid = '{$wid}' and a.bjid = '{$bjid}' and a.fid = '{$fid}' {$where} ORDER BY a.qid DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize);
	if (!empty($list)) {
		foreach ($list as $cid => $cate) {
			//累计错误次数
			$cwtotal[$cate['qid']] = pdo_fetchcolumn("SELECT COUNT(*) FROM " . tablename('onljob_work_answer') . " WHERE weid = '{$_W['uniacid']}' and uid = '{$_SESSION['hzuid']}' and qid = '{$cate['qid']}' and stateh = '1'");
			//累计正确次数
			$zqtotal[$cate['qid']] = pdo_fetchcolumn("SELECT COUNT(*) FROM " . tablename('onljob_work_answer') . " WHERE weid = '{$_W['uniacid']}' and uid = '{$_SESSION['hzuid']}' and qid = '{$cate['qid']}' and stateh = '0'");
			
			//获取知识点名称
			$rsdb_knowledge = pdo_get('onljob_knowledge', array('weid' => $_W['uniacid'],'id' => $cate['parentid']), array('titlename'));
			if($rsdb_knowledge){
				$zsdname[$cate['qid']] = $rsdb_knowledge['titlename'];
			}
		}
	}
}

$pager = pagination($total, $pindex, $psize,'', array('before' => 0, 'after' => 0));

include template_app('jz_homework_result');
?>