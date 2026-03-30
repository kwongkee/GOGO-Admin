<?php

namespace app\index\controller;
use app\index\controller;
use think\Db;
use think\Loader;
use think\Request;
use think\Session;

class RoleManage extends CommonController
{
    public function index()
    {
        $RoleModel = Loader::model('AdminRole','model');
        $authType  = ['write'=>[0=>'不可写',1=>'可写'],'read'=>[0=>'不可读',1=>'可读']];
//        dump($RoleData);
        return view("role/index",[
            'listRole'=>$RoleModel->getAllRoleData(),
            'authType'=>$authType
        ]);
    }

    public function roleAdd(Request $request)
    {
        if($request->isGet()){
            return view("role/role_add");
        }
        if($request->isPost()){
            if(Session::get('UserResutlt')['write']!=1)$this->error('权限不足');
            $RoleModel = Loader::model('AdminRole','model');
            if($RoleModel->saveRoleData($request->post())){
                $this->success("添加成功",Url("index/role_manage"));
            }
            $this->error("字段参数名称必填");
        }
    }

    public function roleDelete(Request $request)
    {
        if(Session::get("UserResutlt")['write']!=1){
            $this->error("没权限");
        }
        Db::name("foll_business_authrole")->where("id",$request->get('id'))->delete();
        Db::name("foll_business_rwaccess")->where('role_id',$request->get('id'))->delete();
        $this->success("删除成功",Url("index/role_manage"));
//        return view("")
    }
    public function roleEdit(Request $request)
    {

    }
}