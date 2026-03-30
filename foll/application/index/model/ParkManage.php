<?php

namespace app\index\model;
use think\Db;
use think\Model;
use think\Log;
class ParkManage extends Model
{
    public function dataSave($data,$uniacid=14)
    {
        $spaceData=array();
        $parkCode =$this->isDevCodeNotNull($data);
        if(!$parkCode['error']){return ['status'=>false,'msg'=>'设备编号存在'.$parkCode['msg']];}
        $position=[
            'Province'  =>explode(":",$data['provin'])[1],
            'City'      =>explode(":",$data['citys'])[1],
            'Area'      =>explode(":",$data['area'])[1],
            'Town'      =>explode(":",$data['town'])[1],
            'Committee' =>explode(":",$data['committee'])[1],
            'Road'      =>$data['road'],
            'Road_num'  =>$data['road_num'],
            'uniacid'   =>$uniacid,
            'Createtime'=>time()
        ];
        try{
            $positionId=Db::name("parking_position")->insertGetId($position);
            $parkCode['devCode']=explode(",",$parkCode['devCode']);
            $parkCode['parkCode']=explode(",",$parkCode['parkCode']);
            foreach (  $parkCode['devCode'] as $key =>$value){
                array_push($spaceData,[
                    'pid'    =>$positionId,
                    'cid'   =>$data['ChargeClass'],
                    'park_code'=>$parkCode['parkCode'][$key],
                    'numbers' =>$value,
                    'uniacid'=>$uniacid,
                    'createtime'=>time()
                ]);
            }
            Db::name("parking_space")->insertAll($spaceData);
            unset($spaceData,$position,$data);
            return ['status'=>true];
        }catch (Exception $exception){
            Log::Write($exception->getMessage());
            return ['status'=>false,'msg'=>$exception->getMessage()];
        }
    }

    protected function isDevCodeNotNull($data)
    {
        $devCode=array();
        $d_code=null;
        $p_code=null;
        $IssetNumber=null;
        $result=null;
        if(!empty($data['numArray'])){
            $data['numArray']=json_decode($data['numArray'],true);
            foreach ( $data['numArray'] as $value){
                $d_code .=$value['devCode'].',';
                $p_code .=$value['ParkCode'].$value['customCode'].',';
            }
            $d_code.=$data['devCode'];
            $p_code.=$data['ParkCode'].$data['customCode'];
            $result=Db::name("parking_space")->where('numbers','in',$d_code)->field('numbers')->select();
            if(!empty($result)){
                foreach ($result as $value){
                    $IssetNumber.=$value['numbers'].',';
                }
                return ['error'=>false,'msg'=>trim($IssetNumber,',')];
            }
            $result=Db::name("parking_space")->where('park_code','in',$p_code)->field('park_code')->select();
            if(!empty($result)){
                foreach ($result as $value){
                    $IssetNumber.=$value['park_code'].',';
                }
                return ['error'=>false,'msg'=>trim($IssetNumber,',')];
            }
            return $devCode=['error'=>true,'devCode'=>$d_code,'parkCode'=>$p_code];
        }
        $result=Db::name("parking_space")->where('numbers','in',$data['devCode'])->field('numbers')->find();
        if(!empty($result)){
            return ['error'=>false,'msg'=>$result['numbers']];
        }
        $result=Db::name("parking_space")->where('park_code','in',$data['ParkCode'].$data['customCode'])->field('park_code')->find();
        if(!empty($result)){
            return ['error'=>false,'msg'=>$result['park_code']];
        }
        return $devCode=['error'=>true,'devCode'=>$data['devCode'],'parkCode'=>$data['ParkCode'].$data['customCode']];
    }
}
