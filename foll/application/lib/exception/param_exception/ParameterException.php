<?php

namespace app\lib\exception\param_exception;

use app\lib\exception\BaseException;
use app\lib\exception\ExceptionErrorCode;
use app\lib\restful_api\RestfulApiCode;

/**
 * Class ParameterException
 * 通用参数类异常错误
 */
class ParameterException extends BaseException
{
    public $code = RestfulApiCode::INVALID_REQUEST;
    public $msg = "invalid parameters";
    public $errorCode = ExceptionErrorCode::PARAMETER_ERROR;
}