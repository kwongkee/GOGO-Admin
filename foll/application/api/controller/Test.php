<?php

namespace app\api\controller;

use think\Controller;
use think\Exception;
use think\Request;
use think\Response;
use think\Loader;
use think\Cache;
use think\Log;
use think\Db;

class Test extends Controller
{
    protected static $requestData;
    public function __construct()
    {
        // 0(空车位)   1(黄灯)   3(绿灯)  4(红灯)  2(停用)
        self::$requestData['user'] = 'gogo';
        self::$requestData['pwd']  = 'gogo198';
        self::$requestData['data'] = 'ZXkp9p+n6vwZ+s2sUO+DLlOI2Y/7xexhZLu7hX3cIisFGJeUpeFjZQ5SILyUWBNoxOTh2CQRRZ6m0HaegiSAPHZ9SMu4boyHzsPu6CZrr4w=';
        //self::$requestData['data'] = 'ZXkp9p+n6vwZ+s2sUO+DLlOI2Y/7xexhZLu7hX3cIisFGJeUpeFjZQ5SILyUWBNoEMGzrey5YOTRes3L/q7+bkvlJm+om+3OcbyXJOvp+C4=';//ZXkp9p+n6vwZ+s2sUO+DLlOI2Y/7xexhZLu7hX3cIisFGJeUpeFjZQ5SILyUWBNoEMGzrey5YOTRes3L/q7+bkvlJm+om+3OcbyXJOvp+C4=
        //
    }

    public function index(){

        try {
            $packResult = validationPacket(self::$requestData);
        } catch (\Exception $e) {
            Log::write(json_encode(['错误信息' => $e->getMessage(), '行号' => $e->getCode(), '文件' => $e->getFile()]));
            return json(['statusCode' => 1002, 'msg' => '解码失败', 'data' => '']);
        }


        $ordersn = date('YmdHi',time()).str_pad(mt_rand(1,999999),5,'0',STR_PAD_LEFT).substr(microtime(),2,6).substr(implode(null, array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0,
            6);

        echo $ordersn;

        /*echo '<pre>';
        print_r($packResult);*/


        /*if (empty($packResult['errorMsg']['etime']) || !isset($packResult['errorMsg']['etime'])) {
            return json(['statusCode' => 1002, 'msg' => '离场时间不能空', 'data' => $packResult['errorMsg']]);
        }
        if (!isset($packResult['errorMsg']['parkCode']) || !isset($packResult['errorMsg']['ordersn'])) {
            return json(['statusCode' => 1002, 'msg' => '参数解析不对', 'data' => $packResult['errorMsg']]);
        }

        if (empty($packResult['errorMsg']['ordersn'])) {
            return json(['statusCode' => 1002, 'msg' => '订单号为空', 'data' => '']);
        }*/
    }
}
