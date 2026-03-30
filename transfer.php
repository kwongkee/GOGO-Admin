<?php
$num=$_GET['num'];
$i=$_GET['i']?14:$_GET['i'];
switch ($_GET['model']){
    case "park":
        header("Location:http://shop.gogo198.cn/app/index.php?i={$i}&c=entry&m=ewei_shopv2&do=mobile&r=parking.info&num=".$num);
        break;
    case "card":
        header("Location:http://shop.gogo198.cn/app/index.php?i={$i}&c=entry&m=ewei_shopv2&do=mobile&r=parking.auth");
        break;
    default:
        exit("404");
}