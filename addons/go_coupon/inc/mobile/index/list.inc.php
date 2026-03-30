<?php

defined('IN_IA') or exit('Access Denied');

require_once IA_ROOT . "/framework/class/curl.class.php";

global $_GPC, $_W;


/**
 * 通过接口获取回来
 * @param $uniacid
 * @param $page
 * @return mixed
 */
function get_coupon($unionid, $uniacid, $pageNum, $pageSize)
{
    $curl = new Curl();
    $curl->setHeader('Content-type', 'application/json;charset=utf-8');
    $curl->post('http://shop.gogo198.cn/foll/public/?s=api_v2/coupon/getCoupon', json_encode([
        'uniacid' => $uniacid,
        'pageNum' => $pageNum,
        'pageSize' => $pageSize,
        'unionid' => $unionid
    ]));
    return $curl->response;
}

/**
 * 领取
 * @param $cid
 * @param $unionid
 * @return mixed
 */
function receive_coupon($cid, $unionid)
{

    $curl = new Curl();
    $curl->setHeader('Content-type', 'application/json;charset=utf-8');
    $curl->post('http://shop.gogo198.cn/foll/public/?s=api_v2/coupon/receive', json_encode([
        'unionid' => $unionid,
        'cid'=>$cid,
        'receive_time'=>time(),
    ]));
    return $curl->response;
}

if ($_W['isajax']) {
    if ($_GPC['p'] != "receive") {
        $list = get_coupon($_W['fans']['unionid'], $_W['uniacid'], $_GPC['pageNum'], $_GPC['pageSize']);
        $list = json_decode($list, true);
        $list = $list['result'];
        exit(json_encode(['curPageData' => $list['data'], 'totalSize' => $list['totalSize']]));
    }
    $result = receive_coupon($_GPC['cid'],$_W['fans']['unionid']);
    exit($result);
}

$url = $this->createMobileUrl('index/list');
include $this->template("index/list");