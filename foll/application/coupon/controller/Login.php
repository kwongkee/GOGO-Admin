<?php

namespace app\coupon\controller;

use think\Controller;
use think\Cookie;
use think\Request;
use think\Session;
use think\Db;


class Login extends Controller
{
    
    
    protected $model;
    protected $commModel;
    
    public function __construct()
    {
        parent::__construct();
        $this->model     = model('User', 'model');
        $this->commModel = model('Common', 'model');
    }
    
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        $token = Cookie::get('_tokens');
        if ($token!=""){
            $user = Db::name('total_merchant_account')->where('token',trim($token))->find();
            if ($user&&time()<$user['token_expire']){
                $res = $this->model->GetUserByAccount($user['mobile']);
                if ($res) {
                    Session::set('business', $res);
                    Redirects('coupon/index');
                }
            }
        }
        // exit();
        if (Session::has('business')) {
            Redirects('coupon/index');
        }
        return view('login/index');
    }
    
    
    /**
     * 验证登录
     * @param  Request  $request
     * @return mixed
     */
    public function check_login(Request $request)
    {
        if ($request->post('code') == "") {
            return json(['code' => -1, 'msg' => '验证码不能为空']);
        }
        
        if ($request->post('code') != Session::get('loginCode')) {
            return json(['code' => -1, 'msg' => '验证码错误']);
        }
        
        if ($request->post('tel') == '' || strlen($request->post('tel')) != 11) {
            return json(['code' => -1, 'msg' => '手机号格式错误']);
        }
        $res = $this->model->GetUserByAccount($request->post('tel'));
        if (!$res) {
            return json(['code' => -1, 'msg' => '不存在用户']);
        }
        Session::set('business', $res);
        Session::set("loginCode", null);
        return json(['code' => 0, 'msg' => '登录成功']);
        
    }
    
    /**
     * 发送验证码
     * @param  Request  $request
     * @return mixed
     */
    public function send_code(Request $request)
    {
        
        $tel = $request->get("tel");
        if (empty($tel)) {
            return json(['code' => -1, 'msg' => '手机号不能为空']);
        }
        
        if (!$this->commModel->check_account($tel)) {
            return json(['code' => -1, 'msg' => '请先注册']);
        }
        
        
        $code   = mt_rand(11, 99).mt_rand(11, 99).mt_rand(11, 99);
        $config = [
            'SingnName'    => 'Gogo购购网',
            'parm'         => [
                'code'    => $code,
                'product' => 'Gogo运营营销管理',
            ],
            'tel'          => $tel,
            'TemplateCode' => 'SMS_35030091',
        ];
        $result = newSendSms($config);
        Session::set("loginCode", $code);
        return json(['code' => 0, 'msg' => $result]);
    }
    
    
    /**
     * 登出
     * @param  Request  $request
     */
    public function signOut(Request $request)
    {
        Session::clear();
        $this->success('登出成功', Url('coupon/login'));
    }
    
}
