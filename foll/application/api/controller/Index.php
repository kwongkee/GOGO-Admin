<?php

namespace app\api\controller;
use think\Controller;
use think\Request;
use think\Des3;

class Index extends Controller
{
    private $_secureKey="ZROXL6DLtFBHEsIJuutlBy";
    private $_iv="20180424";
    public function index(Request $request) 
    {
//        if(empty($request->post())){return json_encode(["msg"=>"no data","code"=>"1001"]);}
//        $Token=md5(KEY.date("Ymd",time()).$request->post('openid'));
//        if($Token!=$request->post("sigin")){
//            return json_encode(['msg'=>"token verification failed!","code"=>"1002"]);
//        }
//        $this->requestPostData=$request->post();
//        return $this->carParkingDepartureCalculation();
    }

    public function test(Request $request)
    {
//        $data =["parkCode"=>"001050","stime"=>"1524707486","cardNo"=>"6223228802600499"];
//        echo json_encode($data);
        $data =$this->charsetToGB("123","GB2312");
        $this->_secureKey  = $this->charsetToGB("ZROXL6DLtFBHEsIJuutlBygo",'GB2312');//转gbk
        $this->_iv  = $this->charsetToGB("20180504","GB2312");//转gbk
        dump($this->encrypt($data));
    }

    public function test2(Request $request)
    {
        $des3=new Des3("ZROXL6DLtFBHEsIJuutlBy","20180424");
        $data=$des3->decrypt($request->post('data'));
        $data=$this->charsetToGB($data,"UTF-8");
        dump($data);
    }

    function decrypt($encrypted){
        $encrypted = base64_decode($encrypted);
//        $key = str_pad($this->_secureKey,24,'0');
        $key=$this->_secureKey;
        $td = @mcrypt_module_open(MCRYPT_3DES,'',MCRYPT_MODE_CBC,'');
        if( $this->_iv == '' )
        {
            $iv = @mcrypt_create_iv (@mcrypt_enc_get_iv_size($td), MCRYPT_RAND);
        }
        else
        {
            $iv = $this->_iv;
        }
        $ks = @mcrypt_enc_get_key_size($td);
        @mcrypt_generic_init($td, $key, $iv);
        $decrypted = @mdecrypt_generic($td, $encrypted);
        @mcrypt_generic_deinit($td);
        @mcrypt_module_close($td);
//        $y=$this->pkcs5_unpad($decrypted);
        return $decrypted;
    }

    private function encrypt($str){
        //使用MCRYPT_3DES算法,cbc模式
        $str=$this->PaddingPKCS7($str);
        $td = mcrypt_module_open( MCRYPT_3DES, '', MCRYPT_MODE_CBC, '');
        mcrypt_generic_init($td, $this->_secureKey, $this->_iv);
        //初始处理
        $data = mcrypt_generic($td, $str);
        //清理加密模块
        mcrypt_generic_deinit($td);
        //结束
        mcrypt_module_close($td);
//        return base64_encode($data);
        return $data;
    }
    private function PaddingPKCS7($input) {
        $srcdata = $input;
        $block_size = mcrypt_get_block_size('tripledes', 'ecb');
        $padding_char = $block_size - (strlen($input) % $block_size);
        $srcdata .= str_repeat(chr($padding_char),$padding_char);
        return $srcdata;
    }
    private function _removeBr($str) {
        $len = strlen( $str );
        $newStr = "";
        $str = str_split($str);
        for ($i = 0; $i < $len; $i++ ) {
            if ($str[$i] != '\n' and $str[$i] != '\r') {
                $newStr .= $str[$i];
            }
        }
        return $newStr;
    }


//    public static function getBytes($string) {
//        $bytes =array();
//        for($i = 0; $i < strlen($string); $i++){
//            $bytes []= ord($string[$i]);
//        }
//        return $bytes;
//    }

    public function charsetToGB($mixed,$Unicode)
    {
        if (is_array($mixed)) {
            foreach ($mixed as $k => $v) {
                if (is_array($v)) {
                    $mixed[$k] = $this->charsetToGB($v,$Unicode);
                } else {
                    $encode = mb_detect_encoding($v, array('ASCII', 'UTF-8', 'GB2312', 'GBK', 'BIG5'));
                    if ($encode == 'UTF-8') {
                        $mixed[$k] = iconv('UTF-8', $Unicode, $v);
                    }
                }
            }
        } else {
            $encode = mb_detect_encoding($mixed, array('ASCII', 'UTF-8', 'GB2312', 'GBK', 'BIG5'));
            if ($encode == 'UTF-8') {
                $mixed = iconv('UTF-8', $Unicode, $mixed);
            }
        }
        return $mixed;
    }
    
}