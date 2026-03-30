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
$ob = empty($_GPC['ob'])?0:intval($_GPC['ob']);
$tab = trim($_GPC['tab']);

$urlt = $this->createMobileUrl('m_cwanswer');

//学生总结
if(checksubmit('s_comment_submit')){
    $fid = empty($_GPC['fid'])?0:intval($_GPC['fid']);
    $wid = empty($_GPC['wid'])?0:intval($_GPC['wid']);
    $bjid = empty($_GPC['bjid'])?0:intval($_GPC['bjid']);
    $qid = empty($_GPC['qid'])?0:intval($_GPC['qid']);
    $uid = empty($_GPC['uid'])?0:intval($_GPC['uid']);
    $data = array(
        'student_comment' => trim($_GPC['student_comment'])
    );
    $condition = array(
        'fid' => $fid,
        'wid' => $wid,
        'bjid' => $bjid,
        'qid' => $qid,
        'uid' => $uid
    );

    $result = pdo_update('onljob_work_answer', $data, $condition);
    if (!empty($result)) {
        message_app('保存成功！', array($urlt), 'success');
    }else{
        message_app('保存失败', array(''), 'error');
    }
    exit();
}

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
	
	$answer_show['answer'] = explode(',', $answer_show['answer']);
	
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
	$total_answer_pg = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('onljob_work_answer_pz')." WHERE weid = '{$_W['uniacid']}' and uid = '{$_W['member']['uid']}' and wid = '{$wid}' and bjid = '{$bjid}' and fid = '{$fid}' and qid = '{$qid}' ");
	  
	//上题
	$answer_on = pdo_fetchall("SELECT qid FROM ".tablename('onljob_work_answer')." WHERE weid = '{$_W['uniacid']}' and uid = '{$_W['member']['uid']}' and wid = '{$wid}' and bjid = '{$bjid}' and fid = '{$fid}' and qid < {$qid} ORDER BY qid DESC LIMIT 0,1");
	//下题
	$answer_up = pdo_fetchall("SELECT qid FROM ".tablename('onljob_work_answer')." WHERE weid = '{$_W['uniacid']}' and uid = '{$_W['member']['uid']}' and wid = '{$wid}' and bjid = '{$bjid}' and fid = '{$fid}' and qid > {$qid} ORDER BY qid ASC LIMIT 0,1");
	
	include template_app('m_homework_answer');
	exit();
}

//列表------------------
$pindex = max(1, intval($_GPC['page']));
$psize = empty($_GPC['psize'])?0:intval($_GPC['psize']);
if(!in_array($psize, array(20,50,100))) $psize = 20;


//当天
$newsday = date("Y-m-d", time());
$timeday = strtotime($newsday);

$day_sec = 60*60*24;
$day_last_sec = $day_sec - 1;
$where = '';
if($op == '1'){
    //今日错题
    $tiltename = date("Y年m月d日", time());
    $where .= " and FROM_UNIXTIME(a.dateline,'%Y-%m-%d') = '{$newsday}'";
}elseif($op == '2'){
    //昨日
    $timecha = $timeday - $day_sec;
    $tiltename = date("Y年m月d日", $timecha);
    $yesterday = date("Y-m-d", $timecha);
    $where .= " and FROM_UNIXTIME(a.dateline,'%Y-%m-%d') = '{$yesterday}'";
}elseif($op == '3'){
    //近七天
    $timecha = $timeday - $day_sec * 6;
    $tiltename = date("Y年m月d日", $timecha).'至'.date("Y年m月d日", $timeday);
    $where .= " and a.dateline between '{$timecha}' and '".($timeday+$day_last_sec)."'";
}elseif($op == "4"){
    //当月
    $month_first_day = date("Y-m", time());
    $month_first_day = strtotime($month_first_day);
    $tiltename = date("Y年m月d日", $month_first_day).'至'.date("Y年m月d日", $timeday);
    $where .= " and a.dateline between '{$month_first_day}' and '".($timeday+$day_last_sec)."'";
}else{
    $tiltename = "全部";
}

//学科搜索
if(!empty($xkid)){
	$where .= " AND a.bjid in (SELECT d.bjid FROM ".tablename('onljob_theclass_apply')." AS d LEFT JOIN ".tablename('onljob_theclass')." AS c ON d.bjid = c.id WHERE b.weid = '{$_W['uniacid']}' and d.uid = '{$_W['member']['uid']}' and c.xkid = '{$xkid}')";
}

$total = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('onljob_work_answer')." AS a LEFT JOIN ".tablename('onljob_questions')." AS b ON a.qid = b.id WHERE a.weid = '{$_W['uniacid']}' and a.uid = '{$_W['member']['uid']}' and a.stateh = '1' and b.type < 6 {$where} ");
if($total){
	$list = pdo_fetchall("SELECT a.*,b.titlename,b.type,b.parentid FROM ".tablename('onljob_work_answer')." AS a LEFT JOIN ".tablename('onljob_questions')." AS b ON a.qid = b.id WHERE a.weid = '{$_W['uniacid']}' and a.uid = '{$_W['member']['uid']}'  and a.stateh = '1' and b.type < 6 {$where} ORDER BY a.qid DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize);
	if (!empty($list)) {
		foreach ($list as $cid => $cate) {
			//累计错误次数
			$cwtotal[$cate['qid']] = pdo_fetchcolumn("SELECT COUNT(*) FROM " . tablename('onljob_work_answer') . " WHERE weid = '{$_W['uniacid']}' and uid = '{$_W['member']['uid']}' and qid = '{$cate['qid']}' and stateh = '1'");
			//累计正确次数
			$zqtotal[$cate['qid']] = pdo_fetchcolumn("SELECT COUNT(*) FROM " . tablename('onljob_work_answer') . " WHERE weid = '{$_W['uniacid']}' and uid = '{$_W['member']['uid']}' and qid = '{$cate['qid']}' and stateh = '0'");
			
			//获取知识点名称
			$rsdb_knowledge = pdo_get('onljob_knowledge', array('weid' => $_W['uniacid'],'id' => $cate['parentid']), array('titlename'));
			if($rsdb_knowledge){
				$zsdname[$cate['qid']] = $rsdb_knowledge['titlename'];
			}
		}
	}
}

$pager = pagination($total, $pindex, $psize,'', array('before' => 0, 'after' => 0));

include template_app('m_cwanswer');
?>