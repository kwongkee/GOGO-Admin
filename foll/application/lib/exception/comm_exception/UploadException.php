<?php

namespace app\lib\exception\comm_exception;

use app\lib\exception\BaseException;
use app\lib\exception\ExceptionErrorCode;
use app\lib\restful_api\RestfulApiCode;

class UploadException extends BaseException
{
    public $code = RestfulApiCode::INVALID_REQUEST;
    public $msg = "上传数据失败";
    public $errorCode = ExceptionErrorCode::UPLOAD_ERROR;
}