<?php
//停车扣费后台回调；
define('IN_MOBILE', true);
require '../../framework/bootstrap.inc.php';
require '../../app/common/bootstrap.app.inc.php';
load()->app('common');
load()->app('template');
load()->func('diysend');

file_put_contents('./logs/Notiparks.txt', print_r($_POST,TRUE));

if (!empty($_POST) && isset($_POST['PACKET'])) {
	
	$obj = isimplexml_load_string($_POST['PACKET'], 'SimpleXMLElement', LIBXML_NOCDATA);
	//转换成数组；
	$data = json_decode(json_encode($obj), true);
	
	file_put_contents('./logs/res_parks.txt', print_r($data,TRUE),FILE_APPEND);
	
	$ordersn = $data['Message']['Plain']['OutTradeNo'];//订单编号
	$TradeNo = $data['Message']['Plain']['TradeNo'];//上级订单编号
	$CarNo = $data['Message']['Plain']['CarNo'];//车牌编号
	$payMoney = ($data['Message']['Plain']['PayAmount']/100);//支付交易金额
	
	//支付时间
	$payTime = strtotime($data['Message']['Plain']['OutTime']);
	
	//设置数据缓存；
	$cacheArr['ordersn'] = $ordersn;
	$key = 'Park_ID';
	
	//不为空，取缓存数据；判断数据一致则直接退出
	if(!empty($cache = cache_load($key)) && ($cache['ordersn'] == $ordersn) )
	{
//		echo 'SUCCESS';
		exit('SUCCESS');
	}
	//写入缓存
	cache_write($key,$cacheArr);
	
	//查询公众号ID 与用户ID
	$filed = 'a.uniacid,a.pay_account,a.total,a.ordersn,a.create_time,a.address,a.user_id,a.body,b.OthSeq,b.starttime,b.endtime,b.duration ';
	$find = array(':ordersn' => $ordersn, ':pay_status' => 0);
	
	$user = pdo_fetch("SELECT ".$filed." FROM ".tablename('foll_order')." a LEFT JOIN ".tablename('parking_order')." b ON a.ordersn = b.ordersn WHERE a.ordersn = :ordersn AND a.pay_status = :pay_status ORDER BY a.id desc LIMIT 1",$find);
	
//	$CardNo = pdo_get('parking_authorize',array('unique_id'=>$user['user_id']),array('credit_accout'));
	
	//计算停车时长返回数组形式
	//$T = timediff($user['starttime'],$user['endtime']);
	 
	//sendMassages  配置消息发送数据
	$sendArr = array(
		'body' 			=> $user['body'],//商品描述
		'paytime' 		=> date('Y-m-d H:i:s',time()),//消费时间
		'touser' 		=> $user['user_id'],//接收消息的用户
		'uniacid' 		=> $user['uniacid'],//公众号ID
		'parkTime' 		=> ceil(($user['endtime']-$user['starttime'])/60),//$T['day'].'天'.$T['hour'].'小时'.$T['min'].'分钟',//停车时长		
//		'realTime' => sprintf('%0.1f',$user['duration']/60),//实计时长
		'realTime' 		=> $user['duration'],//实计时长	分钟	
		'payableMoney'  => $user['total'],//应付金额
		'deducMoney' 	=> abs(sprintf('%.2f',($user['total'] - $payMoney))),//抵扣金额
		'payMoney' 		=> $payMoney,//$user['pay_account'],//交易金额  实付金额
	);
	
	$status = 'FAIL';
	
	try {
		pdo_begin();//开启事务
		
			if ($data['Message']['Plain']['PayStatus'] == 1) {//支付成功
				//是否接收到回调  SUCCESS表示成功
				//支付成功修改status 状态为1  pay_old支付交易表
				pdo_update('pay_old', array('status' => 1,'update_time'=>$payTime,'upOrderId'=>$TradeNo), array('ordersn' => $ordersn));
				//支付成功修改status 状态为1  pay_order订单表
				pdo_update('pay_order', array('status' => 1,'upOrderId'=>$TradeNo), array('ordersn' => $ordersn));
				//支付成功修改parking_order 表中状态：支付成功
				pdo_update('parking_order', array('upOrderId'=>$TradeNo,'CarNo'=>$CarNo,'status'=>'已结算'), array('ordersn' => $ordersn));
				pdo_update('foll_order', array('pay_status' => 1,'upOrderId'=>$TradeNo,'pay_time'=>$payTime,'pay_account'=>$payMoney), array('ordersn' => $ordersn));
				
				$status = 'SUCCESS';
				$sendArr['first']  = '您好，您的停车服务费扣费成功！';
				$sendArr['remark'] = '欢迎您再次使用智能无感路内停车服务！';

				//缴费成功返回数据
				postCredit($ordersn);
				
				//缴费成功模板
				//sendMsgSuccess($sendArr);
				sendSuccesTempl($sendArr);//支付成功模板
				
			} else {//支付失败
	
				//支付成功修改status 状态为2  pay_old支付交易表
				pdo_update('pay_old', array('status' => 2,'update_time'=>time(),'upOrderId'=>$TradeNo), array('ordersn' => $ordersn));
				//支付成功修改status 状态为2  pay_order订单表
				pdo_update('pay_order', array('status' => 2), array('ordersn' => $ordersn));
				//支付成功修改parking_order 表中状态：支付失败
//				pdo_update('parking_order', array('pay_status' => 2,'paytime'=>time(),'status'=>'待缴费'), array('ordersn' => $ordersn));
				pdo_update('parking_order', array('upOrderId'=>$TradeNo,'status'=>'未结算'), array('ordersn' => $ordersn));
				pdo_update('foll_order', array('pay_status' => 2,'upOrderId'=>$TradeNo,'pay_time'=>time(),'pay_account'=>$payMoney), array('ordersn' => $ordersn));
				
				$status = 'FAIL';
				
				$sendArr['first'] = '抱歉，您的停车服务费扣费失败！';
				$sendArr['remark'] = '请点击详情，继续完成支付！';
				$sendArr['Reurl'] = 'http://shop.gogo198.cn/app/index.php?i='.$user['uniacid'].'&c=entry&m=ewei_shopv2&do=mobile&r=parking.pay';
				//停车扣收失败！
//				sendMessagess($sendArr);
				//缴费成功模板
//				sendMsgSuccess($sendArr);
				//sendErrorTempl($sendArr);//支付失败发送模板
			}
		
		pdo_commit();//提交事务
		
	}catch(PDOException $e) {
		pdo_rollback();//执行失败，事务回滚
	}

	//echo $status;
	exit($status);
	
} else {
	exit('FAIL');
}


function postCredit($ordersn) 
{
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


/**返回参数
 * Array
(
    [Message] => Array
        (
            [Plain] => Array
                (
                    [TransId] => 60601
                    [ParkCode] => 1000000000000007
                    [MerDate] => 20180515
                    [OutTradeNo] => G99198101570223660201805158569
                    [TradeNo] => 20180515040000229965
                    [CarNo] => 粤YGB098
                    [InTime] => 20180515114459
                    [OutTime] => 20180515134459
                    [Amount] => 1000
                    [PayAmount] => 1000
                    [CheckDate] => 20180515
                    [PayStatus] => 1
                )

            [Signature] => Array
                (
                    [SignatureValue] => xMM4tK1_15eOdRN5qlzi_a9ABZY
                )

        )

)
 */
?>