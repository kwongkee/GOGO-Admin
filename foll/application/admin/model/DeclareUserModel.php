<?php

namespace app\admin\model;

use app\admin\model\MerchantModel;
use think\Db;

/**
 * 申报系统用户model
 * Class DeclareUserModel
 * @package app\admin\model
 */
class DeclareUserModel extends MerchantModel
{

    /**
     * 创建申报系统用户
     * @param null $data
     * @return bool
     */
    public function createUser($data = null)
    {
        $isUser = Db::name('decl_user')->where('user_tel', $data['mobile'])->field('id')->find();
        if ($isUser) {
            return false;
        }

        $uuid = $this->addSupplier($data['company_name'], $data['uniacid'], $data['openid'], $data['mobile']);
        $uid = Db::name('decl_user')->insertGetId([
            'tid' => $data['unique_id'],
            'user_name' => $data['user_name'],
            'user_tel' => $data['mobile'],
            'user_email' => $data['user_email'],
            'user_password' => $data['password'],
            'openid' => $data['openid'],
            'uniacid' => $data['uniacid'],
            'plat_id' => 1,
            'parent_id' => 0,
            'created_at' => date("Y-m-d H:i:s", time()),
            'user_num' => md5($data['mobile']),
            'buss_id' => $data['uniacid'],
            'company_name' => $data['company_name'],
            'company_num' => "C011000000332982",
            'supplier' => $uuid,
            'address'=>$data['address'],
            'company_tel'=>$data['company_tel'],
            'enterprise_id'=>$data['enterprise_id']
        ]);

        // 写入费率表
        Db::name('customs_rates')->insert([
            'uid'=>$uid,
            'verfee'=>'1.00',
            'payfee'=>'0.006',
            'orderfee'=>'0.5',
            'c_time'=>time()
        ]);

        // 微擎商户
        Db::name('merchat_users')->insert([
            'uid' => $uuid,
            'phone' => $data['mobile'],
            'email' => $data['user_email'],
            'enterName' => $data['company_name'],
            'legalRepre' => $data['user_name'],
            'legalRepre' => $data['user_name'],
            'appStatus' => 1,
            'typeCom' => 'company',
            'cusTypes' => 'Territory',
            'openid' => $data['openid'],
            'c_time' => time(),
            'roleid' => 5
        ]);

        if( $data['openid'] != '' )
        {
            // 发送微信通知
        }

        return true;
    }

    /**
     *
     * 添加商城商户
     * @param $name
     * @param $uniacid
     * @return mixed
     */
    public function addSupplier($name, $uniacid, $openid, $mobile)
    {
        $uid = Db::name('users')->insertGetId([
            'groupid' => 0,
            'username' => $name,
            'password' => "931f68875a20dd0d6d6a91711b856c7b9f1263a0",
            'salt' => "fF42Q83f",
            'type' => 0,
            'status' => 2,
            'joindate' => time(),
            'joinip' => '127.0.0.1',
            'lastvisit' => time(),
            'lastip' => '127.0.0.1',
            'remark' => '',
            'starttime' => 0,
            'endtime' => 0,
        ]);
        Db::name('sz_yi_perm_user')->insert([
            'uniacid' => $uniacid,
            'uid' => $uid,
            'username' => $mobile,
            'password' => "931f68875a20dd0d6d6a91711b856c7b9f1263a0",
            'roleid' => 1,
            'status' => 1,
            'realname' => $name,
            'openid' => $openid,
            'mobile' => $mobile
        ]);

        return $uid;
    }
}