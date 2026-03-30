<?php

require dirname(__FILE__) . '/../../../../framework/bootstrap.inc.php';
require IA_ROOT . '/addons/ewei_shopv2/defines.php';
require IA_ROOT . '/addons/ewei_shopv2/core/inc/functions.php';
require IA_ROOT . '/addons/ewei_shopv2/core/inc/plugin_model.php';
require IA_ROOT . '/addons/ewei_shopv2/core/inc/com_model.php';


/**
 *  模拟提交微信自助报关
 */

$openid = 'ov3-btyLPTGwIduBvEXdiGSnpUK4';

$res = getUserinfo($openid,'SH20200408155329904264','4200000562202004082436294290');

echo '<pre>';
print_r($res);


function getUserinfo($openid,$trade_no,$trade_id)
{
    // 获取sz_yi_member
    $sql = 'SELECT realname,id_card FROM ' . tablename('sz_yi_member') . ' WHERE `openid`=:openid limit 1';
    $params = array();
    $params[':openid'] = $openid;
    $userInfo = pdo_fetch($sql, $params);

    $senArr = [];
    $senArr['token']    = 'create';
    $senArr['trade_no'] = $trade_no;
    $senArr['trade_id'] = $trade_id;
    $senArr['name']     = $userInfo['realname'];
    $senArr['cert_id']  = $userInfo['id_card'];

    return SelfCustoms($senArr);
    //return $senArr;

    // 发送数据
    //$this->SelfCustoms($senArr);
}


function SelfCustoms($senArr)
{
    $url = 'http://declare.gogo198.cn/api/customs';
    // 将数据进行发送
    //初始化
    $curl = curl_init();
    //设置捉取Url
    curl_setopt($curl,CURLOPT_URL,$url);
    //设置头文件的信息
    curl_setopt($curl,CURLOPT_HEADER,0);
    //设置获取的信息以文件流的形式返回，而不是直接输出
    curl_setopt($curl,CURLOPT_RETURNTRANSFER,1);
    //设置超时
    curl_setopt($curl,CURLOPT_TIMEOUT,35);
    //设置post方式提交
    curl_setopt($curl,CURLOPT_POST,1);
    //设置post数据
    //设置请求参数
    curl_setopt($curl,CURLOPT_POSTFIELDS,$senArr);
    //执行命令  并返回结果
    $res = curl_exec($curl);
    //关闭连接
    curl_close($curl);
    //
    return $res;
}

?>