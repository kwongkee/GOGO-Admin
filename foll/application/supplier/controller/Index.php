<?php
/**
 * 供应商管理
 * 2022-05-24
 */
namespace app\supplier\controller;

use think\Controller;
use think\Db;
use think\Request;

class Index extends  Controller{

    //企业信息+对接信息
    public function config_index(Request $request){
        if ( request()->isPost() || request()->isAjax())
        {
            $dat = input();
            $basic_info = Db::name('enterprise_basicinfo')->where('member_id',0)->where('enterprise_id',trim($dat['basic_info']['enterprise_id']))->find();
            $res = Db::name('enterprise_basicinfo')->where('id',$basic_info['id'])->update($dat['basic_info']);
            if($res){
                return json(["code" => 1, "msg" => "企业信息确认成功！", "enterprise_id"=>$basic_info['id']]);
            }else{
                return json(["code" => 0, "msg" => "企业信息确认失败！", "enterprise_id"=>$basic_info['id']]);
            }
        }else{
            $job = Db::name('supplier_job_list')->order('id','desc')->select();
            return view('config_index',compact('job'));
        }
    }

}