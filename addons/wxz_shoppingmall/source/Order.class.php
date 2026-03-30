<?php

/**
 * 订单
 */
class Order {

    public static $table = 'wxz_shoppingmall_order';

    /**
     * 订单分类
     * @var type 
     */
    public static $types = array(
        1 => '停车场微信支付',
        2 => '充值余额',
        3 => '余额消费',
        4 => '微信消费',
    );

    /**
     * 订单状态
     * @var type 
     */
    public static $status = array(
        1 => '待处理', 2 => '成功', 3 => '失败',
    );

    /**
     * 计费方式
     */
    public static $park_pay_types = array(
        1 => '按次扣积分', 2 => '按时长扣积分', 3 => '微信现金',
    );

    /**
     * 创建订单
     */
    public static function creteOrder($data) {
        global $_W;
        if (!$data || $data['money'] <= 0) {
            return;
        }
        $data['uniacid'] = $_W['uniacid'];
        $data['create_at'] = time();
        pdo_insert(self::$table, $data);
        return pdo_insertid();
    }

    /**
     * 通过id获取订单信息
     * @param type $id
     */
    public static function getOrderById($id, $field = '*') {
        global $_W;
        if (!$id || !is_numeric($id)) {
            return;
        }
        $condition = "uniacid={$_W['uniacid']} and id={$id}";
        $sql = "select {$field} from " . tablename(self::$table) . " where $condition";
        return pdo_fetch($sql);
    }

    /**
     * 通过订单号获取订单信息
     * @param type $orderNo
     */
    public static function getOrderByOrderNo($orderNo, $field = '*') {
        global $_W;
        if (!$orderNo) {
            return;
        }
        $condition = "uniacid={$_W['uniacid']} and order_no='{$orderNo}'";
        $sql = "select {$field} from " . tablename(self::$table) . " where $condition";
        return pdo_fetch($sql);
    }

    /**
     * 根据订单id 更新订单
     * @param type $id
     * @param type $update
     */
    public static function updateById($id, $update) {
        if (!$id || !$update) {
            return;
        }
        return pdo_update(self::$table, $update, array('id' => $id));
    }

    /**
     * 用户充值
     * @param type $uid
     * @param type $money
     */
    public static function recharge($uid, $money) {
        global $_W;
        require_once WXZ_SHOPPINGMALL . '/source/Fans.class.php';
        $fans = new Fans();
        return $fans->updateAccount($uid, $money, 1);
    }

    /**
     * 订单成功处理
     * @param type $orderInfo
     */
    public static function doSuccess($orderInfo) {
        switch ($orderInfo['type']) {
            case '2':
                //充值余额
                self::recharge($orderInfo['fans_id'], $orderInfo['pay_money']);
                break;
        }
    }

    /**
     * 获取订单编号
     * @return type
     */
    public static function getOrderNo() {
        $randNo = sprintf('%04d', rand(1, 1000));
        return date("YmdHis") . $randNo;
    }

    /**
     * 余额支付
     * @param type $user 用户信息
     * @param type $money 分
     */
    public static function balancePay($user, $money) {
        global $_W;

        if (!$money) {
            return modelReturnFormat(-1, '金额错误');
        }
        //余额不足
        if ($user['account'] * 100 < $money) {
            return modelReturnFormat(-1, '余额不足');
        }

        //生成订单
        $orderNo = self::getOrderNo();
        $orderData = array(
            'uniacid' => $_W['uniacid'],
            'order_no' => $orderNo,
            'fans_id' => $user['uid'],
            'type' => 3,
            'status' => 2,
            'money' => $money,
            'success_at' => time(),
            'create_at' => time(),
        );
        $orderId = self::creteOrder($orderData);
        if ($orderId) {
            require_once WXZ_SHOPPINGMALL . '/source/Fans.class.php';
            $fans = new Fans();
            $ret = $fans->updateAccount($user['uid'], $money, 2);
        }
        if ($ret) {
            return modelReturnFormat(0, '支付成功', array('order_id' => $orderId));
        }
    }

}
