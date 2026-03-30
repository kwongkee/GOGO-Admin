<?php

class RSA {
	
	private $privateKey='';//私钥（用于用户加密）
	private $publicKey='';//公钥（用于服务端数据解密）
	private $merchantCert;//
	
	public function __construct($pfxpath,$cerpath,$password) {
		
		/*$pfxpath = './cert/test/800075700050001_cert.pfx';
		$cerpath = './cert/test/8f8server.cer';
		$password = '1234qwer';*/
		
		$pkcs12 = file_get_contents($pfxpath);
        $private_key = array();
         
        openssl_pkcs12_read($pkcs12, $private_key, $password);
         
        $priKey = empty($private_key['pkey'])?'':$private_key['pkey'];
        $cert = empty($private_key['cert'])?'':$private_key['cert'];
         
        $cert = $this->getCerts($cert);
        $this->privateKey   = $priKey;
        $this->merchantCert = $cert;
	}
	
	/**
	 * 私钥加密
	 * @param 原始数据 $data
	 * @return 密文结果 string
	 */
	public function encryptByPrivateKey($data) {
		openssl_private_encrypt($data,$encrypted,$this->privateKey,OPENSSL_PKCS1_PADDING);//私钥加密
		$encrypted = base64_encode($encrypted);//加密后的内容通常含有特殊字符，需要编码转换下，在网络间通过url传输时要注意base64编码是否是url安全的
		return $encrypted;
	}
	
	/**
	 * 私钥解密
	 * @param 密文数据 $data
	 * @return 原文数据结果 string
	 */
	public function decryptByPrivateKey($data){
		$data = base64_decode($data);
		openssl_private_decrypt($data,$encrypted,$this->privateKey,OPENSSL_PKCS1_PADDING);//私钥解密
		return $encrypted;
	}
	
	/**
	 * 私钥签名
	 * @param unknown $data
	 */
	public function signByPrivateKey($data){
		openssl_sign($data, $signature, $this->privateKey);
		//$encrypted = base64_encode($signature);//加密后的内容通常含有特殊字符，需要编码转换下，在网络间通过url传输时要注意base64编码是否是url安全的
		$encrypted   = bin2hex($signature);
		return $encrypted;
	}
	
	public function getCert(){
		return $this->merchantCert;
	}
	
	/**
	  * @param $cert
	  * @return mixed
	  * 把标准化证书格式转成字符串，并转成大写 16进制
	  */
	 private function getCerts($cert){
	     $cert = str_replace([
	         '-----BEGIN CERTIFICATE-----',
	         '-----END CERTIFICATE-----',
	         "\n"
	     ],'', $cert);
	     $cert =base64_decode($cert);
	     $cert = strtoupper(bin2hex($cert));
	     return $cert;
	 }
	
	/**
	 * 公钥加密
	 * @param 原文数据 $data
	 * @return 加密结果 string
	 */
	public function encryptByPublicKey($data) {
		openssl_public_encrypt($data,$decrypted,$this->publicKey,OPENSSL_PKCS1_PADDING);//公钥加密
		return base64_encode($decrypted);
	}
	
	/**
	 * 公钥解密
	 * @param 密文数据 $data
	 * @return 原文结果 string
	 */
	public function decryptByPublicKey($data) {
		$data = base64_decode($data);
		openssl_public_decrypt($data,$decrypted,$this->publicKey,OPENSSL_PKCS1_PADDING);//公钥解密
		return $decrypted;
	}
	
	/**
	 * 公钥验签
	 * @param unknown $data
	 * @param unknown $sign
	 */
	public function verifyByPublicKey($data,$sign){
		$sign = base64_decode($sign);
		return openssl_verify($data, $sign, $this->publicKey);
	}
	
	public function __destruct(){
		//openssl_free_key($this->privateKey);
		//openssl_free_key($this->publicKey);
	}
	
}


?>