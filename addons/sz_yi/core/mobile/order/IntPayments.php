<?php
header('Content-Type:text/html;charset=utf-8');
define('IN_MOBILE', true);
define('PDO_DEBUG', true);

// 访问地址;  http://shop.gogo198.cn/addons/sz_yi/core/mobile/order/IntPayments.php

// http://shop.gogo198.cn/addons/sz_yi/payment/tgwechat/paytest.php

$inputs = file_get_contents("php://input");

$inputsd = json_decode($inputs,true);

if(empty($inputsd)){
    exit(json_encode(['code'=>0,'msg'=>'参数不能为空，请检查！']));
} else {

    // token = wxScode、Alipays
    $inData = ['payMoney', 'ordersn', 'body', 'returnUrl', 'openid', 'token'];
    foreach ($inputsd as $k => $v) {
        if (!in_array($k, $inData)) {
            exit(json_encode(['code' => 0, 'msg' => '您的参数有误，请检查！']));
        }
    }

    if (!isset($inputsd['token'])) {
        exit(json_encode(['code' => 0, 'msg' => '请填写token参数！']));
    }

    // 分流数据
    switch ($inputsd['token']) {
        case 'wxScode':
            $response = wxScode($inputsd);
            print_r($response);
            if ($response['status'] == '100') {
                exit(json_encode(['code' => 1, 'msg' => $response['message'], 'pay_url' => $response['pay_url']]));
            } else {
                exit(json_encode(['code' => 0, 'msg' => $response['message'], 'pay_url' => '']));
            }
            break;

        case 'Alipays':
            $response = Alipays($inputsd);
            print_r($response);
            die;

            if ($response['status'] == '100') {
                exit(json_encode(['code' => 1, 'msg' => $response['message'], 'pay_url' => $response['pay_url']]));
            } else {
                exit(json_encode(['code' => 0, 'msg' => $response['message'], 'pay_url' => '']));
            }
            break;
    }

}



// 微信支付
function wxScode($inputs)
{
    $account = '101540254006';
    $key     = 'f8ee27742a68418da52de4fca59b999e';

    $package = array();
    $package['account']    = $account;
    $package['payMoney']   = $inputs['payMoney'];
    $package['lowOrderId'] = $inputs['ordersn'];
    $package['body'] 	   = $inputs['body'];
    $package['notifyUrl']  = 'http://shop.gogo198.cn/foll/public/index.php?s=Payments/Notifys';//后台回调地址
    $package['returnUrl']  = $inputs['returnUrl'];
    $package['openId'] 	   = $inputs['openid'];
    //转换key=value&key=value;
    $str = tostring($package);
    //拼接加密字串
    $str .= '&key=' . $key;
    //MD5加密字串
    $sign = md5($str);
    //返回加密字串转换成大写字母
    $package['sign'] = strtoupper($sign);
    //数据包转换成json格式
    $data =  json_encode($package);
    //数据请求地址，post形式传输
    $url = 'https://ipay.833006.net/tgPosp/payApi/wxJspay';
    //数据请求地址，post形式传输
    $response = ihttp_posts($url,$data);
    // json数据
    $response = json_decode($response,true);
    return $response;
}


function Alipays($inputs)
{
    $account = '101540254006';
    $key     = 'f8ee27742a68418da52de4fca59b999e';

    $package = array();
    $package['account'] 	=   $account;
    $package['payMoney'] 	= 	number_format($inputs['payMoney'],2);
    $package['lowOrderId']  = 	$inputs['ordersn'];
    $package['body'] 		= 	$inputs['body'];
    //$package['notifyUrl']	=   'http://shop.gogo198.cn/payment/Notify/TgNotify.php';
    $package['notifyUrl']	=   'http://shop.gogo198.cn/foll/public/index.php?s=Payments/Notifys';
    //$package['notifyUrl'] 	= 	'http://shop.gogo198.cn/payment/wechat/tgwechatnotify.php';//后台回调地址
    $package['payType'] 	= 	'1';//
    $package['returnUrl']   =  'https://www.baidu.com';
    //转换key=value&key=value;
    $str = tostring($package);
    //拼接加密字串
    $str .= '&key=' . $key;
    //MD5加密字串
    $sign = md5($str);
    //返回加密字串转换成大写字母
    $package['sign'] = strtoupper($sign);
    //数据包转换成json格式
    $data =  json_encode($package);
    //数据请求地址，post形式传输
    $url = 'https://ipay.833006.net/tgPosp/services/payApi/unifiedorder';
    //数据请求地址，post形式传输
    $response = ihttp_posts($url,$data);
    //解析json数据
    $response = json_decode($response,TRUE);
    //返回json数据参数（sign,message,status,codeUrl,account,orderID,lowOrderId)
    return $response;

}


/**
 * 字符串拼接
 * @arrs :数组数据
 */
function tostring($arrs) {
    ksort($arrs, SORT_STRING);
    $str = '';
    foreach ($arrs as $key => $v ) {
        if ($v=='' || $v == null) {
            continue;
        }
        $str .= $key . '=' . $v . '&';
    }
    $str = trim($str,'&');
    return $str;
}

/**
 * @数据请求提交POST json
 * @$url:请求地址
 * @post_data:请求数据
 */
function ihttp_posts($url,$post_data) {
    //初始化
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,0); // 跳过证书检查
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,2);  // 从证书中检查SSL加密算法是否存在
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_POSTFIELDS,$post_data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Content-Length: ' . strlen($post_data)));
    $result = curl_exec($ch);
    curl_close($ch);
    return $result;
}



?>