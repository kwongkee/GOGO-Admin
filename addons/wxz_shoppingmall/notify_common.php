<?php

/**
 * 模块自定义支付（需要附加参数attach自带模块名称|活动id）
 * 支付控制器pay_result
 * @author lirui <vipliruilove@gmail.com>
 */
error_reporting(0);
define('IN_MOBILE', true);
$input = file_get_contents('php://input');

if (!empty($input) && empty($_GET['out_trade_no'])) {
    $obj = simplexml_load_string($input, 'SimpleXMLElement', LIBXML_NOCDATA);
    $data = json_decode(json_encode($obj), true);
    if (empty($data)) {
        exit('fail');
    }
    $get = $data;
} else {
    $get = $_GET;
}

require '../../framework/bootstrap.inc.php';
$attach = explode('|', $get['attach']);
$_W['m'] = $attach[0];
$_W['uniacid'] = $attach[1];

$site = WeUtility::createModuleSite($_W['m']);
$site->doMobilepay_result($ret);
