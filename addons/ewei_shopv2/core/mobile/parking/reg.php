<?php

if ( !defined('IN_IA') ) {
    exit('Access Denied');
}

class Reg_EweiShopV2Page extends mobilePage
{

    public function main ()
    {
        global $_W;
        global $_GPC;
        $title      = '注册';
        $isVerifReg = pdo_get("parking_authorize", ['openid' => $_W['openid']]);
        if ( !empty($isVerifReg) ) {
            header("Location:" . mobileUrl('parking/info'));
        }
        include $this->template('parking/reg');
    }
    
    public  function  regg(){
        global $_W;
        global $_GPC;
        
        $title      = '注册测试';
        include $this->template('parking/reg11');
    }
    
    public function  agreement(){
        $title = '服务协议';
        
        include $this->template('parking/agreement');
    }
    
    public function code ()
    {
        global $_W;
        global $_GPC;
        // $set = m('common')->getSysset(array('shop', 'wap'));
        $sms_id = $this->GetSmsid($_W['uniacid']);
        // $sms_id = $set['wap'][$temp];
        $key  = '__ewei_shopv2_member_verifycodesession_' . $_W['uniacid'] . '_' . $_GPC['mobile'];
        $code = random(5, true);
        $ret  = com('sms')->send($_GPC['mobile'], $sms_id['id'], ['名称' => $_W['uniaccount']['name'], '验证码' => $code]);
        @file_put_contents('../data/logs/'.'sms.log',   json_encode($ret).'|手机号：'.$_GPC['mobile'] . "\n", FILE_APPEND);

        if ( $ret['status'] ) {
            $_SESSION[$key]                 = $code;
            $_SESSION['verifycodesendtime'] = time();
            show_json(1, '短信发送成功');
        }
        show_json(0, $ret['message']);
    }

    public function GetSmsid ( $cid )
    {
        return pdo_get('ewei_shop_sms', ['uniacid' => $cid], ['id']);
    }

    /*public function verify_regs()
    {
        global $_W;
        global $_GPC;
        if ( $_W['isajax'] ) {
            if ( $_GPC['tongyi'] != 1 ) {
                show_json(0, '请同意协议');
            }
            if ( empty($_GPC['mobile']) || strlen($_GPC['mobile']) != 11 ) {
                show_json(0, '请输入手机号');
            }
            if ( !preg_match('#^13[\d]{9}$|^14[5,7]{1}\d{8}$|^15[^4]{1}\d{8}$|^17[0,6,7,8]{1}\d{8}$|^18[\d]{9}$#', $_GPC['mobile']) ) {
                show_json(0, '请输入正确的手机号');
            }
            $key = '__ewei_shopv2_member_verifycodesession_' . $_W['uniacid'] . '_' . $_GPC['mobile'];
            if ( !($_SESSION[$key] == $_GPC['yzm']) || $_GPC === '' ) {
                show_json(0, '验证码错误');
            }
            $member    = pdo_fetch('select uid from ' . tablename('mc_mapping_fans') . ' where openid=:openid and uniacid=:uniacid limit 1', [':openid' => $_W['openid'], ':uniacid' => $_W['uniacid']]);
            $members   = pdo_fetch('select mobile from ' . tablename('mc_members') . ' where uid=:uid and uniacid=:uniacid limit 1', [':uid' => $member['uid'], ':uniacid' => $_W['uniacid']]);
            $issetUser = pdo_fetch('select * from ' . tablename('parking_authorize') . ' where openid=:openid or mobile=:mobile limit 1', [':openid' => $_W['openid'], ':mobile' => $_GPC['mobile']]);
            if ( !empty($issetUser) ) {
                show_json(2, "已注册过");
            }
            $uqid = "u" . uniqid(mt_rand(000000, 999999)) . time();
            if ( isset($members['mobile']) ) {
                pdo_update('mc_members', ['mobile' => $_GPC['mobile']], ['uid' => $member['uid']]);
                pdo_insert('parking_authorize', ['unique_id' => $uqid, 'uid' => $member['uid'], 'uniacid' => $_W['uniacid'], 'openid' => $_W['openid'], 'mobile' => $_GPC['mobile'], 'create_time' => time()]);
                show_json(1, '验证成功');
            } else {
                $salt   = random(16);
                $result = pdo_insert('mc_members', ['uniacid' => $_W['uniacid'], 'mobile' => $_GPC['mobile'], 'salt' => $salt, 'groupid' => $_W['uniacid'], 'createtime' => time()]);
                if ( !empty($result) ) {
                    $uid = pdo_insertid();
                    pdo_insert('parking_authorize', ['unique_id' => $uqid, 'uid' => $uid, 'uniacid' => $_W['uniacid'], 'openid' => $_W['openid'], 'mobile' => $_GPC['mobile'], 'create_time' => time()]);
                    show_json(1, '验证成功');
                }    // show_json(0,'')
            }
        }
    }*/

    //2018-07-24 ZJR
    public function verify_reg()
    {
        global $_W;
        global $_GPC;
        if ( $_W['isajax'] ) {
            if ( $_GPC['tongyi'] != 1 ) {
                show_json(0, '请同意协议');
            }
            if ( empty($_GPC['mobile']) || strlen($_GPC['mobile']) != 11 ) {
                show_json(0, '请输入手机号');
            }
            if ( !preg_match( "/^1[3456789]\d{9}$/", trim($_GPC['mobile'])) ) {
                show_json(0, '请输入正确的手机号');
            }
            $key = '__ewei_shopv2_member_verifycodesession_' . $_W['uniacid'] . '_' . $_GPC['mobile'];
            if ( !($_SESSION[$key] == $_GPC['yzm']) || $_GPC === '' ) {
                show_json(0, '验证码错误');
            }
            $member    = pdo_fetch('select uid from ' . tablename('mc_mapping_fans') . ' where openid=:openid and uniacid=:uniacid limit 1', [':openid' => $_W['openid'], ':uniacid' => $_W['uniacid']]);
            $members   = pdo_fetch('select mobile from ' . tablename('mc_members') . ' where uid=:uid and uniacid=:uniacid limit 1', [':uid' => $member['uid'], ':uniacid' => $_W['uniacid']]);
            $issetUser = pdo_fetch('select * from ' . tablename('parking_authorize') . ' where openid=:openid or mobile=:mobile limit 1', [':openid' => $_W['openid'], ':mobile' => $_GPC['mobile']]);
            $usersinfo = pdo_fetch('select * from ' . tablename('usersinfo') . ' where phone=:mobile AND application=:app limit 1', [':mobile' => $_GPC['mobile'],':app'=>'parking']);
            if ( !empty($issetUser) && !empty($usersinfo) ) {
                show_json(2, "已注册过");
            }
            //唯一ID
            $uqid = "u" . uniqid(mt_rand(000000, 999999)) . time();
            //用户总表
            $usersinfos = ['uiqueid' => $uqid, 'application' => 'parking', 'phone' => $_GPC['mobile'], 'remarks' => 'A',//A注册，U修改，D注销
            'ctime' => time()];
            
            if ( isset($members['mobile']) ) {
                pdo_update('mc_members', ['mobile' => $_GPC['mobile']], ['uid' => $member['uid']]);
                
                pdo_insert('usersinfo', $usersinfos);
                pdo_insert('parking_authorize', ['unique_id' => $uqid, 'uid' => $member['uid'], 'uniacid' => $_W['uniacid'], 'openid' => $_W['openid'], 'mobile' => $_GPC['mobile'], 'create_time' => time()]);
                show_json(1, '验证成功');
            } else {
                $salt   = random(16);
                //写入用户总表
                $userinsert = pdo_insert('usersinfo', $usersinfos);
                $result     = pdo_insert('mc_members', ['uniacid' => $_W['uniacid'], 'mobile' => $_GPC['mobile'], 'salt' => $salt, 'groupid' => $_W['uniacid'], 'createtime' => time()]);
                if (!empty($result) && !empty($userinsert)) {
                    $uid = pdo_insertid();
                    pdo_insert('parking_authorize', ['unique_id' => $uqid, 'uid' => $uid, 'uniacid' => $_W['uniacid'], 'openid' => $_W['openid'], 'mobile' => $_GPC['mobile'], 'create_time' => time()]);
                    show_json(1, '验证成功');
                }    // show_json(0,'')   ims_userinfo
            }

            // var_dump($members);
        }
    }
}
