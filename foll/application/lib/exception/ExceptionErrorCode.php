<?php

namespace app\lib\exception;

/**
 * Class ExceptionErrorCode
 * @package app\lib\exception
 * 异常错误码
 * 错误码范围  -10000到-99999
 */
class ExceptionErrorCode
{
    const SUCCESS                   = 0;

    const SERVER_ERROR              = -9999;    // 服务器内部出错
    const MISS_URL_ERROR            = -10000;   // URL路径出错
    const TOKEN_ERROR               = -10001;   // Token已过期或无效Token
    const FORBIDDEN_ERROR           = -10002;   // 权限不够
    const WXBIZDATACRYPT_ERROR      = -41003;   //微信解密失败

    const EMPTY_RESULT_ERROR        = -10003;   // 查询不到相关数据
    const PARAMETER_ERROR           = -10004;   // 参数出错
    const UPLOAD_ERROR              = -10005;
    const USER_LOGIN_ERROR          = -10006;
    const USER_LOGOUT_ERROR         = -10007;
    const CACHE_ERROR               = -10008;

    const SIGNATURE_ERROR           = -10009;

    const SMS_SEND_ERROR            = -10011;
    const SMS_CHECK_ERROR           = -10010;

    const MEMBER_LOGIN_ERROR                = -20001;
    const MEMBER_LOGOUT_ERROR               = -20002;
    const MEMBER_CHANGE_PASSWORD_ERROR      = -20003;
    const MEMBER_FIND_PASSWORD_ERROR        = -20004;

    const PRODUCT_CREATE_ERROR          = -30001;
    const PRODUCT_COLLECT_ERROR         = -30001;

    const MARKET_BANNER_CREATE_ERROR    = -30002;
    const MARKET_BANNER_UPDATE_ERROR    = -30003;
    const MARKET_BANNER_DELETE_ERROR    = -30004;

    const CART_ERROR                    = -30005;


    const ORDER_CHECK_ERROR             = -40001;   // 检验订单出错
    const ORDER_CREATE_ERROR            = -40002;   // 创建订单出错
    const ORDER_PAY_ERROR               = -40003;   // 支付订单出错
    const ORDER_SURE_ERROR              = -40004;   // 确认订单出错
    const ORDER_CANCEL_ERROR            = -40005;   // 取消订单出错
    const ORDER_LOGISTICS_ERROR         = -40006;

    const WECHAT_PAY_ERROR              = -50005;
    const WECHAT_USER_ERROR             = -50006;
    const USER_XCX_QRCODE_ERROR         = -50007;

    const COMMENT_CREATE_ERROR          = -60001;


    const VIDEO_COURSE_CREATE_ERROR    = -30006;
    const QUESTIONS_PAPER_CREATE_ERROR = -30007;

}