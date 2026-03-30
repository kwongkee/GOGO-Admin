<?php

namespace app\common\logic;

use think\Model;
use think\Curl;
use think\Cache;

class HuaWeiFace extends Model
{
    protected $curl;
    protected $tokenUrl    = 'https://iam.cn-north-1.myhuaweicloud.com/v3/auth/tokens';//获取token
    protected $addFaceUrl  = 'https://face.cn-north-1.myhuaweicloud.com/v1/fd9bf06b98204ed08fca06e647ddd40d/face-sets/googo/faces';
    protected $liveFaceUrl = 'https://face.cn-north-1.myhuaweicloud.com/v1/fd9bf06b98204ed08fca06e647ddd40d/live-detect';//活体检测
    protected $compareUrl  = 'https://face.cn-north-1.myhuaweicloud.com/v1/fd9bf06b98204ed08fca06e647ddd40d/face-compare';//人脸对比

    public function __construct()
    {
        $this->curl = new Curl();
    }


    /**
     * 获取接口token
     * @return mixed
     */
    public function getAuthToken()
    {
        $this->curl->setHeader("Content-Type", "application/json");
        $this->curl->setOpt(CURLOPT_HEADER, true);
        $data = '{"auth":{"identity":{"methods":["password"],"password":{"user":{"name":"Gogo198","password":"p86329911","domain":{"name":"Gogo198"}}}},"scope":{"project":{"id":"fd9bf06b98204ed08fca06e647ddd40d"}}}}';
        $this->curl->post($this->tokenUrl, $data);
        $headerSize = curl_getinfo($this->curl->curl, CURLINFO_HEADER_SIZE);
        $header     = substr($this->curl->response, 0, $headerSize);
        $header     = explode("\n", $header);
        $token      = trim(explode(":", $header[7])[1]);
        $this->writeLog();
        Cache::set('X-Subject-Token', $token, 86000);
        return $token;
    }


    /**
     * 添加人脸
     * @param $str
     * @return mixed
     */
    public function addFace($str)
    {
        $token = Cache::get('X-Subject-Token');
        if (empty($token)) {
            $token = $this->getAuthToken();
        }
        $this->curl->setHeader("Content-Type", "application/json");
        $this->curl->setHeader("x-auth-token", $token);
        $data = [
            'image_base64'      => $str,
            'external_image_id' => 'imageID',
            //            'external_fields'=>['timestamp'=>time(),'id'=>12]
        ];
        $this->curl->post($this->addFaceUrl, json_encode($data));
        $this->writeLog();
        return $this->curl->response;
    }


    /**
     * 活体检测
     * @param $videoStr
     * @return mixed
     */
    public function liveDetect($videoStr)
    {
        $token = Cache::get('X-Subject-Token');
        if (empty($token)) {
            $token = $this->getAuthToken();
        }
        $this->curl->setHeader("Content-Type", "application/json");
        $this->curl->setHeader("x-auth-token", $token);
        $reqData = [
            'video_base64' => $videoStr,
            'actions'      => "3"
        ];
        $this->curl->post($this->liveFaceUrl, json_encode($reqData));
        $this->writeLog();
        return $this->curl->response;
    }


    /**
     * 人脸对比
     * @param $image1
     * @param $image2
     * @return mixed
     */
    public function faceCompare($image1, $image2)
    {
        $token = Cache::get('X-Subject-Token');
        if (empty($token)) {
            $token = $this->getAuthToken();
        }
        $this->curl->setHeader("Content-Type", "application/json");
        $this->curl->setHeader("x-auth-token", $token);
        $this->curl->post($this->compareUrl, json_encode(['image1_base64' => $image1, 'image2_base64' => $image2]));
        $this->writeLog();
        return $this->curl->response;
    }

    public function writeLog($url = '')
    {
        @file_put_contents('../runtime/log/face_api.log',
            date('Y-m-d H:i:s', time()) . "||url:" . $url . "||response:" . $this->curl->response . "\n", FILE_APPEND);
    }
}
