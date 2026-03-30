<?php

namespace app\coupon\controller;

use think\Controller;
use think\Request;
use think\Session;
use think\Validate;

class Register extends Controller
{

    protected $model;
    protected $commModel;

    public function __construct()
    {
        parent::__construct();
        $this->model = model('User', 'model');
        $this->commModel = model('Common', 'model');
    }

    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        return view('register/index');
    }


    /**
     * 保存新建的资源
     *
     * @param  \think\Request $request
     * @return \think\Response
     */
    public function reg_save(Request $request)
    {
        $data = $request->post();
        $file = $request->file('busin_logo');
        $rule = [
            'busin_login_accout' => 'require',
            'busin_name' => 'require',
            'userName' => 'require',
            'captcha' => 'require',
        ];
        $errMsg = [
            'busin_login_accout.require' => '手机号不能为空',
            'busin_name.require' => '请填写商家名称',
            'userName.require' => '请填写用户名',
            'captcha.require' => '请填写验证码!',
        ];
        $validate = new Validate($rule, $errMsg);

        if (!$validate->check($data)) {
            return json(['code' => -1, 'msg' => $validate->getError()]);
        }

        if (Session::get('regCode') != $data['captcha']) {
            return json(['code' => -1, 'msg' => '验证码不正确']);
        }

        if (!empty($file)) {
            $fileSavePath = ROOT_PATH . '/public/uploads/logo_image/';
            $saveResult = $file->validate(['ext' => 'jpg'])->move($fileSavePath);
            if ($saveResult) {
                $data['busin_logo'] = 'uploads/logo_image/' . $saveResult->getSaveName();
            } else {
                return json(['code' => -1, 'msg' => '请上传图片格式jpg']);
            }
        }
        try {

            if ($this->commModel->check_account($data['busin_login_accout'])) {
                return json(['code' => -1, 'msg' => '账户已存在']);
            }
            $this->model->register_save($data);

        } catch (\Exception $e) {
            return json(['code' => -1, 'msg' => $e->getMessage()]);
        }
        Session::set('regCode', null);
        return json(['code' => 0, 'msg' => '注册成功']);

    }

    /**
     * 发送验证码
     * @param Request $request
     * @return mixed
     */
    public function send_code(Request $request)
    {

        $tel = $request->get("tel");
        if (empty($tel)) return json(['code' => -1, 'msg' => '手机号不能为空']);
        if ($this->commModel->check_account($tel)) {
            return json(['code' => -1, 'msg' => '该账户已存在']);
        }
        $code = mt_rand(11, 99) . mt_rand(11, 99) . mt_rand(11, 99);
        $config = [
            'SingnName' => 'Gogo购购网',
            'parm' => [
                'name' => 'Gogo运营营销管理',
                'code' => $code,
            ],
            'tel' => $tel,
            'TemplateCode' => 'SMS_109370056'
        ];
        $result = newSendSms($config);
        unset($result);
        Session::set("regCode", $code);
        return json(['code' => 0, 'msg' => '已发送']);
    }


}
