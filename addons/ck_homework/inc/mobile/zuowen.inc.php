<?php
if (!defined('IN_IA')) {
    exit('Access Denied');
}

global $_W, $_GPC;

require "common.php";

$op = isset($_GPC['op']) ? htmlspecialchars(trim($_GPC['op'])) : '';
$urlt = $this->createMobileUrl('zuowen');
$urlt .= '&op='.$op;

if($op == 'show'){
	
	//显示
	$id = empty($_GPC['id'])?0:intval($_GPC['id']);
	$srdb = pdo_get('onljob_news', array('id' => $id,'weid' => $_W['uniacid']));
	if (empty($srdb)) {
		message_app('不存在或是已经被删除！', '', 'error');
	}
	
	$wurltitle = '作文赏析';
	$returnurl = $this->createMobileUrl('zuowen');
	if($srdb['craid'] == 1){
		$returnurl .= '&craid=1';
		$wurltitle = '英语' . $wurltitle;
	}elseif($srdb['craid'] == 2){
		$returnurl .= '&craid=2';
		$wurltitle = '语文' . $wurltitle;
	}	
	
	//内容
	$message = html_entity_decode($srdb['message']);
	
	include template_app('article_show');
	
}else{

	//列表-------------------------
	$craid = empty($_GPC['craid'])?0:intval($_GPC['craid']);
	$keyword = trim($_GPC['keyword']);
	$pindex = max(1, intval($_GPC['page']));
	$psize = empty($_GPC['psize'])?0:intval($_GPC['psize']);
	if(!in_array($psize, array(20,50,100))) $psize = 20;
	
	$where = '';
	if (!empty($keyword)) {
		$where .= " AND titlename LIKE '%{$keyword}%'";
		$urlt .= '&keyword=' . $keyword;
	}
	if (!empty($_GPC['craid'])) {
		$where .= " AND craid = '{$_GPC['craid']}'";
	}
	
	$total = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('onljob_news')." WHERE weid = '{$_W['uniacid']}' and type = 'zwsx' {$where}");
	if($total){
		$list = pdo_fetchall("SELECT * FROM ".tablename('onljob_news')." WHERE weid = '{$_W['uniacid']}' and type = 'zwsx' {$where} ORDER BY listorder ASC,id DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize);
	}
		
	$pager = pagination($total, $pindex, $psize,'', array('before' => 0, 'after' => 0));
		
	
	include template_app('zuowen_lsit');
	
}