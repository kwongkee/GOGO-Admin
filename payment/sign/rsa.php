<?php

//创建公钥和私钥
$res = openssl_pkey_new(['private_key_bits'=>512]);

//提取私钥
openssl_pkey_export($res,$private_key);

//生成公钥
$public_key = openssl_pkey_get_details($res);

$public_key = $public_key['key'];

//显示数据
echo '<pre>';
var_dump($private_key);
echo '<br>';
var_dump($public_key);


//要加密的数据
$data = "web site www.php.cn";
echo '加密的数据：'.$data.'<br>';

//私钥加密后的数据
openssl_private_encrypt($data,$encrypted,$private_key);

//加密后的内容通常含有特殊字符，需要base64编码转换下
$encrypted = base64_encode($encrypted);
echo '私钥加密后的数据: '.$encrypted.'<br>';

//公钥解密
openssl_public_decrypt(base64_decode($encrypted),$decrypted,$public_key);
echo '公钥解密后的数据: '.$decrypted.'<br>';

//----相反操作。公钥加密
openssl_public_encrypt($data,$encrypted,$public_key);
$encrypted = base64_encode($encrypted);
echo '公钥加密后的数据：'.$encrypted.'<br>';

//公钥解密
openssl_private_decrypt(base64_decode($encrypted),$decrypted,$private_key);
echo '私钥解密后的数据：'.$decrypted.'<br>';
?>