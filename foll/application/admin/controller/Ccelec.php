<?php

namespace app\admin\controller;

use app\admin\controller;
use think\Request;
use think\Db;
use PHPExcel;
use PHPExcel_IOFactory;

class Ccelec extends Auth
{
    public function datalist(Request $request)
    {
        if($request->isPost() || $request->isAjax())
        {

            $keyword = $request->get('search') ? $request->get('search') : '';
            $list = Db::name('customs_ccgoods_bol')->where('bill_num', 'like', '%'.$keyword.'%')->select();
            foreach ($list as $k => $v) {
                $list[$k]['username'] = Db::name('decl_user')->where('id',$v['uid'])->value('user_name');
                $list[$k]['create_time'] = date('Y-m-d H:i:s',$v['create_time']);
                $list[$k]['manage'] = '<button style="margin-right: 5px;" type="button" onclick="seeInfo('."'".$v['bill_num']."'".')" class="btn btn-primary btn-xs">查看信息</button>';
            }
            return json(['code' => 0, 'msg' => '', 'total' => count($list), 'rows' => $list]);
            
        }else{
            return view();
        }
    }

    public function getinfo(Request $request)
    {
        if($request->isPost() || $request->isAjax())
        {
            $bill_num = input('bill_num');
            $list = Db::name('customs_ccgoods_batch')->where('bill_num', $bill_num)->select();
            foreach ($list as $k => $v) {
                $list[$k]['create_time'] = date('Y-m-d H:i:s',$v['create_time']);
                $list[$k]['status'] = $v['status'] == 0 ? '不通过' : '通过';
                $list[$k]['manage'] = '<button style="margin-right: 5px;" type="button" onclick="seeGoods('."'".$v['batch_num']."'".')" class="btn btn-primary btn-xs">查看物品</button>';
            }
            return json(['code' => 0, 'msg' => '', 'total' => count($list), 'rows' => $list]);
        }else{
            $bill_num = input('bill_num');
            $this->assign('bill_num', $bill_num);
            return view();
        }
    }

    public function getGoods(Request $request)
    {
        if($request->isPost() || $request->isAjax())
        {
            $batch_num = input('batch_num');
            $list = Db::name('customs_ccgoods')->where('batch_num', $batch_num)->select();
            return json(['code' => 0, 'msg' => '', 'total' => count($list), 'rows' => $list]);
        }else{
            $batch_num = input('batch_num');
            $this->assign('batch_num', $batch_num);
            return view();
        }
    }
}