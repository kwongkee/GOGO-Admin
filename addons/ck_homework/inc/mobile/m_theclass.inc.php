<?php

if (!defined('IN_IA')) {
    exit('Access Denied');
}

global $_W, $_GPC;
require "common.php";
require "public.php";
require "m_common.php";

$xkid = empty($_GPC['xkid'])?0:intval($_GPC['xkid']);
$op = trim($_GPC['op']);

$urltk = $this->createMobileUrl('m_theclass');

//科目
$category = pdo_fetchall("SELECT * FROM " . tablename('onljob_class') . " WHERE weid = '{$_W['uniacid']}' and pid = '0' and type = 'xk' ORDER BY listorder ASC, cid DESC", array(), 'cid');

//年级
$category_nj = pdo_fetchall("SELECT * FROM " . tablename('onljob_class') . " WHERE weid = '{$_W['uniacid']}' and pid = '0' and type = 'nj' ORDER BY listorder ASC, cid DESC", array(), 'cid');

//列表------------------
$pindex = max(1, intval($_GPC['page']));
$psize = empty($_GPC['psize'])?0:intval($_GPC['psize']);
if(!in_array($psize, array(20,50,100))) $psize = 20;

$where = '';
if(!empty($xkid)){
	$where = " and c.xkid = '{$xkid}' ";
}

$total = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('onljob_theclass_apply')." AS a LEFT JOIN ".tablename('onljob_theclass')." AS c ON a.bjid = c.id LEFT JOIN ".tablename('onljob_user')." AS b ON c.uid = b.uid WHERE a.weid = '{$_W['uniacid']}' and a.uid = '{$_W['member']['uid']}' and a.state = '1' {$where}");
if($total){
	$list = pdo_fetchall("SELECT a.id as sqid,c.*,c.uid as lsuid,b.name FROM ".tablename('onljob_theclass_apply')." AS a LEFT JOIN ".tablename('onljob_theclass')." AS c ON a.bjid = c.id LEFT JOIN ".tablename('onljob_user')." AS b ON c.uid = b.uid WHERE a.weid = '{$_W['uniacid']}' and a.uid = '{$_W['member']['uid']}' and a.state = '1' {$where} ORDER BY a.id DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize);
	if (!empty($list)) {
		$xstotal = '';
		foreach ($list as $cid => $cate) {
			//学生数量
			$xstotal[$cate['id']] = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('onljob_theclass_apply') . " WHERE weid = '{$_W['uniacid']}' and bjid = '{$cate['id']}' ");
			//最新通告
			$notice[$cate['id']] = pdo_fetchall("SELECT titlename,id FROM ".tablename('onljob_theclass_notice')." WHERE weid = '{$_W['uniacid']}' and bjid = '{$cate['id']}' ORDER BY id DESC LIMIT 0,1");
		}
	}
}
$pager = pagination($total, $pindex, $psize,'', array('before' => 0, 'after' => 0));

include template_app('m_theclass');

?>