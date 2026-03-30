<?php
// 模块LTD提供
if (!defined('IN_IA')) {
    exit('Access Denied');
}

global $_W;
global $_GPC;

$op = !empty($_GPC['op'])?trim($_GPC['op']):'display';

if($op=='sureOrderList') {
    //显示下单详情
    $openid = $_W['openid'];
    $id = intval($_GPC['id']);
    $order = pdo_fetch('select * from '.tablename('customs_warehouse_order').' where id=:id',[':id'=>$id]);
    $order['content'] = json_decode($order['content'],true);
    $time = time();

    if(!isset($_GPC['sub'])){
        #扫码进来
        if($order['status']==3){
            echo '<h1>该仓储订单已冻结！</h1>';exit;
        }

        if(empty($openid)){
            header('Location:https://mp.weixin.qq.com/mp/profile_ext?action=home&__biz=MzA4MDQyMTMxMQ==&scene=110#wechat_redirect');
        }
        $fans_info = pdo_fetch('select follow from '.tablename('mc_mapping_fans').' where openid=:openid and uniacid=3',[':openid'=>$openid]);
        if(empty($fans_info['follow'])){
            header('Location:https://mp.weixin.qq.com/mp/profile_ext?action=home&__biz=MzA4MDQyMTMxMQ==&scene=110#wechat_redirect');
        }

        $manage_openid1 = 'ov3-bt8keSKg_8z9Wwi-zG1hRhwg';
        $manage_openid2 = 'ov3-bt5vIxepEjWc51zRQNQbFSaQ';
        #修改下单的openid
         if($openid!=$manage_openid1){
            pdo_update('customs_warehouse_order',['openid'=>$openid],['id'=>$id]);
         }

        $price = 0;
        $total_price = 0;
        foreach($order['content']['event_currency'] as $k=>$v){
            $order['content']['event_currency'][$k] = pdo_fetchcolumn('select code_name from '.tablename('currency').' where code_value=:code_value',[':code_value'=>$v]);
            $order['content']['event_currency'][$k] = explode('(',explode(')',$order['content']['event_currency'][$k])[0])[1];
//            $price += $order['content']['event_totalprice'][$k];
//            $total_price += $order['content']['event_totalprice'][$k];
        }
//        $total_price += $order['invoicing_tax'];

    }
    else{
        if($_GPC['sub']==1){
            #订单确认

            #1、判断该用户有无进行实名认证
            $enterprise_members = pdo_fetch('select is_verify,id,realname,mobile from '.tablename('enterprise_members').' where openid=:openid',[':openid'=>$openid]);
            $enterprise_id = 0;
            if(empty($enterprise_members['is_verify'])){
                $time = time();
                $name = trim($_GPC['name']);
                $mobile = trim($_GPC['mobile']);
                if($name == ''){$name = $enterprise_members['realname'];}
                if($mobile == ''){$mobile = $enterprise_members['mobile'];}
                $manage_person_id = 0;
                $is_have_manageperson = pdo_fetch('select id from '.tablename('centralize_manage_person').' where enterprise_id=:enterprise_id',[':enterprise_id'=>$enterprise_members['id']]);
                if(empty($enterprise_members['id'])){
                    pdo_insert('centralize_manage_person',[
                        'name'=>$name,
                        'type'=>1,
                        'tel'=>$mobile,
                        'email'=>'',
                        'status'=>0,
                        'createtime'=>$time
                    ]);
                    $manage_person = pdo_insertid();
                    if($manage_person){
                        pdo_insert('enterprise_members',[
                            'uniacid'=>3,
                            'openid'=>$_W['openid'],
                            'nickname'=>$name,
                            'realname'=>$name,
                            'mobile'=>$mobile,
                            'reg_type'=>1,
                            'create_at'=>$time,
                            'centralizer_id'=>$manage_person,
                            'entrance'=>1
                        ]);
                        $enterprise_id = pdo_insertid();
                    }

                    pdo_update('centralize_manage_person',['enterprise_id'=>$enterprise_id],['id'=>$manage_person]);
                    $manage_person_id = $manage_person;
                }else{
                    $manage_person_id = $is_have_manageperson['id'];
                    $enterprise_id = $enterprise_members['id'];
                }

                $link = 'https://decl.gogo198.cn/centralize/index/register_merch?pre_id='.base64_encode($manage_person_id);
                echo json_encode(['code'=>-1,'msg'=>'系统检测你还未实名认证，正在跳转进行实名认证！','link'=>$link]);die;
            }else{
                $enterprise_id = $enterprise_members['id'];
            }

            #2、有认证后确认订单提交
            pdo_update('customs_warehouse_order',[
                'status'=>2,
                'enterprise_id'=>$enterprise_id,
                'name'=>$enterprise_members['realname'],
                'mobile'=>$enterprise_members['mobile'],
            ],['id'=>$id]);

            #3、生成跨境结算订单
            $ordersn = 'GP' . date('YmdH', $time) . str_pad(mt_rand(1, 999999), 6, '0',
                    STR_PAD_LEFT) . substr(microtime(), 2, 6);
            $overdue = ( $order['pay_term'] * 86400 ) + $time;
            $totalprice = 0;
            $service_info = [];
            foreach($order['content']['event_date'] as $k=>$v){
                $totalprice += $order['content']['event_totalprice'][$k];
                $uni_name = pdo_fetch('select code_name from '.tablename('unit').' where code_value=:code',[':code'=>$order['content']['event_unit'][$k]]);
                $service_info[] = $order['content']['event_name'][$k].','.$order['content']['event_num'][$k].$uni_name['code_name'].','.$order['content']['event_totalprice'][$k];
            }
            $totalprice += $order['invoicing_tax'];
            $service_info = json_encode($service_info,true);
            pdo_insert('customs_collection',[
                'uniacid'=>$_W['uniacid'],
                'openid'=>$_W['openid'],
                'send_openid'=>'ov3-bt8keSKg_8z9Wwi-zG1hRhwg',
                'ordersn'=>$ordersn,
                'trade_price'=>$totalprice,
                'trade_type'=>3,
                'payer_name'=>$enterprise_members['realname'],
                'payer_tel'=>$enterprise_members['mobile'],
                'pay_term'=>$order['pay_term'],//付款期限（天）
                'pay_fee'=>$order['pay_fee'],//逾期费用
                'overdue'=>$overdue,
                'trans_form'=>1,
                'createtime'=>$time,
                'basic'=>3,
                'description'=>'仓储订单，含开票税费（CNY '.$order['invoicing_tax'].'）',
                'service_info'=>$service_info
            ]);
            $insertid = pdo_insertid();
            $msg_template['first'] = '你好！您有一笔来自［区广祺］的［立即收款］付款请求，请点击消息支付，为确保您的资金安全，如对此支付请求有疑问，请暂缓支付并致电Gogo客服电话07578632991咨询或反馈，感谢您使用Gogo服务。';
            $description = '备注信息：仓储订单,含开票税费（CNY '.$order['invoicing_tax'].'）';

            $link = 'https://shop.gogo198.cn/app/index.php?i=3&c=entry&do=member&p=custompayment&m=sz_yi&oid='.$insertid;
            $post = json_encode([
                'call'=>'collectionNotice',
                'first' =>$msg_template['first'],
                'keyword1' => $ordersn,
                'keyword2' => date('Y-m-d H:i:s',$time),
                'keyword3' => 'CNY '.sprintf('%.2f', $totalprice),
                'keyword4' => $description,
                'keyword5'=> date('Y-m-d H:i:s',$overdue),
                'remark' => 'Gogo在线收款服务为商户提供合规安全的即时、预约及定期收款通知与在线支付服务，如需了解，可回复”8“了解及与客服联系。',
                'url' => $link,
                'openid' => $_W['openid'],
                'temp_id' => 'YU8Nczq9tyT8CNUyu9Lnyi0VcASZ4VBkEzTnB2adal4'
            ]);
            ihttp_request('https://shop.gogo198.cn/api/sendwechattemplatenotice.php', $post);
            $res = pdo_update('customs_collection',['is_send'=>1],['id'=>$insertid]);

            if($res){
                echo json_encode(['code'=>0,'msg'=>'订单确认成功，正在跳转支付页面~','link'=>$link]);exit;
            }
        }
    }
    include $this->template('warehouse/warehouse_order/sure_order_list');
}
elseif($op=='order_detail'){
    $key = intval($_GPC['key']);
    $orderid = intval($_GPC['id']);

    $order = pdo_fetch('select * from '.tablename('customs_warehouse_order').' where id=:id',[':id'=>$orderid]);
    $order['content'] = json_decode($order['content'],true);

    $unit = pdo_fetchall('select * from '.tablename('unit').' where 1');
    $currency = pdo_fetchall('select * from '.tablename('currency').' where 1');

    include $this->template('warehouse/warehouse_order/order_detail');
}
elseif($op=='money_detail'){
    $id = intval($_GPC['id']);
    $detail = pdo_fetch('select * from '.tablename('customs_warehouse_order').' where id=:id',[':id'=>$id]);
    $detail['currency'] = pdo_fetchcolumn('select code_name from '.tablename('currency').' where code_value=:code_value',[':code_value'=>$detail['currency']]);
    include $this->template('warehouse/warehouse_order/money_detail');
}
elseif($op=='edit_order'){
    $key = intval($_GPC['key']);
    $orderid = intval($_GPC['orderid']);

    $order = pdo_fetch('select * from '.tablename('customs_warehouse_order').' where id=:id',[':id'=>$orderid]);
    $order['content'] = json_decode($order['content'],true);

    #修改仓储订单内容
    $order['content']['event_date'][$key] = trim($_GPC['event_date']);
    $order['content']['event_type'][$key] = trim($_GPC['event_type']);
    $order['content']['event_name'][$key] = trim($_GPC['event_name']);
    $order['content']['event_unit'][$key] = trim($_GPC['event_unit']);
    $order['content']['event_currency'][$key] = trim($_GPC['event_currency']);
    $order['content']['event_price'][$key] = trim($_GPC['event_price']);
    $order['content']['event_num'][$key] = trim($_GPC['event_num']);
    $order['content']['event_totalprice'][$key] = trim($_GPC['event_totalprice']);
    $order['content']['event_remark'][$key] = trim($_GPC['event_remark']);

    $content = json_encode([
        'event_date'=>$order['content']['event_date'],
        'event_type'=>$order['content']['event_type'],
        'event_name'=>$order['content']['event_name'],
        'event_unit'=>$order['content']['event_unit'],
        'event_currency'=>$order['content']['event_currency'],
        'event_price'=>$order['content']['event_price'],
        'event_num'=>$order['content']['event_num'],
        'event_totalprice'=>$order['content']['event_totalprice'],
        'event_remark'=>$order['content']['event_remark'],
    ],true);

    #修改开票税费
    $price = 0;
    foreach($order['content']['event_totalprice'] as $k=>$v){
        $price+=$v;
    }
    if(!empty($order['exchange_rate'])){
        $price = floatval($price) * floatval($order['exchange_rate']);
        $price = sprintf('%.2f',$price);
    }
    $invoicing_tax = sprintf('%.2f',$price*$order['tax_num']);
    $real_price = floatval($price) + floatval($invoicing_tax);
    $res = pdo_update('customs_warehouse_order',['content'=>$content,'price'=>$price,'real_price'=>$real_price,'invoicing_tax'=>$invoicing_tax],['id'=>$orderid]);
    if($res){
        echo json_encode(['code'=>0,'msg'=>'修改成功！']);exit;
    }else{
        echo json_encode(['code'=>-1,'msg'=>'暂无修改！']);exit;
    }
}
elseif($op=='del_order'){
    $key = intval($_GPC['key']);
    $orderid = intval($_GPC['orderid']);

    $order = pdo_fetch('select * from '.tablename('customs_warehouse_order').' where id=:id',[':id'=>$orderid]);
    $order['content'] = json_decode($order['content'],true);

    #修改仓储订单内容
    unset($order['content']['event_date'][$key]);
    unset($order['content']['event_type'][$key]);
    unset($order['content']['event_name'][$key]);
    unset($order['content']['event_unit'][$key]);
    unset($order['content']['event_currency'][$key]);
    unset($order['content']['event_price'][$key]);
    unset($order['content']['event_num'][$key]);
    unset($order['content']['event_totalprice'][$key]);
    unset($order['content']['event_remark'][$key]);
    $order['content']['event_date'] =array_values($order['content']['event_date']);
    $order['content']['event_type'] =array_values($order['content']['event_type']);
    $order['content']['event_name'] =array_values($order['content']['event_name']);
    $order['content']['event_unit'] =array_values($order['content']['event_unit']);
    $order['content']['event_currency'] =array_values($order['content']['event_currency']);
    $order['content']['event_price'] =array_values($order['content']['event_price']);
    $order['content']['event_num'] =array_values($order['content']['event_num']);
    $order['content']['event_totalprice'] =array_values($order['content']['event_totalprice']);
    $order['content']['event_remark'] =array_values($order['content']['event_remark']);

    $content = json_encode([
        'event_date'=>$order['content']['event_date'],
        'event_type'=>$order['content']['event_type'],
        'event_name'=>$order['content']['event_name'],
        'event_unit'=>$order['content']['event_unit'],
        'event_currency'=>$order['content']['event_currency'],
        'event_price'=>$order['content']['event_price'],
        'event_num'=>$order['content']['event_num'],
        'event_totalprice'=>$order['content']['event_totalprice'],
        'event_remark'=>$order['content']['event_remark'],
    ],true);

    #修改开票税费
    $price = 0;
    foreach($order['content']['event_totalprice'] as $k=>$v){
        $price+=$v;
    }
    if(!empty($order['exchange_rate'])){
        $price = floatval($price) * floatval($order['exchange_rate']);
        $price = sprintf('%.2f',$price);
    }
    $invoicing_tax = sprintf('%.2f',$price*$order['tax_num']);
    $real_price = floatval($price) + floatval($invoicing_tax);
    $res = pdo_update('customs_warehouse_order',['content'=>$content,'price'=>$price,'real_price'=>$real_price,'invoicing_tax'=>$invoicing_tax],['id'=>$orderid]);
    if($res){
        echo json_encode(['code'=>0,'msg'=>'删除成功！']);exit;
    }
}
elseif($op=='chat_page'){
    $openid = $_W['openid'];
    $id = intval($_GPC['id']);
    $order = pdo_fetch('select * from '.tablename('customs_warehouse_order').' where id=:id',[':id'=>$id]);
    $manage_openid1 = 'ov3-bt8keSKg_8z9Wwi-zG1hRhwg';

    #查找聊天记录
    $list = pdo_fetchall('select * from '.tablename('customs_warehouse_chat').' where orderid=:orderid and pid=0 order by id desc',[':orderid'=>$id]);
    foreach($list as $k=>$v){
        $list[$k]['createtime'] = date('Y-m-d H:i',$v['createtime']);
        $list[$k]['content'] = json_decode($v['content'],true);
        if($v['who_send']==1){
            $list[$k]['identify_role'] = '用户';
        }elseif($v['who_send']==2){
            $list[$k]['identify_role'] = '管理员';
        }
        $list[$k]['child_content'] = pdo_fetchall('select * from '.tablename('customs_warehouse_chat').' where orderid=:orderid and pid=:pid order by id desc',[':orderid'=>$id,':pid'=>$v['id']]);
        if(!empty($list[$k]['child_content'])){
            foreach($list[$k]['child_content'] as $k2=>$v2){
                $list[$k]['child_content'][$k2]['createtime'] = date('Y-m-d H:i',$v2['createtime']);
                $list[$k]['child_content'][$k2]['content'] = json_decode($v2['content'],true);
                if($v2['who_send']==1){
                    $list[$k]['child_content'][$k2]['identify_role'] = '用户';
                }elseif($v2['who_send']==2){
                    $list[$k]['child_content'][$k2]['identify_role'] = '管理员';
                }
            }    
        }
    }
    
    #身份
    $identify = 0;
    if($order['openid']!=$manage_openid1){
        #用户
        $identify=1;
    }
    else{
        #管理员
        $identify=2;
    }

    include $this->template('warehouse/warehouse_order/chat_page');
}
elseif($op=='warehouse_chat'){
    $manage_openid1 = 'ov3-bt8keSKg_8z9Wwi-zG1hRhwg';
    $manage_openid2 = 'ov3-bt5vIxepEjWc51zRQNQbFSaQ';
    $time = time();
    $id = intval($_GPC['id']);
    $openid = $_W['openid'];
    $send_openid = '';
    $who_send = 0;
    if($openid!=$manage_openid1){
        #用户发送
        $send_openid = $manage_openid1;
        $who_send=1;
    }else{
        #管理员发送
        $send_openid = pdo_fetchcolumn('select openid from '.tablename('customs_warehouse_order').' where id=:id',[':id'=>$id]);
        $who_send=2;
    }
    #保存数据
    pdo_insert('customs_warehouse_chat',[
        'orderid'=>$id,
        'openid'=>$openid,
        'content'=>json_encode($_GPC['content'],true),
        'content_type'=>intval($_GPC['content_type']),
        'who_send'=>$who_send,
        'pid'=>intval($_GPC['pid']),
        'createtime'=>$time
    ]);
    
    #发送
    $post = json_encode([
        'call'=>'confirmCollectionNotice',
        'first' =>'您好！您收到一则消息,请打开查阅！',
        'keyword1' => '留言回复',
        'keyword2' => '已收到',
        'keyword3' => date('Y-m-d H:i:s',$time),
        'remark' => '点击查看详情',
        'url' => 'https://shop.gogo198.cn/app/index.php?i=3&c=entry&do=warehouse&p=warehouse&op=sureOrderList&m=sz_yi&id='.$id.'&wxref=mp.weixin.qq.com',
        'openid' => $send_openid,
        'temp_id' => 'SVVs5OeD3FfsGwW0PEfYlZWetjScIT8kDxht5tlI1V8'
    ]);
    ihttp_request('https://shop.gogo198.cn/api/sendwechattemplatenotice.php', $post);

    echo json_encode(['code'=>0,'msg'=>'消息发送成功！']);exit;
}
elseif($op=='chat_page2'){
    $openid = $_W['openid'];
    $id = intval($_GPC['id']);
    $order = pdo_fetch('select * from '.tablename('customs_warehouse_order').' where id=:id',[':id'=>$id]);
    $manage_openid1 = 'ov3-bt8keSKg_8z9Wwi-zG1hRhwg';

    #查找聊天记录
    $list = pdo_fetchall('select createtime from '.tablename('customs_warehouse_chat').' where orderid=:orderid order by id desc',[':orderid'=>$id]);
    $identify = 0;
    if($order['openid']!=$manage_openid1){
        #用户
        $identify=1;
        pdo_update('customs_warehouse_chat',['is_read'=>1],['orderid'=>$id,'who_send'=>2]);
    }
    else{
        #管理员
        $identify=2;
        pdo_update('customs_warehouse_chat',['is_read'=>1],['orderid'=>$id,'who_send'=>1]);
    }

    #聊天记录以时间为数组
    $group = [];
    foreach($list as $k=>$v){
        $time = date('Y-m-d',$v['createtime']);
        if(empty($group)){
            $group = array_merge($group,[$time]);
        }else{
            if(!in_array($time,$group)){
                $group = array_merge($group,[$time]);
            }
        }
    }
    sort($group);
    #根据时间查找聊天记录
    $chat_group = [];

    foreach($group as $k=>$v){
        $starttime = strtotime($v.' 00:00:00');
        $endtime = strtotime($v.' 23:59:59');
        $chat_group[$k]['time'] = date('Y年m月d日',$starttime);
        $chat_group[$k]['info'] = pdo_fetchall('select * from '.tablename('customs_warehouse_chat').' where orderid=:orderid and ( createtime>=:starttime and createtime<=:endtime) ',[':orderid'=>$id,':endtime'=>$endtime,':starttime'=>$starttime]);
    }

    #整理数组
    foreach($chat_group as $k=>$v){
        foreach($v['info'] as $kk=>$vv){
            $chat_group[$k]['info'][$kk]['content'] = json_decode($vv['content'],true);
            $chat_group[$k]['info'][$kk]['createtime'] = date('H:i',$vv['createtime']);
        }
    }

    include $this->template('warehouse/warehouse_order/chat_page');
}
elseif($op=='warehouse_chat2'){
    $manage_openid1 = 'ov3-bt8keSKg_8z9Wwi-zG1hRhwg';
    $manage_openid2 = 'ov3-bt5vIxepEjWc51zRQNQbFSaQ';
    $time = time();
    $id = intval($_GPC['id']);
    $openid = $_W['openid'];
    $send_openid = '';
    $who_send = 0;
    if($openid!=$manage_openid1){
        #用户发送
        $send_openid = $manage_openid1;
        $who_send=1;
    }else{
        #管理员发送
        $send_openid = pdo_fetchcolumn('select openid from '.tablename('customs_warehouse_order').' where id=:id',[':id'=>$id]);
        $who_send=2;
    }

    #保存数据
    pdo_insert('customs_warehouse_chat',[
        'orderid'=>$id,
        'openid'=>$openid,
        'content'=>json_encode($_GPC['content'],true),
        'content_type'=>intval($_GPC['content_type']),
        'is_send'=>1,
        'who_send'=>$who_send,
        'createtime'=>$time
    ]);

    #发送
    $post = json_encode([
        'call'=>'confirmCollectionNotice',
        'first' =>'您好！您收到一则消息,请打开查阅！',
        'keyword1' => '消息回复',
        'keyword2' => '已收到',
        'keyword3' => date('Y-m-d H:i:s',$time),
        'remark' => '点击查看详情',
        'url' => 'https://shop.gogo198.cn/app/index.php?i=3&c=entry&do=warehouse&p=warehouse&op=sureOrderList&m=sz_yi&id='.$id.'&wxref=mp.weixin.qq.com',
        'openid' => $send_openid,
        'temp_id' => 'SVVs5OeD3FfsGwW0PEfYlZWetjScIT8kDxht5tlI1V8'
    ]);
    ihttp_request('https://shop.gogo198.cn/api/sendwechattemplatenotice.php', $post);

    echo json_encode(['code'=>0,'msg'=>'消息发送成功！']);exit;
}