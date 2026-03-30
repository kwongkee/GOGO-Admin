<?php

namespace app\api\controller;
use think\Controller;
use think\Exception;
use think\Request;
use think\Response;
use think\Loader;
use think\Queue;
use think\Db;
use think\Cache;

class Leaving2 extends Controller
{
    protected static $requestData;
    /*
     * 初始化接受数据
     */
    public function __construct ( Request $request)
    {
        parent::__construct($request);
        self::$requestData['user']=$request->header('user');
        self::$requestData['pwd'] =$request->header('pwd');
        self::$requestData['data']=$request->post('data');
    }

    //接受离场信息接口
    public function acceptLeavingTime2(Request $request,Response $response)
    {
        $packResult = validationPacket(self::$requestData);
        if(!$packResult['error']){
            ResponseResult($response,$packResult['errorMsg']);
        }
        if(empty($packResult['errorMsg']['etime'])||!isset($packResult['errorMsg']['etime'])){
            return json(['statusCode' => 1002, 'msg' => '参数错误', 'data' => '']);
        }
        if(!isset($packResult['errorMsg']['parkCode'])||!isset($packResult['errorMsg']['ordersn'])){
            return json(['statusCode' => 1002, 'msg' => '参数解析错误', 'data' => '']);
        }

        // 2019-10-31
        @file_put_contents('../runtime/log/out/lic'.date('Ymd',time()).'.txt','接收离场处理计算：'.json_encode($packResult)."\n",FILE_APPEND);

        // Db::name('parking_queue')->insert(['queue'=>'leave','payload'=>json_encode($packResult['errorMsg']),'create_time'=>time()]);
        $calculateCost = Loader::model("Billing2","logic");
        return json(['statusCode'=>1001,'msg'=>$calculateCost->sumMoney($packResult['errorMsg']),'data'=>'']);
    }

}