<?php

namespace app\api\controller;

use think\Controller;
use think\Request;
use think\Response;
use think\Db;
use think\Log;
use think\Cache;

class AcceptParkingData extends Controller
{
    protected static $requestData;

    public function __construct(Request $request)
    {
        parent::__construct($request);
        self::$requestData['user'] = $request->header('user');
        self::$requestData['pwd']  = $request->header('pwd');
        self::$requestData['data'] = $request->post('data');
        @file_put_contents('../runtime/log/out/' . date('Ymd', time()) . '.txt', date('Y-m-d H:i:s', time()) . '|实时泊位信息|' . json_encode($request->post()) . "\n", FILE_APPEND);
    }

    public function acceptParkingData(Request $request, Response $response)
    {
        try {
            $packResult = validationPacket(self::$requestData);
        } catch (\Exception $exception) {
            return json(['statusCode' => 1003, 'msg' => '数据解密错误', 'data' => '']);
        }

        if (!$packResult['error']) {
            $response->header("Content-Type", "application/json;charset=utf-8")
                ->header("Content-Length", strlen($packResult['errorMsg']))
                ->data($packResult['errorMsg'])->send();
            exit();
        }
        if (empty($packResult['errorMsg']['parkCode']) || $packResult['errorMsg']['parkStatus'] === '') {
            return json(['statusCode' => 1003, 'msg' => '数据解密错误', 'data' => '']);
        }

//        if ($packResult['errorMsg']['parkStatus']==1){
//            Cache::inc('stopIn');
//        }else if ($packResult['errorMsg']['parkStatus']==4){
//            Cache::inc('timeOut');
//        }

        Db::startTrans();
        try {
            Db::name("parking_space")->where("numbers", $packResult['errorMsg']['parkCode'])
                ->update(['status' => $packResult['errorMsg']['parkStatus'], 'up_time' => (int)$packResult['errorMsg']['upTime']]);
            Db::commit();
        } catch (Exception $e) {
            Db::rollback();
            Log::write($e->getMessage());
            return json(['statusCode' => 1001, 'msg' => '失败', 'data' => '']);
        }
        return json(['statusCode' => 1001, 'msg' => '成功', 'data' => '']);
    }
}
