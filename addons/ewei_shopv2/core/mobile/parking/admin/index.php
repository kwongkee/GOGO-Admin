<?php
if (!defined('IN_IA'))
{
    exit('Access Denied');
}
class Index_EweiShopV2Page extends Page
{
    public function __construct ()
    {
        global $_GPC;
        global $_W;
       if(empty($_SESSION['RoleUserInfo'])){
           header("Location:".mobileUrl('parking/admin/login'));
       }
        load()->model('role');
    }

    public function main()
    {
        $title="在线管理";
        $data['role']=getRoleResult($_SESSION['RoleUserInfo']['role']);
        $data['company']=getCompanyResult($_SESSION['RoleUserInfo']['uniacid']);
        include $this->template("parking/admin/index");
    }

    public function roleAuth()
    {
        $title="角色添加";
        $data['role']=getRoleResult($_SESSION['RoleUserInfo']['role']);
        $data['company']=getCompanyResult($_SESSION['RoleUserInfo']['uniacid']);
        include $this->template("parking/admin/role_auth");
    }

    public function billingAdd()
    {
        $title="计费方案添加";
        $data['role']=getRoleResult($_SESSION['RoleUserInfo']['role']);
        $data['company']=getCompanyResult($_SESSION['RoleUserInfo']['uniacid']);
        include $this->template("parking/admin/billing_add");
    }

    public function parkAdd()
    {
        $title="泊位添加";
        $data['role']=getRoleResult($_SESSION['RoleUserInfo']['role']);
        $data['company']=getCompanyResult($_SESSION['RoleUserInfo']['uniacid']);
        include $this->template("parking/admin/park_add");
    }

    public function roleAuthAdd()
    {
        global $_GPC;
        global $_W;
        $read=null;
        $write=null;
        if($_GPC['authType']==1){
            $read=1;$write=0;
        }else{
            $read=1;$write=1;
        }
        $auth=pdo_get("foll_business_rwaccess",array('role_id'=>$_SESSION['RoleUserInfo']['role']));
        if($auth['write']!=1){
            show_json(0,"权限不足");
        }
        $rid=pdo_insert("foll_business_authrole",[
            'name' =>$_GPC['name'],
            'pid'  =>$_SESSION['RoleUserInfo']['role'],
            'status'=>1,
            'create_time'=>time(),
            'update_time'=>time()
        ]);
        if (!empty($rid)) {
            $uid = pdo_insertid();
            $result = pdo_insert('foll_business_admin',[
                'uniacid' =>$_W['uniacid'],
                'user_name'=>$_GPC['userName'],
                'user_mobile'=>$_GPC['mobile'],
                'user_status' =>1,
                'user_email' =>$_GPC['mobile']."@qq.com",
                'create_time' =>date("Y-m-d H:i:s",time()),
                'role'      =>$uid,
                'user_pid'  =>$_SESSION['RoleUserInfo']['id']
            ]);
            pdo_insert("foll_business_rwaccess",[
                'role_id'   =>$uid,
                'read'      =>$read,
                'write'     =>$write
            ]);
            show_json(1,'添加完成');
        }
    }

    public function billingSave()
    {
        global $_W;
        global $_GPC;
        if(empty($_GPC['periodTime'])){
            show_json(0,"时间参数必填");
        }
        if(empty($_GPC['plan'])){
            show_json(0,"请选择方案");
        }
        try{
            pdo_insert("parking_charge",[
                'payPeriod' =>json_encode($_GPC['periodTime']),
                'ChargeClass' =>$_GPC['plan'],
                'Allcapped' =>$_GPC['dayCapped'],
                'uniacid'   =>$_W['uniacid'],
                'period_limit'=>$_GPC['periodLimit'],
            ]);
            show_json(1,"添加完成");
        }catch (Exception $e){
            show_json(0,"异常");
        }
    }

}