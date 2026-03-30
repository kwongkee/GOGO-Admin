<?php

	header('Content-Type:text/html;charset=utf-8');
	
	$params = array();
	$params['tid'] = 'SH'.date('YmdHis',time()).time();
	$params['fee'] = 1;
//	$params['openid'] = '121234sdfsf';
	$params['title'] = '订单支付';
	
	//配置参数
	$options = array();
//	$options['mchid'] = '13974747474';
//	$options['key'] = '5f61d7f65b184d19a1e006bc9bfb6b2f';
	
	$options['mchid'] = '101540254006';
	$options['key'] = 'f8ee27742a68418da52de4fca59b999e';
	
	$res = Tgwechat_h5($params, $options);
	echo "<pre>";
	print_r($res);


	/**2017-12-14
	 * 通莞微信公众号支付。
	 * @params 订单支付信息
	 * @config  配置信息
	 */
	function Tgwechat_h5($params, $config) {
	
		$package = array();
		$package['account'] = $config['mchid'];
		$package['payMoney'] = $params['fee'];
		$package['lowOrderId'] = $params['tid'];
		$package['body'] = $params['title'];
		$package['notifyUrl'] = 'http://shop.gogo198.cn/payment/tgpay/notify.php';//后台回调地址
//		$package['openId'] = $params['openid'];
		//转换key=value&key=value;
		$str = tostrings($package);
	
//		ksort($package, SORT_STRING);
//		$str = '';
//		foreach ($package as $key => $v ) {
//			if ($v=='' || $v == null) {
//				continue;
//			}
//			$str .= $key . '=' . $v . '&';
//		}
//		$str = trim($str,'&');
		
		//拼接加密字串
		$str .= '&key=' . $config['key'];
		//MD5加密字串
		$sign = md5($str);
		//返回加密字串转换成大写字母
		$package['sign'] = strtoupper($sign);
		//数据包转换成json格式
		$data =  json_encode($package);
		
		//数据请求地址，post形式传输
		$url = 'https://ipay.833006.net/tgPosp/services/payApi/wapPay';
//		$url = 'http://tgjf.833006.biz/tgPosp/services/payApi/wapPay'; //测试地址
		//数据请求地址，post形式传输
		$response = ihttp_postJson($url,$data);
	
		//解析json数据
		$response = json_decode($response,TRUE);
		
	//	file_put_contents('./log/wechatRes.txt', print_r($response,TRUE),FILE_APPEND);
		//直接返回支付URL地址
	//	return $response->pay_url;
		//返回数组
		return $response;
	}

	//2017-10-29
	function tostrings($arrs) {
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

	function ihttp_postJson($url,$post_data) {
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