<?php
//namespace app\modules\api\common\bangbf;
	/*
	 * 代付款加解密
	 */
class ipsCrypt {
    
     public $private_key;//私钥
     public $merchantCert;//商户证书
     public $public_key;//公钥
     public $priKey;
    
    /**
     * 
     * @Param  $private_key_path 商户证书路径（p12）
	 * @Param  $public_key 公钥16进制格式
     * @Param  $private_key_password 证书密码
     */
    function __construct($private_key_path,$public_key_path,$private_key_password) {
        
        // 初始化商户私钥
        $pkcs12 = file_get_contents($private_key_path);
        $private_key = array();
         
        openssl_pkcs12_read($pkcs12, $private_key, $private_key_password);
         
        $priKey = empty($private_key['pkey'])?'':$private_key['pkey'];
        $cert = empty($private_key['cert'])?'':$private_key['cert'];
         
        $cert = $this->getCert($cert);
        $this->priKey = $priKey;
         
        $this->merchantCert = $cert;
        
        //邦付宝服务器公钥证书  .cer、跟证书rootca.cer
        $public_key = file_get_contents($public_key_path);
        $public_key = $this->getPublicKey($public_key);
        
        //var_dump($public_key);
        //$public_key = strtoupper(bin2hex($public_key));
        //var_dump($public_key);
		//公钥
        $this->public_key = $public_key;
        //  var_dump($this->public_key);
		 
    }

     /**
      * @param $cert
      * @return mixed
      * 把标准化证书格式转成字符串，并转成大写 16进制
      */
     private function getCert($cert){
         $cert = str_replace([
             '-----BEGIN CERTIFICATE-----',
             '-----END CERTIFICATE-----',
             "\n"
         ],'', $cert);
         $cert =base64_decode($cert);
         $cert = strtoupper(bin2hex($cert));
         return $cert;
     }
     
     private function getPublicKey($public_key){

         //$public_key = base64_encode($public_key);
         $public_key='-----BEGIN CERTIFICATE-----'.PHP_EOL
         .chunk_split(($public_key), 64, PHP_EOL)
         .'-----END CERTIFICATE-----'.PHP_EOL;
         //var_dump($public_key);die;
         return openssl_get_publickey($public_key);
     }
     
     
     
    /**
	 * AES 加密
	 * @param $input 加密字串
	 * @param $key   密码；
	 */
	public function encrypt_pass($input,$key)
	{
		$size = mcrypt_get_block_size(MCRYPT_RIJNDAEL_128,MCRYPT_MODE_ECB);
		$input = $this->pkcs5_pad($input,$size);//计算补码数量；
		
		$td = mcrypt_module_open(MCRYPT_RIJNDAEL_128,'',MCRYPT_MODE_ECB,'');
		mcrypt_generic_init($td,$key,'12345678abcdefgh');
		$data = mcrypt_generic($td,$input);
		
		mcrypt_generic_deinit($td);
		mcrypt_module_close($td);
		
		$data = bin2hex($data);//2进制转16进制
		return $data;		
	}
	
	
	/**
	 * AES 加密
	 * @param $input 加密字串
	 * @param $key   密码；
	 */
	public function encrypt_passd($input,$key)
	{
		$td  	 = mcrypt_module_open(MCRYPT_RIJNDAEL_128,'',MCRYPT_MODE_ECB,'');
		$key 	 = substr($key,0,mcrypt_enc_get_key_size($td));
		$iv_size = mcrypt_enc_get_iv_size($td);
		$iv      = mcrypt_create_iv($iv_size,MCRYPT_RAND);
		$data    = '';
		//初始化加密句柄
		if(mcrypt_generic_init($td,$key,$iv) != -1){
			
			/*加密数据*/
			$data = mcrypt_generic($td,$input);
			/* 执行清理工作 */
			mcrypt_generic_deinit($td);
			mcrypt_module_close($td);
		}
		$data = bin2hex($data);//2进制转16进制
		return $data;
	}
    
    
    /**
	 * 加密填充
	 */
	public function pkcs5_pad($text,$blocksize)
	{
		$pad = $blocksize - (strlen($text) % $blocksize);
		return $text . str_repeat(chr($pad),$pad);
	}
	
     
     /**RSA签名
      * 签名结果需转成16进制
      * return Sign签名
      */

	 public function Rsasign($data) {
	
	     $oRsa = new \app\common\Rsa;
	     $sign =  $oRsa -> sign($data,$this->priKey,'hex');
	     $sign = strtoupper($sign);
	     return $sign;
	 }

     /**验签
      * @param $data
      * @param $sign //签名，需转化成二进制
      * @return bool
      */
     public function verify($data,$sign) {

         $sign = $this -> _hex2bin($sign);
         $ret = false;
             switch (openssl_verify($data, $sign, $this->public_key,OPENSSL_ALGO_SHA1)) {
                 case 1 :
                     $ret = true;
                     break;
                 case 0 :
                 case -1 :
                 default :
                     $ret = false;
             }
         return $ret;

     }
     private function _hex2bin($hex = false) {
         $ret = $hex !== false && preg_match('/^[0-9a-fA-F]+$/i', $hex) ? pack("H*", $hex) : false;
         return $ret;
     }
 }
?>