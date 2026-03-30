<?php
// 模块LTD提供
function testSendFormatedMessage()
{
	$msgNo = time() + 1;
	$msgInfo = array('memberCode' => $member_code, 'charge' => '3000', 'customerName' => '刘小姐', 'customerPhone' => '13321332245', 'customerAddress' => '五山华南理工', 'customerMemo' => '请快点送货', 'msgDetail' => '番茄炒粉@1000@1||客家咸香鸡@2000@1', 'deviceNo' => $device_no, 'msgNo' => $msgNo);
	sendFormatedMessage($msgInfo);
	return $msgNo;
}

function testSendFreeMessage($print_order, $member_code, $device_no, $key, $set, $price_list)
{
	$goods = '';
	$depositprice = number_format($print_order['depositprice'], 2);
	$type = array(2 => '到付', 1 => '在线付');

	if ($print_order['depositpricetype'] == '1') {
		$sum_money = number_format($print_order['goodsprice'] + $print_order['depositprice'], 2);
	}
	else {
		$sum_money = number_format($print_order['goodsprice'], 2);
	}

	$depositpricetype = $type[$print_order['depositpricetype']];

	foreach ($price_list as $value) {
		$total = $value['oprice'] * $print_order['num'];
		$goods .= $value['oprice'] . '   ' . $print_order['num'] . '    ' . $total . '    ' . $value['thisdate'] . "\n";
	}

	$msgNo = $print_order['ordersn'];
	$address = unserialize($print_order['address']);
	$time = date('Y-m-d H:i:s', $print_order['createtime']);

	if ($print_order['depositpricetype'] == '1') {
		$room_price = number_format($print_order['price'] - $print_order['depositprice'], 2);
	}
	else {
		$room_price = number_format($print_order['price'], 2);
	}

	if ($print_order['deductcredit2'] != '') {
		$deductcredit2 = number_format($print_order['deductcredit2'], 2);
	}
	else {
		$deductcredit2 = '0.00';
	}

	if ($print_order['discountprice'] != '') {
		$discountprice = number_format($print_order['discountprice'], 2);
	}
	else {
		$discountprice = '0.00';
	}

	if ($print_order['deductprice'] != '') {
		$deductprice = number_format($print_order['deductprice'], 2);
	}
	else {
		$deductprice = '0.00';
	}

	$freeMessage = array('memberCode' => $member_code, 'msgDetail' => "\n    " . $set['name'] . "\n------------------------------\n订单编号：" . $msgNo . "\n下单时间：" . $time . "\n房型：" . $print_order['goods'][0]['goodstitle'] . "\n客户姓名：" . $print_order['checkname'] . "\n联系方式：" . $print_order['realmobile'] . "\n订单备注：" . $print_order['remark'] . "\n------------------------------\n单价   数量   金额    入住日期        \n" . $goods . "\n------------------------------\n房间小计：       " . $print_order['goodsprice'] . "\n押金：           " . $depositprice . '(' . $depositpricetype . ")\n合计：           " . $sum_money . "\n会员优惠：\t\t " . $discountprice . "\n余额抵扣： \t\t " . $deductcredit2 . "\n积分抵扣： \t\t " . $deductprice . "\n实际支付：       " . $print_order['price'] . "\n------------------------------\n" . $set['description'] . "\n客服服务热线：" . $set['phone'] . "\n", 'deviceNo' => $device_no, 'msgNo' => $msgNo);
	sendFreeMessage($freeMessage, $key);
	return $msgNo;
}

function testSendFreeMessageshop($print_order, $member_code, $device_no, $key, $set)
{
	$goods = '';

	foreach ($print_order['goods'] as $value) {
		$goods .= $value['goodstitle'] . "\n" . '            ' . $value['price'] . '    ' . $value['total'] . '     ' . $value['totalmoney'] . "\n";
	}

	$msgNo = $print_order['ordersn'];
	$time = date('Y-m-d H:i:s', $print_order['createtime']);

	if ($print_order['deductcredit2'] != '') {
		$deductcredit2 = number_format($print_order['deductcredit2'], 2);
	}
	else {
		$deductcredit2 = '0.00';
	}

	if ($print_order['discountprice'] != '') {
		$discountprice = number_format($print_order['discountprice'], 2);
	}
	else {
		$discountprice = '0.00';
	}

	if ($print_order['deductprice'] != '') {
		$deductprice = number_format($print_order['deductprice'], 2);
	}
	else {
		$deductprice = '0.00';
	}

	if ($print_order['olddispatchprice'] != '') {
		$olddispatchprice = number_format($print_order['olddispatchprice'], 2);
	}
	else {
		$olddispatchprice = '0.00';
	}

	if ($print_order['room_number'] != '') {
		$room_number = '配送房间号:  ' . $print_order['room_number'];
	}
	else {
		$room_number = '';
	}

	$sum_money = number_format($print_order['goodsprice'] + $olddispatchprice, 2);
	$freeMessage = array('memberCode' => $member_code, 'msgDetail' => "\n     " . $set['name'] . "\n------------------------------\n订单编号：" . $msgNo . "\n下单时间：" . $time . "\n" . $room_number . "\n订单备注：" . $print_order['remark'] . "\n------------------------------\n商品名称    单价   数量  金额\n" . $goods . "\n------------------------------\n运费 ：          " . $olddispatchprice . "\n合计：           " . $sum_money . "\n会员优惠：\t\t " . $discountprice . "\n余额抵扣： \t\t " . $deductcredit2 . "\n积分抵扣： \t\t " . $deductprice . "\n实际支付：       " . $print_order['price'] . "\n------------------------------\n" . $set['description'] . "\n客服服务热线：" . $set['phone'] . "\n", 'deviceNo' => $device_no, 'msgNo' => $msgNo);
	sendFreeMessage($freeMessage, $key);
	return $msgNo;
}

function testSendFreeMessagemeet($data, $member_code, $device_no, $key, $set)
{
	$time = date('Y-m-d H:i:s', time());
	$freeMessage = array('memberCode' => $member_code, 'msgDetail' => "\n     " . $set['name'] . "\n------------------------------\n预约时间：" . $data['time'] . "\n姓名：" . $data['contact'] . "\n手机：" . $data['mobile'] . "\n会议室：" . $data['title'] . "\n备注：" . $data['message'] . "\n------------------------------\n下单时间：" . $time . "\n------------------------------\n" . $set['description'] . "\n客服服务热线：" . $set['phone'] . "\n", 'deviceNo' => $device_no, 'msgNo' => $msgNo);
	sendFreeMessage($freeMessage, $key);
	return $msgNo;
}

function testSendFreeMessagerest($data, $member_code, $device_no, $key, $set)
{
	$time = date('Y-m-d H:i:s', time());
	$freeMessage = array('memberCode' => $member_code, 'msgDetail' => "\n     " . $set['name'] . "\n------------------------------\n预约时间：" . $data['time'] . "\n姓名：" . $data['contact'] . "\n手机：" . $data['mobile'] . "\n餐厅：" . $data['title'] . "\n备注：" . $data['message'] . "\n------------------------------\n下单时间：" . $time . "\n------------------------------\n" . $set['description'] . "\n客服服务热线：" . $set['phone'] . "\n", 'deviceNo' => $device_no, 'msgNo' => $msgNo);
	sendFreeMessage($freeMessage, $key);
	return $msgNo;
}

function testQueryState($msgNo)
{
	$result = queryState($msgNo);
	echo $result;
	return $result;
}

function testListDevice()
{
	echo listDevice();
}

function testListException()
{
	echo listException();
}

function sendFreeMessage($msg, $key)
{
	$msg['reqTime'] = number_format(1000 * time(), 0, '', '');
	$content = $msg['memberCode'] . $msg['msgDetail'] . $msg['deviceNo'] . $msg['msgNo'] . $msg['reqTime'] . $key;
	$msg['securityCode'] = md5($content);
	$msg['mode'] = 2;
	return sendMessage($msg);
}

function sendFormatedMessage($msgInfo)
{
	$msgInfo['reqTime'] = number_format(1000 * time(), 0, '', '');
	$content = $msgInfo['memberCode'] . $msgInfo['customerName'] . $msgInfo['customerPhone'] . $msgInfo['customerAddress'] . $msgInfo['customerMemo'] . $msgInfo['msgDetail'] . $msgInfo['deviceNo'] . $msgInfo['msgNo'] . $msgInfo['reqTime'] . $key;
	$msgInfo['securityCode'] = md5($content);
	$msgInfo['mode'] = 1;
	return sendMessage($msgInfo);
}

function sendMessage($msgInfo)
{
	$client = new HttpClient(FEYIN_HOST, FEYIN_PORT);

	if (!$client->post('/api/sendMsg', $msgInfo)) {
		return 'faild';
	}

	return $client->getContent();
}

function queryState($msgNo)
{
	$now = number_format(1000 * time(), 0, '', '');
	$client = new HttpClient(FEYIN_HOST, FEYIN_PORT);

	if (!$client->get('/api/queryState?memberCode=' . $member_code . '&reqTime=' . $now . '&securityCode=' . md5($member_code . $now . $key . $msgNo) . '&msgNo=' . $msgNo)) {
		return 'faild';
	}

	return $client->getContent();
}

function listDevice()
{
	$now = number_format(1000 * time(), 0, '', '');
	$client = new HttpClient(FEYIN_HOST, FEYIN_PORT);

	if (!$client->get('/api/listDevice?memberCode=' . $member_code . '&reqTime=' . $now . '&securityCode=' . md5($member_code . $now . $key))) {
		return 'faild';
	}

	$xml = $client->getContent();
	$sxe = new SimpleXMLElement($xml);

	foreach ($sxe->device as $device) {
		$id = $device['id'];
		echo '设备编码：' . $id . '    ';
		$deviceStatus = $device->deviceStatus;
		echo '状态：' . $deviceStatus;
		echo '<br>';
	}
}

function listException()
{
	$now = number_format(1000 * time(), 0, '', '');
	$client = new HttpClient(FEYIN_HOST, FEYIN_PORT);

	if (!$client->get('/api/listException?memberCode=' . $member_code . '&reqTime=' . $now . '&securityCode=' . md5($member_code . $now . $key))) {
		return 'faild';
	}

	return $client->getContent();
}

include 'HttpClient.class.php';
define('MEMBER_CODE', 'd2e595a4225311e6b50d52540008b6e6');
define('FEYIN_KEY', '425c8961');
define('DEVICE_NO', '9497610928583271');
define('FEYIN_HOST', 'my.feyin.net');
define('FEYIN_PORT', 80);

?>
