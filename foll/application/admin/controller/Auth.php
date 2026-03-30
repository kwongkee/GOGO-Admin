<?php

namespace app\admin\controller;
use think\Session;
use think\Controller;

class Auth extends Controller
{

    /*
     *不存在操作函数时执行
     */
    public function _empty()
    {
        abort(404,'页面不存在');
    }

    /*
     * 验证登录
     */
    public function __construct()
    {
        parent::__construct();
          if (!(Session::has('myUser'))){
              return Redirects("admin/login");
          }
    }
}