<?php

header('Content-Type:text/html;charset=utf-8');
define('IN_MOBILE', true);
define('PDO_DEBUG', true);
require '../../../../framework/bootstrap.inc.php';
// 通过支付方法
require_once './Xfbpay.php';


$data = [
    'tid'    => 'GC20190829120513432422',
    'opid'   => 'ov3-btyLPTGwIduBvEXdiGSnpUK4',
    'fee'    => 1,
    'title'  => 'WT47203[跨境直邮]nike男生复古休闲板鞋',
    'uniacid'=> 3,
    'to'     => 'wechat',
    'gid'    => '72768',
    'cusid'  => '供应商ID',
];

$url = 'http://shop.gogo198.cn/addons/sz_yi/payment/tgwechat/customPay.php';

/**
 * 开发思路
 * 1、获取商品信息
 * 2、获取支付配置
 * 3、获取商户配置
 */

// http://shop.gogo198.cn/addons/sz_yi/payment/tgwechat/customPay.php
// 获取商户ID，支付方式
$shop = pdo_fetch('select supplier_uid,paymentType from ' . tablename('sz_yi_goods') . ' where id=:id limit 1', array(':id' => $data['gid']));
if(empty($shop)) {

    $msg = '商品不存在，请检查！';
    showMsg($msg);

    //exit(json_encode(['status'=>0,'msg'=>'商品不存在，请检查！']));
}


// 获取支付配置
$payConfig = pdo_fetch('select sets from '.tablename('sz_yi_sysset').' where uniacid=:uniacid limit 1',[':uniacid'=>$data['uniacid']]);
if(empty($payConfig)) {

    $msg = '未配置支付，请检查！';
    showMsg($msg);

    //exit(json_encode(['status'=>0,'msg'=>'未配置支付，请检查！']));
}

// 解序列号
$pay = unserialize($payConfig['sets']);
unset($payConfig);
// 支付是否开启；
$tgpaystatus = $pay['pay']['tgpaystatus'];
// 支付配置
$tgpayC       = $pay['pay']['tgpay'];
unset($pay);


// 获取APPID
$appid = pdo_fetch('select `key` from '.tablename('account_wechats').' where uniacid=:uniacid',[':uniacid'=>$data['uniacid']])['key'];
if(empty($appid)) {

    $msg = '未配置APPID，请检查！';
    showMsg($msg);

    //exit(json_encode(['status'=>0,'msg'=>'未配置APPID，请检查！']));
}


// 获取商户配置
$customs   = pdo_fetch('select isShop,ifFull,fullFee from '.tablename('customs_charging').' where publicId=:publicId and merchatId=:merchatId limit 1',[':publicId'=>$data['uniacid'],':merchatId'=>$shop['supplier_uid']]);
if(empty($customs)) {

    $msg = '未配置商户，请检查！';
    showMsg($msg);

    //exit(json_encode(['status'=>0,'msg'=>'未配置商户，请检查！']));
}


// 开始计算费用
/**
 * 1、商户是否选择收费；
 * 2、计费方式是全额还是比例
 * 3、商品支付方式；1线上，2线下
 */
// 处理商户是否收费  1：收费，0收费
if(!$customs['isShop']) {
    $html = <<<HTML
<script >
alert('该商户不收费，可线下咨询！')
setTimeout(function(){
    window.history.go(-1);
},1200);
</script>
HTML;
    exit($html);
    //exit(json_encode(['status'=>0,'msg'=>'该商户不收费，可线下咨询！']));
}

$payMoney = $data['fee'];
// 收费，判断是全额还是比例支付
if($customs['ifFull']) {
    $payMoney = sprintf('%.2f',$data['fee'] * $customs['fullFee']);
}

// 选择支付方式；
if($shop['paymentType'] == 1 && $tgpaystatus) { // 线上支付

    // 实例化通莞支付
    //$tgpay = Tgpay::instance();

    $config['account'] = $tgpayC['mchid']; // 商户号
    $config['key']     = $tgpayC['key'];   // 密钥
    $config['appId']   = $appid['key'];    // appId

    $params['fee']     = $data['fee'];
    $params['tid']     = $data['tid'];
    $params['title']   = $data['title'];
    $params['uniacid'] = $data['uniacid'];
    $params['openid']  = $data['opid'];
    $params['gid']     = $data['gid'];

    // 祥付宝微信支付
    $xfb = Xfbpay::getInstance();

    // 微信
    if($data['to'] == 'wechat') {

        // 请求微信支付
        $wx  = $xfb->wechat($params);
        // 报错返回
        if($wx['retCode'] != 'SUCCESS' ) {

            $msg = $wx['message'];
            showMsg($msg);
        }

        $title = '微信扫码支付';
        // 支付二维码
        $qrcode = $wx['trade_qrcode'];


    // 支付宝
    } else if($data['to'] == 'alipay') {

        // 支付宝需要跳转

        // 请求支付宝
        $alipay  = $xfb->alipay($params);

        // 报错返回
        if($alipay['retCode'] != 'SUCCESS' ) {

            $msg = $alipay['message'];
            showMsg($msg);
        }

        // 支付类型
        $title = '支付宝扫码支付';
        // 支付二维码
        $qrcode = $alipay['trade_qrcode'];

    }


    $html = <<<HTMLS
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <script type="text/javascript" src="//cdn.staticfile.org/jquery/2.1.1/jquery.min.js"></script>
    <script type="text/javascript" src="//static.runoob.com/assets/qrcode/qrcode.min.js"></script>
    <title>$title</title>
</head>
<body style="margin: 10%">
   
    <p style="color: lightskyblue;width: 100%;text-align: center;">$title</p>
    <div id="qrcode" style="width: 100px;height: 100px;margin: auto;"></div>
    <p style="color: green;width: 100%;text-align: center;">请扫码支付</p>
</body>
<script >
window.onload =function(){
     var qrcodes = new QRCode(document.getElementById("qrcode"), {width : 100,height : 100});
    //第一次默认生成的包含信息
    qrcodes.makeCode("$qrcode");
}
</script>
</html>
HTMLS;

    echo $html;die;

} else { // 2 线下支付

    $html = <<<HTML
<script >
alert('该商品只能线下支付、金额为：'+$payMoney)
</script>
HTML;
    exit($html);

    //echo '线下支付: '.$payMoney;
}


/*echo '<pre>';
print_r($payMoney);
echo '<hr>';
print_r($customs);*/

function showMsg($msg) {
    $html = <<<HTML
<script >
alert($msg)
setTimeout(function(){
    window.history.go(-1);
},1200);
</script>
HTML;
    exit($html);
}

?>


