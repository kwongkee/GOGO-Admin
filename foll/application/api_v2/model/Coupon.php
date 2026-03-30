<?php

namespace app\api_v2\model;

use think\Model;
use think\Db;

class Coupon extends Model{


    /**
     * 更新支付状态时间
     * @param $orderId
     * @param $parm
     */
    public function updatePayStatusField($orderId,$parm){
        Db::name('foll_coupon_issuancefee')->where('order_id',$orderId)->update($parm);
    }


    /**
     * 获取卡卷id
     * @param $orderId
     * @return mixed
     */
    public function getCouponId($orderId){
        return Db::name('foll_coupon_issuancefee')->where('order_id',$orderId)->field('coupon_id')->find();
    }

    /**
     * 查找月卡信息
     * @param $id
     * @return mixed
     */
    public function getCouponRes($id){
        return Db::name('foll_coupon')->where('id',$id)->find();
    }

    /**
     * 支付成功更新卡卷状态
     * @param $id
     * @param $parm
     */
    public function updateCouponStatus($id,$parm){
        Db::name('foll_coupon')->where('id',$id)->update($parm);
    }


    /**
     * 插入商城优惠券表
     * @param $data
     */
    public function insertShopTable($data){
        Db::name('sz_yi_coupon')->insertAll($data);
        Db::name('ewei_shop_coupon')->insertAll($data);
    }

    /**
     * 获取所有公众号uniacid
     * @return mixed
     */
    public function getAllPublicAccount(){
        return Db::name('account_wechats')->field('uniacid')->select();
    }

}