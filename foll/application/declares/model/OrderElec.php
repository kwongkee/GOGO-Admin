<?php
namespace app\declares\model;

use think\Model;
use think\Db;

class OrderElec extends Model
{
    
    /**
     * 获取批号
     * @param $uid
     * @return mixed
     */
    public function fetchBatchList($uid)
    {
        $data = Db::name('foll_elec_count')->where('uid',$uid)->field('batch_num')->order('id','desc')->select();
        return $data;
    }
}