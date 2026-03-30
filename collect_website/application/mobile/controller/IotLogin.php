<?php

namespace app\mobile\controller;

use think\Request;
use think\Controller;
use think\Db;

class IotLogin extends Controller
{


    public function index()
    {
        return view('iot/login/index');
    }

    /**
     * 发送验证码,za
     * @param Request $request
     * @return mixed
     */
    public function sendCode(Request $request)
    {
        if (!$request->has('phone')) {
            return json(['code' => -1, 'message' => '请填写手机号']);
        }
        $phone = $request->get('phone');

        if (!preg_match('#^13[\d]{9}$|^14[5,7]{1}\d{8}$|^15[^4]{1}\d{8}$|^17[0,6,7,8]{1}\d{8}$|^18[\d]{9}$#', $phone)) {
            return json(['code' => -1, 'message' => '手机号码格式不正确']);
        }
        $code   = mt_rand(11, 99) . mt_rand(11, 99) . mt_rand(11, 99);
        $config = [
            'SingnName'    => 'Gogo购购网',
            'code'         => $code,
            'product'      => 'Gogo物联网系统',
            'tel'          => $request->get('phone'),
            'TemplateCode' => 'SMS_35030091',
        ];
        sendSms($config);
        session("code:" . $request->get('phone'), $code);
        return json(['code' => 0, 'message' => '发送成功']);
    }


    /**
     * @param Request $request
     * @return mixed
     */
    public function login(Request $request)
    {
        $data = $request->post();
        if ($data['code'] == '') {
            return json(['code' => -1, 'message' => '请填写验证码']);
        }
        if (!preg_match('#^13[\d]{9}$|^14[5,7]{1}\d{8}$|^15[^4]{1}\d{8}$|^17[0,6,7,8]{1}\d{8}$|^18[\d]{9}$#',
            $data['phone'])) {
            return json(['code' => -1, 'message' => '手机号码格式不正确']);
        }
        $code = session("code:" . $data['phone']);
        if ($data['code'] != $code) {
            return json(['code' => -1, 'message' => '验证码不正确']);
        }
        $isUser = Db::name('iot_users')->where('phone', $data['phone'])->find();
        if (!$isUser) {
            return json(['code' => -1, 'message' => '请注册']);
        }

        if ($isUser['status'] == 3) {
            return json(['code' => -1, 'message' => '账户已禁用']);
        }

        if ($isUser['status'] == 0 || $isUser['status'] == 2) {
            return json(['code' => -1, 'message' => '待审核中']);
        }
        if ($isUser['expire_type'] != '') {
            switch ($isUser['expire_type']) {
                case 'week':
                    $isUser['create_time'] = $isUser['create_time'] + (($isUser['expire_time'] * 7) * 24 * 60 * 60);
                    break;
                case 'year':
                    $isUser['create_time'] = $isUser['create_time'] + (($isUser['expire_time'] * 365) * 24 * 60 * 60);
                    break;
                case 'month':
                    $isUser['create_time'] = $isUser['create_time'] + (($isUser['expire_time'] * 30) * 24 * 60 * 60);
                    break;
            }
            if ($isUser['create_time'] < time()) {
                return json(['code' => -1, 'message' => '账户已过有效期']);
            }
        }
        session('iot_user', $isUser);
        cookie(md5('iot_user'), $isUser['unique_id'],604800);
        return json(['code' => 0, 'message' => '登录成功','data'=>$isUser['unique_id']]);
    }
}
