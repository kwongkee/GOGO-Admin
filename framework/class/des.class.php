<?php
/**
 * Class Des3
 * @package des3
 */
class Des3 {
    /**
     * @var string
     */
    protected static $_secureKey;
    /**
     * @var string
     */
    protected static $_iv;
    /**
     * Des3 constructor.
     * @param string|null $key
     * @param string|null $iv
     */
    public static function init($key=null,$iv=null)
    {
        self::$_secureKey = $key;
        self::$_iv = $iv;
        return new self;
    }
    /**
     * @param $input
     * @return string
     * 加密
     */

    public  function encrypt($str){
        //使用MCRYPT_3DES算法,cbc模式
        $str=self::PaddingPKCS7($str);
        $td = mcrypt_module_open( MCRYPT_3DES, '', MCRYPT_MODE_CBC, '');
        mcrypt_generic_init($td, self::$_secureKey, self::$_iv);
        //初始处理
        $data = mcrypt_generic($td, $str);
        //清理加密模块
        mcrypt_generic_deinit($td);
        //结束
        mcrypt_module_close($td);
        return base64_encode($data);
    }
    /**
     * @param $encrypted
     * @return bool|string
     * 解密
     */
    public  function decrypt($str){
        $encrypted = base64_decode($str);
//        $key = str_pad($this->_secureKey,24,'0');
        $key=self::$_secureKey;
        $td = @mcrypt_module_open(MCRYPT_3DES,'',MCRYPT_MODE_CBC,'');
        if( self::$_iv == '' )
        {
            $iv = @mcrypt_create_iv (@mcrypt_enc_get_iv_size($td), MCRYPT_RAND);
        }
        else
        {
            $iv = self::$_iv;
        }
        $ks = @mcrypt_enc_get_key_size($td);
        @mcrypt_generic_init($td, $key, $iv);
        $decrypted = @mdecrypt_generic($td, $encrypted);
        @mcrypt_generic_deinit($td);
        @mcrypt_module_close($td);
        $y=self::pkcs5_unpad($decrypted);
        return $y;
    }


    /**
     * @param $text
     * @param $blocksize
     * @return string
     */
    protected  static function pkcs5_pad ($text, $blocksize) {
        $pad = $blocksize - (strlen($text) % $blocksize);
        return $text . str_repeat(chr($pad), $pad);
    }
    /**
     * @param $text
     * @return bool|string
     */
   protected static function pkcs5_unpad($text){
        $pad = ord($text{strlen($text)-1});
        if ($pad > strlen($text)) {
            return false;
        }
        if (strspn($text, chr($pad), strlen($text) - $pad) != $pad){
            return false;
        }
        return substr($text, 0, -1 * $pad);
    }
    /**
     * @param $data  填充
     * @return string
     */
    private static function PaddingPKCS7($input) {
        $srcdata = $input;
        $block_size = mcrypt_get_block_size('tripledes', 'ecb');
        $padding_char = $block_size - (strlen($input) % $block_size);
        $srcdata .= str_repeat(chr($padding_char),$padding_char);
        return $srcdata;
    }

    private function pkcs7_unpad(){
        return '1';
    }
    /*
     * 转编码
     */
    public static function charsetToGB($mixed,$Unicode)
    {
        if (is_array($mixed)) {
            foreach ($mixed as $k => $v) {
                if (is_array($v)) {
                    $mixed[$k] = self::charsetToGB($v,$Unicode);
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

    private static function _removeBr($str) {
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

}