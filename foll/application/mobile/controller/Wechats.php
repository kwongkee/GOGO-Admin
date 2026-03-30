<?php 

namespace app\mobile\controller;
use think\Controller;
//use think\Wechat;
use think\Request;
use think\Loader;
use EasyWeChat\Foundation\Application;

class Wechats
{
   protected $_W=array();
   
   
   public function index(Request $request) 
   {
//         parent::__construct();
       $options = [
           'debug'     => true,
           'app_id'    => 'wx01af4897eca4527e',
           'secret'    => '497ccae978cb1bca589d587d60da8f8d',
           'token'     => 'j0uda0pmx3ipzgjhyuycq5g3ljpk5ppg',
           'log' => [
               'level' => 'debug',
               'file'  => '/tmp/easywechat.log',
           ],
       ];

       $app = new Application($options);
       $oauth=$app->oauth;
           
    }
}