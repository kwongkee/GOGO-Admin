<?php

namespace app\lib\exception\token_exception;

use app\lib\exception\BaseException;
use app\lib\exception\ExceptionErrorCode;
use app\lib\restful_api\RestfulApiCode;

class ForbiddenException extends BaseException
{
    public $code = RestfulApiCode::Forbidden;
    public $msg = "权限不够";
    public $errorCode = ExceptionErrorCode::FORBIDDEN_ERROR;
}