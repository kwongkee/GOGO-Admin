<?php
/**
 * Created by PhpStorm.
 * User: Atsuyoshi
 * Date: 2018/6/7 0007
 * Time: 22:10
 */

/**
 * @param int|float|string $price
 * @param bool $returnFen
 * @return float
 */
function yuan($price = 0, $returnFen = false){
    $price = strval(floatval($price));
    $l = strlen($price);
    $p = strpos($price,'.');
    if($p!==false){
        if(($p+2)<$l){
            $p = $p+3;
        }elseif(($p+1)<$l){
            $p = $p+2;
        }
        $price = substr($price, 0, $p);
    }
    $price = round(floatval($price),2);
    if($returnFen){
        $price = $price * 100;
    }
    return $price;
}