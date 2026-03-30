<?php


if (!defined('IN_IA')) {
    exit('Access Denied');
}
global $_W;
global $_GPC;

if ($_W['isajax']){
    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => "http://declare.gogo198.cn/api/cloud_print?ordersn=".$_GPC['ordersn'],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_HTTPHEADER => array(
            "Content-Type: application/x-www-form-urlencoded",
        ),
    ));
    $response = curl_exec($curl);
    curl_close($curl);
    $response = json_decode($response,true);
    show_json(0,$response['message']);
}else{
    include $this->template('order/easy_deliver_print');
}
