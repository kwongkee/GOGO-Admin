<?php

namespace app\api\model;

use think\Model;
use think\Db;

class Coupon extends Model{

    /**
     * @param $userId
     * @param $where
     * @return mixed
     */
    public function getReceivedCoupon($where){
        return Db::name('foll_cooupon_receive')
            ->alias('a')
            ->join('foll_coupon b','a.coupon_id=b.id')
            ->where($where)
            ->field(['b.*','a.id as aid'])
            ->select();
    }

    public function updateStatus($id,$parm){
        Db::startTrans();
        try{
            Db::name('foll_cooupon_receive')->where('id',$id)->update($parm);
            Db::commit();
        }catch (\Exception $e){
            Db::rollback();
        }
    }


    /**
     * 插入到使用表
     * @param $parm
     */
    public function insertCouponUseTable($parm){
        Db::startTrans();
        try{
            Db::name('foll_coupon_use')->insert($parm);
            Db::commit();
        }catch (\Exception $e){
            Db::rollback();
        }
    }
}