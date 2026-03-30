<?php

namespace app\api\model;

use think\Model;
use think\Db;
class OrderModel extends Model{


    /**
     * 验证是否存在订单
     * @param $dOrder
     * @return mixed
     */
    public function hasDevOrder($dOrder){
        return Db::name('parking_order')->where(['devs_ordersn'=>$dOrder,'endtime'=>0])->field('id')->find();
    }

    /**
     * 验证是否存在订单2
     * @param $dOrder
     * @return mixed
     */
    public function hasDevOrderTow($dOrder){
        return Db::name('parking_order')->where(['devs_ordersn'=>$dOrder])->field('id')->find();
    }

    /**获取计费标准信息
     * @param $id
     * @return mixed
     */
    public function getChargeFromParkCode($id){
        return Db::name('parking_charge')->where('id',$id)->find();
    }

    /**
     * 获取车位信息
     * @param $code
     * @return mixed
     */
    public function getWxParkCode($code){
        return Db::name('parking_space')->where('numbers',$code)->find();
    }

    /**
     * 根据车牌获取用户信息
     * @param $carNo
     * @return mixed
     */
    public function getUserByCard($carNo){
        $res = Db::name('parking_authorize')->where('CarNo',$carNo)->find();
        if (empty($res)){
            $res = Db::name('parking_verified')->where('license',$carNo)->find();
            if (!empty($res)){
                $res['CarNo']  = $res['license'];
            }
        }

        return $res;
    }

    /**
     * 获取车位地址
     * @param $id
     * @return mixed
     */
    public function getAddr($id){
        return Db::name('parking_position')->where('id',$id)->find();
    }

    /**
     * 插入foll订单表
     * @param $data
     */
    public function saveFollOrder($data){
        Db::name('foll_order')->insert($data);
    }

    /**
     * 插入parkding订单表
     * @param $data
     */
    public function saveParkOrder($data){
        Db::name('parking_order')->insert($data);
    }

    public function saveViolation($data){
        Db::name('parking_violation')->insert($data);
    }

    /**
     * 获取优惠价格
     * @param $where
     * @return mixed
     */
    public function getDisRes($where){
       return Db::name('parking_operate')->where($where)->field(['discount', 'startDate', 'endDate'])->find();
    }

    /**
     * 获取订单信息
     * @param $devId
     * @return array
     */
    public function fetchOrderRes($devId){
        $parkRes = Db::name('parking_order')->where('devs_ordersn',$devId)->field(['starttime','ordersn'])->find();
        $follRes = Db::name('foll_order')->where('ordersn',$parkRes['ordersn'])->find();
        unset($parkRes['ordersn']);
        return array_merge($parkRes,$follRes);
    }


    public function updateFollOrder($where,$data){
        Db::name('foll_order')->where($where)->update($data);
    }

    public function updateParkOrder($where,$data){
        Db::name('parking_order')->where($where)->update($data);
    }

    public function updateVioatInfo($where,$data){
        Db::name('parking_violation')->where($where)->update($data);
    }

    /**
     * 查找设备预付费订单
     * [getDevPayOrderByParkCode description]
     * @param  [strint] $code      [description]
     * @param  [int] $startTime [description]
     * @return [array]            [description]
     */
    public function getDevPayOrderByParkCode($code,$startTime){
        return Db::name('parking_order')->where(['number'=>$code,'starttime'=>$startTime,'dev_ordersn'=>'dev'])->field('id')->find();
    }
}
