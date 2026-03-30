<?php


/**
 * 丰瑞祥 微信支付
 */
class Xfbpay {
    //保存类的实例的静态成员变量
    static private $_instance = null;
    //public $ip = 'http://114.242.25.239:8200/';//114.242.25.239:8101
    //public $ip = 'http://jiekou.xiangfubao.com.cn/';
    //public $ip = 'http://jiekou.xiangfubao.com.cn/';
    public $ip = 'http://jiekou.xiangfuba.cn/';
    //static private $mchid ='003020051110012';
    //static private $key = 'ZROXL6DLtFBHEsIJuutl*%dByyTT@EnL';

    static private $iv = '0102030405060708';//偏移量；加解密使用；

    static private $mechat = '000201708090609401';

    static private $mechatKey = 'epC_ynWL`OcRHOZT:ACtrk1BVv5yLcz{';

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
    //设置对象属性
    static public function setpro($mchid,$key) {
        self::$mchid = $mchid;
        self::$key   = $key;
    }


    // H5支付  微信支付
    public function wechat($params) {

        $inParking = [
            //9、微信公众号，13、支付宝条码，14、支付宝扫码，17、微信扫码，18、微信条码，25、微信小程序
            'payType'	=> 9,// 支付方式
            ///交易金额,以分为单位，没有小数点
            'amt'		=> $params['fee'],
            // 开发流水号
            'streamNo'	=> $params['tid'],
            // 支付下单
            'requestType'	=> 1,
            // 商品描述
            'body'		=> $params['title'],
            // 回调地址
            'notifyUrl'		=>'http://shop.gogo198.cn/addons/sz_yi/payment/tgwechat/XfbNotify.php',
        ];

        //数据转json  后进行 AES 加密 再 进行2进制转16进制；
        $strData = json_encode($inParking);
        $datas = $this->AESEncryptRequest(self::$mechatKey,$strData);

        $sendData['merchantNo'] = self::$mechat;//商户号
        $sendData['data'] 		= $datas;//数据
        //数据加密
        $signs 					= $this->sign($sendData,self::$mechatKey);
        //A-z排序后sha1加密，后md5加密；
        $sendData['sign'] 		= $signs;
        $sendData['appVersion'] = '1.0.0';
        //请求地址
        $url 					= $this->ip .'indirect/doPay';
        $res 					= $this->post_json($url,$sendData,true);

        if($res['retCode'] == 'SUCCESS') {
            //数据解密；
            $decrypt = $this->AESDecryptResponse(self::$mechatKey,$res['data']);
            //unset($res['data']);//删除data
            //解析 data 返回 返回的解密数据
            $Result = array_merge($res,json_decode($decrypt,true));
            //返回数据；
            return $Result;
        } else {
            return $res;
        }
    }


    // H5支付  支付宝
    public function alipay() {

        $inParking = [
            //9、微信公众号，13、支付宝条码，14、支付宝扫码，17、微信扫码，18、微信条码，25、微信小程序
            'payType'	=> 14,// 支付方式
            //进入时间：yyyyMMddHHmmss  TRUE
            'amt'		=> 20,//交易金额,
            // 开发流水号
            'streamNo'	=> 'GO'.mt_rand(10000000,9999999),//'粤YGB098',
            // 支付下单
            'requestType'	=> 1,//,
            // 商品描述
            'body'		=> '小商品',
            // 回调地址
            'notifyUrl'		=>'http://shop.gogo198.cn/addons/sz_yi/payment/tgwechat/XfbNotify.php',
        ];

        //数据转json  后进行 AES 加密 再 进行2进制转16进制；
        $strData = json_encode($inParking);
        $datas = $this->AESEncryptRequest(self::$mechatKey,$strData);

        $sendData['merchantNo'] = self::$mechat;//商户号
        $sendData['data'] 		= $datas;//数据
        //数据加密
        $signs 					= $this->sign($sendData,self::$mechatKey);
        //A-z排序后sha1加密，后md5加密；
        $sendData['sign'] 		= $signs;
        $sendData['appVersion'] = '1.0.0';
        //请求地址
        $url 					= $this->ip .'indirect/doPay';
        $res 					= $this->post_json($url,$sendData,true);

        if($res['retCode'] == 'SUCCESS') {
            //数据解密；
            $decrypt = $this->AESDecryptResponse(self::$mechatKey,$res['data']);
            //unset($res['data']);//删除data
            //解析 data 返回 返回的解密数据
            $Result = array_merge($res,json_decode($decrypt,true));
            //返回数据；
            return $Result;
        } else {
            return $res;
        }
    }

    /**
     * 查询车主签约状态
     */
    public function CheckCarNoSign($inData = null ,$config)
    {

        $inType = $inData['inType'];
        if ( $inType == 'PARKING' ) {//停车场

            $inParking = [//1.PARKING:停场停车场景，2.PARKING SPACE 车位停车场景；
                'tradeScene' => $inType, //进入时间：yyyyMMddHHmmss  TRUE
                //				'startTime'		=> date('YmdHis',$inData['starttime']),//'20180426151211',
                //车牌号。仅包括省份+车牌，不包括特殊字符
                'plateNumber' => $inData['CarNo'],//'粤YGB098',
                'openid' 	  => $inData['openid']
            ];

        } else if ( $inType == 'PARKING SPACE' ) {//车位停车场

            $inParking = [//1.PARKING:停场停车场景，2.PARKING SPACE 车位停车场景；
                'tradeScene' => $inType, //进入时间：yyyyMMddHHmmss  TRUE
                //				'startTime'		=> date('YmdHis',$inData['starttime']),//'20180426151211',
                //车牌号。仅包括省份+车牌，不包括特殊字符
                'plateNumber' => $inData['CarNo'],//'粤YGB098',
                'openid'	  => $inData['openid']
            ];
        }

        file_put_contents('./log/CheckstrData.txt',print_r($inParking,true),FILE_APPEND);

        //数据转json  后进行 AES 加密 再 进行2进制转16进制；
        $strData = json_encode($inParking);
        $datas   = $this->AESEncryptRequest($config['openkey'], $strData);

        $sendData['merchantNo'] = $config['mchid'];//商户号
        $sendData['data']       = $datas;//数据
        //数据加密
        $signs = $this->sign($sendData, $config['openkey']);
        //A-z排序后sha1加密，后md5加密；
        $sendData['sign'] = $signs;
        //请求地址
        $url = $this->ip . 'parking/doParking/getUsrStat';

        //file_put_contents('./log/ChecksendPost.txt',json_encode($sendData)."\r\n",FILE_APPEND);

        $res = $this->post_json($url, $sendData, true);

        //file_put_contents('./log/ChecksendRes.txt',json_encode($res)."\r\n",FILE_APPEND);

        if ( $res['retCode'] == 'SUCCESS' ) {
            //数据解密；
            $decrypt = $this->AESDecryptResponse($config['openkey'], $res['data']);
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
    /**
     * 2018-06-21
     * 微信车主免密支付退款接口
     * @param  $params      参 数
     * @param  $RefundMoney 退款金额
     * @param  $config    配置信息
     */
    public function Refund($params,$config,$RefundMoney=0)
    {
        //'orderNum'       => $params['upOrderId'],

        $RefundM = $RefundMoney > 0 ? ($RefundMoney*100):($params['pay_account']*100);

        /**
         * 退款订单把数据写入退款订单表中
         */
        $ReOrder = 'Re'.date('YmdHis',time()).mt_rand(1111,9999).'8'.mt_rand(11111,99999);//开发者退款流水号
        $Refund['account'] = $config['mchid'];
        $Refund['uniacid'] = $params['uniacid'];
        $Refund['openid']  = $params['user_id'];
        $Refund['type']    = 'Fwechat';
        $Refund['ordersn'] = $params['ordersn'];
        $Refund['upOrderId']   = $params['upOrderId'];
        $Refund['refundMoney'] = ($RefundM/100);
        $Refund['payMoney']    = $params['pay_account'];
        $Refund['create_date'] = time();
        $Refund['ReOrder']	   = $ReOrder;
        //插入数据库
        pdo_insert('parking_refund',$Refund);

        $data['streamNo']	     = $params['ordersn'];//平台订单号
        $data['refundStreamNo']  = $ReOrder;		  //退款订单号
        $data['amt']			 = $RefundM;		  //退款金额 分为单位

        //数据转json
        $strData = json_encode($data);
        //后进行 AES 加密 再 进行2进制转16进制；
        $datas = $this->AESEncryptRequest($config['openkey'],$strData);
        //商户号
        $sendData['merchantNo'] = $config['mchid'];
        //data数据
        $sendData['data'] = $datas;
        //数据加密；
        $signs = $this->sign($sendData,$config['openkey']);
        //A-z排序后sha1加密，后md5加密；
        $sendData['sign'] = $signs;
        //请求地址；
        $url 	= $this->ip .'common/doRefund';
        //数据请求
        $res 	= $this->post_json($url,$sendData,true);
        $tms = json_encode($res);
        file_put_contents('./log/Refunds'.date('Ym').'.txt', $tms."\r\n",FILE_APPEND);

        //返回成功
        if($res['retCode'] == 'SUCCESS') {
            //数据解密
            $decrypt = $this->AESDecryptResponse($config['openkey'],$res['data']);
            unset($res['data']);//删除data
            //解析 data 返回 返回的解密数据
            $Result = array_merge($res,json_decode($decrypt,true));
            //返回数据
            return $Result;
        }
        //返回结果
        return $res;
    }


    /**
     * 2018-04-25
     *  查询订单明细
     * 三
     */
    public function doQuery($orderNum = '',$inType = 'streamNo',$config)
    {

        if($inType == 'orderNum') {//支付时，平台返回的流水号
            $data = [
                'orderNum'=> $orderNum,
            ];
        }elseif($inType == 'streamNo'){//开发者流水号，确认同一门店内唯一
            $data = [
                'streamNo'=> $orderNum,
            ];

        } else {
            echo json_encode(['code'=>0,'msg'=>'inType is Null']);
        }

        //数据转json
        $strData = json_encode($data);
        //后进行 AES 加密 再 进行2进制转16进制；
        $datas = $this->AESEncryptRequest($config['openkey'],$strData);
        //商户号
        $sendData['merchantNo'] = $config['mchid'];
        //data数据
        $sendData['data'] = $datas;
        //数据加密；
        $signs = $this->sign($sendData,$config['openkey']);
        //A-z排序后sha1加密，后md5加密；
        $sendData['sign'] = $signs;
        //请求地址；
        $url 	= $this->ip .'common/doQuery';
        //数据请求
        $res 	= $this->post_json($url,$sendData,true);
        //返回成功
        if($res['retCode'] == 'SUCCESS') {
            //数据解密
            $decrypt = $this->AESDecryptResponse($config['openkey'],$res['data']);
            unset($res['data']);//删除data
            //解析 data 返回 返回的解密数据
            $Result = array_merge($res,json_decode($decrypt,true));
            //返回数据
            return $Result;
        }
        //返回结果
        return $res;
    }

    /**
     *  2018-04-25
     *  下载商户对账单
     *  $date  时间搓
     */
    public function downloadBill($date = 0,$config)
    {
        //如果传有时间过来就用传过来的数据，没有则默认为昨天时间；
        $date = $date >0 ? (date('Y-m-d',$date)):date('Y-m-d',strtotime("-1 day"));
        $data = [
            'day' => $date,//日期(YYYY-MM-DD)
        ];
        //数据转json
        $strData = json_encode($data);
        //后进行 AES 加密 再 进行2进制转16进制；
        $datas = $this->AESEncryptRequest($config['openkey'],$strData);
        //商户号
        $sendData['merchantNo'] = $config['mchid'];
        //data数据
        $sendData['data'] = $datas;
        //数据加密；
        $signs = $this->sign($sendData,$config['openkey']);
        //A-z排序后sha1加密，后md5加密；
        $sendData['sign'] = $signs;
        //请求地址
        $url = $this->ip .'download/downloadBill';
        //数据请求
        $res = $this->post_jsont($url,$sendData,true);

        //file_put_contents('./log/loadBill'.date('Ymd',$downloaDate).'.txt',$res);

        //$res	= json_decode($res,true);
        //返回结果
        /*file_put_contents('../../crontab/wx/loadBill'.$date.'.txt',$res,FILE_APPEND);
        file_put_contents("./log/loadBill".$date.'.txt',$res);
        if($res){

        }*/
        return ['retCode'=>'SUCCESS','msg'=>'SUCCESS','date'=>$res];
    }
    /**
     * 返回示例
     * 416557357653295104,1,200,Fri Feb 23 11:31:10 CST 2018,1,2,
     * 416557644480774144,5,100,Fri Feb 23 11:31:55 CST 2018,1,1,416557357653295104
     */


    /**
     * 通过AES加密 请求数据
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
        $input = $this->pkcs5_pad($input,$size);//计算补码数量

        $td = mcrypt_module_open(MCRYPT_RIJNDAEL_128,'',MCRYPT_MODE_ECB,'');
        mcrypt_generic_init($td,$key,self::$iv);
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
        $Str = hex2bin($sStr);
        $decrypted = mcrypt_decrypt(MCRYPT_RIJNDAEL_128,$skey,$Str,MCRYPT_MODE_ECB,self::$iv);
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
        $str = '';
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



    public function post_jsont($url,$data = null,$json=false)
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
        return $res = json_decode($res,true);
    }

    //请求扣费  2018-06-07
    public function postJsonFee($url,$dataArr = null)
    {
        $data_string = json_encode($dataArr);
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS,$data_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type:application/json;charset=utf-8',
                'Content-Length:'.strlen($data_string))
        );

        $result = curl_exec($ch);
        curl_close($ch);
        return $res = json_decode($result,true);
    }

    //请求成返回数据   给阿新
    public function postCredit($ordersn) {
        $postData = [
            'ordersn'=>$ordersn,
            'type'=>'wp'
        ];
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => "http://shop.gogo198.cn/foll/public/?s=api/pullOnlinePayStatusApi",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => json_encode($postData),
            CURLOPT_HTTPHEADER => array(
                "Cache-Control: no-cache",
                "Content-Type: application/json",
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);
    }
}

?>