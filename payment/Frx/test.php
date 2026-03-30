<?php
define('IN_MOBILE', true);
define('PDO_DEBUG', true);
require_once '../../framework/bootstrap.inc.php';
require_once '../../app/common/bootstrap.app.inc.php';
load()->app('common');
load()->app('template');
load()->func('diysend');
global $_W;
global $_GPC;
//$curl = new Curl();

/**
 * 扣费步骤：
 * 1、首先请求入场
 * 2、请求扣费
 *
 * 1：入场需要参数；
 * 	Token = Parking
 * 	$inType:停车场景：PARKING SPACE 停车位；PARKING 停车场；
 */
//print_r($_GPC);

//$ip = 'http://114.2/42.25.239:8200/';11
//$mchid = '003020051110012';
//$OPENKEY = 'ZROXL6DLtFBHEsIJuutl*%dByyTT@EnL';

if(!empty($_GPC['Token']))
{
	echo '1112';
	$CarNo =trim($_GPC['CarNo']);
	$ordersn = trim($_GPC['orderSn']);
	$frx = Frx::getInstance();
	switch($_GET['Token'])
	{
		case "Parking"://1. PARKING：车场停车场景 ；2. PARKING SPACE 车位停车场景
			$res = $frx->in_parking($inData = '',$inType = 'PARKING');
			file_put_contents('./log/in_parking.txt', print_r($res,TRUE),FILE_APPEND);
			if($res['retCode'] == 'SUCCESS') {
				echo '<pre>';
				print_r($res);
			} else {
				echo '<pre>';
				print_r(['status'=>0,'msg'=>'停入失败']);
			}
			/*Array
			(
			    [data] => A2B31175EC8DA23904C7E8B1ED1FC1BCDABA8BDAD8FB703AF8D8303BA13CEA0781A16ACA4C1B6556E6A99D7A1AFB11493D3A9842B13C4FE1F57B2E4DCE387ADA661B697A94705F383B513E4B1E7274B5728E8607AF3467AF2215F3F5FEE345DB9D4D209055056537CF6E73B8FCEAE78BEAB3248786DEBBD24063691ADAD2869C
			    [sign] => 2dae144a39d8449d95975e9c1c6cbc4a
			    [message] => SUCCESS
			    [retCode] => SUCCESS
			)*/
		break;
		case "Parkings"://1. PARKING：车场停车场景 ；2. PARKING SPACE 车位停车场景
			$res = $frx->in_parkings($CarNo);
			file_put_contents('./log/InParkings.txt', print_r($res,TRUE),FILE_APPEND);
			if($res['retCode'] == 'SUCCESS') {
				echo '<pre>';
				print_r($res);
			} else {
				echo '<pre>';
				print_r(['status'=>0,'msg'=>'停入失败','res'=>$res]);
			}
		break;

		case "Status":
			$res = $frx->CheckCarNoSign($inData = '');
			//file_put_contents('./log/Check.txt', print_r($res,TRUE),FILE_APPEND);
			print_r($res);
		break;
		case "Fee":
			$res = $frx->FeeDeduction($inData = '',$inType = 'PARKING',$ordersn);
			file_put_contents('./log/Fee.txt', print_r($res,TRUE),FILE_APPEND);
			if($res['retCode'] == 'SUCCESS') {
				echo '<pre>';
				print_r($res);
			} else {
				echo '<pre>';
				print_r(['status'=>0,'msg'=>'扣费失败']);
			}
			/**
			 * {
			 * 	"appid":"wx01af4897eca4527e",
			 * 	"mchId":"1900006891",
			 * 	"openid":"oR-IB0ssN_p-54H9fxuWpUSDUx8w",
			 * 	"userState":"NORMAL"
			 * }
			 */

		break;
		case "Aesdecrypt":
			$str = "D708016F90B3324C08E0EDF1643C55F8B9C6A6E651B14E728365DFF2A8FAA83AE2B88024B72D7D2D57425441F7521B8ADDF7829EB0130B4404201E4B353299D3991732FE9792872BF6E29CFB98DD91387BE720D21F15CC3BD659E5BBDCDF30926EA6BB4536DC93468E634BA16A8CA47249B9ECAAC0D9A1095D13DEC272B11D76";
			$res = $frx->AESDecryptResponse($OPENKEY,$str);
			file_put_contents('./log/Aesdecrypt.txt', print_r(json_decode($res,true),TRUE),FILE_APPEND);
			echo '<pre>';
			print_r($res);
		break;
	}
} else {
	echo 'token is Null';
}



/**
 * 丰瑞祥 微信代扣接口
 */
class Frx {
	//保存类的实例的静态成员变量
	static private $_instance = null;
	//private $ip = 'http://114.242.25.239:8200/';//114.242.25.239:8101
//	private $ip = 'http://114.242.25.239:8101/';//114.242.25.239:8101
	public $ip = 'http://jiekou.xiangfubao.com.cn/';
	private $mchid ='000201507100239351';
	private $key   = 'da7c3ab0a510b873dd4159d1823460a9';

	/*private $mchid ='003020051110012';
	private $key = 'ZROXL6DLtFBHEsIJuutl*%dByyTT@EnL';*/
	private $iv = '0102030405060708';//偏移量；加解密使用；

	//私有的构造方法
	private function __construct(){}

	//用于访问类的实例的公共静态方法
	static public function getInstance()
	{
		if(!self::$_instance instanceof Frx) {
			//实例化
			self::$_instance = new self;
		}
		return self::$_instance;
	}

	/**
	 * 2018-04-25
	 * 喜柏停车：appid: wx01af4897eca4527e
	 * 用户入场通知接口
	 * 一
	 */
	public function in_parking($inData = [],$inType = 'PARKING') {

		if($inType == 'PARKING') {

			$inParking = [
				//1.PARKING:停场停车场景，2.PARKING SPACE 车位停车场景；
				'tradeScene'=> $inType,
				//进入时间：yyyyMMddHHmmss  TRUE
				'startTime'=> date('YmdHis',time()),
				//车牌号。仅包括省份+车牌，不包括特殊字符
				'plateNumber'=>'粤E31018',//'粤YGB0981',
				//停车场名称  false
				'parkingName'=>'伦教停车',
				//免费时长，单位秒；
				'freeTime'=>'1',
				//停车车辆的类型，可选值：大型车、小型车
				'carType'=>'小型车',

				'openid'=>'oR-IB0g3w57me3fDRAT2nSZG08VY',
			];

		} else if($inType == 'PARKING SPACE') {

			$inParking = [
				//1.PARKING:停场停车场景，2.PARKING SPACE 车位停车场景；
				'tradeScene'=>$inType,
				//进入时间：yyyyMMddHHmmss  TRUE
				'startTime'=>'20200422151211',
				//车辆类型：大型车，小型车  FALSE
				'carType'=>'小型车',
				//停车场名称  false
				'parkingName'=>'伦教停车',
				//免费时长，单位秒；
				'freeTime'=>'30',
				//商户 appid 下的唯一标识
				'openid'=>'oR-IB0g3w57me3fDRAT2nSZG08VY',//   oR-IB0ssN_p-54H9fxuWpUSDUx8w
				//车位编码
				'spaceNumber'=>'101401',
			];
		}

		$strData = json_encode($inParking);
		$datas = $this->AESEncryptRequest($this->key,$strData);

		$sendData['merchantNo'] = $this->mchid;//商户号
		$sendData['data'] 		= $datas;//数据
		//数据加密
		$signs 					= $this->sign($sendData,$this->key);
		//A-z排序后sha1加密，后md5加密；
		$sendData['sign'] 		= $signs;
		//请求地址
		$url 					= $this->ip .'parking/doParking/doNotifyication';
		$res 					= $this->post_json($url,$sendData,true);
		if($res['retCode'] == 'SUCCESS') {
			//数据解密；
			$decrypt = $this->AESDecryptResponse($this->key,$res['data']);
			//unset($res['data']);//删除data
			//解析 data 返回 返回的解密数据
			$Result = array_merge($res,json_decode($decrypt,true));
			//返回数据；
			return $Result;
		} else {
			return $res;
		}

		// //数据转json  后进行 AES 加密 再 进行2进制转16进制；
		// $strData = json_encode($inParking);
		// $datas = $this->AESEncryptRequest($this->key,$strData);
		//
		// $sendData['merchantNo'] = $this->mchid;//商户号
		// $sendData['data'] = $datas;//数据
		// //数据加密；
		// $signs = $this->sign($sendData,$this->key);
		// //A-z排序后sha1加密，后md5加密；
		// $sendData['sign'] = $signs;
		// $url = $this->ip .'parking/doParking/doNotifyication';
		// $res = $this->post_json($url,$sendData,true);
		// //return $sendData;
		// return $res;

		/**
		 * 返回参数
		 * appid	微信支付分配的公众账号 id
		 * mchId	微信支付分配的商户号
		 * userState	NORMAL：正常用户，
		 * 已开通车主服务，发入场通知  BLOCKED:丌符合免密规则用户
		 * OVERDUE: 用户欠费状态，提示用户到微信
		 * openid	用户在商户 appid 下的唯一标识，当用户入驻车主平台时进行返回
		 */
	}

	public function CheckCarNoSign($inData = null )
    {

        $inType = 'PARKING';
        if ( $inType == 'PARKING' ) {//停车场

            $inParking = [//1.PARKING:停场停车场景，2.PARKING SPACE 车位停车场景；
                'tradeScene' => $inType, //进入时间：yyyyMMddHHmmss  TRUE
                //				'startTime'		=> date('YmdHis',$inData['starttime']),//'20180426151211',
                //车牌号。仅包括省份+车牌，不包括特殊字符
                'plateNumber' => '粤E31018',//'粤YGB098',
                'openid' 	  => 'oR-IB0g3w57me3fDRAT2nSZG08VY',
            ];

        } else if ( $inType == 'PARKING SPACE' ) {//车位停车场

            $inParking = [//1.PARKING:停场停车场景，2.PARKING SPACE 车位停车场景；
                'tradeScene' => $inType, //进入时间：yyyyMMddHHmmss  TRUE
                //				'startTime'		=> date('YmdHis',$inData['starttime']),//'20180426151211',
                //车牌号。仅包括省份+车牌，不包括特殊字符
                'plateNumber' => '粤E31018',//'粤YGB098',
                'openid'	  => 'oR-IB0g3w57me3fDRAT2nSZG08VY',
            ];
        }

        //file_put_contents('./log/CheckstrData.txt',print_r($inParking,true),FILE_APPEND);

        //数据转json  后进行 AES 加密 再 进行2进制转16进制；
        $strData = json_encode($inParking);
        $datas   = $this->AESEncryptRequest($this->key, $strData);

        $sendData['merchantNo'] = $this->mchid;//商户号
        $sendData['data']       = $datas;//数据
        //数据加密
        $signs = $this->sign($sendData, $this->key);
        //A-z排序后sha1加密，后md5加密；
        $sendData['sign'] = $signs;
        //请求地址
        $url = $this->ip . 'parking/doParking/getUsrStat';

        //file_put_contents('./log/ChecksendPost.txt',json_encode($sendData)."\r\n",FILE_APPEND);

        $res = $this->post_json($url, $sendData, true);

        //file_put_contents('./log/ChecksendRes.txt',json_encode($res)."\r\n",FILE_APPEND);

        if ( $res['retCode'] == 'SUCCESS' ) {
            //数据解密；
            $decrypt = $this->AESDecryptResponse($this->key, $res['data']);
            unset($res['data']);//删除data
			unset($res['sign']);//删除data
            //解析 data 返回 返回的解密数据
            $Result = array_merge($res, json_decode($decrypt, true));
            //返回数据；
            return $Result;
        } else {
            return $res;
        }
    }


	public function in_parkings($CarNo) {

		$inParking = [
			//1.PARKING:停场停车场景，2.PARKING SPACE 车位停车场景；
			'tradeScene'=> 'PARKING',
			//进入时间：yyyyMMddHHmmss  TRUE
			'startTime' => date('YmdHis',time()),
			//车牌号。仅包括省份+车牌，不包括特殊字符
			'plateNumber'=> $CarNo,//'粤YGB0981',
			//停车场名称  false
			'parkingName'=>'伦教停车',
			//免费时长，单位秒；
			'freeTime'=>'30',
			//停车车辆的类型，可选值：大型车、小型车
			'carType'=>'小型车',
		];

		//数据转json  后进行 AES 加密 再 进行2进制转16进制；
		$strData = json_encode($inParking);
		$datas = $this->AESEncryptRequest($this->key,$strData);

		$sendData['merchantNo'] = $this->mchid;//商户号
		$sendData['data']      = $datas;//数据
		//数据加密；
		$signs = $this->sign($sendData,$this->key);
		//A-z排序后sha1加密，后md5加密；
		$sendData['sign'] = $signs;
		$url = $this->ip .'parking/doParking/doNotifyication';
		$res = $this->post_json($url,$sendData,true);
		if($res['retCode'] == 'SUCCESS') {
			//数据解密；
			$decrypt = $this->AESDecryptResponse($this->key,$res['data']);
			//unset($res['data']);//删除data
			//解析 data 返回 返回的解密数据
			$Result = array_merge($res,json_decode($decrypt,true));
			//返回数据；
			return $Result;
		} else {
			return $res;
		}
		//return $res;
	}

	/**
	 * 2018-04-25
	 * 申请扣款接口
	 * 1. PARKINGSPACE 车位停车场景
	 * 二
	 */
	public function FeeDeduction( $sendArr = null,$parkType = 'PARKING',$ordersn) {
		//目前支持 ：1. PARKING：车场停车场景 ；2. PARKINGSPACE 车位停车场景；3.GAS 加油场景；4.HIGHWAY 高速场景
		// {"body":"\u505c\u8f66\u670d\u52a1",
		// 	"outTradeNo":"G9919820200422885268233",
		// 	"totalFee":500,
		// 	"tradeScene":"PARKING",
		// 	"spbillCreateIp":"120.78.202.118",
		// 	"startTime":"20200422150930",
		// 	"endTime":"20200422155003",
		// 	"chargingTime":2460,
		// 	"plateNumber":"\u7ca4E31018",
		// 	"parkingName":"\u4f26\u6559\u505c\u8f66",
		// 	"carType":"\u5c0f\u578b\u8f66",
		// 	"deductMode":"PROACTIVE",
		// 	"openid":"oR-IB0g3w57me3fDRAT2nSZG08VY"}
		// $inCheckData = array();
    // $inCheckData['inType'] = $parkType;
    // $inCheckData['CarNo']  = '粤E31018';
    // $inCheckData['openid'] = 'oR-IB0g3w57me3fDRAT2nSZG08VY';
    // $Checkres = $this->CheckCarNoSign($inCheckData);

		switch($parkType) {
			case "PARKING"://停车场
				$data = [
					'body'=>'停车服务',//商品或支付单简要描述	true
					//'detail'=>'deatil',//商品名称明细列表 		False
					//'attach'=>'attach',//False 附加数据，在查询 API 和支付通知中原样返回，该字段主要用亍商户携带订单的自定义数据
					'outTradeNo'=>$ordersn,//商户系统内部的订单号,32个字符内、可包含字母  true
					'totalFee'=>'1',//订单总金额，单位为分，只能为整数  true
					//'fee_type'=>'CNY',//符合 ISO 4217 标准的三位字母代码，默讣人民币：CNY   False
					//'goodsTag'=>'goods',//商品标记，代金券或立减优惠功能的参数，说明详见代金券或立减优惠	False
					'tradeScene'=> $parkType,//true，
					'spbillCreateIp'=>'120.78.202.118',//调用微信支付 API 的机器 IP  true

					//parkingspace 车位停车部分；
					'startTime'=>'20200423093005',//true 即用户进入停车时间，格式为 yyyyMMddHHmmss，该值催缴时会向微信用户进行展示
					'endTime'=>'20200423100833',//False 即用户出停车场时间，格式为 yyyyMMddHHmmss，该值催缴时会向微信用户进行展示
					'chargingTime'=>'2460',//true 计费的时间长。单位为秒
					'plateNumber'=>'粤E31018',//车牌号
					'parkingName'=>'伦教停车',//所在停车场的名称
					'carType'=>'小型车',//False 停车车辆的类型，可选值：大型车、小型车
					'deductMode'=>'PROACTIVE',
					'openid'=>'oR-IB0g3w57me3fDRAT2nSZG08VY',
				];
			break;
			case "PARKING SPACE"://停车位
				$data = [
					'body'=>'停车服务',//商品或支付单简要描述	true
					//'detail'=>'deatil',//商品名称明细列表 		False
					//'attach'=>'attach',//False 附加数据，在查询 API 和支付通知中原样返回，该字段主要用亍商户携带订单的自定义数据
					'outTradeNo'=>$ordersn,//商户系统内部的订单号,32个字符内、可包含字母  true
					'totalFee'=>'2',//订单总金额，单位为分，只能为整数  true
					//'fee_type'=>'CNY',//符合 ISO 4217 标准的三位字母代码，默讣人民币：CNY   False
					//'goodsTag'=>'goods',//商品标记，代金券或立减优惠功能的参数，说明详见代金券或立减优惠	False
					'tradeScene'=> $parkType,//true，
					'spbillCreateIp'=>'120.78.202.118',//调用微信支付 API 的机器 IP  true

					//parkingspace 车位停车部分；
					'startTime'=>'20200423093005',//true 即用户进入停车时间，格式为 yyyyMMddHHmmss，该值催缴时会向微信用户进行展示
					'endTime'=>'20200423100833',//False 即用户出停车场时间，格式为 yyyyMMddHHmmss，该值催缴时会向微信用户进行展示
					'chargingTime'=>'2460',//true 计费的时间长。单位为秒
					//'carType'=>'小型车',//False 停车车辆的类型，可选值：大型车、小型车
					'parkingName'=>'parking',//False 所在停车场的名称
					'openid'=>'oR-IB0g3w57me3fDRAT2nSZG08VY',//'oR-IB0ssN_p-54H9fxuWpUSDUx8w',//用户在商户 appid 下的唯一标识	true
					'spaceNumber'=>'101401',//用户停车的车位编号	true
					'deductMode'=>'PROACTIVE',
				];
			break;
		}

		//加密后的data 数据拼接 open_key
		$strData = json_encode($data);//数据转json  后进行 AES 加密 再 进行2进制转16进制；
		$datas = $this->AESEncryptRequest($this->key,$strData);
		$sendData['merchantNo'] = $this->mchid;//商户号
		$sendData['data'] = $datas;//数据
		//数据加密；
		$signs = $this->sign($sendData,$this->key);
		$sendData['sign'] = $signs;//A-z排序后sha1加密，后md5加密；

		$url = $this->ip .'parking/doParking/dopayapply';
		$res = $this->post_json($url,$sendData,true);
//		return $sendData;
		return $res;
	}
	/**
	 * 扣费返回参数
	 * appid	微信支付分配的公众账号 id
	 * mchId	微信支付分配的商户号
	 * deviceInfo	终端设备号
	 * nonceStr		随机字符串，不长于 32位
	 */


	/**
	 * 2018-04-25
	 *  查询订单明细
	 * 三
	 */
	public function doQuery()
	{
		$data = [
			'orderNum' => '',//支付时，平台返回的流水号
			'streamNo' => '',//开发者流水号，确认同一门店内唯一
		];

		$strData = json_encode($data);//数据转json  后进行 AES 加密 再 进行2进制转16进制；
		$datas = $this->AESEncryptRequest($this->key,$strData);

		$sendData['merchantNo'] = $this->mchid;//商户号
		$sendData['data'] = $datas;//数据
		//数据加密；
		$signs = $this->sign($sendData,$this->key);
		$sendData['sign'] = $signs;//A-z排序后sha1加密，后md5加密；

		$url = $this->ip .'common/doQuery';
		$res = $this->post_json($url,$sendData,true);
		return $res;
	}
	/**	返回示例
	 * {
			"message": "查询成功",
			"retCode": "SUCCESS",
			"sign": "XXXXXXXXXXXXXXXXXXXXXXXXXXXX",
			"data": {
				"amt": "1",
				"orderNum": "416587267486777344",
				"payType": "1",
				"requestType": "1",
				"streamNo": "98745632100",
				"tradeState": "1",
				"trade_time": "Fri Feb 23 13:29:06 CST 2018"
			}
		}
	*/



	/**
	 *  2018-04-25
	 *  下载商户对账单
	 *  四
	 */
	public function downloadBill()
	{
		$data = [
			'day' => date('Y-m-d',time()),//日期(YYYY-MM-DD)
		];

		$strData = json_encode($data);//数据转json  后进行 AES 加密 再 进行2进制转16进制；
		$datas = $this->AESEncryptRequest($this->key,$strData);

		$sendData['merchantNo'] = $this->mchid;//商户号
		$sendData['data'] = $datas;//数据
		//数据加密；
		$signs = $this->sign($sendData,$this->key);
		$sendData['sign'] = $signs;//A-z排序后sha1加密，后md5加密；

		$url = $this->ip .'download/downloadBill';
		$res = $this->post_json($url,$sendData,true);
		return $res;
	}
	/**
	 * 返回示例
	 * 416557357653295104,1,200,Fri Feb 23 11:31:10 CST 2018,1,2,
	 * 416557644480774144,5,100,Fri Feb 23 11:31:55 CST 2018,1,1,416557357653295104
	 */


	/**
	 * 通过AES加密请求数据
	 * @param $encryptKey  秘钥
	 * @param array $query 加密字串；
	 * @return string;
	 */
	public function AESEncryptRequest($encryptKey,$query)
	{
		return $this->encrypt_pass($query,$encryptKey);
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
		$iv = '0102030405060708';
		mcrypt_generic_init($td,$key,$iv);
		$data = mcrypt_generic($td,$input);

		mcrypt_generic_deinit($td);
		mcrypt_module_close($td);

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
	 * 通过AES 解密请求数据
	 * @param $encryptKey	秘钥
	 * @param $data	需解密数据
	 * @return string;
	 */
	/*public function AESDecryptResponse($encryptKey,$data)
	{
		return $this->decrypt_pass($data,$encryptKey);
	}

	//解密
	public function decrypt_pass($sStr,$skey)
	{
		$iv = '0102030405060708';
		$Str = hex2bin($sStr);
		$decrypted = mcrypt_decrypt(MCRYPT_RIJNDAEL_128,$skey,$Str,MCRYPT_MODE_ECB,$iv);
		$dec_s = strlen($decrypted);

		$padding = ord($decrypted[$dec_s-1]);

		$decrypted = substr($decrypted,0,-$padding);
		return $decrypted;
	}*/

		/**
	 * 通过AES 解密 请求数据
	 * @param $encryptKey	秘钥
	 * @param $data	需解密数据
	 * @return string;
	 */
	public function AESDecryptResponse($encryptKey,$data)
	{
		return $this->decrypt_pass($data,$encryptKey);
	}

	//解密
	public function decrypt_pass($sStr,$skey)
	{
		//$iv = '0102030405060708';
		$Str = hex2bin($sStr);
		$decrypted = mcrypt_decrypt(MCRYPT_RIJNDAEL_128,$skey,$Str,MCRYPT_MODE_ECB,$this->iv);
		$dec_s = strlen($decrypted);

		$padding = ord($decrypted[$dec_s-1]);

		$decrypted = substr($decrypted,0,-$padding);

		return $decrypted;
	}

	public function pkcs5_unpad($text)
	{
	    $pad = ord($text{strlen($text)-1});
	    if ($pad > strlen($text)) return false;
	    if (strspn($text, chr($pad), strlen($text) - $pad) != $pad) return false;
	    return substr($text, 0, -1 * $pad);
	}

	/**
	 * 上传数据部分加密；
	 */
	public function sign($data = null ,$keys)
	{
		ksort($data);
		foreach($data as $key => $val)
		{
			$str .= $key .'='.$val.'&';
		}
		$str = $str .'open_key='.$keys;
		$sign = strtolower(sha1($str));
		$sign = strtolower(md5($sign));
		return 	$sign;
	}

	/**
	 * 发送post请求  json 数据；
	 * CURL post
	 * @param $url: 请求地址
	 * @param $data: 请求数据
	 * @param $json: 是否json 数据请求；
	 */
	public function post_json($url,$data = null,$json=false)
	{
		$curl = curl_init();
		curl_setopt($curl,CURLOPT_SSL_VERIFYPEER,false);
		curl_setopt($curl,CURLOPT_SSL_VERIFYHOST,false);
		curl_setopt($curl,CURLOPT_HEADER,0); //头文件信息做数据流输出
		curl_setopt($curl,CURLOPT_URL,$url);
		if(!empty($data)) {

			if($json && is_array($data)){
				$data = json_encode($data);
			}

			curl_setopt($curl,CURLOPT_POST,1);
			curl_setopt($curl,CURLOPT_POSTFIELDS,$data);

			if($json) {//发送JSON数据；

				curl_setopt($curl,CURLOPT_HEADER,0);
				curl_setopt($curl,CURLOPT_HTTPHEADER,array(
					'Content-Type:text/html;charset=utf-8',
					'Content-Length:'.strlen($data)
				));
			}
		}
		curl_setopt($curl,CURLOPT_RETURNTRANSFER,1);
		$res = curl_exec($curl);
		$errorno = curl_errno($curl);
		if($errorno) {//错误
			return ['errorno'=>false,'errmsg'=>$errorno];
		}
		curl_close($curl);
		return json_decode($res,true);
	}

	public function post_json1($url,$post_data)
	{
		if(!empty($post_data) && is_array($post_data) ) {

			$data = json_encode($post_data);

		} else {
			return 'data is No Array';
		}

		//初始化
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // 跳过证书检查
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, true);  // 从证书中检查SSL加密算法是否存在
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($ch, CURLOPT_POSTFIELDS,$data);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			'Content-Type: application/json',
			'Content-Length: ' . strlen($data))
		 );
		$result = curl_exec($ch);
		curl_close($ch);
		return $result;
	}



    /*
	array(2) {

		//订单列表  1
	  	["OrderList"] => array(1) {

	  		//订单详细
		    [0] => array(34) {
		      <!-- 企业电子订单编号 -->
		      ["EntOrderNo"] => string(20) "SH201805251436253728"
		      <!-- 电子订单状态 -->
		      ["OrderStatus"] => float(0)
		      <!-- 支付状态 -->
		      ["PayStatus"] => float(0)
		      <!-- 订单商品总额 -->
		      ["OrderGoodTotal"] => float(0.01)
		      <!-- 订单商品总额币制 -->
		      ["OrderGoodTotalCurr"] => string(3) "CNY"
		      <!-- 订单运费 -->
		      ["Freight"] => float(0)
		      <!-- 税款 -->
		      ["Tax"] => float(0)
		      <!-- 抵付金额 -->
		      ["OtherPayment"] => int(0)
		      <!-- 抵付说明 -->
		      ["OtherPayNotes"] => string(6) "待定"
		      <!-- 其它费用 -->
		      ["OtherCharges"] => int(0)
		      <!-- 实际支付金额 -->
		      ["ActualAmountPaid"] => float(0.01)
		      <!-- 收货人名称 -->
		      ["RecipientName"] => string(9) "区广祺"
		      <!-- 收货人地址 -->
		      ["RecipientAddr"] => string(38) "北京北京市顺义区和平新区22"
		      <!-- 收货人电话 -->
		      ["RecipientTel"] => float(13809703680)
		      <!-- 收货人所在国 -->
		      ["RecipientCountry"] => string(2) "CN"
		      <!--  收货人收货人行政区代码  进口需要填收货人所在行政区域代码 出口可空 -->
		      ["RecipientProvincesCode"] => string(2) "CN"
		      <!-- 下单人账户   可空 -->
		      ["OrderDocAcount"] => string(11) "13809703680"
		      <!-- 下单人姓名  -->
		      ["OrderDocName"] => string(9) "区广祺"
		      <!-- 下单人证件类型  可空  -->
		      ["OrderDocType"] => string(2) "01"
		      <!-- 下单人证件号  可空 -->
		      ["OrderDocId"] => string(14) "48201902930233"
		      <!-- 下单人电话 -->
		      ["OrderDocTel"] => string(11) "13809703680"
		      <!-- 订单日期 -->
		      ["OrderDate"] => string(19) "2018/5/25  14:36:25"
		      <!-- 商品批次号 -->
		      ["BatchNumbers"] => string(0) ""
		      <!-- 发票类型 -->
		      ["InvoiceType"] => string(0) ""
		      <!-- 发票编号 -->
		      ["InvoiceNo"] => string(0) ""
		      <!-- 发票抬头 -->
		      ["InvoiceTitle"] => string(0) ""
		      <!-- 纳税人标识号 -->
		      ["InvoiceIdentifyID"] => string(0) ""
		      <!-- 发票内容 -->
		      ["InvoiceDesc"] => string(0) ""
		      <!-- 发票金额 -->
		      ["InvoiceAmount"] => string(0) ""
		      <!-- 开票日期 -->
		      ["InvoiceDate"] => string(0) ""
		      <!-- 备注 -->
		      ["Notes"] => string(0) ""
		      	//商品列表
		      	["GoodsList"] => array(2) {
			        [0] => array(17) {
			        	<!-- 商品序号 -->
				          ["Seq"] => float(1)
				          <!-- 企业商品自编号  -->
				          ["EntGoodsNo"] => string(20) "O3179354109006028301"
				         <!-- 检验检疫商品备案编号 -->
				          ["CIQGoodsNo"] => string(15) "CIQ152755383517"
				         <!-- 海关正式备案编号 -->
				          ["CusGoodsNo"] => string(11) "43212323123"
				          <!-- HS编码 -->
				          ["HSCode"] => string(10) "9619009000"
				          <!-- 商品名称 -->
				          ["GoodsName"] => string(9) "纸尿裤"
				         <!-- 规格型号 -->
				          ["GoodsStyle"] => string(13) "L（大码）"
				          <!-- 企业商品描述  -->
				          ["GoodsDescribe"] => string(0) ""
				          <!-- 原产国 -->
				          ["OriginCountry"] => string(2) "CN"
				          <!-- 商品条形码 可空 -->
				          ["BarCode"] => string(13) "9351109000028"
				          <!-- 品牌  可空 -->
				          ["Brand"] => string(13) "spoiled piggy"
				          <!-- 数量 -->
				          ["Qty"] => float(1)
				          <!-- 计量单位 -->
				          ["Unit"] => float(120)
				          <!-- 单价 -->
				          ["Price"] => float(0.01)
				          <!-- 总价 -->
				          ["Total"] => float(0.01)
				          <!-- 币制 -->
				          ["CurrCode"] => string(3) "CNY"
				          <!-- 备注 可空 -->
				          ["Notes"] => NULL
			        }

			        [1] => array(17) {
			          ["Seq"] => float(2)
			          ["EntGoodsNo"] => string(20) "O3179354109006028301"
			          ["CIQGoodsNo"] => string(15) "CIQ152755383517"
			          ["CusGoodsNo"] => string(11) "43212323123"
			          ["HSCode"] => string(10) "9619009000"
			          ["GoodsName"] => string(9) "纸尿裤"
			          ["GoodsStyle"] => string(13) "L（大码）"
			          ["GoodsDescribe"] => string(0) ""
			          ["OriginCountry"] => string(2) "CN"
			          ["BarCode"] => string(13) "9351109000028"
			          ["Brand"] => string(13) "spoiled piggy"
			          ["Qty"] => float(2)
			          ["Unit"] => float(121)
			          ["Price"] => float(1.01)
			          ["Total"] => float(1.01)
			          ["CurrCode"] => string(3) "CNY"
			          ["Notes"] => NULL
			        }

		      	}//商品列表




	      	//物流信息
	      	["OrderWaybillRel"] => array(4) {
		        ["EHSEntNo"] => NULL
		        ["EHSEntName"] => NULL
		        ["WaybillNo"] => NULL
		        ["Notes"] => NULL
	      	}



	      	//支付信息
	      	["OrderPaymentRel"] => array(4) {
		        ["PayEntNo"] => float(2342342334)
		        ["PayEntName"] => string(9) "易付宝"
		        ["PayNo"] => string(22) "SH20180525143625460868"
		        ["Notes"] => NULL
	      	}


		    }//订单详细

	  	}//订单列表

	    //订单头部  2
	  	["OrderHead"] => array(12) {
	  		<!-- 申报企业编号 -->
		    ["DeclEntNo"] => string(12) "CO0000000033"
		    <!-- 申报企业名称 -->
		    ["DeclEntName"] => string(14) "测试公司17"
		    <!-- 电商企业编号 -->
		    ["EBEntNo"] => string(12) "CO0000000033"
		    <!-- 电商企业名称  可空 -->
		    ["EBEntName"] => string(14) "测试公司17"
		    <!-- 电商平台企业编号  -->
		    ["EBPEntNo"] => string(12) "CO0000000033"
		    <!-- 电商平台企业名称  -->
		    ["EBPEntName"] => string(14) "测试公司17"
		    <!-- 电商平台互联网域名  -->
		    ["InternetDomainName"] => string(15) "www.gogo198.com"
		    <!-- 申报时间 -->
		    ["DeclTime"] => int(1528340174)
		    <!-- 操作方式 -->
		    ["OpType"] => string(1) "A"
		    !-- 进出口标示 -->
		    ["IeFlag"] => string(1) "I"
		    <!-- 主管海关代码  -->
		    ["CustomsCode"] => int(5107)
		    <!-- 检验检疫机构代码 -->
		    ["CIQOrgCode"] => int(441200)
	  	}

	}*/

}
?>
