<?php
namespace app\admin\controller;

use think\Db;
use think\Request;
use think\Response;
use Excel5;
use PHPExcel_IOFactory;

class Enterprise extends Auth
{
    #订单列表
    public function elist(Request $request)
    {
        $dat = input();
        if (request()->isPost() || request()->isAjax()) {
            // 排序
            $order = input('sort') . ' ' . input('order');
            // 分页
            $limit = input('offset') . ',' . input('limit');

            $map = array();
            $list = Db::name('customs_enterprise_info')->order($order)->limit($limit)->select();
            $total = Db::name('customs_enterprise_info')->where($map)->count();
            return json(["status" => 0, "message" => "", "total" => $total, "rows" => $list]);
        } else {
            return view('enterprise/elist');
        }
    }
    
    public function add_elist(Request $request)
    {
        $dat = input();
        if (request()->isPost() || request()->isAjax()) {
            $res = Db::name('customs_enterprise_info')->insert([
                'enterprise_name'=>trim($dat['enterprise_name']),
                'legal_name'=>trim($dat['legal_name']),
                'orgNo'=>trim($dat['orgNo']),
            ]);
            
            return json(['code'=>0,'msg'=>'操作成功!']);
        } else {
            return view('enterprise/add_elist');
        }
    }
}