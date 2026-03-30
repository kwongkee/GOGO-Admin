<?php

defined('IN_IA') or exit('Access Denied');

global $_W, $_GPC;

$typeall = array('xk' => '学科','nj' => '年级','zsd' => '知识点','help' => '帮助');
$op = $_GPC['op'];
$tab = trim($_GPC['tab']);
$nextid = empty($_GPC['nextid'])?0:intval($_GPC['nextid']);
$nextall = $nextid + 1;
$classtype = array('1' => '一','2' => '二','3' => '三','4' => '四');


$urltad = $urlt = $this->createWebUrl('class');
if (!empty($tab)) {
	$urltad .= '&tab='.$tab;
}
if (!empty($nextid)) {
	$urltad .= '&nextid='.$nextid;
}
$pid = empty($_GPC['pid'])?0:intval($_GPC['pid']);
if (!empty($pid)) {
	$urltad .= '&pid='.$pid;
}
//操作
if(checksubmit('add_submit') || checksubmit('edit_submit') || checksubmit('addnext_submit')){

	if (empty($_GPC['name'])) {
		message('请输入名称！');
	}
	if (empty($_GPC['type'])) {
		message('请选择所属类型！');
	}
	$data = array(
		'pid' => intval($_GPC['pid']),
		'name' => $_GPC['name'],
		'type' => trim($_GPC['type']),
		'iconimg' => trim($_GPC['iconimg']),
		'listorder' => intval($_GPC['listorder'])
	);
	
	$urlt .= '&tab='.$data['type'];
	
	//添加
	if(checksubmit('add_submit') || checksubmit('addnext_submit')){
		 $data['weid'] = $_W['uniacid'];
		 pdo_insert('onljob_class', $data);
		 message('添加成功', $urltad, 'success');
	}
	
	//修改
	if(checksubmit('edit_submit')){
		pdo_update('onljob_class', $data, array('cid' => $_GPC['cid'],'weid' => $_W['uniacid']));
		
		//修改子类
		pdo_update('onljob_class', array('type' => trim($_GPC['type'])), array('pid' => $_GPC['cid'],'weid' => $_W['uniacid']));
		message('修改成功', $urltad, 'success');
	}
	
}

//读取
if($op == 'edit'){
	$id = intval($_GPC['id']);
	$srdb = pdo_get('onljob_class', array('cid' => $id,'weid' => $_W['uniacid']));
	if (empty($srdb)) {
		message('不存在或是已经被删除！');
	}
}

//上级分类名称
if (!empty($pid)) {
	$onclassname = pdo_get('onljob_class', array('cid' => $pid,'weid' => $_W['uniacid']));
}


//添加子类
//if($op == 'addnext'){
//	$pid = intval($_GPC['pid']);
//	$srdbbig = pdo_get('onljob_class', array('cid' => $pid,'weid' => $_W['uniacid']));
//	if (empty($srdbbig)) {
//		message('不存在或是已经被删除！');
//	}
//
//}

//删除
if($op == 'delete'){
	$id = intval($_GPC['id']);
	if($id){
		pdo_delete('onljob_class', array('cid' => $id,'weid' => $_W['uniacid']));
		pdo_delete('onljob_class', array('pid' => $id,'weid' => $_W['uniacid']));
		message('删除成功', $urltad, 'success');
	}else{
		message('删除失败', $urltad, 'error');
	}
}

//删除子类
if($op == 'deletex'){
	$id = intval($_GPC['id']);
	if($id){
		pdo_delete('onljob_class', array('cid' => $id));
		message('删除成功', $urltad, 'success');
	}else{
		message('删除失败', $urltad, 'error');
	}
}

//批量修改
if (checksubmit('listsubmit')) {
	if($_GPC['ids'] && is_array($_GPC['ids']) && $_GPC['optype']) {
		switch ($_GPC['optype']) {
			case '1':
				$ids = $_GPC['ids'];
				$listorder = $_GPC['listorder'];
				for($i=0;$i < count($ids); $i++){
					pdo_query("UPDATE ".tablename('onljob_class')." SET listorder = '{$listorder[$ids[$i]]}' WHERE cid = '{$ids[$i]}' and weid = '{$_W['uniacid']}'");
				}
				
				break;
		}
		message('批量修改成功', $urltad, 'success');
	}else{
		message('批量修改失败', $urltad, 'error');
	}
}

//列表--------------------
$pindex = max(1, intval($_GPC['page']));
$psize = 12;

$wheresql = '';

if (!empty($tab)) {
	$wheresql .= " and type = '{$tab}'";
}
$wheresql .= " and pid = '{$pid}'";

$list = pdo_fetchall("SELECT * FROM " . tablename('onljob_class') . " WHERE weid = '{$_W['uniacid']}' $wheresql ORDER BY listorder ASC, cid DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize);
if (!empty($list)) {
	$list_class_x = '';
	foreach ($list as $cid => $cate) {
		$nextnum[$cate['cid']] = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('onljob_class') . " WHERE weid = '{$_W['uniacid']}' and pid = '{$cate[cid]}'");
		if (!empty($cate['pid'])) {
			$onnameall = pdo_fetch("SELECT name FROM ".tablename('onljob_class')." WHERE weid = :weid and cid = :cid LIMIT 1", array(':weid' => $_W['uniacid'],':cid' => $cate['pid']));
			$onname[$cate['cid']] = $onnameall['name'];
		}
	}
}
$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('onljob_class') . " WHERE weid = '{$_W['uniacid']}' $wheresql");
$pager = pagination($total, $pindex, $psize);

//全部
$wherelg = '';
if (!empty($tab)) {
	$wherelg .= " and type = '{$tab}'";
}
if (empty($pid)) {
	$wherelg .= " and pid = '0'";
}
$list_class = pdo_fetchall("SELECT * FROM " . tablename('onljob_class') . " WHERE weid = '{$_W['uniacid']}' $wherelg ORDER BY listorder ASC, cid DESC");

include $this->template('class');
?>