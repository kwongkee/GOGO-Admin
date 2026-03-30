<?php
header('Content-Type:text/html;charset=utf-8');

$stime = '1536532200';
$etime = '1536766200';

//Call($stime,$etime);


/**
	 * 开发步骤：
	 * 1、判断进入时间是否大于分段开始日期；大于分段开始日期就拿进入时间判断是否小于结束日期；
	 * 2、判断
	 * 
	 * 08:00  20:00
		---------------------
		2018-09-10 06:30  2018-09-12 15:30
		
		2018-09-10 06:30  08:00
		           08:00  20:00
		                     
		2018-09-10 20:00  2018-09-11 08:00
		2018-09-11 08:00  20:00
		           20:00  2018-09-12 08:00
		2018-09-12 08:00  15:30
	 */
	
/**
 * 计算分段
 * $stime  进入时间搓
 * $etime  使出时间搓
 */
function Call($inTime,$outTime) {
	$start = '08:00';//分段开始
	$end   = '20:00';//分段结束
	$dayend   = '23:59';//日时分段
	$temp  	  = $start;//临时变量跟随指针往下移；开始的变量  分段开始
	
	//当天结束日期  年月日时分秒   例如：2010-09-10 23:59:59
	$day = date('Y-m-d '.$dayend,$inTime);
	//转换当前开始分段日期时间搓
	//分段开时间搓
	$startTotime = strtotime(date('Y-m-d '.$start,$inTime));
	//$endTotime	 = strtotime(date('Y-m-d '.$end,$inTime));
//	$tempArr = [];
	//如果进入时间搓，小于分段开始，就判断
	if($inTime < $startTotime) {
		$tempArr[1][] = date('Y-m-d H:i:s',$startTotime);
		$temp  =  $end;//指针指向后一个
		$endTotime	 = strtotime(date('Y-m-d '.$temp,$inTime));
		if($startTotime < $endTotime) {//
			$tempArr[1][] = date('Y-m-d H:i:s',$endTotime);
		} else {
			$tempArr[2][] = $day;
		}
	}
	echo '<pre>';
	print_r($tempArr);
	
}




$money = 0;
$minute = 0;
$start = '1536532231';
$end   = '1536737431';
echo '<pre>';

echo date("Y-m-d H:i",$start)."进入时间\n";

echo date("Y-m-d H:i",$end)."离开时间\n";
$minute     = null;
$charge = json_decode('[{"period_name":"\u767d\u5929","starTime":"08:00","endTime":"19:59","free":"30","minute":"30","price":"2.5","capped":"30","y_minute_new":"60","addMinus":"30"},{"period_name":"\u591c\u665a","starTime":"20:00","endTime":"07:59","free":"30","minute":"60","price":"2","capped":"8","y_minute_new":"60","addMinus":"30"}]',true);

$inTime     = date("H:i", $start);//进入小时分时间
$outTime    = date("Y-m-d H:i", $end);//离开小时分时间
$tmpHour    = floor(($end-$start)/60/60);
$enTim      = ['d'=>date("Y-m-d",$start),'time'=>date("H:i",$start)];//存放拆分结束时间

// if ($tmpHour>24){
//     //中间两天
//     $tmpHour = floor($tmpHour/24);
//     $enTim  =date('Y-m-d H:i',$start+($tmpHour*86400));//指定时间戳+1天 2017-01-10 21:10:16
//     $money  +=$tmpHour*30; // 全天金额
//     $minute += $tmpHour*1440;//全天时间
// }

echo $tmpHour."-24小时数\n";
echo $enTim."\n";

$list = [];
$isEnd = true;
while ($isEnd){
    var_dump($enTim);
    foreach ($charge as $key=> $value){
        $min  = 0;
        $moey = 0;
        $periodS  = explode(":",$value['starTime']);
    
        $periodE  = explode(":",$value['endTime']);
        if ($enTim['d']<strtotime('Y-m-d',$outTime)){
            if (date('H:i',strtotime($enTim))>$value['starTime']&&date('H:i',strtotime($enTim))<=$value['endTime']){
                //段相减
                $min = (abs($periodE[0])-abs($periodS[0]))*60+$periodE[1];
                array_push($list,$min);
                if($min<$value['free']){
                    $min =0;
                }
                $minute +=$min;
                $moey  = ceil(($min/$value['minute']))*$value['price'];
                if($moey>$value['capped']){
                    $moey = $value['capped'];
                }
                $money +=$moey;
                if($key==count($charge)-1){
                
                    $enTim['d']  =  date('Y-m-d',(strtotime($enTim['d'])+86400));
                    $enTim['time'] = $value['endTime'];
                }
            }else{

                $min = (strtotime('H',$enTim)-$periodS[0])*60+strtotime('i',$enTim);
                array_push($list,$min);
                if($min<$value['free']){
                    $min =0;
                }
                $minute +=$min;
                $moey  = ceil(($min/$value['minute']))*$value['price'];
                if($moey>$value['capped']){
                    $moey = $value['capped'];
                }
                $money +=$moey;
                if($key==count($charge)-1){
                    $enTim['d']  =  date('Y-m-d',(strtotime($enTim['d'])+86400));
                    $enTim['time'] = $value['endTime'];
                }

                //结束段减开始
            }
          
        }
        if ($enTim['d']>=strtotime('Y-m-d',$outTime)){
            var_dump($enTim);
            //结束段
            $min = (date('H',strtotime($outTime))-explode(":",$enTim['time'])[0])*60+(explode(":",$enTim['time'])[1]-date('i',strtotime($outTime)));
            array_push($list,$min);
            if($min<$value['free']){
                $min +=0;
            }
            $minute +=$min;
            $moey  += ceil(($min/$value['minute']))*$value['price'];
            if($moey>$value['capped']){
                $moey = $value['capped'];
            }
            $money +=$moey;
            $isEnd  = false;
        
        }
        
    }
}

echo $minute."时间\n";
echo $money."金额\n";


var_dump($list);




?>