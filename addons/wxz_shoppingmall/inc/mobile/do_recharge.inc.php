<?php

/**
 * 充值操作
 */
global $_W, $_GPC;
$modulePublic = '../addons/' . $_GPC['m'] . '/static/';

$_W['module_setting'] = $this->module['config'];

require_once WXZ_SHOPPINGMALL . '/source/Order.class.php';
require_once WXZ_SHOPPINGMALL . '/source/Fans.class.php';
$order_id = intval($_GPC['order_id']);

if (!$order_id) {
    message('订单参数错误', $this->createMobileUrl('index'));
}

$order_info = Order::getOrderById($order_id);

if (!$order_info || $order_info['status'] != 1) {
    message('订单不存在或已处理', $this->createMobileUrl('index'));
}

$fans = new Fans();

//JSAPI支付
require_once WXZ_SHOPPINGMALL . "/lib/wxpay/lib/WxPay.Api.php";
require_once WXZ_SHOPPINGMALL . "/lib/wxpay/lib/WxPay.Notify.php";
require_once WXZ_SHOPPINGMALL . "/lib/wxpay/example/WxPay.JsApiPay.php";
$notifyUrl = $_W['siteroot'] . 'payment/wechat/notify_common.php';

//JSAPI支付
require_once WXZ_SHOPPINGMALL . "/lib/wxpay/lib/WxPay.Api.php";
require_once WXZ_SHOPPINGMALL . "/lib/wxpay/example/WxPay.JsApiPay.php";
$tools = new JsApiPay();
$openId = $tools->GetOpenid();
if ($openId) {
    $user = $fans->getOne($openId, true);
}

if (!$user) {
    message('订单用户错误', $this->createMobileUrl('index'));
}
$update['fans_id'] = $user['uid'];
Order::updateById($order_id, $update);

$title = '微信支付';

$input = new WxPayUnifiedOrder();
$input->SetBody($title);
$input->SetAttach("wxz_shoppingmall|{$_W['uniacid']}");
$input->SetOut_trade_no($order_info['order_no']);
$input->SetTotal_fee($order_info['pay_money']);
$input->SetTime_start(date("YmdHis"));
$input->SetTime_expire(date("YmdHis", time() + 600));
$input->SetNotify_url($notifyUrl);
$input->SetTrade_type("JSAPI");
$input->SetOpenid($user['openid']);
$order = WxPayApi::unifiedOrder($input);

$jsApiParameters = $tools->GetJsApiParameters($order);

//获取共享收货地址js函数参数
$editAddress = $tools->GetEditAddressParameters();
include $this->template('js_api_pay');
?>
