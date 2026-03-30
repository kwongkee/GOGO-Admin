<?php

namespace app\admin\logic;

use think\Model;
use think\Db;


class CustomsOrderLogic extends Model
{
    public function orderElecChange($id)
    {
        if (!is_numeric($id)) {
            return json(['code' => -1, 'message' => 'id 值错误']);
        }
        $order = $this->getOrderByid($id);
        if (!$order) {
            return json(['code' => -1, 'message' => '订单信息不存在']);
        }
        $result = $this->requestPayAPI($order['EntOrderNo']);
        $result = json_decode($result,true);
        if (empty($result)||$result['code']<0){
            $this->saveErrorInfo('系统一次或参数错误',$id);
            return json(['code' => -1, 'message' => '系统异常']);
        }

        if ($result['data']['code']==1){
            //更新状态
            Db::name('customs_elec_order_detail')->where('id',$id)->update(['expro_status'=>2]);
            return json(['code' => 0, 'message' => '请求处理成功']);
        }
        $err = is_array($result['data']['info'])?$result['data']['info']['resultMsg']:$result['data']['info'];
        $this->saveErrorInfo($err,$id);
        return json(['code' => -1, 'message' => $err]);
    }



    /**
     * 获取订单信息
     * @param $id
     * @return mixed
     */
    protected function getOrderByid($id)
    {
        return Db::name('customs_elec_order_detail')->where('id', $id)->field(['EntOrderNo'])->find();
    }


    //保存申报错误信息
    protected function saveErrorInfo($err, $id)
    {
        Db::name('customs_elec_order_detail')->where('id', $id)->update(['err_msg' => $err]);
    }


    /**
     * 请求支付变更并且申报
     * @param string $orderId 订单编号
     * @return bool|mixed|string
     */
    protected function requestPayAPI($orderId)
    {
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://declare.gogo198.cn/api/OrderCustomChange/sendChange?orderId=".$orderId,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_POSTFIELDS => "",
            CURLOPT_HTTPHEADER => array(
                "cache-control: no-cache"
            ),
        ));
        $response = curl_exec($curl);
        curl_close($curl);
        return $response;
    }

}