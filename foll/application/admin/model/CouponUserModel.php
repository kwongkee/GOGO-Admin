<?php


namespace app\admin\model;

use app\admin\model\MerchantModel;
use think\Db;

/**
 * 优惠卷系统商户
 * Class CouponUserModel
 * @package app\admin\model
 */
class CouponUserModel extends MerchantModel
{
    public function createUser($data = null)
    {
        $num = mt_rand(1111, 9999).mt_rand(1111, 9999);
        Db::name('foll_seller_member')->insert([
            'tid'                =>$data['unique_id'],
            'user_name'          => $data['user_name'],
            'busin_name'         => $data['company_name'],
            'busin_addr'         => $data['address'],
            'busin_url'          => "",
            'busin_logo'         => "",
            'busin_login_accout' => $data['mobile'],
            'create_time'        => time(),
            'is_public_account'  => 0,
            'busin_num'          => $num,
            'busin_tel'          => $data['company_tel'],
            'uniacid'            => $data['uniacid'],
        ]);
        Db::name('payments_config')->insert([
            'uniacId' => $num,
            'TgPay'   => json_encode(['mach_id' => '101540254006', 'mach_pwd' => 'f8ee27742a68418da52de4fca59b999e']),
        ]);
    }
}