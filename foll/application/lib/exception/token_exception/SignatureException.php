<?php

namespace app\lib\exception\token_exception;

use app\lib\exception\BaseException;
use app\lib\exception\ExceptionErrorCode;
use app\lib\restful_api\RestfulApiCode;

class SignatureException extends BaseException
{
    public $code = RestfulApiCode::Unauthorized;
    public $msg = '签名已过期或无效签名';
    public $errorCode = ExceptionErrorCode::SIGNATURE_ERROR;
}