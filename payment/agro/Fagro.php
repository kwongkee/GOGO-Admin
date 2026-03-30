<?php
	/* 可用的地址/协议 	socket_client()
	 * @param domain: af_inet
	 * 1、AF_INET:IPv4 网络协议。TCP 和 UDP 都可使用此协议。
	 * 2、AF_INET6:IPv6 网络协议。TCP 和 UDP 都可使用此协议。
	 * 3、AF_UNIX : 本地通讯协议。具有高性能和低成本的 IPC（进程间通讯）。
	 * 可用的套接字类型
	 * @param type:
	 * 1、SOCK_STREAM =》 提供一个顺序化的、可靠的、全双工的、基于连接的字节流。支持数据传送流量控制机制。TCP 协议即基于这种流式套接字。
	 * 2、SOCK_DGRAM =》 提供数据报文的支持。(无连接，不可靠、固定最大长度).UDP协议即基于这种数据报文套接字。
	 * 3、SOCK_SEQPACKET =》 提供一个顺序化的、可靠的、全双工的、面向连接的、固定最大长度的数据通信；数据端通过接收每一个数据段来读取整个数据包。
	 * 4、SOCK_RAW =》 提供读取原始的网络协议。这种特殊的套接字可用于手工构建任意类型的协议。一般使用这个套接字来实现 ICMP 请求（例如 ping）。
	 * 5、SOCK_RDM =》 提供一个可靠的数据层，但不保证到达顺序。一般的操作系统都未实现此功能。
	 * 
	 * @param protocol:
	 * 1、icmp =》 Internet Control Message Protocol 主要用于网关和主机报告错误的数据通信。
	 * 2、udp =》 User Datagram Protocol 是一个无连接的、不可靠的、具有固定最大长度的报文协议。
	 * 3、tcp => Transmission Control Protocol 是一个可靠的、基于连接的、面向数据流的全双工协议。
	 * 如果所需的协议是 TCP 或 UDP，可以直接使用常量 SOL_TCP 和 SOL_UDP 。
	 */
	header( 'Content-Type:text/html;charset=utf-8 ');
	define('IN_MOBILE', true);
	define('PDO_DEBUG', true);
	require_once '../../framework/bootstrap.inc.php';
	require_once '../../app/common/bootstrap.app.inc.php';
	load()->app('common');
	load()->app('template');
	load()->func('diysend');
	global $_W;
	global $_GPC;
	
	/**
	 * 开发步骤：
	 * 1、接收传过来的参数；
	 * 2、处理传过来的数据
	 * 3、根据token,分流
	 * 4、返回处理结果；
	 */

	if(!empty($_POST) && isset($_POST['Token']))
	{
		//单例模式 实例化；
		$first = Fagro::getInstance();
		
		$address 	= '192.168.251.10';//农商行IP地址    平台：172.18.68.73 80
		$port 		= '19031';//= '39031';
		$BkEntrNo   = '04000000050';//单位编号 11	char
		$BkAcctNo 	= '801101000927634235';//'801101000974338949';//委托单位账号	32  char 801101000588712434	  801101000961488367
		$BkType1 	= '114021';//中间业务代码	6	char
		
		file_put_contents('./log/getPost.txt', print_r($_POST,TRUE)."\r\n",FILE_APPEND);
		$Token 		= trim($_POST['Token']);
		$SendData 	= $_POST;
		
		//解约、扣费、冲销、查询、单笔对账文件；
		$type = ['Surrender','FeeDeduction','WriteOff','Query','Reconciliation'];
		if(in_array($Token,$type)) {
			
			$Phone = trim($SendData['Phone']);//手机号码(用户唯一编号与签约时一致)、注册会员
			$CarNo = trim($SendData['CardNo']);//注册签约会员银行卡号
			$OrderSn = trim($SendData['OrderSn']);//订单编号
			
			//用于查询订单 Query
			$OldSeq = trim($SendData['OldSeq']);//发起方流水 20 
			$OldDate = trim($SendData['OldDate']);//发起方日期 8 接收时间搓
			
			//用于冲销
			$PlatDate = trim($SendData['PlatDate']);//原银行日期	 8
			$BkOldSeq = trim($SendData['BkOldSeq']);//原银行流水号  12 Char
			
			switch($Token) {
				case 'FeeDeduction'://扣费  100015
					/**
					 * 根据订单编号获取foll_order,parking_order 表中的
					 * 发起方流水号，交易金额，停车位置，开始时间，结束时间；
					 */
					$filed = 'a.uniacid,a.pay_account,a.total,a.ordersn,a.create_time,a.address,a.user_id,a.body,b.OthSeq,b.starttime,b.endtime,b.duration ';
					$find = array(':ordersn' => $OrderSn, ':pay_status' => 0,':paystatus' => 2);
					$OrdData = pdo_fetch("SELECT ".$filed." FROM ".tablename('foll_order')." a LEFT JOIN ".tablename('parking_order')." b ON a.ordersn = b.ordersn WHERE a.ordersn = :ordersn AND (a.pay_status = :pay_status OR a.pay_status = :paystatus) ORDER BY a.id desc LIMIT 1",$find);
					//写入日志
					//file_put_contents('./log/OrdData.txt', print_r($OrdData,TRUE));
					//数据不为空时
					if(!empty($OrdData)) {
						
						//折扣计算部分；
						/*$m = 0;
						$isFree = false;
						$opera = pdo_getall('parking_operate',['uniacid'=>$OrdData['uniacid'],'status' => 2],['discount','startDate','endDate','status']);
						if(!empty($opera) && ($OrdData['total'] > 0))//1
						{
							$m = $OrdData['total'];//折扣率后的金额
							$j = '0.';//折扣率 例如：0.5
							$times = time();//当前时间搓
							foreach($opera as $key=>$val) {//2
								if(($times >= $val['startDate']) && ($times <= $val['endDate'])) {//3
									
									if($val['discount'] == '0'){
										//标志为true;
										$isFree = true;
										//交易金额等于总金额*0 = 0元;
										$m = 0;
									break;
									} else {
										//查找符合条件的折扣率 转换小数 * 待转换的折扣金额
										$m = ($m * (float)($j=$j.$val['discount']));
										break;
									}
								}
							}
							
							//为真时！折扣为0折  直接修改数据库中字段  发送信息；返回支付状态给咪表
							if($isFree) {
								
								//1  更新表状态；
								pdo_update('foll_order',['pay_account'=>$m,'pay_status' => 1,'pay_time'=>time()],['ordersn'=>$OrdData['ordersn']]);
								pdo_update('parking_order', array('status'=>'已结算'), array('ordersn' => $OrdData['ordersn']));
							
								/**
								 * 开发步骤：
								 * 1、修改表交易金额；
								 * 2、发送消息给用户
								 * 3、发送成功信息给设备
								 */
								//计算停车时长返回数组形式	2
								/*$T = timediff($OrdData['starttime'],$OrdData['endtime']);
								 
								//sendMassages  配置消息发送数据
								$sendArr = array(
									'body' 		=> $OrdData['body'],//商品描述		a
									'paytime' 	=> date('Y-m-d H:i:s',time()),//消费时间
									'touser' 	=> $OrdData['user_id'],//接收消息的用户	a
									'uniacid' 	=> $OrdData['uniacid'],//公众号ID	a
									'parkTime' 		=> $T['day'].'天'.$T['hour'].'小时'.$T['min'].'分钟',//停车时长		
									'realTime' 		=> $OrdData['duration'],//实计时长		b
									'payableMoney' 	=> sprintf("%.2f",$OrdData['total']),//应付金额	a
									'deducMoney' 	=> sprintf('%.2f',($OrdData['total']- $m)),//抵扣金额	a
									'payMoney' 		=> sprintf("%.2f",$m),//交易金额  实付金额	a
								);
								
								//发送成功数据
								postCredits($OrdData['ordersn']);
								
								//发送模板消息  
								$sendArr['first'] = '您好，您的停车服务费扣费成功！';
								$sendArr['remark'] = '欢迎您再次使用智能无感路内停车服务！';
								//  2018-07-04 16:06  消息发送失败，重复发送一次！
								$sendmsg['msg']  = $flag = sendMsgSuccess($sendArr);//支付成功发送消息
								if($flag!='success'){
									$sendmsg['msg1'] = sendMsgSuccess($sendArr);//支付成功发送消息
								}
								$sendmsg['time'] = date('Y-m-d H:i:s',time());
								file_put_contents('./log/sendmsg.txt', print_r($sendmsg,TRUE),FILE_APPEND);
								
								//模拟支付成功
								$res = json_encode(['status'=>101,'msg'=>'success']);
								echo $res;
								exit;
							}
							
						}
						//支付交易金额
						$m = $m > 0 ? sprintf("%.2f",$m):sprintf("%.2f",$OrdData['pay_account']);*/
						//折扣计算结束
						
						//实际交易金额
						$m = sprintf("%.2f",$OrdData['pay_account']);						
						$oldOrdersn = $OrdData['ordersn'];//查询出来的订单编号
						$data = [
							//服务名(交易码)  6  char
							'transaction'=>'100015',
							//单位编号 11	char
							'BkEntrNo' => $BkEntrNo,
							//委托单位账号	32  char
							'BkAcctNo' => $BkAcctNo,
							//中间业务代码	6	char
							'BkType1'  => $BkType1,
							//发起方日期	8   YYYYMMDD	char
							'BkOthDate' => date('Ymd',$OrdData['create_time']),//获取foll_order 表中 create_time 
							//发起方流水号	20 char		获取parking_order 表中的 OthSeq 发起方流水号；
							'BkOthSeq'  => $OrdData['OthSeq'],//发起方流水号
							
							/**
							 * 报文部分；
							 */
							//客户编号（发起方唯一健值）  30	char  请求报文
							'BkAcctNo1'	=> $Phone,//用户唯一编号与签约时一致
							//银行卡号	32	char
							'BkAcctNo2'	=> $CarNo,
							//交易金额	17	2 Double
							'BkTxAmt'	=> $m,//$OrdData['pay_account'],//获取foll_order 表中 pay_account 
							//订单编号  30 Char
							'BkAcctNo3'	=> $OrdData['ordersn'],	//获取foll_order 表中 ordersn
							//停车位置	98  Char  广东省佛山市顺德区伦教办事处常教社区居委会尚城路12号
							'BkAddr1'	=> $OrdData['address'],//'广东省佛山市顺德区伦教办事处常教社区居委会尚城路12号',//获取foll_order 表中 address
							//停车开始时间	14 yyyymmddhhmmss  Char
							'Bk19DateTime1'=> date('YmdHis',$OrdData['starttime']),//获取parking_order 表中 starttime
							//停车结束时间	14 yyyymmddhhmmss Char
							'Bk19DateTime2'=> date('YmdHis',$OrdData['endtime']),//获取parking_order 表中 endtime
							//预留1	64	char	32个中文
							'Bk255str1'	=> 'BaoWen',
							//预留2	128	char 64个中文
							'Bk255str2'	=> 'BaoWen',
						];
						
						/**
						 * 组装数据；
						 * 写入pay_old 表中，流水表；
						 */
						$inserData = [
							'payMoney'	=>	$m,//$OrdData['pay_account'],//交易金额
							'ordersn'	=>	$OrdData['ordersn'],//订单编号
							'payType'	=>	'FAgro',//顺德农商行免密支付；
							'body'		=>	$OrdData['body'],//订单描述
							'openid'	=>	$OrdData['user_id'],//用户ID
							'create_time'=>	$OrdData['create_time'],//创建时间
							'account'	=>	$BkAcctNo,//委托收款账号
							'upOrderId'	=>	$OrdData['OthSeq'],//发起方流水；
							//'SeqNo'//银行流水号；
						];
						//写入流水表；
						pdo_insert('pay_old',$inserData);
						
						/**
						 * 组装发送消息模板数据
						 */
						//计算停车时长返回数组形式
						//$T = timediff($OrdData['starttime'],$OrdData['endtime']);
						
						$dikou = (round($OrdData['total'],2) - $m);
						$sendArr = array(
							'body' 		=> $OrdData['body'],//商品描述
							'paytime' 	=> date('Y-m-d H:i:s',time()),//消费时间
							'touser' 	=> $OrdData['user_id'],//接收消息的用户
							'uniacid' 	=> $OrdData['uniacid'],//公众号ID
							'parkTime' 	=> ceil(($OrdData['endtime']-$OrdData['starttime'])/60),//$T['day'].'天'.$T['hour'].'小时'.$T['min'].'分钟',//停车时长
							'realTime' 	=> $OrdData['duration'],//实计时长	分钟	
							'payableMoney'	=> sprintf('%.2f',$OrdData['total']),//应付金额
							'deducMoney' 	=> abs(sprintf('%.2f',$dikou)), //($OrdData['total']- $OrdData['pay_account']),//抵扣金额
							'payMoney' 		=> sprintf('%.2f',$m),//$OrdData['pay_account'],//交易金额  实付金额
						);
						
						//处理扣费数据；
						$strs = $first->FeeDeduction($data);
						//发送报文，并获取请求响应；
						$res = $first->socket_client($address,$port,$strs);
						//file_put_contents('./log/Fee.txt', print_r($res,TRUE)."\r\n",FILE_APPEND);
						
						//解析返回数据；
						$Result = $first->Analysis($res);
						file_put_contents('./log/FeeResult1.txt', print_r($Result,TRUE)."\r\n",FILE_APPEND);
						
						$key = 'Frx_Fee';
						//不为空，取缓存数据；判断数据一致则直接退出
						if(!empty($cache = cache_load($key)) && ($cache['ordersn'] == $Result['OthSeq']) )
						{
							echo 'notify_success';
							die;
							
						} else {
							//设置数据缓存；
							$cacheArr['ordersn'] = $Result['OthSeq'];
							//写入缓存
							cache_write($key,$cacheArr);
						}
						//支付时间
						$payTime = strtotime(substr($Result['OthSeq'],0,14));
						//$payTime 可能有误差  调整为 当前时间   2018-10-08
						$payTime = time();
						
						//扣费成功
						if( ($Result['Result'] == '00000') && ($Result['Message'] == '成功完成') )
						{
							/**
							 * 开发步骤：
							 * 1、扣费成功，更新数据表的状态；
							 * 发送成功扣费通知
							 */
							try
							{
								pdo_begin();//开启事务
								
									pdo_update('pay_old',array('status' => 1,'update_time'=>$payTime,'SeqNo'=>$Result['SeqNo'],'Msg'=>$Result['Message'],'PlatDate'=>$Result['PlatDate']), array('ordersn'=>$oldOrdersn));
									//支付成功修改parking_order 表中状态：支付成功  SeqNo：银行流水；
									pdo_update('parking_order', array('status'=>'已结算','PlatDate'=>$Result['PlatDate']),array('OthSeq' => $Result['OthSeq']));
									
									pdo_update('foll_order', array('pay_status' => 1,'upOrderId'=>$Result['SeqNo'],'PlatDate'=>$Result['PlatDate'],'pay_time'=>$payTime,'pay_account'=>$m), array('ordersn' => $oldOrdersn));
				
								pdo_commit();//提交事务
							}catch(PDOException $e){
								pdo_rollback();//执行失败，事务回滚
							}

							$sendArr['first'] = '您好，您的停车服务费扣费成功！';
							$sendArr['remark'] = '欢迎您再次使用智能无感路内停车服务！';

							//2018-05-11 发送扣费成功信息
							$first->postCredit($oldOrdersn);
							
							$res=json_encode(['status'=>101,'msg'=>'success']);
							echo $res;
							
							//缴费成功模板
							//$msg = sendMsgSuccess($sendArr);
							$msg = sendSuccesTempl($sendArr);
							file_put_contents('./log/sendMsg.txt', print_r($msg,TRUE)."\r\n",FILE_APPEND);
							die;
							
						} else {
							
							//扣费失败；
							/**
							 * 开发步骤：
							 * 1、扣费失败，更新数据表的状态；
							 * 发送失败扣费通知
							 */
							try
							{
								pdo_begin();//开启事务
								
									pdo_update('pay_old', array('status' => 2,'update_time'=>$payTime,'SeqNo'=>$Result['SeqNo'],'Msg'=>$Result['Message']), array('ordersn'=>$oldOrdersn));
									//支付成功修改parking_order 表中状态：支付成功  SeqNo：银行流水；
									pdo_update('parking_order', array('status'=>'未结算','PlatDate'=>$Result['PlatDate']), array('OthSeq' => $Result['OthSeq']));
									
									pdo_update('foll_order', array('pay_status' => 2,'upOrderId'=>$Result['SeqNo'],'PlatDate'=>$Result['PlatDate'],'pay_time'=>$payTime,'pay_account'=>$m), array('ordersn' => $oldOrdersn));
				
								pdo_commit();//提交事务
							}catch(PDOException $e) {
								pdo_rollback();//执行失败，事务回滚
							}
							
							/*$sendArr['first'] = '抱歉，您的停车服务费扣费失败！';
							$sendArr['remark'] = '请点击详情，继续完成支付！';
							$sendArr['Reurl'] = 'http://shop.gogo198.cn/app/index.php?i='.$OrdData['uniacid'].'&c=entry&m=ewei_shopv2&do=mobile&r=parking.pay';
							
							echo json_encode(['status'=>0,'msg'=>'扣费失败']);
							//缴费成功模板
							sendMsgSuccess($sendArr);

							exit(json_encode(['status'=>102,'msg'=>'扣费失败']));
							echo json_encode(['status'=>102,'msg'=>'扣费失败']);
							die;
							return $res=['status'=>102,'msg'=>'扣费失败'];*/
							$res=json_encode(['status'=>102,'msg'=>'error']);
							echo $res;
							exit();
						}
						
					} else {
						$res=json_encode(['status'=>102,'msg'=>'error']);
						echo $res;
						exit();
					}
									
				break;
				
				case 'WriteOff'://冲销  100019
					//exit(json_encode(['code'=>0,'codes'=>1,'msg'=>'success','data'=>$_POST]));
					
					$filed = 'uniacid,pay_account,user_id';
					$find = array(':PlatDate' => $PlatDate, ':pay_status' => 1,':upOrderId' =>$BkOldSeq );
					$OrdData = pdo_fetch("SELECT ".$filed." FROM ".tablename('foll_order')."WHERE PlatDate = :PlatDate AND pay_status = :pay_status AND upOrderId = :upOrderId LIMIT 1",$find);
					
					$data = [
						//服务名(交易码)  6  char
						'transaction'=>'100019',
						//单位编号 11	char
						'BkEntrNo' 	=> $BkEntrNo,
						//委托单位账号	32  char
						'BkAcctNo' 	=> $BkAcctNo,
						//中间业务代码	6	char
						'BkType1' 	=> $BkType1,
						//发起方日期	8   YYYYMMDD	char
						'BkOthDate' => date('Ymd',time()),//time(),
						//发起方流水号	20 char
						'BkOthSeq' 	=> date('YmdHis',time()).mt_rand(111111,999999),
						
						//报文体；
						//原银行日期	 8	传时间格式：yyyymmdd
						'BkOldPlatDate'=> $PlatDate,
						//原银行流水号  12 Char
						'BkOldSeq'	=> $BkOldSeq,
						//预留1	64	char	32个中文
						'Bk255str1'	=> 'BaoWen',
						//预留2	128	char 64个中文
						'Bk255str2'	=> 'BaoWen',
					];
					$strs = $first->WriteOff($data);
					//发送报文，并获取请求响应；
					$res = $first->socket_client($address,$port,$strs);
					//返回结果；
					file_put_contents('./log/Writes.txt', print_r($res,TRUE)."\r\n",FILE_APPEND);
					
					$res = $first->Analysis($res);
					if($res['Result'] == 00000 && $res['Message'] == '成功完成')
					{	
						
						try{
							pdo_begin();//冲销成功更新数据
								//pdo_update('parking_order',array('IsWrite'=>1,'WriteSeq'=>$res['SeqNo']),array('upOrderId'=>$BkOldSeq));
								pdo_update('foll_order',array('IsWrite'=>100,'WriteSeq'=>$res['SeqNo']),array('upOrderId'=>$BkOldSeq));
							pdo_commit();
						}catch(PDOException $e){
							pdo_rollback();
						}
						
						//退款消息模板
						$sendArr = array(
							'first'=>'您好！退款已提交原路退还您的支付账号',
							'touser' 	 => $OrdData['user_id'],//接收消息的用户	a
							'uniacid' 	 => $OrdData['uniacid'],//公众号ID	a
							'RefundType' => '农商卡免密支付',//退款通道
							'RefundMoney'=> $OrdData['pay_account'],//退款金额
							'RefundDate' => date('Y-m-d H:i:s',time()),//退款提交时间
							'remark'	 =>'退款到账详情，请查看支付账户及咨询退款通道客服'
						);
						sendMsgRefund($sendArr);//退款消息模板发送
						
						//返回json 数据；
						$res['codes'] = 1;
						$res['msg']  = $res['Message'];
						echo json_encode($res);
						die;
					} else {
						
						//pdo_update('parking_order',array('IsWrite'=>102),array('upOrderId'=>$BkOldSeq));
						pdo_update('foll_order',array('IsWrite'=>102),array('upOrderId'=>$BkOldSeq));
						$res['codes'] = 0;
						$res['msg']  = $res['Message'];
						echo json_encode($res);
						die;
					}
				break;
				
				
				case 'Query'://查询  100020
					$data = [
						//服务名(交易码)  6  char
						'transaction'=>'100020',
						//单位编号 11	char
						'BkEntrNo' => $BkEntrNo,
						//委托单位账号	32  char
						'BkAcctNo' => $BkAcctNo,
						//中间业务代码	6	char
						'BkType1' => $BkType1,
						//发起方日期	8   YYYYMMDD	char
						'BkOthDate' => date('Ymd',time()),//time(),
						//发起方流水号	20 char
						'BkOthSeq' => date('YmdHis',time()).mt_rand(111111,999999),
						
						//报文体；
						//发起方日期	 8	传时间搓
						'BkOthOldDate'=>date('Ymd',$OldDate),
						//发起方流水号  20 Char
						'BkOthOldSeq'=> $OldSeq,
						//预留1	64	char	32个中文
						'Bk255str1'=> 'BaoWen',
						//预留2	128	char 64个中文
						'Bk255str2'=> 'BaoWen',
					];
					$strs = $first->Query($data);
					//发送报文，并获取请求响应；
					$res = $first->socket_client($address,$port,$strs);
					//返回结果；
					file_put_contents('./log/Quer.txt', print_r($res,TRUE)."\r\n",FILE_APPEND);

					$res = $first->Analysis($res);
					if($res['Result'] == 00000 && $res['Message'] == '成功完成')
					{	//返回json 数据；
						echo json_encode($res);
						die;
					} else {
						echo json_encode($res);
						die;
					}
					
				break;
				
				
				//解约	100014	
				case 'Surrender':
					$SurrData = [
						//服务名(交易码)  6  char
						'transaction'=>'100014',
						//单位编号 11	char
						'BkEntrNo' => $BkEntrNo,
						//委托单位账号	32  char
						'BkAcctNo' => $BkAcctNo,
						//中间业务代码	6	char
						'BkType1' => $BkType1,
						//发起方日期	8   YYYYMMDD	char
						'BkOthDate' => date('Ymd',time()),
						//发起方流水号	20 char
						'BkOthSeq' => date('YmdHis',time()).mt_rand(111111,999999),
						
						//客户编号（发起方唯一健值）  30	char  请求报文
						'BkAcctNo1'=> $Phone,
						//银行卡号	32	char
						'BkAcctNo2'=> $CarNo,
						//预留1	64	char	32个中文
						'Bk255str1'=> 'BaoWen',
						//预留2	128	char 64个中文
						'Bk255str2'=> 'BaoWen',
					];
					//数据转换GB2312  变成字符串
					$strs = $first->Surrender($SurrData);
					//发送报文，并获取请求响应；
					$res = $first->socket_client($address,$port,$strs);
					
					//返回结果；
					file_put_contents('./log/Surr1	.txt', print_r($res,TRUE)."\r\n",FILE_APPEND);
					$Res = $first->Analysis($res);
					file_put_contents('./log/SurrResult1.txt', print_r($Res,TRUE));
//					if($Res['Result'] == '00000' && $Res['Message'] == '成功完成'){
//					if($Res['Result'] == '10000') {
					//返回json 数据；
					echo json_encode($Res);
//					die;
//					} else {
//						echo json_encode(['status'=>0,'msg'=>'error']);
//					}
					
				break;
				
				case 'Reconciliation'://单笔对账文件   100018
					$SurrData = [
						//服务名(交易码)  6  char
						'transaction'=>'100018',
						//单位编号 11	char
						'BkEntrNo' => $BkEntrNo,
						//委托单位账号	32  char
						'BkAcctNo' => $BkAcctNo,
						//中间业务代码	6	char
						'BkType1' => $BkType1,
						//发起方日期	8   YYYYMMDD	char
						'BkOthDate' => date('Ymd',time()),
						//发起方流水号	20 char
						'BkOthSeq' => date('YmdHis',time()).mt_rand(111111,999999),
						
						//实际交易日期(不能是当天) char 8  请求报文
						'Bk8Date1'=> date('Ymd',$OldDate),
						//预留1	64	char	32个中文
						'Bk255str1'=> 'BaoWen',
						//预留2	128	char 64个中文
						'Bk255str2'=> 'BaoWen',
					];
					//数据转换GB2312  变成字符串
					$strs = $first->Reconciliation($SurrData);
					//发送报文，并获取请求响应；
					$res = $first->socket_client($address,$port,$strs);
					//返回结果
					$day = date("Y-m-d",time());
					file_put_contents('./log/Recon'.$day.'.txt', print_r($res,TRUE)."\r\n");
					
					//file_put_contents('../../crontab/sd/Recon'.date('Ymd',$OldDate).'.txt',$res."\r\n",FILE_APPEND);

					$Res = $first->Analysis($res);
//					if($res['Result'] == 00000 && $res['Message'] == '成功完成')
//					{	//返回json 数据；
//						echo json_encode($res);
//					} else {
						echo json_encode($Res);
//						die;
//					}
				break;
			}
			
		} else {
			echo json_encode(['status'=>102,'msg'=>'无此类型!']);
		}		
	} else {
		echo json_encode(['status'=>102,'msg'=>'请求类型不正确!']);
	}
	
	
/**
 * 折扣计算
 * @param  $params  传入参数;
 * 折扣为0 直接处理成功
 * 否则返回交易金额;
 */
function FeeOk($params) {
	//折扣计算部分；
	$m = 0;
	$isFree = false;
	$opera = pdo_getall('parking_operate',['uniacid'=>$params['uniacid'],'status' => 2],['discount','startDate','endDate','status']);
	if(!empty($opera) && ($params['total'] > 0) && ($opera))//1
	{
		$m = $params['total'];//折扣率后的金额
		$j = '0.';//折扣率 例如：0.5
		$times = time();//当前时间搓
		foreach($opera as $key=>$val) {//2
			if(($times >= $val['startDate']) && ($times <= $val['endDate'])) {//3
				
				if($val['discount'] == '0'){
					//标志为true;
					$isFree = true;
					//交易金额等于总金额*0 = 0元;
					$m = 0;
				break;
				} else {
					//查找符合条件的折扣率 转换小数 * 待转换的折扣金额
					$m = ($m * (float)($j=$j.$val['discount']));
					break;
				}
			}
		}
		
		//1  更新表状态；
		pdo_update('foll_order',['pay_account'=>$m,'pay_status' => 1,'pay_time'=>time()],['ordersn'=>$params['ordersn']]);
		pdo_update('parking_order', array('status'=>'已结算'), array('ordersn' => $params['ordersn']));
		
		//为真时！折扣为0折  直接修改数据库中字段  发送信息；返回支付状态给咪表
		if($isFree) {
			/**
			 * 开发步骤：
			 * 1、修改表交易金额；
			 * 2、发送消息给用户
			 * 3、发送成功信息给设备
			 */
			//计算停车时长返回数组形式	2
			$T = timediff($params['starttime'],$params['endtime']);
			 
			//sendMassages  配置消息发送数据
			$sendArr = array(
				'body' 		=> $params['body'],//商品描述		a
				'paytime' 	=> date('Y-m-d H:i:s',time()),//消费时间
				'touser' 	=> $params['user_id'],//接收消息的用户	a
				'uniacid' 	=> $params['uniacid'],//公众号ID	a
				'parkTime' 		=> $T['day'].'天'.$T['hour'].'小时'.$T['min'].'分钟',//停车时长		
				'realTime' 		=> $params['duration'],//实计时长		b
				'payableMoney' 	=> $params['total'],//应付金额	a
				'deducMoney' 	=> sprintf('%.2f',($parms['total']- $m)),//抵扣金额	a
				'payMoney' 		=> $m,//交易金额  实付金额	a
			);
			
			//发送成功数据
			postCredits($parms['ordersn']);
			
			//发送模板消息
			$sendArr['first'] = '您好，您的停车服务费扣费成功！';
			$sendArr['remark'] = '欢迎您再次使用智能无感路内停车服务！';
			
			sendMsgSuccess($sendArr);//支付成功发送消息
			
			//模拟支付成功
			$res=json_encode(['status'=>101,'msg'=>'success']);
			echo $res;
			die;
		}
		
	}
	//支付交易金额
	return $m = $m > 0 ? (round($m,2)*100):(round($params['pay_account'],2)*100);
	//折扣计算结束
}
	
	//交易成功发送信息
function postCredits($ordersn) 
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
	
	

	//农商行  支付授权类
	class Fagro
	{
		//保存类的实例的静态成员变量
		static private $_instance = null;
		//私有的构造方法
		private function __construct(){}
		
		//用于访问类的实例的公共静态方法
		static public function getInstance()
		{
			if(!self::$_instance instanceof Fagro){
				//	'实例化<br>';
				self::$_instance = new self;
			}
			return self::$_instance;
		}
		
		/** 请求数据
		 * scoket  链接
		 * @param address;链接地址
		 * @param port; 端口号
		 * @param data; 发送的数据
		 */
		public function socket_client($address='127.0.0.1',$service_port = 10005,$data = '')
		{			
			//创建并返回一个套接字（通讯节点）
			$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
			if ($socket === false) {
				return "CREATE_ERROR:".socket_strerror(socket_last_error())."\n";
			}
			// 发起socket连接请求
			$result = socket_connect($socket, $address, $service_port);
			if($result === false) {
				return "ERROR: ".socket_strerror(socket_last_error($socket))."\n";
			}
			if($data != '') {
				// 向socket服务器发送消息
				socket_write($socket, $data, strlen($data));
				// 读取socket服务器发送的消息
				while ($out = socket_read($socket, strlen($data)))
				{
					return iconv("GBK", "UTF-8", $out);
				}
			} else {
				return 'Data is Null';
			}
			// 关闭socket连接
			socket_close($socket);
		}
		
		
		//扣费请求 2018-04-07  单笔扣费 100015
		public function FeeDeduction($SurrData = [])
		{
			//扣费请求报文头  PFMT_ YGK_SVR_IN
			$data = [
				//服务名(交易码)  6  char
				'__GDTA_SVCNAME' =>	$SurrData['transaction'],// '100015',
				//单位编号 11	char
				'BkEntrNo' => $SurrData['BkEntrNo'],//'04000000050',
				//委托单位账号	32  char
				'BkAcctNo' => $this->getStrlen($SurrData['BkAcctNo'],32),
				//中间业务代码	6	char
				'BkType1'  => $SurrData['BkType1'],//'114021',				
				//发起方日期	8   YYYYMMDD	char
				'BkOthDate' => $SurrData['BkOthDate'],
				//发起方流水号	20 char
				'BkOthSeq' => $this->getStrlen($SurrData['BkOthSeq'],20),
				
				//  报文部分
				//客户编号（发起方唯一健值）  30 Char
				'BkAcctNo1'	=> $this->getStrlen($SurrData['BkAcctNo1'],30),
				//银行卡号	32	Char
				'BkAcctNo2'	=> $this->getStrlen($SurrData['BkAcctNo2'],32),
				//交易金额	17	2 Double
				'BkTxAmt'	=> $this->getIntlen($SurrData['BkTxAmt'],17),
				//订单编号  30 Char 30位订单编号
				'BkAcctNo3'	=> $this->getStrlen($SurrData['BkAcctNo3'],30),
				//停车位置	98  Char
				'BkAddr1'	=> $this->getStrlen($SurrData['BkAddr1'],98),//返回长度为98位的字符串；
				//停车开始时间	14 yyyymmddhhmmss  Char
				'Bk19DateTime1'=> $SurrData['Bk19DateTime1'],
				//停车结束时间	14 yyyymmddhhmmss Char
				'Bk19DateTime2'=> $SurrData['Bk19DateTime2'],
				'Bk255str1'	=> $this->getStrlen($SurrData['Bk255str1'],64),//预留1	64
				'Bk255str2'	=> $this->getStrlen($SurrData['Bk255str2'],128),//预留2	128
			];
			$str = implode('',$data);
			// 位数：int  8  获取报文长度；
			$lens = $this->getBaoWen($str,8);//拼接字符串长度；
			$len = [
				'__GDTA_ITEMDATA_LENGTH' => $lens,
			];
			//数组合并；
			$datas = array_merge($len,$data);
			file_put_contents('./log/FeeDeduction1.txt', print_r($datas,TRUE),FILE_APPEND);
			//数组转字符串；以空格分割
			$str1 = implode('',$datas);
			//数据转GB2312
			$str2 = $this->charsetToGB($str1,'GB2312');
			return $str2;
		}
		
		
		/**
		 * 2018-04-07
		 * 2.1 （交易码：100014）单笔解约
		 */
		public function Surrender($SurrData = [])
		{
			/**
			 * 单笔解约请求报文头  PFMT_ YGK_SVR_IN
			 * Int  左补0
			 * char 右补0
			 * 00000000	长度
			 * 100014	交易码
			 * 20180619	发起方日期
			 *  1030279
			 * 20180412
			 * 20180412
			 * 09593247472710000 无该协议记录
			 */
			$data = [				 
				//服务名(交易码)  6  char
				'__GDTA_SVCNAME' =>	$SurrData['transaction'],// '100014',
				//单位编号 11	char
				'BkEntrNo' => $SurrData['BkEntrNo'],//'04000000050',
				//委托单位账号	32  char
				'BkAcctNo' =>  $this->getStrlen($SurrData['BkAcctNo'],32),
				//中间业务代码	6	char
				'BkType1' => $SurrData['BkType1'],//'114021',				
				//发起方日期	8   YYYYMMDD	char
				'BkOthDate' => $SurrData['BkOthDate'],
				//发起方流水号	20 char
				'BkOthSeq' => $this->getStrlen($SurrData['BkOthSeq'],20),
				
				//客户编号（发起方唯一健值）  30	char  请求报文  （交易码：100014）单笔解约
				'BkAcctNo1'=> $this->getStrlen($SurrData['BkAcctNo1'],30),
				//银行卡号	32	char
				'BkAcctNo2'=> $this->getStrlen($SurrData['BkAcctNo2'],32),
				//预留1	64	char	32个中文
				'Bk255str1' => $this->getStrlen($SurrData['Bk255str1'],64),
				//预留2	128	char 64个中文
				'Bk255str2' => $this->getStrlen($SurrData['Bk255str2'],128),
			];

			$str = implode('',$data);
			// 位数：int  8  获取报文长度；
			$lens = $this->getBaoWen($str,8);//拼接字符串长度；
			$len = ['__GDTA_ITEMDATA_LENGTH' => $lens,];
			$datas = array_merge($len,$data);
			file_put_contents('./log/Surrender.txt', print_r($datas,TRUE),FILE_APPEND);
			//数组转字符串；以空格分割
			$str1 = implode('',$datas);
			//数据转GB2312
			$str2 = $this->charsetToGB($str1,"GB2312");
			return $str2;
		}
		
		
		/**
		 * 2018-04-07
		 *	（交易码：100020）单笔扣费查询
		 */
		public function Query($SurrData = [])
		{
			//扣费请求报文头  PFMT_ YGK_SVR_IN
			$data = [				 
				//服务名(交易码)  6  char
				'__GDTA_SVCNAME' =>	$SurrData['transaction'],// '100020',
				//单位编号 11	char
				'BkEntrNo' => $SurrData['BkEntrNo'],//'04000000050',
				//委托单位账号	32  char
				'BkAcctNo' => $this->getStrlen($SurrData['BkAcctNo'],32),
				//中间业务代码	6	char
				'BkType1' => $SurrData['BkType1'],//'11402
				//发起方日期	8   YYYYMMDD	char
				'BkOthDate' => $SurrData['BkOthDate'],
				//发起方流水号	20 char
				'BkOthSeq' => $this->getStrlen($SurrData['BkOthSeq'],20),
				//发起方日期8	char 
				'BkOthOldDate'=> $SurrData['BkOthOldDate'],
				//发起方流水号	20	char
				'BkOthOldSeq'=> $this->getStrlen($SurrData['BkOthOldSeq'],20),
				//预留1	64	char	32个中文
				'Bk255str1' => $this->getStrlen($SurrData['Bk255str1'],64),
				//预留2	128	char 64个中文
				'Bk255str2' => $this->getStrlen($SurrData['Bk255str2'],128),
			];

			$str = implode('',$data);
			// 位数：int  8  获取报文长度；
			$lens = $this->getBaoWen($str,8);//拼接字符串长度；
			$len = ['__GDTA_ITEMDATA_LENGTH' => $lens];			
			$datas = array_merge($len,$data);
			file_put_contents('./log/Query.txt', print_r($datas,TRUE),FILE_APPEND);
			//数组转字符串；以空格分割
			$str1 = implode('',$datas);
			//数据转GB2312
			$str2 = $this->charsetToGB($str1,"GB2312");
			return $str2;		
		}
		
		
		/**
		 * 2018-04-17
		 *	（交易码：100019）单笔扣费冲销 （只能冲当天）
		 */
		public function WriteOff($SurrData = [])
		{
			//扣费请求报文头  PFMT_ YGK_SVR_IN
			$data = [				 
				//服务名(交易码)  6  char
				'__GDTA_SVCNAME' =>	$SurrData['transaction'],// '100019',
				//单位编号 11	char
				'BkEntrNo' => $SurrData['BkEntrNo'],//'04000000050',
				//委托单位账号	32  char
				'BkAcctNo' => $this->getStrlen($SurrData['BkAcctNo'],32),
				//中间业务代码	6	char
				'BkType1' => $SurrData['BkType1'],//'11402
				//发起方日期	8   YYYYMMDD	char
				'BkOthDate' => $SurrData['BkOthDate'],
				//发起方流水号	20 char
				'BkOthSeq' => $this->getStrlen($SurrData['BkOthSeq'],20),
				
				//原银行日期	8	char 
				'BkOldPlatDate'=> $SurrData['BkOldPlatDate'],
				//原银行流水号	12	long
				'BkOldSeq'=> $this->getIntlen($SurrData['BkOldSeq'],12),
				//预留1	64	char	32个中文
				'Bk255str1' => $this->getStrlen($SurrData['Bk255str1'],64),
				//预留2	128	char 64个中文
				'Bk255str2' => $this->getStrlen($SurrData['Bk255str2'],128),
			];
			
			$str = implode('',$data);
			// 位数：int  8  获取报文长度；
			$lens = $this->getBaoWen($str,8);//拼接字符串长度；
			$len = ['__GDTA_ITEMDATA_LENGTH' => $lens];			
			$datas = array_merge($len,$data);
			file_put_contents('./log/WriteOff.txt', print_r($datas,TRUE),FILE_APPEND);
			//数组转字符串；以空格分割
			$str1 = implode('',$datas);
			//数据转GB2312
			$str2 = $this->charsetToGB($str1,"GB2312");
			return $str2;
		}
		
		/**
		 * 单笔扣费对账文件获取   100018
		 * 2018-04-17
		 */
		public function Reconciliation($SurrData = [])
		{
			//扣费请求报文头  PFMT_ YGK_SVR_IN
			$data = [				 
				//服务名(交易码)  6  char
				'__GDTA_SVCNAME' =>	$SurrData['transaction'],// '100018',
				//单位编号 11	char
				'BkEntrNo' => $SurrData['BkEntrNo'],//'04000000050',
				//委托单位账号	32  char
				'BkAcctNo' => $this->getStrlen($SurrData['BkAcctNo'],32),
				//中间业务代码	6	char
				'BkType1' => $SurrData['BkType1'],//'11402
				//发起方日期	8   YYYYMMDD	char
				'BkOthDate' => $SurrData['BkOthDate'],
				//发起方流水号	20 char
				'BkOthSeq' => $this->getStrlen($SurrData['BkOthSeq'],20),
				/**
				 * 报文部分；
				 */
				//实际交易日期(不能是当天)	8	char 
				'Bk8Date1'  => $SurrData['Bk8Date1'],
				//预留1	64	char	32个中文  getIntlen
				'Bk255str1' => $this->getStrlen($SurrData['Bk255str1'],64),
				//预留2	128	char 64个中文
				'Bk255str2' => $this->getStrlen($SurrData['Bk255str2'],128),
			];
			
			$str = implode('',$data);
			// 位数：int  8  获取报文长度；
			$lens = $this->getBaoWen($str,8);//拼接字符串长度；
			$len = ['__GDTA_ITEMDATA_LENGTH' => $lens];			
			$datas = array_merge($len,$data);
			file_put_contents('./log/Reconciliation.txt', print_r($datas,TRUE),FILE_APPEND);
			//数组转字符串；以空格分割
			$str1 = implode('',$datas);
			//数据转GB2312
			$str2 = $this->charsetToGB($str1,"GB2312");
			return $str2;
		}
		
	 	//字符串转换编码，UTF-8 转 GB2312;
	 	/**
	 	 * @param $mixed 需转换的数据
	 	 * @param $Unicode 转换的字符编码
	 	 */
		public function charsetToGB($mixed,$Unicode) 
		{
		    if (is_array($mixed)) {
		        foreach ($mixed as $k => $v) {
		            if (is_array($v)) {
		                $mixed[$k] = charsetToGB($v,$Unicode);
		            } else {
		                $encode = mb_detect_encoding($v, array('ASCII', 'UTF-8', 'GB2312', 'GBK', 'BIG5'));
		                if ($encode == 'UTF-8') {
		                    $mixed[$k] = iconv('UTF-8', $Unicode, $v);
		                }
		            }
		        }
		    } else {
		        $encode = mb_detect_encoding($mixed, array('ASCII', 'UTF-8', 'GB2312', 'GBK', 'BIG5'));
		        if ($encode == 'UTF-8') {
		            $mixed = iconv('UTF-8', $Unicode, $mixed);
		        }
		    }
		    return $mixed;
		}
		
		/**
		 * 返回经addslashes处理过的字符串或数组
		 * @param $string 需要处理的字符串或数组
		 * @return mixed
		 */
		public function new_addslashes($string){
		    if(!is_array($string)){ 
		    	return addslashes($string);
		    }   
		    foreach($string as $key => $val) {
		    	$string[$key] = $this->new_addslashes($val);
		    }    
		    return $string;
		}
		
		/**
		 * 解析返回的数据；
		 * 0=8、1=6、2=8、3=12、4=8、5=20、6=6、7=100
		 */
		public function Analysis($str)
		{
			$i = 0;//截取字符串开始位置；
			$start = 0;//开始截取的位置；
			$lenght = 8;//截取的长度；
			$arrayData = [];//截取的字符串保存到数组中；
			for($i ;$i<=7;$i++) {
				switch($i)
				{
					case 0:
						$lenght = 8;//报文长度
						$arrayData['Length'] = substr($str,$start,$lenght);
					break;
					case 1:
						$start = $lenght;
						$lenght = 6;//服务名称
						$arrayData['SvcName'] = substr($str,$start,$lenght);
					break;
					case 2:
						$start += $lenght;
						$lenght = 8;//银行日期
						$arrayData['PlatDate'] = substr($str,$start,$lenght);
					break;
					case 3:
						$start += $lenght;
						$lenght = 12;//银行流水号
						$arrayData['SeqNo'] = ltrim(substr($str,$start,$lenght));
					break;
					case 4:
						$start += $lenght;
						$lenght = 8;//发起方日期
						$arrayData['OthDate'] = substr($str,$start,$lenght);
					break;
					case 5:
						$start += $lenght;
						$lenght = 20;//发起方流水
						$arrayData['OthSeq'] = substr($str,$start,$lenght);
					break;
					case 6:
						$start += $lenght;
						$lenght = 6;//返回码
						$arrayData['Result'] = trim(substr($str,$start,$lenght));
					break;
					case 7:
						$start += $lenght;
						$lenght =100;//返回信息
						$arrayData['Message'] = trim(substr($str,$start,$lenght));
					break;
				}
			}
			return $arrayData;
		}
		
		/**
		 * 2018-04-16
		 * 查询当前字符串的长度，拼接后返回新的字符串
		 * 字符串：右边补空格；
		 */
		public function getStrlen($strs,$len)
		{	
			//字符串转换GB2312
			$str = $this->charsetToGB($strs,'GB2312');
			//转换后的字符串长度计算
			$strlen = strlen($str);
			//计算字符串相差多少
			$leng = $len - $strlen;
			//判断字符串长度是否小于  需要补的长度；
			if($strlen < $len+1)
			{	//循环从1开始，循环计算差补，
				for($i=1 ; $i <= $leng ;$i++)
				{
					$str .= ' ';//循环补空格
				}
				return $str;
				//如果长度一致，直接返回字符串；
			} else if($strlen == $len) {
				return $str;
			}
		}
		
		/**
		 * 2018-04-16
		 * 数值类型 右对齐左边补空格
		 */
		public function getIntlen($number,$len)
		{
			//字符串赋值变量
			$str = $number;
			//定义需要拼接的字符串
			$zero = '';
			//获取字符串的长度；
			$numLen = strlen($number);
			//计算出相差多少；字符串所需长度-字符串长度；
			$num_len = $len - $numLen;
			//如果字符串小于所需长度
			if($numLen < $len+1)
			{
				for($i=1 ; $i <= $num_len ;$i++)
				{
					$zero .= ' ';
				}
				return $zero.$str;
			} else if($numLen == $len)
			{
				return $str;
			}
		}
		
		/**
		 * 2018-04-18
		 * 计算报头+报文长度
		 */
		public function getBaoWen($str,$len)
		{
			$str = strlen($str);//将字符串转换为字符长度；
			$strLen = strlen($str);//计算长度，三位数，需在前面拼接5个空格；
			$lens = $len;
			$left = '';//需拼接变量；
			$ji = ($lens - $strLen);//8-3=5  
			if($strLen == $lens)//如果计算出来的字符串长度与需要的长度一致，直接返回； 
			{
				return $str;
				
			} else if( $strLen < ($lens+1)) {
				
				for( $i=1; $i<=$ji; $i++ )
				{
					$left .= ' ';
				}
				return $left.$str;
			}
		}
		
		public function postCredit($ordersn) {
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
	}
?>