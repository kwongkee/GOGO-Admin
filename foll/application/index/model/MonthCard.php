<?php

namespace app\index\model;

use think\Model;
use think\Db;

class MonthCard extends Model
{
    public function insertTable($data)
    {
        try{
            Db::name('parking_month_type')->insert($data);
            return ['code'=>0,'msg'=>'完成'];
        }catch (\Exception $e){
            return ['code'=>'-1','msg'=>$e->getMessage()];
        }
    }

    public function lists($total,$where,$limit,$q)
    {
        return Db::name('parking_month_type')->where($where)->order('id','desc')->paginate($limit,$total,$q);
    }


    /**获取待受理总人数
     * @return mixed
     */
    public function getApplyUserTotal()
    {
        return Db::name('parking_apply')->where(['uniacid'=>Session('UserResutlt.uniacid'),'is_accept'=>0])->count('id');
    }


    /**返回待受理列表
     * @param $total
     * @param $param
     * @return mixed
     */
    public function getApplyUserList($total,$param)
    {
        return Db::name('parking_apply')
            ->alias('a')
            ->join('parking_month_type b','a.m_id=b.id')
            ->join('parking_authorize c','a.user_id=c.openid')
            ->where('a.uniacid',Session('UserResutlt.uniacid'))
            ->where('a.is_accept',0)
            ->field(['a.*','b.scheme_name','c.mobile'])
            ->paginate(10,$total,$param);
    }


    public function getAllReviewUser($mid)
    {
        return Db::name('parking_apply')
            ->where(['uniacid'=>Session('UserResutlt.uniacid'),'m_id'=>$mid,'is_accept'=>1,'is_review'=>0])
            ->field(['id','user_id'])
            ->select();
    }


    public function updateReviewStatus($id,$status,$speed,$isDone='N')
    {
        Db::name('parking_apply')->where('id','in',$id)->update(['is_review'=>$status,'planned_speed'=>$speed,'is_done'=>$isDone]);
    }

    /**放进抽签表
     * @param $mid
     * @param $userid
     */
    public function inserToLotterTable($mid,$userid)
    {
        Db::name('parking_lotter_total')->insert(['moth_id'=>$mid,'user_id'=>$userid]);
    }

    /**放进中签表
     * @param $mid
     * @param $userid
     */
    public function insertToWin($mid,$userid,$type)
    {
        Db::name('parking_lotter_win')->insert(['moth_id'=>$mid,'user_id'=>$userid,'is_type'=>$type]);
    }

    /**更新中签状态
     * @param $mid
     * @param $user
     */
    public function updateWinStatus($mid,$user)
    {
        Db::name('parking_apply')->where('user_id',$user)->update(['is_win'=>1,'is_round'=>1]);
    }
    
    /**
     * 通过受理数
     * @return mixed
     */
    public function isAepNum()
    {
        return Db::name('parking_apply')->where('is_accept',1)->count('id');
    }
    
    /**
     * 未通过受理数
     * @return mixed
     */
    public function notAepNum($where=null)
    {
        return Db::name('parking_apply')->where($where)->count('id');
    }
    
    /**
     * 拒绝受理数
     * @return mixed
     */
    public function refAepNum($where=null)
    {
        return Db::name('parking_apply')->where($where)->count('id');
    }
    
    public function totalAepNum($where=null)
    {
        return Db::name('parking_apply')->where($where)->count('id');
    }
    
    /**获取月卡方案总数
     * @return mixed
     */
    public function getPalnCount()
    {
        return Db::name('parking_month_type')->where(['is_check'=>1,'month_status'=>1])->count('id');
    }
    
    /**获取所有月卡方案
     * @param $tol
     * @param $parm
     * @return mixed
     */
    public function getAllPlan($tol,$parm)
    {
        return Db::name('parking_month_type')
            ->field(['scheme_name','month_name','month_num','id'])
            ->where(['is_check'=>1,'month_status'=>1])
            ->paginate(15,$tol,$parm);
    }

    public function getMonthId($id)
    {
        return Db::name('parking_month_pay')->where('id',$id)->field('m_id')->find();
    }
    
    public function getAlterUser($id)
    {
        return Db::name('parking_apply')->where(['m_id'=>$id,'is_up'=>'Y'])->field(['id','user_id','m_id'])->find();
    }
    
    
    /**
     * 更新注销月卡字段
     * @param $id
     */
    public function updateCancelUser($id)
    {
        Db::name('parking_month_pay')->where('id',$id)->update(['status'=>'D']);
    }
    
    /**
     * 更新候补变正选字段
     * @param $id
     * @param null $money
     */
    public function updateUpField($id,$money=null,$time)
    {
        Db::name('parking_apply')->where('id',$id)->update(['is_up'=>'N','up_money'=>$money,'delay_paytime'=>$time]);
    }
    
    
    public function getMonthPayTime($id)
    {
        return Db::name('parking_month_type')->where('id',$id)->field(['lottery_pay','lottery_pay2'])->find();
    }
}