<?php

namespace app\mobile\controller;

use think\Request;
use think\Controller;
use think\Db;
use think\VerifIdCard;

class IotInvitedVerif extends Controller
{
    /**
     * 访客验证
     */
    public function index(Request $request)
    {
        if (!$request->has('code')) {
            abort(401, '参数错误');
        }
        $invited = Db::name('iot_invait_record')->where('qr_code', $request->get('code'))->whereOr('order_id',
            $request->get('code'))->find();
        if (!$invited) {
            abort(401, '错误访问');
        }
        $isPay = Db::name('iot_order')->where(['order_id' => $invited['order_id'], 'pay_status' => 1])->find();
        if (!$isPay) {
            abort(401, '错误访问');
        }

        $timer = time();
        if ($timer < $invited['invait_time']) {
            abort(406, '未到访问时间');
        }
        $invited['expire_time'] = $invited['created_at'] + ($invited['expire_time'] * 60 * 60);
        if ($timer > $invited['expire_time']) {
            abort(406, '已超出访问时间');
        }
        $tpl = null;
        switch ($invited['verif_type']) {
            case 'face':
                $tpl = 'face';
                break;
            case 'idcard':
                $tpl = 'idcard';
                break;
            case 'qrcode':
                $tpl = 'qrcode';
                $this->reqOpen('100044a137');
                Db::name('iot_invait_record')->where('qr_code',
                    $request->get('code'))->update(['updated_at' => time()]);
                break;
        }
//        halt();
        return view('iot/invitedverif/' . $tpl, ['code' => $invited['qr_code']]);
    }

    /**
     * 人脸识别验证
     *
     * @param Request $request
     *
     * @return mixed
     */
    public function faceVerif(Request $request)
    {
        $file = $request->file('video');
        $data = $request->post('data');
        $data = json_decode($data, true);
        if (!isset($data['code']) || $data['code'] == '') {
            return json(['code' => -1, 'message' => '参数错误']);
        }
        if (!$file) {
            return json(['code' => -1, 'message' => '请上传视频文件']);
        }
        $path = ROOT_PATH . 'public/' . 'uploads/face/video';
//        $info = $file->validate(['size' => 12000000, 'ext' => 'mp4'])->move($path);
        $info = $file->move($path);
        if (!$info) {
            return json(['code' => -1, 'message' => '读取视频文件错误']);
        }
        $fileName = $info->getSaveName();
        $logic    = model('HuaWeiFace', 'logic');
        if (!file_exists($path . '/' . $fileName)) {
            return json(['code' => -1, 'message' => '保存文件识别']);
        }
        $matchResult = $logic->liveDetect(base64_encode(file_get_contents($path . '/' . $fileName)));
        $matchResult = json_decode($matchResult, true);
        if (isset($matchResult['error_code'])) {
            if ($matchResult['error_code'] == 'FRS.0706') {
                return json(['code' => -1, 'message' => '视频时长太长']);
            } else {
                return json(['code' => -1, 'message' => '识别失败']);
            }
        }
        if (!$matchResult['video-result']['alive']) {
            return json(['code' => -1, 'message' => '检测不到活体']);
        }
        $record                = Db::name('iot_invait_record')->where('qr_code', $data['code'])->find();
        $record['expire_time'] = $record['created_at'] + ($record['expire_time'] * 60 * 60);
        if (time() > $record['expire_time']) {
            return json(['code' => -1, 'message' => '已超出时间']);
        }
        $image1      = $matchResult['video-result']['picture'];
        $image2      = base64_encode(file_get_contents(ROOT_PATH . 'public/' . 'uploads/face/' . $record['file']));
        $matchResult = $logic->faceCompare($image1, $image2);
        $matchResult = json_decode($matchResult, true);
        if (isset($matchResult['error_code'])) {
            return json(['code' => -1, 'message' => '匹配失败']);
        }
        if ($matchResult['similarity'] < 0.90) {
            return json(['code' => -1, 'message' => '人脸匹配不正确']);
        }

        Db::name('iot_invait_record')->where('qr_code', $data['code'])->update(['updated_at' => time()]);
        $this->reqOpen('100044a137');

        return json(['code' => 0, 'message' => '成功']);
    }

    /**
     * 身份证验证
     *
     * @param Request $request
     *
     * @return mixed
     */
    public function idCardVerif(Request $request)
    {
        $data = $request->post();
        if ($data['code'] == '') {
            return json(['code' => -1, 'message' => 'code参数错误']);
        }
        if ($data['tel'] == '') {
            return json(['code' => -1, 'message' => '请填写手机号']);
        }
        if ($data['idname'] == '') {
            return json(['code' => -1, 'message' => '请填写姓名']);
        }
        if ($data['idcard'] == '') {
            return json(['code' => -1, 'message' => '请填写身份证号']);
        }
        if (!(VerifIdCard::validation_filter_id_card($data['idcard']))) {
            return json(['code' => -1, 'message' => '身份证号格式不正确']);
        }
        $recoRes = Db::name('iot_invait_record')->where('qr_code', $data['code'])->find();
        if (!$recoRes) {
            return json(['code' => -1, 'message' => '邀请不存在']);
        }
        if ($recoRes['idcard'] != $data['idcard']) {
            return json(['code' => -1, 'message' => '邀请身份号码不匹配']);
        }
        if ($recoRes['idname'] != $data['idname']) {
            return json(['code' => -1, 'message' => '邀请身份姓名不匹配']);
        }
        if ($recoRes['tel'] != $data['tel']) {
            return json(['code' => -1, 'message' => '邀请手机号不匹配']);
        }

        $recoRes['expire_time'] = $recoRes['created_at'] + ($recoRes['expire_time'] * 60 * 60);
        if (time() > $recoRes['expire_time']) {
            return json(['code' => -1, 'message' => '已超出时间']);
        }

        Db::name('iot_invait_record')->where('qr_code', $data['code'])->update(['updated_at' => time()]);
        $this->reqOpen('100044a137');

        return json(['code' => 0, 'message' => '成功']);
    }

    public function reqOpen($did)
    {
        $open = Url('iot/dooron');
        $open = $open . '&deviceid=' . $did;
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL            => $open,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING       => '',
            CURLOPT_MAXREDIRS      => 10,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST  => 'GET',
            CURLOPT_POSTFIELDS     => '',
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'cache-control: no-cache',
            ],
        ]);
        curl_exec($curl);
        curl_close($curl);
    }
}
