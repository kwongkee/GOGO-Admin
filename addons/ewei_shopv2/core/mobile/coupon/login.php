<?php
if (!defined('IN_IA')) {
    exit('Access Denied');
}

/**
 * 商家登录
 * Class Verification_sheet_EweiShopV2Page
 */
class Login_EweiShopV2Page extends mobilePage {
    public function main() {
        global $_W;
        global $_GPC;
        include $this->template('coupon/login');
    }

    /**
     * 发送验证码
     */
    public function sendCode() {
        global $_W;
        global $_GPC;
        // $set = m('common')->getSysset(array('shop', 'wap'));
        $sms_id = $this->GetSmsid($_W['uniacid']);
        // $sms_id = $set['wap'][$temp];
        $key = '__ewei_shopv2_member_verifycodesession_' . $_W['uniacid'] . '_' . $_GPC['mobile'];
        $code = random(5, true);
        $ret = com('sms')->send($_GPC['mobile'], $sms_id['id'], ['名称' => $_W['uniaccount']['name'], '验证码' => $code]);
        if ($ret['status']) {
            $_SESSION[$key] = $code;
            $_SESSION['verifycodesendtime'] = time();
            show_json(1, '短信发送成功');
        }
        show_json(0, $ret['message']);
    }

    public function GetSmsid($cid) {
        return pdo_get('ewei_shop_sms', ['uniacid' => $cid,'name'=>'登录'], ['id']);
    }


    public function verif_login(){
        global $_W;
        global $_GPC;
        if ( empty($_GPC['mobile']) || strlen($_GPC['mobile']) != 11 ) {
            show_json(-1, '请输入手机号');
        }
        if ( !preg_match( "/^1[34578]\d{9}$/", $_GPC['mobile']) ) {
            show_json(-1, '请输入正确的手机号');
        }
        $key = '__ewei_shopv2_member_verifycodesession_' . $_W['uniacid'] . '_' . $_GPC['mobile'];
        if ( !($_SESSION[$key] == $_GPC['yzm']) || $_GPC === '' ) {
            show_json(-1, '验证码错误');
        }

        $userRes = pdo_get('foll_seller_member',['busin_login_accout'=>$_GPC['mobile']]);
        if (empty($userRes)){
            show_json(-1, '用户不存在');
        }
        $_SESSION['busin_admin'] = $userRes;
        $_COOKIE['busin_admin']  = $userRes['busin_num'];
        show_json(0, '登录成功');
    }

    public function login_success(){
        $this->message('登录成功,关闭出现扫码！','', 'success');
    }

}