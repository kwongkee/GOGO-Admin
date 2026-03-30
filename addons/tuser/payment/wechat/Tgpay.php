<?php
define('IN_MOBILE', true);
require './func/paycomm.php';
//load()->app('common');
//load()->app('template');
//加载数据库操作函数
//load()->func('pdo');

if (!empty($_POST)) {
	$receive = array(
		'ordersn'=>$_POST['ordersn'],//订单编号
		'Openid'=>$_POST['Openid'],//用户Openid
		'copyName'=>$_POST['copyName'],//公司名称，对应公众号的名称
		'body'=>$_POST['body'],//商品描述
		'payMoney'=>$_POST['payMoney'],//交易金额
		'Type'=>$_POST['Type'],//交易类型
		'payTime'=>$_POST['payTime'],//订单时间
		'payUrl' => $_POST['payUrl'],//结果回调地址。2017-11-06
	);
	$paytype = $_POST['Type'];//支付类别，微信，支付宝，闪付
//	file('./log/receive.txt',print_r($receive,TRUE),FILE_APPEND);
	$signs = toreceive($receive);//接收字串拼接
	$sign = strtoupper(md5($signs));//加密匹配
	
	/*$params = array(
		'payMoney'=> '0.01',
		'lowOrderId'=>'GG'.date('Ymd',time()).rand(1, time()),
		'body'=>'gogo',
//		'notifyUrl'=>'http://shop.gogo198.cn/payment/wechat/tgnotify.php',
		'openId'=>'om44I1X2wIlTWdN3pSsgoJkfMLtI',
	);*/
	//数据验证
	if ($_POST['sign'] == $sign) {
		//添加数据到order表中，再添加数据到
		
		
		
		//组装数据
		$params = array(
			'payMoney'=> $_POST['payMoney'],
			'lowOrderId'=>'GG'.date('Ymd',time()).rand(1, time()),
			'body'=>$_POST['body'],
			'openId'=>'om44I1X2wIlTWdN3pSsgoJkfMLtI',//$_POST['Openid']
		);
		file('./log/params.txt',print_r($params,TRUE),FILE_APPEND);
		//配置
		$config = array(
			'account'=>'13974747474',
			'key'=>'5f61d7f65b184d19a1e006bc9bfb6b2f',
		);

		switch($paytype){
			case 'wechat'://通莞微信支付
				$params['notifyUrl'] = 'http://shop.gogo198.cn/payment/wechat/tgwechatnotify.php';//后台回调地址
				$payurl = Tgwechat_public($params,$config);	//微信公众号支付
//				echo $payurl;die;		
				if(!empty($payurl)) {//判断返回参数
//					Header("HTTP/1.1 303 See Other"); 
//					Header("Location: $payurl");
//					echo $payurl;
					$arrs = [
						'status'=>$payurl,
						'message'=>'数据返回成功'
					];
					echo json_encode($arrs);
					exit;
				}else {
					$arrs = [
						'status'=>'error',
						'message'=>'数据返回失败'
					];
					echo json_encode($arrs);
				}
				break;
			case 'alipay'://通莞支付宝
					$params['notifyUrl'] = 'http://shop.gogo198.cn/payment/wechat/tgalipaynotify.php';//后台回调地址
					$payurl = Tgalipay_scode($params,$config);
					$pay = $payurl->codeUrl;
					if(isset($pay)){
						$arrs = [
							'status'=>$pay,
							'message'=>'数据返回成功'
						];
						echo json_encode($arrs);
					
					}else {
						
						$arrs = [
							'status'=>'error',
							'message'=>'数据返回失败'
						];
						echo json_encode($arrs);
					}
					
					exit;				
				break;
			case 'unionpay'://银联闪付
				
				break;
		}		
	}else {
		$arrs = [
			'status'=>'fail',
			'message'=>'数据验证失败'
		];
		echo json_encode($arrs);
	}
} else {
	echo 'abcdef';
}

/**
 * 接收字串拼接
 * @arrs 接收数组参数；
 */
function toreceive($arrs){
	krsort($arrs);
	$str = '';
	foreach($arrs as $key=>$val){
		$str .= $key . '=' . $val . '&';
	}
	$str = trim($str,'&');
	return $str;
}

//获取当前浏览器信息
//$user_agent = $_SERVER['HTTP_USER_AGENT'];
//if (strpos($user_agent, 'MicroMessenger') !== false) {
//	
//	
//}else{
//}

?>
<!--
 <script src="http://res.wx.qq.com/open/js/jweixin-1.0.0.js">
	document.addEventListener('WeixinJSBridgeReady', function onBridgeReady() {

		WeixinJSBridge.invoke('getBrandWCPayRequest', {

			'appId' : '<?php echo $wOpt['appId'];?>',

			'timeStamp': '<?php echo $wOpt['timeStamp'];?>',

			'nonceStr' : '<?php echo $wOpt['nonceStr'];?>',

			'package' : '<?php echo $wOpt['package'];?>',

			'signType' : '<?php echo $wOpt['signType'];?>',

			'paySign' : '<?php echo $wOpt['paySign'];?>'

		}, function(res) {

			if(res.err_msg == 'get_brand_wcpay_request:ok') {

				location.search += '&done=1';

			} else {

//				alert('启动微信支付失败, 请检查你的支付参数. 详细错误为: ' + res.err_msg);

				history.go(-1);

			}

		});

	}, false);

</script -->