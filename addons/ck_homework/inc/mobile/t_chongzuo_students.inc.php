<?php
if (!defined('IN_IA')) {
    exit('Access Denied');
}

global $_W, $_GPC;
load()->func('tpl');

require "common.php";
require "public.php";
require "t_common.php";

load()->func('communication');
load()->func('file');

$fid = empty($_GPC['fid'])?0:intval($_GPC['fid']);
$wid = empty($_GPC['wid'])?0:intval($_GPC['wid']);
$bjid = empty($_GPC['bjid'])?0:intval($_GPC['bjid']);

$op = trim($_GPC['op']);
$ob = trim($_GPC['ob']);

$urltk = $this->createMobileUrl('t_chongzuo_students') . '&fid=' . $fid . '&wid=' . $wid . '&bjid=' . $bjid;

//科目
$category = pdo_fetchall("SELECT * FROM " . tablename('onljob_class') . " WHERE weid = '{$_W['uniacid']}' and pid = '0' and type = 'xk' ORDER BY listorder ASC, cid DESC", array(), 'cid');

//年级
$category_nj = pdo_fetchall("SELECT * FROM " . tablename('onljob_class') . " WHERE weid = '{$_W['uniacid']}' and pid = '0' and type = 'nj' ORDER BY listorder ASC, cid DESC", array(), 'cid');

//学生提交的练习题 
$rsdb = pdo_get('onljob_practice_fen', array('weid' => $_W['uniacid'],'wid' => $wid,'bjid' => $bjid,'fid' => $fid));
if (empty($rsdb)) {
	message_app('不存在或是已经被删除', '', 'error');
}

//学生姓名
$rsdb_userl = pdo_get('onljob_user', array('weid' => $_W['uniacid'],'uid' => $rsdb['uid']), array('name'));


//做题用时---
//秒
$poortimes = $rsdb['dateline'] -  $rsdb['stratimes'];
//分
$poortimes_f = floor($poortimes/60);
//-------
	
//获取作业信息
$rsdb_work = pdo_get('onljob_work', array('weid' => $_W['uniacid'],'wid' => $wid,'bjid' => $bjid));

//批注提交处理
if(checksubmit('save_submit')){
	
	$qid = empty($_GPC['qid'])?0:intval($_GPC['qid']);
	if (empty($qid)) {
		message_app('参数错误！无法访问！', array($urltk), 'error');
	}
	
	if (empty($_GPC['infotext'])) {
		message_app('评论不能为空！', '', 'error');
	}
	$data = array(
		'weid' => $_W['uniacid'],
		'uid' => $rsdb['uid'],
		'qid' => intval($_GPC['qid']),
		'fid' => intval($fid),
		'wid' => intval($wid),
		'bjid' => intval($bjid),
		'infotext' => $_GPC['infotext'],
		'xtag' => intval($_GPC['xtag']),
		'ytag' => intval($_GPC['ytag']),
		'tuxing' => $_GPC['tuxing'],
		'yanse' => $_GPC['yanse'],
		'audio' => trim($_GPC['audio']),
		'audio_time' => intval($_GPC['audio_time']),
		'audio_local' => trim($_GPC['audio_local']),
		'amrurl' => trim($_GPC['filePath']),
		'qnmp3url' => trim($_GPC['baseUrl']),
		'mp3url' => trim($_GPC['mp3_filename']),
		'dateline' => time()
	);
	
	$result = pdo_insert('onljob_practice_answer_pz', $data);
	if (!empty($result)) {
		message_app('批注保存成功！', array($urltk.'&op=edit&ob=notes&qid='.$qid), 'success');
	}else{
		message_app('保存失败', array($urltk.'&op=edit&ob=notes&qid='.$qid), 'error');
	}
	exit();
	
}

//删除批注
if($op === 'delete'){
	$pzid = empty($_GPC['pzid'])?0:intval($_GPC['pzid']);
	$srdb = pdo_get('onljob_practice_answer_pz', array('id' => $pzid,'qid' => $_GPC['qid'],'weid' => $_W['uniacid']));
	if ($srdb) {
		pdo_delete('onljob_practice_answer_pz', array('id' => $pzid,'qid' => $_GPC['qid'],'weid' => $_W['uniacid']));
		if($srdb['amrurl']){file_delete($srdb['amrurl']);}
		if($srdb['mp3url']){file_delete($srdb['mp3url']);}
		message_app('删除成功！', array($urltk.'&op=edit&ob=notes&qid='.$_GPC['qid']), 'success');
	}else{
		message_app('删除失败！', '', 'error');
	}
}

//编辑
if($op === 'edit'){
	$qid = empty($_GPC['qid'])?0:intval($_GPC['qid']);
	if (empty($qid)) {
		message_app('参数错误！无法访问！', '', 'error');
	}
	
	$answer_show = pdo_fetchall("SELECT a.*,b.titlename,b.type,b.parentid,b.answer as q_answer,b.zqanswer as q_zqanswer,b.content FROM ".tablename('onljob_practice_answer')." AS a LEFT JOIN ".tablename('onljob_questions')." AS b ON a.qid = b.id WHERE a.weid = '{$_W['uniacid']}' and a.wid = '{$wid}' and a.bjid = '{$bjid}' and a.fid = '{$fid}' and a.qid = {$qid} ORDER BY a.qid DESC LIMIT 0,1");
	
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
	  
	//上题
	$answer_on = pdo_fetchall("SELECT qid FROM ".tablename('onljob_practice_answer')." WHERE weid = '{$_W['uniacid']}' and wid = '{$wid}' and bjid = '{$bjid}' and fid = '{$fid}' and qid < {$qid} ORDER BY qid DESC LIMIT 0,1");
	//下题
	$answer_up = pdo_fetchall("SELECT qid FROM ".tablename('onljob_practice_answer')." WHERE weid = '{$_W['uniacid']}' and wid = '{$wid}' and bjid = '{$bjid}' and fid = '{$fid}' and qid > {$qid} ORDER BY qid ASC LIMIT 0,1");
	
	//主观题批改
	if($ob === 'pigai'){	
		pdo_update('onljob_practice_answer', array('pgstast' => 1,'stateh' => $_GPC['stateh']), array('weid' => $_W['uniacid'],'wid' => $wid,'bjid' => $bjid,'fid' => $fid,'qid' => $qid));
		
		//未批改数量
		$xpg_total = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('onljob_practice_answer')." AS a LEFT JOIN ".tablename('onljob_questions')." AS b ON a.qid = b.id WHERE  a.weid = '{$_W['uniacid']}' and a.fid = '{$fid}' and a.pgstast = '0' and b.type > 4 ");
		if($xpg_total < 1){
			//修改作业状态
			pdo_update('onljob_practice_fen', array('state' => 2), array('weid' => $_W['uniacid'],'fid' => $fid));
		}
		
		message_app('批改成功！', array($urltk.'&op=edit&qid='.$_GPC['qid']), 'success');
	}
	
	//练习批改
	if($ob === 'zwpigai'){
		
		pdo_update('onljob_practice_answer', array('pgstast' => 1,'scorepf' => intval($_GPC['scorepf'])), array('weid' => $_W['uniacid'],'wid' => $wid,'bjid' => $bjid,'fid' => $fid,'qid' => $qid));
		
		//未批改数量
		$xpg_total = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('onljob_practice_answer')." AS a LEFT JOIN ".tablename('onljob_questions')." AS b ON a.qid = b.id WHERE  a.weid = '{$_W['uniacid']}' and a.fid = '{$fid}' and a.pgstast = '0' and b.type > 4 ");
		if($xpg_total < 1){
			//修改习题状态
			pdo_update('onljob_practice_fen', array('state' => 2), array('weid' => $_W['uniacid'],'fid' => $fid));
		}
		
		message_app('批改打分成功！', array($urltk.'&op=edit&qid='.$_GPC['qid']), 'success');
	}
	
	//批注
	if($ob === 'notes'){
		
		//批注列表
		$notes_list = pdo_fetchall("SELECT * FROM ".tablename('onljob_practice_answer_pz')." WHERE weid = '{$_W['uniacid']}' and wid = '{$wid}' and bjid = '{$bjid}' and fid = '{$fid}' and qid = {$qid} ORDER BY id DESC");
		
		//下载MP3文件
		$notes_list_ulp = pdo_fetchall("SELECT * FROM " . tablename('onljob_practice_answer_pz') . " WHERE weid = '{$_W['uniacid']}' and uid = '{$_W['member']['uid']}' and qid = '{$qid}' and wid = '{$wid}' and mp3url = '' ORDER BY id DESC", array(), 'id');
		if (!empty($notes_list_ulp)) {
			foreach ($notes_list_ulp as $key => $value) {
				if( @fopen( $value['qnmp3url'], 'r' ) ){ 
					$mp3_file_name = random_filename('audio','mp3');
					
					ob_start();
					readfile($value['qnmp3url']);
					$img  = ob_get_contents();
					ob_end_clean();
					$size = strlen($img);
					$fp = fopen(ATTACHMENT_ROOT . $mp3_file_name, 'a');
					fwrite($fp, $img);
					fclose($fp);
					
					if(file_exists(ATTACHMENT_ROOT . $mp3_file_name)){
						pdo_update('onljob_practice_answer_pz', array('mp3url' => $mp3_file_name), array('id' => $value['id'],'weid' => $_W['uniacid']));
					}
					
				}
			}
		}
		//---------------------
		
		
		include template_app('t_chongzuo_students_notes');
	}else{
		include template_app('t_chongzuo_students_edit');
	}
	exit();
}


//总题数
$total_z = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('onljob_practice_answer')." WHERE weid = '{$_W['uniacid']}' and wid = '{$wid}' and bjid = '{$bjid}' and fid = '{$fid}' ");
//错题数
$total_cw = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('onljob_practice_answer')." WHERE weid = '{$_W['uniacid']}' and wid = '{$wid}' and bjid = '{$bjid}' and fid = '{$fid}' and stateh = '1' ");

//获取答题列表-----------------------
$pindex = max(1, intval($_GPC['page']));
$psize = empty($_GPC['psize'])?0:intval($_GPC['psize']);
if(!in_array($psize, array(20,50,100))) $psize = 20;
	
$where = '';
$tab = empty($_GPC['tab'])?0:intval($_GPC['tab']);
if($tab == '1'){
	$where .= " and a.stateh = '1'";
}elseif($tab == '2'){
	$where .= " and b.type > 4 ";
}

$total = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('onljob_practice_answer')." AS a LEFT JOIN ".tablename('onljob_questions')." AS b ON a.qid = b.id WHERE a.weid = '{$_W['uniacid']}' and a.wid = '{$wid}' and a.bjid = '{$bjid}' and a.fid = '{$fid}' {$where} ");
if($total){
	$list = pdo_fetchall("SELECT a.*,b.titlename,b.type,b.parentid FROM ".tablename('onljob_practice_answer')." AS a LEFT JOIN ".tablename('onljob_questions')." AS b ON a.qid = b.id WHERE a.weid = '{$_W['uniacid']}' and a.wid = '{$wid}' and a.bjid = '{$bjid}' and a.fid = '{$fid}' {$where} ORDER BY a.qid DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize);
	if (!empty($list)) {
		foreach ($list as $cid => $cate) {
			//累计错误次数
			$cwtotal[$cate['qid']] = pdo_fetchcolumn("SELECT COUNT(*) FROM " . tablename('onljob_practice_answer') . " WHERE weid = '{$_W['uniacid']}' and uid = '{$_W['member']['uid']}' and qid = '{$cate['qid']}' and stateh = '1'");
			//累计正确次数
			$zqtotal[$cate['qid']] = pdo_fetchcolumn("SELECT COUNT(*) FROM " . tablename('onljob_practice_answer') . " WHERE weid = '{$_W['uniacid']}' and uid = '{$_W['member']['uid']}' and qid = '{$cate['qid']}' and stateh = '0'");
			
			//获取知识点名称
			$rsdb_knowledge = pdo_get('onljob_knowledge', array('weid' => $_W['uniacid'],'id' => $cate['parentid']), array('titlename'));
			if($rsdb_knowledge){
				$zsdname[$cate['qid']] = $rsdb_knowledge['titlename'];
			}
		}
	}
}

$pager = pagination($total, $pindex, $psize,'', array('before' => 0, 'after' => 0));	

include template_app('t_chongzuo_students');