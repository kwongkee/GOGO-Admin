<?php
	/**
	 * 微信免密扣费成功回调
	 */
	define('IN_MOBILE', true);
	require_once '../../framework/bootstrap.inc.php';
	require_once '../../app/common/bootstrap.app.inc.php';
	load()->app('common');
	load()->app('template');
	load()->func('diysend');
	date_default_timezone_set('Asia/Shanghai');

	//http://shop.gogo198.cn/payment/Frx/notify.php 回调地址；
	$urlStr = file_get_contents('php://input');
	//parse_str 把amt=1&ordernum  转换陈数组；
	parse_str($urlStr,$DataInfo);
	
	if(!empty($DataInfo))
	{
	    $tmps = json_encode($DataInfo);

        file_put_contents('./log/urlStr'.date('Ym',time()).'.txt', $tmps."\r\n",FILE_APPEND);



        $data = [
            'ordersn'=>$DataInfo['streamNo'],
            'inTime' =>date('Y-m-d H:i:s',time())
        ];
        $tmpd = json_encode($data);

        file_put_contents('./log/Received'.date('Ym',time()).'.txt', $tmpd."\r\n",FILE_APPEND);


        /**
		 *  [amt] => 1	退款金额（以分为单位，没有小数点）
		    [ordernum] => 439457294502068224	原订单号
		    [requesttype] => 1	1支付5退款6撤销
		    [sign] => 6fdd789c4022177bde4a5c697ec0f69b
		    [streamNo] => G99198101570223660201804278914	开发者流水号
		    [trade_time] => 20180427160623	交易时间
		    [tradestate] => 1	订单状态: 1支付成功7退款完成
			
			
			amt         =250
			createtime  =Mon Oct 22 15:39:16 CST 2018
			merchantno  =000201507100239351
			ordernum        =503955507514441728
			patType     =WECHAT
			requesttype =1
			sign         =f6b1cbcdc0d4d2a5befa154ff03464ac
			streamNo    =G9919820181022328646702
			trade_time  =20181022153921
			tradestate  =1
			upOrderNum  =4200000210201810222123398932
			
		 */

		//file_put_contents('./log/notify.txt',print_r($DataInfo,TRUE),FILE_APPEND);
		
		//订单编号；系统订单号
		$ordersn 	= $DataInfo['streamNo'];
		//上级订单号；
		$upOrderId  = $DataInfo['ordernum'];
		//支付成功时间
		$payDate = strtotime($DataInfo['trade_time']);
				
		$key = 'Frx_ID';
		//不为空，取缓存数据；判断数据一致则直接退出
		if(!empty($cache = cache_load($key)) && ($cache['ordersn'] == $ordersn) )
		{
            $data = [
                'ordersn'=>$DataInfo['streamNo'],
                'inTime' =>date('Y-m-d H:i:s',time())
            ];
            $tmpd = json_encode($data);

            file_put_contents('./log/Received_No'.date('Ym',time()).'.txt', $tmpd."\r\n",FILE_APPEND);

			exit('notify_success');
			
		} else {
			//设置数据缓存；
			$cacheArr['ordersn'] = $ordersn;
			//写入缓存
			cache_write($key,$cacheArr);
		}
		
		$filed = 'a.uniacid,a.pay_account,a.total,a.ordersn,a.body,a.user_id,b.starttime,b.endtime,b.duration ';
		$find = array(':ordersn' => $ordersn,':pay_status' => 0,':paystatus' => 2);
		$user = pdo_fetch("SELECT ".$filed." FROM ".tablename('foll_order')." a LEFT JOIN ".tablename('parking_order')." b ON a.ordersn = b.ordersn WHERE a.ordersn = :ordersn AND (a.pay_status = :pay_status OR a.pay_status = :paystatus) LIMIT 1",$find);

		$utms = json_encode($user);
		file_put_contents('./log/user'.date('Ym',time()).'.txt',$utms."\r\n",FILE_APPEND);
		
	 	//计算停车时长返回数组形式
	 	//$T      = timediff($user['starttime'],$user['endtime']);
	 	$money  = sprintf("%.2f",($DataInfo['amt']/100));
		$dikou = ($user['total']-$money)*1;
		$dikou = $dikou<=0 ? 0 : $dikou;
		//sendMassages  配置消息发送数据
		$sendArr = array(
			'body' 			=> $user['body'],//商品描述		a
			'paytime' 		=> date('Y-m-d H:i:s',time()),//消费时间
			'touser' 		=> $user['user_id'],//接收消息的用户	a
			'uniacid' 		=> $user['uniacid'],//公众号ID	a
			'parkTime' 		=> ceil(($user['endtime']-$user['starttime'])/60),//$user['duration'],//$T['day'].'天'.$T['hour'].'小时'.$T['min'].'分钟',//停车时长		
			'realTime' 		=> $user['duration'],//实计时长		b
			'payableMoney' 	=> $user['total'],//应付金额	a
//			'deducMoney' => ($user['total']-$user['pay_account'])*1,//抵扣金额	a
			'deducMoney' 	=> abs(sprintf('%.2f',$dikou)),//抵扣金额	a
			'payMoney' 		=> $money,//交易金额  实付金额	a
		);

		//file_put_contents('./log/sendArr.txt', print_r($sendArr,TRUE),FILE_APPEND);
		//支付成功
		if($DataInfo['tradestate'] == 1 && $DataInfo['requesttype'] == 1) {
			try{
				
				pdo_begin();//开启事务
				
					pdo_update('foll_order',array('pay_status'=>1,'upOrderId'=>$upOrderId,'pay_time'=> $payDate,'pay_account'=>$money),array('ordersn'=>$ordersn,'pay_type'=>'Fwechat'));
					
					pdo_update('parking_order',array('upOrderId'=>$upOrderId,'status'=>'已结算'),array('ordersn'=>$ordersn));
					
					pdo_update('pay_old',array('upOrderId'=>$upOrderId,'status'=>1,'payMoney'=>$money,'update_time'=>$payDate),array('ordersn'=>$ordersn,'payType'=>'Fwechat'));
				
				pdo_commit();//提交事务
				
			}catch(PDOException $e) {

			    $msg = $e->getMessage().'==>'.$e->getLine().'==>'.$e->getFile();

                file_put_contents('./log/Received_Error'.date('Ym',time()).'.txt', $msg."\r\n",FILE_APPEND);


                pdo_rollback();//事务回滚
			}
			
			$sendArr['first'] = '您好，您的停车服务费扣费成功！';
			$sendArr['remark'] = '欢迎您再次使用智能无感路内停车服务！';
			//发送扣费成功通知
			postCredit($ordersn);
			//支付成功发送消息
			//sendMsgSuccess($sendArr);

            // 2019-06-28
			sendSuccesTempl($sendArr);//支付成功模板
			
		} else {
			
			try{
				
				pdo_begin();//开启事务
				
					pdo_update('foll_order',array('pay_status'=>2,'upOrderId'=>$upOrderId,'pay_time'=>$payDate,'pay_account'=>$money),array('ordersn'=>$ordersn,'pay_type'=>'Fwechat'));
					
					pdo_update('parking_order',array('upOrderId'=>$upOrderId,'status'=>'未结算'),array('ordersn'=>$ordersn));
					
					pdo_update('pay_old',array('upOrderId'=>$upOrderId,'status'=>2,'payMoney'=>$money,'update_time'=>$payDate),array('ordersn'=>$ordersn,'payType'=>'Fwechat'));
					
				pdo_commit();//提交事务
				
			}catch(PDOException $e) {

                $msg = $e->getMessage().'==>'.$e->getLine().'==>'.$e->getFile();

                file_put_contents('./log/Received_Error'.date('Ym',time()).'.txt', $msg."\r\n",FILE_APPEND);

				pdo_rollback();//事务回滚
			}
			
			$sendArr['first'] = '您好，您的停车服务费扣费失败！';
			$sendArr['remark'] = '请点击详情，完成支付！';
			$sendArr['Reurl'] = 'http://shop.gogo198.cn/app/index.php?i='.$user['uniacid'].'&c=entry&m=ewei_shopv2&do=mobile&r=parking.pay';//跳转链接
			//sendMsgError($sendArr);//支付失败模板消息

            // 2019-06-28
            sendErrorTempl($sendArr);//支付失败模板
		}

        $data = [
            'ordersn'=>$DataInfo['streamNo'],
            'outTime' =>date('Y-m-d H:i:s',time())
        ];
        $tmpds = json_encode($data);

        file_put_contents('./log/Received'.date('Ym',time()).'.txt', $tmpds."\r\n",FILE_APPEND);

		echo 'notify_success';


	} else {
		echo 'notify_error';
	}
	
	
	//请求成返回数据   给阿新
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
?>