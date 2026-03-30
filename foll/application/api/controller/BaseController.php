<?php

namespace app\api\controller;


use think\Request;
use think\controller\Rest;
use think\Des3;

class BaseController extends Rest
{
    public $data = null;

    /**
     * 初始化解密
     * BaseController constructor.
     */
    public function __construct (Request $request) {
        $res['user']=$request->header('user');
        $res['pwd'] =$request->header('pwd');
        $res['data']=$request->post('data');
        @file_put_contents(
            '../runtime/log/out/'.date('Ymd',time()).'.txt',
            '原始数据：'.date('Y-m-d H:i:s',time()).'|'.$res['data']."\n",
            FILE_APPEND);
        $this->data = validationPacket($res);
    }

    /**
     * 空操作
     * @return mixed
     */
    public function __empty(){
        return json(['statusCode'=>404,'msg'=>'错误操作','data'=>'']);
    }
}
