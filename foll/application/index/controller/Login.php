<?php

namespace app\index\controller;

use think\Controller;
use think\Loader;
use think\Request;
use think\Session;
use think\Validate;
use think\Cookie;

class Login extends Controller
{

    public function index()
    {
        return view("login/index");
    }

    public function sendCode(Request $request)
    {
        $tel=$request->get("tel");
        if(empty($tel)) return json(['status'=>false,'code'=>10001,'msg'=>'手机号不能为空']);
        $adminUserModel =Loader::model("AdminUser","model");
        $isEmptyMoblie =$adminUserModel->getAdminSingleUserResult($tel);
        if(empty($isEmptyMoblie)) return json(['status'=>false,'code'=>10001,'msg'=>'账户错误']);
        $code =mt_rand(11,99).mt_rand(11,99).mt_rand(11,99);
        $config=[
            'SingnName'=>'Gogo购购网',
            'code'     =>$code,
            'product'  =>'Gogo运营后台管理',
            'tel'      =>$tel,
            'TemplateCode'=>'SMS_35030091'
        ];
        sendSms($config);
        Session::set("verifCode",$code);
        return json(['status'=>true,'code'=>10000,'msg'=>'发送成功']);
    }

    public function verifLogin(Request $request)
    {
        $validate = new Validate([
            'login_tel'   =>'require|min:11|max:11',
            'login_code'  =>'require|min:1|max:6'
        ],[
            'login_tel'=>'手机号必填',
            'login_code'=>'验证码不能为空'
        ]);
        $data=[
            'login_tel' =>$request->post('login_tel'),
            'login_code'=>$request->post("login_code")
        ];
        if(!$validate->check($data)){
            $this->error($validate->getError());
        }
        if($data['login_code']!==Session::get("verifCode")){
            $this->error("验证不正确");
        }
        $adminUserModel =Loader::model("AdminUser","model");
        $isUserResultEmpty = $adminUserModel->getAdminSingleUserResult($data['login_tel']);
        if(empty($isUserResultEmpty)){
            $this->error("登录异常");
        }
        if($isUserResultEmpty['user_status']!==1){
            $this->error("账户禁用");
        }
        $adminUserModel->updateAction($isUserResultEmpty['id']);
        Session::set("UserResutlt",$isUserResultEmpty);
        unset($isUserResultEmpty,$data);
        $this->success("登录成功",Url('index/index'));
    }


    public function logout(Request $request)
    {
        Session::clear();
        Cookie::clear("thinkphp_show_page_trace");
        $this->success("登出成功",Url("index/login"));
    }
}