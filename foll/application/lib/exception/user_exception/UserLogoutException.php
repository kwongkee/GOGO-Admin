<?php

namespace app\lib\exception\user_exception;

use app\lib\exception\BaseException;
use app\lib\exception\ExceptionErrorCode;
use app\lib\restful_api\RestfulApiCode;

class UserLogoutException extends BaseException
{
    public $code = RestfulApiCode::INVALID_REQUEST;
    public $msg = '用户登出出错';
    public $errorCode = ExceptionErrorCode::USER_LOGOUT_ERROR;
}