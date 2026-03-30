<?php


namespace app\common\logic;
use think\Model;
use think\Curl;

/**
 * 硬件设备接口增删改查
 * Class GetIOTDeviceAPIData
 * @package app\common\logic
 */
class GetIOTDeviceAPIData extends Model
{
    protected  $curl;
    protected  $loginUrl = 'https://testapi.coolkit.cn:8080/api/user/login';//登录
    protected  $singleDeveUrl = 'https://testapi.coolkit.cn:8080/api/user/device/';//获取单条设备信息
    protected  $refToken = 'https://testapi.coolkit.cn:8080/api/user/refresh';//从新获取token值
    protected $trunOnUrl = 'https://testapi.coolkit.cn:8080/api/user/device/status';
    public function __construct()
    {
        $this->curl = new Curl();
    }



    //登录获取token
    public function getTokenInfo($data,$token)
    {
        $this->curl->setHeader("Authorization",$token);
        $this->curl->setHeader("Content-Type","application/json");
        $this->curl->post($this->loginUrl,$data);
        $this->writeLog();
        return $this->curl->response;
    }

    //用 refresh token刷新获取acesss token
    public function getRefToken($rt)
    {
        $this->curl->get($this->refToken.'/'.$rt);
        $this->writeLog();
        return $this->curl->response;
    }

    //获取单个设备信息
    public function getSingleDeviceInfo($devId,$token)
    {
        $this->curl->setHeader("Authorization",$token);
        $this->curl->setHeader("Content-Type","application/json");
        $this->curl->get($this->singleDeveUrl.$devId);
        $this->writeLog($this->singleDeveUrl.$devId);
        return $this->curl->response;
    }

    //获取所有设备
    public function getAllDEviceList($devId)
    {

    }

    /**
     * 开关
     * @param $data
     * @param $token
     * @return mixed
     */
    public function turnOn($data,$token)
    {
        $this->curl->setHeader("Authorization",$token);
        $this->curl->setHeader("Content-Type","application/json");
        $this->curl->post($this->trunOnUrl,$data);
        $this->writeLog();
        return $this->curl->response;
    }

    public function writeLog($url='')
    {
        @file_put_contents('../runtime/log/device_api.log',date('Y-m-d H:i:s',time())."||url:".$url."||response:".$this->curl->response."\n",FILE_APPEND);
    }
}
