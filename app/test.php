<?php

@session_start();

$info = '{"openid":"oM5pps8rc-lajUoqv3YCriIqBRV8","nickname":"@Jason@Cindy","sex":1,"language":"zh_CN","city":"Guangzhou","province":"Guangdong","country":"CN","headimgurl":"http:\/\/thirdwx.qlogo.cn\/mmopen\/vi_32\/OLLPKUsPKibOYZJXX0keGYWr4EhO20aMUqqzicnKgmezaqhPOFaUUuxnyM133YY9B6Ijx5RR4uiawnDTpXzYkIOibw\/132","privilege":[],"unionid":"ooWwF0jUPRYtRZ1E3PGKew7z0cHo"}';

$postData = [];
$postData['info'] = $info = base64_encode($info);

$url = 'https://shop.gogo198.cn/app/index.php?i=3&c=entry&p=login&do=member&m=sz_yi&type=wxlogin';

$fs = postWxlogin($url,$postData);

// 解析json
$jsonData = json_decode($fs,true);

// 设置COOKIE
$cookieid = $jsonData['result']['cookiedid'][0];
$openid   = $jsonData['result']['cookiedid'][1];
$lifeTime = 24 * 3600 * 3;
session_set_cookie_params($lifeTime);

// 设置浏览器cookie
setcookie($cookieid, $openid);
setcookie('member_mobile', $jsonData['result']['member_mobile']);
setcookie('member_id', $jsonData['result']['member_id']);

// 跳转登陆成功；
$loginOk = $jsonData['result']['indexUrl'];
// 登陆成功，跳转
header("Location:".$loginOk);
exit();

/*$res = [$_COOKIE,$_SESSION];
echo '<pre>';
print_r($res);
die;*/

function postWxlogin($url,$post_data=[]) {
    //初始化
    $curl = curl_init();
    //设置捉取Url
    curl_setopt($curl,CURLOPT_URL,$url);
    //设置头文件的信息
    curl_setopt($curl,CURLOPT_HEADER,0);
    //设置获取的信息以文件流的形式返回，而不是直接输出
    curl_setopt($curl,CURLOPT_RETURNTRANSFER,1);
    //设置超时
    curl_setopt($curl,CURLOPT_TIMEOUT,65);
    //设置post方式提交
    curl_setopt($curl,CURLOPT_POST,1);
    //设置post数据
    //设置请求参数
    curl_setopt($curl,CURLOPT_POSTFIELDS,$post_data);
    //执行命令  并返回结果
    $res = curl_exec($curl);
    //关闭连接
    curl_close($curl);
    //返回数据
    return $res;
}
?>