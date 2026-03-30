<?php

namespace app\lib\exception\comm_exception;

use app\lib\exception\BaseException;
use app\lib\exception\ExceptionErrorCode;
use app\lib\restful_api\RestfulApiCode;

class CacheException extends BaseException
{
    public $code = RestfulApiCode::INVALID_REQUEST;
    public $msg = "缓存数据失败";
    public $errorCode = ExceptionErrorCode::CACHE_ERROR;
}