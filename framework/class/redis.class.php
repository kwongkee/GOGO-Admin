<?php

if (!(defined('IN_IA'))) {
    exit('Access Denied');
}
class Rediss
{
    protected static $redis=null;
    public static function getInstance(){
        if(null===self::$redis){
            try{
                self::$redis=new Redis();
                self::$redis->connect('127.0.0.1',6379);
            }catch (Exception $e){
                exit($e->getMessage());
            }
        }
        return self::$redis;
    }
}