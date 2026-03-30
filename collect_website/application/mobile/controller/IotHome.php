<?php

namespace app\mobile\controller;

use think\Request;
use app\mobile\controller;
use think\Db;

class IotHome extends IotAuth
{
    public function index()
    {
        return view('iot/home/index');
    }

    /**
     * 设备类别
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function deviceCate(Request $request)
    {
        return view('iot/home/devicecate');
    }

    /**
     * 设备列表
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function deviceList(Request $request)
    {
        $devList = Db::name('iot_device')->select();
        return view('iot/home/devicelist',['devList' => $devList]);
    }

    /**
     * 设备开关
     * @param Request $request
     * @return mixed
     */
    public function turnOn(Request $request)
    {
        if (!$request->has('deviceid')) {
            return json(['code' => -1, 'message' => '字段错误']);
        }
        $devid = $request->get('deviceid');
        if ($devid == '') {
            return json(['code' => -1, 'message' => '字段错误']);
        }
        $devRes = Db::name('iot_device')->where('deviceid', $devid)->find();
        if (!$devRes) {
            return json(['code' => -1, 'message' => '未找到该设备']);
        }
        $setting = Db::name('iot_apisettings')->where('id', $devRes['setting_id'])->find();
        if (!$setting) {
            return json(['code' => -1, 'message' => '未找到设备配置信息']);
        }

        $reqData     = [
            'deviceid' => $devid,
            'params'   => json_encode(json_decode($devRes['params'], true)['开']),
            'ts'       => time(),
            'appid'    => $setting['appid'],
            'nonce'    => randomkeys(8),
            'version'  => 8,
        ];
        $deviceLogic = model('GetIOTDeviceAPIData', 'logic');
        $res         = $deviceLogic->turnOn(json_encode($reqData), 'Bearer ' . $setting['token']);
        $res         = json_decode($res, true);
        if ($res['error'] == 460) {
            $token = $deviceLogic->getRefToken($setting['token']);
            $token = json_decode($token, true);
            if (isset($token['error'])) {
                return json(['code' => -1, 'message' => '打开失败']);
            }
            $res = $deviceLogic->turnOn(json_encode($reqData), 'Bearer ' . $token['at']);
            $res = json_decode($res, true);
            Db::name('iot_apisettings')->where('id', $setting['id'])->update([
                'token'         => $token['at'],
                'refresh_token' => $token['rt'],
            ]);
        }
        if ($res['error'] != 0) {
            if ($res['error'] == 503) {
                Db::name('iot_device')->where('deviceid', $devid)->update(['dev_status' => false]);
            }
            return json(['code' => -1, 'message' => '打开失败']);
        }
        return json(['code' => 0, 'message' => '已打开']);
//        {"deviceid":"100044a137","params":JSON.stringify({"switch":"on"}),"ts":1548220917,"appid":"1xMdjbmOBYctEJfye4EjFLR2M6YpYyyJ","nonce":"jowoqm2p","version":8}
    }
}
