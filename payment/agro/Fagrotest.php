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
//	header("Content-Type:text/html;charset=GBK");
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
	
	$address = '192.168.251.10';
	$port = '39031';
	$BkEntrNo = '04000000050';//单位编号 11	char
	$BkAcctNo = '801101000588712434';//委托单位账号	32  char
	$BkType1 = '114021';//中间业务代码	6	char
	
	//单例模式 实例化；
	$first = Fagro::getInstance();
	/**
	 * 开发步骤：
	 * 1、接收传过来的参数；
	 * 2、处理传过来的数据
	 * 3、根据token,分流
	 * 4、返回处理结果；
	 */

	if(!empty($_POST) && isset($_POST['Token']))
	{
		file_put_contents('./test/getPost.txt', print_r($_POST,TRUE));
		$Token = trim($_POST['Token']);
		$SendData = $_POST;
		
//		exit(json_encode(['msg'=>$SendData]));
		
		//解约、扣费、冲销、查询、单笔对账文件；
		$type = ['Surrender','FeeDeduction','WriteOff','Query','Reconciliation'];
		if(in_array($Token,$type)) {
			
			$Phone = trim($SendData['Phone']);//手机号码、注册会员
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
					$filed = 'a.uniacid,a.pay_account,a.ordersn,a.create_time,a.address,a.user_id,a.create_time,a.body,b.OthSeq,b.starttime,b.endtime,b.duration,a.total ';
					$find = array(':ordersn' => $OrderSn, ':pay_status' => 0,':paystatus' => 2);
					$OrdData = pdo_fetch("SELECT ".$filed." FROM ".tablename('foll_order')." a LEFT JOIN ".tablename('parking_order')." b ON a.ordersn = b.ordersn WHERE a.ordersn = :ordersn AND a.pay_status = :pay_status OR a.pay_status = :paystatus ORDER BY a.id LIMIT 1",$find);
					
					file_put_contents('./test/OrdData.txt', print_r($OrdData,TRUE));
					
					if(!empty($OrdData)) {
						
						$oldOrdersn = $OrdData['ordersn'];//查询出来的订单编号			
						$data = [
							//服务名(交易码)  6  char
							'transaction'=>'100015',
							//单位编号 11	char
							'BkEntrNo' => $BkEntrNo,
							//委托单位账号	32  char
							'BkAcctNo' => $BkAcctNo,
							//中间业务代码	6	char
							'BkType1' => $BkType1,
							//发起方日期	8   YYYYMMDD	char
							'BkOthDate' => date('Ymd',$OrdData['create_time']),//获取foll_order 表中 create_time 
							//发起方流水号	20 char		获取parking_order 表中的 OthSeq 发起方流水号；
							'BkOthSeq' => $OrdData['OthSeq'],//发起方流水号
							
							/**
							 * 报文部分；
							 */
							//客户编号（发起方唯一健值）  30	char  请求报文
							'BkAcctNo1'=> $Phone,
							//银行卡号	32	char
							'BkAcctNo2'=> $CarNo,
							//交易金额	17	2 Double
							'BkTxAmt'=> $OrdData['pay_account'],//获取foll_order 表中 pay_account 
							//订单编号  30 Char
							'BkAcctNo3'=> $OrdData['ordersn'],	//获取foll_order 表中 ordersn
							//停车位置	98  Char  广东省佛山市顺德区伦教办事处常教社区居委会尚城路12号
							'BkAddr1'=> $OrdData['address'],//'广东省佛山市顺德区伦教办事处常教社区居委会尚城路12号',//获取foll_order 表中 address
							//停车开始时间	14 yyyymmddhhmmss  Char
							'Bk19DateTime1'=> date('Ymdhis',$OrdData['starttime']),//获取parking_order 表中 starttime
							//停车结束时间	14 yyyymmddhhmmss Char
							'Bk19DateTime2'=> date('Ymdhis',$OrdData['endtime']),//获取parking_order 表中 endtime
							//预留1	64	char	32个中文
							'Bk255str1'=> 'BaoWen',
							//预留2	128	char 64个中文
							'Bk255str2'=> 'BaoWen',
						];
						
						/**
						 * 组装数据；
						 * 写入pay_old 表中，流水表；
						 */
						$inserData = [
							'payMoney'=>$OrdData['pay_account'],//交易金额
							'ordersn'=>$OrdData['ordersn'],//订单编号
							'payType'=>'FAgro',//顺德农商行免密支付；
							'body'=>$OrdData['body'],//订单描述
							'openid'=>$OrdData['user_id'],//用户ID
							'create_time'=>$OrdData['create_time'],//创建时间
							'account'=>$BkAcctNo,//委托收款账号
							'upOrderId'=>$OrdData['OthSeq'],//发起方流水；
							//'SeqNo'//银行流水号；
						];
						
						pdo_insert('pay_old',$inserData);
						
						/**
						 * 组装发送消息模板数据
						 */
						//计算停车时长返回数组形式
						$T = timediff($OrdData['starttime'],$OrdData['endtime']);
						$sendArr = array(
							'body' => $OrdData['body'],//商品描述
							'paytime' => date('Y-m-d H:i:s',time()),//消费时间
							'touser' => $OrdData['user_id'],//接收消息的用户
							'uniacid' =>$OrdData['uniacid'],//公众号ID
							'parkTime' => $T['day'].'天'.$T['hour'].'小时'.$T['min'].'分',//停车时长
							'realTime' => $OrdData['duration'],//实计时长	分钟	
							'payableMoney' => $OrdData['total'],//应付金额
							'deducMoney' =>($OrdData['total']-$OrdData['pay_account']),//抵扣金额
							'payMoney' => $OrdData['pay_account'],//交易金额  实付金额
						);
						
						//处理扣费数据；
						$strs = $first->FeeDeduction($data);
						//发送报文，并获取请求响应；
						$res = $first->socket_client($address,$port,$strs);
						file_put_contents('./test/Fee.txt', print_r($res,TRUE).'
',FILE_APPEND);
						//解析返回数据；
						$res = $first->Analysis($res);
						//扣费成功
						if($res['Result'] == 00000 && $res['Message'] == '成功完成')
						{
							/**
							 * 开发步骤：
							 * 1、扣费成功，更新数据表的状态；
							 * 发送成功扣费通知
							 */
							try
							{
								pdo_begin();//开启事务
								
									pdo_update('pay_old',array('status' => 1,'update_time'=>time(),'SeqNo'=>$res['SeqNo'],'Msg'=>$res['Message'],'PlatDate'=>$res['PlatDate']), array('ordersn'=>$oldOrdersn));
									//支付成功修改parking_order 表中状态：支付成功  SeqNo：银行流水；
									pdo_update('parking_order', array('upOrderId'=>$res['SeqNo'],'status'=>'已结算','PlatDate'=>$res['PlatDate']),array('ordersn' => $oldOrdersn));
									
									pdo_update('foll_order', array('pay_status' => 1,'pay_time'=>time()), array('ordersn' => $oldOrdersn));
				
								pdo_commit();//提交事务
							}catch(PDOException $e){
								pdo_rollback();//执行失败，事务回滚
							}
							
							$sendArr['first'] = '您好，您的停车服务费扣费成功！';
							$sendArr['remark'] = '欢迎您下次继续使用！';
							
							echo json_encode(['status'=>1,'msg'=>'扣费成功']);
							
							//缴费成功模板
							sendMsgSuccess($sendArr);
							
						} else {//扣费失败；
							/**
							 * 开发步骤：
							 * 1、扣费失败，更新数据表的状态；
							 * 发送失败扣费通知
							 */
							try
							{
								pdo_begin();//开启事务
								
									pdo_update('pay_old', array('status' => 2,'update_time'=>time(),'SeqNo'=>$res['SeqNo'],'Msg'=>$res['Message']), array('ordersn'=>$oldOrdersn));
									//支付成功修改parking_order 表中状态：支付成功  SeqNo：银行流水；
									pdo_update('parking_order', array('upOrderId'=>$res['SeqNo'],'status'=>'未结算'), array('ordersn' => $oldOrdersn));
									
									pdo_update('foll_order', array('pay_status' => 2,'pay_time'=>time()), array('ordersn' => $oldOrdersn));
				
								pdo_commit();//提交事务
							}catch(PDOException $e) {
								pdo_rollback();//执行失败，事务回滚
							}
							
							$sendArr['first'] = '抱歉，您的停车服务费扣费失败！';
							$sendArr['remark'] = '请点击详情，继续完成支付！';
							$sendArr['Reurl'] = 'http://shop.gogo198.cn/app/index.php?i='.$OrdData['uniacid'].'&c=entry&m=ewei_shopv2&do=mobile&r=parking.pay';
							
							echo json_encode(['status'=>0,'msg'=>'扣费失败']);
							//缴费成功模板
							sendMsgSuccess($sendArr);
						}
						
					} else {
						echo json_encode(['status'=>0,'msg'=>'请检查订单编号']);
					}					
				break;
				
				case 'WriteOff'://冲销  100019
					$data = [
						//服务名(交易码)  6  char
						'transaction'=>'100019',
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
						//原银行日期	 8	传时间格式：yyyymmdd
						'BkOldPlatDate'=> $PlatDate,
						//原银行流水号  12 Char
						'BkOldSeq'=> $BkOldSeq,
						//预留1	64	char	32个中文
						'Bk255str1'=> 'BaoWen',
						//预留2	128	char 64个中文
						'Bk255str2'=> 'BaoWen',
					];
					$strs = $first->WriteOff($data);
					//发送报文，并获取请求响应；
					$res = $first->socket_client($address,$port,$strs);
					//返回结果；
					file_put_contents('./test/Write.txt', print_r($res,TRUE).'
',FILE_APPEND);
					
					$res = $first->Analysis($res);
					if($res['Result'] == 00000 && $res['Message'] == '成功完成')
					{	//返回json 数据；
						echo json_encode($res);
					} else {
						echo json_encode($res);
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
					file_put_contents('./test/Quer.txt', print_r($res,TRUE).'
',FILE_APPEND);
					
//					file_put_contents('./test/Result.txt', print_r($res,TRUE));					
//					$file = file_get_contents('./test/Result.txt');
					$res = $first->Analysis($res);
					if($res['Result'] == 00000 && $res['Message'] == '成功完成')
					{	//返回json 数据；
						echo json_encode($res);
					} else {
						echo json_encode($res);
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
					file_put_contents('./test/Surr.txt', print_r($res,TRUE).'
',FILE_APPEND);
					$Res = $first->Analysis($res);
					file_put_contents('./test/SurrResult.txt', print_r($Res,TRUE).'
',FILE_APPEND);
//					if($Res['Result'] == '00000' && $Res['Message'] == '成功完成'){
//					if($Res['Result'] == '10000') {
					//返回json 数据；
					echo json_encode($Res);
						
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
					//返回结果；
					file_put_contents('./test/Recon.txt', print_r($res,TRUE).'
',FILE_APPEND);

					$Res = $first->Analysis($res);
//					if($res['Result'] == 00000 && $res['Message'] == '成功完成')
//					{	//返回json 数据；
//						echo json_encode($res);
//					} else {
						echo json_encode($Res);
//					}
				break;
			}
			
		} else {
			echo json_encode(['status'=>0,'msg'=>'无此类型!']);
		}		
	} else {
		echo json_encode(['status'=>0,'msg'=>'请求类型不正确!']);
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
				'BkType1' => $SurrData['BkType1'],//'114021',				
				//发起方日期	8   YYYYMMDD	char
				'BkOthDate' => $SurrData['BkOthDate'],
				//发起方流水号	20 char
				'BkOthSeq' => $SurrData['BkOthSeq'],
				
				//  报文部分
				//客户编号（发起方唯一健值）  30 Char
				'BkAcctNo1'=> $this->getStrlen($SurrData['BkAcctNo1'],30),
				//银行卡号	32	Char
				'BkAcctNo2'=> $this->getStrlen($SurrData['BkAcctNo2'],32),
				//交易金额	17	2 Double
//				'BkTxAmt'=> '             '.$SurrData['BkTxAmt'],
				'BkTxAmt'=> $this->getIntlen($SurrData['BkTxAmt'],17),
				//订单编号  30 Char
				'BkAcctNo3'=> $SurrData['BkAcctNo3'],
				//停车位置	98  Char
				'BkAddr1'=> $this->getStrlen($SurrData['BkAddr1'],98),//返回长度为98位的字符串；
				//停车开始时间	14 yyyymmddhhmmss  Char
				'Bk19DateTime1'=> $SurrData['Bk19DateTime1'],
				//停车结束时间	14 yyyymmddhhmmss Char
				'Bk19DateTime2'=> $SurrData['Bk19DateTime2'],
				'Bk255str1'=> $this->getStrlen($SurrData['Bk255str1'],64),//预留1	64
				'Bk255str2'=> $this->getStrlen($SurrData['Bk255str2'],128),//预留2	128
			];
			$str = implode('',$data);
			// 位数：int  8  获取报文长度；
			$lens = $this->getBaoWen($str,8);//拼接字符串长度；
			$len = [
				'__GDTA_ITEMDATA_LENGTH' => $lens,
			];
			//数组合并；
			$datas = array_merge($len,$data);
			file_put_contents('./test/FeeDeduction.txt', print_r($datas,TRUE),FILE_APPEND);
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
				'BkOthSeq' => $SurrData['BkOthSeq'],
				
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
			file_put_contents('./test/Surrender.txt', print_r($datas,TRUE),FILE_APPEND);
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
				'BkOthSeq' => $SurrData['BkOthSeq'],
				
				
				//发起方日期8	char 
				'BkOthOldDate'=> $SurrData['BkOthOldDate'],
				//发起方流水号	20	char
				'BkOthOldSeq'=> $SurrData['BkOthOldSeq'],
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
			file_put_contents('./test/Query.txt', print_r($datas,TRUE),FILE_APPEND);
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
				'BkOthSeq' => $SurrData['BkOthSeq'],
				
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
			file_put_contents('./test/WriteOff.txt', print_r($datas,TRUE),FILE_APPEND);
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
				'BkOthSeq' => $SurrData['BkOthSeq'],
				/**
				 * 报文部分；
				 */
				//实际交易日期(不能是当天)	8	char 
				'Bk8Date1'=> $SurrData['Bk8Date1'],
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
			file_put_contents('./test/Reconciliation.txt', print_r($datas,TRUE),FILE_APPEND);
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
						$arrayData['SeqNo'] = ltrim(substr($str,$start,$lenght),' ');
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
						$arrayData['Result'] = trim(substr($str,$start,$lenght),' ');
					break;
					case 7:
						$start += $lenght;
						$lenght =100;//返回信息
						$arrayData['Message'] = trim(substr($str,$start,$lenght),' ');
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
	}
?>