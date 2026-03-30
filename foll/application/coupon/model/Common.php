<?php

namespace app\coupon\model;

use think\Model;
use think\Db;
class Common extends Model
{
    /**
     * 验证是否已存在账户
     * @param $account
     * @return mixed
     */
    public function check_account($account)
    {
        return Db::name('foll_seller_member')->where('busin_login_accout', $account)->find();
    }

    /**
     * 获取所有公众号
     * @return mixed
     */
    public function getAllWechatsAccount(){
        return Db::name('account_wechats')->field(['uniacid','name'])->select();
    }
}
