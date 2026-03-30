<?php
if (!(defined('IN_IA'))) {
	exit('Access Denied');
}
class Money
{
    protected static $openid=null;
    protected static $num=null;
    // protected static $starTime=null;
    protected static $endTime=null;
    protected static $data=null;
    protected static $total=null;
	protected static $money=0;
	protected static $Card=null;
	protected static $lengthTime=0;
    public static function total($openid='',$num='',$order=array(),$endTime='')
    {
        self::$openid=$openid;
        self::$num=$num;
        // self::$starTime=$starTime;
        self::$endTime=$endTime;
		self::$data=$order;
        return self::totalMoneySum();
    }
    protected static function totalMoneySum()
    {
		$ParkingDataId=pdo_get("parking_space",array("numbers"=>self::$num),array('cid'));
		$TimeData=pdo_get("parking_charge",array("id"=>$ParkingDataId['cid']));
        self::$Card=pdo_get("parking_monthcard",array("openid"=>self::$openid,"pay_status"=>1,"status"=>0));
		$TimePeriod=json_decode($TimeData['payPeriod'],true);
		$EveryOtherDay=date("d",self::$endTime)-date("d",self::$data['starttime']);
        $h1=date("H:i",self::$data['starttime']);
        $h2=date("H:i",self::$endTime);
		$beetwn=array();
		$Reslut=array();
		$n=0;
        $Hour=0;
        for ($i = 0; $i <= count($TimePeriod)-1; $i++) {
		    if(($h1>=$TimePeriod[$i]['starTime']&&$h2<=$TimePeriod[$i]['endTime'])||($h1>="00:01"&&$h2<=$TimePeriod[$i]['endTime'])){
		        $beetwn[$n]=$i;
                $n+=1;
                continue;
            }else if ($h1>=$TimePeriod[$i]["starTime"]&&$h1<=$TimePeriod[$i]['endTime']){
                $beetwn[$n]=$i;
                $n+=1;
                continue;
            } else if($h2>=$TimePeriod[$i]['starTime']&&$h1<=$TimePeriod[$i]["endTime"]){
                $beetwn[$n]=$i;
                $n+=1;
             }
        }
		foreach ($beetwn as $key=>$value){
            if($TimePeriod[$value]['starTime']<=$h1){
                if($TimePeriod[$value]['endTime']<=$h2){
                    $ParkingHour=ceil((intval(explode(":",$TimePeriod[$value]['endTime'])['0']+1)-intval(date("H",self::$data['starttime'])))*60);
                    $ParkingHour=abs($ParkingHour);
                }else if($TimePeriod[$value]['endTime']>$h2){
                    $ParkingHour=ceil((intval(self::$endTime)-intval(self::$data['starttime']))/60);
                }
                if($ParkingHour<$TimePeriod[$value]['free']){
                    self::$money=0;
                }else{
                    if(self::$data['moncard']==1){
                        $Hour=empty(self::$Card)?0:self::card_sum($TimePeriod[$value]['starTime'],$TimePeriod[$value]['endTime']);
                    }
                    if($EveryOtherDay>1){
                        $ParkingHour=(($ParkingHour/60-$Hour)*60)+((24*60)*$EveryOtherDay);
//                        date("d",self::$endTime)==date("d",self::$data['starttime'])
                    }else{
                        $ParkingHour=($ParkingHour/60-$Hour)*60;
                    }
                    self::$lengthTime+=$ParkingHour;
                    $money2=(ceil($ParkingHour/$TimePeriod[$value]['minute']))*self::Princes($TimePeriod[$value]['price']);
                    if($money2>$TimePeriod[$value]['capped']){
                        self::$money+=$TimePeriod[$value]['capped'];
                    }else{
                        self::$money+=$money2;
                    }
                }
            }else if($h2<=$TimePeriod[$value]['endTime']){
                if($h2>=$TimePeriod[$value]['endTime']){
                    $ParkingHour=explode(":",$TimePeriod[$value]['endTime'])['0']-explode(":",$TimePeriod[$value]['starTime'])['0'];
                    $ParkingHour=abs(ceil(($ParkingHour+1)*60));
                }else{
                    $ParkingHour=intval(date("H",self::$endTime))-explode(":",$TimePeriod[$value]['starTime'])['0'];
                    $i=explode(":",date("H:i",self::$endTime));
                    $ParkingHour=abs(($ParkingHour*60)+$i['1']);
                }
                if($ParkingHour<$TimePeriod[$value]['free']){
                    self::$money+=0;
                }else{
                    if(self::$data['moncard']==1){
                        $Hour=empty(self::$Card)?0:self::card_sum($TimePeriod[$value]['starTime'],$TimePeriod[$value]['endTime']);
                    }
                    if($EveryOtherDay>1){
                        $ParkingHour=(($ParkingHour/60-$Hour)*60)+((24*60)*$EveryOtherDay);
//                        date("d",self::$endTime)==date("d",self::$data['starttime'])
                    }else{
                        $ParkingHour=($ParkingHour/60-$Hour)*60;
                    }
                    $money3=(ceil($ParkingHour/$TimePeriod[$value]['minute']))*self::Princes($TimePeriod[$value]['price']);
                    if($money3>$TimePeriod[$value]['capped']){
                        self::$money+=$TimePeriod[$value]['capped'];
                    }else{
                        self::$money+=$money3;
                    }
                }
                self::$lengthTime+=$ParkingHour;
            }
        }
        if(!empty($TimeData['Allcapped'])){
            switch($TimeData['allClass']){
                case 0:
                if(self::$money>$TimeData['Allcapped']){
                    $TimeHours=ceil((self::$endTime-self::$data['starttime'])/60/60);
                    if($TimeHours>=24){
                        $TimeDay=floor($TimeHours/24);
                        self::$money=$TimeData['Allcapped']*$TimeDay;
                    }
                }
                break;
                case 1:
                if(self::$money>$TimeData['Allcapped']){
                    if($EveryOtherDay!=0){
                        self::$money=$TimeData['Allcapped']*$EveryOtherDay;
                    }else{
                        self::$money=$TimeData['Allcapped'];
                    }
                }
                break;
                default:break;
            }
        }
//        pdo_update("parking_order",)
        return $Reslut=['total'=>self::$money,'length'=>self::$lengthTime];
        //节日请求回来存缓存，取缓存，第二天就请求新的
    }
    public static function Princes($p)
    {
        $day=cache_load('day');
        if(empty($day)){
            $showapi_appid = '52908';  //替换此值,在官网的"我的应用"中找到相关值
            $showapi_secret = 'f4dbb4a2d3a84cda8555c81237db42c9';  //替换此值,在官网的"我的应用"中找到相关值
            $paramArr = array(
                'showapi_appid'=> $showapi_appid,
                'day'=> date("Ymd",time())
                //添加其他参数
            );
            $param = self::createParam($paramArr,$showapi_secret);
            $url = 'http://route.showapi.com/894-2?'.$param;
            $result = file_get_contents($url);
            $days=json_decode($result,true);
            cache_write('day',$result,86400);
        }else{
          $days=json_decode($day,true);
        }
        if($days['showapi_res_body']['type']=='3'){
              $pirce=pdo_get("parking_holiday_schedule",array("holiday_type"=>'3'),array('pirce'));
              return $pirce['pirce'];
        }
        return $p;
    }
    public static function card_sum($start='',$end='')
    {
        $d=(time()-self::$Card['rechargedate'])/86400;
        $numBeet=explode("-",self::$Card['parspaces']);
        $cardBeet=explode("-",self::$Card['period']);
        $startHi=date("H:i",self::$data['starttime']);
        $endHi=date("H:i",self::$endTime);
        if($d<=self::$Card['endtime']){
            if(self::$num>=$numBeet['0']&&self::$num<=$numBeet['1']){
                if($startHi<=$cardBeet['0']){
                    if($end<=$cardBeet['1']||$cardBeet['1']<=$endHi){
                        return abs(explode(":",$end)['1']-date("H",self::$data['starttime']));
                    }else if ($end>=$cardBeet['1']||$cardBeet['1']<=$endHi){
                        return intval(explode(":",$cardBeet['1'])['0'])-intval(explode(":",$cardBeet['0'])['0']);
                    }else if ($end>=$cardBeet['1']||$cardBeet['1']>=$endHi){
                        return abs(intval(date("H",self::$endTime))-intval(explode(":",$cardBeet['0'])['0']));
                    }
                }else if($startHi>=$cardBeet['0']){
                    if($end<=$cardBeet['1']||$cardBeet['1']<=$endHi){
                        return intval(explode(":",$end)['1'])-intval(date("H",self::$data['starttime']));
                    }else if ($end<=$cardBeet['1']||$cardBeet['1']>=$endHi){
                        return date("H",self::$endTime)<self::$data['starttime']?24-(date("H",self::$endTime)-date("H",self::$data['starttime'])):date("H",self::$endTime)-date("H",self::$data['starttime']);
                    }else if($end>=$cardBeet['1']||$cardBeet['1']<=$endHi){
                        return explode(":",$cardBeet['1'])['0']-date("H",self::$data['starttime']);
                    }
                }
            }
        }else{
            pdo_update("parking_monthcard",array("status"=>1),array("openid"=>self::$openid));
            return 0;
        }
        return 0;
    }
    protected static function createParam ($paramArr,$showapi_secret) {
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
}
