<?php

/**
 * pc 停车缴费系统 扫码页
 */
require_once WXZ_SHOPPINGMALL . '/source/Fans.class.php';
require_once WXZ_SHOPPINGMALL . '/source/Order.class.php';
require_once WXZ_SHOPPINGMALL . '/source/CreditLog.class.php';

global $_W, $_GPC;
$modulePublic = '../addons/' . $_GPC['m'] . '/static/';
$_W['module_setting'] = $this->module['config'];

$orderNo = $_GPC['order_no'];
if (!$orderNo) {
    message('订单号不存在', $this->createMobileUrl('index'));
}


$orderInfo = Order::getOrderByOrderNo($orderNo);

if (!$orderInfo || $orderInfo['status'] != 1) {
    message('二维码已过期', $this->createMobileUrl('index'));
}

require_once WXZ_SHOPPINGMALL . "/lib/wxpay/lib/WxPay.Api.php";
require_once WXZ_SHOPPINGMALL . "/lib/wxpay/example/WxPay.JsApiPay.php";
$tools = new JsApiPay();
$openId = $tools->GetOpenid();

$fans = new Fans();
if ($openId) {
    $user = $fans->getOne($openId, true);
}

$money = $orderInfo['pay_money'];

if ($money <= 0) {
    message('订单金额错误', $this->createMobileUrl('index'));
}

$config = $this->module['config'];

//根据积分计算金额
if ($user) {
    switch ($config['park_pay_type']) {
        case 1:
            //按次
            $credit = $config['park_pay_type_count_credit'];
            break;
        //按时
        case 2:
            $credit = ($money / 100 * $config['park_pay_type_time_credit']);
            break;
    }
}

//判断用户剩余积分是否满足
if ($user && $user['mobile'] && $user['left_credit'] >= $credit) {
    //积分满足 直接更新订单
    $update = array(
        'fans_id' => $user['uid'],
        'status' => 2,
        'credit' => $credit,
        'update_at' => time(),
    );
    Order::updateById($orderInfo['id'], $update);
    //插入积分日志
    $creditLog = new CreditLog();
    $credit_log_data = array(
        'uniacid' => $_W['uniacid'],
        'fans_id' => $user['uid'],
        'type' => 2,
        'operate' => 2,
        'event_type' => 8,
        'event_desc' => '停车抵用积分',
        'num' => $credit,
        'send_time' => time(),
        'create_at' => time(),
    );

    $creditLog->addCreditLog($credit_log_data);
    //用户减积分
    $fans->updateCredit($user['uid'], $credit, 2);
    message('支付成功', $this->createMobileUrl('index'));
}

//JSAPI支付
require_once WXZ_SHOPPINGMALL . "/lib/wxpay/lib/WxPay.Api.php";
require_once WXZ_SHOPPINGMALL . "/lib/wxpay/lib/WxPay.Notify.php";
require_once WXZ_SHOPPINGMALL . "/lib/wxpay/example/WxPay.JsApiPay.php";
$notifyUrl = $_W['siteroot'] . 'payment/wechat/notify_common.php';
$input = new WxPayUnifiedOrder();
$input->SetBody("停车缴费");
$input->SetAttach("wxz_shoppingmall|{$_W['uniacid']}");
$input->SetOut_trade_no($orderNo);
$input->SetTotal_fee($money);
$input->SetTime_start(date("YmdHis"));
$input->SetTime_expire(date("YmdHis", time() + 600));
$input->SetNotify_url($notifyUrl);
$input->SetTrade_type("JSAPI");
$input->SetOpenid($openId);
$order = WxPayApi::unifiedOrder($input);
$jsApiParameters = $tools->GetJsApiParameters($order);

//获取共享收货地址js函数参数
$editAddress = $tools->GetEditAddressParameters();
include $this->template('park_pay_qrcode');
?>
