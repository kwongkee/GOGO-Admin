<?php

namespace app\lib\exception\member_exception;

use app\lib\exception\BaseException;
use app\lib\exception\ExceptionErrorCode;
use app\lib\restful_api\RestfulApiCode;

class MemberChangePasswordException extends BaseException
{
    public $code = RestfulApiCode::INVALID_REQUEST;
    public $msg = '修改密码出错';
    public $errorCode = ExceptionErrorCode::MEMBER_CHANGE_PASSWORD_ERROR;
}