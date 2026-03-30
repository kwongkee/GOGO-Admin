<?php

namespace app\api\logic;

use think\Model;

class Coupon extends Model {

    protected $type = ['satisfyType', 'perType', 'unconditionalType'];
    protected $couponModel;

    public function __construct() {
        parent::__construct();
        $this->couponModel = model('Coupon', 'model');
    }

    /**
     * 优惠券抵扣
     * @param int $amount
     * @return int
     */
    public function Deduction($order) {
        //获取领取的卷
        $_newCoupon = null;
        $_nowTime = time();
        $amount = 0;
        $res = $this->couponModel->getReceivedCoupon('a.user_id="' . $order['user_id'] . '" and a.status=0 and use_type=2');
        foreach ($res as $val) {
            if (!empty($val['coupon_app'])) {
                $app = explode(',', $val['coupon_app']);
                if (!in_array('parking', $app)) {
                    continue;
                }
            }
            if (!empty($val['coupon_buisin'])) {
                $busi = explode(',', $val['coupon_buisin']);
                if (!in_array($order['uniacid'], $busi)) {
                    continue;
                }
            }
            $_newCoupon = $val;
            break;
        }

        if (empty($_newCoupon)) {
            return $order['amount'];
        }

        if ($_newCoupon['coupon_stime'] != '' && $_nowTime < $_newCoupon['coupon_stime']) {
            //优惠卷还没生效
            return $order['amount'];
        }

        if ($_newCoupon['coupon_stime'] != '' && $_nowTime > $_newCoupon['coupon_etime']) {
            //已过期
            $this->couponModel->updateStatus($_newCoupon['aid'], ['status' => 2, 'up_time' => $_nowTime]);
            return $order['amount'];
        }

        $amount = call_user_func([$this, $this->type[$_newCoupon['use_type']]], [$_newCoupon, $order['amount']]);
        $this->couponModel->updateStatus($_newCoupon['aid'], ['status' => 1, 'up_time' => $_nowTime]);
        $parm = [
            'order_id' => $order['ordersn'],
            'coupon_id' => $_newCoupon['id'],
            'user_id' => $order['user_id'],
            'original_amout' => $order['amount'],
            'discount_amout' => $order['amount'] - $amount,
            'create_time' => $_nowTime,
            'busin_id' => $_newCoupon['busin_id'],
            'use_busin_name' => $order['business_name'],
            'application'=>'parking'
        ];
        $this->couponModel->insertCouponUseTable($parm);
        return $amount;
    }


    /**
     * 满X元使用
     * @param $amount
     * @return int
     */
    protected function satisfyType($couponOrderArgv=null) {
        $money = 0;
        if ($couponOrderArgv[1] >= $couponOrderArgv[0]['enough']) {
            $money = $couponOrderArgv[1] - $couponOrderArgv[0]['coupon_money'];
            return $money;
        }
        return $couponOrderArgv[1];
    }

    /**
     * 每X元使用
     * @param $amount
     * @return int
     */
    protected function perType($couponOrderArgv=null) {
        if ($couponOrderArgv[1] == $couponOrderArgv[0]['enough']) {
            return $couponOrderArgv[1] - $couponOrderArgv[0]['coupon_money'];
        }
        return $couponOrderArgv[1];
    }

    /**
     * 无条件
     * @param $money
     * @return int
     */
    public function unconditionalType($couponOrderArgv=null) {
        $money = $couponOrderArgv[1] - $couponOrderArgv[0]['coupon_money'];
        if ($money < 0) {
            $money = 0;
        }
        return $money;
    }
}