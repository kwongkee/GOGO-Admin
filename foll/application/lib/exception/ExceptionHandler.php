<?php

namespace app\lib\exception;

use app\lib\restful_api\RestfulApiCode;
use app\lib\tools\ResultHandler;
use Exception;
use think\exception\Handle;
use think\facade\Request;

class ExceptionHandler extends Handle
{
    private $code;
    private $msg;
    private $errorCode;

    public function render(Exception $exception)
    {
        if ($exception instanceof BaseException)
        {
            /**
             * 如果是自定义异常，则控制http状态码，不需要记录日志
             * 因为这些通常是因为客户端传递参数错误或者是用户请求造成的异常
             * 不应当记录日志
             */
            $this->code         = $exception->code;
            $this->msg          = $exception->msg;
            $this->errorCode    = $exception->errorCode;
        }
        else
        {
            // 如果是服务器未处理的异常，将http状态码设置为500，并记录日志
            if(config('app_debug'))
            {
                // 调试状态下需要显示TP默认的异常页面，因为TP的默认页面
                // 很容易看出问题
                return parent::render($exception);
            }

            $this->code = RestfulApiCode::INVALID_REQUEST;
            // $this->msg = '服务器内部出错';
            $this->msg = $exception->getMessage();
            $this->errorCode = ExceptionErrorCode::SERVER_ERROR;
            //$this->recordErrorLog($e);
        }

        return ResultHandler::returnJson($this->msg, null, $this->errorCode, $this->code);
    }

    /**
     * 将异常写入日志
     */
    private function recordErrorLog(Exception $e)
    {
        Log::init([
            'type'  =>  'File',
            'path'  =>  LOG_PATH,
            'level' => ['error']
        ]);
        Log::record($e->getMessage(),'error');
    }
}