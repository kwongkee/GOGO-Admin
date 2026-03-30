<?php
// 模块LTD提供
if (!defined('IN_IA')) {
	exit('Access Denied');
}

global $_W;
global $_GPC;

$op = !empty($_GPC['op'])?trim($_GPC['op']):'display';

if($op=='display'){
    if(!empty($_W['openid'])){
        //step1:先判断该用户有无申请商户
        $is_user = pdo_fetch('select a.id,b.user_status from '.tablename('enterprise_members').' a left join '.tablename('decl_user').' b on b.openid=a.openid where a.openid=:openid and a.uniacid=:uni limit 1',array(':openid'=>$_W['openid'],':uni'=>$_W['uniacid']));
        if(empty($is_user)){
            //先去成为商户
            header('Location: '.$_W['siteroot'].'./app/index.php?i='.$_W['uniacid'].'&c=entry&do=enterprise&m=sz_yi&p=register');
        }elseif($is_user['user_status']==1 ){
            exit('商户已被禁用，请联系管理员！');
        }elseif($is_user['user_status']==2){
            exit('商户正在审核，请耐心等待！');
        }

        //step2:获取商户的项目配置
        $list = pdo_fetchall('select project_name,id from '.tablename('customs_project').' where openid=:openid and uniacid=:uni order by id desc',array(':openid'=>$_W['openid'],':uni'=>$_W['uniacid']));

        //step3:获取商户上传的后台商品
//        and a.uniacid=:uni
        $goods = pdo_fetchall('select a.title,d.name as catename,a.id from '.tablename('sz_yi_goods').' a left join '.tablename('customs_goods_shelf_linked').' b on a.supplier_uid=b.uniacid left join '.tablename('decl_user').' c on b.user_id=c.id left join '.tablename('sz_yi_category').' d on a.pcate=d.id where c.user_status=0 and c.openid=:openid and c.uniacid=:uni and a.status=1',array(':openid'=>$_W['openid'],':uni'=>$_W['uniacid']));

        //step4:根据分类筛选产品
        $new_goods = [];
        foreach($goods as $k => $v){
            $new_goods[$v['catename']][] = $v;
        }
        include $this->template('member/customcollection');
    }else{
        exit('请先关注公众号并授权登录！');
    }
}elseif($op=='createorder'){
    $send_member = pdo_fetchcolumn('select user_name from '.tablename('decl_user').' where openid=:openid limit 1',array(':openid'=>$_W['openid']));
    if(empty($send_member)){
        $send_member = pdo_fetchcolumn('select nickname from '.tablename('sz_yi_member').' where openid=:openid limit 1',array(':openid'=>$_W['openid']));
    }
    //step1:先查找商城里有无此用户
    $receive_openid = pdo_fetchcolumn('select openid from'.tablename('sz_yi_member').' where mobile=:mob and uniacid=3 limit 1',array(':mob'=>trim($_GPC['payer_tel'])));
    
     $is_mobile = 0;
    if(empty($receive_openid)){
        //发送消息给用户绑定手机号,并同时注册会员
        $mobile = trim($_GPC['payer_tel']);
        $set = m('common')->getSysset();
        if(strlen($mobile)==11){
            //生成会员
            $m = pdo_fetchcolumn('select mobile from'.tablename('sz_yi_member').' where mobile=:mob and uniacid=3 limit 1',array(':mob'=>trim($_GPC['payer_tel'])));
            if(empty($m)){
                pdo_insert('sz_yi_member',['uniacid'=>3,'mobile'=>$mobile,'pwd'=>md5('888888'),'realname'=>trim($_GPC['payer_name']),'nickname'=>trim($_GPC['payer_name']),'createtime'=>time()]);
            }
            //您好，你的朋友/客户正通过「Gogo購購網」向你发起在线收款，为确保你的资金安全，请关注微信公众号：Gogo购购网，并向公众号发送：付款，依据提示查询及支付，如有任何疑问，可致电075786329911与我们联系。
            //  $this->sendSms($mobile, '','SMS_234397342');//sz_yi/core/inc/functions.php
            
                $post_data = [
                    'spid'=>'254560',
                    'password'=>'J6Dtc4HO',
                    'ac'=>'1069254560',
                    'mobiles'=>$mobile,
                    'content'=>'您好，你的朋友/客户正通过「Gogo購購網」向你发起在线收款，为确保你的资金安全，请关注微信公众号：Gogo购购网，并向公众号发送：付款，依据提示查询及支付，如有任何疑问，可致电075786329911与我们联系。 【GOGO】',
                ];
                $post_data = json_encode($post_data,true);
                $ch=curl_init();
                curl_setopt($ch,CURLOPT_URL,'https://decl.gogo198.cn/api/sendmsg_jumeng');
                curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
                curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,false);
                curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,false);
                curl_setopt($ch,CURLOPT_POST,1);
                curl_setopt($ch,CURLOPT_POSTFIELDS,$post_data);
                curl_setopt($ch, CURLOPT_HTTPHEADER,array(
                    'Content-Type: application/json; charset=utf-8',
                    'Content-Length:' . strlen($post_data),
                    'Cache-Control: no-cache',
                    'Pragma: no-cache'
                ));
                $output=curl_exec($ch);
                curl_close($ch);
        }else{
            show_json(-1, array('msg'=>'请输入正确的手机号！'));
        }
        $receive_openid = $mobile;//如果还没openid时，先赋予手机号
        $is_mobile = 1;
    }

    //step2:生成订单
    $ordersn = 'GP' . date('YmdH', time()) . str_pad(mt_rand(1, 999999), 6, '0',
            STR_PAD_LEFT) . substr(microtime(), 2, 6);

    $time = time();
    $pay_term = intval(trim($_GPC['pay_term']));

    $overdue = ( $pay_term * 86400 ) + $time;//逾期时间 付款期限*86400+发起时间

    if($_GPC['trans_form']==2){
        $overdue = ( $pay_term * 86400 ) + strtotime(trim($_GPC['advance_day']));//逾期时间 付款期限*86400+预约时间
    }

    //服务项目

    if(intval($_GPC['trade_type'])==3){
        $service_name = explode(',',substr($_GPC['service_name'],0,strlen($_GPC['service_name'])-1));
        $service_abstract = explode(',',substr($_GPC['service_abstract'],0,strlen($_GPC['service_abstract'])-1));
        $service_price = explode(',',substr($_GPC['service_price'],0,strlen($_GPC['service_price'])-1));

        foreach($service_name as $k=>$v){
            $service_info[] = $service_name[$k].','.$service_abstract[$k].','.$service_price[$k];
        }
        $service_info = json_encode($service_info,true);
    }else{
        $service_info = '';
    }

    $pay_fee = $_GPC['pay_fee']/100;

    $data = [
        'uniacid'=>3,
        'openid'=>$receive_openid,//接收的openid有可能是手机号或正常的openid
        'send_openid'=>$_W['openid'],
        'ordersn'=>$ordersn,
        'trade_price'=>trim($_GPC['trade_price']),
        'trade_type'=>intval($_GPC['trade_type']),
        'good_id'=>intval($_GPC['trade_type'])==1?intval($_GPC['good_id']):'',
        'project_id'=>intval($_GPC['trade_type'])==2?intval($_GPC['project_id']):'',
        'payer_name'=>trim($_GPC['payer_name']),
        'payer_tel'=>trim($_GPC['payer_tel']),
//        'idcard'=>trim($_GPC['idcard']),
        'pay_term'=>$pay_term,
        'pay_fee'=>$pay_fee,
        'overdue'=>$overdue,
        'trans_form'=>intval($_GPC['trans_form']),
        'advance_day'=>intval($_GPC['trans_form'])==2?strtotime(trim($_GPC['advance_day'])):'',
        'regular_type'=>intval($_GPC['trans_form'])==3?trim($_GPC['regular_type']):'',
        'every_day'=>(intval($_GPC['trans_form'])==3 && intval($_GPC['regular_type'])==1)?trim($_GPC['every_day']):'',
        'week'=>(intval($_GPC['trans_form'])==3 && intval($_GPC['regular_type'])==2)?trim($_GPC['week']):'',
        'every_week'=>(intval($_GPC['trans_form'])==3 && intval($_GPC['regular_type'])==2)?trim($_GPC['every_week']):'',
        'month'=>(intval($_GPC['trans_form'])==3 && intval($_GPC['regular_type'])==3)?trim($_GPC['month']):'',
        'every_month'=>(intval($_GPC['trans_form'])==3 && intval($_GPC['regular_type'])==3)?trim($_GPC['every_month']):'',
        'every_year'=>(intval($_GPC['trans_form'])==3 && intval($_GPC['regular_type'])==4)?trim($_GPC['every_year']):'',
        'end_time'=>(intval($_GPC['trans_form'])==3)?strtotime(trim($_GPC['end_time'])):'',
        'createtime'=>$time,
        'basic'=>intval($_GPC['basic']),
        'contract_num'=> intval($_GPC['basic'])==1?trim($_GPC['contract_num']):'',
        'contract_file'=> intval($_GPC['basic'])==1?json_encode($_GPC['contract_file'],true):'',
        'orderno'=>intval($_GPC['basic'])==2?trim($_GPC['orderno']):'',
        'orderurl'=>intval($_GPC['basic'])==2?trim($_GPC['orderurl']):'',
        'orderdemo'=>intval($_GPC['basic'])==2?json_encode($_GPC['orderdemo'],true):'',
        'description'=>intval($_GPC['basic'])==3?trim($_GPC['description']):'',
        'service_info'=>$service_info
    ];

    $res = pdo_insert('customs_collection',$data);
    $insertid = pdo_insertid();

    if($res){
        $first = '';
        if(intval($_GPC['trade_type'])==1){
//                and uniacid=:uni
//                ,':uni'=>$_W['uniacid']
            $first = pdo_fetchcolumn('select title from '.tablename('sz_yi_goods').' where id=:id ',array(':id'=>intval($_GPC['good_id'])));
        }else{
            $first = pdo_fetchcolumn('select project_name from '.tablename('customs_project').' where id=:id',array(':id'=>intval($_GPC['project_id'])));
        }
        if($data['trans_form']==1){
            //即时发送
            if($data['trans_form']==1){
                $trans_form = '立即收款';
            }else if($data['trans_form']==2){
                $trans_form = '预约收款';
            }else if($data['trans_form']==3){
                $trans_form = '定期收款';
            }
            //将要修改
            $msg_template['first'] = '你好！您有一笔来自［'.$send_member.'］的［'.$trans_form.'］付款请求，请点击消息支付，为确保您的资金安全，如对此支付请求有疑问，请暂缓支付并致电Gogo客服电话07578632991咨询或反馈，感谢您使用Gogo服务。';
            if($data['basic']==1){
                $data['basic'] = '合同号：'.$data['contract_num'];
            }elseif($data['basic']==2){
                $data['basic'] = '订单号：'.$data['orderno'];
            }elseif($data['basic']==3){
                $data['basic'] = '备注信息：'.$data['description'];
            }
            if($is_mobile!=1){
                $post = json_encode([
                    'call'=>'collectionNotice',
                    'first' =>$msg_template['first'],
                    'keyword1' => $data['ordersn'],
                    'keyword2' => date('Y-m-d H:i:s',$time),
                    'keyword3' => 'CNY '.$data['trade_price'],
                    'keyword4' => $data['basic'],
                    'keyword5'=> date('Y-m-d H:i:s',$overdue),
                    'remark' => 'Gogo在线收款服务为商户提供合规安全的即时、预约及定期收款通知与在线支付服务，如需了解，可回复”8“了解及与客服联系。',
                    'url' => 'https://shop.gogo198.cn/app/index.php?i=3&c=entry&do=member&p=custompayment&m=sz_yi&oid='.$insertid,
                    'openid' => $receive_openid,
                    'temp_id' => 'YU8Nczq9tyT8CNUyu9Lnyi0VcASZ4VBkEzTnB2adal4'
                ]);
                ihttp_request('https://shop.gogo198.cn/api/sendwechattemplatenotice.php', $post);
            }

            pdo_update('customs_collection',['is_send'=>1],['id'=>$insertid]);
            show_json(1);

        }else{
            show_json(1);
        }
    }
}elseif($op=='search_paycollect_info'){
    //查询收付款信息
    if($_W['ispost']){
        $type = intval($_GPC['type']);
        $mobile = trim($_GPC['mobile']);
        if(strlen($mobile)!=11){
            show_json(-1,['msg'=>'查询失败，请输入正确的手机号！']);
        }

        $member_info = pdo_fetch('select openid,id from '.tablename('sz_yi_member').' where mobile=:mob',[':mob'=>$mobile]);//132
        $is_mobile = 0;
        if(empty($member_info['openid']) && $member_info['id']>0){
            //如果没有openid，则通过手机号/ID 更改openid
            $is_other = pdo_fetch('select openid from '.tablename('sz_yi_member').' where openid=:openid',[':openid'=>$_W['openid']]);
            if(empty($is_other['openid'])){
                pdo_update('sz_yi_member',['openid'=>$_W['openid']],['id'=>$member_info['id']]);
                $member_info['openid'] = $_W['openid'];
                $is_mobile = 1;
            }else{
                pdo_update('sz_yi_member',['openid'=>$_W['openid']],['id'=>$member_info['id']]);
            }
        }

        if($type==1){
            //收款查询
            $list = pdo_fetchall('select * from '.tablename('customs_collection').' where send_openid=:openid and uniacid=:uni order by createtime desc',array(':openid'=>$member_info['openid'],':uni'=>$_W['uniacid']));
        }elseif($type==2){
            //付款查询
            $list2 = pdo_fetchall('select a.*,b.user_name from '.tablename('customs_collection').' a left join '.tablename('decl_user').' b on b.openid=a.send_openid where a.openid=:openid and a.uniacid=:uni and a.createtime <> "" order by a.createtime desc',array(':openid'=>$mobile,':uni'=>$_W['uniacid']));
            $list3 = pdo_fetchall('select a.*,b.user_name from '.tablename('customs_collection').' a left join '.tablename('decl_user').' b on b.openid=a.send_openid where a.openid=:openid and a.uniacid=:uni and a.createtime <> "" order by a.createtime desc',array(':openid'=>$member_info['openid'],':uni'=>$_W['uniacid']));
            $list = array_merge($list2,$list3);

        }

        if(!empty($list)){
            foreach($list as $k=>$v){
                // 0待付款，1已付款
                if($v['status']==0){
                    $list[$k]['status']='待付款';
                }elseif($v['status']==1){
                    $list[$k]['status']='已付款';
                }
            }
        }

        show_json(1,['data'=>$list]);
    }else{
        include $this->template('member/paycollect_info');
    }
}

?>