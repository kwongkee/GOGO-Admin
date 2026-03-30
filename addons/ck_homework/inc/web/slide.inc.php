<?php

defined('IN_IA') or exit('Access Denied');

global $_W, $_GPC;

load()->func('tpl');

$urlt = $this->createWebUrl('slide');
		
//幻灯片管理
$op = $_GPC['op'];

//操作
if(checksubmit('add_submit') || checksubmit('edit_submit')){

	if (empty($_GPC['titlename'])) {
		message('请输入名称！', '', 'error');
	}
	if (empty($_GPC['imgurl'])) {
		message('图片不能为空！', '', 'error');
	}
	$data = array(
		'titlename' => trim($_GPC['titlename']),
		'urlt' => $_GPC['urlt'],
		'sort' => intval($_GPC['sort']),
		'imgurl' => $_GPC['imgurl'],
		'status' => intval($_GPC['statusd'])
	);
	
	//添加
	if(checksubmit('add_submit')){
		 $data['weid'] = $_W['uniacid'];
		 $data['dateline'] = time();
		 pdo_insert('onljob_slide', $data);
		 message('添加成功', $urlt, 'success');
	}
	
	//修改
	if(!empty($_GPC['idd'])){
		pdo_update('onljob_slide', $data, array('id' => $_GPC['idd'],'weid' => $_W['uniacid']));
		message('修改成功！', $urlt, 'success');
	}else{
		message('修改失败！', $urlt, 'error');
	}
	
}

//读取
if($op == 'edit'){
	$id = intval($_GPC['id']);
	$srdb = pdo_get('onljob_slide', array('id' => $id,'weid' => $_W['uniacid']));
	if (empty($srdb)) {
		message('不存在或是已经被删除！', '', 'error');
	}
}

//删除
if($op == 'delete'){
	$id = intval($_GPC['id']);
	if($id){
		pdo_delete('onljob_slide', array('id' => $id,'weid' => $_W['uniacid']));
		message('删除成功', $urlt, 'success');
	}else{
		message('删除失败', $urlt, 'error');
	}
}

//批量删除
if (checksubmit('listsubmit')) {
	if($_GPC['ids'] && is_array($_GPC['ids'])) {
		$ids = $_GPC['ids'];
		$listorder = $_GPC['listorder'];
		for($i=0;$i < count($ids); $i++){
			pdo_delete('onljob_slide', array('id' => $ids[$i],'weid' => $_W['uniacid']));
		}
		message('批量删除成功', $urlt, 'success');
	}else{
		message('批量删除失败', $urlt, 'error');
	}
}

//列表--------------------
$pindex = max(1, intval($_GPC['page']));
$psize = 12;

$list = pdo_fetchall("SELECT * FROM " . tablename('onljob_slide') . " WHERE weid = '{$_W['uniacid']}' ORDER BY sort ASC,id DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize);
$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('onljob_slide') . " WHERE weid = '{$_W['uniacid']}' ");
$pager = pagination($total, $pindex, $psize);

include $this->template('slide');
?>