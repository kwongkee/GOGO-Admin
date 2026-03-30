<?php

namespace app\admin\model;

use think\Model;
use think\Db;
class Role extends Model{

    public function getBusinessRole()
    {
        return Db::table("ims_foll_business_authrole")
            ->alias("a1")
            ->join("ims_foll_business_rwaccess a2","a2.role_id=a1.id")
            ->where("a1.pid",0)
            ->select();
    }


    public function addBusinessRole($data)
    {
        $read=null;
        $write=null;
        if($data['authType']==1){
            $read=1;
            $write=0;
        }else{
            $read=1;
            $write=1;
        }
        try{
            $role_id = Db::table("ims_foll_business_authrole")->insertGetId([
                'name'  =>$data['name'],
                'status'=>$data['roleStatus'],
                'remark'=>$data['remark'],
                'create_time'=>time(),
                'update_time'=>time(),
            ]);
            Db::table("ims_foll_business_rwaccess")->insert([
                'role_id' =>$role_id,
                'read'    =>$read,
                'write'   =>$write
            ]);
            return ['status'=>true,"code"=>100,'msg'=>'添加成功'];
        }catch (Exception $e){
            return ['status'=>false,'code'=>101,'msg'=>$e->getMessage()];
        }
    }

    public function getBusinessRoleSingData($id)
    {
        $data = Db::table("ims_foll_business_authrole")->where("id",$id)->find();
        $Rw   =Db::table("ims_foll_business_rwaccess")->where("role_id",$id)->find();
        $data['read']=$Rw['read'];
        $data['write']=$Rw['write'];
        unset($Rw);
        return $data;
    }

    public function updateSingBusinesRoleData($request)
    {
        $read=null;
        $write=null;
        if($request->post('authType')==1){
            $read=1;
            $write=0;
        }else{
            $read=1;
            $write=1;
        }
        try{
            Db::table("ims_foll_business_authrole")
                ->where("id",$request->post("id"))
                ->update([
                    'name' =>$request->post("name"),
                    'status' =>$request->post("roleStatus"),
                    'remark' =>$request->post("remark"),
                    'update_time' =>time()
                ]);
            Db::table("ims_foll_business_rwaccess")->where("role_id",$request->post("id"))->update(['read'=>$read,"write"=>$write]);
            return true;
        }catch (Exception $e){
            return false;
        }
    }

    public function deleteBusinesRoleData($id)
    {
        Db::table('ims_foll_business_authrole')->delete($id);
        Db::table('ims_foll_business_rwaccess')->where('role_id',$id)->delete();
    }
}