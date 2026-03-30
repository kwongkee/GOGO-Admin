<?php

namespace app\admin\controller;
use app\admin\controller;
use think\Db;
use think\Request;
use think\Loader;
use think\Validate;

class BusinessAdminUser extends Auth{
    public function BusinessAdminUserIndex(Request $request)
    {
        
        $adminUserModel = Loader::model("BusinessAdminUser","model");
        return view("business/admin_user_index",[
            'title'=>'商家超级管理用户列表',
            'companyResult' =>$adminUserModel->getCompanyResult(),
            'roleResule'    =>$adminUserModel->getRoleResult(),
            'userList'      =>$adminUserModel->getAllUserResult(),
            'userStatus'    =>[0=>"禁用",1=>"启用"]
        ]);
    }

    public function BusinessAdminUserAdd(Request $request)
    {
        $data=array();
        $validate = new Validate([
           'user_name'   =>'require',
            'user_mobile'=>'require|min:11|max:11',
            'user_status'=>'require',
            'role'=>'require',
            'uniacid'=>'require'
        ],[
            'user_name'=>'昵称必填',
            'user_mobile'=>'手机号必填',
            'user_status' =>'用户状态必填',
            'role' =>'角色必填',
            'uniacid'=>'所属企业必填'
        ]);
        $data=[
            'user_name' =>$request->post("name"),
            'user_mobile'=>$request->post("mobile"),
            'user_status'=>$request->post("userStatus"),
            'role'      =>$request->post("roleId"),
            'uniacid'   =>$request->post("company")
        ];
        if(!$validate->check($data)){
            $this->error($validate->getError());
        }
        $adminUserModel =Loader::model("BusinessAdminUser","model");
        if($adminUserModel->saveUserResult($data)){
            $this->success('新增成功',Url("admin/BusinessAdminUserIndex"));
        }
        $this->error("只能添加10个账户");
    }

    public function BusinessAdminUserEdit(Request $request)
    {
        $adminUserModel = Loader::model("BusinessAdminUser","model");
        if($request->isGet()){
            return view("business/admin_user_edit",[
                'title'=>'编辑',
                'companyResult' =>$adminUserModel->getCompanyResult(),
                'roleResule'    =>$adminUserModel->getRoleResult(),
                'userList'      =>$adminUserModel->getSingUserResult($request->get("id")),
            ]);
        }
        if($request->isPost()){
            $data=array();
            $validate = new Validate([
                'user_name'   =>'require',
                'user_mobile'=>'require|min:11|max:11',
                'user_status'=>'require',
                'role'=>'require',
                'uniacid'=>'require'
            ],[
                'user_name'=>'昵称必填',
                'user_mobile'=>'手机号必填',
                'user_status' =>'用户状态必填',
                'role' =>'角色必填',
                'uniacid'=>'所属企业必填'
            ]);
            $data=[
                'user_name' =>$request->post("name"),
                'user_mobile'=>$request->post("mobile"),
                'user_status'=>$request->post("userStatus"),
                'role'      =>$request->post("roleId"),
                'uniacid'   =>$request->post("company"),
                'user_email'=>$request->post("email")
            ];
            if(!$validate->check($data)){
                $this->error($validate->getError());
            }
            $adminUserModel->updateUserResult($request->post("id"),$data);
            $this->success("编辑更新成功",Url("admin/BusinessAdminUserIndex"));

        }

    }

    public function BusinessAdminUserDelete(Request $request)
    {
        Db::table("ims_foll_business_admin")->where("id",$request->get("id"))->delete();
        $this->success("删除成功",Url("admin/BusinessAdminUserIndex"));
    }

    public function BusinessAdminUserStatusUpdate(Request $request)
    {
        Db::table("ims_foll_business_admin")
            ->where("id",$request->get("id"))
            ->update(['user_status'=>$request->get("status")]);
        $this->success("状态更新成功",Url("admin/BusinessAdminUserIndex"));
    }

}