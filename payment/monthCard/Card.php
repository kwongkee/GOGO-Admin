<?php
header('Content-Type:text/html;charset=utf-8');
define('IN_MOBILE', true);
define('PDO_DEBUG', true);
define("KEYS","f8ee27742a68418da52de4fca59b999e");
define("KEYS1","b4f16b4526b046c580e363fcfcd07c82");
require_once '../../framework/bootstrap.inc.php';
require_once '../../app/common/bootstrap.app.inc.php';
load()->app('common');
load()->app('template');
//加载发送消息函数  diysend
load()->func('diysend');

//按订单号查询该订单信息发起支付
/**
 * 开发步骤：
 * 1、表单上传token  与订单号 ordersn:
 * 例如：token = wechat,
 * ordersn:GGPgogo198201712051009
 */
 /**
  * 接收数据：
  * 	'token' => wechat,//支付类型如：wechat,alipay,...
		'id' => id,//表中，自增ID
		
		'orderId'=>$_GPC['order'],//旧订单编号
  * 
  * 判断表中是否存在旧订单编号，
  * 1、有，修改对应的订单编号(直接拿订单信息支付);
  * 2、无，在表中添加新的数据(用新数据提交);
  */
  
  
if (!empty($_POST) && isset($_POST['token'])) {
	//可是
	file_put_contents('./log/GetPostdata.txt', print_r($_POST,TRUE),FILE_APPEND);
	/**
	 * 支付类型数组；
	 */
	$type=['wechat','alipay','unionpay','wechat_scode','refund'];
	//验证支付类型是否一致
//	if ($_POST['token'] == $parms['pay_type']) {
	if (in_array($_POST['token'], $type)) {//判断接收到的支付类型是否在数组中

//		$param = array('uniacid', 'openid','ordersn','upOrderId','PayAmount','body','pay_type','pay_status','create_time','returnUrl');

		// $parmsd = pdo_get('parking_month_pay', array('ordersn' => $_POST['id']), array());
        //
		// if(empty($parmsd)) {//如果订单表中存在该条订单
		//
		// 	$arrs = [
		// 		'status'=>0,
		// 		'msg'	=>'数据不能为空'
		// 	];
		// 	echo json_encode($arrs);die;
		// }
        $parmsd = [];
        $parmsd = pdo_get('card_member',['ordersn'=>$_POST['id']]);
        $parmsd['uniacid'] =14;
		$configs = pdo_get('pay_config',array('uniacid'=>$parmsd['uniacid']),array('config'));
		//反序列获取数组；
		$payconfig = json_decode($configs['config'],true);
		/**
		 * 'account'=>'13974747474',
			'key'=>'5f61d7f65b184d19a1e006bc9bfb6b2f',
		 */
		//配置 
		if(is_array($payconfig) && !empty($payconfig)) {
			
			$config = array(
				'account'=>$payconfig['tg']['mchid'],
				'key'=>$payconfig['tg']['key'],
			);

            $params = array(
                'payMoney'	=> $parmsd['money'],//支付金额
                'ordersn'	=> $parmsd['ordersn'],//支付订单号
                'payType'	=> $_POST['token'],//支付类型
                'body'		=> '路内停车月卡',//商品描述
                'openid'	=> $parmsd['openid'],//用户Openid   测试：om44I1X2wIlTWdN3pSsgoJkfMLtI
                'create_time' 	=> time(),//订单提交时间
                'account' 		=> $config['account'],
                'status' 		=> '0',//支付状态  0订单已提交，1支付成功，2支付失败
                //'returnUrl' => $receive['returnUrl'],//前端同步回调地址
            );

			//组装数据  支付订单写入old订单流水表
			// $params = array(
			// 	'payMoney'	=> $parmsd['pay_money'],//支付金额
			// 	'ordersn'	=> $parmsd['ordersn'],//支付订单号
			// 	'payType'	=> $_POST['token'],//支付类型
			// 	'body'		=> '路内停车月卡',//商品描述
			// 	'openid'	=> $parmsd['user_id'],//用户Openid   测试：om44I1X2wIlTWdN3pSsgoJkfMLtI
			// 	'create_time' 	=> time(),//订单提交时间
			// 	'account' 		=> $config['account'],
			// 	'status' 		=> '0',//支付状态  0订单已提交，1支付成功，2支付失败
			// 	//'returnUrl' => $receive['returnUrl'],//前端同步回调地址
			// );



			/**
			 * 将支付订单写入pay_old表中
			 */
			$old = pdo_insert('pay_old', $params);
			if (!empty($old)) {
			    $old = pdo_insertid();
			}else {
				$arrs = [
					'status'=>0,
					'msg'	=>'数据写入失败'
				];
				echo json_encode($arrs);die;
			}
		}


		/**
		 *分流判断： 
		 *
		 * $paytype 判断支付类型：wechat,alipay,unionpay，bestpay,Tgwechat_scode
		 */
		switch($_POST['token']) {

			case 'wechat'://通莞微信支付			
				$payurl = Tgwechat_public($params,$config);	//微信公众号支付
				if(!empty($payurl) && ($payurl['status'] == '100') ) {//判断返回参数
					$arrs = [
						'payurl'    => $payurl['pay_url'],
						'pay_info'  => $payurl['pay_info'],
						'msg'	    =>'success',
					];
				}else {
					$arrs = [
						'status'=>$payurl['status'],
						'msg'	=>$payurl['message'],
					];
				}
				echo json_encode($arrs);
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
						'msg'	=>$payurl['message'],
					];
				}
				echo json_encode($arrs);
				break;
		}

	}else {
		$arrs = [
			'status'=>0,
			'message'=>'数据验证失败',
		];
		echo json_encode($arrs);die;
	}
} else {
	$arrs = [
		'status'=>0,
		'msg'	=>'未接收到请求'
	];
	echo json_encode($arrs);die;
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
 * 通莞微信公众号支付。
 * @params 订单支付信息
 * @config  配置信息
 */
function Tgwechat_public($params, $config) {

	$package = array();
	// 2019-07-01
    $package['appId']       = 'wx01af4897eca4527e';
    $package['account'] 	= $config['account'];
	$package['payMoney'] 	= $params['payMoney'];
	$package['lowOrderId'] 	= $params['ordersn'];
	$package['body'] 		= $params['body'];
	$package['notifyUrl'] 	= 'http://shop.gogo198.cn/payment/monthCard/tgwechatnotify.php';//后台回调地址
	$package['openId'] 		= $params['openid'];
	//转换key=value&key=value;
	$str = tostring($package);
	//拼接加密字串
	$str .= '&key=' . $config['key'];
	//MD5加密字串
	$sign = md5($str);
	//返回加密字串转换成大写字母
	$package['sign'] = strtoupper($sign);
	file_put_contents('./log/McwechatPost.txt', print_r($package,TRUE));
	//数据包转换成json格式
	$data =  json_encode($package);
	//数据请求地址，post形式传输
	$url = 'https://ipay.833006.net/tgPosp/payApi/wxJspay';
	//数据请求地址，post形式传输
	$response = ihttp_posts($url,$data);
	//解析json数据
	$response = json_decode($response,TRUE);
	
	file_put_contents('./log/McwechatRes.txt', print_r($response,TRUE),FILE_APPEND);
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
 * 
 */
function orderQuery($params,$config) {
	
	$query = array(
		'account'=>$config['account'],
		'lowOrderId'=>$params['lowOrderId'],
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
 * 通莞支付宝扫码支付。
 * @params 订单支付信息
 * @config  配置信息
 */
function Tgalipay_scode($params, $config) {

	$package = array();
	$package['account'] = $config['account'];
	$package['payMoney'] = $params['payMoney'];
	$package['lowOrderId'] = $params['ordersn'];
	$package['body'] = $params['body'];
	$package['notifyUrl'] = 'http://shop.gogo198.cn/payment/monthCard/tgwechatnotify.php';//后台回调地址
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
	
	file_put_contents('./log/McalipayRes.txt', print_r($response,TRUE),FILE_APPEND);
	
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