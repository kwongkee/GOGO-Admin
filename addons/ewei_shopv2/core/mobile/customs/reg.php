<?php
if (!(defined('IN_IA'))) {
    exit('Access Denied');
}

class Reg_EweiShopV2Page extends MobilePage
{
    public function main()
    {
        global $_W;
        $isUser = pdo_get('decl_user', ['openid' => $_W['openid']]);
        if ($isUser) {
            $this->message('已注册过', '', 'success');
        }
        $plat_id = pdo_get('customs_platform_list');
        $buss_list = pdo_getall('account_wechats');
        include $this->template('customs/reg');
    }

    public function regStorage()
    {
        global $_W;
        global $_GPC;

        if ($_GPC['plat_id'] == "") {
            show_json(-1, '平台id错误');
        }

        if ($_GPC['user_name'] == "") {
            show_json(-1, '公司名称必填');
        }

        if ($_GPC['user_tel'] == "") {
            show_json(-1, '手机号必填');
        }

        if ($_GPC['user_email'] == "") {
            show_json(-1, '邮箱必填');
        }

        if (empty($_GPC['remark'])) {
            show_json(-1, '使用业务必填');
        }

        if ($_GPC['buss_id'] == '') {
            show_json(-1, '请选择店铺');
        }

        $key = '__ewei_shopv2_member_verifycodesession_' . $_W['uniacid'] . '_' . $_GPC['user_tel'];
        if (!($_SESSION[$key] == $_GPC['yzm']) || $_GPC['yzm'] === '') {
            show_json(-1, '验证码错误');
        }

        $isUser = pdo_get('decl_user', ['user_tel' => $_GPC['user_tel']]);
        if (!empty($isUser)) {
            show_json(-1, '已存在该手机号');
        }


        $remrk = null;
        $busType = ['P' => '平台管理', 'E' => '电商预提', 'L' => '物流申报'];
        $typeNum = null;
        foreach ($_GPC['remark'] as $item) {
            $remrk .= $busType[$item] . ',';
            $typeNum = $item . ',';
        }
        $uuid = pdo_get('users', ['username' => $_GPC['user_name']]);

        try {
            if (!$uuid) {
                pdo_insert('users', [
                    'groupid' => 0,
                    'username' => $_GPC['user_name'],
                    'password' => md5($_GPC['user_tel']),
                    'salt' => substr(uniqid(), -6, 6),
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
                //  生成用户表
                $uuid = pdo_insertid();
            } else {
                $uuid = $uuid['uid'];
            }
            pdo_insert('sz_yi_perm_user', [
                'uniacid' => $_GPC['buss_id'],
                'uid' => $uuid,
                'username' => $_GPC['user_name'],
                'password' => md5($_GPC['user_tel']),
                'roleid' => 1,
                'status' => 1,
                'perms' => '',
                'deleted' => 0,
                'banknumber' => '',
                'accountname' => '',
                'accountbank' => '',
                'openid' => '',
                'status1' => 0,
                'brandname' => '',
            ]);

            pdo_insert('decl_user', [
                'user_name' => $_GPC['user_name'],
                'user_tel' => $_GPC['user_tel'],
                'user_email' => $_GPC['user_email'],
                'user_password' => password_hash($_GPC['user_tel'], PASSWORD_DEFAULT),
                'openid' => $_W['openid'],
                'uniacid' => $_W['uniacid'],
                'plat_id' => $_GPC['plat_id'],
                'remark' => $remrk,
                'created_at' => date('Y-m-d H:i:s', time()),
                'user_num' => md5(mt_rand(1111, 9999) . mt_rand(1111, 9999)),
                'bus_type' => $typeNum,
                'buss_id' => $_GPC['buss_id'],
                'company_name' => $_GPC['user_name'],
                'company_num' => $_GPC['company_num'],
                'supplier' => $uuid,
            ]);


            // 生成默认费率
            $uid = pdo_insertid();
            // 插入数据
            pdo_insert('customs_rates',[
                'uid'       =>$uid,
                'verfee'    =>1.00,
                'payfee'    =>0.006,
                'orderfee'  =>0.50,
                'c_time'    =>time(),
            ]);

        } catch (Exception $exception) {
            show_json(-1, '注册失败');
        }
        show_json(0, '注册成功');
    }
}
