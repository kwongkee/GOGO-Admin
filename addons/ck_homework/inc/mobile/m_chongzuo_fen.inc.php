<?php

if (!defined('IN_IA')) {
    exit('Access Denied');
}

global $_W, $_GPC;
require "common.php";
require "public.php";
require "m_common.php";
load()->func('tpl');

$fid = empty($_GPC['fid'])?0:intval($_GPC['fid']);

$urlt = $this->createMobileUrl('m_chongzuo_fen') . '&fid=' . $fid;
$dourl = $this->createMobileUrl('m_chongzuo_fen') . '&fid=' . $fid;
//附件上传文件地址
$url_attachment = $_W['siteroot'] . 'app/index.php?i='.$_GPC['i'].'&c='.$_GPC['c'].'&do=attachment&m='.$_GPC['m'];

//开始考试时间
$starttimes = empty($_GPC['starttimes'])?0:intval($_GPC['starttimes']);
if($starttimes > 0){
	$kstimes = $starttimes;
	$urlt .= '&starttimes = '.$starttimes;
}else{
	$kstimes = $newstmes;
	$urlt .= '&starttimes = '.$newstimed;
}

//获取练习题 
$work_fen = pdo_get('onljob_practice_fen', array('weid' => $_W['uniacid'],'uid' => $_W['member']['uid'],'fid' => $fid));
if (empty($work_fen)) {
	message_app('该练习题不存在或是已经被删除', '', 'error');
}

if($work_fen['state'] > 0){
	message_app('抱歉！该练习题已经作答，不能再次作答！', '', 'error');
}
	  

//默认
$page = empty($_GPC['page'])?1:intval($_GPC['page']);

$datishow = 0;
if(!empty($_GPC['aqid'])){
	$datishow = 1;
}
if(!empty($_GPC['answerarr'])){
	$datishow = 1;
}

//交卷-----------------------------
if($_GPC['actiond'] == 'dosubmit'){
	
	$q_total = intval($_GPC['q_total']);  //题目总数量
	$stratimes = $_GPC['startimes'];      //开始时间
	
	//答题存入---------
	if($datishow == 1 && !empty($_GPC['q_id'])){
	
		$rsdb_que = pdo_get('onljob_questions', array('weid' => $_W['uniacid'],'id' => $_GPC['q_id']), array('type','zqanswer'));
		if($rsdb_que['type'] == 2){
			$answer = substr($_GPC['aqid'], 0, -1);
			$answer = implode('、', explode(',',$answer));
		}elseif($rsdb_que['type'] > 4){
			$answer = implode(',', $_GPC['answerarr']);
		}else{
			$answer = $_GPC['aqid'];
		}
		
		$data = array(
			'zqanswer' => trim($rsdb_que['zqanswer']),
			'answer' => $answer,
			'numberbh' => intval($_GPC['numberbh']),
			'dateline' => time()
		);
		
		
		if($rsdb_que['type'] < 5 && $work_fen['type'] == 0){  //作业错题重做判断非作文题
			if(trim($rsdb_que['zqanswer']) == trim($answer)){
				$data['stateh'] = 0;
			}else{
				$data['stateh'] = 1;
			}
		}
		if($rsdb_que['type'] < 6 && $work_fen['type'] == 1){    //自组题
			if(trim($rsdb_que['zqanswer']) == trim($answer)){
				$data['stateh'] = 0;
			}else{
				$data['stateh'] = 1;
			}
		}
		
		pdo_update('onljob_practice_answer', $data, array('weid' => $_W['uniacid'],'uid' => $_W['member']['uid'],'fid' => $fid,'qid' => $_GPC['q_id']));
		
	}
	//-----------------
	
	//获取已做题数
	$q_total_yz = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('onljob_practice_answer')." WHERE  weid = '{$_W['uniacid']}' and uid = '{$_W['member']['uid']}' and answer != '' and fid = '{$fid}' ");
	if($q_total_yz < $q_total){
		message_app('抱歉！要做完全部的题目才能交卷！', '', 'error');
	}
	
	//修改练习题信息
	$datap = array(
		'stratimes' => $stratimes,
		'dateline' => time()
	);
	
	if($work_fen['type'] == 1){    //自组题
		$datap['state'] = 2;
	}else{
		//需要批改的题目数
		$xpg_total = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('onljob_practice_answer')." AS a LEFT JOIN ".tablename('onljob_questions')." AS b ON a.qid = b.id WHERE  a.weid = '{$_W['uniacid']}' and a.uid = '{$_W['member']['uid']}' and a.fid = '{$fid}' and b.type > 4 ");
		if($xpg_total > 1){
			$datap['state'] = 1;
		}else{
			$datap['state'] = 2;
		}
	}
	
	pdo_update('onljob_practice_fen', $datap, array('weid' => $_W['uniacid'],'uid' => $_W['member']['uid'],'fid' => $fid));
	
	//跳转
	$result_url = $this->createMobileUrl('m_chongzuo_result', array('fid' => $fid));
	message_app('答题完毕，您的客观题部分可以直接查看解析和答案！您的非客观题部分请等候老师批改。', array($result_url), 'success', array('查看答题结果'));

}
//-----------------------

//答题存入--------------------------------
if($datishow == 1 && !empty($_GPC['q_id'])){
	
	$numberbh = $page - 1;
	$rsdb_que = pdo_get('onljob_questions', array('weid' => $_W['uniacid'],'id' => $_GPC['q_id']));
	if($rsdb_que['type'] == 2){
		$answer = substr($_GPC['aqid'], 0, -1);
		$answer = implode('、', explode(',',$answer));
	}elseif($rsdb_que['type'] > 4){
		$answer = implode(',', $_GPC['answerarr']);
	}else{
		$answer = $_GPC['aqid'];
	}
	
	$data = array(
		'zqanswer' => trim($rsdb_que['zqanswer']),
		'answer' => $answer,
		'numberbh' => intval($numberbh),
		'dateline' => time()
	);
	
	if($rsdb_que['type'] < 5 && $work_fen['type'] == 0){  //作业错题重做判断非作文题
		if(trim($rsdb_que['zqanswer']) == trim($answer)){
			$data['stateh'] = 0;
		}else{
			$data['stateh'] = 1;
		}
	}
	if($rsdb_que['type'] < 6 && $work_fen['type'] == 1){    //自组题
		if(trim($rsdb_que['zqanswer']) == trim($answer)){
			$data['stateh'] = 0;
		}else{
			$data['stateh'] = 1;
		}
	}
	
	pdo_update('onljob_practice_answer', $data, array('weid' => $_W['uniacid'],'uid' => $_W['member']['uid'],'fid' => $fid,'qid' => $_GPC['q_id']));
	
}
//---------------------------------------

//列表-------------------------------------/
$total = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('onljob_practice_answer')." where weid = '{$_W['uniacid']}' and uid = '{$_W['member']['uid']}' and fid = '{$fid}' ");

//题目列表-------------------
$listdb = array();
$query = pdo_fetchall("SELECT b.*,a.answer as dpanswer FROM ".tablename('onljob_practice_answer')." AS a LEFT JOIN ".tablename('onljob_questions')." AS b on a.qid = b.id  where a.weid = '{$_W['uniacid']}' and a.uid = '{$_W['member']['uid']}' and a.fid = '{$fid}' ORDER BY b.type ASC LIMIT " . ($page - 1) . ', 1');
foreach ($query as $lk=>$value) {
	if($value['type']==2){
		$value['hdanswer'] = explode('、',$value['dpanswer']);
	}elseif($value['type'] > 4){
		$value['hdanswer'] = explode(',', $value['dpanswer']);
	}else{
		$value['hdanswer'] = $value['dpanswer'];
	}
	$listdb[] = $value;
}


//已答得题情况
$aqid_all = array();
$query = pdo_fetchall("SELECT numberbh FROM ".tablename('onljob_practice_answer')." WHERE  weid = '{$_W['uniacid']}' and uid = '{$_W['member']['uid']}' and fid = '{$fid}' ORDER BY qid ASC ");
foreach ($query as $bb=>$value) {
	$aqid_all[] = $value['numberbh'];
}

include template_app('m_chongzuo_fen');
?>