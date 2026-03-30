<?php

namespace app\coupon\model;

use think\Model;
use think\Db;


/**
 * 不再用
 * Class Register
 * @package app\coupon\model
 */
class Register extends Model
{
    /**
     * 保存注册信息
     * @param null $data
     * @throws \Exception
     */
    public function register_save($data = null)
    {
        $num  = mt_rand(1111, 9999) . mt_rand(1111, 9999);
        Db::startTrans();
        try {
            Db::name('foll_seller_member')->insert([
                'user_name' => $data['userName'],
                'busin_name' => $data['busin_name'],
                'busin_addr' => $data['busin_addr'],
                'busin_url' => $data['busin_url'],
                'busin_logo' => isset($data['busin_logo']) ? $data['busin_logo'] : null,
                'busin_login_accout' => $data['busin_login_accout'],
                'create_time' => time(),
                'is_public_account' => $data['is_public_account'],
                'busin_num' => $num,
                'busin_tel' =>$data['busin_tel']
            ]);
            Db::name('payments_config')->insert([
                'uniacId'   => $num,
                'TgPay'     => json_encode(['mach_id'=>'101540254006','mach_pwd'=>'f8ee27742a68418da52de4fca59b999e'])
            ]);
            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
            throw new \Exception($e->getMessage());
        }
    }


}
