<?php

namespace app\api\logic;

use think\Db;
use think\Cache;
use think\Model;

/**
 * 泊位停车离场计算
 * Class ParkingFeeCalculation
 * @package app\api\logic
 */
class ParkingFeeCalculation extends Model
{

    /**
     * @var车位编号
     */
    protected $number;


    protected $devOrderCode;

    /*
     * 离场时间
     */
    protected $endTime;

    /*
     * 订单信息
     */
    protected $orderPark;

    /*
     * 时间段信息
     */
    protected $charge;

    /*
     * 总停分
     */
    protected $duration = 0;

    /*
     * 总停分2
     */
    protected $totalMinute = 0;


    protected $monthInfo = null;


    /**
     * 月卡抵扣时间
     * @var int
     */
    protected $monthTime = 0;


    /**
     * 总金额
     */
    protected $tMoney = 0;

    /**
     * /*
     * param number 车位编号
     * param endTime 离场时间
     */
    public function sumMoney($param)
    {

        $this->number = $param['parkCode'];
        $this->endTime = $param['etime'];
        $this->devOrderCode = $param['ordersn'];
        $this->orderPark = $this->getOrderInfo();
        if (empty($this->orderPark)) {
            return "查询订单为空";
        }
        $this->number = $this->orderPark['number'];
        $this->charge = $this->getBerthInfo($this->number);
        $totalMoney = $this->timePeriodOperation();
        $totalMoney = sprintf("%.2f", $totalMoney);//计算总金额、总停分
        $this->tMoney = $totalMoney;
        $totalMoney = $this->isDiscount($totalMoney);
        if ($this->orderPark['charge_type'] == 0) {
            //预付费
            Cache::hdel('userData', $this->orderPark['user_id']);
            $this->prepaidProcessing($totalMoney);
        } else {
            //后付费
            $offerMoney = $totalMoney <= 0 ? 0 : $this->offerMoney($totalMoney);//计算优惠金额
            $this->postpaidProcessing($offerMoney);
        }
        return "已处理结算";
    }


    /*
     * 获取订单信息
     */
    protected function getOrderInfo()
    {
        //根据订单号查找
        return Db::name("foll_order")
            ->alias("a1")
            ->join("parking_order a2", "a2.ordersn=a1.ordersn")
            ->where("a2.devs_ordersn", $this->devOrderCode)
            ->where("a1.pay_status=0 or a2.charge_status=0")
            ->field(['a1.*', 'a2.starttime', 'a2.moncard', 'a2.duration', 'a2.charge_type', 'a2.number', 'a2.endtime'])
            ->find();
    }

    /**
     * 获取泊位信息
     * @param $berthNumber
     * @return array|false|\PDOStatement|string|Model
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    protected function getBerthInfo($berthNumber)
    {
        return Db::table("ims_parking_space space,ims_parking_charge charge")->where("charge.id=space.cid")->where("space.park_code=".$berthNumber)->field("charge.*")->find();//查找车位收费信息
    }

    /**
     * 返回匹配所停的时间段
     * @return array
     */
    protected function matchingPeriod()
    {
        return array_filter($this->charge['payPeriod'], [$this, "charge_range"]);

    }

    protected function charge_range($item)
    {
        $formatEndTime = date("H", $this->endTime);
        $formatStartTime = date("H", $this->orderPark['starttime']);
        $argc = $this->between_hour(explode(":", $item['starTime'])[0], explode(":", $item['endTime'])[0]);
        if (in_array(abs($formatStartTime), $argc) || in_array(abs($formatEndTime), $argc)) {
            return $item;
        }
    }

    /*返回小时自增数*/
    protected function between_hour($s, $e)
    {
        $s = abs($s);
        $e = abs($e);
        $hourArray = [];
        if ($s > $e) {
            for ($i = $s; $i <= 23; $i++) {
                array_push($hourArray, $i);
            }
            for ($i = 0; $i <= $e; $i++) {
                array_push($hourArray, $i);
            }
            return $hourArray;
        }
        for ($i = $s; $i <= $e; $i++) {
            array_push($hourArray, $i);
        }
        return $hourArray;
    }


    /*
     * 返回计算收费费用以及更新总停时
     */
    protected function timePeriodOperation()
    {
        $amount = 0;//存储计算的金额
        $allPeriod = null;//存放所有时间段
        $this->charge['payPeriod'] = json_decode($this->charge['payPeriod'], true);
        $charge = $this->charge['payPeriod'];
        $tem = null;
        $periodIndex = 0;
        $start = $this->orderPark['starttime'];
        $end = $this->endTime;
        //判断入场在那个段
        foreach ($charge as $key => $value) {
            $sp = explode(":", $value['starTime']);
            $ep = explode(":", $value['endTime']);
            $periodBeet = $this->between_hour($sp[0], $ep[0]);
            if (in_array(date('H', $start), $periodBeet)) {
                $tem = $value;
                $periodIndex = $key;
            }
        }
        $allCapped = $this->charge['Allcapped']; //全天封顶
        $topStart = $start;
        $flag = true;
        $list = [];
        $min = 0;
        $mon = 0;

        while ($flag) {
            if ($topStart <= $end) {
                $topStart += 86400;
                $mon = 0;
                foreach ($charge as $v) {
                    if ($this->orderPark['moncard'] == 1) {
                        if (!($this->monthTimePeriodSum2($v['starTime'], $v['endTime']))) {
                            $this->duration += 720;
                            $mon += $v['capped'];
                        } else {
                            $this->monthTime += 720;
                        }
                    } else {
                        $mon += $v['capped'];
                    }
                }
                if ($mon > $allCapped) {
                    $amount += $allCapped;
                } else {
                    $amount += $mon;
                }
            } else {
                if ($topStart > $end) {
                    $topStart = $topStart - 86400;
                    $mon = 0;
                    //减回多加一天
                    foreach ($charge as $val) {
                        if ($this->orderPark['moncard'] == 1) {
                            if (!($this->monthTimePeriodSum2($val['starTime'], $val['endTime']))) {
                                $this->duration = $this->duration - 720;
                                $mon = $mon + $val['capped'];
                            } else {
                                if ($this->monthTime != 0) {
                                    $this->monthTime = $this->monthTime - 720;
                                }
                            }
                        } else {
                            $mon = $mon + $val['capped'];
                        }

                    }
                    if ($mon > $allCapped) {
                        $amount = $amount - $allCapped;
                    } else {
                        $amount = $amount - $mon;
                    }
                    $flag = false;
                    if ($periodIndex == (count($charge) - 1)) {
                        $tempStart = date("Y-m-d", $topStart + 18000).' '.$tem['endTime'];
                    } else {
                        $tempStart = date("Y-m-d", $topStart).' '.$tem['endTime'];
                    }

                    if ($tempStart >= date("Y-m-d H:i", $end)) {
                        array_push($list, [date('Y-m-d H:i', $topStart), date("Y-m-d H:i", $end)]);
                    } else {
//                        array_push($list, [date('Y-m-d H:i', $topStart), date("Y-m-d H:i", strtotime($tempStart)) . ' ' . $tem['endTime']))]);
                        array_push($list,
                            [date('Y-m-d H:i', $topStart), date("Y-m-d", strtotime($tempStart)).' '.$tem['endTime']]);
                        $periodStart = strtotime(date("Y-m-d", $topStart).' '.$tem['endTime']) + 60;
                        $periodStartPar = date("H:i", $periodStart);
                        foreach ($charge as $key => $value) {
                            $sp = explode(":", $value['starTime']);
                            $ep = explode(":", $value['endTime']);
                            $periodBeet = $this->between_hour($sp[0], $ep[0]);
                            if (in_array(abs(explode(":", $periodStartPar)[0]), $periodBeet)) {
                                if ($key == (count($charge) - 1)) {
                                    $tempStart = date("Y-m-d", strtotime($tempStart) + 86400).' '.$value['endTime'];
                                    $tempStart = strtotime($tempStart);
                                } else {
                                    $tempStart = date("Y-m-d", strtotime($tempStart)).' '.$value['endTime'];
                                    $tempStart = strtotime($tempStart);
                                }
                                if ($tempStart >= $end) {
                                    if ($periodIndex == (count($charge) - 1)) {

                                        array_push($list,
                                            [date('Y-m-d', $end).' '.$value['starTime'], date("Y-m-d H:i", $end)]);

                                    } else {
                                        array_push($list, [
                                            date('Y-m-d', $periodStart).' '.$value['starTime'], date("Y-m-d H:i", $end)
                                        ]);
                                    }
//                                    array_push($list, [date('Y-m-d', $end) . ' ' . $value['starTime'], date("Y-m-d H:i", $end)]);
                                } else {

                                    array_push($list, [
                                        date('Y-m-d', $topStart).' '.$value['starTime'],
                                        date('Y-m-d', $tempStart).' '.$value['endTime']
                                    ]);
                                    $periodStart = strtotime(date("Y-m-d", $tempStart).' '.$value['endTime']) + 60;
                                    $periodStartPar = date("H:i", $periodStart);
                                    foreach ($charge as $k => $val) {
                                        $sp1 = explode(":", $val['starTime']);
                                        $ep1 = explode(":", $val['endTime']);
                                        $periodBeet = $this->between_hour($sp1[0], $ep1[0]);
                                        if (in_array(abs(explode(":", $periodStartPar)[0]), $periodBeet)) {
                                            array_push($list,
                                                [date('Y-m-d', $end).' '.$val['starTime'], date("Y-m-d H:i", $end)]);
                                        }
                                    }
                                }
                                break;
                            }
                        }
                    }
                }
            }
        }
        $dayMoney = 0;
        $mon = 0;
        foreach ($list as $key => $value) {
            foreach ($charge as $k => $v) {
                $sp = explode(":", $v['starTime']);
                $ep = explode(":", $v['endTime']);
                $periodBeet = $this->between_hour($sp[0], $ep[0]);
                if (in_array(abs(date("H", strtotime($value[0]))), $periodBeet)) {
                    $min = ceil((strtotime($value[1]) - strtotime($value[0])) / 60);
                    if ($min < $v['free']) {
                        $min = 0;
                    }

                    if ($this->orderPark['moncard'] == 1 && $min != 0) {
                        if ($this->monthTimePeriodSum2($v['starTime'], $v['endTime'])) {
                            $this->monthTime += $min;
                            $min = 0;
                        }
                    }
                    $this->duration += $min;
                    $mon = ceil(($min / $v['minute'])) * $v['price'];
                    if ($mon > $v['capped']) {
                        $dayMoney += $v['capped'];
                    } else {
                        $dayMoney += $mon;
                    }
                }
            }
        }

        if ($dayMoney > $allCapped) {
            $amount += $allCapped;
        } else {
            $amount += $dayMoney;
        }
        $nMinute = (count($list) - 1);
        if ($nMinute < 0) {
            $nMinute = 0;
        }

        $this->duration += $nMinute;
        return $amount;
    }


    /*
    * 是否在于在免费时间段月卡
    */
    protected function monthTimePeriodSum2($sPeroids = null, $ePeroids = null)
    {

        $sPeroidsPar = date_parse($sPeroids);
        $ePeroidsPar = date_parse($ePeroids);
        $isCard = false;

        $newCard = Db::name('card_member')->where(['openid'=>$this->orderPark['user_id'],'status'=>'Y'])->select();
        if (empty($newCard)){
            return false;
        }
        // 月卡类型(0 全日 1 日间 2 夜间)
        foreach ($newCard as $value){
            if ($value['card_type']==0){
                if ( $this->ValidDate(0,$this->orderPark['user_id'],date('Y-m-d',$this->endTime))){
                    $argv = $this->between_hour(00, 24);
                    if (in_array(abs($sPeroidsPar['hour']), $argv) && in_array(abs($ePeroidsPar['hour']), $argv)) {
                        $isCard = true;
                        break;
                    }
                }
            }else if ($value['card_type']==1){
                if ($this->ValidDate(1,$this->orderPark['user_id'],date('Y-m-d',$this->endTime))){
                    $argv = $this->between_hour(8, 20);
                    if (in_array(abs($sPeroidsPar['hour']), $argv) && in_array(abs($ePeroidsPar['hour']), $argv)) {
                        $isCard = true;
                        break;
                    }
                }
            }else if ($value['card_type']==2){
                if ($this->ValidDate(2,$this->orderPark['user_id'],date('Y-m-d',$this->endTime))){
                    $argv = $this->between_hour(20, 8);
                    if (in_array(abs($sPeroidsPar['hour']), $argv) && in_array(abs($ePeroidsPar['hour']), $argv)) {
                        $isCard = true;
                        break;
                    }
                }
            }
        }

        return $isCard;

//        $monthResult = Db::name("parking_month_pay")->where(['user_id' => $this->orderPark['user_id'], 'pay_status' => 1, 'status' => 'A'])->find();
//        $this->monthInfo = Db::name('parking_month_type')->where('id', $monthResult['m_id'])->find();
//
//        if (!is_null($this->monthInfo)) {
//            if ($this->monthInfo['month_status'] == 0) {
//                return false;
//            }
//
//            $numBeet = explode("-", $this->monthInfo['park']);//月卡适用车位段
//            $cardBeet = explode("-", $this->monthInfo['period']);//月卡适用时间段
//            $startTimeFormat = date("H:i", $this->orderPark['starttime']);//进入
//            $endTimeFormat = date("H:i", $this->endTime);//离场
//            $s = explode(":", $startTimeFormat);//拆分时，分
//            $e = explode(":", $endTimeFormat);
//            $c1 = explode(":", $cardBeet[0]);//月卡适用时间开始
//            $c2 = explode(":", $cardBeet[1]);//月卡适用时间段结束
//            $expiryTime = time();
//            /**是否过期*/
//            if ($expiryTime < $this->monthInfo['expir_start']) {
//                unset($monthResult, $s, $e, $c1, $c2, $p2);
//                return false;
//            }
//
//            if ($expiryTime > $this->monthInfo['expir_end']) {
//                Db::name('parking_month_pay')->where('id', $monthResult['id'])->update(['status' => 'D']);
////                Db::name('parking_month_type')->where('id', $this->monthInfo['id'])->update(['month_status' => 0]);
//                unset($monthResult, $s, $e, $c1, $c2, $p2);
//                return false;
//            }
//            if ($this->orderPark['charge_type'] == 0) {
//                return false;
//            }
//            $argv = $this->between_hour($c1[0], $c2[0]);
//            $sPeroidsPar = date_parse($sPeroids);
//            $ePeroidsPar = date_parse($ePeroids);
//            if (in_array(abs($sPeroidsPar['hour']), $argv) && in_array(abs($ePeroidsPar['hour']), $argv)) {
//                return true;
//            }
//        }else{
//            $newCard = Db::name('card_member')->where(['openid'=>$this->orderPark['user_id'],'status'=>'Y'])->find();
//            if (empty($newCard)){
//                return false;
//            }
//            $isCard = false;
//            switch ($newCard['card_type']){
//                // 月卡类型(0 全日 1 日间 2 夜间)
//                case 0:
//                    $isCard = $this->ValidDate(0,$this->orderPark['user_id'],date('Y-m-d',$this->endTime));
//                    break;
//                case 1:
//                    $isCard = $this->ValidDate(1,$this->orderPark['user_id'],date('Y-m-d',$this->endTime));
//                    break;
//                case 2:
//                    $isCard = $this->ValidDate(2,$this->orderPark['user_id'],date('Y-m-d',$this->endTime));
//                    break;
//            }
//            return $isCard;
//        }




    }

    /*
     * 是否在于在免费时间段
     */
    protected function monthTimePeriodSum($sPeroids = null, $ePeroids = null)
    {

        if (is_null($this->monthInfo)) {
            $monthResult = Db::name("parking_month_pay")->where([
                'user_id' => $this->orderPark['user_id'], 'pay_status' => 1, 'status' => 'A'
            ])->find();
            if (empty($monthResult)) {
                return 0;
            }
            $this->monthInfo = Db::name('parking_month_type')->where('id', $monthResult['m_id'])->find();
        }

        if ($this->monthInfo['month_status'] == 0) {
            return 0;
        }

        $numBeet = explode("-", $this->monthInfo['park']);//月卡适用车位段
        $cardBeet = explode("-", $this->monthInfo['period']);//月卡适用时间段
        $startTimeFormat = date("H:i", $this->orderPark['starttime']);//进入
        $endTimeFormat = date("H:i", $this->endTime);//离场
        $s = explode(":", $startTimeFormat);//拆分时，分
        $e = explode(":", $endTimeFormat);
        $c1 = explode(":", $cardBeet[0]);//月卡适用时间开始
        $c2 = explode(":", $cardBeet[1]);//月卡适用时间段结束
        $p2 = explode(":", $ePeroids);
        $expiryTime = time();
        /**是否过期*/
        if ($expiryTime < $this->monthInfo['expir_start']) {
            unset($monthResult, $s, $e, $c1, $c2, $p2);
            return 0;
        }

        if ($expiryTime > $this->monthInfo['expir_end']) {
            Db::name('parking_month_pay')->where('id', $monthResult['id'])->update(['status' => 'D']);
            Db::name('parking_month_type')->where('id', $this->monthInfo['id'])->update(['month_status' => 0]);
            unset($monthResult, $s, $e, $c1, $c2, $p2);
            return 0;
        }


        switch ($this->monthInfo['fit_type']) {
            case 0:
                //区域
                return 0;
            case 1:
                //路段
                return 0;
            case 2:
                //泊位
                return 0;
            case 3:
                //时段
                if ($startTimeFormat >= $cardBeet[0]) {//进入时间c1>=k1月卡开始时间
                    if ($ePeroids >= $cardBeet[1]) {
                        if ($cardBeet[1] <= $endTimeFormat) {
                            $isnegative = ceil($this->Operation($c2[0], $s[0], $c2[1], $s[1]));
                            return ($isnegative < 0) ? abs($isnegative) : $isnegative;
                        } else {
                            if ($cardBeet[1] >= $endTimeFormat) {
                                $isnegative = ceil($this->Operation($e[0], $s[0], $e[1], $s[1]));
                                return ($isnegative < 0) ? abs($isnegative) : $isnegative;
                            }
                        }
                    }
                    if ($ePeroids <= $cardBeet[1]) {
                        $isnegative = ceil($this->Operation($p2[0], $s[0], $p2[1], $s[1]));
                        return ($isnegative < 0) ? abs($isnegative) : $isnegative;
                    }
                }

                if ($startTimeFormat <= $cardBeet[0]) {//进入时间<=月卡开始时间
                    if ($ePeroids >= $cardBeet[1]) {
                        if ($cardBeet[1] <= $endTimeFormat) {
                            $isnegative = ceil($this->Operation($c2[0], $c1[0], $c2[1], $c1[1]));
                            return ($isnegative < 0) ? abs($isnegative) : $isnegative;
                        }
                        if ($cardBeet[1] >= $endTimeFormat) {
                            $isnegative = ceil($this->Operation($e[0], $c1[0], $e[1], $c1[1]));
                            return ($isnegative < 0) ? abs($isnegative) : $isnegative;
                        }
                    }
                    $isnegative = ceil($this->Operation($p2[0], $c1[0], $p2[1], $c1[1]));
                    return ($isnegative < 0) ? abs($isnegative) : $isnegative;
                }
                return 0;
            default :
                return 0;

        }
    }

    protected function Operation($t1 = 0, $t2 = 0, $t3 = 0, $t4 = 0)
    {
        return abs(((int) $t1 - (int) $t2)) * 60 + (int) $t3 - (int) $t4;
    }

    /*
     * 跨天计算
     *
     */
    protected function OperationCrossDays($sDevTime, $eDevTime, $k, $periodHours, $periodHourmer)
    {
        $min = 0;
        $total = count($this->charge['payPeriod']);
        if ($total > 1) {
            if ($k == $total) {
                $min = (int) $eDevTime[0] - (int) $periodHours[0];
                $min = abs($min) * 60 + (int) $eDevTime[1];
                return $min;
            }
            $min = (int) $periodHourmer[0] - (int) $periodHours[0];
            $min = abs($min) * 60 + (int) $periodHourmer[1];
            return $min;
        }
        return ((24 - (int) $sDevTime[0]) + (int) $eDevTime[0]) * 60 + (int) $eDevTime[1] - (int) $sDevTime[1];
    }


    /*
     * 是否节日
     */
    protected function isestivalF($minute)
    {
        $day = Cache::get('day');
        if (empty($day)) {
            $showapi_appid = '52908';  //替换此值,在官网的"我的应用"中找到相关值
            $showapi_secret = 'f4dbb4a2d3a84cda8555c81237db42c9';  //替换此值,在官网的"我的应用"中找到相关值
            $paramArr = [
                'showapi_appid' => $showapi_appid, 'day' => date("Ymd", time())//添加其他参数
            ];
            $param = $this->createParam($paramArr, $showapi_secret);
            $url = 'http://route.showapi.com/894-2?'.$param;
            $result = file_get_contents($url);
            $days = json_decode($result, true);
            $cacheTiem = 24 - (date("H", time())) * 60 * 60;
            Cache::set('day', $result, $cacheTiem);
        } else {
            $days = json_decode($day, true);
        }
        if (isset($days['showapi_res_body']['type']) && $days['showapi_res_body']['type'] == '3') {
            $pirce = Db::name("parking_holiday_schedule")->where([
                'holiday_type' => 3, 'uniacid' => $this->orderPark['uniacid']
            ])->field('pirce')->order('id', 'desc')->find();
            return $pirce['pirce'];
        }
        return $minute;
    }

    /*
     *
     * 创建请求节日参数
     */
    protected function createParam($paramArr, $showapi_secret)
    {
        $paraStr = "";
        $signStr = "";
        ksort($paramArr);
        foreach ($paramArr as $key => $val) {
            if ($key != '' && $val != '') {
                $signStr .= $key.$val;
                $paraStr .= $key.'='.urlencode($val).'&';
            }
        }
        $signStr .= $showapi_secret;//排好序的参数加上secret,进行md5
        $sign = strtolower(md5($signStr));
        $paraStr .= 'showapi_sign='.$sign;//将md5后的值作为参数,便于服务器的效验
        return $paraStr;
    }

    /*
    * 是否全天封顶
    */
    protected function isAllDayCapping($amounts)
    {
        switch ($this->charge['allClass']) {
            case 0:
                //停满24小时
                if ($amounts >= $this->charge['Allcapped']) {
                    $hour = ((int) $this->endTime - (int) $this->orderPark['starttime']) / 60 / 60;
                    $hour = floor($hour);
                    if ($hour >= 24) {
                        return $this->charge['Allcapped'];
                    }
                }
                return $amounts;
            case 1:
                //停入当天2359起
                if (date("d", $this->endTime) !== date("d", $this->orderPark['starttime'])) {
                    return $this->charge['Allcapped'];
                }
                return $amounts;
        }
    }

    /*
     * 计算优惠
     */
    protected function offerMoney($totalMoney)
    {
        return $totalMoney;
    }


    /*
     * 预付费处理
     */
    protected function prepaidProcessing($money)
    {

        $price = $this->orderPark['pay_account'] - $money;
        $price = $this->useBalanceDeduction($price);
        if ($price == 0) {
            $this->duration = 0;
            Db::name("parking_order")
                ->where("ordersn", $this->orderPark['ordersn'])
                ->update(['duration' => $this->duration, 'status' => '已结算', 'charge_status' => 1, 'card_time' => $this->monthTime]);
            $this->sendMsgSuccess($this->successMessageConfig($this->tMoney, 0.00));
        }else if ($price < 0) {
            $sendArr = [
                'touser' => $this->orderPark['user_id'],//接收消息的用户
                'payMoney' => $price, 'uniacid' => $this->orderPark['uniacid'],//公众号ID
                'body' => '停车服务费',//商品描述
                'paytime' => date("Y-m-d H:i", $this->orderPark['starttime'])."至".date("Y-m-d H:i", $this->endTime),
                //离场时间  开始时间跟结束时间date('Ymdhi',time()).'至'.date('Ymdhi',time()),
            ];
            $millisecond = round(explode(" ", microtime())[0] * 1000);
            $order_id = 'G99198'.date('Ymd', time()).str_pad($millisecond, 3, '0', STR_PAD_RIGHT).mt_rand(111, 999).mt_rand(111, 999);
            Db::table("ims_parking_order")->where("ordersn", $this->orderPark['ordersn'])->update(['status' => '已结算', 'charge_status' => 1, 'card_time' => $this->monthTime]);
            $this->InsertFollOrder(($money - $this->orderPark['pay_account']), $order_id, ($money - $this->orderPark['pay_account']));
            $this->InsertParkingOrder($order_id);
            $this->sendErrorMessagess($sendArr, $order_id);
        }else if ($price > 0) {
            $url = 'http://shop.gogo198.cn/payment/wechat/refund.php';
            $postdata = ['token' => 'refund', 'ordersn' => $this->orderPark['ordersn'], 'refundMoney' => $price,];
            $res = httpRequest($url, $postdata);
            @file_put_contents('../runtime/log/pay/'.date('Ymd', time()).'_refund.txt', date('Y-m-d H:i:s', time())."--".$res.":订单:".$this->orderPark['ordersn']."\n", FILE_APPEND);
            $res = json_decode($res, true);
            if (isset($res['status']) && $res['status'] == 100) {
                $isWrite = 103;
                if ($price == $this->orderPark['pay_account']) {
                    $isWrite = 100;
                }
                $this->updateFollOrder([
                    'pay_status' => 1,
                    'pay_account' => ($this->orderPark['pay_account'] - $price),
                    'IsWrite' => $isWrite, 'ref_auto' => 2
                ]);
                $this->updateParkingOrder();
                $this->sendMsgSuccess($this->successMessageConfig($money, $price, '您好，您的停车服务费扣费成功!', '已退还剩余金额'));
            } else {
                $this->updateFollOrder([
                    'pay_status' => 1,
                    'pay_account' => ($this->orderPark['pay_account'] - $price),
                    'IsWrite' => 102,
                    'ref_auto' => 2
                ]);
                $this->updateParkingOrder();
                $this->sendMsgSuccess($this->successMessageConfig($money, $price, '您好，您的停车服务费扣费成功!', '退款失败'));
            }
        }
    }


    /*
     * 后付费处理
     */
    protected function postpaidProcessing($Offermoney)
    {
//        $Offermoney = $this->useBalanceDeduction($Offermoney);
        if ($Offermoney == 0) {
//            $this->duration = ceil(($this->endTime - $this->orderPark['starttime']) / 60);
            $this->duration = 0;
            Db::table("ims_foll_order")
                ->where("ordersn", $this->orderPark['ordersn'])
                ->update(['pay_type' => 'other', 'pay_status' => 1, 'pay_time' => time(), 'pay_account' => $Offermoney, 'total' => $this->tMoney,]);
            Db::table("ims_parking_order")->where("ordersn", $this->orderPark['ordersn'])
                ->update([
                    'endtime' => $this->endTime,
                    'duration' => $this->duration,
                    'status' => '已结算',
                    'card_time' => $this->monthTime
            ]);
            $this->sendMsgSuccess($this->successMessageConfig($this->tMoney, $Offermoney));
            sendPayInfoDev($this->orderPark['ordersn']);
            // httpRequest('http://shop.gogo198.cn/foll/public/?s=api/pullOnlinePayStatusApi', ['ordersn' => $this->orderPark['ordersn'], 'type' => 'wp']);
        } else {
            //更新订单表
            Db::table("ims_foll_order")->where("ordersn", $this->orderPark['ordersn'])->update(['total' => $this->tMoney, 'pay_account' => $Offermoney, 'pay_status' => 2]);
            Db::table("ims_parking_order")->where("ordersn", $this->orderPark['ordersn'])->update([
                'endtime' => $this->endTime,
                'duration' => $this->duration,
                'status' => '已出账',
                'time_period' => json_encode($this->charge['payPeriod']),
                'span_status' => count($this->charge['payPeriod']) > 1 ? 1 : 0,
                'card_time' => $this->monthTime
            ]);
            $res = Db::table("ims_parking_authorize")->where('openid', $this->orderPark['user_id'])->find();
            // 没有免密就主动
            if ($res['auth_status'] == 1) {
                $parkResult = $this->FreePay($res, $this->orderPark['ordersn']);
                if ($parkResult['status']) {
                    Db::name("foll_order")->where('ordersn', $this->orderPark['ordersn'])->update(['pay_type' => $parkResult['type']]);
                }
            }else{
                $sendArr = [
                    'touser' => $this->orderPark['user_id'],//接收消息的用户
                    'payMoney' => $Offermoney, 'uniacid' => $this->orderPark['uniacid'],//公众号ID
                    'body' => '停车服务费',//商品描述
                    'paytime' => date("Y-m-d H:i", $this->orderPark['starttime'])."至".date("Y-m-d H:i", $this->endTime),
                    //离场时间  开始时间跟结束时间date('Ymdhi',time()).'至'.date('Ymdhi',time()),
                ];
                $this->sendErrorMessagess($sendArr);
            }
        }
    }


    /**
     * 使用余额扣费
     * @param $totalMoneys
     * @return int
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    protected function useBalanceDeduction($totalMoneys)
    {
        $userResult = Db::table("ims_foll_wallet")
            ->where(['user_id' => $this->orderPark['user_id'], 'uniacid' => $this->orderPark['uniacid']])
            ->where("money", '>', $totalMoneys)
            ->find();
        if (empty($userResult)) {
            return $totalMoneys;
        }
        $decMoney = $userResult['money'] - $totalMoneys;
        Db::table("ims_parking_authorize")->where('openid', $this->orderPark['user_id'])->setDec('blance', $decMoney);
        Db::table("ims_foll_wallet")
            ->where(['user_id' => $this->orderPark['user_id'], 'uniacid' => $this->orderPark['uniacid']])
            ->update(['money' => $decMoney, 'up_time' => time()]);
        Db::table("ims_money_fullback_log")
            ->insert([
                'user_id' => $this->orderPark['user_id'],
                'uniacid' => $this->orderPark['uniacid'],
                'business_id' => $this->orderPark['uniacid'],
                'orderid' => $this->orderPark['ordersn'],
                'change_money' => $totalMoneys,
                'action_status' => 20,
                'application' => 'parking',
                'remarks' => '路内停车扣费',
                'operating_time' => time()
        ]);
        return 0;
    }

    /*
     * 免密支付
     */
    protected function FreePay($res, $orderId = null)
    {
        $type = unserialize($res['auth_type']);
        //银联无感免密扣费
        if (in_array('FCreditCard', $type)) {
            if (empty($res['credit_accout'])) {
                return ['status' => false, 'type' => 'Parks'];
            }
            $url = "http://shop.gogo198.cn/payment/sign/Togrand.php";
            $postdata = [
                'token' => 'Parks',//停车支付  Parks
                'ordersn' => $orderId,//
                'CarNo' => $res['CarNo']
            ];
            $data = json_decode(httpRequest($url, $postdata), true);
            @file_put_contents("./paylog/api/Fagro.txt", "银联：".json_encode($data).":".date("Y-m-d H:i:s", time())."\n",
                FILE_APPEND);
            if ($data['Message']['Plain']['Result']['ResultCode'] == '00') {
                return ['status' => true, 'type' => 'Parks'];
            }
            return ['status' => false, 'type' => 'Parks'];
        }

        //农商行免密扣费
        if (in_array("FAgro", $type)) {
            if (empty($res['credit_accout'])) {
                return ['status' => false, 'type' => 'FAgro'];
            }
            $url = "http://shop.gogo198.cn/payment/agro/Fagro.php";
            $postdata = [
                'Token' => 'FeeDeduction', 'OrderSn' => $orderId, 'Phone' => $res['CustId'],
                'CardNo' => $res['credit_accout']
            ];
            $data = httpRequest($url, $postdata);
            @file_put_contents("./paylog/api/Fagro.txt", "农商：".$data.":".date("Y-m-d H:i:s", time())."\n", FILE_APPEND);
            //file_put_contents('../runtime/log/lichang.txt',json_encode($data.date('Y-m-d H:i',time()))."\n",FILE_APPEND);
            $data = json_decode($data, true);
            if ($data['status'] == 101) {
                return ['status' => true, 'type' => 'FAgro'];
            }
            return ['status' => false, 'type' => 'FAgro'];
        }
        if (in_array('Fwechat', $type)) {
            $url = "http://shop.gogo198.cn/payment/Frx/Frx.php";
            $postdata = [
                'Token' => 'Fee', //停车类型；
                'inType' => 'PARKING',//场景ID； 停车场：PARKING   停车位：PARKING SPACE
                'orderSn' => $orderId,//订单编号；
            ];
            $data = httpRequest($url, $postdata);
            @file_put_contents("./paylog/api/Fagro.txt",
                'wx:'.$data.":".'订单单号:'.$this->orderPark['ordersn'].date("Y-m-d H:i:s", time())."\r\n", FILE_APPEND);
            $data = json_decode($data, true);
            if (!empty($data) && $data['status'] == 1) {
                return ['status' => true, 'type' => 'Fwechat'];
            }
            return ['status' => false, 'type' => 'Fwechat'];
        }
        return ['status' => false];
    }

    // 发送订单成功消息
    protected function sendMsgSuccess($sendArr)
    {
        $template = [
            'touser' => $sendArr['touser'], 'template_id' => '671nviCnAMjycHKkjzeUg3NqnM0HwIBnt8bKnDEjf8g',
            'url' => '',
            'data' => [
                'first' => ['value' => $sendArr['first'], 'color' => '#173177'],
                'keyword1' => ['value' => $sendArr['parkTime'], 'color' => '#436EEE'],
                'keyword2' => ['value' => $sendArr['realTime'].' 分钟', 'color' => '#173177'],
                'keyword3' => ['value' => '￥'.$sendArr['payableMoney'].'元', 'color' => '#173177'],
                //                'keyword4' => ['value' => '-￥' . abs($sendArr['deducMoney']) . '元','color' => '#173177'],
                'keyword4' => ['value' => '￥'.$sendArr['payMoney'].'元', 'color' => '#173177'],
                'remark' => ['value' => $sendArr['remark'], 'color' => '#808080'],
            ],
        ];//消息模板
        $ASSESS_TOKEN = $this->RequestAccessToken($sendArr['uniacid']);
        $hosts = "https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=".$ASSESS_TOKEN;
        $wxResult = httpRequest($hosts, json_encode($template));
        @file_put_contents("../runtime/log/wx/wx.txt", $wxResult);
    }

    /*
     * 发送失败
     * */
    protected function sendErrorMessagess($sendArr, $ordersn = null)
    {
        $ordersn = empty($ordersn) ? $this->orderPark['ordersn'] : $ordersn;
        $template = [
            'touser' => $sendArr['touser'], 'template_id' => 'trXaMaikj3VTVCmx4l9urCvridhOrD_95q2Z1NG3ae0',
            'url' => 'http://shop.gogo198.cn/app/index.php?i=14&c=entry&m=ewei_shopv2&do=mobile&r=parking.pay&orderid='.$ordersn,
            'data' => [
                'first' => ['value' => "抱歉，你的停车服务费扣费失败！", 'color' => '#173177'],
                'keyword1' => ['value' => $sendArr['body'], 'color' => '#000000'],
                'keyword2' => ['value' => '￥'.$sendArr['payMoney'].'元', 'color' => '#000000'],
                'keyword3' => ['value' => $sendArr['paytime'], 'color' => '#000000'],
                'remark' => ['value' => '请点击详情，继续完成支付！', 'color' => '#000000']
            ]
        ];
        //消息模板
        $ASSESS_TOKEN = $this->RequestAccessToken($sendArr['uniacid']);
        $hosts = "https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=".$ASSESS_TOKEN;
        $wxResult = httpRequest($hosts, json_encode($template));
        @file_put_contents("../runtime/log/wx/wx.txt", $wxResult);
    }


    /**
     * @param $sendArr
     * @return bool|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    protected function RequestAccessToken($uniacid)
    {

        return RequestAccessToken($uniacid);
    }

    /**
     * @param $money
     * @param $order_id
     * @param $price
     */
    protected function InsertFollOrder($money, $order_id, $price)
    {
        Db::table("ims_foll_order")->insert([
            'ordersn' => $order_id, 'user_id' => $this->orderPark['user_id'],
            'business_id' => $this->orderPark['uniacid'], 'uniacid' => $this->orderPark['uniacid'],
            'application' => 'parking', 'goods_name' => '路内停车', 'goods_price' => 0.00, 'pay_status' => 2,
            'pay_account' => $price, 'body' => '停车预付费超出补扣', 'business_name' => $this->orderPark['business_name'],
            'create_time' => time(), 'total' => $this->tMoney, 'nickname' => $this->orderPark['nickname']
        ]);
    }

    /**
     * @param $order_id
     */
    protected function InsertParkingOrder($order_id)
    {
        Db::table("ims_parking_order")->insert([
            'ordersn' => $order_id, 'number' => $this->number, 'starttime' => $this->orderPark['starttime'],
            'endtime' => $this->endTime, 'duration' => $this->duration - $this->orderPark['duration'],
            'status' => '已出账', 'charge_type' => 1, 'charge_status' => 1,
            'time_period' => json_encode($this->charge['payPeriod']),
            'span_status' => count($this->charge['payPeriod']) > 1 ? 1 : 0,
            'OthSeq' => date('YmdHis', time()).rand(111111, 999999)
        ]);
    }

    /**
     * @param $price
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    protected function updateFollOrder($data)
    {
        Db::table("ims_foll_order")->where("ordersn", $this->orderPark['ordersn'])->update($data);
    }

    protected function updateParkingOrder()
    {
        Db::table("ims_parking_order")->where("ordersn", $this->orderPark['ordersn'])->update([
            'endtime' => $this->endTime, 'duration' => $this->duration, 'status' => '已结算', 'charge_status' => 1,
            'card_time' => $this->monthTime
        ]);
    }

    /**
     * @param $money
     * @param $Offermoney
     * @return array
     */
    protected function successMessageConfig($money, $Offermoney, $first = '您好，您的停车服务费扣费成功!', $rem = '欢迎您下次继续使用!')
    {
        $sendArr = [
            'body' => $this->orderPark['body'],//商品描述
            'paytime' => date('Y-m-d H:i', time()),//消费时间
            'touser' => $this->orderPark['user_id'],//接收消息的用户
            'uniacid' => $this->orderPark['uniacid'],//公众号ID
            'parkTime' => $this->duration == 0 ? ceil(($this->endTime - $this->orderPark['starttime']) / 60).' 分钟' : $this->duration,
            //停车时长
            'realTime' => $this->duration,//实计时长
            'payableMoney' => sprintf("%.2f", $money),//应付金额
            'deducMoney' => sprintf("%.2f", $money - $Offermoney),//抵扣金额
            'payMoney' => sprintf("%.2f", $Offermoney),//交易金额  实付金额
        ];
        $sendArr['first'] = $first;
        $sendArr['remark'] = $rem;
        return $sendArr;
    }

    /**
     * @param $totalMoney
     * @param $couponRes
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    protected function rebateProcessing($totalMoney, $couponRes)
    {
        Db::table("ims_parking_authorize")
            ->where("openid", $this->orderPark['user_id'])
            ->setInc('blance', $couponRes['backcredit']);
        Db::table("ims_foll_rebate_water")->insert([
                'user_id' => $this->orderPark['user_id'],
                'business_id' => $this->orderPark['uniacid'],
                'old' => 'Gogo'.date('YmdHis', time()).mt_rand(10000000, 99999999).$this->orderPark['uniacid'],
                'uniacid' => $this->orderPark['uniacid'],
                'goods' => '路内停车',
                'goods_price' => $totalMoney,
                'rebate_money' => $couponRes['backcredit'],
                'status' => 1,
                'body' => '优惠券返利',
                'create_time' => time()
        ]);
        Db::table('ims_money_fullback_log')->insert([
                'user_id' => $this->orderPark['user_id'],
                'uniacid' => $this->orderPark['uniacid'],
                'business_id' => $this->orderPark['uniacid'],
                'orderid' => $this->orderPark['ordersn'],
                'change_money' => $couponRes['backcredit'],
                'action_status' => 30,
                'application' => 'parking',
                'remarks' => '停车优惠返利',
                'operating_time' => time()
        ]);
        $isEmptyOrNullData = Db::table("ims_foll_wallet")
            ->where(['user_id' => $this->orderPark['user_id'], 'uniacid' => $this->orderPark['uniacid']])
            ->find();
        if (empty($isEmptyOrNullData)) {
            Db::table("ims_foll_wallet")->insert([
                'user_id' => $this->orderPark['user_id'],
                'business_id' => $this->orderPark['uniacid'],
                'uniacid' => $this->orderPark['uniacid'],
                'money' => $couponRes['backcredit'],
                'up_time' => time(),
                'create_time' => time()
            ]);
        } else {
            Db::table("ims_foll_wallet")
                ->where(['user_id' => $this->orderPark['user_id'], 'uniacid' => $this->orderPark['uniacid']])
                ->setInc('money', $couponRes['backcredit']);
        }
    }

    /**
     * @param $s
     * @param $ev
     * @param $value
     * @param $money
     * @return array
     */
    protected function computePeriodMoney($s, $ev, $value)
    {
        $t = 0;
        $monthMinute = 0;
        $money = 0;
        $t = $ev[0] - $s[0];
        $t = ceil((abs($t) * 60) + ($ev[1] - $s[1]));
        if ($this->orderPark['moncard'] == 1 && $t != 0) {     //是否使用月卡
            $monthMinute = $this->monthTimePeriodSum($value['starTime'], $value['endTime']);
            if ($t > $monthMinute) {
                $this->monthTime += $monthMinute;
                $t = $t - $monthMinute;
            } else {
                $this->monthTime += $t;
                $t = 0;
            }
        }
        if ($t <= $value['free']) {
            $t = 0;
        }
        $this->duration += $t;
        $t = ceil($t / $value['minute']);
        $m = $t * $value['price'];
        if ($m > $value['capped']) {
            $money = $value['capped'];
        } else {
            $money += $m;
        }
        return $money;
    }

    /**
     * @param $outHour
     * @param $inHour
     * @param $outMinute
     * @param $inMinute
     * @param $value
     * @param $t
     * @return array
     */
    protected function notPeriodCount($outHour, $inHour, $outMinute, $inMinute, $value)
    {
        $amount = 0;
        $monthMinute = null;
        $_timer = ceil($this->Operation($outHour, $inHour, $outMinute, $inMinute));
        $this->duration += $_timer;
        if ($_timer <= $value['free']) {
            $_timer = 0;
        }

        //是否使用月卡
        if ($this->orderPark['moncard'] == 1 && $_timer != 0) {
//            $monthMinute = $this->monthTimePeriodSum($value['starTime'], $value['endTime']);
//            if ( $_timer > $monthMinute ) {
//                $this->monthTime += $monthMinute;
//                $_timer          = $_timer - $monthMinute;
//            } else {
//                $this->monthTime += $_timer;
//                $_timer          = 0;
//            }
            if ($this->monthTimePeriodSum2($value['starTime'], $value['endTime'])) {
                $this->monthTime += $_timer;
                $_timer = 0;
            }
//
        }

        $amount = $_timer <= 0 ? 0 : (ceil($_timer / $this->isestivalF($value['minute']))) * $value['price'];
        if ($amount > $value['capped']) {
            $amount = $value['capped'];
        }
        return $amount;
    }

    /**
     * 是否打折
     * @param $totalMoney
     * @return float|int
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    protected function isDiscount($totalMoney)
    {
        $disRes = Db::name('parking_operate')->where([
            'uniacid' => $this->orderPark['uniacid'], 'status' => 2
        ])->field(['discount', 'startDate', 'endDate'])->find();
        $time = time();
        if (!empty($disRes) && $totalMoney > 0) {
            if (($time >= $disRes['startDate']) && ($time <= $disRes['endDate'])) {
                $totalMoney = $totalMoney * ($disRes['discount'] / 10);
            }
        }
        return $totalMoney;
    }

    /* **
  * 验证对应月卡某天的有效性
  * @$cardType int     月卡类型(0 全日 1 日间 2 夜间)
  * $openid    string  用户 openid
  * $date      string  日期(yyyy-MM-dd)
  *
  * @returns   bool
  *   false 无效 ture 有效
 ** */
    private function ValidDate($cardType, $openid, $date) {
        // $sql = 'select id, sdate, edate, card_type, openid from ims_card_member where status = "Y" and card_type = ? and openid = ? and edate > date_add(now(), interval 1 day)';
        $sql = 'select id, sdate, edate, card_type, openid from ims_card_member where status = "Y" and card_type = ? and openid = ? and edate >= date_format(now(),"%Y-%m-%d")';
        $row = Db::query($sql, array($cardType,$openid));
        /*
        print_r($row);
        echo $date . '**';
        echo strtotime($row['sdate']) . '**';
        echo strtotime($row['edate']) . '**';
        echo strtotime($date) . '**';
        // */
        if ($row) {
            $row = $row[0];
            $n  = strtotime($date);
            $ns = strtotime($row['sdate']);
            $ne = strtotime($row['edate']);
            if ($n >= $ns && $n <= $ne) {
                //echo 'ok';
                return true;
            }
        }
        return false;
    }

}
