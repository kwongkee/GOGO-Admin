<?php


//发送预提通知
global $_GPC;
$data = array(
    'first' => array(
        'value' => "你好,预提提交成功！",
        'color' => '#ff510'
    ),
    'keyword1' => array(
        'value' => $_GPC['name'],
        'color' => '#ff510'
    ),
    'keyword2' => array(
        'value' => $_GPC['time'],
        'color' => '#ff510'
    ),
    'remark' => array(
        'value' => "点击详情进入查看" ,
        'color' => '#ff510'
    ),
);
$account_api = WeAccount::create();
$result = $account_api->sendTplNotice(
    $_GPC['openid'],
    '8vqI7z2VqXks8H9uTcl8tkR2v9wYi-tBQZjOrrQOuwI',
    $data,
    $_GPC['url']
);
exit(json_encode($result));