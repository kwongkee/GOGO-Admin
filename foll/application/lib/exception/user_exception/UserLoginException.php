<?php

namespace app\lib\exception\user_exception;

use app\lib\exception\BaseException;
use app\lib\exception\ExceptionErrorCode;
use app\lib\restful_api\RestfulApiCode;

class UserLoginException extends BaseException
{
    public $code = RestfulApiCode::INVALID_REQUEST;
    public $msg = '账号或密码错误';
    public $errorCode = ExceptionErrorCode::USER_LOGIN_ERROR;
}