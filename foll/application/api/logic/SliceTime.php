<?php

namespace app\api\logic;

use think\Model;

class SliceTime extends Model
{
    
    
    public $tem;
    public $periodIndex;
    /**
     * 分段
     * @param $start
     * @param $end
     * @param $charge
     */
    public function timeBlock ( $start, $end, $charge )
    {
        
        $start1 = $start - 86400; //进入时间
        $end1   = $end + 86400; //结束时间
        
        $timeStart = $start1; //临时自增时间i
        
        $n           = null;
        $list        = [];
        
        //判断入场在那个段
        foreach ($charge as $key => $value) {
            $periodPars = explode(":", $value['starTime']);
            $periodPare = explode(":", $value['endTime']);
            $round      = $this->between_hour($periodPars[0], $periodPare[0]);
            if ( in_array(date('H', $start), $round) ) {
                $this->tem   = $value;
                $this->periodIndex = $key;
            }
        }
        
        //进行分段
        
        while ($timeStart < $end1) {
            $i = date('Y-m-d', $timeStart);
            if ( $this->periodIndex == 0 ) {
                foreach ($charge as $key => $value) {
                    
                    if ( $key == (count($charge) - 1) ) {
                        // array_push($list, [$i . ' ' . $tem['starTime'], date('Y-m-d', strtotime($i)) . ' ' . date('H:i', $start)]);
                        array_push($list, [$i . ' ' . $value['starTime'], date('Y-m-d', strtotime($i) + 86400) . ' ' . $value['endTime']]);
                    } else {
                        array_push($list, [$i . ' ' . date("H:i", $start), date('Y-m-d', strtotime($i)) . ' ' . $value['endTime']]);
                    }
                    
                }
            } else {
                if ( $i == date('Y-m-d', $start1) ) {
                    array_push($list, [$i . ' ' . $this->tem['starTime'], date('Y-m-d', strtotime($i) + 86400) . ' ' . $this->tem['endTime']]);
                    $n += 1;
                } else {
                    foreach ($charge as $key => $value) {
                        if ( $n == count($charge) ) {
                            // array_push($list, [$i . ' ' . $tem['starTime'], date('Y-m-d', strtotime($i)) . ' ' . date('H:i', $start)]);
                            array_push($list, [$i . ' ' . $value['starTime'], date('Y-m-d', strtotime($i) + 86400) . ' ' . $value['endTime']]);
                            $n = 0;
                        } else {
                            array_push($list, [$i . ' ' . $value['starTime'], date('Y-m-d', strtotime($i)) . ' ' . $value['endTime']]);
                        }
                        $n += 1;
                    }
                }
            }
            
            $timeStart += 86400;
        }
        return $list;
    }
    
    
    /**
     * 更改开始时间
     * @param $start
     * @param $list
     */
    public function modifyStartTime ( $start, $list )
    {
        //更改入场时间
        foreach ($list as $key => $value) {
            
            if ( date("Y-m-d H:i", $start) >= $value[0] && date("Y-m-d H:i", $start) <= $value[1] ) {
                
                $list[$key][0] = null;
                $list[$key][0] = date('Y-m-d H:i', $start);
                break;
            } else {
                unset($list[$key]);
            }
        }
        
        return $list;
    }
    
    /**
     * 更改离开时间
     * @param $end
     * @param $list
     */
    public function modifyEndTime ( $start, $end, $list )
    {
        //结束倒序
        $num = count($list);
        
        for ($i = $num - 1; $i >= 0; $i--) {
            if ( date("Y-m-d H:i", $end) >= $list[$i][0] && date("Y-m-d H:i", $start) <= $list[$i][1] ) {
                $list[$i][1] = date('Y-m-d H:i', $end);
                if ($this->periodIndex==0|| date('H:i', $start) < date("H:i", strtotime($list[$i][1]))){
                    $list[$i][0] = date('Y-m-d', strtotime($list[$i][0])) . ' ' . date('H:i', strtotime($this->tem['starTime']));
                }
                break;
            } else {
                unset($list[$i]);
            }
        }
        return $list;
    }
    
    /**
     * 重组新的时间段
     */
    public function generateNewList ( $start, $end, $charge, $list )
    {
        
        $cNum          = count($charge);
        $step          = 0;
        $tempListArray = [];
        foreach ($list as $key => $value) {
            if ( $key == max($list) ) {
                continue;
            }
            if ( $step == $cNum && $key != (count($list) - 1) ) {
                if (intval(date("H", $start)) <= 7) {
                    array_push($tempListArray, [date("Y-m-d", strtotime($value[0])) . ' ' . $this->tem['starTime'], date("Y-m-d", strtotime($value[0]) + 86400) . ' ' . date("H:i", ($start - 60))]);
                }else{
                    array_push($tempListArray, [date("Y-m-d", strtotime($value[0])) . ' ' . $this->tem['starTime'], date("Y-m-d", strtotime($value[0])) . ' ' . date("H:i", $start)]);
                }
                $step = 0;
            }
            array_push($tempListArray, $value);
            $step += 1;
        }
        
        $new_list = [];
        $indexNum = 0;
        $Hours    = 0;
        //按收费段重组列表
        foreach ($tempListArray as $key => $value) {
            foreach ($charge as $keys => $val) {
                $periodBeet = $this->between_hour(explode(":", $val['starTime'])[0], explode(":", $val['endTime'])[0]);
                if ( in_array(date('H', strtotime($value[0])), $periodBeet) && in_array(date('H:i', strtotime($value[1])), $periodBeet) ) {
                    $Hours                 += 1;
                    $new_list[$indexNum][] = $value;
                }
            }
            if ( $Hours == count($charge) + 1 ) {
                $Hours    = 0;
                $indexNum += 1;
            }
            
        }
        return $new_list;
    }
    
    /*返回小时自增数*/
    protected function between_hour ( $s, $e )
    {
        $s         = abs($s);
        $e         = abs($e);
        $hourArray = [];
        if ( $s > $e ) {
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
    
}