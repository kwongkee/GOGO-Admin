<?php
defined('IN_IA') or exit('Access Denied');

global $_W, $_GPC;

load()->func('tpl');
$op = $_GPC['op'];

$url = $this->createWebUrl('article');
$urlt = $this->createWebUrl('article');

$newtimes = mktime();

$typeidall = array('1' => '范文','2' => '模板','3' => '素材','4' => '技巧');
$typeall = array('help' => '帮助管理','news' => '文章管理','zwsx' => '作文赏析管理');
$type = $_GPC['type'] ? $_GPC['type'] : 'help';
$urlt .= '&type=' . $type;

//分类
$category = pdo_fetchall("SELECT * FROM " . tablename('onljob_class') . " WHERE weid = '{$_W['uniacid']}' and type = '{$type}' ORDER BY listorder ASC, cid DESC", array(), 'cid');
if (!empty($category)) {
	$children = '';
	foreach ($category as $cid => $cate) {
		if (!empty($cate['pid'])) {
			$children[$cate['pid']][$cate['cid']] = array($cate['cid'], $cate['name']);
		}
	}
}

//学科分类
$category_xk = pdo_fetchall("SELECT * FROM " . tablename('onljob_class') . " WHERE weid = '{$_W['uniacid']}' and pid = '0' and type = 'xk' ORDER BY listorder ASC, cid DESC", array(), 'cid');

//年级分类
$category_nj = pdo_fetchall("SELECT * FROM " . tablename('onljob_class') . " WHERE weid = '{$_W['uniacid']}' and pid = '0' and type = 'nj' ORDER BY listorder ASC, cid DESC", array(), 'cid');

//操作
if(checksubmit('add_submit') || checksubmit('edit_submit')){

	if (empty($_GPC['titlename'])) {
		message('请输入标题！', '', 'error');
	}

	$data = array(
		'weid' => $_W['uniacid'],
		'titlename' => $_GPC['titlename'],
		'messagejj' => $_GPC['messagejj'],
		'message' => $_GPC['message'],
		'listorder' => $_GPC['listorder'],
		'type' => trim($_GPC['type'])
	);
	
	//帮助
	if($data['type'] == 'help'){
		if (empty($_GPC['craid'])) {
			message('请选择分类！', '', 'error');
		}
		$data['craid'] = intval($_GPC['craid']);
	}
	
	//文章
	if($data['type'] == 'news'){
		if (empty($_GPC['typeid'])) {
			message('请选择类型！', '', 'error');
		}
		if (empty($_GPC['xkid'])) {
			message('请选择学科！', '', 'error');
		}
		if (empty($_GPC['njid'])) {
			message('请选择年级！', '', 'error');
		}
		$data['typeid'] = intval($_GPC['typeid']);
		$data['xkid'] = intval($_GPC['xkid']);
		$data['njid'] = intval($_GPC['njid']);
	}
	
	//作文
	if($data['type'] == 'zwsx'){
		if (empty($_GPC['craid'])) {
			message('请选择类型！', '', 'error');
		}
		$data['craid'] = intval($_GPC['craid']);
	}
	
	//添加
	if(checksubmit('add_submit')){
		 $data['dateline'] = mktime();
		 pdo_insert('onljob_news', $data);
		 message('添加成功', $urlt, 'success');
	}
	
	//修改
	if(!empty($_GPC['idd'])){
		pdo_update('onljob_news', $data, array('id' => $_GPC['idd'],'weid' => $_W['uniacid']));
		message('修改成功！', $urlt, 'success');
	}else{
		message('修改失败！', $urlt, 'error');
	}
	
}

//读取
if($op == 'edit'){
	$id = intval($_GPC['id']);
	$srdb = pdo_get('onljob_news', array('id' => $id,'weid' => $_W['uniacid']));
	if (empty($srdb)) {
		message('不存在或是已经被删除！', '', 'error');
	}
	
}

//删除
if($op == 'delete'){
	$id = intval($_GPC['id']);
	if($id){
		pdo_delete('onljob_news', array('id' => $id,'weid' => $_W['uniacid']));
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
			pdo_delete('onljob_news', array('id' => $ids[$i],'weid' => $_W['uniacid']));
		}
				
		message('批量删除成功', $urlt, 'success');
		
	}else{
		message('批量删除失败', $urlt, 'error');
	}
}

//列表--------------------
$orderby = array($_GPC['orderby']=>' selected');
$ordersc = array($_GPC['ordersc']=>' selected');
$ordersql = " ORDER BY";
if(empty($_GPC['orderby'])) $_GPC['orderby'] = 'id';
if(empty($_GPC['ordersc'])) $_GPC['ordersc'] = 'desc';
$ordersql .= " " .$_GPC['orderby']. " " . $_GPC['ordersc'];


$pindex = max(1, intval($_GPC['page']));
$psize = empty($_GPC['psize'])?0:intval($_GPC['psize']);
if(!in_array($psize, array(20,50,100))) $psize = 20;
$perpages = array($psize => ' selected');

$where = '';
if (!empty($_GPC['titlename'])) {
	$where .= " AND titlename LIKE '%{$_GPC['titlename']}%'";
}
if (!empty($_GPC['craid'])) {
	$where .= " AND craid = '{$_GPC['craid']}'";
}

//----------------------------
$wurlt = $_W['siteroot']. 'app/' . $this->createMobileUrl('article');
$wurltitle = '';
if (!empty($_GPC['typeid'])) {
	$where .= " AND typeid = '{$_GPC['typeid']}'";
	$wurlt .= '&typeid=' . $_GPC['typeid'];
	$wurlshow = 1;
	$wurltitle .= $typeidall[$_GPC['typeid']];
}
if (!empty($_GPC['xkid'])) {
	$where .= " AND xkid = '{$_GPC['xkid']}'";
	$wurlt .= '&xkid=' . $_GPC['xkid'];
	$wurlshow = 1;
	$wurltitle .= $category_xk[$_GPC['xkid']]['name'];
}
if (!empty($_GPC['njid'])) {
	$where .= " AND njid = '{$_GPC['njid']}'";
	$wurlt .= '&njid=' . $_GPC['njid'];
	$wurlshow = 1;
	$wurltitle .= $category_nj[$_GPC['njid']]['name'];
}
//----------------------------

$list = pdo_fetchall("SELECT * FROM " . tablename('onljob_news') . " WHERE weid = '{$_W['uniacid']}' and type = '{$type}' {$where} {$ordersql} LIMIT " . ($pindex - 1) * $psize . ',' . $psize);
$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('onljob_news') . " WHERE weid = '{$_W['uniacid']}' and type = '{$type}' {$where}");
$pager = pagination($total, $pindex, $psize);

include $this->template('article_'.$type);
?>