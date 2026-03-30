<?php

if (!defined('IN_IA')) {
    exit('Access Denied');
}

global $_W, $_GPC;
load()->func('tpl');

require "common.php";
require "public.php";
require "m_common.php";

$op = empty($_GPC['op'])?0:intval($_GPC['op']);
$id = empty($_GPC['id'])?0:intval($_GPC['id']);
$tab = empty($_GPC['tab'])?0:intval($_GPC['tab']);
$urltk = $this->createMobileUrl('m_theclass_show').'&id='.$id;

//科目
$category = pdo_fetchall("SELECT * FROM " . tablename('onljob_class') . " WHERE weid = '{$_W['uniacid']}' and pid = '0' and type = 'xk' ORDER BY listorder ASC, cid DESC", array(), 'cid');

//年级
$category_nj = pdo_fetchall("SELECT * FROM " . tablename('onljob_class') . " WHERE weid = '{$_W['uniacid']}' and pid = '0' and type = 'nj' ORDER BY listorder ASC, cid DESC", array(), 'cid');

//显示内容 
$srdb = pdo_get('onljob_theclass', array('id' => $id,'weid' => $_W['uniacid']));
if (empty($srdb)) {
	message_app('参数错误！访问失败！', '', 'error');
}

//列表-------------------------
$pindex = max(1, intval($_GPC['page']));
$psize = empty($_GPC['psize'])?0:intval($_GPC['psize']);
if(!in_array($psize, array(20,50,100))) $psize = 20;

if($op == 1){

	//班级通知
	$title = '班级通知';
	$total = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('onljob_theclass_notice')." WHERE weid = '{$_W['uniacid']}' and bjid = '{$id}' {$where}");
	if($total){
		$list = pdo_fetchall("SELECT * FROM ".tablename('onljob_theclass_notice')." WHERE weid = '{$_W['uniacid']}' and bjid = '{$id}' {$where} ORDER BY id DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize);
	}
	
	$pager = pagination($total, $pindex, $psize,'', array('before' => 0, 'after' => 0));
	include template_app('m_theclass_notice');
	exit();
	
}elseif($op == 2){

	//班级作业
	$title = '班级作业';
	
	if($tab > 0){
	
		$where = '';
		if($tab == '2'){
			$where .= " and a.state = '0'";  //待批改作业
		}else{
			$where .= " and a.state = '1'";  //已批改作业
		}
		
		$total = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('onljob_work_fen')." AS a LEFT JOIN ".tablename('onljob_work')." AS b ON a.wid = b.wid LEFT JOIN ".tablename('onljob_theclass')." AS c ON a.bjid = c.id WHERE a.weid = '{$_W['uniacid']}' and a.uid = '{$_W['member']['uid']}' and a.bjid = '{$id}' {$where} ");
		if($total){
			$list = pdo_fetchall("SELECT b.titlename,a.* FROM ".tablename('onljob_work_fen')." AS a LEFT JOIN ".tablename('onljob_work')." AS b ON a.wid = b.wid LEFT JOIN ".tablename('onljob_theclass')." AS c ON a.bjid = c.id WHERE a.weid = '{$_W['uniacid']}' and a.uid = '{$_W['member']['uid']}' and a.bjid = '{$id}' {$where} ORDER BY a.fid DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize);
			if (!empty($list)) {
				$list_yz = array();
				foreach ($list as $cid => $cate) {
					//获取题目数量
					$cate['total_q'] = pdo_fetchcolumn("SELECT COUNT(*) FROM " . tablename('onljob_work_questions') . " WHERE weid = '{$_W['uniacid']}' and bjid = '{$cate['bjid']}' and wid = '{$cate['wid']}'");
					//获取错误题数
					$cate['total_cwq'] = pdo_fetchcolumn("SELECT COUNT(*) FROM " . tablename('onljob_work_answer') . " WHERE weid = '{$_W['uniacid']}' and fid = '{$cate['fid']}' and uid = '{$_W['member']['uid']}' and stateh = '1'");
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
		
		$list = pdo_fetchall("SELECT a.* FROM ".tablename('onljob_work')." AS a LEFT JOIN ".tablename('onljob_theclass_apply')." AS b ON a.bjid = b.bjid LEFT JOIN ".tablename('onljob_theclass')." AS c ON a.bjid = c.id WHERE a.weid = '{$_W['uniacid']}' and a.releaset = '1' and b.uid = '{$_W['member']['uid']}' and a.bjid = '{$id}' {$where} ORDER BY a.wid DESC ");
		if (!empty($list)) {
			$list_wz = array();
			foreach ($list as $k => $value) {
				$xs_fenf = pdo_get('onljob_work_fen', array('uid' => $_W['member']['uid'],'wid' => $value['wid'],'bjid' => $value['bjid'],'weid' => $_W['uniacid']));
				if (empty($xs_fenf)) {
					//获取题目数量
					$value['total_q'] = pdo_fetchcolumn("SELECT COUNT(*) FROM " . tablename('onljob_work_questions') . " WHERE weid = '{$_W['uniacid']}' and bjid = '{$value['bjid']}' and wid = '{$value['wid']}'");
					$list_wz[] = $value;
				}
			}
		}
		
	}
	
	include template_app('m_theclass_homework');
	exit();
	
}else{

	//班级信息---------------------------------
	$title = '班级信息';
	//二维码地址
	$attach_dir = IA_ROOT . "/addons/".$_GPC['m']."/data/bjqrcode/" . $_W['uniacid'] . "/";
	$qrcode_url = $srdb['qrcode'];

	//获取学生列表
	$total = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('onljob_theclass_apply')." AS a LEFT JOIN ".tablename('onljob_user')." AS b ON a.uid = b.uid WHERE a.weid = '{$_W['uniacid']}' and a.bjid = '{$id}' and state = '1' ");
	if($total){
		$list = pdo_fetchall("SELECT a.*,b.name FROM ".tablename('onljob_theclass_apply')." AS a LEFT JOIN ".tablename('onljob_user')." AS b ON a.uid = b.uid WHERE a.weid = '{$_W['uniacid']}' and a.bjid = '{$id}' and state = '1' ORDER BY a.id DESC");
	}
	
	include template_app('m_theclass_show');
	
}