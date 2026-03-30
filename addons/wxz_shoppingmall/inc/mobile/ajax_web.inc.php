<?php

global $_W, $_GPC;
$do = (string) trim($_GPC['sdo']);
session_start();

/**
 * 执行动作
 */
require_once WXZ_SHOPPINGMALL . '/func/common_two.func.php';
$_W['module_setting'] = $this->module['config'];
$className = "ControllerAjaxWeb";
$classObj = new $className();

if (is_callable(array($classObj, $do))) {
    call_user_func(array($classObj, $do), $this);
} else {
    ajaxReturnFormat('', '404 not found');
}

class ControllerAjaxWeb extends Wxz_shoppingmallModuleSite {

    /**
     * 生成停车场缴费二维码图片
     */
    public function park_pay_get_qrcode($module) {
        global $_W, $_GPC;
		
        require_once WXZ_SHOPPINGMALL . '/source/Order.class.php';

        $config = $module->module['config'];
        $money = intval($_GPC['money'] * 100);

        if ($money <= 0) {
            ajaxReturnFormat(0, '订单金额小于0');
        }

        require_once WXZ_SHOPPINGMALL . "/lib/wxpay/lib/WxPay.Api.php";
        require_once WXZ_SHOPPINGMALL . "/lib/wxpay/example/WxPay.NativePay.php";

        $orderNo = Order::getOrderNo();

        $orderData = array(
            'uniacid' => $_W['uniacid'],
            'order_no' => $orderNo,
            'type' => 1,
            'status' => 1,
            'park_pay_type' => $config['park_pay_type'],
            'money' => $money * 100,
            'pay_money' => $money * 100, // 
            'create_at' => time(),
        ); //创建订单信息

        switch ($config['park_pay_type']) {
            case 3:
                //微信支付，微信扫码
                $realMoney = $money * $config['park_pay_type_discount'];
                $notifyUrl = $_W['siteroot'] . 'payment/wechat/notify_common.php';
                $orderData['pay_money'] = $realMoney;
                $notify = new NativePay();
                $input = new WxPayUnifiedOrder();
                $input->SetBody("停车缴费");
                $input->SetAttach("wxz_shoppingmall|{$_W['uniacid']}");
                $input->SetOut_trade_no($orderNo);
                $input->SetTotal_fee($realMoney);
                $input->SetTime_start(date("YmdHis"));
                $input->SetTime_expire(date("YmdHis", time() + 600));
                $input->SetNotify_url($notifyUrl);
                $input->SetTrade_type("NATIVE");
                $input->SetProduct_id("000001");
                $result = $notify->GetPayUrl($input);
                if ($result['return_code'] == 'FAIL') {
                    ajaxReturnFormat(0, $result['return_msg']);
                }
                $qrcode_url = $result["code_url"]; //扫码支付二维码
                $param = array(
                    'qrcode' => $qrcode_url,
                    'level' => 'h',
                    'size' => '7',
                );
                $qrcode_url = $module->createMobileUrl('qrcode', $param);
                break;
            default:
                $url = $module->createMobileUrl('park_pay_qrcode', array('order_no' => $orderNo));
                $url = $_W['siteroot'] . 'app' . trim($url, '.');
                $url = urlencode($url);
                $param = array(
                    'qrcode' => $url,
                    'level' => 'h',
                    'size' => '7',
                );
                $qrcode_url = $module->createMobileUrl('qrcode', $param);
                break;
        }

        $orderId = Order::creteOrder($orderData); //创建订单
        $returnData = array(
            'order_id' => $orderId,
            'qrcode' => $qrcode_url,
        );
        ajaxReturnFormat(1, '', $returnData);
    }

    /**
     * 检查订单状态
     */
    public function check_order($module) {
        global $_W, $_GPC;

        require_once WXZ_SHOPPINGMALL . '/source/Order.class.php';
        require_once WXZ_SHOPPINGMALL . '/source/Fans.class.php';
        $orderId = (int) $_GPC['order_id'];
        if (!$orderId) {
            ajaxReturnFormat(0, '订单参数错误');
        }
        $orderInfo = Order::getOrderById($orderId, 'status,credit,fail_reason,pay_money,fans_id');
        $orderInfo['pay_money'] = sprintf("%.2f", $orderInfo['pay_money'] / 100);
        $orderInfo['username'] = '';
        $orderInfo['plate_number'] = '';
        if ($orderInfo && $orderInfo['fans_id']) {
            $fans = new Fans();
            $user = $fans->getOne($orderInfo['fans_id']);
            $orderInfo['username'] = $user['username'];
            $orderInfo['plate_number'] = $user['plate_number'];
        }
        ajaxReturnFormat(1, '', $orderInfo);
    }

    /**
     * 生成充值余额订单
     */
    public function do_recharge() {
        global $_W, $_GPC;

        require_once WXZ_SHOPPINGMALL . '/source/Order.class.php';

        $money_fen = intval($_GPC['money'] * 100);
        if ($money_fen < 1) {
            ajaxReturnFormat(0, "金额错误,{$money_fen}");
        }

        $orderNo = Order::getOrderNo();
        $orderData = array(
            'uniacid' => $_W['uniacid'],
            'order_no' => $orderNo,
            'type' => 2,
            'status' => 1,
            'money' => $money_fen,
            'pay_money' => $money_fen, // 
            'create_at' => time(),
        ); //创建订单信息

        $orderId = Order::creteOrder($orderData); //创建订单
        ajaxReturnFormat(1, '', array('order_id' => $orderId));
    }

    /**
     * 生成支付订单
     */
    public function do_pay() {
        global $_W, $_GPC;

        require_once WXZ_SHOPPINGMALL . '/source/Order.class.php';

        $money_fen = intval($_GPC['money']);
        if ($money_fen < 1) {
            ajaxReturnFormat(0, "金额错误,{$money_fen}");
        }

        $orderData = array(
            'order_no' => Order::getOrderNo(),
            'type' => 4,
            'status' => 1,
            'money' => $money_fen,
            'pay_money' => $money_fen, // 
        ); //创建订单信息

        $orderId = Order::creteOrder($orderData); //创建订单
        ajaxReturnFormat(1, '', array('order_id' => $orderId));
    }

}

?>
