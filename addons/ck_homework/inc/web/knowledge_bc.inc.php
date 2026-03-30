<?php

defined('IN_IA') or exit('Access Denied');

global $_W, $_GPC;
load()->func('tpl');
$op = $_GPC['op'];
//知识点编号
$parentid = empty($_GPC['parentid'])?0:intval($_GPC['parentid']);

$urlt = $this->createWebUrl('knowledge_bc');

$newtimes = time();

//分类
$category = pdo_fetchall("SELECT * FROM " . tablename('onljob_class') . " WHERE weid = '{$_W['uniacid']}' and type = 'zsd' ORDER BY listorder ASC, cid DESC", array(), 'cid');
if (!empty($category)) {
	$children = '';
	foreach ($category as $cid => $cate) {
		if (!empty($cate['pid'])) {
			$children[$cate['pid']][$cate['cid']] = array($cate['cid'], $cate['name']);
		}
	}
}

/* VIP等级列表 */
$level_list = pdo_fetchall("SELECT * FROM " . tablename('onljob_vip_level') . " WHERE weid = '{$_W['uniacid']}' and is_show = '1' ORDER BY sort ASC, id DESC", array(), 'id');

//修改
if(checksubmit('add_submit') || checksubmit('edit_submit')){
	
	$data = array('status' => intval($_GPC['status']));
	
	//修改
	if(!empty($_GPC['id'])){
		pdo_update('onljob_knowledge_message', $data, array('id' => $_GPC['id'],'weid' => $_W['uniacid']));
		message('保存成功！', $urlt, 'success');
	}else{
		message('保存失败！', $urlt, 'error');
	}
	
}

//读取
if($op == 'edit'){
	$id = intval($_GPC['id']);
	$srdb = pdo_get('onljob_knowledge_message', array('id' => $id,'weid' => $_W['uniacid']));
	if (empty($srdb)) {
		message('不存在或是已经被删除！', $urlt, 'error');
	}
	
	$parentid = $srdb['parentid'];
}

if($parentid > 0){
	//知识点名称
	$srdb_knowle = pdo_get('onljob_knowledge', array('id' => $parentid,'weid' => $_W['uniacid']));
	$_GPC['titlename'] = $srdb_knowle['titlename'];
}

//删除---------------
if($op == 'delete'){
	$id = intval($_GPC['id']);
	if($id){
		pdo_delete('onljob_knowledge_message', array('id' => $id,'weid' => $_W['uniacid']));
		message('删除成功', $urlt, 'success');
	}else{
		message('删除失败', $urlt, 'error');
	}
}


//批量删除
if (checksubmit('deletesubmit')) {
	if($_GPC['ids'] && is_array($_GPC['ids'])) {
		$ids = $_GPC['ids'];
		for($i=0; $i < count($ids); $i++){
			pdo_delete('onljob_knowledge_message', array('id' => $ids[$i],'weid' => $_W['uniacid']));
		}
		message('批量删除成功', $urlt, 'success');
	}else{
		message('批量删除失败', $urlt, 'error');
	}
}

//列表-------------------------
//排序
$ordersc = array($_GPC['ordersc']=>' selected');
if($_GPC['ordersc']){
	$ordersql = "ORDER BY a.id ".$_GPC['ordersc'];
}else{
	$ordersql = "ORDER BY a.id DESC";
}

$pindex = max(1, intval($_GPC['page']));
$psize = empty($_GPC['psize'])?0:intval($_GPC['psize']);
if(!in_array($psize, array(20,50,100))) $psize = 20;
$perpages = array($psize => ' selected');

$where = '';

if (!empty($_GPC['titlename'])) {
	$where .= " AND b.titlename LIKE '%{$_GPC['titlename']}%'";
	$urlt .= "&titlename=".$_GPC['titlename'];
}

if (!empty($_GPC['parentid'])) {
	$where .= " AND a.parentid = '{$_GPC['parentid']}'";
	$urlt .= "&parentid=".$_GPC['parentid'];
}

if ($_GPC['statusd'] == '1') {
	$where .= " AND a.status = '0'";
	$urlt .= "&statusd=1";
}elseif ($_GPC['statusd'] == '2') {
	$where .= " AND a.status = '1'";
	$urlt .= "&statusd=2";
}



$total = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('onljob_knowledge_message')." AS a LEFT JOIN ".tablename('onljob_knowledge')." AS b ON a.parentid = b.id  WHERE a.weid = '{$_W['uniacid']}' {$where}");
if($total){
	$list = pdo_fetchall("SELECT a.*,b.titlename FROM ".tablename('onljob_knowledge_message')." AS a LEFT JOIN ".tablename('onljob_knowledge')." AS b ON a.parentid = b.id WHERE a.weid = '{$_W['uniacid']}' {$where} {$ordersql} LIMIT " . ($pindex - 1) * $psize . ',' . $psize);
}
$pager = pagination($total, $pindex, $psize);

include $this->template('knowledge_bc');
?>