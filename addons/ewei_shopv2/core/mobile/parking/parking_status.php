<?php
if (!defined('IN_IA'))
{
    exit('Access Denied');
}
class Parking_Status_EweiShopV2Page extends mobilePage
{
    public function main()
    {
        global $_W;
        global $_GPC;
//        $oderRes=pdo_get("parking_order",array("openid"=>$_W['openid'],'uniacid'=>$_W['uniacid'],"pay_status"=>0));
//        if(!empty($oderRes)){
//            $adminName=pdo_fetchall("SELECT tb1.cid,tb2.user,tb2.phone FROM ims_parking_space tb1 LEFT JOIN ims_parking_admin_user tb2 ON tb1.admin_id=tb2.id WHERE tb1.numbers=".$oderRes['number']);
//            $newTime=floor((time() - $oderRes['starttime'])/60);
//            if($newTime>$this->obtainFreeTime($adminName[0]['cid'],$orderRes['starttime'])&&$oderRes['status']=='已停车'){
//                pdo_update("parking_order",array('status'=>'正计费'),array('id'=>$oderRes['id']));
//            }
//        }
        include $this->template("parking/parking_status");
    }
    
    public function obtainFreeTime($id,$stime)
    {
         $TollTimePrepaid=pdo_get("parking_charge",array("id"=>$id));
         $TollTimePrepaid['payPeriod']=json_decode($TollTimePrepaid['payPeriod'],true);
         $freeParkingTime=0;
         $stime=date("H:i",$stime);
         foreach($TollTimePrepaid as $key=>$value){
             if($stime>=$value['starTime']||$stime<=$value['endTime']){
                 $freeParkingTime=$value['free'];
                 break;
             }
         }
         return $freeParkingTime;
    }
    
    
    
}