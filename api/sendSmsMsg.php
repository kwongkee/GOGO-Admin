<?php
/**
 * 用于法人收到短信后，点击打开链接进行跳转支付宝身份认证
 * 2022-05-26
 **/
include "../framework/bootstrap.inc.php";
include "../framework/class/weixin.account.class.php";

$id = base64_decode($_GET['mbe']);
$res = pdo_fetch('select * from '.tablename('enterprise_legaler_verify').' where id=:id',[':id'=>$id]);
if(!empty($res)){
    echo htmlspecialchars_decode($res['html_info'],ENT_QUOTES);die;
//    print_r($res['html_info']);die;
}else{
    echo '<div style="text-align: center;">
        <img src="https://shop.gogo198.cn/attachment/error.png" style="margin-top:30px;">
        <div class="msg" style="background:#ff2222;color:#fff;font-size: 34px;padding: 15px 30px;box-sizing:border-box;margin: 30px auto;border-radius: 5px;">查无数据！</div>
      </div>';exit;
}

