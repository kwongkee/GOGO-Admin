<?php

namespace app\coupon\model;

use think\Model;
use think\Db;

/**
 * 不再用
 * Class Login
 * @package app\coupon\model
 */
class Login extends Model
{
    
    
    /**
     * 获取用户
     * @param $account
     * @return array|false|\PDOStatement|string|Model
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function GetUserByAccount($account)
    {
        return Db::name('foll_seller_member')->where('busin_login_accout', $account)->find();
    }
}