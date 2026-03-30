<?php

namespace app\lib\restful_api;

/**
 *
 * GET     SELECT ：从服务器获取资源。
 * POST    CREATE ：在服务器新建资源。
 * PUT     UPDATE ：在服务器更新资源。
 * DELETE  DELETE ：从服务器删除资源。
 *
 */

/**
 * Class RestfulApiCode
 * @package app\lib\restful_api
 */
class RestfulApiCode
{
    const OK                    = 200;  // [GET]：服务器成功返回用户请求的数据，该操作是幂等的（Idempotent）
    const CREATED               = 201;  // [POST/PUT/PATCH]：用户新建或修改数据成功。
    const Accepted              = 202;  // [*]：表示一个请求已经进入后台排队（异步任务）
    const NO_CONTENT            = 204;  // [DELETE]：用户删除数据成功
    const INVALID_REQUEST       = 400;  // [POST/PUT/PATCH]：用户发出的请求有错误，服务器没有进行新建或修改数据的操作，该操作是幂等的
    const Unauthorized          = 401;  // [*]：表示用户没有权限（令牌、用户名、密码错误）
    const Forbidden             = 403;  // [*] 表示用户得到授权（与401错误相对），但是访问是被禁止的
    const NOT_FOUND             = 404;  // [*]：用户发出的请求针对的是不存在的记录
    const Not_Acceptable        = 406;  // [GET]：用户请求的的格式不正确（比如用户请求JSON格式，但是只有XML格式）
    const INTERNAL_SERVER_ERROR = 500;  // [*]：服务器发生错误，用户将无法判断发出的请求是否成功
}
