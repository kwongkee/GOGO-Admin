<?php
// 测试订单提交  ftp测试
header('Content-Type:text/html;charset=utf-8');
//$params = array(
// 'ordersn'=>,
// 'body'   =>,
//);
//$config = array(
// 'key'=>'cef9ea4f0ed2cf9352ed6c23d7734345',
//);
//$url = 'http://shop.gogo198.cn/payment/wechat/Tgpay.php';
////文件下载
//$postdata = array(
//	'token' => 'loadBill',
//	'ordersn' => 'G99198101570223660201805165335',
//);

//http://shop.gogo198.cn/payment/wechat/test.php

//$postdata = array(
//	'ordersn'=>'GGPgogo198'.date('Ymd',time()).rand(1, time()),
//	'Openid'=>'oR-IB0t4Yc9zmV-K-_5NRB-u5k4U',//喜柏公众号Openid
////	'Openid'=>'om44I1X2wIlTWdN3pSsgoJkfMLtI',//通莞公众号Openid
////	'Openid' => 'oR-IB0ssN_p-54H9fxuWpUSDUx8w',
////	'copyName'=>'喜柏停车',
//	'body'=>'停车服务费',//商品描述
//	'payMoney'=>'0.01',//交易金额
//	'payType'=>'unionpay',//交易类型
//	'payTime'=> date('Ymdhi',time()).'至'.date('Ymdhi',time()),//交易时间
//	'payNotifyurl' => $url,//
//	'returnUrl' => 'https://www.baidu.com/',
//	'uniacid' => 14,
//);

//$receive['payTime'] = '2017-12-13 16:53:16';
//echo strtotime($receive['payTime']);

//$postdata = array(
//	'token' 	=> 'Tgwechat_scode',
//	'ordersn'   => 'G99198101570223660201805297775',
//);
//$res = ihttp_post($url, $postdata);
//print_r($res);
//print_r($result = json_decode($res,TRUE));

//header("location:".$result['payurl']);
//die;


//echo "<hr>";
//print_r($res);

//$res = json_decode($res);

//echo $res->payurl;
//if(!empty($res->payurl)){
//	echo "订单已下发，请查看[ 喜柏停车 ]公众号";
//}


//var_dump($res);ss
//header("location:".$res);

//location.href = 'https:\/\/qr.alipay.com\/bax02312ijrmsizyrnwe40e3';
//if(strpos($_SERVER['HTTP_USER_AGENT'],'MicroMessenger') !== false) {	 
//	header("location:".$res);	
//} else {		
//	require './pay.htm';//引用模板
//}



/**
 * 退款请求
 */
/*$url = 'http://shop.gogo198.cn/payment/wechat/refund.php';
$postdata = array(
	'token' => 'refund',
	'ordersn' => 'G99198101570223660201806233432',
//	'refundMoney'=>'6.00',
);
$res = ihttp_post($url, $postdata);
print_r($res);
echo '<br>';
$a = json_decode($res,true);
print_r($a);*/



/*$data = [
   [ 'id' => 1, 'name' => '你好，234', 'cate' => '生活日记'],
   [ 'id' => 2, 'name' => '79798', 'cate' => '摄影美图'],
   [ 'id' => 3, 'name' => '567567', 'cate' => '生活日记'],
];

$filtered = array_filter($data, function($item){ 
     return $item['id'] !== '2'; 
});
echo '<pre>';
print_r($filtered);*/


$sendArr = [

	'data'=>[
	
	    'touser'        =>'oR-IB0t4Yc9zmV-K-_5NRB-u5k4U',
	    'template_id'   =>'n7aQkN93Y-CeUBM491OMfzabqvLAWYmhG7awrIyyVNY',
	    'url'           =>'',
	    'data' 			=>[
	        'first'     =>array(
	            'value' => '您好，您的停车服务费扣费成功！',
	            'color' =>'#173177'
	            ),
	
	        'keyword1'  =>array(
	            'value' => '12小时22分',//停车时长 12小时22分
	            'color' =>'#436EEE'
	            ),
	
	        'keyword2'  =>array(
	            'value' =>'600 分钟',//实计时长：10小时20分钟
	            'color' =>'#173177'
	            ),
	
	        'keyword3'  =>array(
	            'value' => '￥5.00元',//应付金额：$12元
	            'color' =>'#173177'
	            ),
	
	        'keyword4'  =>array(
	            'value' => '-￥3.00元',//抵扣金额：-￥2元
	            'color' =>'#173177'
	            ),
	            
	        'keyword5'  =>array(
	            'value' => '￥2.00元',//实付金额：￥10元
	            'color' =>'#173177'
	            ),
	            
	        'remark'    =>array(
	            'value' => '欢迎您再次使用智能无感路内停车服务！',
	            'color' =>'#173177'
	            ), 
	        ],
	],//消息模板
	
	'uniacid'=>'14'
];

//echo $js = json_encode($sendArr);
//die;

$url = 'http://shop.gogo198.cn/foll/public/?s=api/send_wx_message';
$res = sendMsg($url,$sendArr);
echo '<pre>';
print_r($res);


function sendMsg($url,$data) {
	$curl = curl_init();
	curl_setopt_array($curl, array(
	  	CURLOPT_URL => $url,
	  	CURLOPT_RETURNTRANSFER => true,
	  	CURLOPT_ENCODING => "",
	  	CURLOPT_MAXREDIRS => 10,
	  	CURLOPT_TIMEOUT => 30,
	  	CURLOPT_CUSTOMREQUEST => "POST",
	  	CURLOPT_POSTFIELDS => json_encode($data),
	  	CURLOPT_HTTPHEADER => array(
		    "Cache-Control: no-cache",
		    "Content-Type: application/json",
	  	),
	));
	
	$response = curl_exec($curl);
	$err = curl_error($curl);
	curl_close($curl);
	return $response;
}

//拼接字串
function toreceive($arrs){
	krsort($arrs);
	$str = '';
	foreach($arrs as $key=>$val){
		$str .= $key . '=' . $val . '&';
	}
	$str = trim($str,'&');
	return $str;
}

function ihttp_post($url,$post_data) {
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
	$res = curl_exec($ch);
	curl_close($ch);
	return $res;
}
?>
