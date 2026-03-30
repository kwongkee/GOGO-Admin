<?php

namespace app\index\model;
use think\Db;
use think\Model;
use think\Session;

class AdminUser extends Model
{
    public function getAdminSingleUserResult($tel)
    {
        return Db::name("foll_business_admin")->where("user_mobile",$tel)->find();
    }

    public function createUser($data)
    {
        Db::name("foll_business_admin")->insert([
            'uniacid'   =>Session::get('UserResutlt')['uniacid'],
            'user_name' =>$data['user_name'],
            'user_mobile'=>$data['phone'],
            'user_status'=>$data['status'],
            'user_email' =>$data['phone']."@qq.com",
            'create_time'=>date("Y-m-d H:i:s",time()),
            'role'       =>$data['role_id']
        ]);
    }
    public function getAllAdminUserData()
    {
        //出现问题，待修改
//        return Db::name('foll_business_admin')
//            ->alias("a1")
//            ->join("ims_foll_business_authrole a2","a1.role=a2.id")
//            ->where('uniacid',Session::get('UserResutlt')['id'])
//            ->order('id','desc')
//            ->select();
    }
    public function updateAction($id)
    {
        Db::table("ims_foll_business_admin")->where("id",$id)->update(['last_login_ip'=>$_SERVER["REMOTE_ADDR"],'last_login_time'=>date("Y-m-d H:i:s",time())]);
    }
}
