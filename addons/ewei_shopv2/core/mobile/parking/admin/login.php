<?php
if (!defined('IN_IA'))
{
exit('Access Denied');
}
class Login_EweiShopV2Page extends Page
{
    public function main()
    {
        include $this->template("parking/admin/login");
    }

    public function verify_login()
    {
        global $_GPC;
        global $_W;
        $key = '__ewei_shopv2_member_verifycodesession_' . $_W['uniacid'] . '_' . $_GPC['mobile'];
        if(!($_SESSION[$key]==$_GPC['yzm'])){
            show_json(0,'验证码错误');
        }
        $userResult=pdo_get("foll_business_admin",array("user_mobile"=>$_GPC['mobile'],'user_status'=>1));
        if(empty($userResult)) {
            show_json(0,'账户异常');
        }

        $_SESSION['RoleUserInfo']=$userResult;
        show_json(1,'登录成功');
    }
}