<?php

namespace app\index\model;
use think\Model;
use think\Db;
class Charge extends Model
{
    public  function getSingleChargeInfo($id)
    {
        $data = null;
        $data = Db::name("parking_charge")->where('id',$id)->find();
        $data['payPeriod']=json_decode($data['payPeriod'],true);
        $data['period_rand']=json_decode($data['period_rand'],true);
        return $data;
    }
    public function chargeUpdate($request){
        $periodTime=json_decode($request->post('periodTime'),true);
        if(empty($periodTime)){
            return ['status'=>false,'msg'=>'时间段不能为空'];
        }
        if(empty($request->post('plan'))){
            return ['status'=>false,'msg'=>'方案名称不能空'];
        }
        try{
            Db::name("parking_charge")->where("id",$request->post('id'))->update(
                [
                    'payPeriod'     =>json_encode($periodTime),
                    'ChargeClass'   =>$request->post('plan'),
                    'Allcapped'     =>$request->post('Allcapped'),
                    'allClass'      =>$request->post('allClass'),
                    'period_rand'   =>json_encode(['period_rand'=>$request->post('period_rand'),'period_rand2'=>$request->post('period_rand2')]),
                    'period_limit'  =>$request->post('period_limit')
                ]
            );
            return ['status'=>true,'msg'=>'更新成功'];
        }catch (\Exception $e){
            return ['status'=>false,'msg'=>$e->getMessage()];
        }
    }
}