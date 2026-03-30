<?php

namespace app\mobile\controller;

use think\Request;
use think\Db;

class IotInvitedToVisit extends IotAuth
{
    public function index()
    {
        return view('iot/invited/index');
    }

    public function invitedIndex()
    {
        return view('iot/invited/invited_index');
    }

    public function invited(Request $request)
    {
        $fromData = $request->post();
        if ($fromData['invited_time'] == '') {
            return json(['code' => -1, 'message' => '请填写到访时间']);
        }
        if ($fromData['invited_expre'] == '' || !is_numeric($fromData['invited_expre'])) {
            return json(['code' => -1, 'message' => '请填写到访时限']);
        }
        if ($fromData['visit'] == '' || !is_numeric($fromData['visit'])) {
            return json(['code' => -1, 'message' => '请填写来访类型']);
        }
        if ($fromData['type'] == '') {
            return json(['code' => -1, 'message' => '请选择验证类型']);
        }

        try {
            $model = model('IotInvitedModel', 'model');
            $result = $model->invited($fromData, $request);
        } catch (\Exception $exception) {
            return json(['code' => -1, 'message' => $exception->getMessage()]);
        }

        return json(['code' => 0, 'message' => '', 'data' => $result]);
    }

    /**
     * 支付成功页面.
     *
     * @param Request $request
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function paySuccessShare(Request $request)
    {
        if (!$request->has('code')) {
            abort(501, '参数错误');
        }
        $code = base64_decode($request->get('code'));
        $oid = Db::name('iot_invait_record')->where('qr_code', $code)->whereOr('order_id', $code)->field('order_id')->find();
        Db::name('iot_order')->where('order_id', $oid['order_id'])->update(['pay_status' => 1]);
        $qrUrl = Url('iot/inviteverif').'&code='.$code;

        return view('iot/invited/pay_success', ['url' => $qrUrl]);
    }
}
