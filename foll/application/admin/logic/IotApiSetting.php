<?php

namespace app\admin\logic;

use think\Model;
use think\Db;
use think\Hmac;

class IotApiSetting extends Model
{

    /**
     * 验证添加设备接口配置
     * @param $data
     * @return string
     */
    public function verSaveApiSettingField($data)
    {
        if ($data['phone'] == '' && $data['email'] == '') {
            return '请填写手机或邮箱其中一个';
        }

        if (!isset($data['api_name']) || $data['api_name'] == '') {
            return '请填写名称';
        }

        if (!isset($data['password']) || $data['password'] == '') {
            return '请填写密码';
        }

        if (!isset($data['appid']) || $data['appid'] == '') {
            return '请填写appid';
        }
        if (!isset($data['appsecret']) || $data['appsecret'] == '') {
            return '请填写appsecret';
        }
        if (isset($data['id'])) {
            if ($data['id'] == '') {
                return 'id不存在';
            }
        }
        return null;
    }


    /**
     * 入库
     * @param $data
     * @throws \Exception
     */
    public function storageApiSetting($data)
    {
        $data = $this->getDeviceLoginInfoByApi($data);
        $data['create_at'] = time();
        Db::name('iot_apisettings')->insert($data);
    }


    /**
     * 更新
     * @param $data
     * @throws \Exception
     */
    public function updateApiSetting($data)
    {
        $data = $this->getDeviceLoginInfoByApi($data);
        $id = $data['id'];
        unset($data['id']);
        Db::name('iot_apisettings')->where('id', $id)->update($data);
    }

    /**
     * 获取硬件接口登录信息
     * @param $data
     * @return mixed
     * @throws \Exception
     */
    public function getDeviceLoginInfoByApi($data)
    {
        $getDeviceLogic = model('GetIOTDeviceAPIData', 'logic');
        $sign = new Hmac();
        if ($data['phone']==''){
            $reqData = json_encode(["appid" => $data['appid'], "nonce" => randomkeys(8), "ts" => time(), "version" => 8, "email" => $data['email'], "password" => $data['password']]);

        }else{
            $reqData = json_encode(["appid" => $data['appid'], "nonce" => randomkeys(8), "ts" => time(), "version" => 8, "phoneNumber" => "+86" . $data['phone'], "password" => $data['password']]);

        }
        $token = $sign->generateHashSignature($reqData, $data['appsecret']);
        $response = $getDeviceLogic->getTokenInfo($reqData, "Sign " . $token);
        $response = json_decode($response, true);
        if (isset($response['error'])) {
            throw new \Exception($response['error']);
        }
        $data['token'] = $response['at'];
        $data['refresh_token'] = $response['rt'];
        $data['info'] = json_encode($response['user']);
        return $data;
    }
}
