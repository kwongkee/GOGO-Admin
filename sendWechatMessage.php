<?php
class sendWechatMessage
{
    private static $redis;
    private static $appid='wx01af4897eca4527e';
    private static $appscroll='497ccae978cb1bca589d587d60da8f8d';
    public static function Redisconnect()
    {
        if(!isset(self::$redis)){
            self::$redis=new Redis();
            self::$redis->connect('127.0.0.1',6379);
        }
        return self::$redis;
    }
    public static function run()
    {
        $data=self::Redisconnect()->hGetAll('userData');
        if(empty($data)){
            exit();
        }
        $hosts="http://shop.gogo198.cn/foll/public/?s=api/wechat/template";
        $time = time();
        foreach ($data as $key=>$val){
            $vals=json_decode($val,true);
            $vals['etime'] = $vals['etime']-900;
            if($time>=$vals['etime']){
                $template=[
                    'touser'=>$vals['openid'],
                    'template_id'=>'RjLdZBUAL3WJiinkCih4bWsBYc1-nrko6UbkGYwnQa0',
                    'url'=>'http://shop.gogo198.cn/app/index.php?i=14&c=entry&m=ewei_shopv2&do=mobile&r=parking.parking_orderdetails',
                    'data'=>array(
                        'first'=>array(
                            'value'=>"您的停车时间即将到期",
                            'color'=>'#173177'),
                        'keyword1'=>array(
                            'value'=>"停车收费",
                            'color'=>'#436EEE'),
                        'keyword2'=>array(
                            'value'=>date('Y-m-d H:i:s',$vals['etime']),
                            'color'=>'#173177'),
                        'remark'=>array(
                            'value'=>"谢谢使用无感停车!",
                            'color'=>'#808080'))
                ];//消息模板
                $t = ['template'=>serialize($template),'uniacid'=>14];
                $res=json_decode(self::httpRequest($hosts,json_encode($t)),true);
                if($res['message']['errcode']==0){
                    $boole=self::$redis->hDel("userData",$key);
                    unset($data[$key]);
                }
            }

        }
    }

    public  function httpRequest($url,$data)
    {
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => $data,
            CURLOPT_HTTPHEADER => array(
                "Cache-Control: no-cache",
                "Content-Type: application/json",
            ),
        ));
        $response = curl_exec($curl);
        curl_close($curl);
        return $response;
    }
}
sendWechatMessage::run();
