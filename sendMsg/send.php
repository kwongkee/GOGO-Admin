<?php
	define('IN_MOBILE', true);
	define('PDO_DEBUG', true);
	require_once '../framework/bootstrap.inc.php';
	require_once '../app/common/bootstrap.app.inc.php';
	
//	require_once "Rsa.php";
	load()->app('common');
	load()->app('template');
	load()->func('diysend');
	global $_W;
	global $_GPC;
	
	$order = $_GPC;
	$adv_order = pdo_fetchall("SELECT * FROM ".tablename('foll_advertising_order') .'WHERE `id` in ('.$order['order_id'].')');

	$sendArr = [
        'uniacid'=> $adv_order[0]['uniacid'],
        'first'=>'您好,您有一份订单开票申请！',
        'ordersn'=>$adv_order[0]['ordersn'],
        'name'=>$order['user_name'],
        'xmmc'=>'商城开票服务',
        'c_date'=>date('Y-m-d H:i:s',time()),
        'remark'=>'您有新的开票申请，请点击详情完成开票！',
        'touser'=>'oR-IB0r_VdlgfjczSx0sSgdfyZAg',
    //	'touser'=>'oR-IB0t4Yc9zmV-K-_5NRB-u5k4U',
    //						'touser' => 'oR-IB0h7w3lGAxFTeeVAR3LraBZI',//接收处理人Openid
        //$_GPC['parkCheck']:ewei_shop_order 表中所有开票的自增ID，$head['id']：开票人的抬头信息自增ID；
        'Reurl'=>'http://shop.gogo198.cn/app/index.php?i='.$adv_order[0]['uniacid'].'&c=entry&m=ewei_shopv2&do=mobile&r=parking.invoicelist.check_invoiceAdv&kpid='.$order['id'],
	];

	$msg = sendInvoices($sendArr);

	return $msg;
?>