<?php


/**
 * 接收违规信息
 */
namespace app\api\controller;
use app\api\controller;
use think\Request;
use think\Log;
class Violations extends BaseController
{

    /**
     * 违法订单
     */
    public function order()
    {

        if (!$this->data['error']){
            return $this->data['errorMsg'];
        }
        @file_put_contents('../runtime/log/violatOrder_'.date('Ym',time()).'.log',json_encode($this->data['errorMsg'])."\n",FILE_APPEND);
        try{
            $orderLogic = model('OrderLogic','logic');
            $orderLogic::grenerateVoltOrder($this->data['errorMsg']);
        }catch (\Exception $exception){
            Log::write(json_encode([
                'number'=>$exception->getLine(),
                'errMsg'=>$exception->getMessage(),
                'errCode'=>$exception->getCode(),
                'file'      =>$exception->getFile()
            ]));
            return json(['statusCode'=>1002,'msg'=>$exception->getMessage(),'data'=>'']);
        }
        return json(['statusCode'=>1001,'msg'=>'完成','data'=>'']);
    }
}
