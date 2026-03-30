<?php
use think\Des3;
use think\Validate;
use think\Db;
function validationPacket($data){
    $ResponData=null;
    $validate=new Validate(
        ['user'  =>'require', 'pwd'   =>'require', 'data'  =>'require'],
        ['user.require'=>'头部缺少user参数','pwd.require'=>'头部缺少pwd参数','data.require'=>'请求体内容空']
    );
    if(!$validate->check($data)){
        $ResponData=json_encode(['statusCode'=>1002,'msg'=>$validate->getError(),'data'=>'']);
        return ['error'=>false,'errorMsg'=>$ResponData];
    }
    if($data['user']!==config('api_user.user')){
        $ResponData = json_encode(['statusCode'=>1002,'msg'=>'用户不对','data'=>'']);
        return ['error'=>false,'errorMsg'=>$ResponData];
    }
    if($data['pwd']!==config('api_user.pwd')){
        $ResponData = json_encode(['statusCode'=>1002,'msg'=>'密码不对','data'=>'']);
        return ['error'=>false,'errorMsg'=>$ResponData];
    }
    $Des=new Des3(config('api_key'),date("Ymd",time()));
    $decryptData = $Des->decrypt($data['data']);
    $jsonData = charsetToGB(json_decode($decryptData,true),"UTF-8");
//    if(is_null($jsonData)){
//        $ResponData = json_encode(['statusCode'=>1002,'msg'=>'解密异常','data'=>'']);
//        return ['error'=>false,"errorMsg"=>$ResponData];
//    }
    return ['error'=>true,'errorMsg'=>$jsonData];
}

function charsetToGB($mixed,$Unicode)
{
    if (is_array($mixed)) {
        foreach ($mixed as $k => $v) {
            if (is_array($v)) {
                $mixed[$k] = charsetToGB($v,$Unicode);
            } else {
                $encode = mb_detect_encoding($v, array('ASCII', 'UTF-8', 'GB2312', 'GBK', 'BIG5'));
                if ($encode == 'UTF-8') {
                    $mixed[$k] = iconv('UTF-8', $Unicode, $v);
                }
            }
        }
    } else {
        $encode = mb_detect_encoding($mixed, array('ASCII', 'UTF-8', 'GB2312', 'GBK', 'BIG5'));
        if ($encode == 'UTF-8') {
            $mixed = iconv('UTF-8', $Unicode, $mixed);
        }
    }
    return $mixed;
}

function sendWechatMsgTemplate($userRes,$addrRes,$stime,$body='您好，您已成功停入车位'){
    $ASSESS_TOKEN =RequestAccessToken( $userRes['uniacid']);
    $hosts="https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=".$ASSESS_TOKEN;
    $template=[
        'touser'=>$userRes['openid'],
        'template_id'=>'OAPfnX36eT6AbwSNf-y6XdNXFOAt6B6ohnFH7vHzRVc',
        'url'=>'http://shop.gogo198.cn/app/index.php?i='.$userRes['uniacid'].'&c=entry&m=ewei_shopv2&do=mobile&r=parking.parking_orderdetails',
        'data'=>array(
            'first'=>array(
                'value'=>$body,
                'color'=>''),
            'keyword1'=>array(
                'value'=>$addrRes['Road'] . $addrRes['Road_num'].'号',
                'color'=>''),
            'keyword2'=>array(
                'value'=>$addrRes['park_code'],
                'color'=>''),
            'keyword3'=>array(
                'value' => $stime,//停入时间
                'color' => ''
            ),
            'remark'=>array(
                'value'=>"点击详情，查看订单信息",
                'color'=>''))
    ];//消息模板
   return httpRequest($hosts,json_encode($template));
}


/*
 * 获取wxtoken
 */
// function RequestAccessToken ( $uniacid)
//{
//    $uniacid = $uniacid?$uniacid:14;
//    $key = 'accesstoken:'.$uniacid;
//    $coreCache = Db::name('core_cache')->where('key',$key)->find();
//    if(!empty($coreCache)){
//        $coreCache = unserialize($coreCache['value']);
//        if($coreCache['expire']>time()){
//            return $coreCache['token'];
//        }
//    }
//    $account = Db::table("ims_account_wechats")->where('uniacid', $uniacid)->find();
//    $ASSESS_TOKEN = file_get_contents('https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=' . $account['key'] . '&secret=' . $account['secret']);
//    $ASSESS_TOKEN = json_decode($ASSESS_TOKEN, true);
//    $value = serialize([
//        'token'=>$ASSESS_TOKEN['access_token'],
//        'expire'=>(time()+$ASSESS_TOKEN['expires_in'])-200
//    ]);
//    if(empty($coreCache)){
//        Db::name('core_cache')->insert(['key'=>$key,'value'=>$value]);
//    }else{
//        Db::name('core_cache')->where('key',$key)->update(['value'=>$value]);
//    }
//    return $ASSESS_TOKEN['access_token'];
//
//}


/**
 * @param Response $response
 * @return mixed
 */
function ResponseResult ( $response ,$data,$jbool=false)
{
    if($jbool){
        $data = json_encode($data);
    }
    $response->header("Content-Type", "application/json;charset=utf-8")
        ->header("Content-Length", strlen($data))
        ->data($data)->send();
    exit();
}
