<?php

namespace app\lib\exception\questions_exception;

use app\lib\exception\BaseException;
use app\lib\exception\ExceptionErrorCode;
use app\lib\restful_api\RestfulApiCode;

class QuestionsPaperCreateException extends BaseException
{
    public $code = RestfulApiCode::INVALID_REQUEST;
    public $msg = '创建考试专题出错';
    public $errorCode = ExceptionErrorCode::QUESTIONS_PAPER_CREATE_ERROR;
}