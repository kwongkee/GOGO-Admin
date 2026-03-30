<?php

namespace app\coupon\model;

use think\Model;
use think\Db;


class Coupon extends Model {

    /**
     * 保存新增卡卷
     * @param $data
     * @throws \Exception
     */
    public function insertCouponTable($data) {
        Db::startTrans();
        try {
            Db::name('foll_coupon')->insert($data);
            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * 保存到商城优惠券表
     * @param $data
     * @throws \Exception
     *
    public function insertShopTable($data){
        Db::startTrans();
        try {
            Db::name('sz_yi_coupon')->insertAll($data);
            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
            throw new \Exception($e->getMessage());
        }
    }*/
    
    public function getAllCoupon($where) {
        return Db::name('foll_coupon')->where($where)->order('id','desc')->paginate(5, false, [
            'query' => ['s' => 'coupon/coupon_list'],
            'var_page' => 'page',
            'newstyle' => true,
            'type' => 'Layui'
        ]);
    }

    /**
     * 获取适用商家公众号名称
     * @param $id
     * @return mixed
     */
    public function getPublicAccountByUniacid($id){
        return Db::name('account_wechats')->where('uniacid','in',$id)->field(['uniacid','name'])->select();
    }

    /**
     * 取消发布
     * @param $id
     * @param $bid
     * @throws \Exception
     */
    public function closeRel($id,$bid){
        Db::startTrans();
        try{
            Db::name('foll_coupon')->where(['id'=>$id,'busin_id'=>$bid])->update(['coupon_status'=>3]);
            Db::name('foll_coupon_issuancefee')->where(['coupon_id'=>$id,'busin_id'=>$bid])->delete();
            Db::commit();
        }catch (\Exception $e){
            Db::rollback();
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * 更新卡卷状态
     * @param $where
     * @param $parm
     * @throws \Exception
     */
    public function Disable($where,$parm){
        Db::startTrans();
        try{
            Db::name('foll_coupon')->where($where)->update($parm);
            Db::commit();
        }catch (\Exception $e){
            Db::rollback();
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * 删除发布
     * @param $id
     * @throws \Exception
     */
    public function deleteCoupon($id){
        Db::startTrans();
        try{
            Db::name('foll_coupon')->where('id',$id)->delete();
            Db::name('foll_coupon_issuancefee')->where('coupon_id',$id)->delete();
            Db::commit();
        }catch (\Exception $e){
            Db::rollback();
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * 活动优惠券信息by id
     * @param $id
     * @return mixed
     */
    public function getCouponInfoById($where){
        return Db::name('foll_coupon')->where($where)->find();
    }

    /**
     * 获取优惠券发行费用计算标准
     * @param $id
     * @return mixed
     */
    public function getCouponIssceFeeByCid($where){
        return Db::name('foll_coupon_issuancefee')->where($where)->find();
    }

    /**
     * 更新发行费用跟订单号到表
     * @param $where
     * @param $parm
     * @throws \Exception
     */
    public function updateIssceFeeTable($where,$parm){
        Db::startTrans();
        try{
            Db::name('foll_coupon_issuancefee')->where($where)->update($parm);
            Db::commit();
        }catch (\Exception $e){
            Db::rollback();
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * 查找支付状态
     * @param $orderId
     * @return mixed
     */
    public function getPayStatus($orderId){
        return Db::name('foll_coupon_issuancefee')->where('order_id',$orderId)->field('pay_status')->find();
    }

    public function fetchCouponUseInfo($where){
        return Db::name('foll_coupon_receive')
            ->alias('a')
            ->join('foll_coupon b','a.coupon_id=b.id')
            ->where($where)
            ->field(['a.id','a.create_time','a.status','b.coupon_num','b.coupon_name','b.coupon_title','b.coupon_money'])
            ->order('id','desc')
            ->paginate(5, false, [
                'query' => ['s' => 'coupon/use_manage'],
                'var_page' => 'page',
                'newstyle' => true,
                'type' => 'Layui'
            ]);
    }

    /**
     * 领取数
     * @param $bid
     * @return mixed
     */
    public function receiveNum($bid){
        return Db::name('foll_coupon_receive')->where('busin_id',$bid)->count('id');
    }


    /**
     * 使用数
     * @param $bid
     * @return mixed
     */
    public function useNum($bid){
        return Db::name('foll_coupon_receive')->where(['busin_id'=>$bid,'status'=>1])->count('id');

    }

    /**
     * 核销
     * @param $where
     * @return mixed
     */
    public function fetchCouponUse($where){
        return Db::name('foll_coupon_use')
            ->alias('a')
            ->join('foll_coupon b','a.coupon_id=b.id')
            ->join('foll_seller_member c','a.busin_id=c.id')
            ->where($where)
            ->field(['a.id','a.create_time','a.use_busin_name','a.application','a.order_id','a.discount_amout','b.coupon_name','b.use_type','c.busin_name'])
            ->order('id','desc')
            ->paginate(5, false, [
                'query' => ['s' => 'coupon/use_manage'],
                'var_page' => 'page',
                'newstyle' => true,
                'type' => 'Layui'
            ]);
    }

}