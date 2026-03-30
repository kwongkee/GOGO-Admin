<?php


// 模块LTD提供
if (!defined('IN_IA')) {
    exit('Access Denied');
}


global $_W;
global $_GPC;

if (!isset($_GPC['code'])||$_GPC['code']==""){
    message('参数错误');
}
if (filter_var($_GPC['code'],FILTER_VALIDATE_URL)){
    header('Location:'.$_GPC['code']);
}else{
    $qrcode = explode(",",$_GPC['code']);
    $goodssn = pdo_get('foll_goodsreglist',['BarCode'=>$qrcode[1]],['EntGoodsNo']);
    if (empty($goodssn)){
        message('暂无该商品!');
    }
    $goodsid= pdo_get('sz_yi_goods',['goodssn'=>$goodssn['EntGoodsNo']],['id','uniacid']);
    if (!empty($goodsid)){
        header('Location:'.$_W['siteroot'].'/app/index.php?i='.$goodsid['uniacid'].'&c=entry&p=directmailorder&method=detail&id='.$goodsid['id'].'&do=plugin&m=sz_yi');
    }else{
        message('暂无该商品!');
    }

}
