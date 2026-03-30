<?php

namespace app\api_v2\controller;

use think\Request;
use think\Log;
use think\Db;

/**
 * 发布优惠卡卷支付的发行费用支付回调
 * Class CouponPayCallBack
 * @package app\api_v2\controller
 */
class CouponPayCallBack
{
    public $couponModel;

    public function __construct()
    {
        $this->couponModel = model('Coupon', 'model');
    }

    public function index(Request $request)
    {
        @file_put_contents('../runtime/log/pay/' . date('Y', time()) . '_coupon_ref.log',
            '日期：' . date('Ymd H:i:s', time()) . '|数据:' . json_encode($request->post()) . "\n", FILE_APPEND);
        if ($request->post('lowOrderId')==""){
            return 'error';
        }
        $parm = [
            'pay_status' => $request->post('state') == '0' ? 1 : 2,
            'pay_time' => strtotime($request->post('payTime'))
        ];
        Db::startTrans();
        try {
            $this->couponModel->updatePayStatusField($request->post('lowOrderId'), $parm);
            $cid = $this->couponModel->getCouponId($request->post('lowOrderId'));
            $this->couponModel->updateCouponStatus($cid['coupon_id'], ['coupon_status' => 1]);
            // $coupon = $this->couponModel->getCouponRes($cid['coupon_id']);
            // if (empty($coupon['coupon_app'])) {
            //     $this->couponModel->insertShopTable($this->generateShopCoupon($coupon));
            // } else {
            //     $_couponApp = explode(',', $coupon['coupon_app']);
            //     if (in_array('shop', $_couponApp)||in_array('directmail',$_couponApp)) {
            //         $this->couponModel->insertShopTable($this->generateShopCoupon($coupon));
            //     }
            // }
            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
            Log::write(json_encode([
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'code' => $e->getCode(),
                'message' => $e->getMessage()
            ]));
        }
        return 'success';
    }

    /**
     * 生成商城优惠券表
     * @param $data
     * @return array
     */
    protected function generateShopCoupon($data)
    {
        if (empty($data['coupon_buisin'])) {
            $uniacid = $this->couponModel->getAllPublicAccount();
        } else {
            $uniacid = explode(',', $data['coupon_buisin']);
        }
        foreach ($uniacid as $value) {
            $parm [] = [
                'uniacid' => is_array($value) ? $value['uniacid'] : $value,
                'couponname' => $data['coupon_name'],
                'gettype' => 1,
                'getmax' => 1,
                'usetype' => 0,
                'enough' => $data['enough'],
                'coupontype' => 0,
                'returntype' => 1,
                'timedays' => ceil(($data['coupon_etime'] - $data['coupon_stime']) / 86400),
                'timestart' => $data['coupon_stime'],
                'timeend' => $data['coupon_etime'],
                'deduct' => $data['coupon_money'],
                'thumb' => $data['img_smill_url'],
                'desc' => $data['coupon_desc'],
                'createtime' => time(),
                'total' => $data['total'],
                'resptitle' => '[nickname]  [total]',
                'coupon_num' => $data['coupon_num']
            ];
        }
        return $parm;
    }

}
