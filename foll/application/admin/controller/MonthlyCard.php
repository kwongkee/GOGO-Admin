<?php

namespace app\admin\controller;
use app\admin\controller;
use think\Request;
use think\Loader;

class MonthlyCard extends Auth
{
    public function monthlyIndex()
    {
        $list = Loader::model('MonthlyCard','logic')->getCheckList();
        return view('monthly/month_index',['title'=>'商家月卡审核','page'=>$list->render(),'list'=>$list->toArray()]);
    }

    public function isMonthCheck(Request $request)
    {
        $res = $request->get();
        if(empty($res['id'])&&empty($res['status'])){
            $this->error('错误',Url('admin/monthly_index'));
        }
        Loader::model('MonthlyCard','logic')->updateCheckSuatus($res['id'],$res['status']);
        $this->success('完成',Url('admin/monthly_index'));
    }
}