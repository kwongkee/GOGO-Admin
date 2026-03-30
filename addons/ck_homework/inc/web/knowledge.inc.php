<?php

defined('IN_IA') or exit('Access Denied');

global $_W, $_GPC;
load()->func('tpl');
$op = $_GPC['op'];

$urlt = $this->createWebUrl('knowledge');

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
	
	if (empty ($_GPC['titlename'])) {
		message('名称不能为空！', '', 'error');
	}
	if (empty ($_GPC['craid1']) && empty ($_GPC['craid2']) && empty ($_GPC['craid3']) && empty ($_GPC['craid4']) && empty ($_GPC['craid5'])) {
		message('请选择分类！', '', 'error');
	}
	if (empty ($_GPC['imgurl'])) {
		message('封面不能为空！', '', 'error');
	}
	
	$data = array(
		'titlename' => $_GPC['titlename'],
		'craid1' => intval($_GPC['craid1']),
		'craid2' => intval($_GPC['craid2']),
		'craid3' => intval($_GPC['craid3']),
		'craid4' => intval($_GPC['craid4']),
		'craid5' => intval($_GPC['craid5']),
		'imgurl' => $_GPC['imgurl'],
		'paymoney' => $_GPC['paymoney'],
		'vipview' => json_encode($_GPC['vipview']),
		'listorder' => intval($_GPC['listorder']),
		'state' => intval($_GPC['stated']),
		'tj' => intval($_GPC['tjp'])
	);

	//添加
	if(checksubmit('add_submit')){
		 $data['weid'] = $_W['uniacid'];
		 $data['dateline'] = $newtimes;
		 pdo_insert('onljob_knowledge', $data);
		 message('添加成功', $urlt, 'success');
	}
	
	//修改
	if(!empty($_GPC['id'])){
		pdo_update('onljob_knowledge', $data, array('id' => $_GPC['id'],'weid' => $_W['uniacid']));
		message('修改成功！', $urlt, 'success');
	}else{
		message('修改失败！', $urlt, 'error');
	}
	
}

//读取
if($op == 'edit'){
	$id = intval($_GPC['id']);
	$srdb = pdo_get('onljob_knowledge', array('id' => $id,'weid' => $_W['uniacid']));
	if (empty($srdb)) {
		message('不存在或是已经被删除！', $urlt, 'error');
	}
	$vipview  = json_decode($srdb['vipview'], true);  /* 免费学习的VIP等级 */
}

//删除---------------
if($op == 'delete'){
	$id = intval($_GPC['id']);
	if($id){
		pdo_delete('onljob_knowledge', array('id' => $id,'weid' => $_W['uniacid']));
		message('删除成功', $urlt, 'success');
	}else{
		message('删除失败', $urlt, 'error');
	}
}


//批量修改
if (checksubmit('submit')) { /* 排序 */
	if (is_array($_GPC['listorder'])) {
		foreach ($_GPC['listorder'] as $pid => $val) {
			$data = array('listorder' => intval($_GPC['listorder'][$pid]));
			pdo_update('onljob_knowledge', $data, array('id' => $pid,'weid' => $_W['uniacid']));
		}
	}
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
	$ordersql = "ORDER BY listorder ASC,id ".$_GPC['ordersc'];
}else{
	$ordersql = "ORDER BY listorder ASC,id DESC";
}

$pindex = max(1, intval($_GPC['page']));
$psize = empty($_GPC['psize'])?0:intval($_GPC['psize']);
if(!in_array($psize, array(20,50,100))) $psize = 20;
$perpages = array($psize => ' selected');

$where = '';

if (!empty($_GPC['titlename'])) {
	$where .= " AND titlename LIKE '%{$_GPC['titlename']}%'";
}

if (!empty($_GPC['craid1'])) {
	$where .= " AND craid1 = '{$_GPC['craid1']}'";
}
if (!empty($_GPC['craid2'])) {
	$where .= " AND craid2 = '{$_GPC['craid2']}'";
}
if (!empty($_GPC['craid3'])) {
	$where .= " AND craid3 = '{$_GPC['craid3']}'";
}
if (!empty($_GPC['craid4'])) {
	$where .= " AND craid4 = '{$_GPC['craid4']}'";
}
if (!empty($_GPC['craid5'])) {
	$where .= " AND craid5 = '{$_GPC['craid5']}'";
}

$total = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('onljob_knowledge')."  WHERE weid = '{$_W['uniacid']}' {$where}");
if($total){
	$list = pdo_fetchall("SELECT * FROM ".tablename('onljob_knowledge')." WHERE weid = '{$_W['uniacid']}' {$where} {$ordersql} LIMIT " . ($pindex - 1) * $psize . ',' . $psize);
	foreach($list as $key=>$value){
		$list[$key]['section'] = pdo_fetchall("SELECT id,parentid,title,displayorder FROM ".tablename('onljob_knowledge_son')." WHERE parentid=:parentid and weid=:weid ORDER BY displayorder DESC", array(':parentid'=>$value['id'],':weid'=>$_W['uniacid']));
	}
}
$pager = pagination($total, $pindex, $psize);

include $this->template('knowledge');
?>