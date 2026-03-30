<?php

if (!defined('IN_IA')) {
    exit('Access Denied');
}

global $_W, $_GPC;

require "common.php";

checkAuth();

//参数
$id = empty($_GPC['id'])?0:intval($_GPC['id']);
$type = trim($_GPC['type']);
if (empty($type)) {
	message_app('TYPE参数为空！', '', 'error');
}

if($type == 'zsd'){

	//知识点
	$srdb = pdo_get('onljob_knowledge', array('id' => $id,'weid' => $_W['uniacid']));
	if (empty($srdb)) {
		message_app('不存在或是已经被删除！', '', 'error');
	}
	
	$moneydesc = "知识点（".$srdb['titlename']."）";
	$returnurl = $this->createMobileUrl('knowledge', array('op'=>'show','id'=>$id));
	//条件
	$wheresql = array('weid' => $_W['uniacid'],'parentid' => $id,'type' => $type,'uid' => $_W['member']['uid']);
	$params['title'] = "购买知识点,编号:" . $id;
	$paymoney = $srdb['paymoney'];
	
}elseif($type == 'topup'){

	//余额充值
	$moneydesc = "系统充值余额";
	$returnurl = $this->createMobileUrl('topup');
	//条件
	$wheresql = array('weid' => $_W['uniacid'],'status' => 0,'type' => $type,'uid' => $_W['member']['uid']);
	$params['title'] = "系统充值余额";
	$paymoney = floatval($_GPC['paymoney']);
	if($paymoney <= 0) {
		message('支付错误, 金额小于0', '', 'error');
	}
	
}elseif($type == 'vip'){

	//VIP
	$srdb = pdo_get('onljob_vip_level', array('id' => $id,'weid' => $_W['uniacid']));
	if (empty($srdb)) {
		message_app('不存在或是已经被删除！', '', 'error');
	}
	
	if (intval($_GPC['numberd'] < 1)) {
		message_app('不存在或是已经被删除！', '', 'error');
	}
	
	$moneydesc = "VIP名称（".$srdb['level_name']."）";
	$returnurl = $this->createMobileUrl('m_viplevel', array('op'=>'show','id'=>$id));
	//条件
	$wheresql = array('weid' => $_W['uniacid'],'parentid' => $id,'type' => $type,'status' => '0','uid' => $_W['member']['uid']);
	$params['title'] = "支付VIP,编号:" . $id;
	$paymoney = $srdb['level_price'] * $_GPC['numberd'];
	$vipdate = 	$srdb['level_validity'] * $_GPC['numberd'];
	$vipnumberd =  $_GPC['numberd'];
}elseif($type = 'class'){

    //加入班级
    $srdb = pdo_get('onljob_theclass', array('id' => $id,'weid' => $_W['uniacid']));
    if (empty($srdb)) {
        message_app('不存在或是已经被删除！', '', 'error');
    }

    $moneydesc = "购买加入班级（".$srdb['titlename']."）";
    $returnurl = $this->createMobileUrl('theclass');
    //条件
    $wheresql = array('weid' => $_W['uniacid'],'parentid' => $id,'type' => $type,'uid' => $_W['member']['uid']);
    $params['title'] = "购买加入班级,编号:" . $srdb['numberid'];
    $paymoney = $srdb['price'];
}

if(trim($_GPC['tab']) == 'account'){
	$returnurl = $this->createMobileUrl('m_account');
}

$urlt = $this->createMobileUrl('pay');

//判断已经支付操作过
$order_inom = pdo_get('onljob_pay_order', $wheresql);
if (empty($order_inom)) {

	$data = array(
		'weid' => $_W['uniacid'],
		'parentid' => $id,
		'uid' => $_W['member']['uid'],
		'type' => $type,
		'paymoney' => $paymoney,
		'moneydesc' => $moneydesc,
		'orderid' => time() . random(4,1),
		'addtime' => time(),
		'vipdate' => intval($vipdate),
		'vipnumberd' => intval($vipnumberd)
	);
	
	$result = pdo_insert('onljob_pay_order', $data, true);
	if (!empty($result)) {
		$tid = pdo_insertid();
		$orderid = $data['orderid'];
	}else{
		message_app('下单失败！', array($returnurl), 'error', array('返回'));
	}
	
}else{

	$orderid = $order_inom['orderid'];
	$tid = $order_inom['id'];
	
	$data['paymoney'] = $paymoney;
	$data['vipdate'] = intval($vipdate);
	pdo_update('onljob_pay_order', $data, array('id' => $order_inom['id'],'weid' => $_W['uniacid']));
	if ($order_inom['status'] > 0) {
		message_app('抱歉，您的订单已经付款或是被关闭，请重新进入付款！', array($returnurl), 'error', array('返回'));
	}
	
}

//修改支付金额
$paylog = pdo_get('core_paylog', array('uniacid' => $_W['uniacid'], 'module' => $_GPC['m'], 'status' => 0, 'tid' => $orderid));
if (!empty($paylog)) {
	pdo_update('core_paylog', array('fee' => floatval($paymoney),'card_fee' => floatval($paymoney)), array('plid' => $paylog['plid'],'uniacid' => $_W['uniacid']));
}

$params['tid'] = $orderid;
$params['user'] = $_W['fans']['from_user'];
$params['fee'] = $paymoney;
$params['ordersn'] = $orderid;
$params['virtual'] = false;

include $this->template('pay');
