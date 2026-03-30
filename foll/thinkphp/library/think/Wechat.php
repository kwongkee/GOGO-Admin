<?php 

// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: zheng
// +----------------------------------------------------------------------

namespace think;
use think\Curl;

class Wechat 
{
    protected static $init;
    protected static $option=array();
    protected static $data=array();
    
    public static function config(array $config)
    {
        self::$option=$config;
        if(is_null(self::$init))self::$init=new self();
        return self::$init;
    }
    
    public function get_openid() 
    {
        self::$data['fans']="12321";
        return self::$init;
    }
    
    
    public function data()
    {
        return self::$data;
    }
}