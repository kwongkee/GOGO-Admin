<?php

defined('IN_IA') or exit('Access Denied');
function Discount($openid,$total){
    $CouponId=pdo_get("parking_card_receive",array("openid"=>$openid),array("cid"));
    $CouponRes=pdo_get("parking_coupon",array("id"=>$CouponId['cid'],"status"=>0));
    $count=0;
    if(date("Y-m-d",time())<=date("Y-m-d",$CouponRes['timeend'])){
         switch ($CouponRes['coupontype']){
             case 0:
                 //立减
                 $count=LegislativeReduction($CouponRes,$total);
                 break;
             case 1:
                 //打折
                 break;
             case 2:
                 //返利
                 break;
         }
    }
    return $count;
}

function LegislativeReduction($res,$tol){
    if($tol>$res['enough']){
        return $res['deduct'];
    }

}
function imageUrl($imageNmae=null){
    return MODULE_ROOT."/static/images/".$imageNmae.".jpg";
}

/*
userData 用户数据
n 抽取人数
*/
function  lottery($UserData=array(),$n=null)
{
    $res=array('yes'=>array(),'no'=>array());//存放中签跟未中签的用户数组
    $UserRes=$UserData;
    unset($UserData);
    $MaxNum=count($UserRes);
    if(empty($UserRes)){return false;}
    shuffle($UserRes);
    try{
        for($i=0;$i<=$n-1;$i++){
            $randNum=mt_rand(0,$MaxNum-1);
            if($UserRes[$randNum]){
                array_push($res['yes'],$UserRes[$randNum]);
                unset($UserRes[$randNum]);
            }
        }
        $res['no']=$UserRes;
        return $res;
    }catch(Exception $e){
        return false;
    }
}


function isUserReg(){
    global $_W;
    global $_GPC;
    $isUserResult   =   pdo_get("parking_authorize",array('openid'=>$_W['openid']));
    if($_W['fans']['follow']==1){
        if(empty($isUserResult)){
            header("Location:".mobileUrl('parking/reg'));
        }
    }
}

function getPeriodMoney($data){
    global $_W;
    global $_GPC;
    $money = 0;
    if(empty($data['filtered']['price'])){
        return  $money;
    }
//    if(isset($_GPC['jiashi'])){
//        $money = $data['filtered']['price']*($data['filtered']['addMinus']/$data['filtered']['minute']);
//    }else{
    $money = $data['filtered']['price']*($data['filtered']['y_minute_new']/$data['filtered']['minute']);
//    }
    if($money==0){
        $money = $data['filtered']['price'];
    }
    return $money;
}

function getPeriodTime($data)
{
    global $_W;
    global $_GPC;
    if(empty($data['filtered']['y_minute_new'])){
        return 0;
    }
    return $data['filtered']['y_minute_new'];

}