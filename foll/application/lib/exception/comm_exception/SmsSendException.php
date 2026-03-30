<?php

namespace app\lib\exception\comm_exception;

use app\lib\exception\BaseException;
use app\lib\exception\ExceptionErrorCode;
use app\lib\restful_api\RestfulApiCode;

class SmsSendException extends BaseException
{
    public $code = RestfulApiCode::INVALID_REQUEST;
    public $msg = "请求短信发送失败";
    public $errorCode = ExceptionErrorCode::SMS_SEND_ERROR;
}