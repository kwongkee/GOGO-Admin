<?php
if (!defined('IN_IA')) {
    exit('Access Denied');
}

global $_W, $_GPC;
load()->func('tpl');

require "common.php";
require "public.php";
require "t_common.php";

$tab = trim($_GPC['tab']);
$op = empty($_GPC['op'])?0:intval($_GPC['op']);

$urlt = $this->createMobileUrl('t_question');
$urltk = $this->createMobileUrl('t_question');
$sosourl = $this->createMobileUrl('knowledgesoso');

//知识点分类
$category = pdo_fetchall("SELECT * FROM " . tablename('onljob_class') . " WHERE weid = '{$_W['uniacid']}' and type = 'zsd' ORDER BY listorder ASC, cid DESC", array(), 'cid');
if (!empty($category)) {
	$children = '';
	foreach ($category as $cid => $cate) {
		if (!empty($cate['pid'])) {
			$children[$cate['pid']][$cate['cid']] = array($cate['cid'], $cate['name']);
		}
	}
}

//处理
if(checksubmit('do_submit')){
	
	if (empty ($_GPC['type'])) {
		message_app('请选择题型！', '', 'error');
	}
	if (empty ($_GPC['level'])) {
		message_app('请选择题目难度！', '', 'error');
	}
	if (empty ($_GPC['parentid'])) {
		message_app('请选择题目难度！', '', 'error');
	}
	
	$data = array(
		'type' => intval($_GPC['type']),
		'level' => intval($_GPC['level']),
		'parentid' => intval($_GPC['parentid'])
	);
	
	if(!empty($_GPC['id'])){
		//修改
		pdo_update('onljob_questions', $data, array('id' => $_GPC['id'],'weid' => $_W['uniacid']));

		$urlt .= '&tab=next&id='.$_GPC['id'];
		header("location: $urlt");
		exit();
	}else{
		//添加
		$data['weid'] = $_W['uniacid'];
		$data['uid'] = $_W['member']['uid'];
		$data['addtime'] = time();
		$result = pdo_insert('onljob_questions', $data, true);
		$id = pdo_insertid();
		if (!empty($result) && !empty($id)) {
			$urlt .= '&tab=next&id='.$id;
			header("location: $urlt");
			exit();
		}else{
			message_app('保存失败', array($urlt.'&tab=edit&id='.$id), 'error');
		}
	}
	
}

//保存-----------------
if(checksubmit('save_submit')){
	 
	$type = intval($_GPC['type']);
	if($type == 1){
		$zqanswer = $_GPC['zqanswer1'];
		$answer = implode('\n', $_GPC['answer']);
	}elseif($type == 2){
		$zqanswer = implode('、', $_GPC['zqanswer2']);
		$answer = implode('\n', $_GPC['answer']);
	}elseif($type == 4){
		$zqanswer = $_GPC['zqanswer4'];
	}elseif($type == 3){
		$zqanswer = $_GPC['zqanswer3'];
	}else{
		$zqanswer = $_GPC['zqanswer'];
	}
	
	$data = array(
		'titlename' => $_GPC['titlename'],
		'answer' => $answer,
		'zqanswer' => $zqanswer,
		'content' => $_GPC['content'],
		'addtime' => time()
	);
	
	if (empty ($data['titlename'])) {
		message_app('题目不能为空！', array($urlt.'&tab=next&id='.$_GPC['id']), 'error');
	}
	if (empty ($data['answer']) && $type < 3) {
		message_app('选项不能为空！', array($urlt.'&tab=next&id='.$_GPC['id']), 'error');
	}
	if (empty ($data['zqanswer'])) {
		message_app('答案不能为空！', array($urlt.'&tab=next&id='.$_GPC['id']), 'error');
	}
	if (empty ($data['content'])) {
		message_app('答案解析不能为空！', array($urlt.'&tab=next&id='.$_GPC['id']), 'error');
	}
	
	if(!empty($_GPC['id'])){
		pdo_update('onljob_questions', $data, array('id' => $_GPC['id'],'weid' => $_W['uniacid']));
		message_app('保存成功！', array($urlt), 'success', array('我出的题'));
	}else{
		message_app('保存失败！', array($urlt.'&tab=next&id='.$_GPC['id']), 'error');
	}
}

//保存并发布
if(checksubmit('release_submit')){
	
	$type = intval($_GPC['type']);
	if($type == 1){
		$zqanswer = $_GPC['zqanswer1'];
		$answer = implode('\n', $_GPC['answer']);
	}elseif($type == 2){
		$zqanswer = implode('、', $_GPC['zqanswer2']);
		$answer = implode('\n', $_GPC['answer']);
	}elseif($type == 4){
		$zqanswer = $_GPC['zqanswer4'];
	}elseif($type == 3){
		$zqanswer = $_GPC['zqanswer3'];
	}else{
		$zqanswer = $_GPC['zqanswer'];
	}
	
	$data = array(
		'titlename' => $_GPC['titlename'],
		'answer' => $answer,
		'zqanswer' => $zqanswer,
		'content' => $_GPC['content'],
		'status' => 1,
		'addtime' => time()
	);
	
	if (empty ($data['titlename'])) {
		message_app('题目不能为空！', array($urlt.'&tab=next&id='.$_GPC['id']), 'error');
	}
	if (empty ($data['answer']) && $type < 3) {
		message_app('选项不能为空！', array($urlt.'&tab=next&id='.$_GPC['id']), 'error');
	}
	if (empty ($data['zqanswer'])) {
		message_app('答案不能为空！', array($urlt.'&tab=next&id='.$_GPC['id']), 'error');
	}
	if (empty ($data['content'])) {
		message_app('答案解析不能为空！', array($urlt.'&tab=next&id='.$_GPC['id']), 'error');
	}
	
	if(!empty($_GPC['id'])){
		pdo_update('onljob_questions', $data, array('id' => $_GPC['id'],'weid' => $_W['uniacid']));
		message_app('保存并发布成功！请等待审核！', array($urlt), 'success', array('我出的题'));
	}else{
		message_app('保存失败！', array($urlt.'&tab=next&id='.$_GPC['id']), 'error');
	}
}


if($tab === 'add'){
	//添加
	include template_app('t_question_add');
	exit();
}

if($tab === 'next'){
	//下一步
	$id = intval($_GPC['id']);
	$srdb = pdo_get('onljob_questions', array('id' => $id,'weid' => $_W['uniacid']));
	if (empty($srdb)) {
		message_app('不存在或是已经被删除！', '', 'error');
	}
	
	$answer_arll = explode('\n', $srdb['answer']);
	
	if($srdb['type'] == 2){
		$zqanswerarr = explode('、', $srdb['zqanswer']);
	}
	
	include template_app('t_question_next');
	exit();
}

//删除---------------
if($tab == 'delete'){
	$id = intval($_GPC['id']);
	if($id){
		pdo_delete('onljob_questions', array('id' => $id,'weid' => $_W['uniacid']));
		message_app('删除成功！', array($urlt), 'success', array('我出的题'));
	}else{
		message_app('删除失败', '', 'error');
	}
}

//提交审核-------------
if($tab == 'audit'){
	$id = intval($_GPC['id']);
	$srdb = pdo_get('onljob_questions', array('id' => $id,'weid' => $_W['uniacid']));
	if (empty($srdb)) {
		message_app('不存在或是已经被删除！', '', 'error');
	}
	
	pdo_update('onljob_questions', array('status' => 1), array('id' => $id,'weid' => $_W['uniacid']));
	
	message_app('提交成功！', array($urlt), 'success', array('我出的题'));
}

//读取
if($tab == 'edit' || $tab == 'show'){
	$id = intval($_GPC['id']);
	$srdb = pdo_get('onljob_questions', array('id' => $id,'weid' => $_W['uniacid']));
	if (empty($srdb)) {
		message_app('不存在或是已经被删除！', '', 'error');
	}
	
	//知识点名称
	$srdb_knowle = pdo_get('onljob_knowledge', array('id' => $srdb['parentid'],'weid' => $_W['uniacid']));
	
	$answer_arll = explode('\n',$srdb['answer']);
	
	if($srdb['type'] == 2){
		$zqanswerarr = explode('、',$srdb['zqanswer']);
	}
	
	if($tab == 'show'){
		include template_app('t_question_show');
	}else{
		include template_app('t_question_add');
	}
	exit();
}

//列表-------------------------
$pindex = max(1, intval($_GPC['page']));
$psize = empty($_GPC['psize'])?0:intval($_GPC['psize']);
if(!in_array($psize, array(20,50,100))) $psize = 20;

$where = '';
if($op == 1){
	$where .= " and a.status = '0'";
}elseif($op == 2){
	$where .= " and a.status = '1'";
}elseif($op == 3){
	$where .= " and a.status = '2'";
}elseif($op == 4){
	$where .= " and a.status = '3'";
}

$total = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('onljob_questions')." AS a LEFT JOIN ".tablename('onljob_knowledge')." AS b ON a.parentid = b.id  WHERE a.weid = '{$_W['uniacid']}' and a.uid = '{$_W['member']['uid']}' {$where}");
if($total){
	$list = pdo_fetchall("SELECT a.*,b.titlename as zsdname FROM ".tablename('onljob_questions')." AS a LEFT JOIN ".tablename('onljob_knowledge')." AS b ON a.parentid = b.id WHERE a.weid = '{$_W['uniacid']}' and a.uid = '{$_W['member']['uid']}' {$where} ORDER BY a.id DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize);
}
	
$pager = pagination($total, $pindex, $psize,'', array('before' => 0, 'after' => 0));
	

include template_app('t_question');