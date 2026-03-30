<?php
// 2025-03-07：支付宝支付回调

ini_set('display_errors', 'On');
global $_W;
global $_GPC;

require '../../../../framework/bootstrap.inc.php';
require '../../../../addons/sz_yi/defines.php';
require '../../../../addons/sz_yi/core/inc/functions.php';
require '../../../../addons/sz_yi/core/inc/plugin/plugin_model.php';

//载入日志函数
//获取文件流
$input = file_get_contents('php://input');
file_put_contents('./log/customnotify.log', $input."\r\n",FILE_APPEND);
//将接受到的Json数据转换成数组格式。
$data = json_decode($input, true);
//echo $_W['siteroot'] . 'addons/sz_yi/payment/tgwechat/notify.log';
if (!empty($data)) {
	//file_put_contents('./notify.log', print_r($data,TRUE),FILE_APPEND);
    $other_database = array(
        'host' => 'rm-wz9mt4j79jrdh0p3z.mysql.rds.aliyuncs.com',    //数据库IP或是域名
        'username' => 'gogo198',       // 数据库连接用户名
        'password' => 'Gogo@198',     // 数据库连接密码
        'database' => 'lrw',     // 数据库名
        'port' => 3306,             // 数据库连接端口
        'tablepre' => '',       // 表前缀，如果没有前缀留空即可
        'charset' => 'utf8',         // 数据库默认编码
        'pconnect' => 0,            // 是否使用长连接
    );

	$order = pdo_fetch('select * from ' . tablename('customs_collection') . ' where ordersn=:ordersn limit 1', array(':ordersn' => $data['lowOrderId']));
	
	$data['uniacid'] = $order['uniacid'];//订单所属公众号
	
	$setting = uni_setting($order['uniacid'], array('payment'));
	
	$answer = array(
		'lowOrderId'=> $data['lowOrderId'],//下游系统流水号，必须唯一
		'merchantId'=> $data['merchantId'],//商户进件账号
		'upOrderId'=>  $data['upOrderId'],//上游流水号
	);
	
	if ($data['state'] == '0' && $data['orderDesc'] == '支付成功') {
		//是否接收到回调  SUCCESS表示成功
		//付款成功修改订单表中sz_yi_order数据  状态：status = 1
		if ($order['status'] == 0) {
			load()->func('communication');
			m('common')->paylog($data);
			m('common')->paylog('status');
			
			pdo_update('customs_collection', array('status' => '1','ordersn_general'=>$data['upOrderId'],'paytime'=>strtotime($data['payTime'])), array('id' => $order['id']));

            if($order['order_type']==1){
                #新商城订单
                pdo_update('website_order_list',['status'=>1],['pay_id'=>$order['id']]);

                #生成商品订单====start
                $orderlist = pdo_fetch('select * from '.tablename('website_order_list').' where pay_id=:pay_id',[':pay_id'=>$order['id']]);
                $orderlist['content'] = json_decode($orderlist['content'],true);
                foreach($orderlist['content']['goods_info'] as $k=>$v){
                    foreach($v['sku_info'] as $k2=>$v2){
                        if($v2['is_close']==0){
                            $other_db = new DB($other_database);
                            $other_db->insert('order_goods_list',[
                                'order_id'=>$orderlist['id'],
                                'goods_id'=>$v['good_id'],
                                'sku_id'=>$v2['sku_id'],
                                'goods_num'=>$v2['goods_num'],
                                'goods_price'=>$v2['price'],
                            ]);
                        }
                    }
                }
                #生成商品订单====end

                if($orderlist['is_level']>0){
                    #会员等级订单====start
                    $order_goods_id = $orderlist['content']['goods_info'][0]['good_id'];
                    $order_goods_sku_id = $orderlist['content']['goods_info'][0]['sku_info'][0]['sku_id'];

                    $other_db = new DB($other_database);
                    $buy_level_goods = $other_db->fetch('select * from goods where goods_id=:gid',[':gid'=>$order_goods_id]);
                    $buy_level_goods_sku = $other_db->fetch('select * from goods_sku where goods_id=:gid and sku_id=:sku_id',[':gid'=>$order_goods_id,':sku_id'=>$order_goods_sku_id]);

                    $member_level = pdo_fetch('select * from '.tablename('member_level').' where id=:id',[':id'=>$buy_level_goods['level_id']]);
                    $member_level['service_desc'] = json_decode($member_level['service_desc'],true);

                    $leveltime = 0;#购买会员的截止日期
                    foreach($member_level['service_desc'] as $k=>$v){
                        if($buy_level_goods['goods_name'].' '.$v['mname'] == $buy_level_goods_sku['spec_names']){
                            $leveltime = $v['mday']*86400;
                            break;
                        }
                    }

                    $true_leveltime = 0;

                    $user = pdo_fetch('select * from '.tablename('website_user').' where id=:id',[':id'=>$orderlist['user_id']]);
                    $time = time();
                    if($orderlist['is_level']==1){
                        #会员等级订单
                        if($user['leveltime']>0){
                            #首先判断当前等级过期的时间是否过期
                            if($time>$user['leveltime']){
                                #过期，按照当前时间重新分配
                                $true_leveltime = $time+$leveltime;
                            }
                            else{
                                #未过期，按照当前时间再加多秒数
                                if($member_level['id']==$user['level_id']){
                                    #同等级别，时间累积相加
                                    $true_leveltime = $user['leveltime']+$leveltime;
                                }
                                elseif($member_level['id']>$user['level_id']){
                                    #购买级别>当前级别，按当前支付时间+会员原等级时间
                                    $true_leveltime = $time+$leveltime;
                                }
                            }
                        }
                        else{
                            $true_leveltime = time()+$leveltime;
                        }
                        pdo_update('website_user',['leveltime'=>$true_leveltime,'level_id'=>$member_level['id']],['id'=>$orderlist['user_id']]);
                    }
                    elseif($orderlist['is_level']==2){
                        #商户等级订单
                        if($user['mleveltime']>0){
                            #首先判断当前等级过期的时间是否过期
                            if($time>$user['mleveltime']){
                                #过期，按照当前时间重新分配
                                $true_leveltime = $time+$leveltime;
                            }
                            else{
                                #未过期，按照当前时间再加多秒数
                                if($member_level['id']==$user['mlevel_id']){
                                    #同等级别，时间累积相加
                                    $true_leveltime = $user['mleveltime']+$leveltime;
                                }
                                elseif($member_level['id']>$user['mlevel_id']){
                                    #购买级别>当前级别，按当前支付时间+会员原等级时间
                                    $true_leveltime = $time+$leveltime;
                                }
                            }
                        }
                        else{
                            $true_leveltime = time()+$leveltime;
                        }
                        pdo_update('website_user',['mleveltime'=>$true_leveltime,'mlevel_id'=>$member_level['id']],['id'=>$orderlist['user_id']]);
                    }


                    if($orderlist['coupon_id']>0){
                        pdo_update('member_coupon_info',['status'=>1],['id'=>$orderlist['coupon_id']]);
                    }

                    pdo_update('website_order_list',['status'=>9],['pay_id'=>$order['id']]);#已完成
                    #会员等级订单====end
                }
            }

            $zf_type='';
            if($order['pay_type']==1){
                $zf_type = '微信支付';
            }elseif($order['pay_type']==2){
                $zf_type = '支付宝支付';
            }
            
            $sql = 'SELECT * FROM ' . tablename('core_paylog') . ' WHERE `module`=:module AND `tid`=:tid  limit 1';
            $params = array();
            $params[':tid'] = $data['lowOrderId'];
            $params[':module'] = 'sz_yi';
            //查找core_paylog中的数据
            $log = pdo_fetch($sql, $params);
            $record = array();
			$record['status'] = '1';						
			pdo_update('core_paylog', $record, array('plid' => $log['plid']));

            $name = '';
            if($order['trade_type']==1){
                $name = pdo_fetchcolumn('select title from '.tablename('sz_yi_goods').' where id=:id',array(':id'=>$order['good_id']));
            }elseif($order['trade_type']==2){
                $name = pdo_fetchcolumn('select project_name from '.tablename('customs_project').' where id=:id',array(':id'=>$order['project_id']));
            }
            $order['total_money'] = $order['trade_price']+$order['overdue_money'];
            //step1:发送消息给发起付款的人
            $post = json_encode([
                'call'=>'collectionNotice',
                'first'=>'您有一笔收款信息',
                'keyword1'=>$order['payer_name'],
                'keyword2'=>'CNY '.$order['total_money'],
                'keyword3'=>$zf_type,
                'keyword4'=>date('Y-m-d H:i:s',time()),
                'keyword5'=>$order['ordersn'],
                'remark' =>'感谢您的使用',
                'openid' =>$order['send_openid'],
                'uniacid'=>$order['uniacid'],
                'temp_id'=>'WcvDClChgUbLfWHu5jQw5TEilYU36VdNDH514KZ-f4w',
                'url'=>'https://shop.gogo198.cn/app/index.php?i=3&c=entry&do=member&p=custompayment&m=sz_yi&oid='.$order['id'].'&isadmin=1'
            ]);
            ihttp_request('https://shop.gogo198.cn/api/sendwechattemplatenotice.php', $post);

            //step2:成功支付后，发送消息给本人
            $type = '';
            if($order['trade_type']==1){
                $type='商品';
            }elseif($order['trade_type']==2){
                $type='项目';
            }elseif($order['trade_type']==3){
                $type='多项服务';
            }
            if(is_numeric($order['openid'])){
                $order['openid'] = pdo_fetchcolumn('select openid from '.tablename('sz_yi_member').' where mobile=:mob',[':mob'=>$order['openid']]);
            }
            $post2 = json_encode([
                'call'=>'collectionNotice',
                'first'=>'您好，您已经完成新订单的处理，点击查看详情，如有疑问，敬请联系客服075786329911，感谢您的支持！',
                'keyword1'=>$order['ordersn'],
                'keyword2'=>$type,
                'keyword3'=>'CNY '.$order['total_money'],
                'keyword4'=>$zf_type,
                'keyword5'=>date('Y-m-d H:i:s',time()),
                'remark' =>'感谢您的使用',
                'openid' =>$order['openid'],
                'uniacid'=>$order['uniacid'],
                'temp_id'=>'tHWxOL4Kc3v6uZinHT3Zo661I8o6EbAg46XKUP0FnnY',
                'url'=>'https://shop.gogo198.cn/app/index.php?i=3&c=entry&do=member&p=custompayment&m=sz_yi&oid='.$order['id']
            ]);
            ihttp_request('https://shop.gogo198.cn/api/sendwechattemplatenotice.php', $post2);
            //step3:成功支付后，发送消息给管理员（老板）
            $post3 = json_encode([
                'call'=>'collectionNotice',
                'first'=>'您好，有［'.$type.'］订单状态已变更为［订单已付］，点击查看详情！',
                'keyword1'=>$order['ordersn'],
                'keyword2'=>$type,
                'keyword3'=>'CNY '.$order['total_money'],
                'keyword4'=>$zf_type,
                'keyword5'=>date('Y-m-d H:i:s',time()),
                'remark' =>'',
                'openid' =>'ov3-bt8keSKg_8z9Wwi-zG1hRhwg',
                'uniacid'=>$order['uniacid'],
                'temp_id'=>'tHWxOL4Kc3v6uZinHT3Zo661I8o6EbAg46XKUP0FnnY',
                'url'=>'https://shop.gogo198.cn/app/index.php?i=3&c=entry&do=member&p=custompayment&m=sz_yi&oid='.$order['id'].'&isadmin=1'
            ]);
            ihttp_request('https://shop.gogo198.cn/api/sendwechattemplatenotice.php', $post3);
		}
		$answer['finished'] = 'SUCCESS';
		
	} else {
		$answer['finished'] = 'FAIL';
	}
//	$str = tostring($answer);
	
	ksort($answer, SORT_STRING);
	$str = '';
	foreach ($answer as $key => $v ) {
		if (empty($v)) {
			continue;
		}
		$str .= $key . '=' . $v . '&';
	}

//	$str = $str .'&key=5f61d7f65b184d19a1e006bc9bfb6b2f';
	$str .= 'key='.$setting['payment']['tgpay']['key'];
	//数据加密
	$answer['sign'] = strtoupper(md5($str));
	
	//将数据转换成json数据返回
	echo json_encode($answer);

	$get = $data;
	
}else {
	
	$get = $_GET;
}

//$_W['uniacid'] = $_W['weid'] = intval($strs[0]);
$_W['uniacid'] = $get['uniacid'];

//$type = intval($strs[1]);
$type = 0;

$total_fee = $get['payMoney'];

if ($type == 0) {
	$paylog = "\n-------------------------------------------------\n";
	$paylog .= 'orderno: ' . $get['lowOrderId'] . "\n";
	$paylog .= "paytype: alipay\n";
	$paylog .= 'data: ' . json_encode($_POST) . "\n";
	m('common')->paylog($paylog);
}

$set = m('common')->getSysset(array('shop', 'pay'));

$setting = uni_setting($_W['uniacid'], array('payment'));

if (is_array($set['pay'])) {
	
	$wechat = $set['pay']['tgpay'];

	if (!empty($wechat)) {
		
		m('common')->paylog('setting: ok');
		
//		ksort($get);
//		$string1 = '';

//		foreach ($get as $k => $v) {
//			if (($v != '') && ($k != 'sign')) {
//				$string1 .= $k . '=' . $v . '&';
//			}
//		}
//		$wechat['key'] = $setting['payment']['tgpay']['key'];

//		$wechat['key'] = $wechat['version'] == 1 ? $wechat['key'] : $wechat['key'];
		
//		$sign = strtoupper(md5($string1 . 'key=' . $wechat['key']));

//		if ($sign == $get['sign']) { 2017-11-17
		if (($data['state'] == '0') && ($data['orderDesc'] == '支付成功')) {	

			m('common')->paylog('sign: ok');

			if (empty($type)) {

				$tid = $get['lowOrderId'];

				if (strexists($tid, 'GJ')) {
					$tids = explode('GJ', $tid);
					$tid = $tids[0];
				}

				$sql = 'SELECT * FROM ' . tablename('core_paylog') . ' WHERE `module`=:module AND `tid`=:tid  limit 1';
				
				$params = array();
				
				$params[':tid'] = $tid;
				
				$params[':module'] = 'sz_yi';
				//查找core_paylog中的数据
				$log = pdo_fetch($sql, $params);
				
				m('common')->paylog('log: ' . (empty($log) ? '' : json_encode($log)) . '');
				
				if (!empty($log) && ($log['status'] == '0') && (bccomp($log['fee'], $total_fee, 2) == 0)) {
					
					m('common')->paylog('corelog: ok');
					
					$site = WeUtility::createModuleSite($log['module']);

					if (!is_error($site)) {
						
						$method = 'payResult';

						if (method_exists($site, $method)) {
							$ret = array();
							$ret['weid'] = $log['weid'];
							$ret['uniacid'] = $log['uniacid'];
							$ret['result'] = 'success';
//							$ret['type'] = $log['type'];
							$ret['type'] = 'wechat';//2017-11-17
							$ret['from'] = 'return';
							$ret['tid'] = $log['tid'];
							$ret['user'] = $log['openid'];
							$ret['fee'] = $log['fee'];
							$ret['tag'] = $log['tag'];
							$result = $site->$method($ret);
							
							m('common')->paylog('payResult: ' . json_encode($result) . ".\n");
							
							if (is_array($result) && ($result['result'] == 'success')) {
								
								$log['tag'] = iunserializer($log['tag']);
								$log['tag']['transaction_id'] = $get['transaction_id'];
								$record = array();
								$record['status'] = '1';						
//								$record['tag'] = iserializer($log['tag']);
								
								pdo_update('core_paylog', $record, array('plid' => $log['plid']));

								if (p('cashier')) {
									
									$order = pdo_fetch('select id,cashier from ' . tablename('sz_yi_order') . ' where  (ordersn=:ordersn or pay_ordersn=:ordersn or ordersn_general=:ordersn) and uniacid=:uniacid limit 1', array(':uniacid' => $_W['uniacid'], ':ordersn' => $ret['tid']));

									if (!empty($order['cashier'])) {									
										$orders['status'] = '3';
									}
								}
								pdo_update('sz_yi_order',$record, array('ordersn_general' => $tid, 'uniacid' => $log['uniacid']));
								exit();
							}
						}
					}
				}
			}

			else if ($type == 1) {
				$logno = trim($get['lowOrderId']);

				if (empty($logno)) {
					exit();
				}

				$log = pdo_fetch('SELECT * FROM ' . tablename('sz_yi_member_log') . ' WHERE `uniacid`=:uniacid and `logno`=:logno limit 1', array(':uniacid' => $_W['uniacid'], ':logno' => $logno));
				if (!empty($log) && empty($log['status']) && ($log['fee'] == $total_fee) && ($log['openid'] == $get['openid'])) {
					pdo_update('sz_yi_member_log', array('status' => 1, 'rechargetype' => 'wechat'), array('id' => $log['id']));
					m('member')->setCredit($log['openid'], 'credit2', $log['money'], array(0, '商城会员充值:credit2:' . $log['money']));
					m('member')->setRechargeCredit($log['openid'], $log['money']);

					if (p('sale')) {
						p('sale')->setRechargeActivity($log);
					}

					if (!empty($log['couponid'])) {
						$pc = p('coupon');

						if ($pc) {
							$pc->useRechargeCoupon($log);
						}
					}
					m('notice')->sendMemberLogMessage($log['id']);
				}
			}
		}
	}
}
?>