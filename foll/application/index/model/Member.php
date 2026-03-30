<?php

namespace app\index\model;

use think\Model;
use think\Db;


class Member extends Model{

    /**
     * 获取总会员数量
     * @param $uniacid
     * @return mixed
     */
    public function getTotalNum($uniacid){
        return Db::name('parking_authorize')->where('uniacid',$uniacid)->count('id');
    }

    /**
     * 获取微信授权数量
     * @param $uniacid
     * @return mixed
     */
    public function getWxAuthNum($uniacid){
        return Db::name('parking_authorize')->where(['uniacid'=>$uniacid,'auth_type'=>'a:1:{s:2:"wx";s:7:"Fwechat";}'])->count('id');
    }

    /**获取农商卡授权数
     * @param $uniacid
     * @return mixed
     */
    public function getFAgroNum($uniacid){
        return Db::name('parking_authorize')->where(['uniacid'=>$uniacid,'auth_type'=>'a:1:{s:2:"sd";s:5:"FAgro";}'])->count('id');
    }

    /**
     * 获取银联授权数量
     * @param $uniacid
     * @return mixed
     */
    public function getCardNum($uniacid){
        return Db::name('parking_authorize')->where(['uniacid'=>$uniacid,'auth_type'=>'a:1:{s:2:"wg";s:11:"FCreditCard";}'])->count('id');
    }

    /**
     * 查询该用户拥有的月卡
     * @param $userId
     * @return string
     */
    public function fetchUserMonthCardByUid($userId){
        $mid= Db::name('parking_month_pay')->where(['user_id'=>$userId,'pay_status'=>1,'status'=>'A'])->field('m_id')->find();
        if (!empty($mid)){
            return Db::name('parking_month_type')->where('id',$mid['m_id'])->field('month_name')->find()['month_name'];
        }
        return '无';
    }

    /**
     * 查询用户验证信息
     * @param $userId
     * @return mixed
     */
    public function fetchUserVerifInfoByUid($userId){
        return Db::name('parking_verified')->where('openid',$userId)->field(['idcard','driverlicense','license'])->find();
    }

    /**
     * 查询用户未支付订单数
     * @param $userId
     * @return mixed
     */
    public function fetchUserNotPayOrderByUid($userId){
        return Db::name('foll_order')
            ->alias('a')
            ->join('parking_order b','a.ordersn=b.ordersn')
            ->where("a.user_id='".$userId."' and (a.pay_status=2 or a.pay_status=0 or b.charge_status=0)")
            ->count('a.id');
    }

    /**
     * 查询用户违规次数
     * @param $userId
     * @return mixed
     */
    public function fetchUserVioOrderByUid($userId){
        return Db::name('foll_order')
            ->alias('a')
            ->join('parking_order b','a.ordersn=b.ordersn')
            ->where("a.user_id='".$userId."' and b.is_violation=1")
            ->count('a.id');
    }

}