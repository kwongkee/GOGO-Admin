<?php
namespace app\mobile\controller;
use think\Controller;
use think\Request;
use think\Session;
class Login extends Controller
{
    public function index()
    {
        return view("login");
    }
    public function verif_login(Request $request)
    {
        if($request->param("username")!="13809703680"){
            return ["code"=>"error","msg"=>"账户不对","data"=>""];
        }
        Session::set("mobile","13809703680");
        return ["code"=>"success","msg"=>"登录成功","data"=>""];
    }
}