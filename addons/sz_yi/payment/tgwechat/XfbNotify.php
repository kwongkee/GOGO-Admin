<?php

/**
 * 接收支付回调
 */
$inp = file_get_contents("php://input");


$path = './XfbNotify.log';

file_put_contents($path,json_encode($inp),FILE_APPEND);


?>