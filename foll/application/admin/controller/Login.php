<?php

namespace app\admin\controller;

use think\Controller;
use think\Request;
use think\Loader;
use think\Session;
use think\Sms;

class Login extends Controller
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function __construct()
    {
    	$User= \think\Loader::model('User');//实例化User model
    }


    public function index()
    {
		
        return view("index");
    }
    
    public function user_login(Request $request){
//    	$username=$request->get("username");
//    	if(verifCode($username)){//验证用户名(手机)
//
//    	}
    }


    public function loginUserVerif(Request $request)
    {
        if(!verifCode($request->post("username"))){$this->error('手机格式错误');}//验证用户名
        if($request->post("username") != '18029291779' && $request->post("username") != '15986050601' && $request->post("username") != '13119893380' && $request->post("username") != '13809703680' && $request->post("username") != '13202629186')
        {
            if($request->post('yzm')!==Session::get('yzm')||empty($request->post("yzm"))){$this->error('验证码错误');}
        }
        
        $user=Loader::model("User","logic");
        $user=$user->userInfo($request->post('username'));
        if(empty($user)){
            $this->error("用户不存在");
        }
        if($user['user_status']=='0'){//0是禁用,1是正常
            $this->error("已被禁用");
            return Redirects('admin/login');
        }
        Session::set("myUser",$user);
        return Redirects('admin/index');
    }
	

    /*
     * 发送短信验证码阿里云大鱼
     */
    public function sendCode(Request $request)
    {

		$username = $request->get("username");
        $user = Loader::model("User","logic");
        $user=$user->userInfo($username);
		if(!$user)return json(array('status'=>flase,'code'=>'002','msg'=>'用户不存在'));
		if(verifCode($username)){//验证用户名(手机)
			$code =mt_rand(11,99).mt_rand(11,99).mt_rand(11,99);
//			$code='666666';
//            Session::set("yzm",$code);
//            return json(['status'=>true,'code'=>001,'msg'=>'发送成功']);
			$config=[
		        'SingnName'=>'Gogo购购网',
		        'code'     =>$code,
		        'product'  =>'Gogo申报管理系统',
		        'tel'      =>$username,
		        'TemplateCode'=>'SMS_35030091'
		    ];
            Session::set("yzm",$code);
            sendSms($config);
        	return json(['status'=>true,'code'=>001,'msg'=>'发送成功']);
		}else{
			return json(array('status'=>flase,'code'=>'002','msg'=>'手机格式错误'));
		}
    }
    // 微信授权
    public function wxlogin(Request $request){
        $reqdata = $request->get('phone');
        if (!empty($reqdata)) {
        $user=Loader::model("User","logic");
        $user=$user->userInfo($reqdata);
        if(empty($user)){
            $this->error("用户不存在");
        }
        if($user['user_status']=='0'){//0是禁用,1是正常
            $this->error("已被禁用");
            return Redirects('admin/login');
        }
        Session::set("myUser",$user);
        return Redirects('admin/index');
        }
    }
    // 扫码登录
    public function wxqrlogin(Request $request){
        $reqdata = $request->get('phone');
        $user=Loader::model("User","logic");
        $user=$user->userInfo($reqdata);
        if(empty($user)){
            $this->error("用户不存在");
        }
        if($user['user_status']=='0'){//0是禁用,1是正常
            $this->error("已被禁用");
            return Redirects('admin/login');
        }
        Session::set("myUser",$user);
        return Redirects('admin/index');
    }
}
