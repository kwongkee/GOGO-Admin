<?php

namespace app\mobile\controller;

use app\mobile\controller;
use think\Request;
use think\Db;

class ResidentInvitation extends IotAuth
{
    public function index()
    {
        return view("iot/user/resident");
    }

    /**
     * 邀请并提交注册信息
     * @param Request $request
     * @return mixed
     */
    public function storeInvitInfo(Request $request)
    {
        $data = $request->post();
        if ($data['tel'] == '') {
            return json(['code' => -1, 'message' => '手机号不能为空']);
        }
        if ($data['name'] == '') {
            return json(['code' => -1, 'message' => '姓名不能为空']);
        }
        if ($data['unit'] == '') {
            return json(['code' => -1, 'message' => '所属单位不能为空']);
        }
        if ($data['expire_time'] == '' || !is_numeric($data['expire_time'])) {
            return json(['code' => -1, 'message' => '有效期单位1个或多个数字']);
        }
        $userModel = model('IotUser', 'model');
        $user      = $userModel->findUserInfoByWhere(['phone' => $data['tel']]);
        try {
            if ($user) {
                if ($user['pid'] == 0) {
                   return json(['code' => -1, 'message' => '已存在账号']);
                } else {
                    $userModel->updateUser(['phone' => $data['tel']], [
                        'name'        => $data['name'],
                        'unit'        => $data['unit'],
                        'expire_time' => $data['expire_time'],
                        'expire_type' => $data['expire_type'],
                    ]);
                }
            } else {
                $userModel->storageUser([
                    'unique_id'   => md5($data['tel'].mt_rand(1111,99999)),
                    'name'        => $data['name'],
                    'idcard'      => '',
                    'phone'       => $data['tel'],
                    'status'      => 1,
                    'create_time' => time(),
                    'openid'      => '',
                    'uniacid'     => Session('iot_user')['uniacid'],
                    'pid'         => Session('iot_user')['id'],
                    'unit'        => $data['unit'],
                    'expire_time' => $data['expire_time'],
                    'expire_type' => $data['expire_type'],
                ]);
            }
        } catch (\Exception $exception) {
            return  json(['code' => -1, 'message' => '提交失败'.$exception->getMessage()]);
        }
        return json(['code' => 0, 'message' => '已提交']);
    }
}
