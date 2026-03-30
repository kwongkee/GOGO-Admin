<?php
namespace app\coupon\controller;

use app\coupon\controller;
use think\Request;
use think\Session;

class Index extends Base
{
    public function __construct() {
        parent::__construct();
    }

    public function index()
    {
        return view('index/index',['user'=>Session('business.user_name')]);
    }
}
