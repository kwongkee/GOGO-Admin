<?php

defined('IN_IA') or exit('Access Denied');

global $_W, $_GPC;
load()->func('tpl');
$op = $_GPC['op'];

$urlt = $this->createWebUrl('knowledge_chapter');

$newtimes = time();

$pid = intval($_GPC['pid']);
$lesson = pdo_fetch("SELECT * FROM ".tablename('onljob_knowledge')." WHERE weid=:weid AND id=:id", array(':weid'=>$_W['uniacid'],':id'=>$pid));
if(empty($lesson)){
	message("知识点不存在或已被删除！", "", "error");
}

$urlt .= '&pid='.$pid;

//修改
if(checksubmit('add_submit') || checksubmit('edit_submit')){
	
	$data = array();
	$data['parentid']		= intval($pid);
	$data['title']			= trim($_GPC['title']);
	$data['sectiontype']	= intval($_GPC['sectiontype']);
	$data['savetype']		= trim($_GPC['savetype']);
	$data['videourl']		= trim($_GPC['videourl']);
	$data['videotime']		= str_replace("：",":",trim($_GPC['videotime']));
	$data['content']		= $_GPC['content'];
	$data['displayorder']	= intval($_GPC['displayorder']);
	$data['is_free']	    = intval($_GPC['is_free']);
	$data['status']			= intval($_GPC['status']);

	if(empty($data['parentid'])){
		message("知识点不存在或已被删除", '', 'error');
	}
	if(empty($data['title'])){
		message("请填写章节名称！", '', 'error');
	}
	if($data['sectiontype']==1 && empty($data['videourl'])){
		message("请填写章节视频URL！", '', 'error');
	}
	if(!in_array($data['is_free'], array('0','1'))){
		message("请选择是否为试听章节！", '', 'error');
	}
	if(!in_array($data['status'], array('0','1'))){
		message("请选择是否上架！", '', 'error');
	}

	if($data['savetype']==1){//内嵌代码存储方式保留内容的空格
		$data['videourl'] = $_GPC['videourl'];
	}

	//添加
	if(checksubmit('add_submit')){
		 $data['weid'] = $_W['uniacid'];
		 $data['addtime'] = time();
		 pdo_insert('onljob_knowledge_son', $data);
		 message('添加成功', $urlt, 'success');
	}
	
	//修改
	if(!empty($_GPC['refurl'])){
		$urlt = $this->createWebUrl('knowledge');
	}
	if(!empty($_GPC['id'])){
		pdo_update('onljob_knowledge_son', $data, array('id' => $_GPC['id'],'weid' => $_W['uniacid']));
		message('修改成功！', $urlt, 'success');
	}else{
		message('修改失败！', $urlt, 'error');
	}
	
}

//读取
if($op == 'edit'){
	$id = intval($_GPC['id']);
	$section = pdo_get('onljob_knowledge_son', array('id' => $id,'weid' => $_W['uniacid']));
	if (empty($section)) {
		message('不存在或是已经被删除！', $urlt, 'error');
	}
}

//删除---------------
if($op == 'delete'){
	if(!empty($_GPC['refurl'])){
		$urlt = $this->createWebUrl('knowledge');
	}
	$id = intval($_GPC['id']);
	if($id){
		pdo_delete('onljob_knowledge_son', array('id' => $id,'weid' => $_W['uniacid']));
		message('删除成功', $urlt, 'success');
	}else{
		message('删除失败', $urlt, 'error');
	}
}

//批量
if (checksubmit('submit')) { /* 排序 */
	if (is_array($_GPC['sectionorder'])) {
		foreach ($_GPC['sectionorder'] as $sid => $val) {
			$data = array('displayorder' => intval($_GPC['sectionorder'][$sid]));
			pdo_update('onljob_knowledge_son', $data, array('id' => $sid,'weid' => $_W['uniacid']));
		}
	}
	
	message('操作成功!', $urlt, 'success');
}

//列表-------------------------
//排序
$ordersc = array($_GPC['ordersc']=>' selected');
if($_GPC['ordersc']){
	$ordersql = "ORDER BY id ".$_GPC['ordersc'];
}else{
	$ordersql = "ORDER BY displayorder ASC,id DESC";
}

$pindex = max(1, intval($_GPC['page']));
$psize = empty($_GPC['psize'])?0:intval($_GPC['psize']);
if(!in_array($psize, array(20,50,100))) $psize = 20;
$perpages = array($psize => ' selected');

$where = '';

if (!empty($_GPC['titlename'])) {
	$where .= " AND titlename LIKE '%{$_GPC['titlename']}%'";
}

$total = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('onljob_knowledge_son')."  WHERE weid = '{$_W['uniacid']}' and parentid = '{$pid}' {$where}");
if($total){
	$list = pdo_fetchall("SELECT * FROM ".tablename('onljob_knowledge_son')." WHERE weid = '{$_W['uniacid']}' and parentid = '{$pid}' {$where} {$ordersql} LIMIT " . ($pindex - 1) * $psize . ',' . $psize);
}
$pager = pagination($total, $pindex, $psize);

include $this->template('knowledge_son');
?>