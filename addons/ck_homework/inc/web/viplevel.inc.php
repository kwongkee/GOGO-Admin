<?php

defined('IN_IA') or exit('Access Denied');

global $_W, $_GPC;
load()->func('tpl');
$op = $_GPC['op'];

$urlt = $this->createWebUrl('viplevel');

$newtimes = time();

//获取设置信息
$config = pdo_get('onljob_config', array('weid' => $_W['uniacid']));

//保存服务协议
if(checksubmit('add_config') || checksubmit('edit_config')){
	
	$data = array('vipfwxieyi' => $_GPC['vipfwxieyi']);
	
	//添加
	if(checksubmit('add_config')){	
		 $data['weid'] = $_W['uniacid'];
		 $result = pdo_insert('onljob_config', $data, true); 
	}
	
	//修改
	if(checksubmit('edit_config')){
		pdo_update('onljob_config', $data, array('weid' => $_W['uniacid']));
	}
	
	message('保存成功！', $urlt, 'success');
		
}

//修改
if(checksubmit('add_submit') || checksubmit('edit_submit')){
	
	$data = array(
		'level_name'      => trim($_GPC['level_name']),
		'level_validity'  => intval($_GPC['level_validity']),
		'level_price'     => floatval($_GPC['level_price']),
		'discount'        => intval($_GPC['discount']),
		'sort'            => intval($_GPC['sort']),
		'is_show'		  => intval($_GPC['is_show']),
		'addtime'		  => time(),
	);

	if(empty($data['level_name'])){
		message("VIP等级名称不能为空", "", "error");
	}
	if(empty($data['level_validity'])){
		message("VIP等级有效期不能为空", "", "error");
	}
	if(empty($data['level_price'])){
		message("VIP等级价格不能为空", "", "error");
	}
	
	//添加
	if(checksubmit('add_submit')){
		 $data['weid'] = $_W['uniacid'];
		 pdo_insert('onljob_vip_level', $data);
		 message('添加成功', $urlt, 'success');
	}
	
	//修改
	if(checksubmit('edit_submit')){
		pdo_update('onljob_vip_level', $data, array('id' => $_GPC['id'],'weid' => $_W['uniacid']));
		message('修改成功', $urlt, 'success');
	}
	
}

//读取
if($op == 'edit'){
	
	$id = intval($_GPC['id']);
	$srdb = pdo_get('onljob_vip_level', array('id' => $id,'weid' => $_W['uniacid']));
	if (empty($srdb)) {
		message('不存在或是已经被删除！', $urlt, 'error');
	}

}

//删除---------------
if($op == 'delete'){
	$id = intval($_GPC['id']);
	if($id){
		pdo_delete('onljob_vip_level', array('id' => $id,'weid' => $_W['uniacid']));
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
			pdo_delete('onljob_vip_level', array('id' => $ids[$i],'weid' => $_W['uniacid']));
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
	$ordersql = "ORDER BY id ".$_GPC['ordersc'];
}else{
	$ordersql = "ORDER BY id DESC";
}

$pindex = max(1, intval($_GPC['page']));
$psize = empty($_GPC['psize'])?0:intval($_GPC['psize']);
if(!in_array($psize, array(20,50,100))) $psize = 20;
$perpages = array($psize => ' selected');

$where = '';

if($_GPC['statep']==1){
	$where .= " AND is_show = '1'";
}elseif($_GPC['statep']==2){
	$where .= " AND is_show = '0'";
}

$total = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('onljob_vip_level')."  WHERE weid = '{$_W['uniacid']}' {$where}");
if($total){
	$list = pdo_fetchall("SELECT * FROM ".tablename('onljob_vip_level')." WHERE weid = '{$_W['uniacid']}' {$where} {$ordersql} LIMIT " . ($pindex - 1) * $psize . ',' . $psize);
}
$pager = pagination($total, $pindex, $psize);

include $this->template('viplevel');
?>