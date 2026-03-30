<?php


if (!defined('IN_IA')) {
    exit('Access Denied');
}
global $_W, $_GPC;

$id = $_GPC['cartids'];
if ($id == "") {
    show_json(-1, '请选择商品');
}
$goodsId = pdo_fetchall('select goodsid from '.tablename('sz_yi_member_cart').' where id in('.$id.')');
if(count($goodsId)==1){
    show_json(0,'暂无');
}
$gid = '';
foreach ($goodsId as $val) {
    $gid .= $val['goodsid'].',';
}
$gid = rtrim($gid, ',');
$sql = "select a1.title,a2.HSCode from ".tablename('sz_yi_goods')." as a1 left join ".tablename('foll_goodsreglist')." as a2 on a1.goodssn=a2.EntGoodsNo where a1.id in({$gid})";
$hsCode = pdo_fetchall($sql);
$lojinHsCode = '';
$goodsName = [];
foreach ($hsCode as $value) {
    $lojinHsCode .= $value['HSCode'].',';
    $goodsName[$value['HSCode']] =$value['title'];
}
$lojinHsCode = rtrim($lojinHsCode, ',');
$sql = "select * from ".tablename('sz_yi_dismantling_conditions')." where hs_code in({$lojinHsCode})";
$isSplitOrder = pdo_fetchall($sql);
$message = '';
foreach ($isSplitOrder as $value) {
    $message .= $goodsName[$value['hs_code']].',';
}
$message = rtrim($message,',');
if (empty($message)){
    show_json(0,'暂无');
}
show_json(-1,"不可拼单商品:".$message);