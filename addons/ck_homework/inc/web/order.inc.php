<?php

defined('IN_IA') or exit('Access Denied');

global $_W, $_GPC;

load()->func('tpl');
$op = $_GPC['op'];
$tab = trim($_GPC['tab']);
$typeall = array('zsd' => '知识点','topup'=>'充值余额','vip'=>'续费/购买VIP','class'=>'购买加入班级');
$urlt = $this->createWebUrl('order');

//读取
if($op == 'edit'){
	
	$id = intval($_GPC['id']);
	$srdb = pdo_get('onljob_pay_order', array('id' => $id,'weid' => $_W['uniacid']));
	if (empty($srdb)) {
		message('不存在或是已经被删除！', $urlt, 'error');
	}
	
}

//删除---------------
if($op == 'delete'){
	$id = intval($_GPC['id']);
	if($id){
		pdo_delete('onljob_pay_order', array('id' => $id,'weid' => $_W['uniacid']));
		message('删除成功', $urlt, 'success');
	}else{
		message('删除失败', $urlt, 'error');
	}
}

//批量删除--------------------
if (checksubmit('deletesubmit')) {
	if($_GPC['ids'] && is_array($_GPC['ids'])) {
		
		$ids = $_GPC['ids'];
		for($i=0;$i < count($ids); $i++){
			pdo_delete('onljob_pay_order', array('id' => $ids[$i],'weid' => $_W['uniacid']));
		}

		message('批量删除成功', $urlt, 'success');
	}else{
		message('批量删除失败', $urlt, 'error');
	}
}//--------------------------

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

if (!empty($_GPC['orderid'])) {
	$where .= " AND a.orderid = '{$_GPC['orderid']}'";
}
if (!empty($_GPC['uid'])) {
	$where .= " AND a.uid = ".intval($_GPC['uid']);
}
if (!empty($tab)) {
	$where .= " AND a.type = '{$tab}'";
}
if (!empty($_GPC['statusd'])) {
	$statusd = intval($_GPC['statusd']) - 1;
	$where .= " AND a.status = '{$statusd}'";
}

$total = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('onljob_pay_order')." AS a LEFT JOIN ".tablename('onljob_user')." AS b on a.uid = b.uid WHERE a.weid = '{$_W['uniacid']}' {$where}");
if($total){
	$list = pdo_fetchall("SELECT a.*,b.name FROM ".tablename('onljob_pay_order')." AS a LEFT JOIN ".tablename('onljob_user')." AS b on a.uid = b.uid WHERE a.weid = '{$_W['uniacid']}' {$where} {$ordersql} LIMIT " . ($pindex - 1) * $psize . ',' . $psize);
}
$pager = pagination($total, $pindex, $psize);

include $this->template('order');
?>