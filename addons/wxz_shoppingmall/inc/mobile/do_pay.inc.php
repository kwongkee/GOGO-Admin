<?php

/**
 * 付费操作
 */
global $_W, $_GPC;
include dirname(__FILE__) . '/permission.php';
$modulePublic = '../addons/' . $_GPC['m'] . '/static/';

$_W['module_setting'] = $this->module['config'];

require_once WXZ_SHOPPINGMALL . '/source/Order.class.php';
require_once WXZ_SHOPPINGMALL . '/source/Fans.class.php';

$money = intval($_GPC['money']); //支付金额
$type = intval($_GPC['type']) ? $_GPC['type'] : 1; //支付类型1.余额，2微信支付

if (!$money) {
    message('支付金额错误', $this->createMobileUrl('pay'));
}

if ($type == 1) {
    if ($user['account'] * 100 < $moeny) {
        message('余额不足', $this->createMobileUrl('do_pay', array('pay_select' => $money, 'type' => $type)));
    }
    //余额支付
    $payRet = Order::balancePay($user, $money);

    if ($payRet['error_code'] !== 0) {
        message($payRet['error_msg'], $this->createMobileUrl('pay_select', array('money' => $money, 'type' => $type)));
    } else {
        message('支付成功', $this->createMobileUrl('index'));
    }
}
?>
