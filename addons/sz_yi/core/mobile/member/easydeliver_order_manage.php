<?php
// 模块LTD提供
if (!defined('IN_IA')) {
    exit('Access Denied');
}

global $_W;
global $_GPC;
$operation = (!empty($_GPC['op']) ? $_GPC['op'] : 'display');
$type = $_GPC['type'];
$openid = m('user')->getOpenid();
$uniacid = $_W['uniacid'];
$r_type = array('退款', '退货退款', '换货');
$plugin_yunbi = p('yunbi');

if ($plugin_yunbi) {
    $yunbi_set = $plugin_yunbi->getSet();
}

if ($_W['isajax']) {
    if ($operation == 'display') {
        $pindex = max(1, intval($_GPC['page']));
        $psize = 5;
        $status = $_GPC['status'];
        $condition = ' and openid=:openid  and userdeleted=0 and deleted=0 and uniacid=:uniacid ';
        $params = array(':uniacid' => $uniacid, ':openid' => $openid);

        if ($status != '') {
            if ($status != 4) {
                if ($status == 2) {
                    $condition .= ' and (status=2 or status=0 and paytype=3)';
                }
                else if ($status == 0) {
                    $condition .= ' and status=0 and paytype!=3';
                }
                //peng 20170331 添加已退款订单列表
                else if($status == 5){
                    $condition .=' and refundtime<>0 ';
                }
                //END
                else {
                    $condition .= ' and status=' . intval($status);
                }
            }
            else {
                $condition .= ' and refundstate>0 and status!=-1';
            }
        }
        else {
            $condition .= ' and status<>-1';
        }

        if (p('hotel') && ($type == 'hotel')) {
            $condition .= ' AND order_type=3';
        }
        else {
            $condition .= ' AND order_type<>3';
        }

        $conds = '';

        if (p('channel')) {
            $conds = ',ischannelself';
        }

        $list = pdo_fetchall('select * from ' . tablename('sz_yi_order') . ' where 1 ' . $condition . ' group by ordersn_general order by createtime desc LIMIT ' . (($pindex - 1) * $psize) . ',' . $psize, $params);
        $total = pdo_fetchcolumn('select count(*) from ' . tablename('sz_yi_order') . ' where 1 ' . $condition, $params);
        $tradeset = m('common')->getSysset('trade');
        $refunddays = intval($tradeset['refunddays']);
        $ordersn_general = '';
        $p_cashier = p('cashier');

        foreach ($list as $key => &$row) {
            if (p('hotel')) {
                if ($type == 'hotel') {
                    $list[$key]['btime'] = date('Y-m-d', $row['btime']);
                    $list[$key]['etime'] = date('Y-m-d', $row['etime']);
                }
            }

            if (!empty($row['ordersn_general']) && ($row['status'] == 0)) {
                $ordersn_general = $row['ordersn_general'];
                $row['ordersn'] = $row['ordersn_general'];
                $orderids = pdo_fetchall('select distinct id from ' . tablename('sz_yi_order') . ' where ordersn_general=:ordersn_general and uniacid=:uniacid and openid=:openid', array(':ordersn_general' => $ordersn_general, ':uniacid' => $uniacid, ':openid' => $openid), 'id');
                $row['price'] = pdo_fetchcolumn('select sum(price) from ' . tablename('sz_yi_order') . ' where ordersn_general=:ordersn_general and uniacid=:uniacid and openid=:openid', array(':ordersn_general' => $ordersn_general, ':uniacid' => $uniacid, ':openid' => $openid));
                $orderid_where_in = implode(',', array_keys($orderids));
                $order_where = 'og.orderid in (' . $orderid_where_in . ')';
            }
            else {
                $order_where = 'og.orderid = ' . $row['id'];
            }

            $channel_cond = '';

            if (p('channel')) {
                if ($row['ischannelself'] == 1) {
                    $row['ordertype'] = '自提单';
                }

                $channel_cond = ',og.ischannelpay';
            }

            $sql = 'SELECT og.goodsid,og.total,g.type,g.title,g.thumb,og.price,og.optionname as optiontitle,og.optionid' . $channel_cond . ' FROM ' . tablename('sz_yi_order_goods') . ' og ' . ' left join ' . tablename('sz_yi_goods') . ' g on og.goodsid = g.id ' . ' where ' . $order_where . ' order by og.id asc';
            $row['goods'] = set_medias(pdo_fetchall($sql), 'thumb');

            foreach ($row['goods'] as $k => $value) {
                if (is_array($value)) {
                    if ($value['ischannelpay'] == 1){
                        $row['ordertype'] = '采购单';
                    }
                    
                }

                break;
            }

            if ($p_cashier) {
                $row['name'] = set_medias(pdo_fetch('select cs.name,cs.thumb from ' . tablename('sz_yi_cashier_store') . 'cs ' . 'left join ' . tablename('sz_yi_cashier_order') . ' co on cs.id = co.cashier_store_id where co.order_id=:orderid and co.uniacid=:uniacid', array(':orderid' => $row['id'], ':uniacid' => $_W['uniacid'])), 'thumb');
            }

            $row['goodscount'] = count($row['goods']);
            $row['createtime'] = date('Y-m-d H:i:s', $row['createtime']);

            switch ($row['status']) {
                case '-1':
                    $status = '已取消';
                    break;

                case '0':
                    if ($row['paytype'] == 3) {
                        $status = '待发货';
                    }
                    else {
                        $status = '待付款';
                    }

                    break;

                case '1':
                    if ($row['isverify'] == 1) {
                        $status = '待使用';
                    }
                    else if (empty($row['addressid'])) {
                        $status = '待取货';
                    }
                    else {
                        $status = '待发货';
                    }

                    break;

                case '2':
                    $status = '待收货';
                    break;

                case '3':
                    if (empty($row['iscomment'])) {
                        $status = '待评价';
                    }
                    else {
                        $status = '交易完成';
                    }

                    break;
            }

            if (p('hotel') && ($type == 'hotel')) {
                switch ($row['status']) {
                    case '-1':
                        $status = '已取消';
                        break;

                    case '0':
                        if ($row['paytype'] == 3) {
                            $status = '待发货';
                        }
                        else {
                            $status = '待付款';
                        }

                        break;

                    case '1':
                        if ($row['isverify'] == 1) {
                            $status = '待使用';
                        }
                        else if (empty($row['addressid'])) {
                            $status = '待取货';
                        }
                        else {
                            $status = '待确认';
                        }

                        break;

                    case '2':
                        $status = '待入住';
                        break;

                    case '6':
                        $status = '待退房';
                        break;

                    case '3':
                        if (empty($row['iscomment'])) {
                            $status = '待评价';
                        }
                        else {
                            $status = '交易完成';
                        }

                        break;
                }
            }

            $row['statusstr'] = $status;
            if ((0 < $row['refundstate']) && !empty($row['refundid'])) {
                $refund = pdo_fetch('select * from ' . tablename('sz_yi_order_refund') . ' where id=:id and uniacid=:uniacid and orderid=:orderid limit 1', array(':id' => $row['refundid'], ':uniacid' => $uniacid, ':orderid' => $row['id']));

                if (!empty($refund)) {
                    $row['statusstr'] = '待' . $r_type[$refund['rtype']];
                }
            }

            $canrefund = false;
            if (($row['status'] == 1) || ($row['status'] == 2)) {
                if ((0 < $refunddays) || ($row['status'] == 1)) {
                    $canrefund = true;
                }
            }
            else {
                if ($row['status'] == 3) {
                    if (0 < $refunddays) {
                        $days = intval((time() - $row['finishtime']) / 3600 / 24);

                        if ($days <= $refunddays) {
                            $canrefund = true;
                        }
                    }
                }
            }

            $row['canrefund'] = $canrefund;

            if ($canrefund == true) {
                if ($row['status'] == 1) {
                    $row['refund_button'] = '申请退款';
                }
                else {
                    $row['refund_button'] = '申请售后';
                }

                if (!empty($row['refundstate'])) {
                    $row['refund_button'] .= '中';
                }
            }
        }

        unset($row);
        show_json(1, array('total' => $total, 'list' => $list, 'pagesize' => $psize));
    }elseif($operation=='pay'){
        $orderRes = pdo_get('sz_yi_order',['id'=>$_GPC['orderid']],['uniacid','ordersn','price']);
        if(empty($orderRes)){
            show_json(1,'支付失败');
        }else{
            $orderRes['ordersn']='GC'.generateOrderSn();
            pdo_update('sz_yi_order',['ordersn'=>$orderRes['ordersn']],['id'=>$_GPC['orderid']]);
        }
        $data = [
            'tid' => $orderRes['ordersn'], // 订单编号
            'opid' => $_W['openid'],// 用户openid
            'fee' => $orderRes['price'], // 订单交易金额
            'title' => '个人用品',// 订单商品名称
            'uniacid' => $_W['uniacid'], // 公众号所属id
            'returnUrl'=>'',//回调地址
        ];
        if($_GPC['type']=='wx'){
            $data['url']=$_W['siteroot'].'/addons/sz_yi/payment/tgwechat/Wechat.php';
            $data['returnUrl'] = $this->createMobileUrl('order/easy_deliver_paysuccess');
            $data['returnUrl'] = $data['returnUrl'] . "&oid=" . $orderRes['ordersn'];
            show_json(0,$data);
        }else if($_GPC['type']=='ali'){
            $url['payParam'] = $data;
            $url['ajaxReqUrl']=$_W['siteroot'].'/addons/sz_yi/payment/tgwechat/Alipay.php';
            $url['locaUrl']=$_W['siteroot'].'/addons/sz_yi/payment/tgwechat/Alipays.php';
            $url['payParam']['returnUrl'] = $this->createMobileUrl('order/easy_deliver_paysuccess');
            $url['payParam']['returnUrl'] = $url['payParam']['returnUrl'] . "&oid=" . $orderRes['ordersn'];
            show_json(0,$url);
        }
        snow_json(1,'支付失败');
    }
}

if (p('hotel')) {
    include $this->template('member/easydeliver_order_manage');
    return 1;
}

include $this->template('member/easydeliver_order_manage');

?>
