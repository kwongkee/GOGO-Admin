<?php

namespace app\index\model;
use think\Db;
use think\Model;
use think\Session;

class AdminRole extends Model
{
    private $offset= 2;

    public function getAllRoleData()
    {
        return Db::name("foll_business_authrole")->alias("a1")
            ->join("foll_business_rwaccess a2","a1.id=a2.role_id")
            ->where("a1.pid",Session::get("UserResutlt")['role'])
            ->order('a1.id','desc')
            ->select();
    }

    public function saveRoleData($data)
    {
        $write =null;
        $read=null;
        if(empty($data['name']))return false;
        if($data['authType']==1){
            $write =0;
            $read=1;
        }else{
            $write=1;
            $read=1;
        }
        $inserId=Db::name("foll_business_authrole")->insertGetId([
            'name'  =>$data['name'],
            'pid'   =>Session::get('UserResutlt')['role'],
            'status'=>$data['status'],
            'remark'=>$data['remark'],
            'create_time'=>time(),
            'update_time'=>time()
        ]);
        Db::name("foll_business_rwaccess")->insert([
            'role_id'   =>$inserId,
            'read'      =>$read,
            'write'     =>$write
        ]);
        return true;
    }
}