<?php
if (!defined('IN_IA'))
{
exit('Access Denied');
}

class Verified_EweiShopV2Page extends mobilePage
{
    public function __construct () {
        parent::__construct();
        load()->func("common");
        isUserReg();
    }

    public function main()
    {
        include $this->template("parking/verified");
    }
    public  function verifImage()
    {
        $boolean=$this->SendImage();
        if(!$boolean['error']){
            show_json(1,$boolean['data']);
        }else{
            show_json(0);
        }
    }
    public  function SendImage()
    {
        global $_W;
        global $_GPC;
        $file=$_GPC['images'];
        if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $file, $result)){
            $fileConten=str_replace($result[1],'',$file);
        }
        switch ($_GPC['n'])
        {
            case 1:
                $host = "http://dm-51.data.aliyun.com/rest/160601/ocr/ocr_idcard.json";
                $headers = array();
                array_push($headers, "Authorization:APPCODE 504fd5f6a735437c97cd117e61cb4a24");
                array_push($headers, "Content-Type".":"."application/json; charset=UTF-8");
                $body = "{\"inputs\":[{\"image\":{\"dataType\":50,\"dataValue\":\"{$fileConten}\"},\"configure\":{\"dataType\":50,\"dataValue\":\"{\\\"side\\\":\\\"face\\\"}\"}}]}";
                $data=$this->ImageReques("POST",$host,$headers,$body);
                @file_put_contents('../data/logs/yanz.txt',$data.'----'.date('Y-m-d H:i:s',time())."\n",FILE_APPEND);
                $data=json_decode(json_decode($data,true)['outputs'][0]['outputValue']['dataValue'],true);
                if((empty($data))||(!$data['success'])){
                    return ['error'=>true,'data'=>''];
                }
                $_SESSION['idcard']=$data;
                @file_put_contents('../attachment/images/verifed/'.$_W['openid'].'_idcard.jpg',base64_decode($fileConten));
                return ['error'=>false,'data'=>''];
                break;
            case 2:
                 $host="http://dm-52.data.aliyun.com/rest/160601/ocr/ocr_driver_license.json";
                 $headers=array();
                 array_push($headers, "Authorization:APPCODE 504fd5f6a735437c97cd117e61cb4a24");
                 array_push($headers, "Content-Type".":"."application/json; charset=UTF-8");
                 $body = "{\"inputs\":[{\"image\":{\"dataType\":50,\"dataValue\":\"{$fileConten}\"},\"configure\":{\"dataType\":50,\"dataValue\":\"{\\\"side\\\":\\\"face\\\"}\"}}]}";
                 $data=$this->ImageReques("POST",$host,$headers,$body);
                 $data=json_decode(json_decode($data,true)['outputs'][0]['outputValue']['dataValue'],true);
                 if((empty($data))||(!$data['success'])){return ['error'=>true,'data'=>''];}
                 $_SESSION['driver']=$data;
                @file_put_contents('../data/logs/yanz.txt',json_encode($data).'----'.date('Y-m-d H:i:s',time())."\n",FILE_APPEND);
                @file_put_contents('../attachment/images/verifed/'.$_W['openid'].'_license.jpg',base64_decode($fileConten));
                return ['error'=>false,'data'=>''];
                break;
            case 3:
                $host = "http://dm-53.data.aliyun.com/rest/160601/ocr/ocr_vehicle.json";
                $headers=array();
                array_push($headers, "Authorization:APPCODE 504fd5f6a735437c97cd117e61cb4a24");
                array_push($headers, "Content-Type".":"."application/json; charset=UTF-8");
                $body = "{\"inputs\":[{\"image\":{\"dataType\":50,\"dataValue\":\"{$fileConten}\"}}]}";
                $data=$this->ImageReques("POST",$host,$headers,$body);
                $data=json_decode(json_decode($data,true)['outputs'][0]['outputValue']['dataValue'],true);
                if(!$data['success']){return ['error'=>true,'data'=>''];}
                 $_SESSION['vehicle']=$data;
                @file_put_contents('../data/logs/yanz.txt',json_encode($data).'----'.date('Y-m-d H:i:s',time())."\n",FILE_APPEND);
                @file_put_contents('../attachment/images/verifed/'.$_W['openid'].'_vehicle.jpg',base64_decode($fileConten));
                return ['error'=>false,'data'=>$data['plate_num']];
                break;
            default:
                return ['error'=>true,'data'=>''];
                break;
        }
    }
    public function ImageReques($method,$url,$headers,$bodys){
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_FAILONERROR, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HEADER, false);
        if (1 == strpos("$".$url, "https://"))
        {
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        }
        curl_setopt($curl, CURLOPT_POSTFIELDS, $bodys);
        $result=curl_exec($curl);
        curl_close($curl);
        return $result;
    }
    public  function save()
    {
        global $_W;
        global $_GPC;
        $idcard=$_SESSION['idcard'];
        $driver=$_SESSION['driver'];
        unset($_SESSION['idcard'],$_SESSION['driver']);
        if(empty($idcard)&&empty($driver)){
            show_json('0','识别错误重新上传');
        }
        if($idcard['name']!=$driver['name']){
            show_json('0','两者姓名不相同');
        }
        if (empty($idcard['address'])){
            show_json('0','身份证地址识别错误');
        }
        $sex=['女'=>0,'男'=>1];
        $userInfo=[
            'uid'=>$_W['uid'],
            'uniacid'=>$_W['uniacid'],
            'openid'=>$_W['openid'],
            'idcard'=>$idcard['num'],
            'driverlicense'=>$driver['num'],
            'uname'=>$idcard['name'],
            'sex'=>$sex[$idcard['sex']],
            'addr'=>$idcard['address'],
            'time'=>time()
        ];
        $isAlreadyKnown = pdo_get('parking_verified',array('openid'=>$_W['openid']));
        $IdcardIsEmpty = pdo_get('parking_verified',['idcard'=>$idcard['num']]);
        if(!empty($IdcardIsEmpty)){
            show_json('0','该身份证已存在');
        }
        if(empty($isAlreadyKnown)){
            $result = pdo_insert('parking_verified', $userInfo);
        }else{
            $result = pdo_update('parking_verified',$userInfo,['openid'=>$_W['openid']]);
        }
        if(!empty($result)){
            show_json('1');
        }else{
            show_json('0','保存失败');
        }
    }

    public function verSave()
    {
        global $_W;
        global $_GPC;
        $nums=$_SESSION['vehicle'];
        if(empty($nums)){
            show_json(-1,'检测失败');
        }
        $isCardNo = pdo_get("parking_verified",['license'=>$nums['plate_num']]);
        if(!empty($isCardNo)){
            show_json(-1,'已绑定过');
        }
        $result = pdo_update('parking_verified', array("license"=>$nums['plate_num']), array('openid' => $_W['openid']));
        if (!empty($result)) {
            $this->getIvoOrderByCarNo($_W['openid'],$nums['plate_num']);
            unset($_SESSION['vehicle'], $nums);
            show_json(0, '完成');
        }
    }

    protected function getIvoOrderByCarNo($openId, $carNo)
    {
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => "http://shop.gogo198.cn/foll/public/?s=api/wechat/sendVioOrederTempl&user_id=" . $openId . "&carNo=" . $carNo,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                "Cache-Control: no-cache",
            ),
        ));
        $response = curl_exec($curl);
//        $err = curl_error($curl);
        curl_close($curl);
        unset($response);
    }
}
