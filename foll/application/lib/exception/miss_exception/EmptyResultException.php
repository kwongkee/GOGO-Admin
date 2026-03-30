<?php

namespace app\lib\exception\miss_exception;

use app\lib\exception\BaseException;
use app\lib\exception\ExceptionErrorCode;
use app\lib\restful_api\RestfulApiCode;

/**\
 * Class EmptyResultException
 * @package app\lib\exception\miss_exception
 * 查询不到相关数据异常错误
 */
class EmptyResultException extends BaseException
{
    public $code = RestfulApiCode::NOT_FOUND;
    public $msg = "查询不到相关数据";
    public $errorCode = ExceptionErrorCode::EMPTY_RESULT_ERROR;
}