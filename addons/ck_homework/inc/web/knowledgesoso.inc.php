<?php

defined('IN_IA') or exit('Access Denied');

global $_W, $_GPC;
load()->func('tpl');
$op = $_GPC['op'];

$urlt = $this->createWebUrl('knowledgesoso');

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

$total = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('onljob_knowledge')."  WHERE weid = '{$_W['uniacid']}' and state = '1' {$where}");
if($total){
	$list = pdo_fetchall("SELECT * FROM ".tablename('onljob_knowledge')." WHERE weid = '{$_W['uniacid']}' and state = '1' {$where} {$ordersql} LIMIT " . ($pindex - 1) * $psize . ',' . $psize);
}
$pager = pagination($total, $pindex, $psize);

include $this->template('knowledgesoso');
?>