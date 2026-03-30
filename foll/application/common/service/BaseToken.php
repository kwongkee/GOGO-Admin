<?php

namespace app\common\service;

class BaseToken
{
    public static function generateToken()
    {
        $length = 32;
        $randChar = rand(pow(10,($length-1)), pow(10,$length)-1);
        $timestamp = $_SERVER['REQUEST_TIME_FLOAT'];
        $tokenSalt = '9k*hz1L8TPVgKLN#3n!RTf*hj4fB8qkBZqs8h3FHgDapJWkrd$';
        return md5($randChar . $timestamp . $tokenSalt);
    }
}