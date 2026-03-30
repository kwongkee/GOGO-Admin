<?php
if (!defined('IN_IA')) {
    exit('Access Denied');
}

global $_W, $_GPC;
load()->func('tpl');

require "common.php";
require "public.php";
require "t_common.php";

$op = empty($_GPC['op'])?0:intval($_GPC['op']);
$bjid = empty($_GPC['bjid'])?0:intval($_GPC['bjid']);


$urltk = $this->createMobileUrl('t_homework');

//科目
$category = pdo_fetchall("SELECT * FROM " . tablename('onljob_class') . " WHERE weid = '{$_W['uniacid']}' and pid = '0' and type = 'xk' ORDER BY listorder ASC, cid DESC", array(), 'cid');

//年级
$category_nj = pdo_fetchall("SELECT * FROM " . tablename('onljob_class') . " WHERE weid = '{$_W['uniacid']}' and pid = '0' and type = 'nj' ORDER BY listorder ASC, cid DESC", array(), 'cid');

//班级
$theclass_list = pdo_fetchall("SELECT * FROM " . tablename('onljob_theclass') . " WHERE weid = '{$_W['uniacid']}' and uid = '{$_W['member']['uid']}' and state != '1' ORDER BY id DESC", array(), 'id');
	
//列表------------------
$pindex = max(1, intval($_GPC['page']));
$psize = empty($_GPC['psize'])?0:intval($_GPC['psize']);
if(!in_array($psize, array(20,50,100))) $psize = 20;
	
$where = '';
if(!empty($bjid)){
	$where .= " and a.bjid = '{$bjid}' ";
}

$total = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('onljob_work_fen')." AS a LEFT JOIN ".tablename('onljob_work')." AS b ON a.wid = b.wid LEFT JOIN ".tablename('onljob_user')." AS c ON a.uid = c.uid WHERE a.weid = '{$_W['uniacid']}' and b.uid = '{$_W['member']['uid']}' {$where} ");
if($total){
	$list = pdo_fetchall("SELECT b.titlename,a.*,c.name,c.uid AS stuid FROM ".tablename('onljob_work_fen')." AS a LEFT JOIN ".tablename('onljob_work')." AS b ON a.wid = b.wid LEFT JOIN ".tablename('onljob_user')." AS c ON a.uid = c.uid WHERE a.weid = '{$_W['uniacid']}' and b.uid = '{$_W['member']['uid']}' {$where} ORDER BY a.fid DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize);
	if (!empty($list)) {
		foreach ($list as $cid => $cate) {
			//自动批改题数量
			$ypg_total[$cate['fid']] = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('onljob_work_answer')." AS a LEFT JOIN ".tablename('onljob_questions')." AS b ON a.qid = b.id WHERE  a.weid = '{$_W['uniacid']}' and a.fid = '{$cate['fid']}' and b.type < 5 ");
			//获取错误题数
			$cw_total[$cate['fid']] = pdo_fetchcolumn("SELECT COUNT(*) FROM " . tablename('onljob_work_answer') . " WHERE weid = '{$_W['uniacid']}' and fid = '{$cate['fid']}' and stateh = '1'");
			//需批改题数
			$xpg_total[$cate['fid']] = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('onljob_work_answer')." AS a LEFT JOIN ".tablename('onljob_questions')." AS b ON a.qid = b.id WHERE  a.weid = '{$_W['uniacid']}' and a.fid = '{$cate['fid']}' and b.type > 4 ");
		}
	}
}

$pager = pagination($total, $pindex, $psize,'', array('before' => 0, 'after' => 0));
	
include template_app('t_homework');