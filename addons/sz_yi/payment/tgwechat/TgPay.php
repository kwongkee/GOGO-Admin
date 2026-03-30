<?php

/**
 * Class Tgpay
 * 聚合支付，商店正式使用；
 * 服务费支付回调  customs_directpostage_order
 * http://shop.gogo198.cn/addons/sz_yi/payment/tgwechat/ServiceNotify.php
 */
class Tgpay {
    private static $instance;

    final private function __construct(){}
    //防止克隆
    final private  function __clone(){}

    static function instance() {
        if((self::$instance == null) && !(self::$instance instanceof self)){
            self::$instance = new self;
        }
        return self::$instance;
    }

    //微信支付
    public function wechat($params,$config) {
        $package = array();
        $package['account']    = $config['account'];
        $package['appId']	   = $config['appId'];
        $package['payMoney']   = $params['fee'];
        $package['lowOrderId'] = $params['tid'];
        $package['body'] 	   = $params['title'];
        $package['notifyUrl']  = $params['notifyUrl'];//'http://shop.gogo198.cn/addons/sz_yi/payment/tgwechat/notify.php';//后台回调地址
        //$package['returnUrl']  = 'http://shop.gogo198.cn/app/index.php?i=14&c=entry&m=ewei_shopv2&do=mobile&r=parking.msg';//成功回调地址；
        //$package['returnUrl']  = "http://shop.gogo198.cn/app/index.php?i={$params['uniacid']}&c=entry&p=index&do=shop&m=sz_yi";
        $package['returnUrl']  = $params['returnUrl'];
        $package['openId'] 	   = $params['openid'];
        //$package['isMinipg']   = '2';
        //转换key=value&key=value;
        $str = $this->ArrTostring($package);
        //拼接加密字串
        $str .= '&key=' . $config['key'];
        //MD5加密字串
        $sign = md5($str);
        //返回加密字串转换成大写字母
        $package['sign'] = strtoupper($sign);
        //数据包转换成json格式
        $data =  json_encode($package);
        //请求报文
        file_put_contents('./log/postJson.txt',$data);

        //数据请求地址，post形式传输
        $url = 'https://ipay.833006.net/tgPosp/payApi/wxJspay';
        //数据请求地址，post形式传输
        $response = $this->JsonPost($url,$data);
        //解析json数据
        $response = json_decode($response,TRUE);
        //2018-07-06
        $response['c_time'] = date('Y-m-d H:i:s',time());
        $c  = array_merge($package,$response);
        file_put_contents('./log/WechatReson.txt', print_r($c,TRUE));
        //返回数组
        return $response;
    }

    //支付宝支付
    public function alipay($params,$config) {
        $package = array();
        $package['account'] 	= $config['account'];
        $package['payMoney'] 	= $params['fee'];
        $package['lowOrderId']  = $params['tid'];
        $package['body'] 		= $params['title'];
        $package['notifyUrl'] 	= $params['notifyUrl'];//'http://shop.gogo198.cn/addons/sz_yi/payment/tgalipay/notify.php';//后台回调地址
        $package['payType'] 	= '1';//
        //转换key=value&key=value;
        $str = $this->ArrTostring($package);
        //拼接加密字串
        $str .= '&key=' . $config['key'];

        //MD5加密字串
        $sign = md5($str);
        //返回加密字串转换成大写字母
        $package['sign'] = strtoupper($sign);
        //数据包转换成json格式
        $data =  json_encode($package);
        //数据请求地址，post形式传输
        $url = 'https://ipay.833006.net/tgPosp/services/payApi/unifiedorder';
        //数据请求地址，post形式传输
        $response = $this->JsonPost($url,$data);
        //解析json数据
        $response = json_decode($response,TRUE);
        $response['c_time'] = date('Y-m-d H:i:s',time());
        file_put_contents('./log/alipay.txt', print_r($response,TRUE),FILE_APPEND);
        return $response;
    }

    //字符串拼接
    protected function ArrTostring($arrs) {
        ksort($arrs);
        $str = '';
        foreach ($arrs as $key => $v ) {
            if ($v=='' || $v == null) {
                continue;
            }
            $str .= $key . '=' . $v . '&';
        }
        $str = trim($str,'&');

        return $str;
    }

    //数据请求
    protected function JsonPost($url,$post_data) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // 跳过证书检查
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, true);  // 从证书中检查SSL加密算法是否存在
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS,$post_data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Content-Length: ' . strlen($post_data)));
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }
}

?>