<?php
// Ä£¿éLTDÌá¹©
$alipay_config['sign_type'] = strtoupper('MD5');
$alipay_config['input_charset'] = strtolower('utf-8');
$alipay_config['cacert'] = $_SERVER['SERVER_NAME'] . '/addons/sz_yi/cert/cacert.pem';
$alipay_config['transport'] = 'http';
echo "\n\n";

?>
