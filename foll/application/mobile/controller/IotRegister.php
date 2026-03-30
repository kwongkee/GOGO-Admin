<?php

namespace app\mobile\controller;

use think\Request;
use think\Controller;
use think\VerifIdCard;
use think\Db;

class IotRegister extends Controller
{

    public function index(Request $request)
    {
        $uniacid = $request->has('uniacid') ? $request->get('uniacid') : cookie('uniacid');
        $wxRes   = Db::name('account_wechats')->where('uniacid', $uniacid)->field(['key', 'secret'])->find();
        if (!$request->has('code')) {
            cookie('uniacid', $uniacid);
            $url = 'https://open.weixin.qq.com/connect/oauth2/authorize?appid=' . $wxRes['key'] . '&redirect_uri=http://shop.gogo198.cn/foll/public/?s=iot/user/reg&response_type=code&scope=snsapi_base&state=STATE#wechat_redirect';
            header('Location: ' . $url);
            exit();
        }
        $res = file_get_contents('https://api.weixin.qq.com/sns/oauth2/access_token?appid=' . $wxRes['key'] . '&secret=' . $wxRes['secret'] . '&code=' . $request->get('code') . '&grant_type=authorization_code');
        $res = json_decode($res, true);
        return view('iot/register/index', ['openid' => $res['openid']]);
    }


    public function regStorage(Request $request)
    {
        $data = $request->post();
        if ($data['phone'] === '' || !is_numeric($data['phone'])) {
            return json(['code' => -1, 'message' => '手机号码不能为空']);
        }

        if (!preg_match('#^13[\d]{9}$|^14[5,7]{1}\d{8}$|^15[^4]{1}\d{8}$|^17[0,6,7,8]{1}\d{8}$|^18[\d]{9}$#',
            $data['phone'])) {
            return json(['code' => -1, 'message' => '手机号码格式不正确']);
        }

        if ($data['name'] === '') {
            return json(['code' => -1, 'message' => '姓名不能为空']);
        }

        if ($data['idCard'] === '') {
            return json(['code' => -1, 'message' => '身份证号码不能为空']);
        }
        if (!(VerifIdCard::validation_filter_id_card($data['idCard']))) {
            return json(['code' => -1, 'message' => '身份证号格式不正确']);
        }
        $isUser = Db::name('iot_users')->where('phone', $data['phone'])->find();
        if ($isUser) {
            return json(['code' => -1, 'message' => '已存在账户']);
        }
        $uid = md5($data['phone'] . $data['idCard']);
        try {
            Db::name('iot_users')->insert([
                'unique_id'   => $uid,
                'name'        => $data['name'],
                'idcard'      => $data['idCard'],
                'phone'       => $data['phone'],
                'status'      => 0,
                'create_time' => time(),
                'openid'      => $data['openid'],
                'uniacid'     => cookie('uniacid')
            ]);
        } catch (\Exception $exception) {
            return json(['code' => -1, 'message' => '注册失败']);
        }
        return json(['code' => 0, 'message' => '注册成功,等待审核']);
    }


    /**
     * 注册成功提示信息
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function regSuccessMsg(Request $request)
    {
        return view('iot/register/success');
    }
}
