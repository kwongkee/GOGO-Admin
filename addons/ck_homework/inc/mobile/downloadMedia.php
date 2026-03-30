<?php

class downloadMedia
{
    /**
     * 获取微信access_token
     * @return string access_token
     */
    public function getWechatAccessToken($appid='wx76d541cc3e471aeb',$secret='3e3d16ccb63672a059d387e43ec67c95')
    {

        $token_url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential';
        $get_token_url = $token_url . '&appid=' . $appid . '&secret=' . $secret;
        $res           = file_get_contents ( $get_token_url );
        @file_put_contents(__DIR__."/audio.log",$res."\n",FILE_APPEND);
        $res_arr       = json_decode ( $res, true );

        if($res_arr['access_token']) return $res_arr['access_token'];
        return false;
    }

    /**
     * 生成毫秒级时间戳
     */
    public function msectime()
    {
        list($msec, $sec) = explode(' ', microtime());
        return (float)sprintf('%.0f', (floatval($msec) + floatval($sec)) * 1000);
    }


    /**
     * 随机取出字符串
     * @param  int $strlen 字符串位数
     * @return string
     */
    public function salt($strlen)
    {
        $str  = "abcdefghkmnprstuvwxyzABCDEFGHKMNPRSTUVWXYZ23456789";
        $salt = '';
        $_len = strlen($str)-1;
        for ($i = 0; $i < $strlen; $i++) {
            $salt .= $str[mt_rand(0,$_len)];
        }
        return $salt;
    }

    /**
     * 下载微信素材资源到本地
     * @param  url $url  素材地址
     * @return json
     */
    public function download_media($media_id)
    {
        global $_W;
        // 获取微信服务器的录音ID
        if ($media_id) {
            // 获取access_token
            // $access_tokens = $this->getWechatAccessToken($_W['uniaccount']['key'],$_W['uniaccount']['secret']);
            $access_tokens = cache_load('accesstoken:'.$_W['uniacid'])['token'];
            // 下载素材接口
            $down_media_url   = 'https://api.weixin.qq.com/cgi-bin/media/get';
            /**
             * 根据access_tokens获取素材
             */
            $get_media_url = $down_media_url . '?access_token=' . $access_tokens . '&media_id=' . $media_id;

            // 获取文件流
            $file_flow = file_get_contents($get_media_url);

            // 本地保存目录
            $save_path = ATTACHMENT_ROOT.'/audios/'.$_W['uniacid'].'/'.date('Y',time()).'/'.date('m',time());

            if( !is_dir($save_path) ) {
                mkdir(iconv('UTF-8', 'GBK', $save_path), 0777, TRUE);
            }

            // 生成文件名
            $filename = $this->msectime() . $this->salt(6) . '.amr';

            // 写入文件流到本地
            $flag     = file_put_contents($save_path . '/' . $filename, $file_flow);

            unset($file_flow);
            if($flag !== FALSE) {
                return $save_path . '/' . $filename;
            }else {
                return FALSE;
            }
        }
    }
}