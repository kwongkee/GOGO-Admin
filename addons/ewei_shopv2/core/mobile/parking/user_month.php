<?php

if (!defined('IN_IA'))
{
    exit('Access Denied');
}

class User_Month_EweiShopV2Page extends mobilePage
{

    public function main()
    {
        global $_W;
        global $_GPC;
//        $allMonth = pdo_getall('parking_month_pay',['user_id'=>$_W['openid']],[],'','id desc',[1,10]);
        $allMonth = pdo_fetchall("select a.id as muid, a.status, a.pay_status,b.* from ims_parking_month_pay as a left join ims_parking_month_type as b on a.m_id = b.id where a.user_id='".$_W['openid']."' and pay_status=1 order by id desc limit 10");

        $applyList = pdo_fetch("select b.* from ".tablename('parking_apply')." as a  left join ".tablename('parking_month_type')." as b on a.m_id=b.id where a.user_id='".$_W['openid']."' and a.is_up='N' and a.is_win=1 and a.is_done='N'");
        if (!empty($applyList)){
            $applyList['pay_status'] = "0";
            $applyList['status']  = 'A';
            array_push($allMonth,$applyList);
        }
//        dump($allMonth);
        include $this->template('parking/user_month_list');
    }
    
    /**
     * 月卡注销提交页面
     *
     */
    public function month_out()
    {
        global $_W;
        global $_GPC;
        include $this->template('parking/user_month_out');
    }
    
    /**
     * 月卡注销处理
     */
    public function month_out_handle()
    {
        global $_W;
        global $_GPC;
        try{
            if (empty($_GPC['cardNo'])){
                show_json(-1,'请填写卡号!');
            }
            
            if (!$this->check_bankCard($_GPC['cardNo'])){
                show_json(-1,'卡号格式错误!');
            }
            
            if(empty($_GPC['name'])){
                show_json(-1,'请填写姓名!');
            }
            
            pdo_update('parking_month_pay',['ref_account'=>$_GPC['cardNo'],'ref_name'=>$_GPC['name'],'is_ref'=>1,'update_at'=>time()],['id'=>$_GPC['id']]);
            show_json(0,'已提交,请等待退款');
        }catch (Exception $exception){
            show_json(-1,'系统异常');
        }
        
    }
    
    public function check_bankCard($card_number){
        $chars = "/^(\d{16}|\d{19}|\d{17})$/";
        if (preg_match($chars, $card_number)) {
            return true;
        } else {
            return false;
        }
    }
    
}
