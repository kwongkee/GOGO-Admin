<?php

/**
 * 发送短信验证码
 */
include_once IA_ROOT . '/addons/mer_chats/vendor/alidayu/top/TopClient.php';
include_once IA_ROOT . '/addons/mer_chats/vendor/alidayu/top/ResultSet.php';
include_once IA_ROOT . '/addons/mer_chats/vendor/alidayu/top/TopLogger.php';
include_once IA_ROOT . '/addons/mer_chats/vendor/alidayu/top/RequestCheckUtil.php';
include_once IA_ROOT . '/addons/mer_chats/vendor/alidayu/top/request/AlibabaAliqinFcSmsNumSendRequest.php';

class Sendsms{

    private static  $getInstance = null;

    private function __construct(){}

    private function __clone(){}

    public static function getInstance() {
        if(self::$getInstance === null) {
            self::$getInstance = new Static();
        }
        return self::$getInstance;
    }

    // 发送验证码
    public function Send($phone,$yzm) {
        $client = new \TopClient;
        $client->appkey = '23583756';
        $client->secretKey ='0ba6116a41c1b994994e5504543010fb';
        $req = new \AlibabaAliqinFcSmsNumSendRequest;
        $req->setSmsType("normal");
        $req->setSmsFreeSignName('Gogo购购网');
        $req->setSmsParam(json_encode(['code'=>$yzm,'product'=>'商户注册']));
        $req->setRecNum($phone);//参数为用户的手机号码
        $req->setSmsTemplateCode('SMS_35030091');
        $resp = $client->execute($req);
        $resp = json_decode(json_encode($resp),true);
        return $resp;
    }

}


?>