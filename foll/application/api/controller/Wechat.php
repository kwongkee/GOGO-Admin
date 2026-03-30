<?php

namespace app\api\controller;
use app\api\logic\WechatLoigc;
use think\Request;
use think\Loader;
use think\Controller;
use think\Log;

class Wechat extends Controller
{

    /**
     * @param Request $request
     * @return mixed
     * 发送模板
     */
    public function wechatTemplate(Request $request)
    {
        @file_put_contents('../runtime/log/wx/wxtemplate-'.date('Ym',time()).'.log',json_encode($request->post())."\n",FILE_APPEND);
        $weLogic = Loader::model('WechatLoigc','logic');
//        $weLogic = new WechatLoigc();
        $body = $weLogic->sendTplNotice($request->post());
        @file_put_contents('../runtime/log/wx/wxtemplate-'.date('Ym',time()).'.log',$body."\n",FILE_APPEND);
        return json(['code'=>200,'message'=>$body]);
    }


    /**
     * 关联车票查找违规订单发送通知
     * @param Request $request
     * @return mixed
     */
    public function sendVioOrederTempl(Request $request){
        @file_put_contents('../runtime/log/wx/vio_order.log','绑定违规订单|数据:'.json_encode($request->get())."\n",FILE_APPEND);
        $wxLogic = model('WechatLoigc','logic');
        try{
            $wxLogic->searOrderUserByCard($request->get());
        }catch (\Exception $exception){
            Log::write(json_encode([
                'number'=>$exception->getLine(),
                'errMsg'=>$exception->getMessage(),
                'errCode'=>$exception->getCode(),
                'file'      =>$exception->getFile()
            ]));
            return json(['code'=>200,'message'=>$exception->getMessage()]);
        }
        return json(['code'=>200,'message'=>'完成']);
    }
}
