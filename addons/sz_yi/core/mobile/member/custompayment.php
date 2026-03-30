<?php
// 模块LTD提供
if (!defined('IN_IA')) {
	exit('Access Denied');
}

global $_W;
global $_GPC;

$op = !empty($_GPC['op'])?trim($_GPC['op']):'display';
$shopset = m('common')->getSysset('shop');
$set = m('common')->getSysset(array('pay'));

if($op=='display'){
    if(empty($_W['openid'])){
        exit('openid不能为空！');
    }
    if(empty($_GPC['oid'])){
        exit('订单id不能为空！');
    }else{
        if(intval($_GPC['isadmin'])==1){
            //管理员查看
            $data = pdo_fetch('select * from '.tablename('customs_collection').' where id=:id and uniacid=:uni',array(':id'=>trim($_GPC['oid']),':uni'=>$_W['uniacid']));
        }else{
            $m = pdo_fetch('select mobile from '.tablename('sz_yi_member').' where openid=:openid and uniacid=3 order by id desc',[':openid'=>$_W['openid']]);
            $data = pdo_fetch('select * from '.tablename('customs_collection').' where id=:id and uniacid=:uni and openid=:openid',array(':id'=>trim($_GPC['oid']),':openid'=>$_W['openid'],':uni'=>$_W['uniacid']));

            if(empty($data)){
                $data = pdo_fetch('select * from '.tablename('customs_collection').' where id=:id and uniacid=:uni and openid=:openid',array(':id'=>trim($_GPC['oid']),':openid'=>$m['mobile'],':uni'=>$_W['uniacid']));
            }
        }
//        $data = pdo_fetch('select * from '.tablename('customs_collection').' where id=:id and uniacid=:uni ',array(':id'=>trim($_GPC['oid']),':uni'=>$_W['uniacid']));
        if(empty($data)){
            exit('参数错误！');
        }

        //查询收款人
        $user = pdo_fetch('select user_name,user_tel from '.tablename('decl_user').' where openid=:openid',array(':openid'=>$data['send_openid']));
        if(empty($user)){
            $user = pdo_fetch('select phone as user_tel,realname as user_name from '.tablename('website_user').' where openid=:openid',[':openid'=>$data['send_openid']]);
        }
        //查询付款人(有无支付宝验证)
        $is_at = pdo_fetch('select is_attestation from '.tablename('sz_yi_member').' where openid=:openid and uniacid=3 order by id desc',[':openid'=>$_W['openid']]);
//        $data['is_attestation'] = $is_at['is_attestation'];
        $data['is_attestation'] = 1;
//        if($_W['openid']=='ov3-bt5vIxepEjWc51zRQNQbFSaQ'){
//            print_r($data['is_attestation']);die;
//        }

        //逾期收款,未付款时查看
        if($data['status']==0){
            $Date_1 = date('Y-m-d H:i:s',time());//今天
            // $Date_1 = date('2022-01-10 H:i:s',time());//今天
            if(!empty($data['overdue'])){
                $Date_2 = date('Y-m-d H:i:s',$data['overdue']);//逾期天数
                $d1 = strtotime($Date_1);
                $d2 = strtotime($Date_2);
                $data['overdue_money'] = 0;
                if($d1>=$d2){
                    $Days = round(($d2-$d1)/3600/24);
                    if($Days<0){
                        $data['overdue_money'] = sprintf('%.2f',($data['trade_price'] * abs($Days) * $data['pay_fee']));
                        pdo_update('customs_collection',['overdue_money'=>$data['overdue_money']],['id'=>$data['id']]);
                    }
                }
            }
        }
        
        //实付金额
        $data['total_money'] = sprintf('%.2f',($data['trade_price']+$data['overdue_money']));
        
        //付款依据
        if($data['basic']==1){
            $data['basic'] = '合同';
            $data['contract_file'] = json_decode($data['contract_file'],true);
        }elseif($data['basic']==2){
            $data['basic'] = '订单';
            $data['orderdemo'] = json_decode($data['orderdemo'],true);
        }elseif($data['basic']==3){
            $data['basic'] = '备注信息';
        }

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

        if(1>2){
            #测试
            #判断仓库类型是否为“直发”的商品==========================start
            $orderlist = pdo_fetch('select * from '.tablename('website_order_list').' where pay_id=:pay_id',[':pay_id'=>$_GPC['oid']]);
            $orderlist['content'] = json_decode($orderlist['content'],true);

            $other_db = new DB($other_database);
            $express_info = [];
            $warehouse_id = 0;
            $is_baoyou = 3;//1（寄方月结）&2（寄方结算）都是包邮，3是买家付款（不包邮）
            $goods_type = 0;//单品
            foreach($orderlist['content']['goods_info'] as $k=>$v){
                $express_info = $other_db->fetch('select express_info,shop_id,wid,is_baoyou,goods_type from goods where goods_id=:gid',[':gid'=>$v['good_id']]);
                $warehouse_id = $express_info['wid'];
                $is_baoyou = $express_info['is_baoyou'];
                $goods_type = $express_info['goods_type'];
            }

            #判断该商品的仓库的截单时间
            $warehouse_info = pdo_fetch('select * from '.tablename('centralize_warehouse_list').' where id=:wid',[':wid'=>$warehouse_id]);
            $warehouse_info['process_time_config'] = json_decode($warehouse_info['process_time_config'],true);#仓库截单时间
    //                $warehouse_info['platform_time_config'] = json_decode($warehouse_info['platform_time_config'],true);#平台发货清单时间

            if($warehouse_info['warehouse_form']==1){
                #直接发货（代发货的需要在卖家后台中操作发货）

                if($warehouse_info['process_time_config']['type']==1){
                    #每x天（这里做每日）
                    $hour = date('H');
                    if($hour>=$warehouse_info['process_time_config']['hours_start'] && $hour<=$warehouse_info['process_time_config']['hours']){

                        #在仓库终端指定时间内通知打印机
                        if(!empty($orderlist['freight_id'])){
                            #不包邮：才有运费详情id
                            $freight_data = pdo_fetch('select * from '.tablename('centralize_freight_config').' where id=:id',[':id'=>$orderlist['freight_id']]);
                            notify_terminal(['order_id'=>$orderlist['id'],'express_id'=>$freight_data['express_id'],'company_id'=>$orderlist['company_id'],'warehouse_id'=>$warehouse_id]);
                        }else{
                            #包邮：查找商品上架的时候所选的运费id
                            $express_infos = json_decode($express_info['express_info'],true);
                            $express_id = $express_infos['express_info'][0]['express_id'];
                            notify_terminal(['order_id'=>$orderlist['id'],'express_id'=>$express_id,'company_id'=>$orderlist['company_id'],'warehouse_id'=>$warehouse_id]);
                        }
                    }
                }
                elseif($warehouse_info['process_time_config']['type']==2){
                    #每周x（待做）

                }
                elseif($warehouse_info['process_time_config']['type']==3){
                    #每月x日（待做）

                }
                elseif($warehouse_info['process_time_config']['type']==4){
                    #每隔x时（待做）

                }
            }
            elseif($warehouse_info['warehouse_form']==2){
                #代发货商品，需要卖家在后台执行“手动发货”或“他人发货”

            }
            dd(2);
            #判断是否仓库类型为“直发”的商品==========================end
        }

        if($data['trade_type']==1){
            $data['trade_type_name'] = '商品';
            if($data['order_type']==1){
                //新商城订单商品
                $other_db = new DB($other_database);
                $order = pdo_fetch('select * from '.tablename('website_order_list').' where pay_id=:pay_id',[':pay_id'=>intval($_GPC['oid'])]);
                if($order['origin_type']==1){
                    #其他平台商品
                    $goods = $other_db->fetch('select * from goods_backydrop where id=:gid',[':gid'=>$data['good_id']]);
                    $goods['content'] = json_decode($goods['content'],true);
                    $goods['goods_name'] = $goods['content']['goodsName'];
                }elseif($order['origin_type']==0){
                    #本平台商品
                    $goods = $other_db->fetch('select * from goods where goods_id=:gid',[':gid'=>$data['good_id']]);
                }

                $name = $goods['goods_name'];
            }else{
                $name = pdo_fetchcolumn('select title from '.tablename('sz_yi_goods').' where id=:id',array(':id'=>$data['good_id']));
//                $data['trade_type_name'] = '商品';
            }
        }elseif($data['trade_type']==2){
            $name = pdo_fetchcolumn('select project_name from '.tablename('customs_project').' where id=:id',array(':id'=>$data['project_id']));
            $data['trade_type_name'] = '项目';
        }elseif($data['trade_type']==3){
            $data['trade_type_name'] = '服务';
            $data['service_info'] = json_decode($data['service_info'],true);
            foreach($data['service_info'] as $k=>$v){
                $data['service_info'][$k] = explode(',',$v);
            }
            $name = '多项服务';
        }

        //查询是否已提交线下收款信息
        $other_pay = pdo_fetch('select * from '.tablename('customs_collection_otherpay').' where orderid=:orderid and uniacid=:uni',[':orderid'=>trim($_GPC['oid']),':uni'=>$_W['uniacid']]);
        if($other_pay['payment_mode']==1){
            $other_pay['transfer_demo'] = json_decode($other_pay['transfer_demo'],true);
        }elseif($other_pay['payment_mode']==2){
            $other_pay['collect_demo'] = json_decode($other_pay['collect_demo'],true);
        }

        //待确认收款,收款那个人审核
        $iswaitcheck = intval($_GPC['iswaitcheck']);

        //查询平台账户
        $platform_account = pdo_fetchall('select * from '.tablename('onshore_account').' where id!=1 order by id desc');
        //查询收款人账户
        $sender_account = pdo_fetchall('select * from '.tablename('customs_bank_account').' where openid=:send_openid order by id desc',[':send_openid'=>$data['send_openid']]);

        include $this->template('member/custompayment');
    }
}
elseif($op=='pay'){
    
    //支付通道
    $member = pdo_fetch('select * from '.tablename('sz_yi_member').' where openid=:openid',[':openid'=>trim($_W['openid'])]);
    // dd($member);
    $member['openid'] = $_W['openid'];
    $uniacid = $_W['uniacid'];
    $orderid = intval($_GPC['orderid']);
    $ordersn_general = pdo_fetchcolumn('select ordersn from ' . tablename('customs_collection') . ' where id=:id and uniacid=:uniacid and openid=:openid limit 1', array(':id' => $orderid, ':uniacid' => $uniacid, ':openid' => $member['openid']));
    if(empty($ordersn_general)){
        $ordersn_general = pdo_fetchcolumn('select ordersn from ' . tablename('customs_collection') . ' where id=:id and uniacid=:uniacid and openid=:openid limit 1', array(':id' => $orderid, ':uniacid' => $uniacid, ':openid' => $member['mobile']));
    }
    if(empty($ordersn_general)){
        //其他人进来查看
        show_json(1,'');
    }
	$order = pdo_fetch('select * from ' . tablename('customs_collection') . ' where ordersn=:ordersn and uniacid=:uniacid and openid=:openid', array(':ordersn' => $ordersn_general, ':uniacid' => $uniacid, ':openid' => $member['openid']));

    if(empty($order)){
        $order = pdo_fetch('select * from ' . tablename('customs_collection') . ' where ordersn=:ordersn and uniacid=:uniacid and openid=:openid', array(':ordersn' => $ordersn_general, ':uniacid' => $uniacid, ':openid' => $member['mobile']));
    }

	if (empty($orderid)) {
		show_json(0, '参数错误!');
	}

	if (empty($order)) {
		show_json(0, '订单未找到!');
	}

	if ($order['status'] == -1) {
		show_json(-1, '订单已关闭, 无法付款!');
	}
	else {
		if (1 <= $order['status'] && intval($_GPC['iswaitcheck'])!=1 && $_W['openid']==$order['openid']) {
//			show_json(-1, '订单已付款, 无需重复支付!');
		}
	}
	
	$log = pdo_fetch('SELECT * FROM ' . tablename('core_paylog') . ' WHERE `uniacid`=:uniacid AND `module`=:module AND `tid`=:tid limit 1', array(':uniacid' => $uniacid, ':module' => 'sz_yi', ':tid' => $ordersn_general));

	if (!empty($log) && ($log['status'] != '0')) {
		show_json(-1, '订单已支付, 无需重复支付!');
	}
	
	if (!empty($log) && ($log['status'] == '0')) {
		pdo_delete('core_paylog', array('plid' => $log['plid']));
		$log = NULL;
	}
	
	if (empty($log)) {
		$log = array('uniacid' => $uniacid, 'openid' => $member['openid'], 'module' => 'sz_yi', 'tid' => $ordersn_general, 'fee' => $order['trade_price'], 'status' => 0);
		pdo_insert('core_paylog', $log);
		$plid = pdo_insertid();
	}
	$set = m('common')->getSysset(array('shop', 'pay'));
	
	//2017-11-16 通莞微信支付
	$tgwechat = array('success' => false);
	$tgalipay = array('success' => false);

	if (($set['pay']['tgpaystatus']) && !empty($set['pay']['tgpay'])) {
		$tgwechat['success'] = true;
		$tgalipay['success'] = true;
	}
	load()->model('payment');
	$setting = uni_setting($_W['uniacid'], array('payment'));
	$wechat = array('success' => false, 'qrcode' => false);
	$jie = $set['pay']['weixin_jie'];

	if (is_weixin()) {
		if (isset($set['pay']) && ($set['pay']['weixin'] == 1) && ($jie != 1)) {
			if (is_array($setting['payment']['wechat']) && $setting['payment']['wechat']['switch']) {
				$wechat['success'] = true;
				$wechat['weixin'] = true;
				$wechat['weixin_jie'] = false;
			}
		}
	}

	if (strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false) {
		if ((isset($set['pay']) && ($set['pay']['weixin_jie'] == 1) && !$wechat['success']) || ($jie == 1)) {
			$wechat['success'] = true;
			$wechat['weixin_jie'] = true;
			$wechat['weixin'] = false;
		}
	}

	$wechat['jie'] = $jie;
	if (!isMobile() && isset($set['pay']) && ($set['pay']['weixin'] == 1)) {
		if (isset($set['pay']) && ($set['pay']['weixin'] == 1)) {
			if (is_array($setting['payment']['wechat']) && $setting['payment']['wechat']['switch']) {
				$wechat['qrcode'] = true;
			}
		}
	}

	$returnurl = '';
	show_json(1, array('order' => $order, 'set' => $set, 'wechat' => $wechat, 'tgwechat' => $tgwechat, 'tgalipay' => $tgalipay, 'isweixin' => is_weixin(), 'returnurl' => $returnurl));
}
elseif($op=='tgpay'){
    $type = trim($_GPC['type']);
    if(!in_array($type, array('weixin', 'alipay', 'app_alipay', 'app_weixin', 'unionpay', 'yunpay', 'yeepay', 'paypal', 'yeepay_wy','tgwechat','tgalipay','helpay'))) {
			show_json(0, '未找到支付方式');
	}
	$set = m('common')->getSysset(array('shop', 'pay'));
	$order = pdo_fetch('select * from '.tablename('customs_collection').' where id=:id and uniacid=:uni and openid=:openid',array(':id'=>intval($_GPC['orderid']),':openid'=>$_GPC['openid'],':uni'=>$_W['uniacid']));
	if(empty($order)){
        $member = m('member')->getMember(trim($_GPC['openid']));
        $order = pdo_fetch('select * from '.tablename('customs_collection').' where id=:id and uniacid=:uni and openid=:openid',array(':id'=>intval($_GPC['orderid']),':openid'=>$member['mobile'],':uni'=>$_W['uniacid']));
    }
	if(empty($order)){
	    show_json(0,'参数错误');
	}
    $name = '';
    if($order['trade_type']==1){
        $name = pdo_fetchcolumn('select title from '.tablename('sz_yi_goods').' where id=:id',array(':id'=>$order['good_id']));
    }elseif($order['trade_type']==2){
        $name = pdo_fetchcolumn('select project_name from '.tablename('customs_project').' where id=:id',array(':id'=>$order['project_id']));
    }elseif($order['trade_type']==3){
        $name = '多项服务';
    }
    if($type == 'tgwechat'){
        if (empty($set['pay']['tgpay'])) {
			show_json(0, '未开启通莞微信支付!');
		}

		$tgwechat = array('success' => false);
		$params = array();
		$params['tid'] = $order['ordersn'];
        $params['openid']  = $order['openid'];
		if(is_numeric($order['openid'])){
            $params['openid']  = $_W['openid'];
        }

		$params['fee']   = $order['trade_price'] + $_GPC['overdue_money'];
		$params['title'] = $name;
		load()->model('payment');
		
		if (is_array($set['pay']['tgpay'])) {
			$payment = 	$set['pay']['tgpay'];
			if (is_weixin()) {
				
				if ($set['pay']['tgpaystatus']) {
					
					$params['account']  = $options['account'] = $payment['mchid'];
					$params['key'] 		= $options['key'] 	  = $payment['key'];
					$params['token']	= 'wechat';
					
					$tgwechat['tid']	= $params['tid'];
					$tgwechat['openid']	= $params['openid'];
					$tgwechat['fee']	= $params['fee'];
					$tgwechat['title']	= $params['title'];
					$tgwechat['account']= $params['account'];
					$tgwechat['key']	= $params['key'];
					$tgwechat['token']	= $params['token'];
					$tgwechat['uniacid']= $_W['uniacid'];
					$tgwechat['success'] = true;
					
					if(empty($tgwechat)){
						show_json(0, '数据为空！');
					}
					
				}else {
					$tgwechat['success'] = FALSE;
				}
			}
			
			if (!$tgwechat['success']) {
				show_json(0, '微信支付参数错误!');
			}
			//2022-01-03 记录支付总金额和逾期金额
			pdo_query('update '.tablename('customs_collection').' set pay_type=1,overdue_money='.$_GPC['overdue_money'].',total_money='.$params['fee'].' where id=:id and uniacid=3',array(':id'=>$order['id']));
			show_json(1, array('tgwechat' => $tgwechat));
		}
		
    }
    elseif($type == 'tgalipay'){
        $param_title = $shopset['name'] . '订单:' . $order['ordersn'];
        
    	$tgalipay = array('success' => false);
    	$params = array();
    	$params['tid'] = $order['ordersn'];
    	$params['user'] = $order['openid'];
        if(is_numeric($order['openid'])){
            $params['user']  = $_W['openid'];
        }
    	$params['fee'] = $order['trade_price'] + $_GPC['overdue_money'];
    	$params['title'] = $name;
    	$params['typ'] = 'custompayment';
    	load()->func('communication');
    	//记录支付总金额和逾期金额
    	pdo_query('update '.tablename('customs_collection').' set pay_type=2,overdue_money='.$_GPC['overdue_money'].',total_money='.$params['fee'].' where id=:id and uniacid=3',array(':id'=>$order['id']));
    	
    	if (!empty($set['pay']['tgpay']) && ($set['pay']['tgpaystatus'] == 1)) {
    	    $options = $set['pay']['tgpay'];
    		//获取该公众号配置
    		$config = array(
    			'mchid'=>$options['mchid'],
    			'key'=>$options['key'],
    		);
    		
    		$tgalipayRes = m('common')->Tgalipay_scode($params, $config);
    		//"下游订单号重复"
    		if ($tgalipayRes['status'] == '100') {
    			header("Location:".$tgalipayRes['codeUrl']);
    			exit('正在跳转支付宝支付...');
    		}else if ($tgalipayRes['status'] == '101') {
	
				$params['tid'] = 'GP' . date('YmdH', time()) . str_pad(mt_rand(1, 999999), 6, '0',
                STR_PAD_LEFT) . substr(microtime(), 2, 6);//重新生成订单
                //更新订单数据
    			$result = pdo_update('customs_collection', ['ordersn' => $params['tid']], array('id' => $order['id']));
    			pdo_update('core_paylog',['tid'=>$params['tid']],array('tid'=>$order['ordersn']));
    			if (!empty($result)) {
    				$tgalipayRes = m('common')->Tgalipay_scode($params, $config);
    				if ($tgalipayRes['status'] == '100') {
    					header("Location:".$tgalipayRes['codeUrl']);
    					exit('正在跳转支付宝支付...');
    				}else {
    					$tgalipay['success'] = FALSE;
    					$tgalipay['message'] = $tgalipayRes['message'];
    					
    					exit($tgalipayRes['message']);
    				}
    			}
		    }
    	}
    }
}
elseif($op=='otherpay'){
    $orderid = intval($_GPC['orderid']);
    $order = pdo_fetch('select * from '.tablename('customs_collection').' where id=:id and uniacid=:uni and status=0',[':id'=>$orderid,':uni'=>$_W['uniacid']]);
    if(empty($order)){
        show_json(-1, '参数错误!');
    }

    //step1:查询是否已创建收款记录
    $isHavaLog = pdo_fetchcolumn('select id from '.tablename('customs_collection_otherpay').' where orderid=:orderid and uniacid=:uni and pay_openid=:pay_openid and status=0',[':orderid'=>$orderid,':uni'=>$_W['uniacid'],':pay_openid'=>$_W['openid']]);
    if(empty($isHavaLog)){
        $member = m('member')->getMember($_W['openid']);
        $isHavaLog = pdo_fetchcolumn('select id from '.tablename('customs_collection_otherpay').' where orderid=:orderid and uniacid=:uni and pay_openid=:pay_openid and status=0',[':orderid'=>$orderid,':uni'=>$_W['uniacid'],':pay_openid'=>$member['mobile']]);
    }
    $payment_mode = intval($_GPC['payment_mode']);
    $insert_data = [
        'uniacid'=>$_W['uniacid'],
        'pay_openid'=>$_W['openid'],
        'collect_openid'=>$order['send_openid'],
        'orderid'=>$orderid,
        'payment_mode'=>$payment_mode,
        'pay_account'=>$payment_mode==1?trim($_GPC['pay_account']):'',
        'transfer_price'=>$payment_mode==1?trim($_GPC['transfer_price']):'',
        'collect_account'=>$payment_mode==1?trim($_GPC['collect_account']):'',
        'transfer_demo'=>$payment_mode==1?json_encode($_GPC['transfer_demo'],true):'',
        'true_pay_price'=>$payment_mode==2?trim($_GPC['true_pay_price']):'',
        'cash_paytime'=>$payment_mode==2?trim($_GPC['cash_paytime']):'',
        'collect_staff'=>$payment_mode==2?trim($_GPC['collect_staff']):'',
        'collect_demo'=>$payment_mode==2?json_encode($_GPC['collect_demo'],true):'',
        'status'=>0,
        'createtime'=>time()
    ];

    if(empty($isHavaLog)){
        //判断付款人openid是否电话
        if(is_numeric($order['openid'])){
            pdo_update('customs_collection',['openid'=>$insert_data['pay_openid']],['id'=>$orderid,'uniacid'=>$_W['uniacid']]);
        }

        $res = pdo_insert('customs_collection_otherpay',$insert_data);
    }else{
        //判断付款人openid是否电话
        $insert_data2 = [];
        if(is_numeric($order['openid'])){
            $insert_data2 = array_merge($insert_data2,['is_nocheck_send'=>0,'openid'=>$insert_data['pay_openid']]);
        }else{
            $insert_data2 = ['is_nocheck_send'=>0];
        }

        pdo_update('customs_collection',$insert_data2,['id'=>$orderid,'uniacid'=>$_W['uniacid']]);
        $res = pdo_update('customs_collection_otherpay',$insert_data,['orderid'=>$orderid,'uniacid'=>$_W['uniacid'],'pay_openid'=>$_W['openid']]);
    }

    //step2:发送确认收款通知
    $send_member = pdo_fetchcolumn('select user_name from '.tablename('decl_user').' where openid=:openid limit 1',array(':openid'=>$_W['openid']));
    if(empty($send_member)){
        $send_member = pdo_fetchcolumn('select nickname from '.tablename('sz_yi_member').' where openid=:openid limit 1',array(':openid'=>$_W['openid']));
    }
    $post = json_encode([
        'call'=>'confirmCollectionNotice',
        'first' =>'您的客户（'.$send_member.'）提交了确认收款通知，请及时审核！',
        'keyword1' => $order['ordersn'],
        'keyword2' => $payment_mode==1?'CNY '.$insert_data['transfer_price']:'CNY '.$insert_data['true_pay_price'],
        'keyword3' => '本人',
        'remark' => '请点击查看详情',
        'url' => 'https://shop.gogo198.cn/app/index.php?i=3&c=entry&do=member&p=custompayment&m=sz_yi&iswaitcheck=1&oid='.$orderid,
        'openid' => $insert_data['collect_openid'],
        'temp_id' => 'Nv1opIIHshICu1Lx1QfnGK_RsoLztCuLJrTgQr7VeKQ'
    ]);
    ihttp_request('https://shop.gogo198.cn/api/sendwechattemplatenotice.php', $post);

    if($res){
        show_json(1, '提交成功!');
    }
}
elseif($op=='otherpay_bankaccount'){
    $orderid = intval($_GPC['orderid']);
    $order = pdo_fetch('select * from '.tablename('customs_collection').' where id=:id and uniacid=:uni and status=0',[':id'=>$orderid,':uni'=>$_W['uniacid']]);
    if(empty($order)){
        show_json(-1, '参数错误!');
    }

    //step1:查询是否已创建收款记录
    $isHavaLog = pdo_fetchcolumn('select id from '.tablename('customs_collection_otherpay').' where orderid=:orderid and uniacid=:uni and pay_openid=:pay_openid and status=0',[':orderid'=>$orderid,':uni'=>$_W['uniacid'],':pay_openid'=>$_W['openid']]);

    $payment_mode = intval($_GPC['payment_mode']);
    $insert_data = [
        'uniacid'=>$_W['uniacid'],
        'pay_openid'=>$_W['openid'],
        'collect_openid'=>$order['send_openid'],
        'orderid'=>$orderid,
        'bankaccount_mode'=>intval($_GPC['bankaccount_mode']),//bankaccount_mode
        'bank_account'=>intval($_GPC['bank_account']),//bank_account
        'status'=>0,
        'createtime'=>time()
    ];
    if(empty($isHavaLog)){
        //判断付款人openid是否电话
        if(is_numeric($order['openid'])){
            pdo_update('customs_collection',['openid'=>$insert_data['pay_openid']],['id'=>$orderid,'uniacid'=>$_W['uniacid']]);
        }
        $res = pdo_insert('customs_collection_otherpay',$insert_data);
    }else{
        $insert_data2 = [];
        if(is_numeric($order['openid'])){
            $insert_data2 = array_merge($insert_data2,['is_nocheck_send'=>0,'openid'=>$insert_data['pay_openid']]);
        }else{
            $insert_data2 = ['is_nocheck_send'=>0];
        }
        pdo_update('customs_collection',$insert_data2,['id'=>$orderid,'uniacid'=>$_W['uniacid']]);
        $res = pdo_update('customs_collection_otherpay',$insert_data,['orderid'=>$orderid,'uniacid'=>$_W['uniacid'],'pay_openid'=>$_W['openid']]);
    }
    if($res){
        //step2:发送确认收款通知
//        $send_member = pdo_fetchcolumn('select user_name from '.tablename('decl_user').' where openid=:openid limit 1',array(':openid'=>$_W['openid']));
//        if(empty($send_member)){
//            $send_member = pdo_fetchcolumn('select nickname from '.tablename('sz_yi_member').' where openid=:openid limit 1',array(':openid'=>$_W['openid']));
//        }
//        $post = json_encode([
//            'call'=>'confirmCollectionNotice',
//            'first' =>'您的客户（'.$send_member.'）提交了确认收款通知，请及时审核！',
//            'keyword1' => $order['ordersn'],
//            'keyword2' => $payment_mode==1?'CNY '.$insert_data['transfer_price']:'CNY '.$insert_data['true_pay_price'],
//            'keyword3' => '本人',
//            'remark' => '请点击查看详情',
//            'url' => 'https://shop.gogo198.cn/app/index.php?i=3&c=entry&do=member&p=custompayment&m=sz_yi&iswaitcheck=1&oid='.$orderid,
//            'openid' => $insert_data['collect_openid'],
//            'temp_id' => 'Nv1opIIHshICu1Lx1QfnGK_RsoLztCuLJrTgQr7VeKQ'
//        ]);
//        ihttp_request('https://shop.gogo198.cn/api/sendwechattemplatenotice.php', $post);

        show_json(1, '提交成功!');
    }
}
elseif($op=='examine'){
    $type = intval($_GPC['type']);
    $orderid = intval($_GPC['orderid']);
    $order = pdo_fetch('select * from '.tablename('customs_collection').' where id=:id and uniacid=:uni and send_openid=:send_openid',[':id'=>$orderid,':uni'=>$_W['uniacid'],':send_openid'=>$_W['openid']]);
    $other_pay = pdo_fetch('select * from '.tablename('customs_collection_otherpay').' where orderid=:orderid and collect_openid=:c_openid',[':orderid'=>$order['id'],':c_openid'=>$order['send_openid']]);
    if(empty($order)){
        show_json(-1, '参数错误!');
    }
    if($order['status']==1){
        show_json(-1, '订单已支付，无需审核!');
    }

    if($type==1){
        //step1：确认收款后通知付款人
        $type2 = '';
        if($order['trade_type']==1){
            $type2='商品';
        }elseif($order['trade_type']==2){
            $type2='项目';
        }elseif($order['trade_type']==3){
            $type2='多项服务';
        }
        $pay_money = 0;
        if($other_pay['payment_mode']==1){
            $pay_money = $other_pay['transfer_price'];
        }elseif($other_pay['payment_mode']==2){
            $pay_money = $other_pay['true_pay_price'];
        }
        $post2 = json_encode([
            'call'=>'collectionNotice',
            'first'=>'您好，您已经完成新订单的处理，点击查看详情，如有疑问，敬请联系客服075786329911，感谢您的支持！',
            'keyword1'=>$order['ordersn'],
            'keyword2'=>$type2,
            'keyword3'=>'CNY '.$pay_money,
            'keyword4'=>'线下支付',
            'keyword5'=>date('Y-m-d H:i:s',time()),
            'remark' =>'感谢您的使用',
            'openid' =>$order['openid'],
            'uniacid'=>$order['uniacid'],
            'temp_id'=>'tHWxOL4Kc3v6uZinHT3Zo661I8o6EbAg46XKUP0FnnY',
            'url'=>'https://shop.gogo198.cn/app/index.php?i=3&c=entry&do=member&p=custompayment&m=sz_yi&oid='.$order['id']
        ]);
        ihttp_request('https://shop.gogo198.cn/api/sendwechattemplatenotice.php', $post2);

        //todo：如果是商城订单，则需要通知供货商（平台客服-API/商家/买手）
        $website_order = pdo_fetch('select * from '.tablename('website_order_list').' where pay_id='.$orderid);
        if(!empty($website_order)){
            //商城订单的通知
            if($website_order['buyer_id'] > 0){
                #通知买手或API客服

            }
            if($website_order['company_id'] > 0){
                #通知商家

            }
            if($website_order['company_id']==0 && $website_order['buyer_id']){
                #通知API客服

            }
        }else{
            //普通收付款的通知

            //step2:通知管理员（老板）
            $post3 = json_encode([
                'call'=>'collectionNotice',
                'first'=>'您好，有［'.$type2.'］订单状态已变更为［订单已付］，点击查看详情！',
                'keyword1'=>$order['ordersn'],
                'keyword2'=>$type2,
                'keyword3'=>'CNY '.$pay_money,
                'keyword4'=>'线下支付',
                'keyword5'=>date('Y-m-d H:i:s',time()),
                'remark' =>'',
                'openid' =>'ov3-bt8keSKg_8z9Wwi-zG1hRhwg',
                'uniacid'=>$order['uniacid'],
                'temp_id'=>'tHWxOL4Kc3v6uZinHT3Zo661I8o6EbAg46XKUP0FnnY',
                'url'=>'https://shop.gogo198.cn/app/index.php?i=3&c=entry&do=member&p=custompayment&m=sz_yi&oid='.$order['id'].'&isadmin=1'
            ]);
            ihttp_request('https://shop.gogo198.cn/api/sendwechattemplatenotice.php', $post3);

            //step3:通知发起人（收款人）
            $post = json_encode([
                'call'=>'collectionNotice',
                'first'=>'您有一笔收款信息',
                'keyword1'=>$order['payer_name'],
                'keyword2'=>'CNY '.$pay_money,
                'keyword3'=>'线下支付',
                'keyword4'=>date('Y-m-d H:i:s',time()),
                'keyword5'=>$order['ordersn'],
                'remark' =>'感谢您的使用',
                'openid' =>$order['send_openid'],
                'uniacid'=>$order['uniacid'],
                'temp_id'=>'WcvDClChgUbLfWHu5jQw5TEilYU36VdNDH514KZ-f4w',
                'url'=>'https://shop.gogo198.cn/app/index.php?i=3&c=entry&do=member&p=custompayment&m=sz_yi&oid='.$order['id'].'&isadmin=1'
            ]);
            ihttp_request('https://shop.gogo198.cn/api/sendwechattemplatenotice.php', $post);
        }

        //确认收款
        $res = pdo_update('customs_collection',[
            'press_money_type'=>'',
            'press_money_day'=>'',
            'press_notice_time'=>'',
            'pay_type'=>3,
            'status'=>1,
            'paytime'=>time()
            ],['id'=>intval($_GPC['orderid']),'uniacid'=>$_W['uniacid'],'send_openid'=>$_W['openid'],'status'=>0]);
        if($res){
            show_json(1, '提交成功!');
        }else{
            show_json(-1, '提交失败!');
        }
    }elseif($type==2){
        //未予收款
        if($order['is_nocheck_send']==1){
            show_json(-1, '此次审核已通知，请勿重复通知!');
        }
        $post = json_encode([
            'call'=>'confirmCollectionNotice',
            'first' =>'很抱歉, 你提交的付款方式，经审核结果为[查未到账]',
            'keyword1' => date('Y-m-d H:i:s',time()),
            'keyword2' => '订单号：'.$order['ordersn'],
            'keyword3' => '您提交的付款方式，未予收到。为了资金安全，请尽快与客服确认情况！',
            'remark' => '请点击查看详情',
            'url' => 'https://shop.gogo198.cn/app/index.php?i=3&c=entry&do=member&p=custompayment&m=sz_yi&oid='.$orderid,
            'openid' => $order['openid'],
            'temp_id' => 'cIqMYWBtz7fCxwU9uDZj47m_st-dN8qNJvEt5hU4UV4'
        ]);
        $res = ihttp_request('https://shop.gogo198.cn/api/sendwechattemplatenotice.php', $post);
        if($res){
            pdo_update('customs_collection',['is_nocheck_send'=>1],['id'=>intval($_GPC['orderid']),'uniacid'=>$_W['uniacid'],'send_openid'=>$_W['openid'],'status'=>0]);
            show_json(1);
        }
    }elseif($type==3){
        //到账不全
        if($order['is_nocheck_send']==1){
            show_json(-1, '此次审核已通知，请勿重复通知!');
        }
        $post = json_encode([
            'call'=>'confirmCollectionNotice',
            'first' =>'很抱歉, 你提交的付款方式，经审核结果为[到账不全]',
            'keyword1' => date('Y-m-d H:i:s',time()),
            'keyword2' => '订单号：'.$order['ordersn'],
            'keyword3' => '您提交的付款方式，到账不全。为了资金安全，请尽快与客服确认情况！',
            'remark' => '应到账金额[CNY '.sprintf('%.2f',$_GPC['should_payMoney']).']',
            'url' => 'https://shop.gogo198.cn/app/index.php?i=3&c=entry&do=member&p=custompayment&m=sz_yi&oid='.$orderid,
            'openid' => $order['openid'],
            'temp_id' => 'cIqMYWBtz7fCxwU9uDZj47m_st-dN8qNJvEt5hU4UV4'
        ]);
        $res = ihttp_request('https://shop.gogo198.cn/api/sendwechattemplatenotice.php', $post);
        if($res){
            pdo_update('customs_collection',['is_nocheck_send'=>1],['id'=>intval($_GPC['orderid']),'uniacid'=>$_W['uniacid'],'send_openid'=>$_W['openid'],'status'=>0]);
            show_json(1);
        }
    }
}
elseif($op=='save_bankaccount_pdf'){
    $bankMode = intval($_GPC['bankMode']);
    $bankAcc = intval($_GPC['bankAcc']);
    if($bankMode==1){
        $bank = pdo_fetch('select * from '.tablename('onshore_account').' where id=:bank',[':bank'=>$bankAcc]);
    }elseif($bankMode==2){
        $bank = pdo_fetch('select * from '.tablename('customs_bank_account').' where id=:bank',[':bank'=>$bankAcc]);
    }
    include $this->template('member/save_bankaccount_pdf');
}
elseif($op=='sure_attestation'){
    #确认付款页面
    $id = intval($_GPC['oid']);
    $info = pdo_fetch('select * from '.tablename('customs_collection').' where id=:id',[':id'=>$id]);
    include $this->template('member/sure_attestation');
}


?>