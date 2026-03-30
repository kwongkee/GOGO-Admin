<?php
if (!defined('IN_IA')) {
    exit('Access Denied');
}

global $_W, $_GPC;
load()->func('tpl');

require "common.php";
require "public.php";
require "t_common.php";

$bjid = empty($_GPC['bjid'])?0:intval($_GPC['bjid']);
$wid = empty($_GPC['wid'])?0:intval($_GPC['wid']);
$op = empty($_GPC['op'])?0:intval($_GPC['op']);
$tab = trim($_GPC['tab']);
$urltk = $this->createMobileUrl('t_work').'&bjid=' . $bjid . '&wid=' . $wid;
$datatimes = time();

//科目
$category = pdo_fetchall("SELECT * FROM " . tablename('onljob_class') . " WHERE weid = '{$_W['uniacid']}' and pid = '0' and type = 'xk' ORDER BY listorder ASC, cid DESC", array(), 'cid');

//年级
$category_nj = pdo_fetchall("SELECT * FROM " . tablename('onljob_class') . " WHERE weid = '{$_W['uniacid']}' and pid = '0' and type = 'nj' ORDER BY listorder ASC, cid DESC", array(), 'cid');

//知识点分类
$category_zsd = pdo_fetchall("SELECT * FROM " . tablename('onljob_class') . " WHERE weid = '{$_W['uniacid']}' and type = 'zsd' ORDER BY listorder ASC, cid DESC", array(), 'cid');
if (!empty($category_zsd)) {
	$children = '';
	foreach ($category_zsd as $cid => $cate) {
		if (!empty($cate['pid'])) {
			$children[$cate['pid']][$cate['cid']] = array($cate['cid'], $cate['name']);
		}
	}
}

$nubmg_arll =array('','一','二','三','四','五','六','七','八','九','十');

//显示内容 
$srdb = pdo_get('onljob_work', array('wid' => $wid,'bjid' => $bjid,'weid' => $_W['uniacid']));
if (empty($srdb)) {
	message_app('参数错误！访问失败！', '', 'error');
}

//总题数
$total_q = pdo_fetchcolumn("SELECT COUNT(*) FROM " . tablename('onljob_work_questions') . " WHERE weid = '{$_W['uniacid']}' and bjid = '{$bjid}' and wid = '{$wid}'");
//主观题数
$total_zg = pdo_fetchcolumn("SELECT COUNT(*) FROM " . tablename('onljob_work_questions') . " AS a LEFT JOIN ".tablename('onljob_questions')." AS b on a.qid = b.id WHERE a.weid = '{$_W['uniacid']}' and a.bjid = '{$bjid}' and a.wid = '{$wid}' and b.type = '5'");
//非观题数
$total_fzg = $total_q - $total_zg;

if($tab == 'hand'){
	//已交作业学生
	//列表---------------------------------
	$pindex = max(1, intval($_GPC['page']));
	$psize = empty($_GPC['psize'])?0:intval($_GPC['psize']);
	if(!in_array($psize, array(20,50,100))) $psize = 20;
	
	$where = '';
	if($op == 1){       $where = " and a.state = '0'";
	}elseif($op == 2){  $where = " and a.state = '1'";}
	
	$total = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('onljob_work_fen')." AS a LEFT JOIN ".tablename('onljob_theclass_apply')." AS b ON a.uid = b.uid LEFT JOIN ".tablename('onljob_user')." AS c ON a.uid = c.uid WHERE a.weid = '{$_W['uniacid']}' and a.wid = '{$wid}' and a.bjid = '{$bjid}' and b.bjid = '{$bjid}' and b.state = '1' {$where}");
	if($total){
		$list = pdo_fetchall("SELECT a.*,c.name,c.headimg FROM ".tablename('onljob_work_fen')." AS a LEFT JOIN ".tablename('onljob_theclass_apply')." AS b ON a.uid = b.uid LEFT JOIN ".tablename('onljob_user')." AS c ON a.uid = c.uid WHERE a.weid = '{$_W['uniacid']}' and a.wid = '{$wid}' and a.bjid = '{$bjid}' and b.bjid = '{$bjid}' and b.state = '1' {$where} ORDER BY a.fid DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize);
	}
	
	$pager = pagination($total, $pindex, $psize,'', array('before' => 0, 'after' => 0));
	
}elseif($tab == 'whand'){
	//未交作业学生
	$list = pdo_fetchall("SELECT a.uid,b.name,b.headimg FROM ".tablename('onljob_theclass_apply')." AS a LEFT JOIN ".tablename('onljob_user')." AS b ON a.uid = b.uid WHERE a.weid = '{$_W['uniacid']}' and a.bjid = '{$bjid}' and a.state = '1' {$where} ORDER BY a.id DESC ");
	if (!empty($list)) {
		$list_wj = array();
		foreach ($list as $k => $value) {
			$xs_fenf = pdo_get('onljob_work_fen', array('uid' => $value['uid'],'wid' => $wid,'bjid' => $bjid,'weid' => $_W['uniacid']));
			if (empty($xs_fenf)) {
				$list_wj[] = $value;
			}
		}
	}
	
}elseif($tab == 'delete'){
	//删除作业
	pdo_delete('onljob_work', array('wid' => $wid,'bjid' => $bjid,'weid' => $_W['uniacid']));
	pdo_delete('onljob_work_questions', array('wid' => $wid,'bjid' => $bjid,'weid' => $_W['uniacid']));
	pdo_delete('onljob_work_fen', array('wid' => $wid,'bjid' => $bjid,'weid' => $_W['uniacid']));
	pdo_delete('onljob_work_answer', array('wid' => $wid,'bjid' => $bjid,'weid' => $_W['uniacid']));
	pdo_delete('onljob_work_answer_pz', array('wid' => $wid,'bjid' => $bjid,'weid' => $_W['uniacid']));
	
	message_app('删除成功！', array($this->createMobileUrl('t_theclass_show').'&op=3&id='.$bjid), 'success', array('查看班级作业'));
	exit();
}elseif($tab == 'auditp'){
	//确认已批改
	pdo_update('onljob_work', array('state' => 1), array('wid' => $wid,'bjid' => $bjid,'weid' => $_W['uniacid']));
	message_app('操作成功！', array($this->createMobileUrl('t_theclass_show').'&op=3&id='.$bjid), 'success', array('查看班级作业'));
	exit();
}elseif($tab == 'show'){
	//题目显示
	$id = intval($_GPC['id']);
	$srdb_qu = pdo_get('onljob_questions', array('id' => $id,'weid' => $_W['uniacid']));
	if (empty($srdb_qu)) {
		message_app('不存在或是已经被删除！', '', 'error');
	}
	
	//知识点名称
	$srdb_knowle = pdo_get('onljob_knowledge', array('id' => $srdb_qu['parentid'],'weid' => $_W['uniacid']));
	$answer_arll = explode('\n',$srdb_qu['answer']);
	if($srdb_qu['type'] == 2){
		$zqanswerarr = explode('、',$srdb_qu['zqanswer']);
	}
	
	include template_app('t_work_show');
	exit();
}else{
	
	//列表--------------------------
	$pindex = max(1, intval($_GPC['page']));
	$psize = empty($_GPC['psize'])?0:intval($_GPC['psize']);
	if(!in_array($psize, array(20,50,100))) $psize = 20;
	
	$where = '';
	
	$total = pdo_fetchcolumn("SELECT COUNT(*) FROM " . tablename('onljob_work_questions') . " AS a LEFT JOIN ".tablename('onljob_questions')." AS b on a.qid = b.id WHERE a.weid = '{$_W['uniacid']}' and a.bjid = '{$bjid}' and a.wid = '{$wid}' {$where}");
	if($total){
		$list = pdo_fetchall("SELECT b.* FROM " . tablename('onljob_work_questions') . " AS a LEFT JOIN ".tablename('onljob_questions')." AS b on a.qid = b.id WHERE a.weid = '{$_W['uniacid']}' and a.bjid = '{$bjid}' and a.wid = '{$wid}' {$where} ORDER BY a.qid DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize);
		if (!empty($list)) {
			foreach ($list as $k => $value) {
				//获取知识点名称
				$zsdname[$value['id']] = pdo_get('onljob_knowledge', array('weid' => $_W['uniacid'], 'id' => $value['parentid']), array('titlename'));
			}
		}
	}
		
	$pager = pagination($total, $pindex, $psize,'', array('before' => 0, 'after' => 0));
		
}

include template_app('t_work');