<?php
error_reporting(0);
define('IN_MOBILE', true);
require dirname(__FILE__) . '/../../../../framework/bootstrap.inc.php';
require dirname(__FILE__) . '/../../../../framework/function/diysend.func.php';
require IA_ROOT . '/addons/ewei_shopv2/defines.php';
require IA_ROOT . '/addons/ewei_shopv2/core/inc/functions.php';
require IA_ROOT . '/addons/ewei_shopv2/core/inc/plugin_model.php';
require IA_ROOT . '/addons/ewei_shopv2/core/inc/com_model.php';
global $_W;
global $_GPC;
// load()->app('common');
// load()->app('template');
// load()->func('diysend');
date_default_timezone_set('Asia/Shanghai');
$input = file_get_contents('php://input');
//接收后台回调参数，并转换json格式
$receive = json_decode($input,TRUE);
file_put_contents('./log/cardwecahtpaylog.txt', $receive,FILE_APPEND);
if (!empty($receive)) {

    //组装数据返回给支付平台
    $answer = array(
        'lowOrderId'=>$receive['lowOrderId'],//下游系统流水号，必须唯一
        'merchantId'=>$receive['merchantId'],//商户进件账号
        'upOrderId'=>$receive['upOrderId'],//上游流水号
    );
    $user = pdo_get('parking_monthcard', array('orderid' => $receive['lowOrderId']));
    //如果返回的数据，支付状态为：0 并且：orderDesc 订单描述= 支付成功
    if ($receive['state'] == 0 && $receive['orderDesc'] == '支付成功') {//支付成功
        //是否接收到回调  SUCCESS表示成功
        try{
            pdo_begin();//开启事务

            //支付成功修改pay_old表中 status 状态为1 ，更新时间为当前时间,上游订单号：更新到表中，根据订单编号修改；
            pdo_update('pay_old', array('status' => 1,'update_time'=>time(),'upOrderId'=>$receive['upOrderId']), array('ordersn' => $receive['lowOrderId']));
            //支付成功修改status 状态为1  pay_order订单表
            // pdo_update('pay_order', array('status' => 1,'upOrderId'=>$receive['upOrderId']), array('ordersn' => $receive['lowOrderId']));
            //支付成功修改parking_order 表中状态：支付成功
            pdo_update('parking_monthcard', array('pay_status' => 1,'rechargedate'=> time()), array('orderid' => $receive['lowOrderId']));

            pdo_commit();//提交事务
        }catch(PDOException $e){
            pdo_rollback();//执行失败，事务回滚
        }
        $answer['finished'] = 'SUCCESS';
    } else if ($receive['state'] == 1) {//支付失败
        $answer['finished'] = 'FAIL';
//		sendMessagess($sendArr);
    }

    //拼接字串
    $str = tostring($answer);

    //查找当前配置
    $config = pdo_get('pay_config', array('uniacid' =>$user['uniacid']), array('config'));
    //反序列化
    $key = unserialize($config['config']);

    $k = $key['tg']['key'];
    //字符串拼接加密
    $str .= '&key='.$k;
    $answer['sign'] = strtoupper(md5($str));
    file_put_contents('./log/wecahtjsons.txt', print_r($answer,TRUE),FILE_APPEND);
    //将数据转换成json数据返回
    echo json_encode($answer);

} else {
    $answer['finished'] = 'FAIL';
    echo json_encode($answer);
}

/**
 * 字符串拼接
 */
function tostring($arrs) {
    ksort($arrs, SORT_STRING);
    $str = '';
    foreach ($arrs as $key => $v ) {
        if (empty($v)) {
            continue;
        }
        $str .= $key . '=' . $v . '&';
    }
    $str = trim($str,'&');
    return $str;
}
?>