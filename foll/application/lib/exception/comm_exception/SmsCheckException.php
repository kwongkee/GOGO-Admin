<?php

namespace app\lib\exception\comm_exception;

use app\lib\exception\BaseException;
use app\lib\exception\ExceptionErrorCode;
use app\lib\restful_api\RestfulApiCode;

class SmsCheckException extends BaseException
{
    public $code = RestfulApiCode::INVALID_REQUEST;
    public $msg = "校验短信验证码失败";
    public $errorCode = ExceptionErrorCode::SMS_CHECK_ERROR;
}