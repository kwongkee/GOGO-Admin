<?php

namespace app\admin\model;

use app\admin\model\MerchantModel;
use think\Db;


/**
 * 创建推广系统商户
 * Class TuiUserModel
 * @package app\admin\model
 */
class TuiUserModel extends MerchantModel
{

    public function createUser($data=null)
    {
        $isUser = Db::name('customs_agents_admin')->where('uphone',$data['mobile'])->find();
        if ($isUser){
            return false;
        }
        Db::name('customs_agents_admin')->insert([
            'tid'=>$data['unique_id'],
            'uname'=>$data['user_name'],
            'uphone'=>$data['mobile'],
            'status'=>1,
            'g_id'=>1,
            'pid'=>0,
            'c_time'=>time()
        ]);
        return true;
    }
}