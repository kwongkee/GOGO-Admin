<?php

if (!defined('IN_IA')) {
    exit('Access Denied');
}

global $_W, $_GPC;
require "common.php";
require "public.php";
require "m_common.php";

//编码
$op = trim($_GPC['op']);
$tab = trim($_GPC['tab']);
$urlt = $this->createMobileUrl('m_viplevel');
//支付地址
$payurl = $this->createMobileUrl('pay');

if($op == 'show'){
	//显示
	if($tab == 'continue'){	$returnurl = $this->createMobileUrl('m_index'); $titelname = '续费'; }else{ $returnurl = $urlt; $titelname = '购买';}

	$id = empty($_GPC['id'])?0:intval($_GPC['id']);
	$srdb = pdo_get('onljob_vip_level', array('id' => $id,'weid' => $_W['uniacid']));
	if (empty($srdb)) {
		message_app('不存在或是已经被删除！', '', 'error');
	}
	
	if($srdb['is_show'] != '1'){
		if($tab == 'continue'){	$message = '抱歉！该VIP已关闭不能续费！'; }else{ $message = '抱歉！该VIP已关闭不能购买！';}
		message_app($message, '', 'error');
	}
	
	include template_app('m_viplevel_show');
}else{
	//列表
	include template_app('m_viplevel');
}
?>