<?php
/**
 * date: 2019-11-11
 * 服务费支付成功，回调；
 * author: 赵金如
 */
ini_set('display_errors', 'On');

require '../../../../framework/bootstrap.inc.php';
require '../../../../addons/sz_yi/defines.php';
require '../../../../addons/sz_yi/core/inc/functions.php';
require '../../../../addons/sz_yi/core/inc/plugin/plugin_model.php';

//载入日志函数
//获取文件流
$input = file_get_contents('php://input');
//写入日志
file_put_contents('./log/Servicenotify.log', $input."\r\n",FILE_APPEND);
//将接受到的Json数据转换成数组格式。
$data = json_decode($input, true);

if (!empty($data)) {

    // customs_directpostage_order
    $order = pdo_fetch('select id,status from ' . tablename('customs_directpostage_order') . ' where pay_ordersn=:pay_ordersn limit 1', array(':pay_ordersn' => $data['lowOrderId']));
    if (empty($order)) {
        /*$answer['finished'] = 'FAIL';
        echo json_encode($answer);
        die;*/
        exit('SUCCESS');
    }

    //$data['uniacid'] = $order['uniacid'];//订单所属公众号

    //$setting = uni_setting($order['uniacid'], array('payment'));

    /*$answer = array(
        'lowOrderId' => $data['lowOrderId'],//下游系统流水号，必须唯一
        'merchantId' => $data['merchantId'],//商户进件账号
        'upOrderId' => $data['upOrderId'],//上游流水号
    );*/

    if ($data['state'] == '0' && $data['orderDesc'] == '支付成功') {
        //是否接收到回调  SUCCESS表示成功
        //付款成功修改订单表中sz_yi_order数据  状态：status = 1
        if ($order['status'] == 0) {
            /*m('common')->paylog($data);
            m('common')->paylog('status');*/

            pdo_update('customs_directpostage_order', array(
                'status' => 1,
                'pay_ordersn1' => $data['upOrderId'],
                'pay_time'     => strtotime($data['payTime']),
            ), array('id' => $order['id']));
        }
        //$answer['finished'] = 'SUCCESS';
        $flag = 'SUCCESS';

    } else {
        //$answer['finished'] = 'FAIL';
        $flag = 'FAIL';
    }

    /*ksort($answer, SORT_STRING);
    $str = '';
    foreach ($answer as $key => $v) {
        if (empty($v)) {
            continue;
        }
        $str .= $key . '=' . $v . '&';
    }*/

    //$str .= 'key=' . $setting['payment']['tgpay']['key'];
    //数据加密
    //$answer['sign'] = strtoupper(md5($str));

    //将数据转换成json数据返回
    //echo json_encode($answer);
    exit($flag);
} else {
    exit('FAIL');
    /*$answer['finished'] = 'FAIL';
    echo json_encode($answer);*/

}

?>