<?php
define('IN_MOBILE', true);
require '../../framework/bootstrap.inc.php';
require '../../app/common/bootstrap.app.inc.php';
load()->app('common');
load()->app('template');
load()->func('diysend');

date_default_timezone_set('Asia/Shanghai');
$input = file_get_contents('php://input');
//接收后台回调参数，并转换json格式
$receive = json_decode($input,TRUE);

file_put_contents('./log/unionpay.txt', print_r($receive,TRUE),FILE_APPEND);

if (!empty($receive)) {
	
	//组装数据返回给支付平台
	$answer = array(
		'lowOrderId'=>$receive['lowOrderId'],//下游系统流水号，必须唯一
		'merchantId'=>$receive['merchantId'],//商户进件账号
		'upOrderId'=>$receive['upOrderId'],//上游流水号
	);
	
	//支付时间
	$payTiem = strtotime($receive['payTime']);
	
	//查询公众号ID 与用户ID
	$filed = 'a.uniacid,a.pay_account,a.total,a.body,a.user_id,b.starttime,b.endtime,b.duration ';
	$find = array(':ordersn' => $receive['lowOrderId'], ':pay_status' => 0,':paystatus' => 2);
	$user = pdo_fetch("SELECT ".$filed." FROM ".tablename('foll_order')." a LEFT JOIN ".tablename('parking_order')." b ON a.ordersn = b.ordersn WHERE a.ordersn = :ordersn AND a.pay_status = :pay_status OR a.pay_status = :paystatus LIMIT 1",$find);
	
	//计算停车时长返回数组形式
	$T = timediff($user['starttime'],$user['endtime']);
	 
	//sendMassages  配置消息发送数据
	$sendArr = array(
		'body' => $user['body'],//商品描述
		'paytime' => date('Y-m-d H:i:s',time()),//消费时间
		'touser' => $user['user_id'],//接收消息的用户
		'uniacid' =>$user['uniacid'],//公众号ID				
		'parkTime' => $user['duration'].'分',//停车时长
		'realTime' => $user['duration'],//实计时长		
		'payableMoney' => $user['total'],//应付金额
		'deducMoney' =>($user['total']-$user['pay_account']),//抵扣金额
		'payMoney' => $user['pay_account'],//交易金额  实付金额
	);
	
		
	if ($receive['state'] == 0 && $receive['orderDesc'] == '支付成功') {//支付成功
		try{		
			pdo_begin();//开启事务
			
				//是否接收到回调  SUCCESS表示成功
				//支付成功修改status 状态为1  pay_old支付交易表
				pdo_update('pay_old', array('status' => 1,'update_time'=>$payTiem,'upOrderId'=>$receive['upOrderId']), array('lowOrderId' => $receive['lowOrderId']));
				//支付成功修改status 状态为1  pay_order订单表
				pdo_update('pay_order', array('status' => 1,'upOrderId'=>$receive['upOrderId']), array('ordersn' => $receive['lowOrderId']));
				//支付成功修改parking_order 表中状态：支付成功
				pdo_update('parking_order', array('upOrderId'=>$receive['upOrderId'],'status'=>'已结算'), array('ordersn' => $receive['lowOrderId']));
				pdo_update('foll_order', array('pay_status' => 1,'pay_time'=>$payTiem), array('ordersn' => $receive['lowOrderId']));
		
			pdo_commit();//提交事务
		}catch(PDOException $e){
			pdo_rollback();//执行失败，事务回滚
		}
		
		$answer['finished'] = 'SUCCESS';
		$sendArr['first'] = '您好，您的停车服务费扣费成功！';
		$sendArr['remark'] = '欢迎您再次使用智能无感路内停车服务！';
		sendMsgSuccess($sendArr);//支付成功发送消息
		
	} else if ($receive['state'] == 1) {//支付失败
		try{		
			pdo_begin();//开启事务
				//支付成功修改status 状态为2  pay_old支付交易表
				pdo_update('pay_old', array('status' => 2,'update_time'=>time(),'upOrderId'=>$receive['upOrderId']), array('lowOrderId' => $receive['lowOrderId']));
				//支付成功修改status 状态为2  pay_order订单表
				pdo_update('pay_order', array('status' => 2), array('ordersn' => $receive['lowOrderId']));
				//支付成功修改parking_order 表中状态：支付失败
//				pdo_update('parking_order', array('upOrderId'=>$receive['upOrderId'],'pay_status' => 2,'paytime'=>time(),'status'=>'待缴费'), array('ordersn' => $receive['lowOrderId']));
				pdo_update('parking_order', array('upOrderId'=>$receive['upOrderId'],'status'=>'未结算'), array('ordersn' => $receive['lowOrderId']));
			pdo_update('foll_order', array('pay_status' => 2,'pay_time'=>time()), array('ordersn' => $receive['lowOrderId']));
				
			pdo_commit();//提交事务
		}catch(PDOException $e){
			pdo_rollback();//执行失败，事务回滚
		}
		
		$answer['finished'] = 'FAIL';
		$sendArr['first'] = '您好，您的停车服务费扣费失败！';
		$sendArr['remark'] = '请点击详情，完成支付！';
		$sendArr['Reurl'] = 'http://shop.gogo198.cn/app/index.php?i='.$user['uniacid'].'&c=entry&m=ewei_shopv2&do=mobile&r=parking.pay';//跳转链接
		sendMsgError($sendArr);//支付失败模板消息
	}
	
	$str = tostring($answer);
	
	//生成的SQL等同于：SELECT username, uid FROM ims_users WHERE uid = '1' LIMIT 1
//	$user = pdo_get('users', array('uid' => 1), array('username', 'uid'));
	//查找当前所属公众号
	$uniacid = pdo_get('pay_order', array('ordersn' => $receive['lowOrderId']), array('uniacid'));
	//查找当前配置
	$config = pdo_get('pay_config', array('uniacid' =>$uniacid['uniacid']), array('config'));
	//反序列化
//	$key = unserialize($config['config']);
	$key = json_decode($config['config'],true);
	$k = $key['tg']['key'];
//	$str = $str .'&key=5f61d7f65b184d19a1e006bc9bfb6b2f';
	//字符串拼接加密；
	$str .= '&key='.$k;
	
	$answer['sign'] = strtoupper(md5($str));
	
	file_put_contents('./log/unionpaySign.txt', print_r($answer,TRUE),FILE_APPEND);
	//将数据转换成json数据返回
	echo json_encode($answer);

} else {
	echo '无参数...';
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