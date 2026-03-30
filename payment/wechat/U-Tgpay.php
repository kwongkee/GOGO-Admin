<?php
header('Content-Type:text/html;charset=utf-8');
define('IN_MOBILE', true);
define('PDO_DEBUG', true);
define("KEYS","f8ee27742a68418da52de4fca59b999e");
define("KEYS1","b4f16b4526b046c580e363fcfcd07c82");
require_once '../../framework/bootstrap.inc.php';
require_once '../../app/common/bootstrap.app.inc.php';
//load()->app('common');
load()->app('template');
//加载发送消息函数  diysend
load()->func('diysend');
global $_W;
global $_GPC;

//按订单号查询该订单信息发起支付
/**
 * 开发步骤：主动支付；
 * 1、表单上传token  与订单号 ordersn:
 * 例如：token = wechat,
 * ordersn:GGPgogo198201712051009
 */

 /**
  * 接收数据：
  * 	'token'   => $receive['pay_type'],//支付类型如：wechat,alipay,...
		'ordersn' => $receive['ordersn'],//新订单编号
		'orderId' =>$_GPC['order'],//旧订单编号
  * 
  * 判断表中是否存在旧订单编号，
  * 1、有，修改对应的订单编号(直接拿订单信息支付);
  * 2、无，在表中添加新的数据(用新数据提交);
  */
  
if (!empty($_POST) && isset($_POST['token'])) {
	file_put_contents('./log/getTgpay.txt', print_r($_POST,TRUE),FILE_APPEND);
	/**
	 * 支付类型数组；
	 */
	//聚合支付：wechat：微信,alipay：支付宝,UnionPay：银联，退款：refund
	//免密授权：Fwechat:微信,Falipay：支付宝,FCreditCard：信用卡,FAgro：农商行
	$type=['wechat','alipay','Tgwechat_scode','Tgalipay_scode','unionpay','refund'];
	//验证支付类型是否一致
	if (in_array($_POST['token'], $type)) {//判断接收到的支付类型是否在数组中
		
		//查询旧的订单编号对应的ID  orderId
		$orderid = pdo_get('pay_order', array('ordersn' => trim($_POST['orderId'])), array('id','ordersn','upOrderId'));
		file_put_contents('./log/pay_order.txt', print_r($orderid,TRUE));
		//获取订单流水信息
		//$old = pdo_get('pay_old', array('ordersn' => $_POST['orderId']), array('id','ordersn'));
		
		/**
		 * 组装条件
		 */
		$updata = [
			'ordersn' => trim($_POST['ordersn']),//新的订单编号
			'payType' => trim($_POST['token']),//新的支付类型
		];
		//作为缓存键
		$key = "Tgpay_ID";
		//如果订单表中存在该条订单
		if(!empty($orderid)) {
			
			//按条件更新到订单表中，更新订单编号，订单支付类型
			$result = pdo_update('pay_order', $updata, array('id' => $orderid['id']));
			//更新成功
			if (!empty($result)) {
			    //查询表中对应的订单编号是否有未支付订单
			    //2018-04-13
				$parms = pdo_fetch("SELECT a.uniacid,a.pay_account,a.ordersn,a.pay_type,a.body,a.user_id,a.create_time,b.upOrderId FROM ".tablename('foll_order')." a LEFT JOIN ".tablename('parking_order')." b ON a.ordersn = b.ordersn WHERE a.ordersn = :ordersn AND (a.pay_status = :pay_status OR a.pay_status = :paystatus) ORDER BY a.id LIMIT 1", array(':ordersn' => $updata['ordersn'], ':pay_status' => 0,':paystatus' => 2));
				if(!$parms) {
					exit(json_encode(['code'=>0,'msg'=>'无该订单信息，请检查!']));
				}
				
				//获取支付配置   a.uniacid
				$configs = pdo_get('pay_config',array('uniacid'=>$parms['uniacid']),array('config'));
				//无配置 
				if(!$configs) {
					exit(json_encode(['code'=>0,'msg'=>'无该配置，请检查!']));
				}
				
				//定义key
				$key = $key.$parms['uniacid'];
				//不为空，取缓存数据；判断数据一致则直接退出
				if(!empty($cache = cache_load($key)))
				{
					$config = $cache;
				} else {
					//反序列获取数组；
					$config = json_decode($configs['config'],true);
					$config = array(
						'account'=> $payconfig['tg']['mchid'],//商户号
						'key'    =>	$payconfig['tg']['key'],//秘钥
					);
					//写入缓存
					cache_write($key,$config);
				}
				
				
				/**
				 * 2018-06-19
				 * 试运营设置
				 * 步骤：1、查找该公众号是否有折扣优惠，并且总金额要大于0
				 * 2、获取全部折扣
				 * 3、循环比对数据，找到符合条件退出循环；
				 * 4、使用（总金额）* (折扣率) = 实付金额
				 * 5、用实付金额 写入数据；请求支付；
				 */
				$m = 0 ;
				$opera = pdo_getall('parking_operate',['uniacid'=>$parms['uniacid'],'status'=>2],['discount','startDate','endDate','status']);
				if(!empty($opera) && ($parms['total'] > 0))//1
				{
					$m = $parms['total'];//折扣率后的金额
					$j = '0.';//折扣率 例如：0.5
					$times = time();//当前时间搓
					foreach($opera as $key=>$val) {//2
						if(($times >= $val['startDate']) && ($times <= $val['endDate'])) {//3
							//查找符合条件的折扣率 转换小数 * 待转换的折扣金额
							$m = ($m * (float)($j=$j.$val['discount']));
							break;
						}
					}
					//交易金额大于0  更新表内实付金额
					if($m > 0) {
						pdo_update('foll_order',['pay_account'=>$m],['ordersn'=>$parms['ordersn']]);
					}
				}
				//支付交易金额
				$m = $m > 0 ? (round($m,2)):(round($parms['pay_account'],2));
				//折扣结算结束
				
				//组装数据  支付订单写入old订单流水表
				$params = array(
					'payMoney'	=>	$m>0 ? $m : $parms['pay_account'],//支付金额  a.pay_account
					'ordersn'	=>	$parms['ordersn'],//支付订单号  a.ordersn
					'upOrderId' =>	$parms['upOrderId'],//上游订单号  b.upOrderId
					'payType'	=>	$parms['pay_type'],//支付类型	a.pay_type
					'body'		=>	$parms['body'],//商品描述	a.body
					'openid'	=>	$parms['user_id'],//用户Openid  a.user_id  测试：om44I1X2wIlTWdN3pSsgoJkfMLtI				
					'create_time' => $parms['create_time'],//订单提交时间	a.create_time		
					'account' 	  => $config['account'],
					'status' 	  => '0',//支付状态  0订单已提交，1支付成功，2支付失败
				);
				
				/**
				 * 将支付订单写入pay_old表中
				 */
				$old = pdo_insert('pay_old', $params);
				if (!empty($old)) {
				    $old = pdo_insertid();
				}else {
					echo 'sqlError';
				}
	
			}

		} else {//如果 pay_order 表中不存在该订单编号
					
			$parms = pdo_fetch("SELECT a.uniacid,a.pay_account,a.total,a.ordersn,a.pay_type,a.body,a.user_id,a.create_time,b.upOrderId FROM ".tablename('foll_order')." a LEFT JOIN ".tablename('parking_order')." b ON a.ordersn = b.ordersn WHERE a.ordersn = :ordersn AND (a.pay_status = :pay_status OR a.pay_status = :paystatus) ORDER BY a.id LIMIT 1", array(':ordersn' => $updata['ordersn'], ':pay_status' => 0,':paystatus' => 2));
			file_put_contents('./log/pay_Ok.txt', print_r($parms,TRUE));
			if(!$parms) {
				exit(json_encode(['code'=>0,'msg'=>'无该订单信息，请检查!']));
			}
			//获取支付配置参数 a.uniacid
			$configs = pdo_get('pay_config',array('uniacid'=>$parms['uniacid']),array('config'));
			//无配置 
			if(!$configs) {
				exit(json_encode(['code'=>0,'msg'=>'无该配置，请检查!']));
			}
			
			//折扣优惠计费  2018-06-20
			$m = 0;
			$opera = pdo_getall('parking_operate',['uniacid'=>$parms['uniacid'],'status'=>2],['discount','startDate','endDate']);
			if(!empty($opera) && $parms['total'] > 0)//1、数据不为空，并且应付总额大于0
			{
				$m = $parms['total'];//折扣率后的金额
				$j = '0.';//折扣率 例如：0.5
				$times = time();//当前时间搓
				foreach($opera as $key=>$val) {//2
					if(($times >= $val['startDate']) && ($times <= $val['endDate'])) {//3
						//查找符合条件的折扣率 转换小数 * 待转换的折扣金额
						$m = ($m * (float)($j=$j.$val['discount']));
						break;
					}
				}
				//交易金额大于0  更新表内实付金额
				if($m > 0) {
					pdo_update('foll_order',['pay_account'=>$m],['ordersn'=>$parms['ordersn']]);
				}
			}
			//支付交易金额
			$m = $m > 0 ? (round($m,2)):(round($parms['pay_account'],2));
			//折扣计算结束
			
			//组装par_order表中数据
			$receive = array(
				'ordersn'	=>  $parms['ordersn'],//订单编号	a.ordersn
				'upOrderId'	=>	$parms['upOrderId'],//上游订单号	b.upOrderId
				'openid'	=>  $parms['user_id'],//用户Openid		a.user_id
				'body'		=>  $parms['body'],//商品描述		a.body
				'payMoney'	=>  $m,//$parms['pay_account'],//交易金额	a.pay_account  单位：元
				'payType'	=>  $parms['pay_type'],//交易类型 wechat,alipay	a.pay_type
				'payTime'	=>  $parms['create_time'],//订单时间	a.create_time
				'uniacid' 	=>  $parms['uniacid'],//公众号ID
				'status' 	=>  '0',
			);

			//定义key
			$key = $key.$parms['uniacid'];
			//不为空，取缓存数据；判断数据一致则直接退出
			if(!empty($cache = cache_load($key)))
			{
				$config = $cache;
			} else {
				//反序列获取数组；
//				$payconfig = unserialize($configs['config']);
				$config = json_decode($configs['config'],true);
				$config = array(
					'account'=> $payconfig['tg']['mchid'],//商户号
					'key'    =>	$payconfig['tg']['key'],//秘钥
				);
				//写入缓存
				cache_write($key,$config);
			}
		
			//组装数据  支付订单写入old订单流水表   用于请求支付参数
			$params = array(
				'payMoney'	=>  	 $receive['payMoney'],//支付金额
				'ordersn'	=>   	 $receive['ordersn'],//支付订单号
				'payType'	=>   	 $receive['payType'],//支付类型
				'body'		=>       $receive['body'],//商品描述
				'openid'	=>       $receive['openid'],//用户Openid   测试：om44I1X2wIlTWdN3pSsgoJkfMLtI				
				'create_time' 	=>   time(),//订单提交时间			
				'account' 		=> 	 $config['account'],
				'status' 		=>   '0',//支付状态  0订单已提交，1支付成功，2支付失败
			);
			
			try {
				
				pdo_begin();//开启事务
					/**
					 * 订单存入数据库中 
					 * 添加数据到order表中
					 */
					$order = pdo_insert('pay_order', $receive);
					if (!empty($order)) {
					    $orderid = pdo_insertid();   
					}else {
						exit(json_encode(['code'=>0,'msg'=>'数据写入失败']));
					}
					/**
					 * 将支付订单写入pay_old表中
					 */
					$old = pdo_insert('pay_old', $params);
					if (!empty($old)) {
					    $old = pdo_insertid();
					}else {
						exit(json_encode(['code'=>0,'msg'=>'数据写入失败']));
					}
					
				pdo_commit();//提交事务
				
			}catch(PDOException $e) {
				
				pdo_rollback();//执行失败，事务回滚			
			}
		}


		/**
		 *分流判断： 
		 *
		 * $paytype 判断支付类型：wechat,alipay,unionpay，bestpay,   Tgwechat_scode
		 */
		switch($parms['pay_type']) {

			case 'wechat'://微信公众号支付	
				$payurl = Tgwechat_public($params,$config);	
				//判断返回参数
				if(!empty($payurl) && ($payurl['status'] == '100') ) {
					$arrs = [
						'payurl'=> $payurl['pay_url'],
						'msg'=>'success',
					];
				}else {
					$arrs = [
						'status'=>$payurl['status'],
						'msg'=>$payurl['message'],
					];
				}
				exit(json_encode($arrs));
			break;

			case 'alipay'://通莞支付宝alipay
				$payurl = Tgalipay_scode($params,$config);
				if(!empty($payurl) && ($payurl['status'] == '100')){
					$arrs = [
						'payurl'=>$payurl['codeUrl'],
						'msg'=>'success',
					];
				}else {
					$arrs = [
						'status'=>$payurl['status'],
						'msg'=>$payurl['message'],
					];
				}
				exit(json_encode($arrs));
			break;
			
			//通莞微信扫码
			case 'Tgwechat_scode':
				$payurl = Tgwechat_scode($params,$config);
				if(!empty($payurl) && ($payurl['status'] == '100')){
					$arrs = [
						'payurl'=>$payurl['codeUrl'],
						'msg'=>'success',
					];
				}else {
					$arrs = [
						'status'=>$payurl['status'],
						'msg'=>$payurl['message'],
					];
				}
				exit(json_encode($arrs));
			break;
			
			//通莞支付宝扫码
			case 'Tgalipay_scode':
				$payurl = Tgalipay_scode($params,$config);
				if(!empty($payurl) && ($payurl['status'] == '100')){
					$arrs = [
						'payurl'=>$payurl['codeUrl'],
						'msg'=>'success',
					];
				}else {
					$arrs = [
						'status'=>$payurl['status'],
						'msg'=>$payurl['message'],
					];
				}
				exit(json_encode($arrs));
			break;
			
			
			case 'unionpay'://银联快捷闪付
//				$params['returnUrl'] = $receive['returnUrl'];
				$payurl = TgUnionpay($params,$config);
//				$payurl = $payurl->codeUrl;
				if(!empty($payurl) && $payurl['status'] == '100' ) {
					$arrs = [
						'payurl'=>$payurl['payUrl'],
						'msg'=>'success'
					];
				}else {
					$arrs = [
						'status'=> $payurl['status'],
						'msg'=>$payurl['message'],
					];
				}
				echo json_encode($arrs);
			break;
		}

	}else {
		$arrs = [
			'status'=>'fail',
			'message'=>'数据验证失败',
		];
		echo json_encode($arrs);
	}
} else {
	echo '未接收到请求！';
}


function fee($params) {
	
	
}



/**
 * 接收字串拼接
 * @arrs 接收数组参数；
 */
function toreceive($arrs) {
	krsort($arrs);
	$str = '';
	foreach($arrs as $key=>$val){
		$str .= $key . '=' . $val . '&';
	}
	$str = trim($str,'&');
	return $str;
}


/**
 * 通莞微信撤销订单。 请转到 refund.php 中处理；
 * @params 订单支付信息
 * @config  配置信息
 */
function Tgwechat_Rpublic($params, $config) {

	$package = array();
	$package['account'] = $config['account'];//商户号
	$package['upOrderId'] = $params['upOrderId'];//上游订单号
	$package['refundMoney'] = $params['refundMoney'];//退款金额  不能大于本次交易；
	//转换key=value&key=value;
	$str = tostring($package);
	//拼接加密字串
	$str .= '&key=' . $config['key'];
	//MD5加密字串
	$sign = md5($str);
	//返回加密字串转换成大写字母
	$package['sign'] = strtoupper($sign);
	//数据包转换成json格式
	$data =  json_encode($package);
	//数据请求地址，post形式传输
	$url = 'https://ipay.833006.net/tgPosp/services/payApi/refund';
//	$url = 'http://tgjf.833006.biz/tgPosp/services/payApi/refund'; //测试地址
	//数据请求地址，post形式传输
	$response = ihttp_posts($url,$data);

	//解析json数据
	$response = json_decode($response,TRUE);
	
	file_put_contents('./log/Rpublic.txt', print_r($response,TRUE),FILE_APPEND);
	//直接返回支付URL地址
//	return $response->pay_url;
	//返回数组
	return $response;
}


/**
 * 通莞微信公众号支付。 
 * @params 订单支付信息
 * @config  配置信息
 */
function Tgwechat_public($params, $config) {

	$package = array();
	$package['account'] = $config['account'];
	$package['payMoney'] = $params['payMoney'];
	$package['lowOrderId'] = $params['ordersn'];
	$package['body'] = $params['body'];
	$package['notifyUrl'] = 'http://shop.gogo198.cn/payment/wechat/tgwechatnotify.php';//后台回调地址
	$package['returnUrl'] = 'http://shop.gogo198.cn/app/index.php?i=14&c=entry&m=ewei_shopv2&do=mobile&r=parking.msg';//成功回调地址；
	$package['openId'] = $params['openid'];
	//转换key=value&key=value;
	$str = tostring($package);
	//拼接加密字串
	$str .= '&key=' . $config['key'];
	//MD5加密字串
	$sign = md5($str);
	//返回加密字串转换成大写字母
	$package['sign'] = strtoupper($sign);
	//数据包转换成json格式
	$data =  json_encode($package);
	//数据请求地址，post形式传输
	$url = 'https://ipay.833006.net/tgPosp/payApi/wxJspay';
//	$url = 'http://tgjf.833006.biz/tgPosp/payApi/wxJspay'; //测试地址
	//数据请求地址，post形式传输
	$response = ihttp_posts($url,$data);

	//解析json数据
	$response = json_decode($response,TRUE);
	
	file_put_contents('./log/wechatRes.txt', print_r($response,TRUE));
	//直接返回支付URL地址
//	return $response->pay_url;
	//返回数组
	return $response;
}




/**
 * 通莞订单查询 
 * @params 查询信息
 * @config  配置信息
 */
function ordQuery($params, $config) {

	$package = array();
	$package['account'] = $config['account'];
	$package['upOrderId'] = $params['upOrderId'];  //上游订单号
	$package['lowOrderId'] = $params['ordersn'];   //下游订单号
	//转换key=value&key=value;
	$str = tostring($package);
	//拼接加密字串
	$str .= '&key=' . $config['key'];
	//MD5加密字串
	$sign = md5($str);
	//返回加密字串转换成大写字母
	$package['sign'] = strtoupper($sign);
	//数据包转换成json格式
	$data =  json_encode($package);
	//数据请求地址，post形式传输
	$url = 'https://ipay.833006.net/tgPosp/services/payApi/reverse';
	//数据请求地址，post形式传输
	$response = ihttp_posts($url,$data);

	//解析json数据
	$response = json_decode($response,TRUE);
	
	file_put_contents('./log/ordQuery.txt', print_r($response,TRUE),FILE_APPEND);
	//直接返回支付URL地址
//	return $response->pay_url;
	//返回数组
	return $response;
}


/**
 * 通莞微信公众号原生支付。
 * @params 订单信息数组
 * @config  配置数据
 * 
 */
function Tgwechat_jsapi($params, $config) {

	$wOpt = array();
	$package = array();
	$package['account'] = $config['account'];
	$package['payMoney'] = $params['payMoney'];
	$package['lowOrderId'] = $params['lowOrderId'];
	$package['body'] = $params['body'];
	$package['notifyUrl'] = $params['notifyUrl'];
	$package['openId'] = $params['openId'];
	//转换key=value&key=value;
	$str = tostring($package);
	//拼接加密字串
	$str .= '&key=' . $config['key'];
	//MD5加密字串
	$sign = md5($str);
	//返回加密字串转换成大写字母
	$package['sign'] = strtoupper($sign);
	//数据包转换成json格式
	$data =  json_encode($package);
	//数据请求地址，post形式传输
	$url = 'https://ipay.833006.net/tgPosp/payApi/wxJspay';
//	测试地址
//	$url = 'http://tgjf.833006.biz/tgPosp/payApi/wxJspay';

	//数据请求地址，post形式传输
	$response = ihttp_posts($url,$data);
	//解析json数据
	$response = json_decode($response,TRUE);
	//解析pay_info json数据！
	$payinfo = json_decode($response['pay_info'],TRUE);

	$wOpt['appId'] = $payinfo['appId'];
	$wOpt['timeStamp'] = $payinfo['timeStamp'];
	$wOpt['nonceStr'] = $payinfo['nonceStr'];
	$wOpt['package'] = $payinfo['package'];
	$wOpt['signType'] = 'MD5';
	$wOpt['paySign'] = $payinfo['paySign'];
	//返回支付参数数组
	return $wOpt;
}

/**
 * 订单查询
 * @parmas  订单号
 * @config  配置信息
 *  下游订单号lowOrderId与通莞金服订单号upOrderId二选一。
 */
function orderQuery($params,$config) {
	
	$query = array(
		'account'=>$config['account'],
		'lowOrderId'=>$params['lowOrderId'],//下级订单号
	);
	//转换key=value&key=value;
	$str = tostring($query);
	//拼接加密字串
	$str .= '&key=' . $config['key'];
	//字串加密
	$sign = md5($str);
	//加密字串转大写
	$query['sign'] = strtoupper($sign);
	//数组数据编码为json格式数据
	$query = json_encode($query);
	//查询请求地址
	$url = 'https://ipay.833006.net/tgPosp/services/payApi/orderQuery';
//	$url = 'http://tgjf.833006.biz/tgPosp/services/payApi/orderQuery';//测试地址
	//发送请求
	$result = ihttp_posts($url, $query);
	//
	$result = json_decode($result,TRUE);
	
	return $result;	
}


/**
 * 通莞微信扫码支付。
 * @params  订单支付信息
 * @config  配置信息
 */
function Tgwechat_scode($params, $config) {

	$package = array();
	$package['account'] 	=   $config['account'];
	$package['payMoney'] 	= 	$params['payMoney'];
	$package['lowOrderId']  = 	$params['ordersn'];
	$package['body'] 		= 	$params['body'];
	$package['notifyUrl'] 	= 	'http://shop.gogo198.cn/payment/wechat/tgwechatnotify.php';//后台回调地址
	$package['payType'] 	= 	'0';//
	//转换key=value&key=value;
	$str = tostring($package);	
	//拼接加密字串
	$str .= '&key=' . $config['key'];
	//MD5加密字串
	$sign = md5($str);
	//返回加密字串转换成大写字母
	$package['sign'] = strtoupper($sign);
	//数据包转换成json格式
	$data =  json_encode($package);
	//数据请求地址，post形式传输
	$url = 'https://ipay.833006.net/tgPosp/services/payApi/unifiedorder';
//	测试地址
//	$url = 'http://tgjf.833006.biz/tgPosp/services/payApi/unifiedorder';
	//数据请求地址，post形式传输
	$response = ihttp_posts($url,$data);
	//解析json数据
	$response = json_decode($response,TRUE);
	
	file_put_contents('./log/WechatScode.txt', print_r($response,TRUE),FILE_APPEND);
	
	//返回json数据参数（sign,message,status,codeUrl,account,orderID,lowOrderId)
	return $response;
}


/**
 * 通莞支付宝扫码支付。
 * @params 订单支付信息
 * @config  配置信息
 */
function Tgalipay_scode($params, $config) {

	$package = array();
	$package['account'] 	= $config['account'];
	$package['payMoney'] 	= $params['payMoney'];
	$package['lowOrderId']  = $params['ordersn'];
	$package['body'] 		= $params['body'];
	$package['notifyUrl'] 	= 'http://shop.gogo198.cn/payment/wechat/tgalipaynotify.php';//后台回调地址
	$package['payType'] 	= '1';//
	//转换key=value&key=value;
	$str = tostring($package);	
	//拼接加密字串
	$str .= '&key=' . $config['key'];
	//MD5加密字串
	$sign = md5($str);
	//返回加密字串转换成大写字母
	$package['sign'] = strtoupper($sign);
	//数据包转换成json格式
	$data =  json_encode($package);
	//数据请求地址，post形式传输
	$url = 'https://ipay.833006.net/tgPosp/services/payApi/unifiedorder';
//	测试地址
//	$url = 'http://tgjf.833006.biz/tgPosp/services/payApi/unifiedorder';
	//数据请求地址，post形式传输
	$response = ihttp_posts($url,$data);
	//解析json数据
	$response = json_decode($response,TRUE);
	
	file_put_contents('./log/Tgalipay_scode.txt', print_r($response,TRUE),FILE_APPEND);
	
	//返回json数据参数（sign,message,status,codeUrl,account,orderID,lowOrderId)
	return $response;
}

/**
 * 通莞支付对账文件下载。
 * @params 订单支付信息
 * @config  配置信息
 */
function Tg_loadBill($params, $config) {
	
	$appId='tgkj22493580';
	$key='cef9ea4f0ed2cf9352ed6c23d7734345';
	$package = array();
	$package['api'] = $config['account'];
	$package['appId'] = $appId;
	$package['fromDate'] = $params['ordersn'];
	$package['toDate'] = $params['body'];
	$package['merId'] = 11;
	$package['method'] = 11;
	
	$package['notifyUrl'] = 'http://shop.gogo198.cn/payment/wechat/tgalipaynotify.php';//后台回调地址
	$package['payType'] = '1';//
	//转换key=value&key=value;
	$str = tostring($package);	
	//拼接加密字串
	$str .= '&key=' . $config['key'];
	//MD5加密字串
	$sign = md5($str);
	//返回加密字串转换成大写字母
	$package['sign'] = strtoupper($sign);
	//数据包转换成json格式
	$data =  json_encode($package);
	//数据请求地址，post形式传输
	$url = 'https://ipay.833006.net/tgPosp/services/payApi/unifiedorder';
//	测试地址
//	$url = 'http://tgjf.833006.biz/tgPosp/services/payApi/unifiedorder';
	//数据请求地址，post形式传输
	$response = ihttp_posts($url,$data);
	//解析json数据
	$response = json_decode($response,TRUE);
	
	file_put_contents('./log/loadBill.txt', print_r($response,TRUE),FILE_APPEND);
	
	//返回json数据参数（sign,message,status,codeUrl,account,orderID,lowOrderId)
	return $response;
}



/**
 * 通莞快捷支付。
 * @params 订单支付信息
 * @config  配置信息
 */
function TgUnionpay($params, $config) {
	
	$package = array();
	$package['account'] = '13974747474';//商户号  测试账号
//	$package['account'] = $config['account'];
	$package['payMoney'] = $params['payMoney'];//订单交易金额
	$package['lowOrderId'] = $params['ordersn'];//订单编号
	$package['body'] = $params['body'];//商品描述
//	$package['returnUrl'] = 'https://www.baidu.com/';
//	$package['returnUrl'] = $params['returnUrl'];//前端同步回调
	$package['returnUrl'] = 'http://shop.gogo198.cn/app/index.php?i=14&c=entry&m=ewei_shopv2&do=mobile&r=parking.pay';
	$package['notifyUrl'] = 'http://shop.gogo198.cn/payment/wechat/tgunionpaynotify.php';//后台回调地址
	//转换key=value&key=value;
	$str = tostring($package);
	//拼接加密字串
//	$str .= '&key=' . $config['key'];
	$str .= '&key=5f61d7f65b184d19a1e006bc9bfb6b2f';//测试秘钥
	//MD5加密字串
	$sign = md5($str);
	//返回加密字串转换成大写字母
	$package['sign'] = strtoupper($sign);
	//数据包转换成json格式
	$data =  json_encode($package);
	//数据请求地址，post形式传输
//	$url = 'https://ipay.833006.net/tgPosp/services/gatewayPayApi/gatewayPay';
	
	$url = 'http://tgjf.833006.biz/tgPosp/services/gatewayPayApi/gatewayPay'; //测试地址
	//数据请求地址，post形式传输
	$response = ihttp_posts($url,$data);
	//解析json数据
	$responses = json_decode($response,TRUE);
	
	file_put_contents('./log/Unionpay.txt', print_r($responses,TRUE),FILE_APPEND);
	//直接返回支付URL地址
//	return $response->payUrl;
	return $responses;
}


/**
 * 银联支付测试
 */
  function TgUnionpayt() {
	
	$package = array();
//	$package['account'] = '13974747474';//商户号
//	$package['account'] = $config['account'];
	$package['account'] = '101570223660';
	$package['payMoney'] = '1.01';//订单交易金额
	$package['lowOrderId'] = 'G99198商务号20171211156647620';//订单编号
	$package['body'] = '停车服务';//商品描述
//	$package['returnUrl'] = 'https://www.baidu.com/';
//	$package['returnUrl'] = $params['returnUrl'];//前端同步回调
	$package['returnUrl'] = 'http://shop.gogo198.cn/app/index.php?i=14&c=entry&m=ewei_shopv2&do=mobile&r=parking.pay';
	$package['notifyUrl'] = 'http://shop.gogo198.cn/payment/wechat/tgunionpaynotify.php';//后台回调地址
	//转换key=value&key=value;
	$str = tostring($package);
	//拼接加密字串
//	$str .= '&key=' . $config['key'];
//	$str .= '&key=b4f16b4526b046c580e363fcfcd07c82';//KEYS1
	$str .= '&key='.KEYS1;//KEYS1
//	$str .= '&key=5f61d7f65b184d19a1e006bc9bfb6b2f';
	//MD5加密字串
	$sign = md5($str);
	//返回加密字串转换成大写字母
	$package['sign'] = strtoupper($sign);
	//数据包转换成json格式
	$data =  json_encode($package);
	//数据请求地址，post形式传输
	$url = 'https://ipay.833006.net/tgPosp/services/gatewayPayApi/gatewayPay';
	
//	$url = 'http://tgjf.833006.biz/tgPosp/services/gatewayPayApi/gatewayPay'; //测试地址
	//数据请求地址，post形式传输
	$response = ihttp_posts($url,$data);
	//解析json数据
	$responses = json_decode($response,TRUE);
	
	file_put_contents('./log/Unionpayt.txt', print_r($responses,TRUE),FILE_APPEND);
	//直接返回支付URL地址
//	return $response->payUrl;
//	return $responses;
	print_r($responses);
}





/**
 * @数据请求提交POST json
 * @$url:请求地址
 * @post_data:请求数据
 */
function ihttp_posts($url,$post_data) {
	//初始化	 
	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // 跳过证书检查
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, true);  // 从证书中检查SSL加密算法是否存在
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
	curl_setopt($ch, CURLOPT_POSTFIELDS,$post_data);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Content-Length: ' . strlen($post_data)));
	$result = curl_exec($ch);
	curl_close($ch);
	return $result;
}

function httpspost($url,$post_data){
	$curl = curl_init(); // 启动一个CURL会话
	curl_setopt($curl, CURLOPT_URL, $url); // 要访问的地址
	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0); // 对认证证书来源的检查
	curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 1); // 从证书中检查SSL加密算法是否存在
	curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']); // 模拟用户使用的浏览器
	curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Content-Length: ' . strlen($post_data)));
	curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1); // 使用自动跳转
	curl_setopt($curl, CURLOPT_AUTOREFERER, 1); // 自动设置Referer
	curl_setopt($curl, CURLOPT_POST, 1); // 发送一个常规的Post请求
	curl_setopt($curl, CURLOPT_POSTFIELDS, $post_data); // Post提交的数据包
	curl_setopt($curl, CURLOPT_TIMEOUT, 30); // 设置超时限制防止死循环
	curl_setopt($curl, CURLOPT_HEADER, 0); // 显示返回的Header区域内容
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); // 获取的信息以文件流的形式返回
	$tmpInfo = curl_exec($curl); // 执行操作
	if (curl_errno($curl)) {
		echo 'Errno'.curl_error($curl);//捕抓异常
	}
	curl_close($curl); // 关闭CURL会话
	return $tmpInfo; // 返回数据，json格式
}

/**
 * 字符串拼接
 * @arrs :数组数据
 */
function tostring($arrs) {
	ksort($arrs, SORT_STRING);
	$str = '';
	foreach ($arrs as $key => $v ) {
		if ($v=='' || $v == null) {
			continue;
		}
		$str .= $key . '=' . $v . '&';
	}
	$str = trim($str,'&');
	return $str;
}
?>