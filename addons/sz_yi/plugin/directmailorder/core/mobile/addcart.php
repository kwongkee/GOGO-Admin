<?php

global $_W;
global $_GPC;

$id       = intval($_GPC['id']);
$total    = intval($_GPC['total']);
$openid = $_W['openid'];
$uniacid = $_W['uniacid'];
$goods    = pdo_fetch('select id,marketprice,hasoption,`type`, total from '.tablename('sz_yi_goods').' where uniacid=:uniacid and id=:id limit 1', [':uniacid' => $uniacid, ':id' => $id]);
if (empty($_GPC['diyformdata']['diydata']['diymaijiaxingming'])||empty($_GPC['diyformdata']['diydata']['diyshenfenzhenghao'])){
    show_json(0,'身份信息为空');
}
if (empty($goods)) {
    show_json(0, '商品未找到');
}

if ($goods['maxbuy']>0&&$total > $goods['maxbuy']) {
    show_json(0, '已超出最大限购'.$goods['maxbuy']);
}

$diyform_plugin = p('diyform');
$datafields     = 'id,total';
if ($diyform_plugin) {
    $datafields .= ',diyformdataid';
}
$data          = pdo_fetch('select '.$datafields.' from '.tablename('sz_yi_member_cart').' where openid=:openid and goodsid=:id  and deleted=0 and  uniacid=:uniacid   limit 1', [':uniacid' => $uniacid, ':openid' => $openid, ':id' => $id]);
$diyformdataid = 0;
$diyformfields = iserializer([]);
$diyformdata   = iserializer([]);
if ($diyform_plugin) {
    $diyformdata = $_GPC['diyformdata'];
    if (!empty($diyformdata) && is_array($diyformdata)) {
        $diyformid = intval($diyformdata['diyformid']);
        $diydata   = $diyformdata['diydata'];
        if (!empty($diyformid) && is_array($diydata)) {
            $formInfo = $diyform_plugin->getDiyformInfo($diyformid);
            
            if (!empty($formInfo)) {
                $diyformfields = $formInfo['fields'];
                $insert_data   = $diyform_plugin->getInsertData($diyformfields, $diydata);
                $idata         = $insert_data['data'];
                $diyformdata   = $idata;
                $diyformfields = iserializer($diyformfields);
            }
        }
    }
}

if (empty($data)) {
    if ($goods['total'] < intval($total)) {
        show_json(0, ['message' => '您最多购买'.$goods['total'].'件']);
    }
    $data = ['uniacid'     => $uniacid, 'openid' => $openid, 'goodsid' => $id, 'optionid' => 0,
             'marketprice' => $goods['marketprice'], 'total' => $total, 'diyformid' => $diyformid,
             'diyformdata' => $diyformdata, 'diyformfields' => $diyformfields, 'createtime' => time(),
    ];
    pdo_insert('sz_yi_member_cart', $data);
    show_json(1, ['message' => '添加成功', 'cartcount' => $total]);
} else {
    $data['total'] += $total;
    if ($goods['total'] < intval($data['total'] + $total)) {
        show_json(0, ['message' => '您最多购买'.$goods['total'].'件']);
    }
    pdo_update('sz_yi_member_cart', ['total' => $data['total']], ['uniacid' => $uniacid, 'goodsid' => $id, 'openid' => $openid]);
    show_json(1, ['message' => '添加成功', 'cartcount' => $data['total']]);
}