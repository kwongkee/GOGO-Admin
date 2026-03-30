<?php

namespace app\admin\controller;

use app\admin\controller;
use think\Db;
use think\Request;
use think\Loader;
use think\Validate;

class AdminAccount extends Auth
{

    //管理账户
    public function index()
    {
        $adminAccountModel = Loader::model("AdminAccount", "model");
        return view("adminaccount/index", [
            'title'      => '平台账户管理列表',
            'userList'   => $adminAccountModel->getAllUserResult(),
            'roleResule' => $adminAccountModel->getRoleResult(),
            'userStatus' => [0 => "禁用", 1 => "启用"],
        ]);
    }

    //管理账户新增
    public function add(Request $request)
    {
        $data     = [];
        $validate = new Validate([
            'username'    => 'require',            //用户名
            'tel'         => 'require|min:11|max:11',        //手机号
            'user_status' => 'require',            //用户状态
            'role'        => 'require',                    //角色
        ], [
            'username'    => '昵称必填',
            'tel'         => '手机号必填',
            'user_status' => '用户状态必填',
            'role'        => '角色必填',
        ]);
        $data     = [
            'username'    => $request->post("username"),
            'tel'         => $request->post("tel"),
            'user_status' => $request->post("user_status"),
            'role'        => $request->post("role"),
        ];
        if (!$validate->check($data)) {
            $this->error($validate->getError());
        }
        $adminAccountModel = Loader::model("AdminAccount", "model");
        if ($adminAccountModel->add($data)) {
            $this->success('新增成功', Url("admin/adminAccountAdd"));
        }
    }


    //用户编辑
    public function edit(Request $request)
    {
        $adminAccountModel = Loader::model("AdminAccount", "model");
        if ($request->isGet()) {
            return view("adminaccount/edit", [
                'title'      => '编辑',
                'userList'   => $adminAccountModel->getSingUserResult($request->get("id")),
                'roleResule' => $adminAccountModel->getRoleResult(),
            ]);
        }

        if ($request->isPost()) {
            $data     = [];
            $validate = new Validate([
                'username'    => 'require',            //用户名
                'tel'         => 'require|min:11|max:11',        //手机号
                'user_status' => 'require',            //用户状态
                'role'        => 'require',                    //角色
            ], [
                'username'    => '昵称必填',
                'tel'         => '手机号必填',
                'user_status' => '用户状态必填',
                'role'        => '角色必填',
            ]);
            $data     = [
                'username'    => $request->post("username"),
                'tel'         => $request->post("tel"),
                'user_status' => $request->post("user_status"),
                'role'        => $request->post("role"),
            ];
            if (!$validate->check($data)) {
                $this->error($validate->getError());
            }
            $adminAccountModel->updateUserResult($request->post("id"), $data);
            $this->success("编辑更新成功", Url("admin/adminAccountAdd"));

        }
    }

    //管理账户删除
    public function delete(Request $request)
    {
        Db::table("ims_foll_user")->where("id", $request->get("id"))->delete();
        $this->success("删除成功", Url("admin/adminAccountAdd"));
    }


    //用户状态更新
    public function update(Request $request)
    {
        Db::table("ims_foll_user")
            ->where("id", $request->get("id"))
            ->update(['user_status' => $request->get("status")]);
        $this->success("状态更新成功", Url("admin/adminAccountAdd"));
    }
}

?>
