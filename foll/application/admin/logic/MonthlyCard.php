<?php

namespace app\admin\logic;
use think\Model;
use think\Db;

class MonthlyCard extends Model
{

    public $param = [
        'type' =>'Layui',
        'query'=>['s'=>'admin/monthly_index'],
        'var_page'=>'page',
        'newstyle'=>true
    ];

    public function getCheckList()
    {
        $total = $this->getCount();
        return Db::name('parking_month_type')
            ->alias('a')
            ->join('account_wechats b','a.uniacid=b.uniacid')
            ->field(['a.*','b.name'])
            ->where('a.is_check',0)
            ->order('a.id','desc')
            ->paginate(15,$total,$this->param);
    }

    public function getCount()
    {
        return Db::name('parking_month_type')->where('is_check',0)->count('id');
    }


    public function updateCheckSuatus($id,$status)
    {
        Db::name('parking_month_type')->where('id',$id)->update(['is_check'=>$status]);
    }
}