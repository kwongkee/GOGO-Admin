<?php
header('Content-Type:text/html;charset=utf-8');
define('IN_MOBILE', true);
define('PDO_DEBUG', true);
require_once '../../framework/bootstrap.inc.php';
require_once '../../app/common/bootstrap.app.inc.php';
load()->app('common');
load()->app('template');
//加载发送消息函数  diysend
load()->func('diysend');
//设置时区
date_default_timezone_set('Asia/Shanghai');
  
if (!empty($_POST) && isset($_POST['token'])) {
	//写入日志
	$_POST['times'] = date('Y-m-d H:i:s',time());
	@file_put_contents('./log/Refund1'.date('Ym',time()).'.txt', print_r($_POST,TRUE),FILE_APPEND);
	/**
	 * 支付类型数组；微信退款
	 */
	//退款金额
	$refundMoneys = sprintf("%.2f",trim($_POST['refundMoney']));
	
	$token = trim($_POST['token']);
	$type=['wechat','alipay','unionpay','refund','Test'];
	//验证支付类型是否一致
	if (in_array($token, $type)) {//判断接收到的支付类型是否在数组中
		//订单编号
		$ordersn = trim($_POST['ordersn']);
		//查询字段
		/*$filed = 'a.ordersn,a.uniacid,a.pay_account,a.user_id,b.upOrderId,b.IsWrite ';
		//查询条件
		$find = array(':ordersn' => $ordersn, ':pay_status' =>1);
		//执行查询
		$order = pdo_fetch("SELECT ".$filed." FROM ".tablename('foll_order')." a LEFT JOIN ".tablename('parking_order')." b ON a.ordersn = b.ordersn WHERE a.ordersn = :ordersn AND a.pay_status = :pay_status ORDER BY a.id desc LIMIT 1",$find);
		*/

		$filed = 'ordersn,uniacid,pay_account,user_id,upOrderId,IsWrite,pay_type,pay_status';
		$find = array(':ordersn' => $ordersn, ':pay_status' =>1);
		$order = pdo_fetch("SELECT ".$filed." FROM ".tablename('foll_order')." WHERE upOrderId!='' AND ordersn = :ordersn AND pay_status = :pay_status LIMIT 1",$find);
		//日志
		@file_put_contents('./log/Order1.txt', print_r($order,TRUE),FILE_APPEND);
		//查询退款表中的ID，条件：订单号一致，并状态等于100  代表退款成功！

		if(empty($order)){
				exit(json_encode(['code'=>0,'status'=>0,'codes'=>0,'msg'=>'无该订单数据，请检查!']));
		}
		
		if(!empty($order) && (($order['IsWrite'] == 100) || ($order['IsWrite'] == 103))){
				exit(json_encode(['code'=>0,'status'=>0,'codes'=>0,'msg'=>'该订单已退款，请稍后查询!']));
		}

		//退款金额 
		$RefundMoney = trim($_POST['refundMoney'])>0 ? sprintf("%.2f",trim($_POST['refundMoney'])) : $order['pay_account'];
		//获取支付配置参数
		$configs = pdo_get('pay_config',array('uniacid'=>$order['uniacid']),array('config'));
		//反序列获取数组；
		$payconfig = json_decode($configs['config'],true);
		//配置 
		if(is_array($payconfig) && !empty($payconfig)) {
			
			$config = array(
				'account'       =>	$payconfig['tg']['mchid'],
				'key'			=>	$payconfig['tg']['key'],
			);
			
		}
		
		
		/**
		 * 退款路径
		 */
		if($order['pay_type'] == 'wechat'){
			$RefundType  = '微信支付';
		}else if($order['pay_type'] == 'alipay'){
			$RefundType  = '支付宝支付';
		}


		// 商户退款订单号
        $lowRefundNo = OrderNo();
		
		$refundArr = [
			'account'			=>	$config['account'],//商户ID
			'uniacid'			=>	$order['uniacid'],//公众号ID
			'openid'			=>	$order['user_id'],//用户Openid
			'type'				=>  'tgpay',//退款接口类型
			'ordersn'			=>	$order['ordersn'],//订单编号
			'lowRefundNo'		=>	$lowRefundNo,//商户退款订单号
			'upOrderId'		=>	$order['upOrderId'],//上游订单号
			'refundMoney'	=>  $RefundMoney,//退款金额
			'payMoney'		=>	$order['pay_account'],//原来订单金额
			'create_date'	=>	time(),//退款创建时间 
			'status'			=>	'99',//退款状态   退款失败：99，成功：100，订单已退款：101，已撤销：102，重复操作：103
		];
		
		/**
		 * 将支付订单写入parking_refund表中
		 */
		$old = pdo_insert('parking_refund', $refundArr);
		if (!empty($old)) {
			//parking_refund 自增ID
		  $oldid = pdo_insertid();
			
			switch($_POST['token']) {
				
					case 'refund';
							//$Refund = Tgwechat_Refund($refundArr,$config);	//微信退款
							$Refund = WechatRefund($refundArr,$config);	//微信退款
							file_put_contents('./log/Refunds.txt', print_r($Refund,TRUE),FILE_APPEND);

							//if(!empty($Refund) && ($Refund['status'] == '100') && ($Refund['message'] == '已转入退款' || $Refund['message'] == '已转入部分退款') ) {//判断返回参数
							if(!empty($Refund) && ($Refund['status'] == '100')) {//判断返回参数

								$arrs['status']  = 	$Refund['status'];
								$arrs['msg'] 		 =	$Refund['message'];
								$arrs['up_date'] =	time();
								
								//原交易金额等于退款金额       状态为全额退款
								if($order['pay_account'] == $RefundMoney){
										$Refu['IsWrite']  = 100;//退款状态
								} else if($RefundMoney < $order['pay_account']) {//退款金额小于交易金额为部分退款
										$Refu['IsWrite']  = 103;//退款状态
								}
								$Refu['WriteSeq'] 		= $Refund['upOrderId'];//退款流水号
								$Refu['RefundMoney']  = $RefundMoney;//退款金额
								$Refu['PlatDate'] 		= time();//交易时间
								
								$res['codes']  = 1;
								$res['status'] = 100;
								$res['msg']    = $Refund['message'];
								
								//退款消息模板
								$sendArr = array(
									'first'=>'您好！退款已提交原路退还您的支付账号',
									'touser' 	   => $refundArr['openid'],//接收消息的用户	a
									'uniacid' 	 => $refundArr['uniacid'],//公众号ID	a
									'RefundType' => $RefundType,//退款通道
//									'RefundMoney'=> $refundArr['payMoney'],//退款金额
                                    'RefundMoney'=>$RefundMoney,
									'RefundDate' => date('Y-m-d H:i:s',time()),//退款提交时间
									'remark'	=>'退款到账详情，请查看支付账户及咨询退款通道客服'
								);
								sendMsgRefund($sendArr);//退款消息模板发送
								
							} else {//退款失败
								
								$arrs['status']  = 	$Refund['status'];
								$arrs['msg']     =	$Refund['message'];
								$arrs['up_date'] =	time();
								
								$Refu['IsWrite']  		 = 102;//退款状态
								$Refu['RefundMoney']     = $RefundMoney;//退款金额
								$Refu['PlatDate'] 		 = time();//交易时间
								
								$res['codes']  = 0;
								$res['status'] = 0;
								$res['msg']    = $Refund['message'];
							}
							
							try{
							
								pdo_begin();//开启事务
									
									//按条件更新到退款订单表中，更新状态，退款时间；
									pdo_update('parking_refund', $arrs, array('id' => $oldid));
									//修改停车表的退款记录
									//pdo_update('parking_order', $Refu, array('ordersn' => $ordersn));
									pdo_update('foll_order', $Refu, array('ordersn' => $ordersn));

								pdo_commit();//提交事务
							}catch(PDOException $e){
								pdo_rollback();//执行失败，事务回滚
							}
						
						exit(json_encode($res));
							
					break;
			}
			
		}else {
			exit(json_encode(['code'=>0,'status'=>0,'codes'=>0,'msg'=>'sqlError']));
		}
	}
}else {
	exit(json_encode(['code'=>0,'status'=>0,'codes'=>0,'msg'=>'No Request Data']));
}


/**
 * 通莞微信撤销订单。
 * @params 订单支付信息
 * @config  配置信息
 */
function Tgwechat_Refund($params, $config) {

	$package = array();
	$package['account'] = $config['account'];
	$package['upOrderId'] = $params['upOrderId'];
	$package['refundMoney'] = $params['refundMoney'];
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
	//$url = 'http://tgjf.833006.biz/tgPosp/services/payApi/refund'; //测试地址
	//数据请求地址，post形式传输
	$response = ihttp_posts($url,$data);

	//解析json数据
	$response = json_decode($response,TRUE);
	
	file_put_contents('./log/Rpublic.txt', print_r($response,TRUE),FILE_APPEND);
	//直接返回支付URL地址
	//return $response->pay_url;
	//返回数组
	return $response;
}


// 微信退款，支持多次退款   2019-11-04
function WechatRefund($params, $config)
{
    $package = array();
    $package['account']     = $config['account'];
    $package['lowRefundNo'] = $params['lowRefundNo'];
    $package['upOrderId']   = $params['upOrderId'];
    $package['refundMoney'] = $params['refundMoney'];// ABCDEFGHIJKLMNOPQRESTUVWXYZ
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
    file_put_contents('./log/Rpublic.txt', $data."\r\n",FILE_APPEND);
    //数据请求地址，post形式传输
    $url = 'https://ipay.833006.net/tgPosp/payApi/reverse/v2';
    //$url = 'http://tgjf.833006.biz/tgPosp/payApi/reverse/v2'; //测试地址
    //数据请求地址，post形式传输
    $response = ihttp_posts($url,$data);
    //解析json数据
    $response = json_decode($response,TRUE);
    file_put_contents('./log/Rpublic.txt', print_r($response,TRUE),FILE_APPEND);
    //直接返回支付URL地址
    //return $response->pay_url;
    //返回数组
    return $response;

}

// 生成退款订单号  2019-11-04
function OrderNo() {
    return 'Ref'.date('YmdH',time()).str_pad(mt_rand(1,999999),5,'0',STR_PAD_LEFT).substr(microtime(),2,6);
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
?>