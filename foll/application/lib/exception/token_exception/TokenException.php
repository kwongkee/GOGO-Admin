<?php

namespace app\lib\exception\token_exception;

use app\lib\exception\BaseException;
use app\lib\exception\ExceptionErrorCode;
use app\lib\restful_api\RestfulApiCode;

class TokenException extends BaseException
{
    public $code = RestfulApiCode::Unauthorized;
    public $msg = 'Token已过期或无效Token';
    public $errorCode = ExceptionErrorCode::TOKEN_ERROR;
}