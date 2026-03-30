<?php
if (!defined('IN_IA'))
{
    exit('Access Denied');
}

/**
 *
 * stopIn,//停入车次当天
*totalStop,//停入车次总
*wxConfirm,//微信确认当天
*totalWxConfir,//微信确认总
*devPay,//咪表缴费当天
*totalDevPay,//咪表缴费总
*timeOut,//确认超时当天
*totalTimeOut,//确认超时总
*leavePayDone,//离开已结当天
*toLeavePayDone,//离开已结总
*leaveNotPay,//离开未结当天
*toLeaveNotPay,//离开未结总
*excepOrder,//异免离开当天
*totalExcepOrder,//异免离开总
*parkNotPay,//停入未结当天
*totalParkNotPay//停入未结总
 * Class Parking_data_EweiShopV2Page
 */

class Parking_data_EweiShopV2Page extends mobilePage{
    
    protected $redis = null;
    
    public function main(){
        global $_W;
        global $_GPC;
        include $this->template("parking/quserinfo/data_info");
    }
    
    public function payData(){
        global $_W;
        global $_GPC;
        include $this->template("parking/quserinfo/pay_data_info");
    }

    public function getParkingData(){
        global $_W;
        global $_GPC;
        load()->classs("redis");
        try{
            $this->redis = Rediss::getInstance();
            if ( !empty($_GPC['time']) ) {
                $data = $this->searchDbByTime($_GPC['time']);
            } else {
                $data = $this->getDataByCache();
                $data['stopIn'] = $data['wxConfirm']+$data['devPay']+$data['timeOut'];
                $this->redis->set('stopIn',$data['stopIn']);
            }
            $total = $this->getCount($data);
//            $payInfo  = $this->getPayInfo($_GPC['time']);
            $data=array_merge($data,$total);
//            $data=array_merge($data,$payInfo);
        }catch (Exception $exception){
            show_json(-1, $exception->getMessage());
        }
        show_json(0, $data);
    
    }


    public function getPayData(){
        global $_W;
        global $_GPC;
        $payInfo  = $this->getPayInfo($_GPC['time']);
        show_json(0, $payInfo);
//        load()->classs("redis");
//        $this->redis = Rediss::getInstance();
    }


    /**
     * 从缓存查找当天
     * @return array
     */
    protected function getDataByCache(){
    
//        $stopIn       = $this->redis->get('stopIn');
        $wxConfirm    = $this->redis->get('wxConfirm');
        $devPay       = $this->redis->get('devPay');
        $timeOut      = $this->redis->get('timeOut');
        $excepOrder   = $this->redis->get('excepOrder');
        return [
            'stopIn'=> 0,//停入车次当天
            'wxConfirm'=>empty($wxConfirm)?0:$wxConfirm,//微信确认当天
            'devPay'=>empty($devPay)?0:$devPay,//咪表缴费当天
            'timeOut'=>empty($timeOut)?0:$timeOut,//确认超时当天
            'excepOrder'=>empty($excepOrder)?0:$excepOrder,//异免离开当天
        ];
        

    }
    
    /**
     * 根据日期从表查找
     * @param $time
     * @return array
     */
    protected function searchDbByTime($time){
        $stopIn       = 0;
        $wxConfirm    = 0;
        $devPay       = 0;
        $timeOut      = 0;
        $excepOrder   = 0;
        $res = pdo_get('parking_data',['time'=>$time]);
        if (!empty($res)){
            $stopIn       = $res['stopIn'];
            $wxConfirm    = $res['wxConfirm'];
            $devPay       = $res['devPay'];
            $timeOut      = $res['timeOut'];
            $excepOrder   = $res['excepOrder'];
        }
        return [
            'stopIn'=>$stopIn,//停入车次当天
            'wxConfirm'=>$wxConfirm,//微信确认当天
            'devPay'=>$devPay,//咪表缴费当天
            'timeOut'=>$timeOut,//确认超时当天
            'excepOrder'=>$excepOrder,//异免离开当天
        ];
    }
    
    /**
     * 获取总数
     */
    protected function getCount($res){
        $totalStop = pdo_fetch('select sum(stopIn) as num from '.tablename('parking_data'))['num'];
        $totalWxConfir = pdo_fetch('select sum(wxConfirm) as num from '.tablename('parking_data'))['num'];
        $totalDevPay = pdo_fetch('select sum(devPay) as num from '.tablename('parking_data'))['num'];
        $totalTimeOut = pdo_fetch('select sum(timeOut) as num from '.tablename('parking_data'))['num'];
        $totalExcepOrder = pdo_fetch('select sum(excepOrder) as num from '.tablename('parking_data'))['num'];
        return [
            'totalStop'=>empty($totalStop)?(0+ $res['stopIn']):($totalStop+$res['stopIn']),//停入车次总
            'totalWxConfir'=>empty($totalWxConfir)?(0+$res['wxConfirm']):($totalWxConfir+$res['wxConfirm']),//微信确认总
            'totalDevPay'=>empty($totalDevPay)?(0+$res['devPay']):($totalDevPay+$res['devPay']),//咪表缴费总
            'totalTimeOut'=>empty($totalTimeOut)?(0+$res['timeOut']):($totalTimeOut+$res['timeOut']),//确认超时总
            'totalExcepOrder'=>empty($totalExcepOrder)?(0+$res['excepOrder']):($totalExcepOrder+$res['excepOrder']),//异免离开总
        ];
    }
    
    protected function getPayInfo($time){
        $todayFirstSeconds = mktime(0, 0, 0, date('m'), date('d'), date('Y'));
        $todayLastSeconds = mktime(23, 59, 59, date('m'), date('d'), date('Y'));
        if (!empty($time)){
            $time = strtotime($time);
            $todayFirstSeconds = mktime(0, 0, 0, date('m',$time), date('d',$time), date('Y',$time));
            $todayLastSeconds  = mktime(23, 59, 59, date('m',$time), date('d',$time), date('Y',$time));
        }
        $leavePayDone = pdo_fetch("select count(a.id) as num from ".tablename('foll_order')." as a left join ".tablename('parking_order')." as b on a.ordersn=b.ordersn where a.application='parking' and a.pay_status=1 and b.charge_status=1 and a.create_time>=".$todayFirstSeconds." and a.create_time<=".$todayLastSeconds)['num'];
        $leaveNotPay = pdo_fetch("select count(id) as num from ".tablename('foll_order')." where application='parking' and pay_status=2 and create_time>=".$todayFirstSeconds." and create_time<=".$todayLastSeconds)['num'];
        $parkNotPay = pdo_fetch("select count(a.id) as num from ".tablename('foll_order')." as a left join ".tablename('parking_order')." as b on a.ordersn=b.ordersn where  a.application='parking' and a.create_time>=".$todayFirstSeconds." and a.create_time<=".$todayLastSeconds." and (b.endtime=0 or b.charge_status=0)")['num'];
        
        $toLeavePayDone = pdo_fetch("select count(a.id) as num from ".tablename('foll_order')." as a left join ".tablename('parking_order')." as b on a.ordersn=b.ordersn where  a.application='parking' and a.pay_status=1 and b.charge_status=1")['num'];
        $toLeaveNotPay = pdo_fetch("select count(id) as num from ".tablename('foll_order')." where application='parking' and pay_status=2")['num'];
        $totalParkNotPay =  pdo_fetch("select count(a.id) as num from ".tablename('foll_order')." as a left join ".tablename('parking_order')." as b on a.ordersn=b.ordersn where a.application='parking' and (b.endtime=0 or b.charge_status=0)")['num'];
       return [
            'leavePayDone'=>empty($leavePayDone)?0:$leavePayDone,//离开已结当天
            'leaveNotPay'=>empty($leaveNotPay)?0:$leaveNotPay,//离开未结当天
            'parkNotPay' =>empty($parkNotPay)?0:$parkNotPay,//停入未结当天
           'toLeavePayDone'=>$toLeavePayDone,//离开已结总
           'toLeaveNotPay'=>$toLeaveNotPay,//离开未结总
           'totalParkNotPay'=>$totalParkNotPay//停入未结总
       ];
    }
    
}
