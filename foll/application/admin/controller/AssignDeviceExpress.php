<?php

namespace app\admin\controller;

use app\admin\controller;
use think\Request;
use think\Db;

class AssignDeviceExpress extends Auth
{
    public function index(Request $request)
    {
        $uid = $request->get('uid');
        //step1:找出设备&快递公司
        $device = Db::name('hjxssl_device')->order('id','desc')->select();
        $express = Db::name('hjxssl_express')->order('id','desc')->select();
        foreach($express as $k=>$v){
            $express[$k]['name'] = Db::name('customs_express_company_code')->where('code',$v['code'])->field('name')->find()['name'];
        }
        $device = json_encode($device);
        $express = json_encode($express);
        //step2:找出商户已选择的设备&快递公司
        $deex = Db::name('hjxssl_assign_device_express')->where('uid',$uid)->find();
        if(empty($deex)){
            $deex['device_id'] = '';
            $deex['express_id'] = '';
        }

        return $this->fetch('CustomsSystem/assign_device_express/index', ['uid' => $uid,'device'=>$device,'express'=>$express,'deex'=>$deex]);
    }

    /**
     * 保存配置
     * @param  Request  $request
     * @return \think\response\Json
     */
    public function saveConfig(Request $request)
    {
        $data = $request->post();
        if (!is_numeric($data['uid']) || $data['uid'] == "") {
            return json(['code' => -1, 'message' => '参数错误']);
        }
        if (empty($data['data']['device']) || empty($data['data']['express'])) {
            return json(['code' => -1, 'message' => '内容不允许为空']);
        }

        $isEmpty = Db::name('hjxssl_assign_device_express')->where('uid',$data['uid'])->find();
        if (empty($isEmpty)){
            Db::name('hjxssl_assign_device_express')->insert([
                'uid' => $data['uid'],
                'device_id' => $data['data']['device'],
                'express_id' => $data['data']['express'],
            ]);
        }else{
            Db::name('hjxssl_assign_device_express')->where('uid',$data['uid'])->update([
                'device_id' => $data['data']['device'],
                'express_id' => $data['data']['express'],
            ]);
        }

        return json(['code' => 0, 'message' => '已保存']);
    }
}