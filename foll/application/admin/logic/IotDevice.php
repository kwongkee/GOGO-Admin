<?php

namespace app\admin\logic;

use think\Model;
use think\Db;
use think\Hmac;

class IotDevice extends Model
{
    public function verDeviceField($data)
    {
        if (!isset($data['device_name']) || $data['device_name'] == '') {
            return '请填写名称';
        }
        if (!isset($data['deviceid']) || $data['deviceid'] == '') {
            return '请填写设备id';
        }

        if (!isset($data['setting_id']) || $data['setting_id'] == '') {
            return '请选择接口配置';
        }
        if (!isset($data['class_id']) || $data['class_id'] == '') {
            return '请选择分类';
        }
        if (isset($data['id'])){
            if ($data['id']==''){
                return '参数错误';
            }
        }
    }

    /**
     * 入库
     * @param $data
     * @throws \Exception
     */
    public function storageDevice($data)
    {
        $response = $this->getDeviceInfoByApi($data);
        if (!isset($response['error'])){
            if ($response){
                $data['dev_status'] = $response['online'];
                $data['apikey'] = $response['apikey'];
                $data['online_time'] = strtotime($response['onlineTime']);
                $data['settings'] = json_encode($response);
            }
        }

        Db::name('iot_device')->insert($data);
    }


    public function updateDeviceData($data)
    {
        $id =  $data['id'];
        unset($data['id']);
        $response = $this->getDeviceInfoByApi($data);
        if (!isset($response['error'])){
            if ($response){
                $data['dev_status'] = $response['online'];
                $data['apikey'] = $response['apikey'];
                $data['online_time'] = strtotime($response['onlineTime']);
                $data['settings'] = json_encode($response);
            }
        }


        Db::name('iot_device')->where('id',$id)->update($data);
    }


    /**
     * 获取设备信息
     * @param $data
     * @return mixed
     * @throws \Exception
     */
    public function getDeviceInfoByApi($data)
    {
        $getDeviceLogic = model('GetIOTDeviceAPIData', 'logic');
        $token = Db::name('iot_apisettings')->where('id',$data['setting_id'])->find();
        $response = $getDeviceLogic->getSingleDeviceInfo($data['deviceid'], "Bearer " . $token['token']);
        $response = json_decode($response, true);
        return $response;
    }

}
