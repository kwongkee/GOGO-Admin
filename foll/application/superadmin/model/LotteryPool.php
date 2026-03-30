<?php
namespace app\superadmin\model;

use think\Model;
use think\Db;
class LotteryPool extends Model
{


    /**
     * 获取方案号池
     * @param $limit
     * @param $parm
     * @param $total
     * @return mixed
     */
    public function getMonthPlan($limit,$parm,$total)
    {
        return Db::name('parking_month_type')
            ->where(['is_check'=>1,'month_status'=>1])
            ->field(['id','scheme_name'])
            ->paginate($limit,$total,$parm);
    }

    /**
     * 获取总数
     * @return mixed
     */
    public function getMonthPlanCount()
    {
        return Db::name('parking_month_type')->where(['is_check'=>1,'month_status'=>1])->count('id');
    }


    /**获取受理数
     * @param $mid
     * @return mixed
     */
    public function getAcceptCount($mid)
    {
        return Db::name('parking_apply')->where(['m_id'=>$mid,'is_accept'=>1])->count('id');
    }

    /**获取号池数
     * @param $mid
     * @return mixed
     */
    public function getPoolCount($mid)
    {
        return Db::name('parking_lotter_total')->where('moth_id',$mid)->count('user_id');
    }

    /**获取抽签数
     * @param $mid
     * @return mixed
     */
    public function getLoteryCount($mid)
    {
        return Db::name('parking_pool')->where(['m_id'=>$mid])->field(['round_num'])->order('id','desc')->find();
    }


    /**获取注册用户
     * id
     * @param $tel
     * @return mixed
     */
    public function getUser($tel)
    {
        return Db::name('parking_authorize')->where('mobile',$tel)->field(['openid','uniacid'])->find();
    }

    /**
     * 保存抽签轮次
     * @param $data
     * @return null|string
     */
    public function insertPool($data)
    {
        $hasEmpty = Db::name('parking_pool')->where('m_id',$data['m_id'])->find();
        Db::startTrans();
        try{
            if(empty($hasEmpty)){
                Db::name('parking_pool')->insert($data);
            }else{
                Db::name('parking_pool')->where('m_id',$data['m_id'])->update($data);
            }
            Db::commit();
            return null;
        }catch (\Exception $exception){
            Db::rollback();
            return $exception->getMessage();
        }
    }


    /**
     * 返回申请数
     * @param $id
     * @return mixed
     */
    public function appleCount($id)
    {
        return Db::name('parking_apply')->where('m_id',$id)->count('id');
    }

    /**获取轮次管理
     * @param $p
     */
    public function getRoundManageList($p)
    {
        $total = Db::name('parking_pool')->count('id');
        return Db::name('parking_pool')
            ->alias('a')
            ->join('parking_month_type b','a.m_id=b.id')
            ->field(['a.*','b.scheme_name'])
            ->paginate(15,$total,$p);
    }

    public function getAllPlanAndRound($p)
    {
        $total = Db::name('parking_month_type')->where(['is_check'=>0,'month_status'=>0])->count('id');
        return Db::name('parking_month_type')
            ->alias('a')
            ->join('parking_pool b','a.id=b.m_id')
            ->where(['a.is_check'=>1,'a.month_status'=>1])
            ->field(['a.*','b.round_num','b.lottery_type','b.lottery_addr'])
            ->order('a.id','desc')
            ->paginate(15,$total,$p);
    }

    public function getOpenid($fid)
    {
        return Db::name('parking_apply')->where('flag_id','in',$fid)->field(['user_id','flag_id','uniacid'])->select();
    }

    /**
     * 删除原有方案的中签用户
     * @param $mid
     */
    public function delWin($mid)
    {
        Db::name('parking_lotter_win')->where('moth_id',$mid)->delete();
    }

    /**保存上传的中签信息
     * @param $data
     * @return array
     */
//    public function saveWin($data)
//    {
//        Db::startTrans();
//        try{
//            Db::name('parking_lotter_win')->insert($data);
//            Db::commit();
//            return ['完成',0];
//        }catch (\Exception $e){
//            Db::rollback();
//            return [$e->getMessage(),-1];
//        }
//    }

    public function getAllUser($mid)
    {
        return Db::name('parking_apply')->where('m_id',$mid)->field(['user_id','uniacid'])->select();
    }

//    public function updateSeep($id)
//    {
//    }


    /**
     * 查询意见表
     * @param $p
     * @return mixed
     */
    public function fetchallSay($p)
    {
        $total = Db::name('parking_saymsg')->where('pid',0)->count('id');
        return Db::name('parking_saymsg')->where('pid',0)->paginate(15,$total,$p);
    }

    public function insertSayMsg($data)
    {
        Db::name('parking_saymsg')->insert($data);
        return json(['code'=>0,'msg'=>'完成']);
    }


    public function getWinUser($id)
    {
        return Db::name('parking_apply')->where(['m_id'=>$id,'is_win'=>1])->select();
    }


    public function updateWinStatus($flaid,$data)
    {
        Db::startTrans();
        try{
            Db::name('parking_apply')->where('flag_id','in',$flaid)->update($data);
            Db::commit();
            return ['完成',0];
        }catch (\Exception $exception){
            Db::rollback();
            return [$e->getMessage(),-1];
        }
    }
}