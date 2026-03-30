<?php

namespace app\admin\controller;

use app\admin\controller;
use think\Db;
use think\Request;


class Trademanage extends Auth
{
    // 平台交易
    public function platform_list()
    {
        if ( request()->isPost() || request()->isAjax())
        {

        }else{
            return view();
        }
    }

    // 其他交易
    public function other_list()
    {
        if ( request()->isPost() || request()->isAjax())
        {

        }else{
            return view();
        }
    }
}