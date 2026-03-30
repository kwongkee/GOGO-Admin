<?php

namespace think;

class Hmac{

    /**
     * 生成hash_hmac签名
     * @param $str
     * @param $key
     * @return string
     */
    public function generateHashSignature($str,$key)
    {
        $signature = "";
        if (function_exists('hash_hmac')){
            $signature = base64_encode(hash_hmac("sha256", $str, $key, true));
        }else{
            $signature = $this->generateHashSignaturer($str,$key);
        }

        return $signature;
    }


    public function generateHashSignaturer($str,$key)
    {
        $blocksize = 64;
        $hashfunc = 'sha1';
        if (strlen($key) > $blocksize) {
            $key = pack('H*', $hashfunc($key));
        }
        $key = str_pad($key, $blocksize, chr(0x00));
        $ipad = str_repeat(chr(0x36), $blocksize);
        $opad = str_repeat(chr(0x5c), $blocksize);
        $hmac = pack(
            'H*', $hashfunc(
                ($key ^ $opad) . pack(
                    'H*', $hashfunc(
                        ($key ^ $ipad) . $str
                    )
                )
            )
        );
        return base64_encode($hmac);
    }
}
