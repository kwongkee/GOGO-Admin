<?php

namespace app\mobile\controller;

// use app\mobile\controller;
use think\Session;
use think\Controller;
use think\Db;
class IotAuth extends Controller
{
    public function __construct()
    {
        parent::__construct();
        $u = cookie(md5('iot_user'));
        if ($u == '') {
            if (!Session::has("iot_user")) {
                return Redirects("iot/login");
            }
        }else{
            $user = Db::name('iot_users')->where('unique_id',$u)->find();
            if (!$user){
                return Redirects("iot/login");
            }
            session('iot_user',$user);
        }

    }

    //空操作
    public function _empty()
    {
        abort(404);
    }
}
