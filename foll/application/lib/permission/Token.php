<?php

namespace app\lib\permission;

use app\lib\exception\token_exception\TokenException;
use com\xhzer\tools\Random;
use think\facade\Request;

class Token
{
    // 生成令牌
    public static function generateToken($tokenSalt='I&TC{pft>L,C`wFQ>&#ROW>k{Kxlt1>ryW(>r<#R')
    {
        $randChar   = Random::alpha(32);
        $timestamp  = $_SERVER['REQUEST_TIME_FLOAT'];
        return md5($randChar . $timestamp . $tokenSalt);
    }

    public static function getCurrentTokenData($key)
    {
        $token = Request::header('token');
        $data = Cache::get($token);
        if (!$data)
        {
            throw new TokenException();
        }
        else
        {
            if(!is_array($data))
            {
                $data = json_decode($data, true);
            }
            if (array_key_exists($key, $data))
            {
                return $data[$key];
            }
            else
            {
                throw new Exception('尝试获取的Token变量并不存在');
            }
        }
    }
}