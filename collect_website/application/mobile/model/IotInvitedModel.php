<?php

namespace app\mobile\model;

use think\Model;
use think\VerifIdCard;
use think\Db;
use think\Curl;

class IotInvitedModel extends Model
{
    protected $data = null;

    public function invited($data, $request = null)
    {
        $this->data = $data;
        return call_user_func([$this, $data['type']], $request);
//        switch ($data['type']) {
//            case 'face':
//                $file = $request->file('face');
//                if (!$file) {
//                    throw new \Exception('请上传人脸图');
//                }
//
//                return $this->face($file);
//            case 'idcard':
//                if ($data['idName'] == '') {
//                    throw new \Exception('请填写姓名');
//                }
//                if ($data['idCard'] == '') {
//                    throw new \Exception('请填写身份证');
//                }
//                if (!(VerifIdCard::validation_filter_id_card($data['idCard']))) {
//                    throw new \Exception('身份证号格式不正确');
//                }
//
//                return $this->idcard();
//            case 'qrcode':
//                return $this->qrCode();
//            default:
//                throw new \Exception('type参数错误');
//        }
    }

    protected function face($request)
    {
        $file = $request->file('face');
        if (!$file) {
            throw new \Exception('请上传人脸图');
        }

        $path = ROOT_PATH . 'public/' . 'uploads/face';
        $info = $file->validate(['size' => 12000000, 'ext' => 'jpg,png,jpeg'])->move($path);
        if (!$info) {
            throw new \Exception($file->getError());
        }
        $fileName = $info->getSaveName();
        $logic    = model('HuaWeiFace', 'logic');
        $resp     = $logic->addFace(base64_encode(file_get_contents($path . '/' . $fileName)));
        $resp     = json_decode($resp, true);
        $faceId   = null;
        if (!isset($resp['error_code'])) {
            $faceId = $resp['faces'][0]['face_id'];
        }
        $payConf     = Db::name('iot_pay_setting')->find();
        $millisecond = round(explode(' ', microtime())[0] * 1000);
        $order_id    = 'G99198' . date('Ymd', time()) . str_pad($millisecond, 3, '0', STR_PAD_RIGHT) . mt_rand(111,
                999) . mt_rand(111, 999);
        $qrcode      = md5(randomkeys(10) . time());
        Db::name('iot_invait_record')->insert([
            'user_id'     => session('iot_user')['unique_id'],
            'verif_type'  => $this->data['type'],
            'face_id'     => $faceId,
            'idname'      => '',
            'idcard'      => '',
            'qr_code'     => $qrcode,
            'invait_time' => strtotime($this->data['invited_time']),
            'expire_time' => $this->data['invited_expre'],
            'created_at'  => time(),
            'other'       => null,
            'file'        => $fileName,
            'order_id'    => $order_id,
            'is_flag'     => $this->data['visit'],
        ]);
        $orderRes = [
            'user_id'     => session('iot_user')['unique_id'],
            'order_id'    => $order_id,
            'money'       => $payConf['money'],
            'pay_status'  => $payConf['money'] == 0 ? 1 : 0,
            'create_time' => time(),
            'body'        => '邀请支付费用',
        ];
        $this->inserPay($orderRes);
        if ($payConf['money'] <= '0') {
            $url = Url('iot/paysuccess') . '&code=' . base64_encode($qrcode);
        } else {
            $payRes = $this->reqPay($orderRes);
            $payRes = json_decode($payRes, true);
            $url    = 'http://shop.gogo198.cn/payment/wechat/iotpay.php?url=' . base64_encode($payRes['pay_info']);
        }

        return $url;
    }

    /**
     * 身份证方式验证
     * @param null $request
     * @return string
     * @throws \Exception
     */
    protected function idcard($request = null)
    {
        if ($this->data['idName'] == '') {
            throw new \Exception('请填写姓名');
        }
        if ($this->data['idCard'] == '') {
            throw new \Exception('请填写身份证');
        }
        if (!(VerifIdCard::validation_filter_id_card($this->data['idCard']))) {
            throw new \Exception('身份证号格式不正确');
        }

        $payConf     = Db::name('iot_pay_setting')->find();
        $millisecond = round(explode(' ', microtime())[0] * 1000);
        $order_id    = 'G99198' . date('Ymd', time()) . str_pad($millisecond, 3, '0', STR_PAD_RIGHT) . mt_rand(111,
                999) . mt_rand(111, 999);
        $qrcode      = md5(randomkeys(10) . time());
        Db::name('iot_invait_record')->insert([
            'user_id'     => session('iot_user')['unique_id'],
            'verif_type'  => $this->data['type'],
            'face_id'     => '',
            'idname'      => $this->data['idName'],
            'idcard'      => $this->data['idCard'],
            'qr_code'     => $qrcode,
            'invait_time' => strtotime($this->data['invited_time']),
            'expire_time' => $this->data['invited_expre'],
            'created_at'  => time(),
            'other'       => null,
            'file'        => '',
            'order_id'    => $order_id,
            'tel'         => $this->data['tel'],
            'is_flag'     => $this->data['visit'],
        ]);
        $orderRes = [
            'user_id'     => session('iot_user')['unique_id'],
            'order_id'    => $order_id,
            'money'       => $payConf['money'],
            'pay_status'  => $payConf['money'] == 0 ? 1 : 0,
            'create_time' => time(),
            'body'        => '邀请支付费用',
        ];
        $this->inserPay($orderRes);
        if ($payConf['money'] <= '0') {
            $url = Url('iot/paysuccess') . '&code=' . base64_encode($qrcode);
        } else {
            $payRes = $this->reqPay($orderRes);
            $payRes = json_decode($payRes, true);
            $url    = 'http://shop.gogo198.cn/payment/wechat/iotpay.php?url=' . base64_encode($payRes['pay_info']);
        }

        return $url;
    }

    protected function qrCode($request = null)
    {
        $payConf     = Db::name('iot_pay_setting')->find();
        $millisecond = round(explode(' ', microtime())[0] * 1000);
        $order_id    = 'G99198' . date('Ymd', time()) . str_pad($millisecond, 3, '0', STR_PAD_RIGHT) . mt_rand(111,
                999) . mt_rand(111, 999);
        $qrcode      = md5(randomkeys(10) . time());
        Db::name('iot_invait_record')->insert([
            'user_id'     => session('iot_user')['unique_id'],
            'verif_type'  => $this->data['type'],
            'face_id'     => '',
            'idname'      => '',
            'idcard'      => '',
            'qr_code'     => $qrcode,
            'invait_time' => strtotime($this->data['invited_time']),
            'expire_time' => $this->data['invited_expre'],
            'created_at'  => time(),
            'other'       => null,
            'file'        => '',
            'order_id'    => $order_id,
            'tel'         => '',
            'is_flag'     => $this->data['visit'],
        ]);
        $orderRes = [
            'user_id'     => session('iot_user')['unique_id'],
            'order_id'    => $order_id,
            'money'       => $payConf['money'],
            'pay_status'  => $payConf['money'] == 0 ? 1 : 0,
            'create_time' => time(),
            'body'        => '邀请支付费用',
        ];
        $this->inserPay($orderRes);
        if ($payConf['money'] <= '0') {
            $url = Url('iot/paysuccess') . '&code=' . base64_encode($qrcode);
        } else {
            $payRes = $this->reqPay($orderRes);
            $payRes = json_decode($payRes, true);
            $url    = 'http://shop.gogo198.cn/payment/wechat/iotpay.php?url=' . base64_encode($payRes['pay_info']);
        }

        return $url;
    }

    /**
     * @param $order_id
     */
    protected function inserPay($data)
    {
        Db::name('iot_order')->insert($data);
    }

    /**
     * 请求支付.
     *
     * @param $data
     *
     * @return mixed
     */
    public function reqPay($data)
    {
        $reqData = [
            'payMoney'  => $data['money'],
            'ordersn'   => $data['order_id'],
            'body'      => $data['body'],
            'returnUrl' => 'http://shop.gogo198.cn/foll/public/?s=iot/paysuccess&code=' . base64_encode($data['order_id']),
            'openid'    => session('iot_user')['openid'],
            'token'     => 'wxScode', //wxScode、Alipays
        ];
        $curl    = new Curl();
        $curl->setHeader('Content-Type', 'application/json');
        $curl->post('http://shop.gogo198.cn/payment/wechat/Payments.php', json_encode($reqData));
        trace($curl->response, 'info');

        return $curl->response;
    }
}
