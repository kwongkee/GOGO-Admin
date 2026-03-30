<?php

namespace app\lib\service;

use app\common\service\BaseToken;
use app\lib\exception\comm_exception\CacheException;
use app\lib\exception\token_exception\TokenException;
use think\Cache;

class Tokens extends BaseToken
{
    public static function saveTokenToCache($key, $token)
    {
        $expire_in = 7200;
        $key_prefix = 'gogo_uid_';

        $result = cache($key_prefix.$key, $token, $expire_in);

        if (!$result)
        {
            throw new CacheException();
        }

        return true;
    }

    public static function clearTokenFromCache($key)
    {
        $key_prefix = 'gogo_uid_';
        Cache::rm($key_prefix.$key);
    }

    public static function getTokenFromCache($key)
    {
        $key_prefix = 'gogo_uid_';
        $token = Cache::get($key_prefix.$key);
        if (!$token)
        {
            throw new TokenException();
        }
        return $token;
    }
}