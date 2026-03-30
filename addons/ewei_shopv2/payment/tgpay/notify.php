<?php
define('IN_MOBILE', true);
require_once $_SERVER['DOCUMENT_ROOT'].'/framework/bootstrap.inc.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/app/common/bootstrap.app.inc.php';
///www/web/default/addons/ewei_shopv2/payment/tgpay/notify.php
//echo $_SERVER['DOCUMENT_ROOT'];
load()->app('common');
load()->app('template');
load()->func('diysend');
date_default_timezone_set('Asia/Shanghai');

$input = file_get_contents('php://input');
//接收后台回调参数，并转换json格式
$receive = json_decode($input,TRUE);

file_put_contents('./log/wecahtpaylog.txt', print_r($receive,TRUE),FILE_APPEND);
/**
 * [sign] => 4A654996F755DAC09994BFDBECBADAFB
    [payMoney] => 0.01
    [orderDesc] => 支付成功
    [state] => 0
    [upOrderId] => 9938666984466415616
    [account] => 101570223660
    [openid] => o-Rj7wKcdzw97DMkBEs_n94qcN8g
    [merchantId] => 617112200019682
    [payTime] => 2017-12-07 15:10:33
    [lowOrderId] => G99198商务号20171207300468061
 */

if (!empty($receive)) {
	
	//组装数据返回给支付平台
	$answer = array(
		'lowOrderId'=>$receive['lowOrderId'],//下游系统流水号，必须唯一
		'merchantId'=>$receive['merchantId'],//商户进件账号
		'upOrderId'=>$receive['upOrderId'],//上游流水号
	);

	//按提交返回的订单编号查询pay_order订单表中的 ，公众号ID，支付金额，商品描述，用户ID，支付状态：
	$user = pdo_get('pay_order', array('ordersn' => $receive['lowOrderId']), array('uniacid','payMoney','body','Openid','status'));
	
	//sendMassages  配置消息发送数据
	$sendArr = array(
		'touser' => $user['Openid'],//接收消息的用户
		'payMoney' =>$user['payMoney'],//交易金额
		'uniacid' =>$user['uniacid'],//公众号ID
		'body' => $user['body'],//商品描述
		'paytime' => date('Y-m-d H:i:s',time()),//消费时间
	);
	
	//如果返回的数据，支付状态为：0 并且：orderDesc 订单描述= 支付成功
	if ($receive['state'] == 0 && $receive['orderDesc'] == '支付成功') {//支付成功
//			//是否接收到回调  SUCCESS表示成功
		try{
			
			pdo_begin();//开启事务
//			//支付成功修改pay_old表中 status 状态为1 ，更新时间为当前时间,上游订单号：更新到表中，根据订单编号修改；
			pdo_update('pay_old', array('status' => 1,'update_time'=>time(),'upOrderId'=>$receive['upOrderId']), array('lowOrderId' => $receive['lowOrderId']));
//			//支付成功修改status 状态为1  pay_order订单表
			pdo_update('pay_order', array('status' => 1), array('ordersn' => $receive['lowOrderId']));
//			//支付成功修改parking_order 表中状态：支付成功
			pdo_update('parking_order', array('pay_status' => 1,'paytime'=> time()), array('ordersn' => $receive['lowOrderId']));
			pdo_commit();//提交事务
		}catch(PDOException $e){
			pdo_rollback();//执行失败，事务回滚
		}
//			
		$answer['finished'] = 'SUCCESS';
		$sendArr['first'] = '您好，您的停车服务费扣费成功！';
		$sendArr['remark'] = '欢迎您再次使用！';
		sendMessagess($sendArr);

	} else if ($receive['state'] == 1) {//支付失败
		try{
			pdo_begin();//开启事务
//			//支付成功修改status 状态为2  pay_old支付交易表
			pdo_update('pay_old', array('status' => 2,'update_time'=>time(),'upOrderId'=>$receive['upOrderId']), array('lowOrderId' => $receive['lowOrderId']));
//			//支付成功修改status 状态为2  pay_order订单表
			pdo_update('pay_order', array('status' => 2), array('ordersn' => $receive['lowOrderId']));
//			//支付成功修改parking_order 表中状态：支付失败；
			pdo_update('parking_order', array('pay_status' => 2,'paytime'=>time()), array('ordersn' => $receive['lowOrderId']));
			pdo_commit();//提交事务
		}catch(PDOException $e){
			pdo_rollback();//执行失败，事务回滚
		}
		
		$answer['finished'] = 'FAIL';
		$sendArr['first'] = '您好，您的停车服务费扣费失败！';
		$sendArr['remark'] = '请点击详情，完成支付！';
		$sendArr['Reurl'] = 'http://shop.gogo198.cn/app/index.php?i=14&c=entry&m=ewei_shopv2&do=mobile&r=parking.pay';//跳转链接
		sendMessagess($sendArr);
	}
		
	//拼接字串
	$str = tostring($answer);
	
	//查找当前配置
	$config = pdo_get('pay_config', array('uniacid' =>$user['uniacid']), array('config'));
	//反序列化
	$key = unserialize($config['config']);
	
	$k = $key['tg']['key'];
	//字符串拼接加密
	$str .= '&key='.$k;
	$answer['sign'] = strtoupper(md5($str));
	file_put_contents('./log/wecahtjsons.txt', print_r($answer,TRUE),FILE_APPEND);
	//将数据转换成json数据返回
	echo json_encode($answer);
	
} else {
	$answer['finished'] = 'FAIL';
	echo json_encode($answer);
}

/**
 * 字符串拼接
 */
function tostring($arrs) {
	ksort($arrs, SORT_STRING);
	$str = '';
	foreach ($arrs as $key => $v ) {
		if (empty($v)) {
			continue;
		}
		$str .= $key . '=' . $v . '&';
	}
	$str = trim($str,'&');
	return $str;
}
?>