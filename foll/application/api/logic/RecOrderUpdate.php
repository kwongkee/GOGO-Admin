<?php

namespace app\api\logic;
use think\Model;
use think\Log;
use think\Db;
class RecOrderUpdate extends Model
{
    public function updateElecStatus($oid,$status)
    {
        Db::startTrans();
        try{
            Db::name('foll_elec_order_detail')->where('EntOrderNo',$oid)->update(['elecStatus'=>$status]);
            Db::commit();
        }catch (\Exception $e){
            Db::rollback();
            Log::Write($e->getMessage());
        }
    }
    public function getUserEmail($oid)
    {
        $head_id = Db::name('foll_elec_order_detail')->where('EntOrderNo',$oid)->field('head_id')->find();
        $uid    = Db::name('foll_elec_order_head')->where('id',$head_id['head_id'])->field('uid')->find();
        $subject = Db::name('foll_cross_border')->where('uid',$uid['uid'])->field(['subject'])->find();
        $subject = json_decode($subject['subject'],true);
        return $subject['conmpanyemail'];
    }
}