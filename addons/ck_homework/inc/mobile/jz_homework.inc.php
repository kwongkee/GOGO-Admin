<?php

if (!defined('IN_IA')) {
    exit('Access Denied');
}

global $_W, $_GPC;
require "common.php";
require "public.php";
require "jz_common.php";

$xkid = empty($_GPC['xkid'])?0:intval($_GPC['xkid']);
$tab = empty($_GPC['tab'])?0:intval($_GPC['tab']);
$op = trim($_GPC['op']);

$urlt = $this->createMobileUrl('jz_homework');
$urltk = $this->createMobileUrl('jz_homework') . "&tab=" . $tab;

//科目
$category = pdo_fetchall("SELECT * FROM " . tablename('onljob_class') . " WHERE weid = '{$_W['uniacid']}' and pid = '0' and type = 'xk' ORDER BY listorder ASC, cid DESC", array(), 'cid');

//年级
$category_nj = pdo_fetchall("SELECT * FROM " . tablename('onljob_class') . " WHERE weid = '{$_W['uniacid']}' and pid = '0' and type = 'nj' ORDER BY listorder ASC, cid DESC", array(), 'cid');

//列表------------------
$pindex = max(1, intval($_GPC['page']));
$psize = empty($_GPC['psize'])?0:intval($_GPC['psize']);
if(!in_array($psize, array(20,50,100))) $psize = 20;


if($tab > 0){
	
	$where = '';
	if(!empty($xkid)){
		$where .= " and c.xkid = '{$xkid}' ";
	}
	if($tab == '2'){
		$where .= " and a.state = '0'";  //待批改作业
	}else{
		$where .= " and a.state = '1'";  //已批改作业
	}
	
	$total = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('onljob_work_fen')." AS a LEFT JOIN ".tablename('onljob_work')." AS b ON a.wid = b.wid LEFT JOIN ".tablename('onljob_theclass')." AS c ON a.bjid = c.id WHERE a.weid = '{$_W['uniacid']}' and a.uid = '{$_SESSION['hzuid']}' {$where} ");
	if($total){
		$list = pdo_fetchall("SELECT b.titlename,a.* FROM ".tablename('onljob_work_fen')." AS a LEFT JOIN ".tablename('onljob_work')." AS b ON a.wid = b.wid LEFT JOIN ".tablename('onljob_theclass')." AS c ON a.bjid = c.id WHERE a.weid = '{$_W['uniacid']}' and a.uid = '{$_SESSION['hzuid']}' {$where} ORDER BY a.fid DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize);
		if (!empty($list)) {
			$list_yz = array();
			foreach ($list as $cid => $cate) {
				//获取题目数量
				$cate['total_q'] = pdo_fetchcolumn("SELECT COUNT(*) FROM " . tablename('onljob_work_questions') . " WHERE weid = '{$_W['uniacid']}' and bjid = '{$cate['bjid']}' and wid = '{$cate['wid']}'");
				//获取错误题数
				$cate['total_cwq'] = pdo_fetchcolumn("SELECT COUNT(*) FROM " . tablename('onljob_work_answer') . " WHERE weid = '{$_W['uniacid']}' and fid = '{$cate['fid']}' and uid = '{$_SESSION['hzuid']}' and stateh = '1'");
				$list_yz[] = $cate;
			}
		}
	}
	
	$pager = pagination($total, $pindex, $psize,'', array('before' => 0, 'after' => 0));

}else{
	
	//未完成的作业
	$where = '';
	if(!empty($xkid)){
		$where .= " and c.xkid = '{$xkid}' ";
	}
	
	$list = pdo_fetchall("SELECT a.* FROM ".tablename('onljob_work')." AS a LEFT JOIN ".tablename('onljob_theclass_apply')." AS b ON a.bjid = b.bjid LEFT JOIN ".tablename('onljob_theclass')." AS c ON a.bjid = c.id WHERE a.weid = '{$_W['uniacid']}' and a.releaset = '1' and b.uid = '{$_SESSION['hzuid']}' {$where} ORDER BY a.wid DESC ");
	if (!empty($list)) {
		$list_wz = array();
		foreach ($list as $k => $value) {
			$xs_fenf = pdo_get('onljob_work_fen', array('uid' => $_SESSION['hzuid'],'wid' => $value['wid'],'bjid' => $value['bjid'],'weid' => $_W['uniacid']));
			if (empty($xs_fenf)) {
				//获取题目数量
				$value['total_q'] = pdo_fetchcolumn("SELECT COUNT(*) FROM " . tablename('onljob_work_questions') . " WHERE weid = '{$_W['uniacid']}' and bjid = '{$value['bjid']}' and wid = '{$value['wid']}'");
				$list_wz[] = $value;
			}
		}
	}
	
}

include template_app('jz_homework');
?>