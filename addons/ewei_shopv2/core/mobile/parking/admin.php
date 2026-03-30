<?php
if (!defined('IN_IA'))
{
    exit('Access Denied');
}
class Admin_EweiShopV2Page extends mobilePage
{
    public function main()
    {
        include $this->template("parking/admin");
    }
    public function imagePost()
    {
        global $_W;
        global $_GPC;
        $size=floor($_FILES['file']['size']/1024);
        if($_FILES['file']['error']>0){
            show_json('error',"上传错误");
            exit();
        }
        if($size>1024){
            show_json('error',"上传图片过大");
            exit();
        }
        $result=$this->sendImage($_FILES['file']['tmp_name']);
        print_r($result);
    }
    public function sendImage($file)
    {
        $url="http://api03.aliyun.venuscn.com/ocr/car-license";
        $header=array();
        array_push($header,"Authorization:APPCODE 504fd5f6a735437c97cd117e61cb4a24");
        array_push($header, "Content-Type".":"."application/x-www-form-urlencoded; charset=UTF-8");
        $body="pic=".base64_encode(file_get_contents($file));
        return $this->request("post",$header,$body,$url);
    }

    public function request($method,$headers,$bodys,$url)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_FAILONERROR, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HEADER, true);
        if (1 == strpos("$".$host, "https://"))
        {
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        }
        curl_setopt($curl, CURLOPT_POSTFIELDS, $bodys);
        $res= curl_exec($curl);
        curl_close($curl);
        return $res;
    }
}