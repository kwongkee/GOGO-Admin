<?php
namespace app\mobile\controller;
// use app\mobile\controller;
use think\Session;
use think\Controller;

class Auth extends Controller
{
    public function __construct()
    {
        parent::__construct();
        if(!Session::has("mobile")){
           return Redirects("mobile/login");
        }
    }
}
