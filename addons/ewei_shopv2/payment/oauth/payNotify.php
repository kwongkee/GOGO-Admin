<?php
error_reporting(0);
define('IN_MOBILE', true);
require dirname(__FILE__) . '/../../../../framework/bootstrap.inc.php';
require dirname(__FILE__) . '/../../../../framework/function/diysend.func.php';
require IA_ROOT . '/addons/ewei_shopv2/defines.php';
require IA_ROOT . '/addons/ewei_shopv2/core/inc/functions.php';
require IA_ROOT . '/addons/ewei_shopv2/core/inc/plugin_model.php';
require IA_ROOT . '/addons/ewei_shopv2/core/inc/com_model.php';
global $_W;
global $_GPC;


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
	
	/**
	 * 会员钱包操作
	 * 1、查找该会员的交易记录，
	 * 2、更新会员的返利
	 */
	
	//如果返回的数据，支付状态为：0 并且：orderDesc 订单描述= 支付成功
	if ($receive['state'] == 0 && $receive['orderDesc'] == '支付成功') {//支付成功
			
			//获取订单表中的Openid
			$Money = pdo_get('pay_money',array('tid' => $receive['lowOrderId']),array('openid','title'));
			
			//查询该商家本次的返利金额；
			$Mold = pdo_get('pay_mold',array('tid' => $receive['lowOrderId']),array('mcid'));
			if($Mold){
				$Mold = pdo_get('pay_mcinfo',array('id' => $Mold['mcid']),array('feet'));
			}
			
			$feet = false;
			//查询钱包中的金额，手机，用户ID
			$getMoney = pdo_get('pay_getmoney',array('openid' => $Money['openid']),array('money','phone','openid'));
			if(!empty($getMoney))
			{
				$where = [
					'openid'=> $getMoney['openid'],
					'phone' => $getMoney['phone'],
				];
				
				$m = ($getMoney['money'] + $Mold['fet']);
				//更新表中的会员钱包返利金额；每次加0.5
				pdo_update('pay_getmoney',array('money'=> $m),$where);
				$feet = true;
			}
			
			$upPay = [//支付订单修改参数
				'payMoney'=>$receive['payMoney'],
				'orderDesc'=>$receive['orderDesc'],
				'status'=>'1',
				'upOrderId'=>$receive['upOrderId'],
				'account'=>$receive['account'],
				'merchantId'=>$receive['merchantId'],
				'payTime'=>$receive['payTime'],
			];
			
			
			$sendArr = [
				'first'=> '您好，您的订单支付成功！',
				'money'=> $receive['payMoney'].'元',//交易金额
				'shop_name'=> $Money['title'],//商品名称
				'ordersn'=> $receive['upOrderId'],//交易订单
				'time'=> $receive['payTime'],//交易时间
				'feet' => $feet?$Mold['feet'].'元':'0.00元',//本次返利
				'remark'=> '更多返利消费以及查看返利钱包，请点击详情',//详情
				'uniacid'=> '3',//公众号ID
				'Reurl'=> 'http://www.baidu.com/',//详情链接地址；
				'touser'=> $Money['openid'],//交易用户；
			];
			
			//发送订单交易通知；Fh3uY4ohrWSwKUVtTB71IWgR-7KAcKIAljkeELkyAxs
			sendPay_m($sendArr);

			$upMold = [//收款订单流水；
				'status' => '1',//支付状态
				'upOrderId' => $receive['upOrderId'],//上游流水号
				'u_time' => strtotime($receive['payTime']),//支付时间
			];
			
			//是否接收到回调  SUCCESS表示成功
		try{
			pdo_begin();//开启事务
			
				//支付成功修改pay_money表中 status 状态为1 ，更新时间为当前时间,上游订单号：更新到表中，根据订单编号修改；
				pdo_update('pay_money', $upPay, array('tid' => $receive['lowOrderId']));
				//收款订单流水；
				pdo_update('pay_Mold', $upMold, array('tid' => $receive['lowOrderId']));
				
			pdo_commit();//提交事务
		}catch(PDOException $e){
			pdo_rollback();//执行失败，事务回滚
		}
		
		$answer['finished'] = 'SUCCESS';

	} else if ($receive['state'] == 1) {//支付失败
		try{
			pdo_begin();//开启事务
//			//支付成功修改status 状态为2  pay_old支付交易表
			pdo_update('pay_money', array('status' => 2), array('tid' => $receive['lowOrderId']));

			pdo_commit();//提交事务
		}catch(PDOException $e){
			pdo_rollback();//执行失败，事务回滚
		}
		
		$answer['finished'] = 'FAIL';
	}
		
	//拼接字串
	$str = tostring($answer);
	
	//字符串拼接加密
	$str .= '&key=f8ee27742a68418da52de4fca59b999e';
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