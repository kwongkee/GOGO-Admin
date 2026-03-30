<?php

namespace app\index\controller;

use think\Controller;
use think\Loader;
use think\Request;
use think\Session;

class UserManage extends Controller
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        $userModel=Loader::model("AdminUser","model");
        return view("user/index");
    }

    /**
     * 显示创建资源表单页.
     *
     * @return \think\Response
     */
    public function userCreate(Request $request)
    {
        if($request->isGet()){
            $roleModel=Loader::model('AdminRole','model');
            return view("user/user_add",['list'=>$roleModel->getAllRoleData()]);
        }
        if($request->isPost()){
            if(empty($request->post("user_name")))$this->error("用户名不能为空");
            if(empty($request->post("phone")))$this->error("手机格式不正确");
            if(Session::get('UserResutlt')['write']!=1)$this->error('权限不足');
            $userModel=Loader::model('AdminUser','model');
            $userModel->createUser($request->post());
            $this->success("添加成功",Url('index/user_manage'));
        }
    }

    /**
     * 保存新建的资源
     *
     * @param  \think\Request  $request
     * @return \think\Response
     */
    public function save(Request $request)
    {
        //
    }

    /**
     * 显示指定的资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function read($id)
    {
        //
    }

    /**
     * 显示编辑资源表单页.
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * 保存更新的资源
     *
     * @param  \think\Request  $request
     * @param  int  $id
     * @return \think\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * 删除指定资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function delete($id)
    {
        //
    }
}
