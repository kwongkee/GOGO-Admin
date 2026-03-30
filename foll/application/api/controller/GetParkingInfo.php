<?php
namespace app\api\controller;
use think\Controller;
use think\Request;
use think\Response;
use think\Db;

class GetParkingInfo extends Controller{
    protected static $requestData;
    public function __construct ( Request $request )
    {
        parent::__construct($request);
        self::$requestData['user']=$request->header('user');
        self::$requestData['pwd'] =$request->header('pwd');
        self::$requestData['data']=$request->post('data');
    }
    public function getParkCode(Request $request,Response $response){
        $packResult = validationPacket(self::$requestData);
        if(!$packResult['error']){
            ResponseResult($response,$packResult['errorMsg']);
        }
        if(empty($packResult['errorMsg']['parkCode'])){
            ResponseResult($response,['statusCode' => 1002, 'msg' => '设备编号不能空', 'data' => ''],true);
        }
        $number = Db::name("parking_space")
            ->where('numbers',$packResult['errorMsg']['parkCode'])
            ->field('park_code')
            ->find();
        if(!empty($number)){
            ResponseResult($response,['statusCode'=>1001,'msg'=>'成功','data'=>$number['park_code']],true);
        }
        ResponseResult($response,['statusCode'=>1002,'msg'=>'失败','data'=>''],true);
    }
}