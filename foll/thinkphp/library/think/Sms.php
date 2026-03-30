<?php

namespace think;

class Sms
{
    private static $appCode;//appcode
    private static $SignName;//模板签名
    private static $TemplateCode;//模板id
    private static $url='http://sms.market.alicloudapi.com/singleSendSms';
    public static $bool=false;
    private static $errorCode;
    private static $error=[
        'The specified dayu status is wrongly formed.'=>'账户短信开通状态不正确',
        'The specified sign name is wrongly formed.'=>'短信签名不正确或签名状态不正确',
        'The specified templateCode is wrongly formed.'=>'短信模板Code不正确或者模板状态不正确',
        'The specified recNum is wrongly formed.'=>'目标手机号不正确，单次发送数量不能超过100',
        'The specified paramString is wrongly formed.'=>'短信模板中变量不是json格式',
        'The specified paramString and template is wrongly formed.'=>'短信模板中变量与模板内容不匹配',
        'Frequency limit reaches.'=>'触发业务流控',
        'null'=>'变量不能是url，可以将变量固化在模板中',
        'can not use old interface'=>"数据已迁移"
    ];

    public static function Config(array $parms)
    {
        self::$appCode=$parms['appCode'];
        self::$SignName=$parms['Sing'];
        self::$TemplateCode=$parms['templateId'];
        return new self();
    }

    public function send($tel,$params)
    {
        $header=array();
        array_push($header,"Authorization:APPCODE " . self::$appCode);
        self::$url=self::$url."?"."ParamString=".json_encode($params)."&RecNum=".$tel."&SignName=".self::$SignName."&TemplateCode=".self::$TemplateCode;
        $result=json_decode(self::curl($header),true);
        if($result['success']){
            self::$bool=true;
        }else{
            self::$errorCode=$result['message'];
        }
        return new self();
    }

    /**
     * @return mixed
     */
    public  function error()
    {
        return self::$error[self::$errorCode];
    }

    protected static function curl($headers)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($curl, CURLOPT_URL, self::$url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_FAILONERROR, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HEADER, false);
        if (1 == strpos("$".self::$url, "https://"))
        {
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        }
        $result=curl_exec($curl);
        curl_close($curl);
        return $result;
    }

}