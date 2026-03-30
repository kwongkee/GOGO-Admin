<?php

namespace app\lib\tools;

use think\Request;

/**
 * 返回数据相关处理类
 */
class ResultHandler
{
    /**
     * @param               $message        提示信息
     * @param array|null    $data           返回数据
     * @param int           $errorCode      错误码
     * @param int           $statusCode     HTTP状态码
     * @return \think\response\Json
     */
    public static function returnJson($message, $data=null, $errorCode=0, $statusCode=200)
    {
        $json = array(
            'error_code'    => $errorCode,
            'msg'           => $message,
            'data'          => $data,
            'request_url'   => Request::instance()->url()
        );
        return json($json, $statusCode);
    }
}