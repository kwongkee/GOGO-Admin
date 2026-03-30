<?php

namespace app\admin\controller;

use think\Request;
use think\Db;

/**
 * 物联网系统
 * Class IotSystemController.
 */
class IotSystemController extends Auth
{
    /**
     * 常驻用户管理.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function residentUser()
    {
        $total = Db::name('iot_users')->count();
        $userList = Db::name('iot_users')->paginate(10, $total,
            ['query' => ['s' => 'admin/iot/user'], 'var_page' => 'page']);

        return view('iot/user/residen_user_index',
            ['title' => '常驻用户管理', 'page' => $userList->render(), 'user' => $userList]);
    }

    /**
     * 审核用户.
     *
     * @param Request $request
     *
     * @return mixed
     */
    public function checkUser(Request $request)
    {
        if (!$request->has('id')) {
            return json(['code' => -1, 'message' => '参数错误']);
        }
        if (!$request->has('status') || !is_numeric($request->get('status'))) {
            return json(['code' => -1, 'message' => '参数错误']);
        }

        if ($request->get('status') == 1) {
            $userRes = Db::name('iot_users')->where('unique_id', $request->get('id'))->find();
            $msg = '{"touser":"'.$userRes['openid'].'","msgtype":"text","text":{"content":"已通过,平台域名：http://shop.gogo198.cn/foll/public/?s=iot,登录账户：'.$userRes['phone'].'"}}';
//            $msg = ['touser'=>$userRes['openid'],'msgtype'=>'text','text'=>['content'=>'已通过审核,系统地址：http://shop.gogo198.cn/foll/public/?s=iot']];
            $token = RequestAccessToken($userRes['uniacid']);
            $url = 'https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token='.$token;
            httpRequest($url, json_encode($msg), ['Content-Type: application/json']);
        }

        Db::name('iot_users')->where('unique_id', $request->get('id'))->update(['status' => $request->get('status')]);

        return json(['code' => 0, 'message' => '成功']);
    }

    /**
     * 删除.
     *
     * @param Request $request
     *
     * @return mixed
     */
    public function delUser(Request $request)
    {
        if (!$request->has('id')) {
            return json(['code' => -1, 'message' => '参数错误']);
        }
        Db::name('iot_users')->where('unique_id', $request->get('id'))->delete();

        return json(['code' => 0, 'message' => '成功']);
    }

    /**
     * 设备接口信息配置列表.
     *
     * @param Request $request
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function devApiSetting(Request $request)
    {
        $total = Db::name('iot_apisettings')->count();
        $list = Db::name('iot_apisettings')->paginate(10, $total,
            ['query' => ['s' => 'admin/iot/apisetting'], 'var_page' => 'page']);

        return view('iot/config/api_setting_index',
            ['title' => '接口配置', 'page' => $list->render(), 'data' => $list->toArray()['data']]);
    }

    /**
     * 添加配置页面和保存信息.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function addApiSetting(Request $request)
    {
        if ($request->isGET()) {
            return view('iot/config/api_setting_add', ['title' => '添加']);
        }
        if ($request->isPOST()) {
            $data = $request->post();
            $logic = model('IotApiSetting', 'logic');
            if ($err = $logic->verSaveApiSettingField($data)) {
                return json(['code' => -1, 'message' => $err]);
            }

            try {
                $logic->storageApiSetting($data);
            } catch (\Exception $exception) {
                return json(['code' => -1, 'message' => $exception->getMessage()]);
            }

            return json(['code' => 0, 'message' => '添加成功']);
        }
    }

    /**
     * 删除设备接口信息配置.
     *
     * @param Request $request
     *
     * @return mixed
     */
    public function delApiSetting(Request $request)
    {
        if ($request->get('id') == '') {
            return json(['code' => -1, 'message' => '参数为空']);
        }
        Db::name('iot_apisettings')->where('id', $request->get('id'))->delete();

        return json(['code' => 0, 'message' => '已删除']);
    }

    /**
     * 编辑更新设备接口信息配置.
     *
     * @param Request $request
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function editApiSetting(Request $request)
    {
        if ($request->isGET()) {
            if ($request->get('id') == '') {
                abort(401, '参数错误');
            }
            $data = Db::name('iot_apisettings')->where('id', $request->get('id'))->find();

            return view('iot/config/api_setting_edit', ['data' => $data, 'title' => '编辑']);
        }
        if ($request->isPOST()) {
            $logic = model('IotApiSetting', 'logic');
            if ($err = $logic->verSaveApiSettingField($request->post())) {
                return json(['code' => -1, 'message' => $err]);
            }
            try {
                $logic->updateApiSetting($request->post());
            } catch (\Exception $exception) {
                return json(['code' => -1, 'message' => $exception->getMessage()]);
            }

            return json(['code' => 0, 'message' => '更新成功']);
        }
    }

    /**
     * 邀请费用支付设置.
     *
     * @param Request $request
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function paySetting(Request $request)
    {
        if ($request->isGET()) {
            $res = Db::name('iot_pay_setting')->find();

            return view('iot/config/pay_setting', ['title' => '邀请费用支付设置', 'res' => $res]);
        }
        if ($request->isPOST()) {
            $postData = $request->post();
            if (!isset($postData['id']) || !isset($postData['money'])) {
                return json(['code' => -1, 'message' => '参数错误']);
            }
            if ($postData['id'] == '') {
                Db::name('iot_pay_setting')->insert(['money' => $postData['money']]);
            } else {
                Db::name('iot_pay_setting')->where('id', $postData['id'])->update(['money' => $postData['money']]);
            }

            return json(['code' => 0, 'message' => '操作成功']);
        }
    }

    /**
     * 设备分类管理.
     *
     * @param Request $request
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function devClass(Request $request)
    {
        $total = Db::name('iot_devclass')->count();
        $list = Db::name('iot_devclass')->order('id', 'desc')->paginate(10, $total,
            ['query' => ['s' => 'admin/iot/devclass'], 'var_page' => 'page']);

        return view('iot/device/dev_class',
            ['title' => '设备分类管理', 'page' => $list->render(), 'list' => $list->toArray()['data']]);
    }

    /**
     * 分类添加.
     *
     * @param Request $request
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function addDevClass(Request $request)
    {
        if ($request->isGET()) {
            return view('iot/device/add_class', ['title' => '设备分类添加']);
        }

        if ($request->isPOST()) {
            if (!$request->has('name')) {
                return json(['code' => -1, 'message' => '参数错误']);
            }
            Db::name('iot_devclass')->insert(['name' => $request->post('name')]);

            return json(['code' => 0, 'message' => '添加成功']);
        }
    }

    /**
     * 更改设备分类.
     *
     * @param Request $request
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function modifyDevClass(Request $request)
    {
        if ($request->isGET()) {
            if (!$request->has('id')) {
                return '参数错误';
            }
            $res = Db::name('iot_devclass')->where('id', $request->get('id'))->find();

            return view('iot/device/modify_class', ['title' => '变更分类信息', 'data' => $res]);
        }
        if ($request->isPOST()) {
            if (!$request->has('name')) {
                return json(['code' => -1, 'message' => '参数错误']);
            }
            if (!$request->has('id')) {
                return json(['code' => -1, 'message' => '参数错误']);
            }
            Db::name('iot_devclass')->where('id', $request->post('id'))->update(['name' => $request->post('name')]);

            return json(['code' => 0, 'message' => '更新完成']);
        }
    }

    /**
     * 删除设备分类信息.
     *
     * @param Request $request
     *
     * @return mixed
     */
    public function delDevClass(Request $request)
    {
        if (!$request->has('id')) {
            return json(['code' => -1, 'message' => '参数错误']);
        }
        Db::name('iot_devclass')->where('id', $request->get('id'))->delete();

        return json(['code' => 0, 'message' => '删除完成']);
    }

    /**
     * 硬件设备管理.
     *
     * @param Request $request
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function deviceManage(Request $request)
    {
        $total = Db::name('iot_device')->count();
        $devList = Db::name('iot_device')
            ->alias('a')
            ->join('iot_apisettings b', 'a.setting_id=b.id')
            ->field(['a.*', 'b.api_name'])
            ->paginate(10, $total, ['query' => ['s' => 'admin/iot/devicelist'], 'var_page' => 'page']);

        return view('iot/device/device_list',
            ['title' => '设备列表', 'page' => $devList->render(), 'data' => $devList->toArray()['data']]);
    }

    /**
     * 添加硬件设备.
     *
     * @param Request $request
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function addDevice(Request $request)
    {
        if ($request->isGET()) {
            $setting = Db::name('iot_apisettings')->select();
            $cate = Db::name('iot_devclass')->select();

            return view('iot/device/device_add', ['title' => '添加', 'setting' => $setting, 'cate' => $cate]);
        }
        if ($request->isPOST()) {
            $logic = model('IotDevice', 'logic');
            if ($err = $logic->verDeviceField($request->post())) {
                return json(['code' => -1, 'message' => $err]);
            }

            try {
                $logic->storageDevice($request->post());
            } catch (\Exception $exception) {
                return json(['code' => -1, 'message' => $exception->getMessage()]);
            }

            return json(['code' => 0, 'message' => '添加成功']);
        }
    }

    /**
     * 编辑硬件设备信息.
     *
     * @param Request $request
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function editDevice(Request $request)
    {
        if ($request->isGET()) {
            if ($request->get('id') == '') {
                abort(401, '参数错误');
            }
            $setting = Db::name('iot_apisettings')->field(['id', 'api_name'])->select();
            $data = Db::name('iot_device')->where('id', $request->get('id'))->find();
            $cate = Db::name('iot_devclass')->select();

            return view('iot/device/device_edit',
                ['title' => '编辑', 'setting' => $setting, 'cate' => $cate, 'data' => $data]);
        }

        if ($request->isPOST()) {
            $logic = model('IotDevice', 'logic');
            if ($err = $logic->verDeviceField($request->post())) {
                return json(['code' => -1, 'message' => $err]);
            }

            try {
                $logic->updateDeviceData($request->post());
            } catch (\Exception $exception) {
                return json(['code' => -1, 'message' => $exception->getMessage()]);
            }

            return json(['code' => 0, 'message' => '更新成功']);
        }
    }

    /**
     * 删除设备信息.
     *
     * @param Request $request
     *
     * @return mixed
     */
    public function delDevice(Request $request)
    {
        if ($request->get('id') == '') {
            return json(['code' => -1, 'message' => '参数为空']);
        }
        Db::name('iot_device')->where('id', $request->get('id'))->delete();

        return json(['code' => 0, 'message' => '已删除']);
    }

    /**
     * 访客日志.
     *
     * @param Request $request
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function visitLog(Request $request)
    {
        $verifType = ['face' => '人脸识别', 'idcard' => '身份证', 'qrcode' => '扫码'];
        $flag = ['首次来访', '再次来访'];
        $total = Db::name('iot_invait_record')->count();
        $list = Db::name('iot_invait_record')->alias('a')->join('iot_users b',
            'b.unique_id=a.user_id')->order('id',
            'desc')->field(['a.*', 'b.name'])->paginate(10, $total,
            ['query' => ['s' => 'admin/iot/visitlog'], 'var_page' => 'page']);

        return view('iot/log/list', [
            'title' => '访客日志',
            'verifType' => $verifType,
            'flag' => $flag,
            'page' => $list->render(),
            'data' => $list->toArray()['data'],
        ]);
    }
}
