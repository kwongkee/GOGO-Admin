<?php
define('IN_MOBILE', true);
require_once '../../framework/bootstrap.inc.php';
require_once '../../app/common/bootstrap.app.inc.php';
load()->app('common');
load()->app('template');
load()->func('diysend');
date_default_timezone_set('Asia/Shanghai');

$input = file_get_contents('php://input');
//接收后台回调参数，并转换json格式
$receive = json_decode($input,TRUE);

file_put_contents('./log/wecahtpaylog'.date('Ymd').'.txt', $input."\r\n",FILE_APPEND);
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

// 91110046991326511104

if (!empty($receive)) {
	
	//组装数据返回给支付平台
	$answer = array(
		'lowOrderId'=>$receive['lowOrderId'],//下游系统流水号，必须唯一
		'merchantId'=>$receive['merchantId'],//商户进件账号
		'upOrderId'=>$receive['upOrderId'],//上游流水号
	);
	$payMoney = sprintf("%.2f",$receive['payMoney']);
	
	//设置数据缓存；
	$cacheArr['TgWechat'] = $receive['lowOrderId'];
	$key = 'TgWechat';
	
	//不为空，取缓存数据；判断数据一致则直接退出
	if(!empty($cache = cache_load($key)) && ($cache['TgWechat'] == $receive['lowOrderId']) )
	{
		echo 'SUCCESS';
		exit();
	}
	//写入缓存
	cache_write($key,$cacheArr);
	
	//支付时间
	$payTime = strtotime($receive['payTime']);

	//按提交返回的订单编号查询pay_order订单表中的 ，公众号ID，支付金额，商品描述，用户ID，支付状态：	
//	$user = pdo_fetch("SELECT a.uniacid,a.pay_account,a.total,a.body,a.user_id,b.starttime,b.endtime,b.duration FROM ".tablename('foll_order')." a LEFT JOIN ".tablename('parking_order')." b ON a.ordersn = b.ordersn WHERE a.ordersn = :ordersn LIMIT 1", array(':ordersn' => $receive['lowOrderId']));
	
	$filed = 'a.uniacid,a.pay_account,a.total,a.body,a.user_id,b.starttime,b.endtime,b.duration ';
	$find = array(':ordersn' => $receive['lowOrderId'], ':pay_status' => 0,':paystatus' => 2);
	$user = pdo_fetch("SELECT ".$filed." FROM ".tablename('foll_order')." a LEFT JOIN ".tablename('parking_order')." b ON a.ordersn = b.ordersn WHERE a.ordersn = :ordersn AND (a.pay_status = :pay_status OR a.pay_status = :paystatus) LIMIT 1",$find);
	
	/**
	 * array(
	 * 'uniacid',//公众号ID
	 * 'openid',//用户ID
	 * 'body',//订单描述
	 * 'total',//总金额
	 * 'PayAmount',//优惠后金额
	 * 'starttime',//进场时间
	 * 'endtime',//出场时间
	 * 'pay_status'//支付状态:0:未支付。1：已支付,2：支付失败
	 * )*/
	//计算停车时长返回数组形式	b
	//$T = timediff($user['starttime'],$user['endtime']);
	 
	//sendMassages  配置消息发送数据
	$sendArr = array(
		'body' 		=> $user['body'],//商品描述		a
		'paytime' 	=> date('Y-m-d H:i:s',time()),//消费时间
		'touser' 	=> $user['user_id'],//接收消息的用户	a
		'uniacid' 	=> $user['uniacid'],//公众号ID	a
		'parkTime' 		=> ceil(($user['endtime']-$user['starttime'])/60),//$user['duration'],//停车时长
		'realTime' 		=> $user['duration'],//实计时长		b
		'payableMoney' 	=> sprintf("%.2f",$user['total']),//应付金额	a
		'deducMoney' 	=> abs(sprintf('%.2f',($user['total']- $payMoney))),//抵扣金额	a
		'payMoney' 		=> $payMoney,//sprintf("%.2f",$user['pay_account']),//交易金额  实付金额	a
	);
	
	//file_put_contents('./log/sendMsgss.txt',print_r($sendArr),FILE_APPEND);
	
	//如果返回的数据，支付状态为：0 并且：orderDesc 订单描述= 支付成功
	if ($receive['state'] == '0' && $receive['orderDesc'] == '支付成功') {//支付成功
			//是否接收到回调  SUCCESS表示成功
		try{
			pdo_begin();//开启事务
			
			//支付成功修改pay_old表中 status 状态为1 ，更新时间为当前时间,上游订单号：更新到表中，根据订单编号修改；
			pdo_update('pay_old', array('status' => 1,'update_time'=>$payTime,'upOrderId'=>$receive['upOrderId']), array('ordersn' => $receive['lowOrderId']));
			//支付成功修改status 状态为1  pay_order订单表
			pdo_update('pay_order', array('status' => 1,'upOrderId'=>$receive['upOrderId']), array('ordersn' => $receive['lowOrderId']));
			//支付成功修改parking_order 表中状态：支付成功
//			pdo_update('parking_order', array('upOrderId'=>$receive['upOrderId'],'pay_status' => 1,'paytime'=> time(),'status'=>'已缴费'), array('ordersn' => $receive['lowOrderId']));
			pdo_update('parking_order', array('status'=>'已结算'), array('ordersn' => $receive['lowOrderId']));
			pdo_update('foll_order', array('pay_status' => 1,'upOrderId'=>$receive['upOrderId'],'pay_time'=>$payTime,'pay_account'=>$payMoney,'pay_type'=>'wechat'), array('ordersn' => $receive['lowOrderId']));
			
			pdo_commit();//提交事务
		}catch(PDOException $e){
			pdo_rollback();//执行失败，事务回滚
		}
		
		//2018-05-11 支付成功，返回订单号与支付类型；
		postCredit($receive['lowOrderId']);
		//预付费回调请求
		//postCredits($receive['lowOrderId']);
		
		$answer['finished'] = 'SUCCESS';
		$sendArr['first'] = '您好，您的停车服务费扣费成功！';
		$sendArr['remark'] = '欢迎您再次使用智能无感路内停车服务！';
		
		//sendMsgSuccess($sendArr);//支付成功发送消息

        // 2019-06-28
		sendSuccesTempl($sendArr);//支付成功模板
		//sendMessagess($sendArr);
		exit('SUCCESS');
		
	} else if ($receive['state'] == 1) {//支付失败
		
		try{
			
			pdo_begin();//开启事务
			
				//支付成功修改status 状态为2  pay_old支付交易表
				pdo_update('pay_old', array('status' => 2,'update_time'=>$payTime,'upOrderId'=>$receive['upOrderId']), array('ordersn' => $receive['lowOrderId']));
				//支付成功修改status 状态为2  pay_order订单表
				pdo_update('pay_order', array('status' => 2), array('ordersn' => $receive['lowOrderId']));
				//支付成功修改parking_order 表中状态：支付失败；
				pdo_update('parking_order', array('status'=>'未结算'), array('ordersn' => $receive['lowOrderId']));
				pdo_update('foll_order', array('pay_status' => 2,'upOrderId'=>$receive['upOrderId'],'pay_time'=>$payTime,'pay_account'=>$payMoney,'pay_type'=>'wechat'), array('ordersn' => $receive['lowOrderId']));
			
			pdo_commit();//提交事务
		}catch(PDOException $e){
			pdo_rollback();//执行失败，事务回滚
		}
		
		$answer['finished'] = 'FAIL';
		$sendArr['first'] = '您好，您的停车服务费扣费失败！';
		$sendArr['remark'] = '请点击详情，完成支付！';
		$sendArr['Reurl'] = 'http://shop.gogo198.cn/app/index.php?i='.$user['uniacid'].'&c=entry&m=ewei_shopv2&do=mobile&r=parking.pay';//跳转链接
		//sendMsgError($sendArr);//支付失败模板消息

        // 2019-06-28
        sendErrorTempl($sendArr);//支付失败模板
		//sendMessagess($sendArr);
		exit('FAIL');
	}
		
	//拼接字串
	//$str = tostring($answer);
	
	//查找当前配置
	/*$config = pdo_get('pay_config', array('uniacid' =>$user['uniacid']), array('config'));
	//反序列化
//	$key = unserialize($config['config']);
	$key = json_decode($config['config'],true);
	$k = $key['tg']['key'];
	//字符串拼接加密
	$str .= '&key='.$k;
	$answer['sign'] = strtoupper(md5($str));
	//将数据转换成json数据返回
	echo json_encode($answer);
	file_put_contents('./log/wecahtjsons.txt', json_encode($answer)."\r\n",FILE_APPEND);*/
	
} else {
	$answer['finished'] = 'FAIL';
	//echo json_encode($answer);
	exit('FAIL');
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

function postCredit($ordersn) {
	$postData = [
		'ordersn'=>$ordersn,
		'type'=>'wp'
	];
	$curl = curl_init();
	curl_setopt_array($curl, array(
	  CURLOPT_URL => "http://shop.gogo198.cn/foll/public/?s=api/pullOnlinePayStatusApi",
	  CURLOPT_RETURNTRANSFER => true,
	  CURLOPT_ENCODING => "",
	  CURLOPT_MAXREDIRS => 10,
	  CURLOPT_TIMEOUT => 30,
	  CURLOPT_CUSTOMREQUEST => "POST",
	  CURLOPT_POSTFIELDS => json_encode($postData),
	  CURLOPT_HTTPHEADER => array(
	    "Cache-Control: no-cache",
	    "Content-Type: application/json",
	  ),
	));
	
	$response = curl_exec($curl);
	$err = curl_error($curl);
	curl_close($curl);
}

/**
 * 预付费成功回调
 */
function postCredits($ordersn) {
	$postData = [
		'ordersn'=>$ordersn
	];
	$curl = curl_init();
	curl_setopt_array($curl, array(
	  CURLOPT_URL => "http://120.78.202.118:80/foll/public/?s=api/lights_up",
	  CURLOPT_RETURNTRANSFER => true,
	  CURLOPT_ENCODING => "",
	  CURLOPT_MAXREDIRS => 10,
	  CURLOPT_TIMEOUT => 30,
	  CURLOPT_CUSTOMREQUEST => "POST",
	  CURLOPT_POSTFIELDS => json_encode($postData),
	  CURLOPT_HTTPHEADER => array(
	    "Cache-Control: no-cache",
	    "Content-Type: application/json",
	  ),
	));
	
	$response = curl_exec($curl);
	$err = curl_error($curl);
	curl_close($curl);
}

?>