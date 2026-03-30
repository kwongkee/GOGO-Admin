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

class Leaving extends Controller
{
    protected static $requestData;

    /*
     * 初始化接受数据
     */
    public function __construct(Request $request)
    {
        parent::__construct($request);
        self::$requestData['user'] = $request->header('user');
        self::$requestData['pwd'] = $request->header('pwd');
        self::$requestData['data'] = $request->post('data');
        @file_put_contents(
            '../runtime/log/out/' . date('Ymd', time()) . '.log',
            '离场计算订单原始数据：' . date('Y-m-d H:i:s', time()) . '|数据:' . $request->post('data') . "\n",
            FILE_APPEND);
    }

    //接受离场信息接口
    public function acceptLeavingTime(Request $request, Response $response)
    {
        $packResult = validationPacket(self::$requestData);
        if (!$packResult['error']) {
            ResponseResult($response, $packResult['errorMsg']);
        }
        if (empty($packResult['errorMsg']['etime']) || !isset($packResult['errorMsg']['etime'])) {
            return json(['statusCode' => 1002, 'msg' => '参数错误', 'data' => '']);
        }
        if (!isset($packResult['errorMsg']['parkCode']) || !isset($packResult['errorMsg']['ordersn'])) {
            return json(['statusCode' => 1002, 'msg' => '参数解析错误', 'data' => '']);
        }
        Db::name('parking_queue')->insert([
            'queue' => 'leave',
            'payload' => json_encode($packResult['errorMsg']),
            'create_time' => time()
        ]);
        return json(['statusCode' => 1001, 'msg' => '完成', 'data' => '']);
    }

    //接受离场信息接口
    public function acceptLeavingTime2(Request $request, Response $response) {
        try {
            $packResult = validationPacket(self::$requestData);
        } catch (\Exception $e) {
            Log::write(json_encode(['错误信息' => $e->getMessage(), '行号' => $e->getCode(), '文件' => $e->getFile()]));
            return json(['statusCode' => 1002, 'msg' => '解码失败', 'data' => '']);
        }

        if (!$packResult['error']) {
            ResponseResult($response, $packResult['errorMsg']);
        }
        if (empty($packResult['errorMsg']['etime']) || !isset($packResult['errorMsg']['etime'])) {
            return json(['statusCode' => 1002, 'msg' => '离场时间不能空', 'data' => $packResult['errorMsg']]);
        }
        if (!isset($packResult['errorMsg']['parkCode']) || !isset($packResult['errorMsg']['ordersn'])) {
            return json(['statusCode' => 1002, 'msg' => '参数解析不对', 'data' => $packResult['errorMsg']]);
        }

        if (empty($packResult['errorMsg']['ordersn'])) {
            return json(['statusCode' => 1002, 'msg' => '订单号为空', 'data' => '']);
        }

        @file_put_contents(
            '../runtime/log/out/' . date('Ymd', time()) . '.txt',
            '离场数据：' . date('Y-m-d H:i:s', time()) . '---' . json_encode(self::$requestData) . '|车位编号：' . $packResult['errorMsg']['parkCode'] . "\n",
            FILE_APPEND);

        if (!Cache::store('redis')->setnx($packResult['errorMsg']['ordersn'], time())) {
            return json(['statusCode' => 1001, 'msg' => '', 'data' => '']);
        } else {
            Cache::store('redis')->expire($packResult['errorMsg']['ordersn'], 5);
        }

        $calculateCost = Loader::model("Billing", "logic");
        $calculateCost->sumMoney($packResult['errorMsg']);
        unset($calculateCost);
        return json(['statusCode' => 1001, 'msg' => '', 'data' => '']);
    }

}
