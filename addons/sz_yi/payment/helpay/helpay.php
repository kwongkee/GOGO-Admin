<?php
	header( 'Content-Type:text/html;charset=utf-8 ');
	// 模块LTD提供
	error_reporting(0);
	define('IN_MOBILE', true);
	require_once '../../../../framework/bootstrap.inc.php';
	require_once '../../../../addons/sz_yi/defines.php';
	require_once '../../../../addons/sz_yi/core/inc/functions.php';
	require_once '../../../../addons/sz_yi/core/inc/plugin/plugin_model.php';
	//引入文件
	//require_once './cls/ipsCrypt.php';
	require_once './cls/Rsa.php';
	
	load()->app('common');
	load()->app('template');
	load()->func('diysend');
	$curl = new Curl();
	global $_W;
	global $_GPC;
	
	
	$helpay = Helpay::getInstance();
	//$helpay->pfxTopem();
	$resArr = $helpay->payment();
	/*echo '<pre>';
	print_r($resArr);*/
	if(!empty($resArr)) {
		echo json_encode(['code'=>1,'msg'=>$resArr]);
		die;
	} else {
		echo json_encode(['code'=>0,'msg'=>'验签错误']);
	}
	
	//$helpay = Helpay::getInstance();
	

//邦付宝支付类
class Helpay {
	
	static private $instance;
	private $config;
	private $ips;
		
	private function __construct() {
		//测试账号
		$pfxpath = './cert/test/800075700050001_cert.pfx';
		$cerpath = './cert/test/8f8server.cer';
		$password = '1234qwer';
		//获取证书
		$this->ips = new RSA($pfxpath,$cerpath,$password);
		
	}
	//防止克隆
	private function __clone(){}
	//单例模式
	static public function getInstance(){
		if(!self::$instance instanceof self){
			self::$instance = new self();
		}
		return self::$instance;
	}
		
	public function getInfo($info = ''){
		echo $info;
	}
		
	/**
	 * 支付请求下单
	 */
	public function payment($payArr=[]) {
		
		$payArr['charset'] 			= '02';//字符集
		$payArr['version'] 			= '1.0';//接口版本
		$payArr['signType'] 		= 'RSA';//签名方式
		$payArr['service'] 			= 'DirectPayment';//DirectPayment(收银台)、GWDirectPay(无界面收银台)、APPPayment(手机支付)、QRPayment(二维码支付)
		$payArr['pageReturnUrl'] 	= 'http://shop.gogo198.cn/addons/sz_yi/payment/helpay/Notice.php';//页面通知url
		$payArr['offlineNotifyUrl'] = 'http://shop.gogo198.cn/addons/sz_yi/payment/helpay/Notify.php';//后台通知url
		//$payArr['clientIP'] 		= '02';//客户端ip
		$payArr['requestId'] 		= 'GGO99198'.mt_rand(11111,time()).'88'.mt_rand(10000,99999);//请求号
		//$payArr['purchaserIdv'] 	= '02';//购买者标识
		$payArr['merchantId'] 		= '800075700050001';//合作商户编号
		//$payArr['merchantName'] 	= '02';//合作商户展示名称
		$payArr['orderId'] 			= 'GGH99198'.mt_rand(11111,time()).'99'.mt_rand(10000,99999);//订单号
		$payArr['orderTime'] 		=  date('Y-m-d H:i:s');//订单时间
		//$payArr['bankAbbr'] 		= '02';//银行编码
		$payArr['cardType'] 		= '1';//卡类型
		$payArr['totalAmount'] 		= '1000';//订单总金额,以分为单位
		$payArr['currency'] 		= 'CNY';//交易币种
		$payArr['validUnit'] 		= '00';//订单有效期单位
		$payArr['validNum'] 		= '30';//订单有效期数量
		$payArr['showUrl'] 			= 'http://shop.gogo198.cn/app/index.php?i=3&c=entry&p=detail&id=46&do=shop&m=sz_yi';//商品展示url
		$payArr['productName'] 		= '全脂罐装奶粉900g儿童中老年成人孕妇奶粉02';//商品名称
		$payArr['productId'] 		= '11011650';//商品编号
		//$payArr['productDesc'] 	= '02';//商品描述
		//$payArr['backParam'] 		= 'USD#10.00#6.6886';//原样返回的商户数据    跨境下单，不可为空
		
		//数组转字符串
		$res = self::ArrTostring($payArr);
		//$priKey = $this->ips->priKey;//私钥
		//返回加签数据  私钥
		//$Sign = $this->ips->encrypt_passd($res,$priKey);
		$Sign = $this->ips->signByPrivateKey($res);
		//证书
		$Cert = strtoupper($this->ips->getCert());
		$Sign = strtoupper($Sign);
		//商户数据
		$payArr['merchantCert'] = $Cert;//商户证书
		$payArr['merchantSign'] = $Sign;//商户签名
		
		return $payArr;
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
		//mcrypt_generic_init($td,$key,'12345678abcdefgh');
		
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
		$td  	 = mcrypt_module_open('dec','','ecb','');
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
	
	/**
	 * 通过AES 解密 请求数据
	 * @param $skey	秘钥
	 * @param $sStr	需解密数据
	 * @return string;
	 */
	public function decrypt_pass($sStr,$skey)
	{
		$Str = hex2bin($sStr);
		$decrypted = mcrypt_decrypt(MCRYPT_RIJNDAEL_128,$skey,$Str,MCRYPT_MODE_ECB,'12345678abcdefgh');
		$dec_s = strlen($decrypted);
		
		$padding = ord($decrypted[$dec_s-1]);
		
		$decrypted = substr($decrypted,0,-$padding);
		
		return $decrypted;
	}
		
    //字符串拼接
	private static function ArrTostring($Arr=[]){
		$str = '';
		if(!empty($Arr)) {
			ksort($Arr);
			foreach($Arr as $key=>$val) {
				if ($val == null || $val == '') {
	                unset($Arr[$key]);
	                continue;
	            }
				$str .= $key.'='.$val.'&';
				
			}
		}
		return rtrim($str,'&');
	}
}
?>