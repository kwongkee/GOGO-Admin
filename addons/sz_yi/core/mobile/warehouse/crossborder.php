<?php
// 模块LTD提供
if (!defined('IN_IA')) {
    exit('Access Denied');
}

global $_W;
global $_GPC;

$op = !empty($_GPC['op'])?trim($_GPC['op']):'display';
$openid = $_W['openid'];

if($op=='index') {
    #管理员进入查看拖车订单/仓储订单

    include $this->template('warehouse/crossborder/index');
}
elseif($op=='freight_list'){
    #拖车订单
    $list = pdo_fetchall('select * from '.tablename('customs_freight_order').' where 1 order by id desc');
    foreach($list as $k=>$v){
        $list[$k]['createtime'] = date('m月d日',$v['createtime']);
    }

    include $this->template('warehouse/crossborder/freight_list');
}
elseif($op=='warehouse_list'){
    #仓储订单
    $list = pdo_fetchall('select * from '.tablename('customs_warehouse_order').' where 1 order by id desc');
    foreach($list as $k=>$v){
        $list[$k]['createtime'] = date('m月d日',$v['createtime']);
    }
    include $this->template('warehouse/crossborder/warehouse_list');
}

elseif($op=='order_list'){
    #订单列表
    $openid = $_W['openid'];
    $id = intval($_GPC['id']);
    $order = pdo_fetch('select * from '.tablename('customs_crossorder_list').' where id=:id',[':id'=>$id]);
    $order['content'] = json_decode($order['content'],true);
    $time = time();

    if(!isset($_GPC['sub'])){
        #扫码进来
        if($order['status']==3){
            echo '<h1>该仓储订单已冻结！</h1>';exit;
        }

//        if(empty($openid)){
//            header('Location:https://mp.weixin.qq.com/mp/profile_ext?action=home&__biz=MzA4MDQyMTMxMQ==&scene=110#wechat_redirect');
//        }
//        $fans_info = pdo_fetch('select follow from '.tablename('mc_mapping_fans').' where openid=:openid and uniacid=3',[':openid'=>$openid]);
//        if(empty($fans_info['follow'])){
//            header('Location:https://mp.weixin.qq.com/mp/profile_ext?action=home&__biz=MzA4MDQyMTMxMQ==&scene=110#wechat_redirect');
//        }

        $manage_openid1 = 'ov3-bt8keSKg_8z9Wwi-zG1hRhwg';
        $manage_openid2 = 'ov3-bt5vIxepEjWc51zRQNQbFSaQ';
        #修改下单的openid
        if($openid!=$manage_openid1){
            pdo_update('customs_crossorder_list',['openid'=>$openid],['id'=>$id]);
        }

        $price = 0;
        $total_price = 0;
        foreach($order['content']['event_currency'] as $k=>$v){
            $order['content']['event_currency'][$k] = pdo_fetchcolumn('select code_name from '.tablename('currency').' where code_value=:code_value',[':code_value'=>$v]);
            $order['content']['event_currency'][$k] = explode('(',explode(')',$order['content']['event_currency'][$k])[0])[1];
            #检测账单下的订单状态
            $child_oid = explode('id=',$order['content']['event_url'][$k])[1];
            $child_oinfo = pdo_fetch('select update_status from '.tablename('customs_crossorder_detail').' where id=:id',[':id'=>$child_oid]);
            $order['content']['upd_status'][$k] = $child_oinfo['update_status'];
        }
    }
    else{
        if($_GPC['sub']==1){
            #订单确认

            #检测有无关注公众号
            $fans_info = pdo_fetch('select follow from '.tablename('mc_mapping_fans').' where openid=:openid and uniacid=3',[':openid'=>$openid]);
            if(empty($fans_info['follow'])){
                echo json_encode(['code'=>-2,'msg'=>'系统检测您还未关注公众号，为了能更好地提供消息服务，请先关注公众号！','link'=>'https://mp.weixin.qq.com/mp/profile_ext?action=home&__biz=MzA4MDQyMTMxMQ==&scene=110#wechat_redirect']);die;
            }
            
            #1、判断该用户有无进行实名认证
            $enterprise_members = pdo_fetch('select id,realname,mobile from '.tablename('enterprise_members').' where openid=:openid',[':openid'=>$openid]);
            $enterprise_id = 0;
            if(empty($enterprise_members['id'])){
                echo json_encode(['code'=>-1,'msg'=>'系统检测你还未进行信息录入，正在打开信息录入页面！']);die;
            }
            
            #2、检查有无子订单还在"不通过"和"审核中"
            $is_have_processing = pdo_fetch('select id from '.tablename('customs_crossorder_detail').' where (update_status=1 or update_status=3) and orderid=:id',[':id'=>$id]);
            if($is_have_processing['id']>0){
                echo json_encode(['code'=>-3,'msg'=>'确认失败，系统检测到您的账单事项还处于"审核不通过"或"已提交待审核"状态中！']);die;
            }
//            if(empty($enterprise_members['id'])){
//                $time = time();
//                $name = trim($_GPC['name']);
//                $mobile = trim($_GPC['mobile']);
//                if($name == ''){$name = $enterprise_members['realname'];}
//                if($mobile == ''){$mobile = $enterprise_members['mobile'];}
//                $manage_person_id = 0;
//                $is_have_manageperson = pdo_fetch('select id from '.tablename('centralize_manage_person').' where enterprise_id=:enterprise_id',[':enterprise_id'=>$enterprise_members['id']]);
//                if(empty($enterprise_members['id'])){
//                    pdo_insert('centralize_manage_person',[
//                        'name'=>$name,
//                        'type'=>1,
//                        'tel'=>$mobile,
//                        'email'=>'',
//                        'status'=>0,
//                        'createtime'=>$time
//                    ]);
//                    $manage_person = pdo_insertid();
//                    if($manage_person){
//                        pdo_insert('enterprise_members',[
//                            'uniacid'=>3,
//                            'openid'=>$_W['openid'],
//                            'nickname'=>$name,
//                            'realname'=>$name,
//                            'mobile'=>$mobile,
//                            'reg_type'=>1,
//                            'create_at'=>$time,
//                            'centralizer_id'=>$manage_person,
//                            'entrance'=>1
//                        ]);
//                        $enterprise_id = pdo_insertid();
//                    }
//
//                    pdo_update('centralize_manage_person',['enterprise_id'=>$enterprise_id],['id'=>$manage_person]);
//                    $manage_person_id = $manage_person;
//                }else{
//                    $manage_person_id = $is_have_manageperson['id'];
//                    $enterprise_id = $enterprise_members['id'];
//                }
//
////                $link = 'https://decl.gogo198.cn/centralize/index/register_merch?pre_id='.base64_encode($manage_person_id).'&eid='.base64_encode($order['company_id']);
////                echo json_encode(['code'=>-1,'msg'=>'系统检测你还未实名认证，正在跳转进行实名认证！','link'=>$link]);die;
//            }else{
//                $enterprise_id = $enterprise_members['id'];
//            }

            #2、有认证后确认订单提交
            pdo_update('customs_crossorder_list',[
                'status'=>2,
                'enterprise_id'=>$enterprise_members['id'],
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

    include $this->template('warehouse/crossborder/order_list');
}
elseif($op=='insert_info'){
    #录入下单人姓名和手机号码
    $id = intval($_GPC['id']);
    
    pdo_insert('enterprise_members',[
        'uniacid'=>3,
        'openid'=>$_W['openid'],
        'nickname'=>trim($_GPC['name']),
        'realname'=>trim($_GPC['name']),
        'mobile'=>trim($_GPC['mobile']),
        'reg_type'=>1,
        'create_at'=>time(),
        'centralizer_id'=>0,
        'entrance'=>1
    ]);
    $iinserid = pdo_insertid();
    $res = pdo_update('customs_crossorder_list',['name'=>trim($_GPC['name']),'mobile'=>trim($_GPC['mobile']),'enterprise_id'=>$iinserid],['id'=>$id]);
    if($res){
        echo json_encode(['code'=>0]);
    }
}
elseif($op=='order_detail'){
    $key = intval($_GPC['key']);
    $id = intval($_GPC['id']);#小订单id
    $boid = intval($_GPC['boid']);#大订单id
    $manage_openid1 = 'ov3-bt8keSKg_8z9Wwi-zG1hRhwg';
    $manage_openid2 = 'ov3-bt5vIxepEjWc51zRQNQbFSaQ';
    #记录账单id
    pdo_update('customs_crossorder_detail',['orderid'=>$boid],['id'=>$id]);
    if(isset($_GPC['update'])){
        #检测有无关注公众号
        $fans_info = pdo_fetch('select follow from '.tablename('mc_mapping_fans').' where openid=:openid and uniacid=3',[':openid'=>$openid]);
        if(empty($fans_info['follow'])){
            echo json_encode(['code'=>-2,'msg'=>'系统检测您还未关注公众号，为了能更好地提供消息服务，请先关注公众号！','link'=>'https://mp.weixin.qq.com/mp/profile_ext?action=home&__biz=MzA4MDQyMTMxMQ==&scene=110#wechat_redirect']);die;
        }
        #修改订单详情信息
        if(isset($_GPC['company_file'])){
            $_GPC['content']['declare_file'] = $_GPC['company_file'];
        }
        #账单-类型
        $order_detail = pdo_fetch('select content from '.tablename('customs_crossorder_list').' where id=:id',[':id'=>$boid]);
        $order_detail['content'] = json_decode($order_detail['content'],true);
        $order_type = $order_detail['content']['event_name'][$key];
        
        if($_GPC['update']==1){
            #用户操作
            $content = json_encode($_GPC['content'],true);
            $update_status=1;
            $status='';
            if(isset($_GPC['update_status'])){
                $update_status = intval($_GPC['update_status']);
                if($update_status==2){
                    $status='已审核并通过';        
                }elseif($update_status==3){
                    $status='已审核不通过';        
                }
            }
            $res = pdo_update('customs_crossorder_detail',[
                'content'=>$content,
                'update_status'=>$update_status,
                'remark'=>trim($_GPC['remark']),
                'update_currency'=>trim($_GPC['update_currency']),
                'update_price'=>trim($_GPC['update_price']),
                'is_add'=>0
            ],['id'=>$id]);
            if($res){
                #通知管理员进入核实订单金额
                if($update_status==1){
                    $post = json_encode([
                        'call'=>'confirmCollectionNotice',
                        'first' =>'管理员您好！有['.$order_type.']订单详情信息修改,请打开查阅并进行最终确认！',
                        'keyword1' => '订单修改',
                        'keyword2' => '已提交待审核',
                        'keyword3' => date('Y-m-d H:i:s',time()),
                        'remark' => '点击查看详情',
                        'url' => 'https://shop.gogo198.cn/app/index.php?i=3&c=entry&do=warehouse&p=crossborder&op=order_list&m=sz_yi&id='.$boid,
                        'openid' => $manage_openid2,
                        'temp_id' => 'SVVs5OeD3FfsGwW0PEfYlZWetjScIT8kDxht5tlI1V8'
                    ]);
                    ihttp_request('https://shop.gogo198.cn/api/sendwechattemplatenotice.php', $post);
                    
                    echo json_encode(['code'=>0,'msg'=>'修改成功,待管理员确认!']);exit;
                }else{
                    $post = json_encode([
                        'call'=>'confirmCollectionNotice',
                        'first' =>'管理员您好！用户已对['.trim($_GPC['content']['event_name']).']事项信息进行['.$status.'],请知悉！',
                        'keyword1' => $status,
                        'keyword2' => '已提交待审核',
                        'keyword3' => date('Y-m-d H:i:s',time()),
                        'remark' => '点击查看详情',
                        'url' => 'https://shop.gogo198.cn/app/index.php?i=3&c=entry&do=warehouse&p=crossborder&op=order_list&m=sz_yi&id='.$boid,
                        'openid' => $manage_openid2,
                        'temp_id' => 'SVVs5OeD3FfsGwW0PEfYlZWetjScIT8kDxht5tlI1V8'
                    ]);
                    ihttp_request('https://shop.gogo198.cn/api/sendwechattemplatenotice.php', $post);
                    
                    echo json_encode(['code'=>0,'msg'=>'操作成功,已通知管理员!']);exit;
                }
                
            }else{
                echo json_encode(['code'=>-1,'msg'=>'暂无修改']);exit;
            }
        }elseif($_GPC['update']==2){
            #管理员操作
            $border = pdo_fetch('select * from '.tablename('customs_crossorder_list').' where id=:id',[':id'=>$boid]);
            $update_currency = '';
            $update_price = '';
            #修改大订单金额
            if((intval($_GPC['update_status'])==2 || empty($_GPC['update_status']) || !isset($_GPC['update_status'])) && !empty(trim($_GPC['update_currency'])) && !empty(trim($_GPC['update_price']))){
                #确认时,自动计算大订单应付金额,税费,实付金额.
                $border['content'] = json_decode($border['content'],true);
                $update_currency = trim($_GPC['update_currency']);
                $update_price = trim($_GPC['update_price']);
                $border['content']['event_totalprice'][$key] = $update_price;
                
                if($order_detail['type2']==1){
                    $_GPC['content']['freight_currency'] = $update_currency;
                    $_GPC['content']['freight_money'] = $update_price;  
                }elseif($order_detail['type2']==2){
                    $_GPC['content']['weight_currency'] = $update_currency;
                    $_GPC['content']['weight_money'] = $update_price;
                }elseif($order_detail['type2']==3){
                    $_GPC['content']['declare_currency'] = $update_currency;
                    $_GPC['content']['declare_money'] = $update_price;
                }elseif($order_detail['type2']==7){
                    $_GPC['content']['incidental_currency'] = $update_currency;
                    $_GPC['content']['incidental_money'] = $update_price;
                }
                if($order_detail['type']==2){
                    $border['content']['event_currency'][$key] = $update_currency;
                    $border['content']['event_totalprice'][$key] = $update_price;
                }
                #应付金额
                $price = 0;
                foreach($border['content']['event_totalprice'] as $k => $v){
                    $price += $v;                    
                }
                #开票税费
                $invoicing_tax = sprintf('%.2f',$price * $border['tax_num']);
                #实付金额
                $real_price = floatval($price) + floatval($invoicing_tax);
                pdo_update('customs_crossorder_list',['content'=>json_encode($border['content'],true),'invoicing_tax'=>$invoicing_tax,'price'=>$price,'real_price'=>$real_price],['id'=>$boid]);
            }
            $content = json_encode($_GPC['content'],true);
            #修改小订单
            $res = pdo_update('customs_crossorder_detail',[
                'content'=>$content,
                'update_status'=>intval($_GPC['update_status']),
                'update_currency'=>$update_currency,
                'update_price'=>$update_price,
                'remark'=>trim($_GPC['remark']),
                'is_add'=>0
            ],['id'=>$id]);
            
            if($res){
                #通知用户进入核实订单金额
                if(!empty($border['openid'])){
                    $keyword2 = '';
                    if(intval($_GPC['update_status'])==2){
                        $keyword2 = '已审核并通过';
                    }elseif(intval($_GPC['update_status'])==3){
                        $keyword2 = '已审核不通过';
                    }elseif(intval($_GPC['update_status'])==0){
                        $keyword2 = '已修改';
                    }

                    $post = json_encode([
                        'call'=>'confirmCollectionNotice',
                        'first' =>'您好！管理员已对您的['.$order_type.']订单信息进行审核,请打开查阅！',
                        'keyword1' => '订单审核',
                        'keyword2' => $keyword2,
                        'keyword3' => date('Y-m-d H:i:s',time()),
                        'remark' => '点击查看详情',
                        'url' => 'https://shop.gogo198.cn/app/index.php?i=3&c=entry&do=warehouse&p=crossborder&op=order_list&m=sz_yi&id='.$boid,
                        'openid' => $border['openid'],
                        'temp_id' => 'SVVs5OeD3FfsGwW0PEfYlZWetjScIT8kDxht5tlI1V8'
                    ]);
                    ihttp_request('https://shop.gogo198.cn/api/sendwechattemplatenotice.php', $post);
                }
                
                echo json_encode(['code'=>0,'msg'=>'操作成功,已通知用户!']);exit;
            }else{
                echo json_encode(['code'=>-1,'msg'=>'暂无修改']);exit;
            }
        }
        
    }else{
        $order = pdo_fetch('select * from '.tablename('customs_crossorder_detail').' where id=:id',[':id'=>$id]);
        $order['content'] = json_decode($order['content'],true);
        $unit = pdo_fetchall('select * from '.tablename('unit').' where 1');
        $currency = pdo_fetchall('select * from '.tablename('currency').' where 1');
        #港口
        $fPort = pdo_fetchall('select * from '.tablename('customs_freight_port_name').' where pid=0');
        #具体港口名称
        $order_fPort = pdo_fetch('select * from '.tablename('customs_freight_port_name').' where code=:code',[':code'=>$order['content']['fPort']]);
        $sPort = pdo_fetchall('select * from '.tablename('customs_freight_port_name').' where pid=:pid',['pid'=>$order_fPort['id']]);
        $border = pdo_fetch('select * from '.tablename('customs_crossorder_list').' where id=:id',[':id'=>$boid]);
        #是否管理员
        $identify=1;#普通用户
        if($openid==$manage_openid1){
            $identify=2;#管理员
        }
    }

    include $this->template('warehouse/crossborder/order_detail');
}
elseif($op=='del_order'){
    $key = intval($_GPC['key']);
    $orderid = intval($_GPC['id']);#小订单id
    $boid = intval($_GPC['boid']);#大订单id

    $order = pdo_fetch('select * from '.tablename('customs_crossorder_list').' where id=:id',[':id'=>$boid]);
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
    unset($order['content']['event_url'][$key]);
    $order['content']['event_date'] =array_values($order['content']['event_date']);
    $order['content']['event_type'] =array_values($order['content']['event_type']);
    $order['content']['event_name'] =array_values($order['content']['event_name']);
    $order['content']['event_unit'] =array_values($order['content']['event_unit']);
    $order['content']['event_currency'] =array_values($order['content']['event_currency']);
    $order['content']['event_price'] =array_values($order['content']['event_price']);
    $order['content']['event_num'] =array_values($order['content']['event_num']);
    $order['content']['event_totalprice'] =array_values($order['content']['event_totalprice']);
    $order['content']['event_remark'] =array_values($order['content']['event_remark']);
    $order['content']['event_url'] =array_values($order['content']['event_url']);

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
        'event_url'=>$order['content']['event_url'],
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
    $res = pdo_update('customs_crossorder_list',['content'=>$content,'price'=>$price,'real_price'=>$real_price,'invoicing_tax'=>$invoicing_tax],['id'=>$boid]);
    if($res){
        pdo_delete('customs_crossorder_detail',['id'=>$orderid]);
        echo json_encode(['code'=>0,'msg'=>'删除成功！']);exit;
    }
}
elseif($op=='get_sport'){
    #获取子港口名称
    $fPort = pdo_fetch('select * from '.tablename('customs_freight_port_name').' where code=:code',[':code'=>trim($_GPC['code'])]);
    $sPort = pdo_fetchall('select * from '.tablename('customs_freight_port_name').' where pid=:pid',['pid'=>$fPort['id']]);

    echo json_encode(['code'=>0,'data'=>$sPort]);exit;
}
elseif($op=='money_detail'){
    $id = intval($_GPC['id']);
    $detail = pdo_fetch('select * from '.tablename('customs_crossorder_list').' where id=:id',[':id'=>$id]);
    $detail['currency'] = pdo_fetchcolumn('select code_name from '.tablename('currency').' where code_value=:code_value',[':code_value'=>$detail['currency']]);
    include $this->template('warehouse/crossborder/money_detail');
}
elseif($op=='chat_page'){
    $openid = $_W['openid'];
    $id = intval($_GPC['id']);
    $order = pdo_fetch('select * from '.tablename('customs_crossorder_list').' where id=:id',[':id'=>$id]);
    $manage_openid1 = 'ov3-bt8keSKg_8z9Wwi-zG1hRhwg';
    $manage_openid2 = 'ov3-bt5vIxepEjWc51zRQNQbFSaQ';

    #查找聊天记录
    $list = pdo_fetchall('select createtime from '.tablename('customs_crossorder_chat').' where orderid=:orderid order by id desc',[':orderid'=>$id]);
    $identify = 0;
    if($order['openid']!=$manage_openid1){
        #用户
        $identify=1;
        pdo_update('customs_crossorder_chat',['is_read'=>1],['orderid'=>$id,'who_send'=>2]);
    }
    else{
        #管理员
        $identify=2;
        pdo_update('customs_crossorder_chat',['is_read'=>1],['orderid'=>$id,'who_send'=>1]);
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
        $chat_group[$k]['info'] = pdo_fetchall('select * from '.tablename('customs_crossorder_chat').' where orderid=:orderid and ( createtime>=:starttime and createtime<=:endtime) ',[':orderid'=>$id,':endtime'=>$endtime,':starttime'=>$starttime]);
    }

    #整理数组
    foreach($chat_group as $k=>$v){
        foreach($v['info'] as $kk=>$vv){
            $chat_group[$k]['info'][$kk]['content'] = json_decode($vv['content'],true);
            $chat_group[$k]['info'][$kk]['createtime'] = date('H:i',$vv['createtime']);
        }
    }

    include $this->template('warehouse/crossborder/chat_page');
}
elseif($op=='crossorder_chat'){
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
        $send_openid = pdo_fetchcolumn('select openid from '.tablename('customs_crossorder_list').' where id=:id',[':id'=>$id]);
        $who_send=2;
    }
    #保存数据
    pdo_insert('customs_crossorder_chat',[
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
        'keyword1' => '留言回复',
        'keyword2' => '已收到',
        'keyword3' => date('Y-m-d H:i:s',$time),
        'remark' => '点击查看详情',
        'url' => 'https://shop.gogo198.cn/app/index.php?i=3&c=entry&do=warehouse&p=crossborder&op=order_list&m=sz_yi&id='.$id,
        'openid' => $send_openid,
        'temp_id' => 'SVVs5OeD3FfsGwW0PEfYlZWetjScIT8kDxht5tlI1V8'
    ]);
    ihttp_request('https://shop.gogo198.cn/api/sendwechattemplatenotice.php', $post);

    echo json_encode(['code'=>0,'msg'=>'消息发送成功！']);exit;
}
elseif($op=='add_orderdetail'){
    #增加费用事项
    $id = intval($_GPC['id']);
    
    if($_W['ispost']){
        $manage_openid1 = 'ov3-bt8keSKg_8z9Wwi-zG1hRhwg';
        $manage_openid2 = 'ov3-bt5vIxepEjWc51zRQNQbFSaQ';
        #是否管理员
        $identify=1;#普通用户
        if($openid==$manage_openid2){
            $identify=2;#管理员
        }
        
        $time = time();
        #1、插入新的订单详情表
        pdo_insert('customs_crossorder_detail',['content'=>json_encode($_GPC['content'],true),'type'=>intval($_GPC['type']),'type2'=>intval($_GPC['type2']),'orderid'=>$id,'createtime'=>$time,'status'=>1,'update_status'=>1,'is_add'=>$identify]);
        $detail_id=pdo_insertid();
        #2、插入账单表
        $border = pdo_fetch('select * from '.tablename('customs_crossorder_list').' where id=:id',[':id'=>$id]);
        #新增时,自动计算大订单应付金额,税费,实付金额.
        $border['content'] = json_decode($border['content'],true);
        #应付金额
        $price = floatval($_GPC['content']['event_totalprice']);
        foreach($border['content']['event_totalprice'] as $k => $v){
            $price += $v;                
        }
        #开票税费
        $invoicing_tax = sprintf('%.2f',$price * $border['tax_num']);
        #实付金额
        $real_price = floatval($price) + floatval($invoicing_tax);
        #插入内容
        array_push($border['content']['event_date'],trim($_GPC['content']['event_date']));
        array_push($border['content']['event_type'],intval($_GPC['content']['event_type']));
        array_push($border['content']['event_name'],trim($_GPC['content']['event_name']));
        array_push($border['content']['event_unit'],trim($_GPC['content']['event_unit']));
        array_push($border['content']['event_currency'],trim($_GPC['content']['event_currency']));
        array_push($border['content']['event_price'],trim($_GPC['content']['event_price']));
        array_push($border['content']['event_num'],trim($_GPC['content']['event_num']));
        array_push($border['content']['event_totalprice'],trim($_GPC['content']['event_totalprice']));
        array_push($border['content']['event_remark'],trim($_GPC['content']['event_remark']));
        array_push($border['content']['event_url'],'https://shop.gogo198.cn/app/index.php?i=3&c=entry&do=warehouse&p=crossborder&op=order_detail&m=sz_yi&id='.$detail_id);
        pdo_update('customs_crossorder_list',['content'=>json_encode($border['content'],true),'invoicing_tax'=>$invoicing_tax,'price'=>$price,'real_price'=>$real_price],['id'=>$id]);
        $name = '';
        $check_name = '';
        $send_openid='';
        if($identify==1){
            #用户添加通知管理员确认
            $name='用户';
            $check_name = '管理员';
            $send_openid=$manage_openid2;
        }elseif($identify==2){
            #管理员添加通知用户确认
            $name='管理员';
            $check_name = '用户';
            $send_openid=$border['openid'];
        }
        if(!empty($border['openid'])){
            $post = json_encode([
                'call'=>'confirmCollectionNotice',
                'first' =>'您好！'.$name.'已新增['.trim($_GPC['content']['event_name']).']事项信息,请打开查阅！',
                'keyword1' => '新增事项',
                'keyword2' => '待审核',
                'keyword3' => date('Y-m-d H:i:s',$time),
                'remark' => '点击查看详情',
                'url' => 'https://shop.gogo198.cn/app/index.php?i=3&c=entry&do=warehouse&p=crossborder&op=order_list&m=sz_yi&id='.$id,
                'openid' => $send_openid,
                'temp_id' => 'SVVs5OeD3FfsGwW0PEfYlZWetjScIT8kDxht5tlI1V8'
            ]);
            ihttp_request('https://shop.gogo198.cn/api/sendwechattemplatenotice.php', $post);
        }else{
            pdo_update('customs_crossorder_detail',['is_add'=>0,'update_status'=>0],['id'=>$detail_id]);
        }
        echo json_encode(['code'=>0,'msg'=>'新增成功,待['.$check_name.']审核']);die;
    }else{
        $unit = pdo_fetchall('select * from '.tablename('unit').' where 1');
        $currency = pdo_fetchall('select * from '.tablename('currency').' where 1');
        
        include $this->template('warehouse/crossborder/add_orderdetail');
    }
}
elseif($op=='chat_page2'){
    $openid = $_W['openid'];
    $id = intval($_GPC['id']);
    $order = pdo_fetch('select * from '.tablename('customs_crossorder_list').' where id=:id',[':id'=>$id]);
    $manage_openid1 = 'ov3-bt8keSKg_8z9Wwi-zG1hRhwg';

    #查找聊天记录
    $list = pdo_fetchall('select * from '.tablename('customs_crossorder_chat').' where orderid=:orderid and pid=0 order by id desc',[':orderid'=>$id]);
    foreach($list as $k=>$v){
        $list[$k]['createtime'] = date('Y-m-d H:i',$v['createtime']);
        $list[$k]['content'] = json_decode($v['content'],true);
        if($v['who_send']==1){
            $list[$k]['identify_role'] = '用户';
        }elseif($v['who_send']==2){
            $list[$k]['identify_role'] = '管理员';
        }
        $list[$k]['child_content'] = pdo_fetchall('select * from '.tablename('customs_crossorder_chat').' where orderid=:orderid and pid=:pid order by id desc',[':orderid'=>$id,':pid'=>$v['id']]);
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

    include $this->template('warehouse/crossborder/chat_page');
}