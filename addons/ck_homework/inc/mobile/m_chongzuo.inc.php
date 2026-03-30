<?php

if (!defined('IN_IA')) {
    exit('Access Denied');
}

global $_W, $_GPC;
require "common.php";
require "public.php";
require "m_common.php";

$xkid = empty($_GPC['xkid'])?0:intval($_GPC['xkid']);
$tab = empty($_GPC['tab'])?0:intval($_GPC['tab']);
$op = trim($_GPC['op']);

$urlt = $this->createMobileUrl('m_chongzuo');
$urltk = $this->createMobileUrl('m_chongzuo') . "&tab=" . $tab;

//科目
$category = pdo_fetchall("SELECT * FROM " . tablename('onljob_class') . " WHERE weid = '{$_W['uniacid']}' and pid = '0' and type = 'xk' ORDER BY listorder ASC, cid DESC", array(), 'cid');

//年级
$category_nj = pdo_fetchall("SELECT * FROM " . tablename('onljob_class') . " WHERE weid = '{$_W['uniacid']}' and pid = '0' and type = 'nj' ORDER BY listorder ASC, cid DESC", array(), 'cid');

//列表------------------
$pindex = max(1, intval($_GPC['page']));
$psize = empty($_GPC['psize'])?0:intval($_GPC['psize']);
if(!in_array($psize, array(20,50,100))) $psize = 20;
	
$where = '';

if($tab == 1){

	//自我组卷测试
	$total = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('onljob_practice_fen')." WHERE weid = '{$_W['uniacid']}' and uid = '{$_W['member']['uid']}' and type = '1' {$where} ");
	if($total){
		$list = pdo_fetchall("SELECT * FROM ".tablename('onljob_practice_fen')." WHERE weid = '{$_W['uniacid']}' and uid = '{$_W['member']['uid']}' and type = '1' {$where} ORDER BY fid DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize);
		if (!empty($list)) {
			$list_yz = array();
			foreach ($list as $cid => $cate) {
				//获取题目数量
				$cate['total_q'] = pdo_fetchcolumn("SELECT COUNT(*) FROM " . tablename('onljob_practice_answer') . " WHERE weid = '{$_W['uniacid']}' and fid = '{$cate['fid']}' and uid = '{$_W['member']['uid']}'");
				//获取错误题数
				$cate['total_cwq'] = pdo_fetchcolumn("SELECT COUNT(*) FROM " . tablename('onljob_practice_answer') . " WHERE weid = '{$_W['uniacid']}' and fid = '{$cate['fid']}' and uid = '{$_W['member']['uid']}' and stateh = '1'");
				$list_yz[] = $cate;
			}
		}
	}
	
}else{
	
	//作业错题重做
	if(!empty($xkid)){
		$where .= " and c.xkid = '{$xkid}' ";
	}
	
	$total = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('onljob_practice_fen')." AS a LEFT JOIN ".tablename('onljob_theclass')." AS c ON a.bjid = c.id WHERE a.weid = '{$_W['uniacid']}' and a.uid = '{$_W['member']['uid']}' and a.type = '0' {$where} ");
	if($total){
		$list = pdo_fetchall("SELECT a.* FROM ".tablename('onljob_practice_fen')." AS a LEFT JOIN ".tablename('onljob_theclass')." AS c ON a.bjid = c.id WHERE a.weid = '{$_W['uniacid']}' and a.uid = '{$_W['member']['uid']}' and a.type = '0' {$where} ORDER BY a.fid DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize);
		if (!empty($list)) {
			$list_yz = array();
			foreach ($list as $cid => $cate) {
				//获取题目数量
				$cate['total_q'] = pdo_fetchcolumn("SELECT COUNT(*) FROM " . tablename('onljob_practice_answer') . " WHERE weid = '{$_W['uniacid']}' and fid = '{$cate['fid']}' and uid = '{$_W['member']['uid']}'");
				//获取错误题数
				$cate['total_cwq'] = pdo_fetchcolumn("SELECT COUNT(*) FROM " . tablename('onljob_practice_answer') . " WHERE weid = '{$_W['uniacid']}' and fid = '{$cate['fid']}' and uid = '{$_W['member']['uid']}' and stateh = '1'");
				$list_yz[] = $cate;
			}
		}
	}

}

$pager = pagination($total, $pindex, $psize,'', array('before' => 0, 'after' => 0));


include template_app('m_chongzuo');
?>