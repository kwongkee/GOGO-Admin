<?php
if (!defined('IN_IA')) {
    exit('Access Denied');
}

global $_W, $_GPC;

require "common.php";
require "public.php";
require "m_common.php";

$op = empty($_GPC['op'])?0:intval($_GPC['op']);
$urltk = $this->createMobileUrl('m_account');
$typeall = array('zsd' => '知识点','topup'=>'充值余额','vip'=>'续费/购买VIP','class'=>'购买加入班级');

//作废操作
if(trim($_GPC['tab']) == 'invalid'){
	$id = intval($_GPC['id']);
	$srdb = pdo_get('onljob_pay_order', array('id' => $id,'weid' => $_W['uniacid']));
	if (empty($srdb)) {
		exit('不存在或是已经被删除');
	}
	
	pdo_update('onljob_pay_order', array('status' => 2), array('id' => $id,'weid' => $_W['uniacid']));
	
	exit('操作成功');
}

//列表-------------------
$pindex = max(1, intval($_GPC['page']));
$psize = empty($_GPC['psize'])?0:intval($_GPC['psize']);
if(!in_array($psize, array(20,50,100))) $psize = 20;

$where = '';
if($op == 1){         $where = ' and status = 0';
}elseif($op == 2){    $where = ' and status = 1';
}elseif($op == 3){    $where = ' and status = 2';
}

$total = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('onljob_pay_order')." WHERE weid = '{$_W['uniacid']}' and uid = '{$_W['member']['uid']}' {$where}");
if($total){
	$list = pdo_fetchall("SELECT * FROM ".tablename('onljob_pay_order')." WHERE weid = '{$_W['uniacid']}' and uid = '{$_W['member']['uid']}' {$where} ORDER BY id DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize);
}
	
$pager = pagination($total, $pindex, $psize,'', array('before' => 0, 'after' => 0));

	
include template_app('m_account');