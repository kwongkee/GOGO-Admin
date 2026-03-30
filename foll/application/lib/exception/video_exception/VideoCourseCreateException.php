<?php

namespace app\lib\exception\video_exception;

use app\lib\exception\BaseException;
use app\lib\exception\ExceptionErrorCode;
use app\lib\restful_api\RestfulApiCode;

class VideoCourseCreateException extends BaseException
{
    public $code = RestfulApiCode::INVALID_REQUEST;
    public $msg = '创建视频教程出错';
    public $errorCode = ExceptionErrorCode::VIDEO_COURSE_CREATE_ERROR;
}