<?php

if (!(defined('IN_IA'))) {
	exit('Access Denied');
}

class Parking_EweiShopV2Model
{
    public  function charger($num=''){
        $id=pdo_get("parking_space",array("park_code"=>$num));
        $adrr=pdo_get("parking_position",array("id"=>$id['pid']));
        $char=pdo_get("parking_charge",array("id"=>$id['cid']));
		return $data=['adrr'=>$adrr,'char'=>$char];
    }
    public  function auth($openid='') {
        global $_W;
        //global $_GPC;
        $opid = $openid != '' ? $openid : $_W['openid'];
        $UserAuth=pdo_get("parking_authorize",array("openid"=>$opid,"auth_status"=>1));
        if(!empty($UserAuth)){
            $UserAuth['auth_type']=unserialize($UserAuth['auth_type']);
        }
        return $UserAuth;
    }
    public  function verifMonthCard($st){
        global $_W;
        global $_GPC;
        // $res = pdo_get('card_member',['openid'=>,'status'=>'Y']);
        $res = pdo_fetchall("select * from ".tablename('card_member')." where openid = '{$_W['openid']}' and status = 'Y' and '{$st}' between sdate and edate");
        return  $res;
    }

    public function coupon(){
        global $_W;
        global $_GPC;
        return pdo_fetchall("select tb2.id from ims_foll_receivecoupon tb1 left join ims_foll_coupon tb2 on tb2.id=tb1.card_id where tb1.user_id='{$_W['openid']}' and tb1.status=1 and tb2.status=0");
    }
}
