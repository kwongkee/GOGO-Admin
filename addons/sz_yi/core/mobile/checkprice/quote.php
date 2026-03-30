<?php
if (!defined('IN_IA')) {
    exit('Access Denied');
}

global $_W;
global $_GPC;
$op = !empty($_GPC['op'])?trim($_GPC['op']):'display';
$openid = $_W['openid'];
$time = TIMESTAMP;
$data = $_GPC;

#网站字体、颜色设置
$website = pdo_fetch('select * from '.tablename('website_basic').' where id=1');

if($op=='quote_list'){
    #报价列表
    $id = isset($data['id'])?intval($data['id']):0;
    if(isset($data['pa'])){
        $limit = intval($data['limit']);
        $page = (intval($data['page'])-1) * $limit;
        $offset = ' limit '.$page.','.$limit;
        $count = pdo_fetchcolumn('select count(id) from '.tablename('website_quote_order').' where uid=:user_id',[':user_id'=>$_SESSION['account']['id']]);
        $list = pdo_fetchall('select a.*,c.ordersn,c.buss_id,a.is_notice from '.tablename('website_quote_order').' a left join '.tablename('website_inquiry_order').' c on c.id=a.inquiry_id where a.uid=:user_id order by a.id desc '.$offset,[':user_id'=>$_SESSION['account']['id']]);
        $status = [0=>'未报价',1=>'已报价',2=>'已下单',3=>'已接单'];
        foreach($list as $k=>$v){
            $list[$k]['buss_name'] = pdo_fetchcolumn('select `name` from '.tablename('website_bussiness').' where id=:buss_id',[':buss_id'=>$v['buss_id']]);
            $list[$k]['statusname'] = $status[$v['status']];
            $list[$k]['createtime'] = date('Y-m-d H:i',$v['createtime']);
        }
        die(json_encode(['code'=>0,'data'=>$list,'count'=>$count]));
    }
    include $this->template('checkprice/quote_list');
}
elseif($op=='quote_info'){
    #询价的报价详情
    // $_SESSION['account'] = [];
    $id = isset($data['id'])?$data['id']:0;
    if($_W['isajax']){
        #判断有无登录
        if(empty($_SESSION['account']['id'])){
            show_json(-3,['msg'=>'请先登录']);
        }
        
        if(isset($data['check'])){
            #确认接单
            $account = pdo_fetch('select * from '.tablename('website_user').' where id=:id',[':id'=>$_SESSION['account']['id']]);
            $_SESSION['account'] = $account;
            if(empty($account['is_verify'])){
                show_json(-1,['msg'=>'首次接单请完成商户实名认证']);
            }
            if($account['merch_status']==1){
                show_json(-2,['msg'=>'请等待客服人员商户认证审批']);
            }
            
            $order = pdo_fetch('select c.openid,c.phone,c.email,b.ordersn,b.status,a.inquiry_id,a.id from '.tablename('website_quote_order').' a left join '.tablename('website_inquiry_order').' b on b.quote_id=a.id left join '.tablename('website_user').' c on c.id=b.uid where b.id=:id',[':id'=>$id]);
            
            if($order['status']==3){
                show_json(-1,['msg'=>'接单失败，该询价单已被接单']);
            }
            
            pdo_update('website_quote_order',['status'=>3],['id'=>$order['id']]);
            pdo_update('website_inquiry_order',['status'=>3],['id'=>$order['inquiry_id']]);
            
            #通知O端
            $system = pdo_fetch('select * from '.tablename('centralize_system_notice').' where uid=0');
            if($system['notice_type']==1){
                #微信
                $post = json_encode([
                    'call'=>'confirmCollectionNotice',
                    'first' =>'商户['.$_SESSION['account']['custom_id'].']已接单，请打开查看！',
                    'keyword1' => '商户['.$_SESSION['account']['custom_id'].']已接单，请打开查看！',
                    'keyword2' => '已接单',
                    'keyword3' => date('Y-m-d H:i:s',$time),
                    'remark' => '点击查看详情',
                    'url' => 'https://shop.gogo198.cn/app/index.php?i=3&c=entry&do=checkprice&p=quote&m=sz_yi&op=quote_info&id='.$order['id'],
                    'openid' => $system['account'],
                    'temp_id' => 'SVVs5OeD3FfsGwW0PEfYlZWetjScIT8kDxht5tlI1V8'
                ]);

                httpRequest2('https://shop.gogo198.cn/api/sendwechattemplatenotice.php', $post);
            }
            else if($system['notice_type']==3){
                #邮箱通知
                httpRequest2('https://shop.gogo198.cn/collect_website/public/?s=/api/sendemail/index',['email'=>$system['account'],'title'=>'商户['.$_SESSION['account']['custom_id'].']已接单','content'=>'询价单['.$order['ordersn'].']已被该商户接单，请查看：https://shop.gogo198.cn/app/index.php?i=3&c=entry&do=checkprice&p=quote&m=sz_yi&op=update_quote_info&id='.$order['id']]);
            }
            
            #通知C端
            if(!empty($order['openid'])){
                $post = json_encode([
                    'call'=>'confirmCollectionNotice',
                    'first' =>'尊敬的客户，你的询价单['.$order['ordersn'].']已被接单，请打开查看！',
                    'keyword1' => '尊敬的客户，你的询价单['.$order['ordersn'].']已被接单，请打开查看！',
                    'keyword2' => '已接单',
                    'keyword3' => date('Y-m-d H:i:s',$time),
                    'remark' => '点击查看详情',
                    'url' => 'https://shop.gogo198.cn/app/index.php?i=3&c=entry&do=checkprice&p=index&m=sz_yi&op=quote_detail&quote_id='.$order['id'],
                    'openid' => $order['openid'],
                    'temp_id' => 'SVVs5OeD3FfsGwW0PEfYlZWetjScIT8kDxht5tlI1V8'
                ]);
    
                httpRequest2('https://shop.gogo198.cn/api/sendwechattemplatenotice.php', $post);
            }elseif(!empty($order['email'])){
                httpRequest2('https://shop.gogo198.cn/collect_website/public/?s=/api/sendemail/index',['email'=>$order['email'],'title'=>'已接单','content'=>'询价单号['.$order['ordersn'].']已接单，请进入询价中心查看询价单状态：https://www.gogo198.net/?s=index/account_manage']);
            }elseif(!empty($order['phone'])){
                $post_data = [
                    'spid'=>'254560',
                    'password'=>'J6Dtc4HO',
                    'ac'=>'1069254560',
                    'mobiles'=>$order['phone'],
                    'content'=>'询价单号['.$order['ordersn'].']已接单，请进入询价中心查看询价单状态：https://www.gogo198.net/?s=index/account_manage 【GOGO】',
                ];
                $post_data = json_encode($post_data,true);
                httpRequest2('https://decl.gogo198.cn/api/sendmsg_jumeng',$post_data,array(
                    'Content-Type: application/json; charset=utf-8',
                    'Content-Length:' . strlen($post_data),
                    'Cache-Control: no-cache',
                    'Pragma: no-cache'
                ));
            }
            
            show_json(0,['msg'=>'接单成功']);
        }
        else{
            #确认报价，通知O端
            $res = pdo_insert('website_quote_order',[
                'uid'=>$_SESSION['account']['id'],
                'inquiry_id'=>$id,
                'quote_formid'=>intval($data['template_id']),
                'content'=>json_encode($data['content'],true),
                'status'=>1,
                'createtime'=>$time
            ]);
            $insid = pdo_insertid();
            if($res){
                #通知O端
                $system = pdo_fetch('select * from '.tablename('centralize_system_notice').' where uid=0');
                
                if($system['notice_type']==1){
                    #微信
                    $post = json_encode([
                        'call'=>'confirmCollectionNotice',
                        'first' =>'商户['.$_SESSION['account']['custom_id'].']确认报价，请打开查看！',
                        'keyword1' => '商户['.$_SESSION['account']['custom_id'].']确认报价，请打开查看！',
                        'keyword2' => '已报价待确认',
                        'keyword3' => date('Y-m-d H:i:s',$time),
                        'remark' => '点击查看详情',
                        'url' => 'https://shop.gogo198.cn/app/index.php?i=3&c=entry&do=checkprice&p=quote&m=sz_yi&op=update_quote_info&id='.$insid,
                        'openid' => $system['account'],
                        'temp_id' => 'SVVs5OeD3FfsGwW0PEfYlZWetjScIT8kDxht5tlI1V8'
                    ]);
    
                    httpRequest2('https://shop.gogo198.cn/api/sendwechattemplatenotice.php', $post);
                }
                elseif($system['notice_type']==3){
                    #邮箱通知
                    httpRequest2('https://shop.gogo198.cn/collect_website/public/?s=/api/sendemail/index',['email'=>$system['account'],'title'=>'商户['.$_SESSION['account']['custom_id'].']确认报价','content'=>'请登录总后台，进入报价管理中心进行查看：https://gadmin.gogo198.cn/']);
                }
                show_json(0,['msg'=>'确认报价成功，请等待客户下单']);
            }   
        }
    }
    else{
        $order = pdo_fetch('select a.*,b.content as form from '.tablename('website_inquiry_order').' a left join '.tablename('website_inquiry_form').' b on b.pid=a.buss_id left join '.tablename('website_quote_order').' c on c.inquiry_id=a.id where c.id=:id',[':id'=>$id]);
        if(empty($order)){
            #c端
            $order = pdo_fetch('select a.*,b.content as form from '.tablename('website_inquiry_order').' a left join '.tablename('website_inquiry_form').' b on b.pid=a.buss_id where a.id=:id',[':id'=>$id]);    
        }

        $order['content'] = json_decode($order['content'],true);
        if(!empty($order['files'])){
            $order['files'] = json_decode($order['files'],true);
        }
        $order['form'] = json_decode($order['form'],true);
        
        #此询价业务的报价表单
        $quote_temp = pdo_fetchall('select * from '.tablename('website_quote_form').' where pid=:pid',[':pid'=>$order['buss_id']]);
        #报价记录
        $quote_info = pdo_fetch('select a.* from '.tablename('website_quote_order').' a left join '.tablename('website_user').' b on b.id=a.uid where b.openid=:openid and a.inquiry_id=:id',[':openid'=>$openid,':id'=>$id]);
        if(empty($quote_info)){
            $quote_info = pdo_fetch('select a.* from '.tablename('website_quote_order').' a left join '.tablename('website_user').' b on b.id=a.uid where b.openid=:openid and a.id=:id',[':openid'=>$openid,':id'=>$id]);
        }
        if(!empty($quote_info)){
            #查询报价模板
            $quote_info['form'] = pdo_fetchcolumn('select content from '.tablename('website_quote_form').' where id=:id',[':id'=>$quote_info['quote_formid']]);
            $quote_info['form'] = json_decode($quote_info['form'],true);
            #已输入内容
            $quote_info['content'] = json_decode($quote_info['content'],true);
        }
        $jssdkconfig = $_W['account']['jssdkconfig'];
        include $this->template('checkprice/quote_info');   
    }
}
elseif($op=='save_template'){
    #保存报价模板
    // dd($_SESSION['account']);
    $buss_id = isset($data['buss_id'])?intval($data['buss_id']):0;
    if($_W['isajax']){
        if(empty($_SESSION['account']['id'])){
            show_json(-1,['msg'=>'请先登录']);
        }
        
        $content = [];
        $label_name = $data['content']['label_name'];
        foreach($label_name as $k=>$v){
            array_push($content,[
                'label_name'=>$v,
                'label_value'=>$data['content']['label_value'][$k],#元素id
                'label_select'=>$data['content']['label_select'][$k],#选择框
                'label_rand'=>$data['content']['label_rand'][$k],#时间框
            ]);
        }
        
        $res = pdo_insert('website_quote_form',[
            'uid'=>$_SESSION['account']['id'],
            'pid'=>$buss_id,
            'content'=>json_encode($content,true),
        ]);
        
        if($res){
            show_json(0,['msg'=>'添加成功']);
        }
    }else{
        include $this->template('checkprice/save_template');
    }
}
elseif($op=='select_template'){
    #选择模板
    
    $info = pdo_fetch('select * from '.tablename('website_quote_form').' where id=:id',[':id'=>$data['id']]);
    $info['content'] = json_decode($info['content'],true);
    foreach($info['content'] as $k=>$v){
        if($v['label_value']==4){
            $info['content'][$k]['label_select'] = explode('、', $info['content'][$k]['label_select']);
        }
    }
    show_json(0,['content'=>$info['content']]);
}
elseif($op=='merchant_reg'){
    #商户认证
    if($_W['isajax']){
        #查询是否验证成功
        $is_merch = pdo_fetch('select * from '.tablename('website_user').' where id=:id',[':id'=>$_SESSION['account']['id']]);
        if($is_merch['merch_status']>=1){
            $_SESSION['account']['merch_status'] = $is_merch;
            show_json(1,['msg'=>'您已提交商户认证，请等待客服人员审批，感谢支持！']);
        }
        if($data['reg_method']==2){
            $files = [];
            foreach($data['filename'] as $k=>$v){
                if(empty($v)){
                    show_json(-1,['msg'=>'请输入文件名称']);
                }else{
                    $files = array_merge($files,[['files'=>$dat['file'][$k],'filenames'=>trim($v)]]);
                }
            }
            $files = json_encode($files,true);
            $res = pdo_update('website_user',['reg_file'=>$files,'merch_status'=>1,'reg_method'=>2],['id'=>$_SESSION['account']['id']]);
            $_SESSION['account']['merch_status'] = 1;

            #通知O端
            $system = pdo_fetch('select * from '.tablename('centralize_system_notice').' where uid=0');
            if($system['notice_type']==1){
                #微信
                $post = json_encode([
                    'call'=>'confirmCollectionNotice',
                    'first' =>'商户['.$_SESSION['account']['custom_id'].']提交境外商户认证！',
                    'keyword1' => '商户['.$_SESSION['account']['custom_id'].']提交境外商户认证！',
                    'keyword2' => '待审核',
                    'keyword3' => date('Y-m-d H:i:s',$time),
                    'remark' => '点击查看详情',
                    'url' => 'https://gadmin.gogo198.cn/',
                    'openid' => $system['account'],
                    'temp_id' => 'SVVs5OeD3FfsGwW0PEfYlZWetjScIT8kDxht5tlI1V8'
                ]);

                httpRequest2('https://shop.gogo198.cn/api/sendwechattemplatenotice.php', $post);
            }
            elseif($system['notice_type']==3){
                #邮箱通知
                httpRequest2('https://shop.gogo198.cn/collect_website/public/?s=/api/sendemail/index',['email'=>$system['account'],'title'=>'商户['.$_SESSION['account']['custom_id'].']提交境外商户认证','content'=>'请登录总后台，进入账户管理进行审批：https://gadmin.gogo198.cn/']);
            }

            show_json(0,['msg'=>'提交成功，请等待客服人员审批，感谢支持！']);
        }else{
             if($_SESSION['verify_code']!=trim($data['code'])){
                 show_json(-1,['msg'=>'验证码不正确！']);
             }

            $res = pdo_update('website_user',[
                'company'=>trim($data['company']),
                'realname'=>trim($data['realname']),
                'idcard'=>trim($data['idcard']),
                'phone'=>isset($data['phone'])?trim($data['phone']):$_SESSION['account']['phone'],
                'email'=>isset($data['email'])?trim($data['email']):$_SESSION['account']['email'],
                'merch_status'=>1,
                'reg_method'=>1
            ],['id'=>$_SESSION['account']['id']]);

            show_json(0,['data'=>[$_SESSION['account']['phone'],trim($data['realname']),trim($data['idcard'])]]);
        }
    }else{
        $account = pdo_fetch('select * from '.tablename('website_user').' where id=:id',[':id'=>$_SESSION['account']['id']]);
        $_SESSION['account'] = $account;
        $open = isset($data['open'])?$data['open']:0;
        include $this->template('checkprice/merchant_reg');
    }
}
#O端修改报价
elseif($op=='update_quote_info'){
    $quote_id = intval($data['id']);
    if($_W['isajax']){
        $res = pdo_update('website_quote_order',[
            'content'=>isset($data['content'])?json_encode($data['content'],true):'',
            'text'=>isset($data['text'])?$data['text']:'',
            ],['id'=>$quote_id]);
        if($res){
            $user = pdo_fetch('select c.email,c.phone,c.openid,b.ordersn,a.inquiry_id,a.id as quote_id from '.tablename('website_quote_order').' a left join '.tablename('website_inquiry_order').' b on b.id=a.inquiry_id left join '.tablename('website_user').' c on c.id=b.uid where a.id=:id',[':id'=>$quote_id]);
            if(!empty($user['openid'])){
                $post = json_encode([
                    'call'=>'confirmCollectionNotice',
                    'first' =>'尊敬的客户，您的询价单['.$user['ordersn'].']已有报价，请打开查看！',
                    'keyword1' => '尊敬的客户，您的询价单['.$user['ordersn'].']已有报价，请打开查看！',
                    'keyword2' => '已报价待下单',
                    'keyword3' => date('Y-m-d H:i:s',$time),
                    'remark' => '点击查看详情',
                    'url' => 'https://shop.gogo198.cn/app/index.php?i=3&c=entry&do=checkprice&p=index&m=sz_yi&op=quote_detail&quote_id='.$user['quote_id'],
                    'openid' => $user['openid'],
                    'temp_id' => 'SVVs5OeD3FfsGwW0PEfYlZWetjScIT8kDxht5tlI1V8'
                ]);
    
                $res = httpRequest2('https://shop.gogo198.cn/api/sendwechattemplatenotice.php', $post);
            }
            elseif(!empty($user['email'])){
                $res = httpRequest2('https://shop.gogo198.cn/collect_website/public/?s=/api/sendemail/index',['email'=>$user['email'],'title'=>'报价信息','content'=>'尊敬的客户，您的询价单['.$user['ordersn'].']有新的报价，请点击链接进入账户管理下的询价中心查看报价详情：https://www.gogo198.net/?s=index/account_manage']);
            }
            elseif(!empty($user['phone'])){
                $post_data = [
                    'spid'=>'254560',
                    'password'=>'J6Dtc4HO',
                    'ac'=>'1069254560',
                    'mobiles'=>$user['phone'],
                    'content'=>'尊敬的客户，您的询价单['.$user['ordersn'].']有新的报价，请点击链接进入账户管理下的询价中心查看报价详情：https://www.gogo198.net/?s=index/account_manage 【GOGO】',
                ];
                $post_data = json_encode($post_data,true);
                $res = httpRequest2('https://decl.gogo198.cn/api/sendmsg_jumeng',$post_data,array(
                    'Content-Type: application/json; charset=utf-8',
                    'Content-Length:' . strlen($post_data),
                    'Cache-Control: no-cache',
                    'Pragma: no-cache'
                ));
            }
            pdo_update('website_quote_order',['is_notice'=>1],['id'=>$quote_id]);
            pdo_update('website_inquiry_order',['status'=>1],['ordersn'=>$user['ordersn']]);
            show_json(0,['msg'=>'修改成功，已通知C端！']);
        }
    }else{
        $order = pdo_fetch('select a.*,b.content as form from '.tablename('website_inquiry_order').' a left join '.tablename('website_inquiry_form').' b on b.pid=a.buss_id left join '.tablename('website_quote_order').' c on c.inquiry_id=a.id where c.id=:id',[':id'=>$quote_id]);
        if(!empty($order['content'])){
            $order['content'] = json_decode($order['content'],true);
        }
        if(!empty($order['files'])){
            $order['files'] = json_decode($order['files'],true);
        }
        $order['form'] = json_decode($order['form'],true);

        #此询价业务的报价表单
        $quote_temp = pdo_fetchall('select * from '.tablename('website_quote_form').' where pid=:pid',[':pid'=>$order['buss_id']]);
        #报价记录
        $quote_info = pdo_fetch('select a.* from '.tablename('website_quote_order').' a left join '.tablename('website_user').' b on b.id=a.uid where a.id=:id',[':id'=>$quote_id]);
        if(!empty($quote_info)){
            #查询报价模板
            $quote_info['form'] = pdo_fetchcolumn('select content from '.tablename('website_quote_form').' where id=:id',[':id'=>$quote_info['quote_formid']]);
            $quote_info['form'] = json_decode($quote_info['form'],true);
            if(!empty($quote_info['content'])) {
                #已输入内容
                $quote_info['content'] = json_decode($quote_info['content'], true);
            }
        }
        include $this->template('checkprice/update_quote_info');
    }
}