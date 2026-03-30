<?php
namespace app\mobile\controller;
use app\mobile\controller;


class IotMyInfo extends IotAuth
{
    public function index()
    {
        $user = session('iot_user');
        return view('iot/user/index',['user'=>$user]);
    }

    public function logout()
    {
        session('iot_user',null);
        cookie(md5('iot_user'), null);
        Redirects('iot');
    }
}
