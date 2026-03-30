<?php
// 模块LTD提供
if (!defined('IN_IA')) {
    exit('Access Denied');
}

global $_W;
global $_GPC;

$op = !empty($_GPC['op'])?trim($_GPC['op']):'display';

if($op=='display'){
    //显示下单详情
    $id = intval($_GPC['id']);
    $decl_user = intval($_GPC['decl_user']);
    if(empty($id)){
        echo '<h3>参数错误</h3>';
    }

    $order = pdo_fetch('select a.*,b.openid,b.user_name,b.freight_info from '.tablename('customs_freight_place_order').' a left join '.tablename('decl_user').' b on b.id=a.uid where a.id=:id',[':id'=>$id]);
    $order['fPortName'] = pdo_fetchcolumn('select name from '.tablename('customs_freight_port_name').' where code=:code',[':code'=>$order['fPort']]);
    $order['sPortName'] = pdo_fetchcolumn('select name from '.tablename('customs_freight_port_name').' where code=:code',[':code'=>$order['sPort']]);
    $order['address'] = json_decode($order['address'],true);
    foreach($order['address'] as $k=>$v){
        $order['address'][$k] = explode('||',$v)[0];
    }

    //商户拖车费用
    $order['freight_info'] = json_decode($order['freight_info'],true);
    $order['price_order_info'] = explode(',',$order['price_order_info']);
    $origin_price = floatval($order['price_order_info'][1]) - floatval($order['freight_info']['freight_price']);
    $totalMoney = $order['price_order_info'][1]+$order['price_order_info'][2]+$order['price_order_info'][3]+$order['price_order_info'][4];
    $order['date_range'] = json_decode($order['date_range'],true);
    $order['container_info'] = json_decode($order['container_info'],true);
//     print_r($order['container_info']);die;
    include $this->template('freight/freight_info');
}
elseif($op=='refuse'){
    $updata_arr = [
        'remark'=>trim($_GPC['remark']),
        'status'=>2
    ];
    $res = pdo_update('customs_freight_place_order',$updata_arr,['id'=>intval($_GPC['id'])]);
    if($res){
        $decl_user = pdo_fetch('select b.openid from '.tablename('customs_freight_place_order').' a left join '.tablename('decl_user').' b on b.id=a.uid where a.id=:id',[':id'=>intval($_GPC['id'])]);
        //通知商户
        $post = json_encode([
            'call'=>'confirmCollectionNotice',
            'first' =>'您好！好抱歉通知您，由于订单详情已变更，有关服务未获服务提供商确认，点击进入可查看详情并可再次询价与下单，为您带来不便，敬请谅解，如有任何疑问，可致电075786329911与我司联系，感谢您的支持！',
            'keyword1' => '拖车服务审核',
            'keyword2' => '确认失败',
            'keyword3' => date('Y-m-d H:i:s',time()),
            'remark' => '点击查看详情',
            'url' => 'https://shop.gogo198.cn/app/index.php?i=3&c=entry&do=freight&m=sz_yi&p=freight&id='.intval($_GPC['id']).'&decl_user=1',
            'openid' => $decl_user['openid'],
            'temp_id' => 'SVVs5OeD3FfsGwW0PEfYlZWetjScIT8kDxht5tlI1V8'
        ]);
        ihttp_request('https://shop.gogo198.cn/api/sendwechattemplatenotice.php', $post);

        show_json(1,['msg'=>'拒绝成功，已通知商户！']);
    }
}
elseif($op=='tmp_search'){
    $id = intval($_GPC['id']);
    $order = pdo_fetch('select * from '.tablename('customs_freight_tmp_search').' where id=:id',[':id'=>$id]);
    if(empty($order)){
        echo '<h3>查询已被删除！</h3>';exit;
    }
    $order['createtime'] = date('Y-m-d H:i',$order['createtime']);
    $order['info'] = json_decode($order['info'],true);

    include $this->template('freight/freight_tmp_info');
}
elseif($op=='sureOrderList'){
    $openid = $_W['openid'];
    $id = intval($_GPC['id']);
    $order = pdo_fetch('select * from '.tablename('customs_freight_order').' where id=:id',[':id'=>$id]);
    $time = time();

    if(!isset($_GPC['sub'])){
        #扫码进来
        if($order['status']==3){
            echo '<h1>该拖车订单已冻结！</h1>';exit;
        }
        $order['making_date'] = date('Y-m-d H:i',$order['making_date']);
        $order['end_date'] = date('Y-m-d H:i',$order['end_date']);
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
            pdo_update('customs_freight_order',['openid'=>$openid],['id'=>$id]);
        }

        $order['fPort'] = pdo_fetchcolumn('select name from '.tablename('customs_freight_port_name').' where code=:code',[':code'=>$order['fPort']]);
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
                $is_have_manageperson = pdo_fetch('select id from '.tablename('centralize_manage_person').' where tel=:tel',[':tel'=>$mobile]);
                if(empty($is_have_manageperson['id'])){
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
            pdo_update('customs_freight_order',[
                'status'=>2,
                'enterprise_id'=>$enterprise_id,
                'name'=>$enterprise_members['realname'],
                'mobile'=>$enterprise_members['mobile'],
            ],['id'=>$id]);

            #3、生成跨境结算订单
            $ordersn = 'GP' . date('YmdH', $time) . str_pad(mt_rand(1, 999999), 6, '0',
                    STR_PAD_LEFT) . substr(microtime(), 2, 6);
            $overdue = ( $order['pay_term'] * 86400 ) + $time;
            $port_name = pdo_fetch('select `name` from '.tablename('customs_freight_port_name').' where code=:code',[':code'=>$order['fPort']]);
            $service_info[] = '拖车订单,'.$port_name['name'].','.$order['price'];
            $service_info = json_encode($service_info,true);
            pdo_insert('customs_collection',[
                'uniacid'=>$_W['uniacid'],
                'openid'=>$_W['openid'],
                'send_openid'=>'ov3-bt8keSKg_8z9Wwi-zG1hRhwg',
                'ordersn'=>$ordersn,
                'trade_price'=>$order['price'],
                'trade_type'=>3,
                'payer_name'=>$order['name'],
                'payer_tel'=>$order['mobile'],
                'pay_term'=>$order['pay_term'],//付款期限（天）
                'pay_fee'=>$order['pay_fee'],//逾期费用
                'overdue'=>$overdue,
                'trans_form'=>1,
                'createtime'=>$time,
                'basic'=>3,
                'description'=>'拖车订单，含开票税费（CNY '.$order['invoicing_tax'].'）',
                'service_info'=>$service_info
            ]);
            $insertid = pdo_insertid();
            $msg_template['first'] = '你好！您有一笔来自［区广祺］的［立即收款］付款请求，请点击消息支付，为确保您的资金安全，如对此支付请求有疑问，请暂缓支付并致电Gogo客服电话07578632991咨询或反馈，感谢您使用Gogo服务。';
            $description = '备注信息：拖车订单，含开票税费（CNY '.$order['invoicing_tax'].'）';

            $link = 'https://shop.gogo198.cn/app/index.php?i=3&c=entry&do=member&p=custompayment&m=sz_yi&oid='.$insertid;
            $post = json_encode([
                'call'=>'collectionNotice',
                'first' =>$msg_template['first'],
                'keyword1' => $ordersn,
                'keyword2' => date('Y-m-d H:i:s',$time),
                'keyword3' => 'CNY '.$order['price'],
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
        }elseif($_GPC['sub']==2){
            #冻结
            $res = pdo_update('customs_freight_order',['status'=>3],['id'=>$id]);
            if($res){
                echo json_encode(['code'=>0,'msg'=>'冻结成功，正在刷新页面~']);die;
            }
        }
    }

    include $this->template('freight/sure_order_list');
}
elseif($op=='order_detail'){
    $openid = $_W['openid'];
    $id = intval($_GPC['id']);
    $order = pdo_fetch('select * from '.tablename('customs_freight_order').' where id=:id',[':id'=>$id]);
    $order['making_date'] = date('Y-m-d H:i',$order['making_date']);
    $order['end_date'] = date('Y-m-d H:i',$order['end_date']);
    if(isset($_GPC['update'])){
        $res = pdo_update('customs_freight_order',[
            'name'=>$name,
            'mobile'=>$mobile,

            'fPort'=>trim($_GPC['fPort']),
            'sPort'=>trim($_GPC['sPort']),
            'lading_no'=>trim($_GPC['lading_no']),
            'ship_name'=>trim($_GPC['ship_name']),
            'voyage'=>trim($_GPC['voyage']),
            'destination_port'=>trim($_GPC['destination_port']),

            'factory_address'=>trim($_GPC['factory_address']),
            'factory_contacter'=>trim($_GPC['factory_contacter']),
            'factory_mobile'=>trim($_GPC['factory_mobile']),
            'is_penalty'=>intval($_GPC['is_penalty']),
            'approach_idea'=>intval($_GPC['is_penalty'])==2?trim($_GPC['approach_idea']):'',
            'is_baoshui'=>intval($_GPC['is_baoshui']),
            'is_beian'=>intval($_GPC['is_baoshui'])==2?intval($_GPC['is_beian']):'',
            'data_service'=>intval($_GPC['data_service']),
            'data_service3'=>intval($_GPC['data_service'])==3?intval($_GPC['data_service3']):'',

            'making_date'=>strtotime($_GPC['making_date']),
            'estimate_weight'=>trim($_GPC['estimate_weight']),
            'box_type'=>trim($_GPC['box_type']),
            'box_num'=>trim($_GPC['box_num']),
            'making_requrest'=>trim($_GPC['making_requrest']),

            'is_wait'=>intval($_GPC['is_wait']),
            'is_wait2'=>intval($_GPC['is_wait'])==2?intval($_GPC['is_wait2']):'',
            'end_date'=>strtotime($_GPC['end_date']),
            'is_entrust'=>intval($_GPC['is_entrust']),
        ],['id'=>$id]);
        if($res){
            echo json_encode(['code'=>0,'msg'=>'修改成功']);exit;
        }else{
            echo json_encode(['code'=>-1,'msg'=>'暂无修改']);exit;
        }
    }
    #港口
    $fPort = pdo_fetchall('select * from '.tablename('customs_freight_port_name').' where pid=0');
    #具体港口名称
    $order_fPort = pdo_fetch('select * from '.tablename('customs_freight_port_name').' where code=:code',[':code'=>$order['fPort']]);
    $sPort = pdo_fetchall('select * from '.tablename('customs_freight_port_name').' where pid=:pid',['pid'=>$order_fPort['id']]);
    include $this->template('freight/order_detail');
}
elseif($op=='get_sport'){
    #获取子港口名称
    $fPort = pdo_fetch('select * from '.tablename('customs_freight_port_name').' where code=:code',[':code'=>trim($_GPC['code'])]);
    $sPort = pdo_fetchall('select * from '.tablename('customs_freight_port_name').' where pid=:pid',['pid'=>$fPort['id']]);

    echo json_encode(['code'=>0,'data'=>$sPort]);exit;
}
elseif($op=='chat_page'){
    $openid = $_W['openid'];
    $id = intval($_GPC['id']);
    $order = pdo_fetch('select * from '.tablename('customs_freight_order').' where id=:id',[':id'=>$id]);
    $manage_openid1 = 'ov3-bt8keSKg_8z9Wwi-zG1hRhwg';

    #查找聊天记录
    $list = pdo_fetchall('select * from '.tablename('customs_freight_chat').' where orderid=:orderid and pid=0 order by id desc',[':orderid'=>$id]);
    foreach($list as $k=>$v){
        $list[$k]['createtime'] = date('Y-m-d H:i',$v['createtime']);
        $list[$k]['content'] = json_decode($v['content'],true);
        if($v['who_send']==1){
            $list[$k]['identify_role'] = '用户';
        }elseif($v['who_send']==2){
            $list[$k]['identify_role'] = '管理员';
        }
        $list[$k]['child_content'] = pdo_fetchall('select * from '.tablename('customs_freight_chat').' where orderid=:orderid and pid=:pid order by id desc',[':orderid'=>$id,':pid'=>$v['id']]);
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

    include $this->template('freight/chat_page');
}
elseif($op=='freight_chat'){
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
        $send_openid = pdo_fetchcolumn('select openid from '.tablename('customs_freight_order').' where id=:id',[':id'=>$id]);
        $who_send=2;
    }
    #保存数据
    pdo_insert('customs_freight_chat',[
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
        'url' => 'https://shop.gogo198.cn/app/index.php?i=3&c=entry&do=freight&p=freight&op=sureOrderList&m=sz_yi&id='.$id.'&wxref=mp.weixin.qq.com',
        'openid' => $send_openid,
        'temp_id' => 'SVVs5OeD3FfsGwW0PEfYlZWetjScIT8kDxht5tlI1V8'
    ]);
    ihttp_request('https://shop.gogo198.cn/api/sendwechattemplatenotice.php', $post);

    echo json_encode(['code'=>0,'msg'=>'消息发送成功！']);exit;
}
elseif($op=='chat_page2'){
    $openid = $_W['openid'];
    $id = intval($_GPC['id']);
    $order = pdo_fetch('select * from '.tablename('customs_freight_order').' where id=:id',[':id'=>$id]);
    $manage_openid1 = 'ov3-bt8keSKg_8z9Wwi-zG1hRhwg';

    #查找聊天记录
    $list = pdo_fetchall('select createtime from '.tablename('customs_freight_chat').' where orderid=:orderid order by id desc',[':orderid'=>$id]);
    $identify = 0;
    if($order['openid']!=$manage_openid1){
        #用户
        $identify=1;
        pdo_update('customs_freight_chat',['is_read'=>1],['orderid'=>$id,'who_send'=>2]);
    }
    else{
        #管理员
        $identify=2;
        pdo_update('customs_freight_chat',['is_read'=>1],['orderid'=>$id,'who_send'=>1]);
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

    include $this->template('freight/chat_page');
}
elseif($op=='freight_chat2'){
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
        $send_openid = pdo_fetchcolumn('select openid from '.tablename('customs_freight_order').' where id=:id',[':id'=>$id]);
        $who_send=2;
    }
    
    #保存数据
    pdo_insert('customs_freight_chat',[
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
        'url' => 'https://shop.gogo198.cn/app/index.php?i=3&c=entry&do=freight&p=freight&op=sureOrderList&m=sz_yi&id='.$id.'&wxref=mp.weixin.qq.com',
        'openid' => $send_openid,
        'temp_id' => 'SVVs5OeD3FfsGwW0PEfYlZWetjScIT8kDxht5tlI1V8'
    ]);
    ihttp_request('https://shop.gogo198.cn/api/sendwechattemplatenotice.php', $post);

    echo json_encode(['code'=>0,'msg'=>'消息发送成功！']);exit;
}