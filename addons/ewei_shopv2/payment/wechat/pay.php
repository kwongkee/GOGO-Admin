<?php

/**
 * 微信支付，唤起支付目录；
 */
$payinfo = $_POST;

if(empty($payinfo)) {
    return '支付参数错误';
}

if($payinfo['appId'] == ''){
    return '支付参数错误appId';
}

if($payinfo['timeStamp'] == ''){
    return '支付参数错误timeStamp';
}

if($payinfo['nonceStr'] == ''){
    return '支付参数错误nonceStr';
}

if($payinfo['package'] == ''){
    return '支付参数错误package';
}

if($payinfo['signType'] == ''){
    return '支付参数错误signType';
}

if($payinfo['paySign'] == ''){
    return '支付参数错误paySign';
}

$payinfo['appId']       = trim($payinfo['appId']);
$payinfo['timeStamp']   = trim($payinfo['timeStamp']);
$payinfo['nonceStr']    = trim($payinfo['nonceStr']);
$payinfo['package']     = trim($payinfo['package']);
$payinfo['signType']    = trim($payinfo['signType']);
$payinfo['paySign']     = trim($payinfo['paySign']);

$url = 'http://shop.gogo198.cn/app/index.php?i=14&c=entry&m=ewei_shopv2&do=mobile&r=parking.invoicelist.park_Norder';

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

<script>
    alert('微信支付');
        document.addEventListener('WeixinJSBridgeReady', function onBridgeReady() {
            WeixinJSBridge.invoke('getBrandWCPayRequest', {
                'appId': '<?php echo $payinfo['appId'];?>',
                'timeStamp': '<?php echo $payinfo['timeStamp'];?>',
                'nonceStr': '<?php echo $payinfo['nonceStr'];?>',
                'package': '<?php echo $payinfo['package'];?>',
                'signType': '<?php echo $payinfo['signType'];?>',
                'paySign': '<?php echo $payinfo['paySign'];?>'
            }, function(res) {
                var urls = '<?php echo $url;?>';
                if (res.err_msg == 'get_brand_wcpay_request:ok') {
                    document.getElementById('error').innerHTML = '支付成功';
                    alert('支付完成');
                    backs(urls);
                } else if(res.err_msg=='get_brand_wcpay_request:cancel') {
                    document.getElementById('error').innerHTML = '取消支付';
                    alert('取消支付');
                    backs(urls);
                } else {
                    alert('启动微信支付失败, 请检查你的支付参数'+JSON.stringify(res));
                    backs(urls);
                }
            });
            
        }, false);

    // 支付成功跳转
    function backs(url) {
        setTimeout(function () {
            location.href = url;
        },800)
    }
</script>

</body>
</html>
