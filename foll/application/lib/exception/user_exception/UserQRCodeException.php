<?php

namespace app\lib\exception\user_exception;

use app\lib\exception\BaseException;
use app\lib\exception\ExceptionErrorCode;
use app\lib\restful_api\RestfulApiCode;

class UserQRCodeException extends BaseException
{
    public $code = RestfulApiCode::INVALID_REQUEST;
    public $msg = '获取用户二维码出错';
    public $errorCode = ExceptionErrorCode::USER_XCX_QRCODE_ERROR;
}