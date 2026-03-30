<?php

defined('IN_IA') or exit('Access Denied');

global $_W, $_GPC;
load()->func('tpl');
$op = $_GPC['op'];

$urlt = $this->createWebUrl('theclass_list');
$urlt1 = $this->createWebUrl('school_list');
$newtimes = time();

//科目
$category = pdo_fetchall("SELECT * FROM " . tablename('onljob_class') . " WHERE weid = '{$_W['uniacid']}' and pid = '0' and type = 'xk' ORDER BY listorder ASC, cid DESC", array(), 'cid');

//年级
$category_nj = pdo_fetchall("SELECT * FROM " . tablename('onljob_class') . " WHERE weid = '{$_W['uniacid']}' and pid = '0' and type = 'nj' ORDER BY listorder ASC, cid DESC", array(), 'cid');

//删除---------------
if($op == 'delete'){
	$id = intval($_GPC['id']);
	if($id){
		load()->func('file');
		$srdb = pdo_get('onljob_theclass', array('id' => $id,'weid' => $_W['uniacid']));
		if($srdb){
			pdo_delete('onljob_theclass', array('id' => $id,'weid' => $_W['uniacid']));
			@rmdirs(IA_ROOT . "/addons/".$_GPC['m']."/data/bjqrcode/" . $_W['uniacid'] . "/bjqrcode_".$srdb['uid']."_".$id.".png");
		}
		message('删除成功', $urlt, 'success');
	}else{
		message('删除失败', $urlt, 'error');
	}
}

//批量删除
if (checksubmit('deletesubmit')) {
	if($_GPC['ids'] && is_array($_GPC['ids'])) {
		load()->func('file');
		$ids = $_GPC['ids'];
		for($i=0; $i < count($ids); $i++){
			$srdb = pdo_get('onljob_theclass', array('id' => $ids[$i],'weid' => $_W['uniacid']));
			if($srdb){
				pdo_delete('onljob_theclass', array('id' => $ids[$i],'weid' => $_W['uniacid']));
				@rmdirs(IA_ROOT . "/addons/".$_GPC['m']."/data/bjqrcode/" . $_W['uniacid'] . "/bjqrcode_".$srdb['uid']."_".$ids[$i].".png");
			}
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

if (!empty($_GPC['name'])) {
	$where .= " AND b.name LIKE '%{$_GPC['name']}%'";
}

if (!empty($_GPC['phone'])) {
	$where .= " AND b.phone LIKE '%{$_GPC['phone']}%'";
}

if (!empty($_GPC['numberid'])) {
	$where .= " AND a.numberid LIKE '%{$_GPC['numberid']}%'";
}

if($_GPC['statep']==1){
	$where .= " AND a.state = '0'";
}elseif($_GPC['statep']==2){
	$where .= " AND a.state = '1'";
}

$total = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('onljob_theclass')." AS a LEFT JOIN ".tablename('onljob_user')." AS b ON a.uid = b.uid WHERE a.weid = '{$_W['uniacid']}' {$where}");
if($total){
	$list = pdo_fetchall("SELECT a.*,b.name FROM ".tablename('onljob_theclass')." AS a LEFT JOIN ".tablename('onljob_user')." AS b ON a.uid = b.uid WHERE a.weid = '{$_W['uniacid']}' {$where} {$ordersql} LIMIT " . ($pindex - 1) * $psize . ',' . $psize);
}

$pager = pagination($total, $pindex, $psize);

include $this->template('theclass_list');
?>