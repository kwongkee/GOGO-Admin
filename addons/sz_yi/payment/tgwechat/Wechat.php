<?php
/**
 * 商店聚合微信支付；
 * 2019-11-11
 * author: 赵金如
 */
header('Content-Type:text/html;charset=utf-8');
require '../../../../framework/bootstrap.inc.php';
require '../../../../addons/sz_yi/defines.php';
require '../../../../addons/sz_yi/core/inc/functions.php';
require '../../../../addons/sz_yi/core/inc/plugin/plugin_model.php';
require_once './Tgpay.php';

// 微信支付
// http://shop.gogo198.cn/addons/sz_yi/payment/tgwechat/Wechat.php
//echo $pay->test();// 显示数据

/**
 * 1、判断用户是否post传送数据；
 * 2、检查数据是否都存在；
 * 3、获取支付配置；
 * 4、进行支付请求；
 */

$input   = file_get_contents('php://input');
parse_str($input,$Arrdata);

file_put_contents('./log/WechatPost.txt',json_encode($Arrdata)."\r\n",FILE_APPEND);

if(!$_POST) {
    exit(json_encode(['code'=>0,'msg'=>'is No Post']));
}

// 检查数据
if(!isset($_POST['tid'])) {
    exit(json_encode(['code'=>0,'msg'=>'请填写订单编号']));
}
if(!isset($_POST['opid'])) {
    exit(json_encode(['code'=>0,'msg'=>'请填写用户Openid']));
}
if(!isset($_POST['fee'])) {
    exit(json_encode(['code'=>0,'msg'=>'请填写订单金额']));
}
if(!isset($_POST['title'])) {
    exit(json_encode(['code'=>0,'msg'=>'请填写商品名称']));
}
if(!isset($_POST['uniacid'])) {
    exit(json_encode(['code'=>0,'msg'=>'请填写公众号ID']));
}
if(!isset($_POST['returnUrl'])) {
    exit(json_encode(['code'=>0,'msg'=>'请填写微信支付完成返回地址！']));
}

$uniacid = trim($_POST['uniacid']);

// 请求参数
$params = [
    'tid'       =>  trim($_POST['tid']), // 订单编号
    'openid'    =>  trim($_POST['opid']),// 用户openid
    'fee'       =>  trim($_POST['fee']), // 订单交易金额
    'title'     =>  trim($_POST['title']),// 订单商品名称
    'uniacid'   =>  trim($_POST['uniacid']), // 公众号所属id
    'returnUrl' =>  trim($_POST['returnUrl']), // 返回连接地址
    // 支付成功回调地址；
    'notifyUrl' =>  isset($_POST['notifyUrl']) ? trim($_POST['notifyUrl']) : 'https://shop.gogo198.cn/addons/sz_yi/payment/tgwechat/notify.php',
];


//获取支付配置；
$setting = uni_setting($uniacid, array('payment'));
$payment = $setting['payment']['tgpay'];
if($payment['switch']!=1) {
    exit(json_encode(['code'=>0,'msg'=>'该商户未开启支付通道;']));
}

$appid = pdo_get('account_wechats', array('uniacid' => $uniacid), array('key'));
// 商户配置信息；
$config = [
    'account'   =>  $payment['mchid'],
    'key'       =>  $payment['key'],
    'appId'     =>  $appid['key'],
];

// 支付实例化
$pay = Tgpay::instance();

// 请求微信支付；
$payurl = $pay->wechat($params,$config);

//判断返回参数
if(!empty($payurl) && ($payurl['status'] != '100') ) {
    exit(json_encode(['code'=>0,'msg'=>$payurl['message']]));
}

$payinfo = json_decode($payurl['pay_info'],true);

//$urls    = "http://shop.gogo198.cn/app/index.php?i={$uniacid}&c=entry&p=index&do=shop&m=sz_yi";
$urls    = $_POST['returnUrl'];// 跳转地址；

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