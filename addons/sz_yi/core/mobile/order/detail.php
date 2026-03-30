<?php
// 模块LTD提供
if (!defined('IN_IA')) {
	exit('Access Denied');
}

global $_W;
global $_GPC;
$operation = (!empty($_GPC['op']) ? $_GPC['op'] : 'display');

if( $operation == 'form_manage' )
{
	@session_start();
	$cookieid = '__cookie_sz_yi_userid_18';
	setcookie($cookieid, '', time() - 1);
	$_COOKIE[$cookieid] = '';
	$info = pdo_fetch('select * from ' . tablename('sz_yi_member') . ' where  mobile=:mobile and uniacid=:uniacid and pwd=:pwd limit 1', array(':uniacid' => 18, ':mobile' => $_GPC['account'], ':pwd' => md5('888888')));
	if ($info) {
		$lifeTime = 24 * 3600 * 3;
		session_set_cookie_params($lifeTime);
        $cookieid = '__cookie_sz_yi_userid_18';
		setcookie($cookieid, base64_encode($info['openid']));
		setcookie('member_mobile', $info['mobile']);
        setcookie('member_id', $info['id']);
        $_SESSION['level_id'] = $info['level'];
		$url = "https://shop.gogo198.cn/app/index.php?i=18&c=entry&do=order&m=sz_yi&p=detail&id=".$_GPC['id'];
		header("Location: ".$url);
	}
}
$openid = m('user')->getOpenid();
$uniacid = $_W['uniacid'];
$orderid = intval($_GPC['id']);
$shopset = m('common')->getSysset('shop');
$diyform_plugin = p('diyform');
$orderisyb = pdo_fetch('select ordersn_general,status,supplier_uid,addressid,carrier,address,trade_id,paytype,ordersn from ' . tablename('sz_yi_order') . ' where id=:id and uniacid=:uniacid and openid=:openid limit 1', array(':id' => $orderid, ':uniacid' => $uniacid, ':openid' => $openid));
// 卖家信息，2021/07/21修改
$seller = pdo_fetch('SELECT * FROM ' . tablename('decl_user') . ' WHERE supplier = :uid', array(':uid' => $orderisyb['supplier_uid']));
//查找卖家信用代码
$seller['creditNo'] = pdo_fetchcolumn('select creditNo from '.tablename('enterprise_basicinfo').' where member_id=:enterprise_id',array(':enterprise_id'=>$seller['enterprise_id']));
if (empty($orderisyb['addressid'])) {
	$user = unserialize($orderisyb['carrier']);
}
else {
	$user = iunserializer($orderisyb['address']);
	
	if (!is_array($user)) {
		$user = pdo_fetch('SELECT * FROM ' . tablename('sz_yi_member_address') . ' WHERE id = :id and uniacid=:uniacid', array(':id' => $orderisyb['addressid'], ':uniacid' => $_W['uniacid']));
		$address_info = $user['address'];
    	$user['address'] = $user['province'] . ' ' . $user['city'] . ' ' . $user['area'] . ' ' . $user['address'];
    	$user['email'] = $openid;
	}else{
	    $orderisyb['address'] = $user['address'];
	}

	
	$orderisyb['addressdata'] = array('realname' => $user['realname'], 'mobile' => $user['mobile'], 'address' => $user['address']);
}
if(!empty($orderisyb['seller_id']) && !empty($orderisyb['seller_type'])){
    if($orderisyb['seller_type']==1){
        $seller = pdo_fetch('select company_name,company_email as user_email,company_tel as user_tel,company_address as address from '.tablename('decl_user_enterprise_seller').' where id=:id',array(':id'=>$orderisyb['seller_id'])); 
    }else{
        $seller = pdo_fetch('select first_name,last_name,email as user_email,tel as user_tel,address from '.tablename('decl_user_personal_seller').' where id=:id',array(':id'=>$orderisyb['seller_id'])); 
        $seller['company_name'] = $seller['first_name'].$seller['last_name'];
    }
}

// 平台订单信息
$trade = pdo_fetch('SELECT * FROM ' . tablename('decl_user_trade_platform') . ' WHERE id = :id', array(':id' => $orderisyb['trade_id']));
//发货单位信息
if(!empty($trade['deliver_id'])){
    $deliver = pdo_fetch('select * from '.tablename('decl_user_logistics_unit').' where id=:id',[':id'=>$trade['deliver_id']]);
    if($deliver['company_name']!=$seller['company_name']){
        $deliver['company_name'].='  [代为发货]';
    }else{
        $deliver['company_name'].='  [自主发货]';
    }
    $del_country = pdo_fetchcolumn('select code_name from '.tablename('country_code').' where code_value=:code',[':code'=>$deliver['country_code']]);
    $deliver['country'] = $del_country.'/'.$deliver['country_code'];
}
//收货单位信息
if(!empty($trade['receive_id'])){
    $receive = pdo_fetch('select * from '.tablename('decl_user_logistics_unit').' where id=:id',[':id'=>$trade['receive_id']]);
    if($receive['company_name']!=$user['realname']){
        $receive['company_name'].='  [代为收货]';
    }else{
        $receive['company_name'].='  [自主收货]';
    }
    $rec_country = pdo_fetchcolumn('select code_name from '.tablename('country_code').' where code_value=:code',[':code'=>$receive['country_code']]);
    $receive['country'] = $rec_country.'/'.$receive['country_code'];
}
//查找买家编码
if($trade['buyer_type']==1){
     $enterprise_buyer = pdo_fetch('select * from '.tablename('decl_user_enterprise_buyer').' where id=:buyer_id',array(':buyer_id'=>$trade['buyer_id']));    
     $user['company_code'] = $enterprise_buyer['company_code'];
     $country = pdo_fetchcolumn('select code_name from '.tablename('country_code').' where code_value=:code_value',array(':code_value'=>$enterprise_buyer['country_code']));
     $user['country'] = $country.'/'.$enterprise_buyer['country_code'];
     if(empty($user['email']) && !empty($enterprise_buyer['company_email'])){
         $user['email'] = $enterprise_buyer['company_email'];
     }
     $user['company_name'] = $enterprise_buyer['company_name'];
}else{
    //后台个人没有编码
    $personal_buyer = pdo_fetch('select * from '.tablename('decl_user_personal_buyer').' where id=:buyer_id',[':buyer_id'=>$trade['buyer_id']]);
    $user['company_code'] = '';
    $user['address'] = $personal_buyer['address'];
    $user['tel'] = $personal_buyer['tel'];
    $user['company_name'] = $personal_buyer['first_name'].' '.$personal_buyer['last_name'];
}


$trade_order = pdo_fetch('SELECT * FROM ' . tablename('decl_user_trade_order') . ' WHERE trade_id = :trade_id', array(':trade_id' => $orderisyb['trade_id']));
$trade_order_currency = pdo_fetchcolumn('select code_name from '.tablename('currency').' where code_value=:code',[':code'=>$trade_order['currency']]);
$trade_order_goods = pdo_fetch('select country from '.tablename('decl_user_trade_order_goods').' where order_id=:oid',array(':oid'=>$trade_order['id']));
//查询运抵国
$yundi = pdo_fetch('select code_value,code_name from '.tablename('country_code').' where code_value !="000" and code_value=:code',array(':code'=>$trade_order_goods['country']));

$trade_pdf = pdo_fetch('SELECT * FROM ' . tablename('decl_user_trade_pdf') . ' WHERE trade_id = :trade_id and type="pi_invoice"', array(':trade_id' => $orderisyb['trade_id']));
$trade_po_pdf = pdo_fetch('select * from '.tablename('decl_user_trade_pdf') . ' WHERE trade_id = :trade_id and type="po_contract"', array(':trade_id' => $orderisyb['trade_id']));
//查询该订单申报时的电商企业,发货方式
$ebc = pdo_fetch('select oh.ebc_name from '.tablename('customs_export_order_list').' ol left join '.tablename('customs_export_order_head').' oh on ol.hid=oh.id where ol.ordersn=:ordersn',array(':ordersn'=>$orderisyb['ordersn']));

if($ebc['ebc_name']==$seller['company_name']){
    $fahuo = '由 ['.$ebc['ebc_name'].'] 自主发货';
}else{
    $fahuo = '由 ['.$ebc['ebc_name'].'] 代为发货';
}
//收货方式
$origin_shouhuo = pdo_fetch('SELECT * FROM ' . tablename('sz_yi_member_address') . ' WHERE id = :id and uniacid=:uniacid', array(':id' => $orderisyb['addressid'], ':uniacid' => $_W['uniacid']));
if($origin_shouhuo['realname']==$user['realname']){
    $shouhuo = '由 ['.$origin_shouhuo['realname'].'] 自主收货';
}else{
    $shouhuo = '由 ['.$user['realname'].'] 代为收货';
}

    //报关单位
    // and a.order_status=3
    $declare = pdo_fetch('select c.decl_name,d.creditNo,e.customs_code,e.port_code,e.trade_mode,e.country,e.traf_mode,e.traf_name,e.voyage_no,e.country,e.pod from '.tablename('customs_export_order_list').' a left join '.tablename('customs_export_order_head').' b on b.id=a.hid left join '.tablename('customs_portplatforminfo').' c on c.id=b.sid left join '.tablename('enterprise_basicinfo').' d on d.name=c.decl_name left join '.tablename('customs_export_declarationlist_head').' e on e.tracking_num=b.tracking_num where a.ordersn=:ordersn  order by a.create_at desc limit 1',array(':ordersn'=>$orderisyb['ordersn']));
    
    $customs_codes = pdo_fetchall('select * from '.tablename('customs_codes').' where 1');
    foreach ($customs_codes as $k => $v) {
        $vs = explode(":",$v['AreaCode']);
        $customs_codes[$k]['value_code'] = $vs[0];
        if($vs[0]==$declare['customs_code']){
            //申报地
            $declare['customs_name'] = $vs[1].'/'.$vs[0];
        }
        if($declare['port_code']==$vs[0]){
            //出境海关
            $declare['port_name'] = $vs[1].'/'.$vs[0];
        }
    }
    //监管方式
    $tradeway_name = pdo_fetchcolumn('select code_name from '.tablename('tradeway').' where code_value=:code_value',array(':code_value'=>$declare['trade_mode']));
    $declare['trade_way'] = $tradeway_name.'/'.$declare['trade_mode'];
    //贸易国地
    $country_name = pdo_fetchcolumn('select code_name from '.tablename('country_code').' where code_value=:code',[':code'=>$declare['country']]);
    $declare['country_name'] = $country_name.'/'.$declare['country'];
    //报关单位end
    
    //运输方式
    $declare['transport'] = pdo_fetchcolumn('select code_name from '.tablename('transport').' where code_value=:code',[':code'=>$declare['traf_mode']]);
    //运抵国地
    $declare['country_name'] = pdo_fetchcolumn('select code_name from '.tablename('country_code').' where code_value=:code',[':code'=>$declare['country']]);
    $declare['country_name'].='/'.$declare['country'];
    //指运港口
    $declare['pod_name'] = pdo_fetchcolumn('select code_name from '.tablename('port_code').' where code_value=:code',[':code'=>$declare['pod']]);
    $declare['pod_name'].='/'.$declare['pod'];
//2021/07/21修改end
if ($_GPC['master'] == 1) {
	$order = pdo_fetch('select * from ' . tablename('sz_yi_order') . ' where id=:id and uniacid=:uniacid limit 1', array(':id' => $orderid, ':uniacid' => $uniacid));
}
else {
	$order = pdo_fetch('select * from ' . tablename('sz_yi_order') . ' where id=:id and uniacid=:uniacid and openid=:openid limit 1', array(':id' => $orderid, ':uniacid' => $uniacid, ':openid' => $openid));
}

switch($order['status']){
    case -1:
        $order['status']='取消状态';break;
    case 0:
        $order['status']='普通状态';break;
    case 1:
        $order['status']='已付款';break;
    case 2:
        $order['status']='已发货';break;
    case 3:
        $order['status']='成功';break;
    default:
        break;
}
//2021/07/22,判断用户是否真的支付了或者在跨境结算处申请离岸收款时，总后台审核已通过，款项已入账，则把支付时间显示出来。
$ispay = pdo_fetch('select * from '.tablename('core_paylog').' where tid=:ordersn and status=1',array(':ordersn'=>$order['ordersn_general']));
if(empty($ispay['plid'])){
    $ispay = pdo_fetch('select ue.status,ue.receipt_date from '.tablename('decl_user_entry').' ue left join '.tablename('decl_user_trade_platform').' tp on tp.ordersn=ue.relation_ordersn where ue.status=1 and tp.id=:tradeid',array(':tradeid'=>$order['trade_id']));   
    if($ispay['status']!=1){
        $order['paytime'] = '';
    }else{
        $order['paytime'] = $ispay['receipt_date'] .' '. date('H:i:s', time());
    }
}else{
    $order['paytime'] = date('Y-m-d H:i:s', $order['paytime']);
}
//交易方式
$transaction_mode = pdo_fetch('select ue.trans_mode from '.tablename('decl_user_entry').' ue left join '.tablename('decl_user_trade_platform').' tp on tp.ordersn=ue.relation_ordersn where ue.status=0 and tp.id=:tradeid',array(':tradeid'=>$order['trade_id'])); 
switch($transaction_mode['trans_mode']){
    case 'EXW':
        $transaction_mode['trans_mode'] = 'EXW 工厂交付';break;
    case 'FOB':
        $transaction_mode['trans_mode'] = 'FOB 离岸交付';break;
    case 'CIF':
        $transaction_mode['trans_mode'] = 'CIF 抵岸交付';break;
    case 'CFR':
        $transaction_mode['trans_mode'] = 'CFR 船上交付';break;
    case 'FAC':
        $transaction_mode['trans_mode'] = 'FCA 承运交付';break;
    case 'DDU':
        $transaction_mode['trans_mode'] = 'DDU 税前交付';break;
    case 'DDP':
        $transaction_mode['trans_mode'] = 'DDP 税后交付';break;
    default:
        $transaction_mode['trans_mode'] = '';break;
}
if($order['finishtime']!=0){
    $order['finishtime'] = date('Y-m-d H:i:s', $order['finishtime']);
}else{
    $order['finishtime'] = '';
}
//2021/07/22,END

$yunbi_plugin = p('yunbi');

if ($yunbi_plugin) {
	$yunbiset = $yunbi_plugin->getSet();
}

if (!empty($orderisyb['ordersn_general']) && ($orderisyb['status'] == 0)) {
	$order_all = pdo_fetchall('select * from ' . tablename('sz_yi_order') . ' where ordersn_general=:ordersn_general and uniacid=:uniacid and openid=:openid', array(':ordersn_general' => $orderisyb['ordersn_general'], ':uniacid' => $uniacid, ':openid' => $openid));
	$orderids = array();
	$order['goodsprice'] = 0;
	$order['olddispatchprice'] = 0;
	$order['discountprice'] = 0;
	$order['deductprice'] = 0;
	$order['deductcredit2'] = 0;
	$order['deductenough'] = 0;
	$order['changeprice'] = 0;
	$order['changedispatchprice'] = 0;
	$order['couponprice'] = 0;
	$order['price'] = 0;

	foreach ($order_all as $k => $v) {
		$orderids[] = $v['id'];
		$order['goodsprice'] += $v['goodsprice'];
		$order['olddispatchprice'] += $v['olddispatchprice'];
		$order['discountprice'] += $v['discountprice'];
		$order['deductprice'] += $v['deductprice'];
		$order['deductcredit2'] += $v['deductcredit2'];
		$order['deductenough'] += $v['deductenough'];
		$order['changeprice'] += $v['changeprice'];
		$order['changedispatchprice'] += $v['changedispatchprice'];
		$order['couponprice'] += $v['couponprice'];
		$order['price'] += $v['price'];
	}

	$order['ordersn'] = $orderisyb['ordersn_general'];
	$orderid_where_in = implode(',', $orderids);
	$order_where = 'og.orderid in (' . $orderid_where_in . ')';
}
else {
	$order_where = 'og.orderid = ' . $orderid;
}

if (p('cashier') && ($order['cashier'] == 1)) {
	$order['name'] = set_medias(pdo_fetch('select * from ' . tablename('sz_yi_cashier_store') . ' where id=:id and uniacid=:uniacid', array(':id' => $order['cashierid'], ':uniacid' => $_W['uniacid'])), 'thumb');
}

if (!empty($order)) {
	$order['virtual_str'] = str_replace("\n", '<br/>', $order['virtual_str']);
	$diyformfields = '';

	if ($diyform_plugin) {
		$diyformfields = ',og.diyformfields,og.diyformdata';
	}

	$goods = pdo_fetchall('select g.goodssn,og.goodsid,og.price,g.title,g.thumb,og.total,g.credit,og.optionid,og.optionname as optiontitle,g.isverify,g.storeids' . $diyformfields . '  from ' . tablename('sz_yi_order_goods') . ' og ' . ' left join ' . tablename('sz_yi_goods') . ' g on g.id=og.goodsid ' . ' where ' . $order_where . ' and og.uniacid=:uniacid ', array(':uniacid' => $uniacid));
	
	$show = 1;
	$diyform_flag = 0;

	foreach ($goods as &$g) {
		$g['thumb'] = tomedia($g['thumb']);
		
		//单商品合计总价，2021/07/30修改
		$g['sum_price'] = number_format($g['total']*$g['price'],2);
		
		if(empty($g['optionid'])){
		    $g['optiontitle'] = pdo_fetchcolumn('select gmodel from '.tablename('customs_export_declarationlist_goods').' where item_no=:item_no',array(':item_no'=>$g['goodssn']));
		}
		
		if ($diyform_plugin) {
			$diyformdata = iunserializer($g['diyformdata']);
			$fields = iunserializer($g['diyformfields']);
			$diyformfields = array();
			if (!empty($fields)){
				foreach ($fields as $key => $value) {
					$tp_value = '';
					$tp_css = '';
					if (($value['data_type'] == 1) || ($value['data_type'] == 3)) {
						$tp_css .= ' dline1';
					}

					if ($value['data_type'] == 5) {
						$tp_css .= ' dline2';
					}

					if (($value['data_type'] == 0) || ($value['data_type'] == 1) || ($value['data_type'] == 2) || ($value['data_type'] == 6) || ($value['data_type'] == 7)) {
						$tp_value = str_replace("\n", '<br/>', $diyformdata[$key]);
					}
					else {
						if (($value['data_type'] == 3) || ($value['data_type'] == 8)) {
							if (is_array($diyformdata[$key])) {
								foreach ($diyformdata[$key] as $k1 => $v1) {
									$tp_value .= $v1 . ' ';
								}
							}
						}
						else if ($value['data_type'] == 5) {
							if (is_array($diyformdata[$key])) {
								foreach ($diyformdata[$key] as $k1 => $v1) {
									$tp_value .= '<img style=\'height:25px;padding:1px;border:1px solid #ccc\'  src=\'' . tomedia($v1) . '\'/>';
								}
							}
						}
						else {
							if ($value['data_type'] == 9) {
								$tp_value = ($diyformdata[$key]['province'] != '请选择省份' ? $diyformdata[$key]['province'] : '') . ' - ' . ($diyformdata[$key]['city'] != '请选择城市' ? $diyformdata[$key]['city'] : '');
							}
						}
					}

					$diyformfields[] = array('tp_name' => $value['tp_name'], 'tp_value' => $tp_value, 'tp_css' => $tp_css);
				}
			}


			$g['diyformfields'] = $diyformfields;
			$g['diyformdata'] = $diyformdata;

			if (!empty($g['diyformdata'])) {
				$diyform_flag = 1;
			}
		}
		else {
			$g['diyformfields'] = array();
			$g['diyformdata'] = array();
		}

		unset($g);
	}
}

if ($_W['isajax']) {
	if (empty($order)) {
		show_json(0);
	}

	$order['virtual_str'] = str_replace("\n", '<br/>', $order['virtual_str']);
	$order['goodstotal'] = count($goods);
	$order['createtime'] = date('Y-m-d H:i:s', $order['createtime']);
// 	$order['paytime'] = date('Y-m-d H:i:s', $order['paytime']);
	$order['sendtime'] = date('Y-m-d H:i:s', $order['sendtime']);
	$order['finishtimevalue'] = $order['finishtime'];
// 	$order['finishtime'] = date('Y-m-d H:i:s', $order['finishtime']);
	$address = false;
	$carrier = false;
	$stores = array();

	if ($order['isverify'] == 1) {
		$storeids = array();

		foreach ($goods as $g) {
			if (!empty($g['storeids'])) {
				$storeids = array_merge(explode(',', $g['storeids']), $storeids);
			}
		}

		if (empty($storeids)) {
			$stores = pdo_fetchall('select * from ' . tablename('sz_yi_store') . ' where  uniacid=:uniacid and status=1', array(':uniacid' => $_W['uniacid']));
		}
		else {
			$stores = pdo_fetchall('select * from ' . tablename('sz_yi_store') . ' where id in (' . implode(',', $storeids) . ') and uniacid=:uniacid and status=1', array(':uniacid' => $_W['uniacid']));
		}

		if ($order['dispatchtype'] == 0) {
			$address = iunserializer($order['address']);

			if (!is_array($address)) {
				$address = pdo_fetch('select realname,mobile,address from ' . tablename('sz_yi_member_address') . ' where id=:id limit 1', array(':id' => $order['addressid']));
			}
		}
	}
	else {
		if ($order['dispatchtype'] == 0) {
			$address = iunserializer($order['address']);

			if (!is_array($address)) {
				$address = pdo_fetch('select realname,mobile,address from ' . tablename('sz_yi_member_address') . ' where id=:id limit 1', array(':id' => $order['addressid']));
			}
		}
	}

	if (($order['dispatchtype'] == 1) || ($order['isverify'] == 1) || !empty($order['virtual'])) {
		$carrier = unserialize($order['carrier']);
	}

	$set = set_medias(m('common')->getSysset('shop'), 'logo');
	$canrefund = false;
	$tradeset = m('common')->getSysset('trade');
	$refunddays = intval($tradeset['refunddays']);
	if (($order['status'] == 1) || ($order['status'] == 2)) {
		if ((0 < $refunddays) || ($order['status'] == 1)) {
			$canrefund = true;
		}
	}
	else {
		if ($order['status'] == 3) {
			if (($order['isverify'] != 1) && empty($order['virtual'])) {
				if (0 < $refunddays) {
					$days = intval((time() - $order['finishtimevalue']) / 3600 / 24);

					if ($days <= $refunddays) {
						$canrefund = true;
					}
				}
			}
		}
	}

	$order['canrefund'] = $canrefund;

	if ($canrefund == true) {
		if ($order['status'] == 1) {
			$order['refund_button'] = '申请退款';
		}
		else {
			$order['refund_button'] = '申请售后';
		}

		if (!empty($order['refundstate'])) {
			$order['refund_button'] .= '中';
		}
	}

	$variable = array('show' => $show, 'diyform_flag' => $diyform_flag, 'goods' => $goods);
	return show_json(1, array('order' => $order, 'goods' => $goods, 'address' => $address, 'carrier' => $carrier, 'stores' => $stores, 'isverify' => $isverify, 'set' => $set), $variable);
}

if (p('hotel')) {
	if ($order['order_type'] == '3') {
		include $this->template('order/detail_hotel');
		return 1;
	}

	include $this->template('order/detail');
	return 1;
}

include $this->template('order/detail');

?>
