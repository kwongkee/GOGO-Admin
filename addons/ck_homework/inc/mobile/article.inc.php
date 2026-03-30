<?php
if (!defined('IN_IA')) {
    exit('Access Denied');
}

global $_W, $_GPC;

require "common.php";

$op = isset($_GPC['op']) ? htmlspecialchars(trim($_GPC['op'])) : 'lsit';
$urlt = $this->createMobileUrl('article');
$urlt .= '&op='.$op;

//学科分类
$category_xk = pdo_fetchall("SELECT * FROM " . tablename('onljob_class') . " WHERE weid = '{$_W['uniacid']}' and pid = '0' and type = 'xk' ORDER BY listorder ASC, cid DESC", array(), 'cid');

//年级分类
$category_nj = pdo_fetchall("SELECT * FROM " . tablename('onljob_class') . " WHERE weid = '{$_W['uniacid']}' and pid = '0' and type = 'nj' ORDER BY listorder ASC, cid DESC", array(), 'cid');

if($op == 'show'){
	
	//显示
	$id = empty($_GPC['id'])?0:intval($_GPC['id']);
	$srdb = pdo_get('onljob_news', array('id' => $id,'weid' => $_W['uniacid']));
	if (empty($srdb)) {
		message_app('不存在或是已经被删除！', '', 'error');
	}
	
	$wurltitle = '';
	$returnurl = $this->createMobileUrl('article');	
	if (!empty($srdb['njid'])) {
		$wurltitle .= $category_nj[$srdb['njid']]['name'];
		$returnurl .= "&njid=" . $srdb['njid'];
	}
	if (!empty($srdb['xkid'])) {
		$wurltitle .= $category_xk[$srdb['xkid']]['name'];
		$returnurl .= "&xkid=" . $srdb['xkid'];
	}
	if (!empty($srdb['typeid'])) {
		$wurltitle .= $typeidall[$srdb['typeid']];
		$returnurl .= "&typeid=" . $srdb['typeid'];
	}
	
	//内容
	$message = html_entity_decode($srdb['message']);
	
	include template_app('article_show');
	
}else{

	//列表-------------------------
	$njid = empty($_GPC['njid'])?0:intval($_GPC['njid']);
	$xkid = empty($_GPC['xkid'])?0:intval($_GPC['xkid']);
	$typeid = empty($_GPC['typeid'])?0:intval($_GPC['typeid']);
	$keyword = trim($_GPC['keyword']);
	$pindex = max(1, intval($_GPC['page']));
	$psize = empty($_GPC['psize'])?0:intval($_GPC['psize']);
	if(!in_array($psize, array(20,50,100))) $psize = 20;
	
	$where = '';
	if (!empty($keyword)) {
		$where .= " AND titlename LIKE '%{$keyword}%'";
		$urlt .= '&keyword=' . $keyword;
	}
	$wurltitle = '';	
	if (!empty($njid)) {
		$where .= " AND njid = '{$njid}'";
		$wurltitle .= $category_nj[$njid]['name'];
	}
	if (!empty($xkid)) {
		$where .= " AND xkid = '{$xkid}'";
		$urlt .= '&xkid=' . $xkid;
		$wurltitle .= $category_xk[$xkid]['name'];
	}
	if (!empty($typeid)) {
		$where .= " AND typeid = '{$typeid}'";
		$wurltitle .= $typeidall[$typeid];
	}
	
	$total = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('onljob_news')." WHERE weid = '{$_W['uniacid']}' and type = 'news' {$where}");
	if($total){
		$list = pdo_fetchall("SELECT * FROM ".tablename('onljob_news')." WHERE weid = '{$_W['uniacid']}' and type = 'news' {$where} ORDER BY listorder ASC,id DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize);
	}
		
	$pager = pagination($total, $pindex, $psize,'', array('before' => 0, 'after' => 0));
		
	
	include template_app('article_lsit');
	
}