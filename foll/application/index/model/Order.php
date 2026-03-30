<?php


namespace app\index\model;

use think\Model;
use think\Db;

class Order extends Model
{


    /**
     * 查询订单
     * @param $where
     * @return array
     */
    public function selOrderTabByJoin($where, $offset, $limit)
    {
        $res = Db::name('foll_order')
            ->alias('foll')
            ->join('parking_order parking', 'foll.ordersn=parking.ordersn')
            ->where($where)
            ->field(['foll.create_time', 'foll.isError', 'foll.ordersn', 'foll.user_id', 'foll.total', 'foll.pay_account', 'foll.pay_time', 'foll.pay_status', 'foll.pay_type', 'foll.upOrderId', 'parking.starttime', 'parking.endtime', 'parking.duration', 'parking.number', 'parking.charge_type', 'parking.devs_ordersn', 'parking.card_time', 'parking.is_violation', 'parking.CarNo', 'parking.status'])
            ->limit($offset, $limit)
            ->order('foll.id','desc')
            ->select();
        return ['total' => $this->countOrder($where), 'res' => $res];
    }

    /**
     * 订单总数
     * @param $where
     * @return mixed
     */
    public function countOrder($where)
    {
        return Db::name('foll_order')
            ->alias('foll')
            ->join('parking_order parking', 'foll.ordersn=parking.ordersn')
            ->where($where)
            ->count('foll.id');
    }

    public function GetOrder($oid, $uniacid)
    {
        return Db::name('foll_order')
            ->alias('foll')
            ->join('parking_order parking', 'foll.ordersn=parking.ordersn')
            ->whereor('foll.ordersn', $oid)
            ->whereor('parking.devs_ordersn', $oid)
            ->whereor('foll.upOrderId', $oid)
            ->where('foll.uniacid', $uniacid)
            ->field(['foll.create_time', 'foll.isError', 'foll.ordersn', 'foll.user_id', 'foll.total', 'foll.pay_account', 'foll.pay_time', 'foll.pay_status', 'foll.pay_type', 'foll.upOrderId', 'parking.starttime', 'parking.endtime', 'parking.duration', 'parking.number', 'parking.charge_type', 'parking.devs_ordersn', 'parking.card_time', 'parking.is_violation', 'parking.CarNo', 'parking.status'])
            ->find();
    }


    public function GetParkCode($code)
    {
        return Db::name('parking_space')->where('park_code', $code)->field(['pid', 'numbers'])->find();
    }

    public function GetParkAddr($id)
    {
        return Db::name('parking_position')
            ->where('id', $id)
            ->field(['Town', 'Committee', 'Road', 'Road_num'])
            ->find();
    }


    public function getOrderCount($time1, $time2, $where)
    {
        return Db::name('foll_order')
            ->alias('foll')
            ->join('parking_order parking', 'foll.ordersn=parking.ordersn')
            ->whereTime('foll.create_time', 'between', [$time1, $time2])
            ->where($where)
            ->count('foll.id');
    }

//    public function GetInOrder($time1, $time2, $offset, $limit, $where)
//    {
//        return Db::name('foll_order')
//            ->alias('foll')
//            ->join('parking_order parking', 'foll.ordersn=parking.ordersn')
//            ->whereTime('foll.create_time', 'between', [$time1, $time2])
//            ->where($where)
//            ->field(['foll.create_time', 'foll.isError', 'foll.ordersn', 'foll.user_id', 'foll.total', 'foll.pay_account', 'foll.pay_time', 'foll.pay_status', 'foll.pay_type', 'foll.upOrderId', 'parking.starttime', 'parking.endtime', 'parking.duration', 'parking.number', 'parking.charge_type', 'parking.devs_ordersn', 'parking.card_time', 'parking.is_violation', 'parking.status'])
//            ->limit($offset, $limit)
//            ->select();
//    }

    public function GetParkCodeAndAddr($code)
    {
        return Db::name('parking_space')
            ->alias('a')
            ->join('parking_position b', 'a.pid=b.id')
            ->where('a.park_code', 'in', $code)
            ->field(['a.numbers', 'a.park_code', 'b.Town', 'b.Committee', 'b.Road', 'b.Road_num'])
            ->select();
    }


    public function getOrderUserInfo($uid)
    {
        $info = Db::name('parking_authorize')->where('openid', $uid)->field(['mobile', 'auth_status', 'auth_type'])->find();
        $verif = Db::name('parking_verified')->where('openid', $uid)->field(['idcard', 'uname', 'license'])->find();
        return [$info, $verif];
    }


    public function getAdminOpenid($tel)
    {
//        $mobile =  Db::name('foll_business_admin')->where('uniacid',$uid)->field('user_mobile')->find();
        return Db::name('parking_authorize')->where('mobile', $tel)->field('openid')->find()['openid'];
    }

    /**查找时间范围内订单
     * @param $time1
     * @param $time2
     * @param $uniacid
     * @return mixed
     */
    public function getAllOrderInTime($where)
    {
        return Db::name('foll_order')
            ->alias('foll')
            ->join('parking_order parking', 'foll.ordersn=parking.ordersn')
            ->where($where)
            ->field(['foll.create_time', 'foll.isError', 'foll.ordersn', 'foll.user_id', 'foll.total', 'foll.pay_account', 'foll.pay_time', 'foll.pay_status', 'foll.pay_type', 'foll.upOrderId', 'parking.CarNo', 'parking.starttime', 'parking.endtime', 'parking.duration', 'parking.number', 'parking.charge_type', 'parking.devs_ordersn', 'parking.card_time', 'parking.is_violation', 'parking.status'])
            ->select();
    }


    public function getOrderCountFromPayStatus($where)
    {
        return Db::name('foll_order')
            ->alias('foll')
            ->join('parking_order parking', 'foll.ordersn=parking.ordersn')
            ->where($where)
            ->count('foll.id');
    }

//    public function GetOrderFromPayStatus($where, $offset, $limit)
//    {
//        return Db::name('foll_order')
//            ->alias('foll')
//            ->join('parking_order parking', 'foll.ordersn=parking.ordersn')
//            ->where($where)
//            ->field(['foll.create_time', 'foll.isError', 'foll.ordersn', 'foll.user_id', 'foll.total', 'foll.pay_account', 'foll.pay_time', 'foll.pay_status', 'foll.pay_type', 'foll.upOrderId', 'parking.starttime', 'parking.endtime', 'parking.duration', 'parking.number', 'parking.charge_type', 'parking.devs_ordersn', 'parking.card_time', 'parking.is_violation', 'parking.status'])
//            ->limit($offset, $limit)
//            ->select();
//    }


    public function delOrder($where)
    {
        Db::name('foll_order')
            ->alias('foll')
            ->join('parking_order parking', 'foll.ordersn=parking.ordersn')
            ->where($where)
            ->delete();
    }

    public function fetchNotPayAllOrder($end)
    {
        return Db::name('parking_order')->where('endtime=0 and starttime<=' . $end)->field('id')->select();
    }

    /**
     * 试运营结束变更入场时间
     * @param $oid
     * @param $time
     */
    public function updateStartTimeField($oid, $time)
    {
        Db::name('parking_order')->where('id', 'in', $oid)->update(['starttime' => $time]);
    }

    /**
     * 查找已支付订单
     * @param $oid
     * @return mixed
     */
    public function isOrderPayStatus($oid){
        return Db::name('foll_order')->where('ordersn','in',$oid)->where('pay_status',1)->select();
    }

    /**
     * 删除订单
     * @param $oid
     */
    public function deleteFromOrderId($oid)
    {
        Db::name('parking_order')->where('ordersn', 'in', $oid)->delete();
        Db::name('foll_order')->where('ordersn', 'in', $oid)->delete();
    }

    /**
     * 修改foll订单表
     * @param $where
     * @param $parm
     */
    public function modifyFollOrderByOid($where, $parm)
    {
        Db::name('foll_order')->where($where)->update($parm);
    }

    /**修改park订单表
     * @param $where
     * @param $parm
     */
    public function modifyParkOrderByOid($where, $parm)
    {
        Db::name('parking_order')->where($where)->update($parm);
    }

    /**查找openid
     * [GetUserIdByMobile description]
     * @param [array] $tel [$openid]
     */
    public function GetUserIdByMobile($tel){
        return Db::name('parking_authorize')->where('mobile',$tel)->field('openid')->find();
    }

//    public function delAllOrder(){
//        Db::execute("truncate table ims_foll_order;");
//        Db::execute("truncate table ims_parking_order;");
//    }
}
