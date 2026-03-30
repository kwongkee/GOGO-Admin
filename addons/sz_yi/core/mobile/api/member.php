<?php
// 模块LTD提供
global $_W;
global $_GPC;
$openid = m('user')->getOpenid();
require IA_ROOT . '/addons/sz_yi/core/inc/interface.php';
$set = m('common')->getSysset(array('trade'));
$shop_set = m('common')->getSysset(array('shop'));
$shopset = m('common')->getSysset('shop');
$setdata = pdo_fetch('select * from ' . tablename('sz_yi_sysset') . ' where uniacid=:uniacid limit 1', array(':uniacid' => $_W['uniacid']));
$appset = unserialize($setdata['sets']);
$app = $appset['app']['base'];
$member = m('member')->getMember($openid);
$member['nickname'] = empty($member['nickname']) ? $member['mobile'] : $member['nickname'];
$uniacid = $_W['uniacid'];
$trade['withdraw'] = $set['trade']['withdraw'];
$trade['closerecharge'] = $set['trade']['closerecharge'];
$trade['transfer'] = $set['trade']['transfer'];
$hascom = false;
$supplier_switch = false;
$supplier_switch_centre = false;

if (p('merchant')) {
	$ismerchant = pdo_fetchall('select * from ' . tablename('sz_yi_merchants') . ' where uniacid=' . $_W['uniacid'] . ' and member_id=' . $member['id']);
}

if (p('supplier')) {
	$supplier_set = p('supplier')->getSet();
	$issupplier = p('supplier')->isSupplier($openid);
	$af_result = pdo_fetchcolumn('select status from ' . tablename('sz_yi_af_supplier') . ' where uniacid=' . $_W['uniacid'] . ' and openid=\'' . $openid . '\'');

	if ($af_result == 2) {
		$shopset['af_result'] = true;
	}

	$shopset['switch'] = $supplier_set['switch'];
	$shopset['switch_centre'] = $supplier_set['switch_centre'];
}

$plugc = p('commission');

if ($plugc) {
	$pset = $plugc->getSet();

	if (!empty($pset['level'])) {
		if (($member['isagent'] == 1) && ($member['status'] == 1)) {
			$hascom = true;
		}
	}
}

$shopset['commission_text'] = $pset['texts']['center'];
$shopset['hascom'] = $hascom;
$hascoupon = false;
$hascouponcenter = false;
$plugin_coupon = p('coupon');

if ($plugin_coupon) {
	$pcset = $plugin_coupon->getSet();

	if (empty($pcset['closemember'])) {
		$hascoupon = true;
		$hascouponcenter = true;
	}
}

$shopset['hascoupon'] = $hascoupon;
$shopset['hascouponcenter'] = $hascouponcenter;
$pluginbonus = p('bonus');
$bonus_start = false;
$bonus_text = '';

if (!empty($pluginbonus)) {
	$bonus_set = $pluginbonus->getSet();
	$islevel = $pluginbonus->isLevel($openid);
	if ((!empty($bonus_set['start']) || !empty($bonus_set['area_start'])) && !empty($islevel)) {
		$bonus_start = true;
		$bonus_text = ($bonus_set['texts']['center'] ? $bonus_set['texts']['center'] : '分红明细');
	}
}

$shopset['bonus_start'] = $bonus_start;
$shopset['bonus_text'] = $bonus_text;
$shopset['is_weixin'] = is_weixin();
$plugin_article = p('article');

if ($plugin_article) {
	$article_set = $plugin_article->getSys();
	$shopset['article_text'] = $article_set['article_text'] ? $article_set['article_text'] : '文章管理';
	$shopset['isarticle'] = $article_set['isarticle'];
}

$reurnset = m('plugin')->getpluginSet('return');
$shopset['isreturn'] = false;
if (($reurnset['isqueue'] == 1) || ($reurnset['isreturn'] == 1) || ($reurnset['islevelreturn'] == 1)) {
	$shopset['isreturn'] = true;
}

if (p('ranking')) {
	$ranking_set = p('ranking')->getSet();
	$shopset['rankingname'] = $ranking_set['ranking']['rankingname'] ? $ranking_set['ranking']['rankingname'] : '排行榜';
	$shopset['isranking'] = $ranking_set['ranking']['isranking'];
}

$open_creditshop = false;
$creditshop = p('creditshop');

if ($creditshop) {
	$creditshop_set = $creditshop->getSet();

	if (!empty($creditshop_set['centeropen'])) {
		$open_creditshop = true;
	}
}

$level = array('levelname' => empty($this->yzShopSet['levelname']) ? '普通会员' : $this->yzShopSet['levelname']);

if (!empty($member['level'])) {
	$level = m('member')->getLevel($openid);
}

$orderparams = array(':uniacid' => $_W['uniacid'], ':openid' => $openid);
$order = array('status0' => pdo_fetchcolumn('select count(distinct ordersn_general) from ' . tablename('sz_yi_order') . ' where openid=:openid and status=0 and order_type<>3  and uniacid=:uniacid limit 1', $orderparams), 'status1' => pdo_fetchcolumn('select count(distinct ordersn_general) from ' . tablename('sz_yi_order') . ' where openid=:openid and status=1 and order_type<>3 and refundid=0 and uniacid=:uniacid limit 1', $orderparams), 'status2' => pdo_fetchcolumn('select count(distinct ordersn_general) from ' . tablename('sz_yi_order') . ' where openid=:openid and status=2 and order_type<>3 and refundid=0 and uniacid=:uniacid limit 1', $orderparams), 'status4' => pdo_fetchcolumn('select count(distinct ordersn_general) from ' . tablename('sz_yi_order') . ' where openid=:openid and order_type<>3 and refundstate>0 and uniacid=:uniacid limit 1', $orderparams));
$orderhotel = array('status0' => pdo_fetchcolumn('select count(distinct ordersn_general) from ' . tablename('sz_yi_order') . ' where openid=:openid and status=0 and order_type=3  and uniacid=:uniacid limit 1', $orderparams), 'status1' => pdo_fetchcolumn('select count(distinct ordersn_general) from ' . tablename('sz_yi_order') . ' where openid=:openid and status=1 and order_type=3 and refundid=0 and uniacid=:uniacid limit 1', $orderparams), 'status6' => pdo_fetchcolumn('select count(distinct ordersn_general) from ' . tablename('sz_yi_order') . ' where openid=:openid and status=6 and order_type=3 and refundid=0 and uniacid=:uniacid limit 1', $orderparams), 'status4' => pdo_fetchcolumn('select count(distinct ordersn_general) from ' . tablename('sz_yi_order') . ' where openid=:openid and order_type=3 and refundstate>0 and uniacid=:uniacid limit 1', $orderparams));

if (6 < mb_strlen($member['nickname'], 'utf-8')) {
	$member['nickname'] = mb_substr($member['nickname'], 0, 6, 'utf-8');
}

$referrer = array();

if ($shop_set['shop']['isreferrer']) {
	if (0 < $member['agentid']) {
		$referrer = pdo_fetch('select * from ' . tablename('sz_yi_member') . ' where uniacid=' . $_W['uniacid'] . ' and id = \'' . $member['agentid'] . '\' ');
		$referrer['realname'] = mb_substr($referrer['realname'], 0, 6, 'utf-8');
	}
	else {
		$referrer['realname'] = '总店';
	}
}

$counts = array('cartcount' => pdo_fetchcolumn('select ifnull(sum(total),0) from ' . tablename('sz_yi_member_cart') . ' where uniacid=:uniacid and openid=:openid and deleted=0 ', array(':uniacid' => $uniacid, ':openid' => $openid)), 'favcount' => pdo_fetchcolumn('select count(*) from ' . tablename('sz_yi_member_favorite') . ' where uniacid=:uniacid and openid=:openid and deleted=0 ', array(':uniacid' => $uniacid, ':openid' => $openid)));

if ($plugin_coupon) {
	$time = time();
	$sql = 'select count(*) from ' . tablename('sz_yi_coupon_data') . ' d';
	$sql .= ' left join ' . tablename('sz_yi_coupon') . ' c on d.couponid = c.id';
	$sql .= ' where d.openid=:openid and d.uniacid=:uniacid and  d.used=0 ';
	$sql .= ' and (   (c.timelimit = 0 and ( c.timedays=0 or c.timedays*86400 + d.gettime >=unix_timestamp() ) )  or  (c.timelimit =1 and c.timestart<=' . $time . ' && c.timeend>=' . $time . ')) order by d.gettime desc';
	$counts['couponcount'] = pdo_fetchcolumn($sql, array(':openid' => $openid, ':uniacid' => $_W['uniacid']));
}

$pcashier = p('cashier');
$has_cashier = false;

if ($pcashier) {
	$store = pdo_fetch('select * from ' . tablename('sz_yi_cashier_store') . ' where uniacid=:uniacid and member_id=:member_id limit 1', array(':uniacid' => $_W['uniacid'], ':member_id' => $member['id']));
	$store_waiter = pdo_fetch('select * from ' . tablename('sz_yi_cashier_store_waiter') . ' where uniacid=:uniacid and member_id=:member_id limit 1', array(':uniacid' => $_W['uniacid'], ':member_id' => $member['id']));
	if ($store || $store_waiter) {
		$has_cashier = true;
	}
}

$res = array(
	'member' => array(
		array('avatar' => !empty($member['avatar']) ? $member['avatar'] : '../addons/sz_yi/template/mobile/default/static/images/photo-mr.jpg', 'nickname' => $member['nickname'], 'level' => $level['levelname'], 'url' => $this->createMobileUrl('member/info')),
		array('price' => $member['credit2'], 'status' => empty($set['trade']['closerecharge']) ? 1 : 0, 'url' => $this->createMobileUrl('member/recharge', array('openid' => $openid))),
		array('total' => $member['credit1'], 'status' => $open_creditshop ? 1 : 0, 'url' => $this->createPluginMobileUrl('creditshop'))
		),
	'order'  => array(
		'url'          => $this->createMobileUrl('order'),
		'order_status' => array(
			array('status' => 0, 'num' => $order['status0'], 'url' => $this->createMobileUrl('order', array('status' => 0))),
			array('status' => 1, 'num' => $order['status1'], 'url' => $this->createMobileUrl('order', array('status' => 1))),
			array('status' => 2, 'num' => $order['status2'], 'url' => $this->createMobileUrl('order', array('status' => 2))),
			array('status' => 4, 'num' => $order['status4'], 'url' => $this->createMobileUrl('order', array('status' => 4)))
			)
		),
	'urls'   => array(
		'part1' => array(
			array('name' => '分销中心', 'status' => $hascom ? 1 : 0, 'url' => $this->createPluginMobileUrl('commission')),
			array('name' => '我的资料', 'status' => 1, 'url' => $this->createMobileUrl('member/info'))
			),
		'part2' => array(
			array('name' => '供应商申请', 'status' => p('supplier') ? 1 : 0, 'url' => $this->createPluginMobileUrl('supplier/af_supplier')),
			array('name' => '分红中心', 'status' => $pluginbonus && empty($bonus_set['bonushow']) ? 1 : 0, 'url' => $this->createPluginMobileUrl('bonus/index')),
			array('name' => '我的推荐码', 'status' => $app['accept'] ? 1 : 0, 'url' => $this->createPluginMobileUrl('member/referral'))
			),
		'part3' => array(
			array('name' => '领取优惠券', 'status' => $hascoupon && $hascouponcenter ? 1 : 0, 'url' => $this->createPluginMobileUrl('coupon')),
			array('name' => '我的优惠券', 'status' => $hascoupon ? 1 : 0, 'num' => $counts['couponcount'], 'url' => $this->createPluginMobileUrl('coupon/my'))
			),
		'part4' => array(
			array('name' => '我的购物车', 'status' => 1, 'num' => $counts['cartcount'], 'url' => $this->createMobileUrl('shop/cart')),
			array('name' => '我的收藏', 'status' => 1, 'num' => $counts['favcount'], 'url' => $this->createMobileUrl('shop/favorite')),
			array('name' => '我的足迹', 'status' => 1, 'url' => $this->createMobileUrl('shop/history')),
			array('name' => '消息提醒设置', 'status' => 1, 'url' => $this->createMobileUrl('member/notice')),
			array('name' => '订单通知', 'status' => 1, 'url' => $this->createMobileUrl('member/messagelist')),
			array('name' => '系统消息', 'status' => 1, 'url' => $this->createMobileUrl('member/pushlist'))
			),
		'part5' => array(
			array('name' => '余额提现', 'status' => isset($set['trade']) && ($set['trade']['withdraw'] == 1) ? 1 : 0, 'url' => $this->createMobileUrl('member/withdraw')),
			array('name' => '充值记录', 'status' => isset($set['trade']) && (($set['trade']['withdraw'] == 1) || empty($set['trade']['closerecharge'])) ? 1 : 0, 'url' => $this->createMobileUrl('member/log'))
			),
		'part6' => array(
			array('name' => '我的地址管理', 'status' => 1, 'url' => $this->createMobileUrl('shop/address'))
			),
		'part7' => array(
			array('name' => '退出', 'status' => 1, 'url' => $this->createMobileUrl('member/logout'))
			)
		)
	);
echo json_encode($res);

?>
