<?php

namespace app\index\controller;
use app\index\controller;
use think\Db;
use think\Request;
use think\Session;
use think\Cookie;

class Index extends CommonController
{
    public function index()
    {
        $roleAuthority = Db::name("foll_business_rwaccess")->where("role_id",Session::get("UserResutlt")['role'])->field(['read','write'])->find();
        $UserResutlt    = Session::get("UserResutlt");
        $UserResutlt['read']=$roleAuthority['read'];
        $UserResutlt['write'] =$roleAuthority['write'];
        Session::set("UserResutlt",$UserResutlt);
        return view("index/index",['UserResutlt'=>$UserResutlt]);
    }
    
}