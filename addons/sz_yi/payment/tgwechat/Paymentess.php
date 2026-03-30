<?php
header('Content-Type:text/html;charset=utf-8');
define('IN_MOBILE', true);
define('PDO_DEBUG', true);
/*require_once '../../framework/bootstrap.inc.php';
require_once '../../app/common/bootstrap.app.inc.php';
//load()->app('common');
load()->app('template');
//加载发送消息函数  diysend
load()->func('diysend');
global $_W;
global $_GPC;*/

//按订单号查询该订单信息发起支付
/**
 *
 * 商店微信聚合支付，正式使用
 * 开发步骤：主动支付；
 * 1、表单上传token  与订单号 ordersn:
 * 例如：token = wechat,
 * ordersn:GGPgogo198201712051009
 */

$input   = file_get_contents('php://input');
parse_str($input,$Arrdata);

file_put_contents('./log/POST.txt',json_encode($Arrdata)."\r\n",FILE_APPEND);

//解析数据
//$Arrdata = json_decode($input,true);
//传送json 数据

if (!empty($Arrdata) && isset($Arrdata['to'])) {
	//聚合支付：wechat：微信,alipay：支付宝,UnionPay：银联，退款：refund
	//免密授权：Fwechat:微信,Falipay：支付宝,FCreditCard：信用卡,FAgro：农商行
	$type=['wechat','alipay','unionpay','refund'];
	//验证支付类型是否一致
	if(in_array($Arrdata['to'], $type)) {//判断接收到的支付类型是否在数组中

		file_put_contents('./log/Pdata.txt', print_r($Arrdata,TRUE));
		$pay = Payment::instance();
		$uniacid = trim($Arrdata['uniacid']);
		//请求参数
		$params['tid'] 		= trim($Arrdata['tid']);
		$params['openid'] 	= trim($Arrdata['opid'])?trim($Arrdata['opid']):'11';
		$params['fee'] 		= trim($Arrdata['fee']);
		$params['title'] 	= trim($Arrdata['title']);
		$params['uniacid']  = $uniacid;
		//配置
		$config['account']  = trim($Arrdata['acc']);
		$config['key'] 		= trim($Arrdata['ky']);
		
		$project = trim($Arrdata['project']) ? trim($Arrdata['project']) : 'no';
		switch($Arrdata['to']) {
			case 'wechat'://微信公众号支付	
				$payurl = $pay->wechat($params,$config,$project);
				
				//判断返回参数
				if(!empty($payurl) && ($payurl['status'] == '100') ) {
					/*$arrs = [
						'status'	=> $payurl['status'],
						'pay_url'	=> isset($payurl['pay_url'])?$payurl['pay_url']:'',
						'pay_info'	=> isset($payurl['pay_info'])?$payurl['pay_info']:'',
						'msg'		=> 'success',
					];*/
					$payinfo = json_decode($payurl['pay_info'],true);
					//$urls    = "https://shop.gogo198.cn/app/index.php?i={$uniacid}&c=entry&p=index&do=shop&m=sz_yi";
					//$urls    = "https://shop.gogo198.cn/app/index.php?i={$uniacid}&c=entry&p=center&do=member&m=sz_yi";
					if($project == 'gaoyi')
					{
						$urls    = "http://manage.gaoyimall.net/app/index.php?i={$uniacid}&c=entry&m=ewei_shopv2&do=mobile&r=member";
					}else{
                        $urls    = "https://shop.gogo198.cn/app/index.php?i={$uniacid}&c=entry&p=easy_deliver_cart&do=shop&m=sz_yi";
					}
				}else {
					/*$arrs = [
						'status'=> $payurl['status'],
						'msg'   => $payurl['message'],
					];*/
					
					//echo json_encode($arrs);die;
					
					echo $payurl['message'];die;
				}
				
			break;

			case 'alipay'://通莞支付宝alipay
				$payurl = $pay->alipay($params,$config);
				if(!empty($payurl) && ($payurl['status'] == '100')){
					$arrs = [
						'status'	=>  $payurl['status'],
						'pay_url'	=>	$payurl['codeUrl'],
						'pay_info'	=>	isset($payurl['pay_info'])?$payurl['pay_info']:'',
						'msg'	=>'success',
					];
				}else {
					$arrs = [
						'status'  => $payurl['status'],
						'msg'	=>$payurl['message'],
					];
				}
				echo json_encode($arrs);die;
			break;
		}
		

	}else {
		$arrs = [
			'code'	=>'101',
			'msg'=>'数据验证失败',
		];
		echo json_encode($arrs);die;
	}
	
} else {
	$arrs = [
		'code'	=>'101',
		'msg'=>'未接收到请求',
	];
	echo json_encode($arrs);die;
}


class Payment {
	private static $instance;
	
	final private function __construct(){}
	//防止克隆
	final private  function __clone(){}
	
	static function instance() {
		if((self::$instance == null) && !(self::$instance instanceof self)){
			self::$instance = new self;	
		}
		return self::$instance;
	}

	// 测试通道使用
	public function test(){
        $package = array();
        $package['account']    = '101540254006';
        $package['payMoney']   = rand(1,9);
        $package['lowOrderId'] = 'GZ20191023'.rand(10);
        $package['body'] 	   = '测试订单';
        $package['notifyUrl']  = 'http://shop.gogo198.cn/addons/sz_yi/payment/tgwechat/notify.php';//后台回调地址
        //$package['returnUrl']  = 'http://shop.gogo198.cn/app/index.php?i=14&c=entry&m=ewei_shopv2&do=mobile&r=parking.msg';//成功回调地址；
        $package['openId'] 	   = 'oR-IB0t4Yc9zmV-K-_5NRB-u5k4U';
        //转换key=value&key=value;
        $str = $this->ArrTostring($package);
        //拼接加密字串
        $str .= '&key=f8ee27742a68418da52de4fca59b999e';
        //MD5加密字串
        $sign = md5($str);
        //返回加密字串转换成大写字母
        $package['sign'] = strtoupper($sign);
        //数据包转换成json格式
        $data =  json_encode($package);
        //请求报文
        file_put_contents('./log/postJson.txt',$data);

        //数据请求地址，post形式传输
        $url = 'https://ipay.833006.net/tgPosp/payApi/wxJspay';
        //数据请求地址，post形式传输
        $response = $this->JsonPost($url,$data);
        return $response;
        //解析json数据
        $response = json_decode($response,TRUE);
        //2018-07-06
        $response['c_time'] = date('Y-m-d H:i:s',time());
        $c  = array_merge($package,$response);
        file_put_contents('./log/wechat.txt', print_r($c,TRUE),FILE_APPEND);
        //返回数组
        return $response;
    }
	
	//微信支付
	public function wechat($params,$config,$project) {
		$package = array();
		$package['account']    = $config['account'];
		$package['appId']	   = 'wx76d541cc3e471aeb';
		$package['payMoney']   = $params['fee'];
		$package['lowOrderId'] = $params['tid'];
		$package['body'] 	   = $params['title'];
        
		if($project == 'gaoyi'){
			$package['notifyUrl']  = 'http://manage.gaoyimall.net/addons/ewei_shopv2/payment/tgwechat/notify.php';//后台回调地址
		}elseif($project == 'custompay'){
		    $package['notifyUrl']  = 'https://shop.gogo198.cn/addons/sz_yi/payment/tgwechat/customnotify.php';//自定义收款回调地址
		}elseif($project == 'onlinepayment'){
            $package['notifyUrl']  = 'https://shop.gogo198.cn/addons/sz_yi/payment/tgwechat/onlinenotify.php';//在线收款回调地址
        }elseif($project == 'gatherpay'){
            $package['notifyUrl']  = 'https://shop.gogo198.cn/addons/sz_yi/payment/tgwechat/gathernotify.php';//集运收款回调地址
        }elseif($project == 'gatherbalance'){
            $package['notifyUrl']  = 'https://shop.gogo198.cn/addons/sz_yi/payment/tgwechat/gatherbalancenotify.php';//集运余额充值回调地址
        }else{
			$package['notifyUrl']  = 'https://shop.gogo198.cn/addons/sz_yi/payment/tgwechat/notify.php';//后台回调地址
		}
		
		if($project == 'gaoyi'){
			$package['returnUrl']  = "http://manage.gaoyimall.net/app/index.php?i={$params['uniacid']}&c=entry&m=ewei_shopv2&do=mobile";
		}else{
			$package['returnUrl']  = "https://shop.gogo198.cn/app/index.php?i={$params['uniacid']}&c=entry&p=index&do=shop&m=sz_yi";
		}
		$package['openId'] 	   = $params['openid'];
		//$package['isMinipg']	= '2';
		//转换key=value&key=value;
		$str = $this->ArrTostring($package);
		//拼接加密字串
		$str .= '&key=' . $config['key'];
		//MD5加密字串
		$sign = md5($str);
		//返回加密字串转换成大写字母
		$package['sign'] = strtoupper($sign);
		//数据包转换成json格式
		$data =  json_encode($package);
		
		//请求报文
		file_put_contents('./log/postJson.txt',$data);
		//数据请求地址，post形式传输
		$url = 'https://ipay.833006.net/tgPosp/payApi/wxJspay';
		//数据请求地址，post形式传输
		$response = $this->JsonPost($url,$data);
		//解析json数据
		$response = json_decode($response,TRUE);
		//2018-07-06
		$response['c_time'] = date('Y-m-d H:i:s',time());
		$c  = array_merge($package,$response);
		file_put_contents('./log/wechat.txt', print_r($c,TRUE));
		//返回数组
		return $response;
	}
	
	
	//微信支付
	public function wechat1($params,$config) {
		$package = array();
		$package['account']    = $config['account'];
		$package['payMoney']   = $params['fee'];
		$package['lowOrderId'] = $params['tid'];
		$package['body'] 	   = $params['title'];
		$package['notifyUrl']  = 'http://shop.gogo198.cn/addons/sz_yi/payment/tgwechat/notify.php';//后台回调地址
		//$package['returnUrl']  = 'http://shop.gogo198.cn/app/index.php?i=14&c=entry&m=ewei_shopv2&do=mobile&r=parking.msg';//成功回调地址；
		$package['openId'] 	   = $params['openid'];
		//转换key=value&key=value;
		$str = $this->ArrTostring($package);
		//拼接加密字串
		$str .= '&key=' . $config['key'];
		//MD5加密字串
		$sign = md5($str);
		//返回加密字串转换成大写字母
		$package['sign'] = strtoupper($sign);
		//数据包转换成json格式
		$data =  json_encode($package);
		//请求报文
		file_put_contents('./log/postJson.txt',$data);
		
		//数据请求地址，post形式传输
		$url = 'https://ipay.833006.net/tgPosp/payApi/wxJspay';
		//数据请求地址，post形式传输
		$response = $this->JsonPost($url,$data);
		//解析json数据
		$response = json_decode($response,TRUE);
		//2018-07-06
		$response['c_time'] = date('Y-m-d H:i:s',time());
		$c  = array_merge($package,$response);
		file_put_contents('./log/wechat.txt', print_r($c,TRUE),FILE_APPEND);
		//返回数组
		return $response;
		
	}
	
	//支付宝支付
	public function alipay($params,$config) {
		$package = array();
		$package['account'] 	= $config['account'];
		$package['payMoney'] 	= $params['fee'];
		$package['lowOrderId']  = $params['tid'];
		$package['body'] 		= $params['title'];
		$package['notifyUrl'] 	= 'http://shop.gogo198.cn/addons/sz_yi/payment/tgalipay/notify.php';//后台回调地址
		$package['payType'] 	= '1';//
		//转换key=value&key=value;
		$str = $this->ArrTostring($package);	
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
		//数据请求地址，post形式传输
		$response = $this->JsonPost($url,$data);
		//解析json数据
		$response = json_decode($response,TRUE);
		$response['c_time'] = date('Y-m-d H:i:s',time());
		file_put_contents('./log/alipay.txt', print_r($response,TRUE),FILE_APPEND);
		return $response;
	}
	
	//字符串拼接
	protected function ArrTostring($arrs) {
		ksort($arrs);
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
	
	//数据请求
	protected function JsonPost($url,$post_data) {
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
}
?>

<!doctype html>
<html lang="en">
<head>
	<meta charset="UTF-8" />
	<title>微信支付</title>
	<meta http-equiv="Content-Type" content="text/html;charset=utf-8">
	<meta name="viewport" content="width=device-width,initial-scale=1.0,maximum-scale=1.0,user-scalable=0">
	<meta name="apple-mobile-web-app-capable" content="yes">
	<meta name="apple-mobile-web-app-status-bar-style" content="black">
	<meta name="format-detection" content="telephone=no">
</head>
<style type="text/css">
	body{padding: 0;margin:0;background-color:#4cb131;font-family: '黑体';}
	.pay-main{padding-top:45%;padding-left: 20px;padding-bottom: 20px;}
	.pay-main img{margin: 0 auto;display: block;}
	.pay-main .lines{margin: 0 auto;text-align: center;color:#cae8c2;font-size:16pt;margin-top: 10px;}
	.err{margin: 2px auto;text-align: center;color: red;font-size: 14pt;}
</style>

<body>
	
	<div class="conainer">
		<div class="pay-main">
			<img src="./img/pay_logo.png">
		<div class="lines"><span>微信安全支付，请耐心等待！</span></div>
		<div class="err"><span id="error"></span></div>
		</div>
	</div>
	
</body>
<script type="text/javascript">
	document.addEventListener('WeixinJSBridgeReady', function onBridgeReady() {
		WeixinJSBridge.invoke('getBrandWCPayRequest', {
			'appId': '<?php echo $payinfo['appId'];?>',
			'timeStamp': '<?php echo $payinfo['timeStamp'];?>',
			'nonceStr': '<?php echo $payinfo['nonceStr'];?>',
			'package': '<?php echo $payinfo['package'];?>',
			'signType': '<?php echo $payinfo['signType'];?>',
			'paySign': '<?php echo $payinfo['paySign'];?>'
		}, function(res) {
			if (res.err_msg == 'get_brand_wcpay_request:ok') {
            	document.getElementById('error').innerHTML = '支付成功';
            	location.href = '<?php echo $urls;?>';
           } else if(res.err_msg=='get_brand_wcpay_request:cancel') {
                document.getElementById('error').innerHTML = '取消支付';
                window.history.back()
            } else {
        		alert('启动微信支付失败, 请检查你的支付参数. 详细错误为: ' + res.err_msg);
        		window.history.back()
            }
		});
	}, false);
</script>
</html>