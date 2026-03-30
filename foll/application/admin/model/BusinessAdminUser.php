<?php

namespace app\admin\model;
use think\Db;
use think\Model;

class BusinessAdminUser extends Model{
    private $offset =10;
    public function getCompanyResult()
    {
        return Db::table("ims_account_wechats")->field(['uniacid','name'])->select();
    }

    public function getRoleResult()
    {
        return Db::table("ims_foll_business_authrole")->where("status",1)->field(['name as role_name','id'])->select();
    }

    public function saveUserResult($data)
    {
        $userAmount=Db::table("ims_foll_business_admin")->max('company_id');
        if($userAmount==10){
            return false;
        }
        Db::table("ims_foll_business_admin")->insert([
            'uniacid'    =>$data['uniacid'],
            'user_name'  =>$data['user_name'],
            'user_mobile'=>$data['user_mobile'],
            'user_status'=>$data['user_status'],
            'user_email' =>$data['user_mobile']."@qq.com",
            'create_time'=>date("Y-m-d H:i",time()),
            'role'       =>$data['role'],
            'user_pid'  =>0,
            'company_id'=>$userAmount+1
        ]);
        return true;
    }


    public function getAllUserResult()
    {
       return Db::table("ims_foll_business_admin")
            ->alias("a1")
            ->join("ims_account_wechats a2","a2.uniacid=a1.uniacid")
            ->join("ims_foll_business_authrole a3","a3.id=a1.role")
            ->where("a1.user_pid",0)
            ->field(["a1.*","a2.name","a3.name as role_name"])
            ->limit(0,$this->offset)
            ->select();
    }

    public function getSingUserResult($id)
    {
        return Db::table("ims_foll_business_admin")
            ->where("id",$id)
            ->find();
    }

    public function updateUserResult($id,$data)
    {
        Db::table("ims_foll_business_admin")
            ->where("id",$id)
            ->update($data);
    }
}