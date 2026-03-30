<?php
/**
 * 商店聚合支付宝支付；
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

/*$input   = file_get_contents('php://input');
parse_str($input,$Arrdata);*/

@file_put_contents('./log/AlipayPost.txt',json_encode($_POST)."\r\n",FILE_APPEND);

if(!$_POST) {
    exit(json_encode(['code'=>0,'msg'=>'is No Post']));
}

// 检查数据
if(!isset($_POST['tid'])) {
    exit(json_encode(['code'=>0,'msg'=>'请填写订单编号']));
}
if(!isset($_POST['opid'])) {
    exit(json_encode(['code'=>0,'msg'=>'请填写用户opid']));
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
    // 支付成功，回调地址；
    'notifyUrl' =>  isset($_POST['notifyUrl']) ? trim($_POST['notifyUrl']) : 'http://shop.gogo198.cn/addons/sz_yi/payment/tgalipay/notify.php',
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

// 请求支付宝支付；
$payurl = $pay->alipay($params,$config);

/**
 * Array
(
[sign] => BBBBE839CBB8F142B6C7A50296F1EBA8
[message] => 获取二维码成功
[status] => 100
[codeUrl] => https://qr.alipay.com/bax01669bqfku9m9cwj120b4
[state] => 4
[account] => 101570223660
[orderId] => 91193782702504022016
[lowOrderId] => G9919820191111600891632
[c_time] => 2019-11-11 14:49:10
)
 *
 */
//判断返回参数
if(!empty($payurl) && ($payurl['status'] != '100') ) {
    exit(json_encode(['code'=>0,'msg'=>$payurl['message']]));
}

$pay_url = $payurl['codeUrl'];
//$urls    = "http://shop.gogo198.cn/app/index.php?i={$uniacid}&c=entry&p=index&do=shop&m=sz_yi";

exit(json_encode([
    'code'=>1, // 请求状态
    'pay_url'=>$pay_url,// 请求支付跳转URL
    'returnUrl'=>$params['returnUrl'],// 返回地址
]));

?>
