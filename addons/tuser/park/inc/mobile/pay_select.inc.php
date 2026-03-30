<?php

/**
 * 选择支付方式 余额 | 微信支付
 */
global $_W, $_GPC;
include dirname(__FILE__) . '/permission.php';

$modulePublic = '../addons/' . $_GPC['m'] . '/static/';
$pay_money = intval($_GPC['money'] * 100);
if ($pay_money <= 0) {
    message('金额错误', $this->createMobileUrl('pay'));
}
include $this->template('pay_select');
?>
