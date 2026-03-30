<?php

namespace app\lib\exception\wechat_exception;

use app\lib\exception\BaseException;
use app\lib\exception\ExceptionErrorCode;
use app\lib\restful_api\RestfulApiCode;

class WechatUserException extends BaseException
{
    public $code = RestfulApiCode::INVALID_REQUEST;
    public $msg = '获取微信用户信息出错';
    public $errorCode = ExceptionErrorCode::WECHAT_USER_ERROR;
}