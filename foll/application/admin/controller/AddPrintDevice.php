<?php

namespace app\admin\controller;


use app\admin\controller;
use think\Request;
use think\Db;

class AddPrintDevice extends Auth
{
    public function index(Request $request)
    {
        $express = \think\Db::name('customs_express_company_code')->select();
        $data = Db::name('customs_express_electronicface_config')->where('uid', $request->get('uid'))->find();
        return $this->fetch('CustomsSystem/add_print_device/index', ['uid' => $request->get('uid'), 'express' => $express, 'data' => $data]);
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
        if (empty($data['data']['deviceId']) || empty($data['data']['express_code']) || empty($data['data']['tempid'])) {
            return json(['code' => -1, 'message' => '内容不允许为空']);
        }
        $isEmpty = Db::name('customs_express_electronicface_config')->where('uid',$data['uid'])->find();
        if (empty($isEmpty)){
            Db::name('customs_express_electronicface_config')->insert([
                'uid' => $data['uid'],
                'device_id' => $data['data']['deviceId'],
                'express_code' => $data['data']['express_code'],
                'temp_id' => $data['data']['tempid'],
                'partner_id'=>$data['data']['partnerId'],
                'partner_key'=>$data['data']['partnerKey']
            ]);
        }else{
            Db::name('customs_express_electronicface_config')->where('uid',$data['uid'])->update([
                'device_id' => $data['data']['deviceId'],
                'express_code' => $data['data']['express_code'],
                'temp_id' => $data['data']['tempid'],
                'partner_id'=>$data['data']['partnerId'],
                'partner_key'=>$data['data']['partnerKey']
            ]);
        }

        return json(['code' => 0, 'message' => '已保存']);
    }
}