<?php

namespace app\mobile\controller;

use app\mobile\controller;
use think\Request;
use think\Db;

class VisitLog extends IotAuth
{
    public $verifType = ['face' => '人脸识别', 'idcard' => '身份证', 'qrcode' => '扫码'];

    public function index(Request $request)
    {

        $list = Db::name('iot_invait_record')->where('user_id', Session('iot_user')['unique_id'])->order('id',
            'desc')->limit(0, 10)->select();
        return view('iot/user/log', ['title' => '访客日志', 'verifType' => $this->verifType, 'list' => $list]);
    }

    /**
     * 常驻人员管理
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function permanentUserManage(Request $request)
    {
        $timeType = ['year' => '年', 'month' => '月', 'week' => '周'];
        $list     = Db::name('iot_users')->where('pid', Session('iot_user')['id'])->order('id',
            'desc')->limit(0, 10)->select();
        return view('iot/user/permanent', ['list' => $list, 'timeType' => $timeType]);
    }

    /**
     * 流加载获取人员信息
     * @param Request $request
     * @return mixed
     */
    public function askGetPermanentList(Request $request)
    {
        $timeType  = ['year' => '年', 'month' => '月', 'week' => '周'];
        $page      = $request->has('page') ? $request->get('page') : 0;
        $indexPage = ($page - 1) * 10;
        $list      = Db::name('iot_users')->where('pid', Session('iot_user')['id'])->order('id',
            'desc')->limit($indexPage, 10)->select();
        if (!$list) {
            return json(['code' => 0, 'message' => '完成', 'data' => '']);
        }
        foreach ($list as &$value) {
            $value['expire_type'] = $timeType[$value['expire_type']];
        }
        return json(['code' => 0, 'message' => '完成', 'data' => $list]);
    }

    /**
     * 流加载获取数据
     * @param Request $request
     * @return mixed
     */
    public function askData(Request $request)
    {
        $page      = $request->has('page') ? $request->get('page') : 0;
        $indexPage = ($page - 1) * 10;
        $list      = Db::name('iot_invait_record')->where('user_id', Session('iot_user')['unique_id'])->order('id',
            'desc')->limit($indexPage, 10)->select();
        if (!$list) {
            return json(['code' => 0, 'message' => '完成', 'data' => '']);
        }
        foreach ($list as &$value) {
            $value['verif_type']  = $this->verifType[$value['verif_type']];
            $value['invait_time'] = date('Y-m-d', $value['invait_time']);
            $value['idname']      = $value['idname'] == '' ? '无' : $value['idname'];
        }
        return json(['code' => 0, 'message' => '完成', 'data' => $list]);
    }

    /**
     * 访客详情
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function visitorDetail(Request $request)
    {
        if (!$request->has('id')) {
            abort(404, '参数错误');
        }
        $data = Db::name('iot_invait_record')->where('id', $request->get('id'))->find();
        if (!$data) {
            return false;
        }
        $sharUrl = Url('iot/inviteverif') . '&code=' . $data['qr_code'];
        return view('iot/user/information', [
            'data'    => $data,
            'verif'   => ['idcard' => '身份验证', 'qrcode' => '扫码', 'face' => '人脸识别'],
            'sharurl' => $sharUrl,
        ]);
    }
}
