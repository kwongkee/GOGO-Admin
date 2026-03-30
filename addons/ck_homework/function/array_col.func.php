<?php
/**
 * Created by PhpStorm.
 * User: Atsuyoshi
 * Date: 2018/4/2
 * Time: 10:29
 */

/**
 * array_column 兼容
 * @param $arr
 * @param string $col
 * @param string $key
 * @return array|bool
 */
function array_col($arr,$col=NULL,$key=NULL){
    if(empty($arr) || !is_array($arr)){return false;}
    foreach($arr as $val){
        if(!is_array($val)){return false;}
    }
    if(function_exists('array_column')){
        $data = array_column($arr,$col,$key);
    }else{
        if(!is_null($col) && !is_null($key)){
            foreach($arr as $val){
                $data[$val[$key]] = $val[$col];
            }
        }elseif(!is_null($col)){
            foreach($arr as $val){
                $data[] = $val[$col];
            }
        }else{
            $data = $arr;
        }
    }
    return $data;
}
