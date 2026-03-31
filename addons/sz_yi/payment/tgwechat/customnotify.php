<?php
// 2025-03-07：微信支付回调

ini_set('display_errors', 'On');
//define('IN_MOBILE', true);
//error_reporting(30719 ^ 8);
//global $_W;
//global $_GPC;

require '../../../../framework/bootstrap.inc.php';
require '../../../../addons/sz_yi/defines.php';
require '../../../../addons/sz_yi/core/inc/functions.php';
require '../../../../addons/sz_yi/core/inc/plugin/plugin_model.php';

//载入日志函数
//获取文件流
$input = file_get_contents('php://input');
//写入日志
file_put_contents('./log/customnotify.log', $input."\r\n",FILE_APPEND);
//将接受到的Json数据转换成数组格式。
$data = json_decode($input, true);
//echo $_W['siteroot'] . 'addons/sz_yi/payment/tgwechat/notify.log';
if (!empty($data)) {
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
	if(empty($order)){
		$answer['finished'] = 'FAIL';
		echo json_encode($answer);die;
	}
	
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
			m('common')->paylog($data);
			m('common')->paylog('status');
            load()->func('communication');
            
			pdo_update('customs_collection', array(
			    'status' => '1',
                'ordersn_general'=>$data['upOrderId'],
                'paytime'=>strtotime($data['payTime'])
            ), array('id' => $order['id']));

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

                #判断订单是否“国内收货/自主集运”（那就打印面单&通知仓库打包发货），若是“平台集运”就...
                if($orderlist['content']['delivery_method']==1 || $orderlist['content']['gather_method']==2){
                    #判断仓库类型是否为“直发”的商品==========================start
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
//                    $warehouse_info['platform_time_config'] = json_decode($warehouse_info['platform_time_config'],true);#平台发货清单时间

                    #ps:代发货的商品，需要卖家在后台执行“手动发货”或“他人发货”
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
                    #判断是否仓库类型为“直发”的商品==========================end
                }

                #会员订单==========================start
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
                #会员订单==========================end
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

            //如果是商城订单，则需要通知供货商（平台客服-API/商家/买手）
            $website_order = pdo_fetch('select * from '.tablename('website_order_list').' where pay_id='.$order['id']);
            $member = pdo_fetch('select * from '.tablename('website_user').' where id=:id',[':id'=>$website_order['user_id']]);
            //step1:成功支付后，发送消息给本人
            $type = '';
            if ($order['trade_type'] == 1) {
                $type = '商品';
            } elseif ($order['trade_type'] == 2) {
                $type = '项目';
            } elseif ($order['trade_type'] == 3) {
                $type = '多项服务';
            }
            if (is_numeric($order['openid'])) {
                $order['openid'] = pdo_fetchcolumn('select openid from ' . tablename('sz_yi_member') . ' where mobile=:mob', [':mob' => $order['openid']]);
            }
            $post2 = json_encode([
                'call' => 'collectionNotice',
                'first' => '您好，您已经完成新订单的处理，点击查看详情，如有疑问，敬请联系客服075786329911，感谢您的支持！',
                'keyword1' => $order['ordersn'],
                'keyword2' => $type,
                'keyword3' => 'CNY ' . $order['total_money'],
                'keyword4' => $zf_type,
                'keyword5' => date('Y-m-d H:i:s', time()),
                'remark' => '感谢您的使用',
                'openid' => $order['openid'],
                'uniacid' => $order['uniacid'],
                'temp_id' => 'tHWxOL4Kc3v6uZinHT3Zo661I8o6EbAg46XKUP0FnnY',
                'url' => 'https://shop.gogo198.cn/app/index.php?i=3&c=entry&do=member&p=custompayment&m=sz_yi&oid=' . $order['id']
            ]);
            ihttp_request('https://shop.gogo198.cn/api/sendwechattemplatenotice.php', $post2);

            if(!empty($website_order)){
                if($website_order['is_level']==0){
                    #商品订单
                    //商城订单的通知
                    if($website_order['buyer_id'] > 0){
                        #通知买手或API客服
                        $buyer = pdo_fetch('select b.openid,a.id,a.type from '.tablename('website_buyer').' a left join '.tablename('website_user').' b on b.id=a.uid where a.id=:id',[':id'=>$website_order['buyer_id']]);

                        if($buyer['type']==1){
                            #接口买手
                            $time = time();
                            #通知接口下单
                            $other_db = new DB($other_database);
                            $productList = [];
                            $gather_method = 1;//1平台集运，2自主集运（要求要买家的收货地址）
                            if(!empty($order['content']['goods_info'])){
                                foreach($order['content']['goods_info'] as $k=>$v){
                                    $goods = $other_db->fetch('select * from goods where id=:gid',[':gid'=>$data['good_id']]);
                                    foreach($v['sku_info'] as $k2=>$v2){
                                        $sku_goods = $other_db->fetch('select * from goods_sku where sku_id=:sku_id',[':sku_id'=>$v2['sku_id']]);
                                        $sku_goods['sku_prices'] = json_decode($sku_goods['sku_prices'],true);

                                        array_push($productList,[
                                            'platform'=>$goods['other_platform'],
                                            'productCount'=>$v2['goods_num'],
                                            'productLink'=>$goods['other_goods_link'],
                                            'productName'=>$goods['goods_name'],
                                            'productPrice'=>$sku_goods['sku_prices']['price'][0],#订购产品单价
                                            'skuCode'=>$sku_goods['goods_sn'],
                                            'spuCode'=>$goods['other_spuCode'],
                                            'productImage'=>$goods['goods_image'],
                                            'orderRemark'=>'买家：'.$member['custom_id']
                                        ]);
                                    }
                                }
                                #delivery_method：1中国收货，2海外收货
                                if($order['content']['delivery_method']==1){
                                    $gather_method = 2;
                                }
                                elseif($order['content']['delivery_method']==2){
                                    $gather_method = $order['content']['gather_method'];
                                }
                            }
//                    {"goods_info":[{"good_id":61064,"otherfee_content":null,"otherfee_currency":null,"otherfee_total":null,"reduction_content":null,"reduction_money":null,"prefe_gift":null,"prefe_reduction":null,"gift_money":null,"noinclude_content":null,"noinclude_money":null,"potential_content":null,"potential_money":null,"file":null,"services":"[{\"service_id\":2},{\"service_id\":12},{\"service_id\":13}]","sku_info":[{"sku_id":1727588,"goods_num":1,"price":"1.00","currency":"5","cart_id":180}]}],"warehouse_id":16,"delivery_method":2,"gather_method":1,"line_id":8,"address_id":24}
                            $api_data = json_encode([
                                'ordersn'=>$website_order['ordersn'],
                                'createtime'=>$time*1000,
                                'productList'=>json_encode($productList,true),
                                'address_id'=>$gather_method==2?$website_order['address_id']:0,
                            ],true);
                            $res = httpRequest2('https://shop.gogo198.cn/collect_website/public/?s=/api/getgoods/create_order',$api_data,['Content-Type: application/json;charset=utf-8']);
                            $res = json_decode($res,true);

                            if($res['code'] == 0 && $gather_method==1){
                                #平台集运才生成集运单

                                #1、仓库预定
                                $prediction_id = 2;
                                $start = strtotime(date('Y-01-01 00:00:00'));$end = strtotime(date('Y-12-31 23:59:59'));
                                $order_num = pdo_fetchcolumn("SELECT COUNT(*) FROM " . tablename('centralize_parcel_order') . " WHERE prediction_id = :prediction_id AND user_id = :user_id AND createtime BETWEEN :start AND :end", [
                                    ':prediction_id' => $prediction_id,
                                    ':user_id'       => $member['id'],
                                    ':start'         => $start,
                                    ':end'           => $end
                                ]);
                                $order_num = str_pad($order_num+1,3,'0',STR_PAD_LEFT);
                                $ordersn = substr($member['custom_id'],-5) . date('Y') . $order_num;

                                #多个包裹
                                $content = [
                                    'user_id'    => $member['id'],
                                    'agent_id'   => $member['agent_id'],
                                    'ordersn'    => 'G'.$ordersn,
                                    'warehouse_id'=> 32,#默认直邮仓库
                                    'prediction_id'=> $prediction_id,
                                    'task_id'    => 0,
                                    'sure_prediction'=>1,
                                    'createtime' => $time
                                ];
                                pdo_insert('centralize_parcel_order',$content);
                                $orderid = pdo_insertid();

                                //1.1、任务信息处理
                                #获取任务流水号
//                            $start_num = $this->get_today_task($time);
                                $today_task = pdo_fetch('select * from '.tablename('centralize_task').' where createtime=:times order by id desc',[':times'=>$time]);
                                $start_num = substr($today_task['serial_number'],-2);
                                if(empty($start_num)){
                                    $serial_number = 'MC'.date('ymdHis',$time).str_pad(1,2,'0',STR_PAD_LEFT);
                                }else{
                                    $serial_number = 'MC'.date('ymdHis',$time).str_pad(intval($start_num)+1,2,'0',STR_PAD_LEFT);
                                }
                                #获取任务名称
                                $task_name = $member['custom_id'].'发起任务[仓库预报]';
                                pdo_insert('centralize_task',[
                                    'user_id'=>$member['id'],
                                    'type'=>3,
                                    'task_name'=>$task_name,
                                    'task_id'=>19,
                                    'order_id'=>$orderid,
                                    'serial_number'=>$serial_number,
                                    'remark'=>'',
                                    'status'=>1,
                                    'createtime'=>$time
                                ]);

                                #2、包裹预报
                                $insert_data = [
                                    'user_id'=>$member['id'],
                                    'orderid'=>$orderid,
                                    'gogo_oid'=>$website_order['id'],
                                    'express_id'=>'',#todo 待接口返回快递企业信息
                                    'express_no'=>'',#todo 待接口返回快递编码信息
                                    #入仓信息
                                    'delivery_logistics'=>1,
                                    'delivery_method'=>1,
                                    #物品信息
                                    'inspection_method'=>1,
                                    #包装材质
                                    'package'=>'',
                                    'package_name'=>'',
                                    #包裹毛重
                                    'grosswt'=>'',
                                    #包裹体积
                                    'volumn'=>'1*1*1',
                                    #状态
                                    'status2'=>0,#直接转运或集货转运都要先签收入库
                                    #创建时间
                                    'createtime'=>$time
                                ];
                                pdo_insert('centralize_parcel_order_package',$insert_data);
                                $package_id = pdo_insertid();

                                #3、预报商品
                                $brand_name = '';
                                if(!empty($order['content']['goods_info'])){
                                    foreach($order['content']['goods_info'] as $k=>$v){
                                        $goods = $other_db->fetch('select * from goods where id=:gid',[':gid'=>$data['good_id']]);
                                        foreach($v['sku_info'] as $k2=>$v2){
                                            $sku_goods = $other_db->fetch('select * from goods_sku where sku_id=:sku_id',[':sku_id'=>$v2['sku_id']]);
                                            $sku_goods['sku_prices'] = json_decode($sku_goods['sku_prices'],true);

                                            array_push($productList,[
                                                'platform'=>$goods['other_platform'],
                                                'productCount'=>$v2['goods_num'],
                                                'productLink'=>$goods['other_goods_link'],
                                                'productName'=>$goods['goods_name'],
                                                'productPrice'=>$sku_goods['sku_prices']['price'][0],#订购产品单价
                                                'skuCode'=>$sku_goods['goods_sn'],
                                                'spuCode'=>$goods['other_spuCode'],
                                                'productImage'=>$goods['goods_image'],
                                                'orderRemark'=>'买家：'.$member['custom_id']
                                            ]);

                                            pdo_insert('centralize_parcel_order_goods',[
                                                'user_id'=>$member['id'],
                                                'orderid'=>$orderid,
                                                'package_id'=>$package_id,
                                                #物品属性
                                                'valueid'=>'',
                                                #物品描述
                                                'good_desc'=>$goods['goods_name'],
                                                #物品数量
                                                'good_num'=>$v2['goods_num'],
                                                #物品单位
                                                'good_unit'=>$sku_goods['sku_prices']['unit'][0],
                                                #物品币种
                                                'good_currency'=>$goods['currency'],
                                                #物品金额
                                                'good_price'=>$sku_goods['sku_prices']['price'][0],
                                                #物品金额（等值美元）
                                                'goods_usdprice'=>'',
                                                #物品包装
                                                'good_package'=>'',
                                                #物品品牌类型
                                                'brand_type'=>'',
                                                'brand_name'=>$brand_name,
                                                #物品备注
                                                'good_remark'=>'',
                                                #创建时间
                                                'createtime'=>$time
                                            ]);
                                        }
                                    }
                                }

                                #4、包裹订单
                                pdo_insert('centralize_order_fee_log',[
                                    'type'=>1,
                                    'ordersn'=>'G'.date('YmdHis'),
                                    'user_id'=>$member['id'],
                                    'orderid'=>$orderid,
                                    #包裹id
                                    'good_id'=>$package_id,
                                    'express_no'=>'',#todo 待接口返回快递编码信息
                                    'service_status'=>1,
                                    'order_status'=>0,
                                    'createtime'=>$time
                                ]);

                                #5、修改购购网订单表（已采购待发货）
                                pdo_update('website_order_list',['status'=>2],['id'=>$website_order['id']]);

                                #todo 1、需要写查询订单接口，获取物流企业&物流编码
                                #todo 2、通知仓库商

                                #6、通知用户
                                $post3 = json_encode([
                                    'call'=>'collectionNotice',
                                    'first'=>'您好，订单［'.$website_order['ordersn'].'］订单状态已变更为［已采购］，点击查看详情！',
                                    'keyword1'=>$website_order['ordersn'],
                                    'keyword2'=>'已采购',
                                    'keyword3'=>date('Y-m-d H:i:s',time()),
                                    'remark' =>'点击查看详情',
                                    'url'=>'https://www.gogo198.com',
                                    'openid' =>$member['openid'],
                                    'temp_id'=>'SVVs5OeD3FfsGwW0PEfYlZWetjScIT8kDxht5tlI1V8',

                                ]);
                                ihttp_request('https://shop.gogo198.cn/api/sendwechattemplatenotice.php', $post3);
                            }
                        }
                        elseif($buyer['type']==2){
                            #平台买手
                            if(!empty($buyer['openid'])){
                                #买手才有openid
                                $post3 = json_encode([
                                    'call'=>'collectionNotice',
                                    'first'=>'您好，有［'.$type.'］订单状态已变更为［订单已付］，点击查看详情！',
                                    'keyword1'=>$website_order['ordersn'],
                                    'keyword2'=>$type,
                                    'keyword3'=>'CNY '.$order['total_money'],
                                    'keyword4'=>$zf_type,
                                    'keyword5'=>date('Y-m-d H:i:s',time()),
                                    'remark' =>'点击登录商户端查看',
                                    'openid' =>$buyer['openid'],
                                    'uniacid'=>$order['uniacid'],
                                    'temp_id'=>'tHWxOL4Kc3v6uZinHT3Zo661I8o6EbAg46XKUP0FnnY',
                                    'url'=>'https://dtc.gogo198.net/?s=index/customer_login&is_merchs=1'
                                ]);
                                ihttp_request('https://shop.gogo198.cn/api/sendwechattemplatenotice.php', $post3);
                            }
                        }

                        #通知总后台管理员&客服
                        $servicers = pdo_fetchall('select * from '.tablename('centralize_system_servicer').' where status=1');
                        foreach($servicers as $k=>$v) {
                            $muser = pdo_fetch('select * from ' . tablename('website_user') . ' where id=:id', [':id' => $v['uid']]);
                            if (!empty($muser['openid'])) {
                                $post = json_encode([
                                    'call'=>'collectionNotice',
                                    'first'=>'您有一笔淘中国收款信息',
                                    'keyword1'=>$order['payer_name'],
                                    'keyword2'=>'CNY '.$order['total_money'],
                                    'keyword3'=>$zf_type,
                                    'keyword4'=>date('Y-m-d H:i:s',time()),
                                    'keyword5'=>$order['ordersn'],
                                    'remark' =>'感谢您的使用',
                                    'openid' =>$muser['openid'],
                                    'uniacid'=>$order['uniacid'],
                                    'temp_id'=>'WcvDClChgUbLfWHu5jQw5TEilYU36VdNDH514KZ-f4w',
                                    'url'=>'https://shop.gogo198.cn/app/index.php?i=3&c=entry&do=member&p=custompayment&m=sz_yi&oid='.$order['id'].'&isadmin=1'
                                ]);
                                ihttp_request('https://shop.gogo198.cn/api/sendwechattemplatenotice.php', $post);
                            }
                        }
                    }
                    if($website_order['company_id'] > 0){
                        #通知商家
                        $manage = pdo_fetch('select b.openid from '.tablename('website_user_company').' a left join '.tablename('website_user').' b on b.id=a.user_id where a.id=:id',[':id'=>$website_order['company_id']]);

                        if(!empty($manage['openid'])){
                            $post3 = json_encode([
                                'call'=>'collectionNotice',
                                'first'=>'您好，有［'.$type.'］订单状态已变更为［订单已付］，点击查看详情！',
                                'keyword1'=>$website_order['ordersn'],
                                'keyword2'=>$type,
                                'keyword3'=>'CNY '.$order['total_money'],
                                'keyword4'=>$zf_type,
                                'keyword5'=>date('Y-m-d H:i:s',time()),
                                'remark' =>'点击登录商户端查看',
                                'openid' =>$manage['openid'],
                                'uniacid'=>$order['uniacid'],
                                'temp_id'=>'tHWxOL4Kc3v6uZinHT3Zo661I8o6EbAg46XKUP0FnnY',
                                'url'=>'https://dtc.gogo198.net/?s=index/customer_login&is_merchs=1'
                            ]);
                            ihttp_request('https://shop.gogo198.cn/api/sendwechattemplatenotice.php', $post3);
                        }

                        #通知总后台管理员&客服
                        $servicers = pdo_fetchall('select * from '.tablename('centralize_system_servicer').' where status=1');
                        foreach($servicers as $k=>$v) {
                            $muser = pdo_fetch('select * from ' . tablename('website_user') . ' where id=:id', [':id' => $v['uid']]);
                            if (!empty($muser['openid'])) {
                                $post = json_encode([
                                    'call'=>'collectionNotice',
                                    'first'=>'您有一笔淘中国收款信息',
                                    'keyword1'=>$order['payer_name'],
                                    'keyword2'=>'CNY '.$order['total_money'],
                                    'keyword3'=>$zf_type,
                                    'keyword4'=>date('Y-m-d H:i:s',time()),
                                    'keyword5'=>$order['ordersn'],
                                    'remark' =>'感谢您的使用',
                                    'openid' =>$muser['openid'],
                                    'uniacid'=>$order['uniacid'],
                                    'temp_id'=>'WcvDClChgUbLfWHu5jQw5TEilYU36VdNDH514KZ-f4w',
                                    'url'=>'https://shop.gogo198.cn/app/index.php?i=3&c=entry&do=member&p=custompayment&m=sz_yi&oid='.$order['id'].'&isadmin=1'
                                ]);
                                ihttp_request('https://shop.gogo198.cn/api/sendwechattemplatenotice.php', $post);
                            }
                        }
                    }
                }
            }
            else {
                //普通收付款的通知

                //step1:发送消息给发起付款的人
                $post = json_encode([
                    'call' => 'collectionNotice',
                    'first' => '您有一笔收款信息',
                    'keyword1' => $order['payer_name'],
                    'keyword2' => 'CNY ' . $order['total_money'],
                    'keyword3' => $zf_type,
                    'keyword4' => date('Y-m-d H:i:s', time()),
                    'keyword5' => $order['ordersn'],
                    'remark' => '感谢您的使用',
                    'openid' => $order['send_openid'],
                    'uniacid' => $order['uniacid'],
                    'temp_id' => 'WcvDClChgUbLfWHu5jQw5TEilYU36VdNDH514KZ-f4w',
                    'url' => 'https://shop.gogo198.cn/app/index.php?i=3&c=entry&do=member&p=custompayment&m=sz_yi&oid=' . $order['id'] . '&isadmin=1'
                ]);
                ihttp_request('https://shop.gogo198.cn/api/sendwechattemplatenotice.php', $post);

                //step2:成功支付后，发送消息给本人
                $type = '';
                if ($order['trade_type'] == 1) {
                    $type = '商品';
                } elseif ($order['trade_type'] == 2) {
                    $type = '项目';
                } elseif ($order['trade_type'] == 3) {
                    $type = '多项服务';
                }
                if (is_numeric($order['openid'])) {
                    $order['openid'] = pdo_fetchcolumn('select openid from ' . tablename('sz_yi_member') . ' where mobile=:mob', [':mob' => $order['openid']]);
                }
                $post2 = json_encode([
                    'call' => 'collectionNotice',
                    'first' => '您好，您已经完成新订单的处理，点击查看详情，如有疑问，敬请联系客服075786329911，感谢您的支持！',
                    'keyword1' => $order['ordersn'],
                    'keyword2' => $type,
                    'keyword3' => 'CNY ' . $order['total_money'],
                    'keyword4' => $zf_type,
                    'keyword5' => date('Y-m-d H:i:s', time()),
                    'remark' => '感谢您的使用',
                    'openid' => $order['openid'],
                    'uniacid' => $order['uniacid'],
                    'temp_id' => 'tHWxOL4Kc3v6uZinHT3Zo661I8o6EbAg46XKUP0FnnY',
                    'url' => 'https://shop.gogo198.cn/app/index.php?i=3&c=entry&do=member&p=custompayment&m=sz_yi&oid=' . $order['id']
                ]);
                ihttp_request('https://shop.gogo198.cn/api/sendwechattemplatenotice.php', $post2);
                //step3:成功支付后，发送消息给管理员（老板）
                $post3 = json_encode([
                    'call' => 'collectionNotice',
                    'first' => '您好，有［' . $type . '］订单状态已变更为［订单已付］，点击查看详情！',
                    'keyword1' => $order['ordersn'],
                    'keyword2' => $type,
                    'keyword3' => 'CNY ' . $order['total_money'],
                    'keyword4' => $zf_type,
                    'keyword5' => date('Y-m-d H:i:s', time()),
                    'remark' => '',
                    'openid' => 'ov3-bt8keSKg_8z9Wwi-zG1hRhwg',
                    'uniacid' => $order['uniacid'],
                    'temp_id' => 'tHWxOL4Kc3v6uZinHT3Zo661I8o6EbAg46XKUP0FnnY',
                    'url' => 'https://shop.gogo198.cn/app/index.php?i=3&c=entry&do=member&p=custompayment&m=sz_yi&oid=' . $order['id'] . '&isadmin=1'
                ]);
                ihttp_request('https://shop.gogo198.cn/api/sendwechattemplatenotice.php', $post3);
            }
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
$_W['uniacid'] = $_W['weid'] = $get['uniacid'];

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
if (is_array($set['payment'])) {
	
	$wechat = $set['payment']['tgpay'];

	if (!empty($wechat)) {
		
		m('common')->paylog('setting: ok');
		
		if (($data['state'] == '0') && ($data['orderDesc'] == '支付成功')) {	

			m('common')->paylog('sign: ok');

			if (empty($type)) {

				$tid = $get['lowOrderId'];

				// if (strexists($tid, 'GJ')) {
				// 	$tids = explode('GJ', $tid);
				// 	$tid = $tids[0];
				// }

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
				// 			$ret['weid'] = $log['weid'];
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

								// pdo_update('sz_yi_order',$record, array('ordersn_general' => $tid, 'uniacid' => $log['uniacid']));
								exit();
							}
						}
					}
				}
			} else if ($type == 1) {
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