<?php

/**
 * 生成二维码
 * 请求方式 GET
 * 请求参数
 * qrcode二维码内容
 * level级别
 * size 图片大小
 */
global $_GPC;

require_once WXZ_SHOPPINGMALL . '/lib/phpqrcode/phpqrcode.php';
$level = 'QR_ECLEVEL_' . strtoupper($_GPC['level']);
if (!$_GPC['qrcode']) {
    return;
}
QRcode::png(urldecode($_GPC['qrcode']), false, $level, $_GPC['size']);
?>


