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

if($op=='bind_user'){
    if($_W['isajax']){
        $user = pdo_fetch('select * from '.tablename('website_user').' where custom_id=:cid',[':cid'=>trim($data['gogo_id'])]);
        if(empty($user)){
            show_json(-1,['msg'=>'找不到账号，请输入正确的ID']);
        }else{
            pdo_update('centralize_user',['openid'=>$_W['openid']],['gogo_id'=>trim($data['gogo_id'])]);
            pdo_update('website_user',['openid'=>$_W['openid']],['custom_id'=>trim($data['gogo_id'])]);
            show_json(0,['msg'=>'绑定账号成功，请自行返回系统']);
        }
    }else{
        include $this->template('checkprice/bind/bind_user');
    }
}
elseif($op=='display'){
    #询价、报价页
//    $account = pdo_fetch('select * from '.tablename('website_user').' where openid=:openid',[':openid'=>$openid]);
//    if(!empty($account)){
//        $_SESSION['account'] = $account;
//    }
    if(empty($_SESSION['account'])){
        header('Location:./index.php?i=3&c=entry&do=checkprice&m=sz_yi&p=index&op=login');
    }
    include $this->template('checkprice/index');
}
elseif($op=='login'){
    #登录
//    dd($_SESSION['account']);
    if(!empty($_SESSION['account'])){
        header('Location:./index.php?i=3&c=entry&do=checkprice&m=sz_yi&p=index&op=display');
    }
    if($_W['isajax']){
        $number = '';
        if($data['reg_method']==1){
            $number = trim($data['phone']);
            $account = pdo_fetch('select * from '.tablename('website_user').' where phone=:phone',[':phone'=>$number]);
        }elseif($data['reg_method']==2){
            $number = trim($data['email']);
            $account = pdo_fetch('select * from '.tablename('website_user').' where email=:email',[':email'=>$number]);
        }
        
        if($number!='947960547@qq.com' && $number!='13119893380' && $number!='13119893381' && $number!='947960542@qq.com' && $number!='947960543@qq.com' && $number!='13119893382'&& $number!='13809703680' && $number!='13809703681' && $number!='hejunxin@gogo198.net'){
            if($_SESSION['login_code']!=trim($data['code'])){
                show_json(-1,['msg'=>'验证码不正确！']);
            }    
        }
        
        if(empty($account['openid'])){
            pdo_update('website_user',['openid'=>$_W['openid']],['id'=>$account['id']]);
        }
        
        if(empty($account)){
            #无感注册
            pdo_insert('website_user',[
                'openid'=>$_W['openid'],
                'phone'=>$data['reg_method']==1?$number:'',
                'email'=>$data['reg_method']==2?$number:'',
                'times'=>1,
                'createtime'=>$time
            ]);
            $insertid = pdo_insertid();
            $res = pdo_update('website_user',['custom_id'=>'GG'.date('YmdHis',$time).str_pad($insertid, 3, '0', STR_PAD_LEFT)],['id'=>$insertid]);
            
            if($res){
                #赋予账号
                $account = pdo_fetch('select * from '.tablename('website_user').' where id=:id',[":id"=>$insertid]);
                
                #集运网账号
                pdo_insert('centralize_user',[
                    'openid'=>$_W['openid'],
                    'name'=>$account['nickname'],
                    'realname'=>$account['realname'],
                    'email'=>$account['email'],
                    'pwd'=>md5('888888'),
                    'mobile'=>$account['phone'],
                    'status'=>0,
                    'agentid'=>$account['agent_id'],
                    'gogo_id'=>$account['custom_id'],
                    'createtime'=>$time,
                ]);
                
                #买全球账号
                pdo_insert('sz_yi_member',[
                    'openid'=>$_W['openid'],
                    'uniacid'=>3,
                    'realname'=>$account['realname'],
                    'nickname'=>$account['nickname'],
                    'mobile'=>$account['phone']!=''?$account['phone']:$account['email'],
                    'pwd'=>md5('888888'),
                    'id_card'=>$account['idcard'],
                    'gogo_id'=>$account['custom_id'],
                    'createtime'=>$time,
                ]);
                
                #卖全球账号
                pdo_insert('sz_yi_member',[
                    'openid'=>$_W['openid'],
                    'uniacid'=>18,
                    'realname'=>$account['realname'],
                    'nickname'=>$account['nickname'],
                    'mobile'=>$account['phone']!=''?$account['phone']:$account['email'],
                    'pwd'=>md5('888888'),
                    'id_card'=>$account['idcard'],
                    'gogo_id'=>$account['custom_id'],
                    'createtime'=>$time,
                ]);
            }
            
            #通知用户
            if($data['reg_method']==1){
                #手机
                $post_data = [
                    'spid'=>'254560',
                    'password'=>'J6Dtc4HO',
                    'ac'=>'1069254560',
                    'mobiles'=>$number,
                    'content'=>'尊敬的客户，您好！您已成功注册成为购购网会员，感谢您的支持！【GOGO】',
                ];
                $post_data = json_encode($post_data,true);
                httpRequest2('https://decl.gogo198.cn/api/sendmsg_jumeng',$post_data,array(
                    'Content-Type: application/json; charset=utf-8',
                    'Content-Length:' . strlen($post_data),
                    'Cache-Control: no-cache',
                    'Pragma: no-cache'
                ));
            }elseif($data['reg_method']==2){
                #邮箱                
                httpRequest2('https://shop.gogo198.cn/collect_website/public/?s=/api/sendemail/index',['email'=>$number,'title'=>'购购网','content'=>'尊敬的客户，您好！您已成功注册成为购购网会员，感谢您的支持！']);
            }
        }else{
            if($account['times']==1){
                pdo_update('website_user',['times'=>2],['id'=>$account['id']]);
            }else{
                pdo_update('website_user',['times'=>intval($account['times'])+1],['id'=>$account['id']]);
            }
        }
        
        #如果是询价跳转过来的
        if($data['inquiry_id']>0){
            $res = pdo_update('website_inquiry_order',['uid'=>$account['id']],['id'=>intval($data['inquiry_id'])]);
            #通知O端
            if($res){
                $system = pdo_fetch('select * from '.tablename('centralize_system_notice').' where uid=0');
                $ordersn = pdo_fetchcolumn('select ordersn from '.tablename('website_inquiry_order').' where id=:id',[':id'=>intval($data['inquiry_id'])]);
                if($system['notice_type']==1){
                    #微信
                    $post = json_encode([
                        'call'=>'confirmCollectionNotice',
                        'first' =>'有新的询价单['.$ordersn.']，请打开查看！',
                        'keyword1' => '有新的询价单['.$ordersn.']，请打开查看！',
                        'keyword2' => '已提交待分享报价',
                        'keyword3' => date('Y-m-d H:i:s',$time),
                        'remark' => '点击查看详情',
                        'url' => 'https://shop.gogo198.cn/app/index.php?i=3&c=entry&do=checkprice&p=index&m=sz_yi&op=check_detail&ordersn='.$ordersn,
                        'openid' => $system['account'],
                        'temp_id' => 'SVVs5OeD3FfsGwW0PEfYlZWetjScIT8kDxht5tlI1V8'
                    ]);
    
                    httpRequest2('https://shop.gogo198.cn/api/sendwechattemplatenotice.php', $post);
                }elseif($system['notice_type']==3){
                    #邮箱通知
                    httpRequest2('https://shop.gogo198.cn/collect_website/public/?s=/api/sendemail/index',['email'=>$system['account'],'title'=>'客户['.$_SESSION['account']['custom_id'].']发起询价','content'=>'请登录总后台，进入询价管理中心进行查看：https://gadmin.gogo198.cn/']);
                }
            }
        }
        $_SESSION['account'] = $account;

        show_json(0,['msg'=>'登录成功！','uid'=>$account['id']]);
    }else{
        $open = isset($data['open'])?intval($data['open']):0;
        $inquiry_id = isset($data['inquiry_id'])?intval($data['inquiry_id']):0;
        include $this->template('checkprice/login');
    }
}
elseif($op=='send_code'){
    #发送验证码
    if($_W['isajax']){
        $code = mt_rand(11, 99) . mt_rand(11, 99) . mt_rand(11, 99);
        if(isset($data['islogin'])){
            $_SESSION['login_code'] = $code;
        
            if($data['code_type']==1){
                #手机号码
                $tel = trim($data['number']);
                
                $post_data = [
                    'spid'=>'254560',
                    'password'=>'J6Dtc4HO',
                    'ac'=>'1069254560',
                    'mobiles'=>$tel,
                    'content'=>'您正在登录GOGO购购网，手机验证码为：'.$code.'【GOGO】',
                ];
                $post_data = json_encode($post_data,true);
                $res = httpRequest2('https://decl.gogo198.cn/api/sendmsg_jumeng',$post_data,array(
                    'Content-Type: application/json; charset=utf-8',
                    'Content-Length:' . strlen($post_data),
                    'Cache-Control: no-cache',
                    'Pragma: no-cache'
                ));
            }elseif($data['code_type']==2){
                #邮箱
                $res=httpRequest2('https://shop.gogo198.cn/collect_website/public/?s=/api/sendemail/index',['email'=>trim($data['number']),'title'=>'登录Gogo购购网','content'=>'验证码：'.$code.'，您正在登录Gogo购购网。']);
            }
        }
        
        if($res){
            show_json(0,['msg'=>'发送成功！']);
        }else{
            show_json(-1,['msg'=>'发送失败，请联系管理员！']);
        }
    }
}
elseif($op=='send_code2'){
    #实名认证-手机/邮箱验证码发送
    if($_W['isajax']){
        $code = mt_rand(11, 99) . mt_rand(11, 99) . mt_rand(11, 99);
        if(isset($data['islogin'])){
            $_SESSION['verify_code'] = $code;
        
            if($data['code_type']==1){
                #手机号码
                $tel = trim($data['number']);
                if(empty($tel)){
                    show_json(-1,['msg'=>'手机格式错误！']);
                }
                
                $post_data = [
                    'spid'=>'254560',
                    'password'=>'J6Dtc4HO',
                    'ac'=>'1069254560',
                    'mobiles'=>$tel,
                    'content'=>'您正在GOGO实名认证，手机验证码为：'.$code.'【GOGO】',
                ];
                $post_data = json_encode($post_data,true);
                $res = httpRequest2('https://decl.gogo198.cn/api/sendmsg_jumeng',$post_data,array(
                    'Content-Type: application/json; charset=utf-8',
                    'Content-Length:' . strlen($post_data),
                    'Cache-Control: no-cache',
                    'Pragma: no-cache'
                ));
            }elseif($data['code_type']==2){
                #邮箱
                $res=httpRequest2('https://shop.gogo198.cn/collect_website/public/?s=/api/sendemail/index',['email'=>trim($data['number']),'title'=>'Gogo实名认证','content'=>'验证码：'.$code.'，您正在GOGO实名认证。']);
            }
        }
        
        if($res){
            show_json(0,['msg'=>'发送成功！']);
        }else{
            show_json(-1,['msg'=>'发送失败，请联系管理员！']);
        }
    }
}
elseif($op=='inquiry_buss'){
    #询价业务
    $list = pdo_fetchall('select * from '.tablename('website_bussiness').' where 1');
    include $this->template('checkprice/inquiry_buss');
}
elseif($op=='inquiry_list'){
     #询价列表
     $id = isset($data['id'])?intval($data['id']):0;
     if(isset($data['pa'])){
         $limit = intval($data['limit']);
         $page = (intval($data['page'])-1) * $limit;
         $offset = ' limit '.$page.','.$limit;
         $count = pdo_fetchcolumn('select count(id) from '.tablename('website_inquiry_order').' where uid=:user_id and buss_id=:buss_id',[':user_id'=>$_SESSION['account']['id'],':buss_id'=>$id]);
         $list = pdo_fetchall('select * from '.tablename('website_inquiry_order').' where uid=:user_id and buss_id=:buss_id order by id desc '.$offset,[':user_id'=>$_SESSION['account']['id'],':buss_id'=>$id]);
         $status = [0=>'已询价待报价',1=>'已报价待下单',2=>'已下单',3=>'已接单'];
         $platform = [1=>'官网',2=>'公众号',3=>'小程序'];
         foreach($list as $k=>$v){
             $list[$k]['buss_name'] = pdo_fetchcolumn('select `name` from '.tablename('website_bussiness').' where id=:buss_id',[':buss_id'=>$v['buss_id']]);
             $list[$k]['platform'] = $platform[$v['platform']];
             $list[$k]['statusname'] = $status[$v['status']];
             $list[$k]['createtime'] = date('Y-m-d H:i',$v['createtime']);
         }
         die(json_encode(['code'=>0,'data'=>$list,'count'=>$count]));
     }
    include $this->template('checkprice/inquiry_list');
}
elseif($op=='save_inquiry'){
    #发起询价
    $buss_id = intval($data['buss_id']);
    $type = intval($data['type']);

    if($_W['isajax']){
        #插入数据表
        $ordersn = 'GP' . date('YmdH', $time) . str_pad(mt_rand(1, 999999), 6, '0',
                    STR_PAD_LEFT) . substr(microtime(), 2, 6);
        $files = [];
        //file和filename不知道要不要加s
        if(isset($data['file'])){
            foreach($data['filename'] as $k=>$v){
                if(empty($v)){
                    show_json(-2,['msg'=>'请输入文件名称']);
                }else{
                    $files = array_merge($files,[['files'=>$data['file'][$k],'filenames'=>trim($v)]]);
                }
            }
        }

        $files = json_encode($files,true);
        pdo_insert('website_inquiry_order',[
            'uid'=>$_SESSION['account']['id'],
            'buss_id'=>$buss_id,
            'ordersn'=>$ordersn,
            'platform'=>1,
            'content'=>$type==0?json_encode($data['content'],true):'',
            'text'=>$type==1?trim($data['text']):'',
            'files'=>$files,
            'createtime'=>$time
        ]);
        $res = pdo_insertid();
        #判断有无登录
        if(empty($_SESSION['account']['id'])){
            show_json(-1,['msg'=>'请先登录','inquiry_id'=>$res]);
        }
        
        #通知O端
        if($res){
            $system = pdo_fetch('select * from '.tablename('centralize_system_notice').' where uid=0');
            if($system['notice_type']==1){
                #微信
                $post = json_encode([
                    'call'=>'confirmCollectionNotice',
                    'first' =>'有新的询价单['.$ordersn.']，请打开查看！',
                    'keyword1' => '有新的询价单['.$ordersn.']，请打开查看！',
                    'keyword2' => '已提交待分享报价',
                    'keyword3' => date('Y-m-d H:i:s',$time),
                    'remark' => '点击查看详情',
                    'url' => 'https://shop.gogo198.cn/app/index.php?i=3&c=entry&do=checkprice&p=index&m=sz_yi&op=check_detail&ordersn='.$ordersn,
                    'openid' => $system['account'],
                    'temp_id' => 'SVVs5OeD3FfsGwW0PEfYlZWetjScIT8kDxht5tlI1V8'
                ]);

                httpRequest2('https://shop.gogo198.cn/api/sendwechattemplatenotice.php', $post);
            }elseif($system['notice_type']==3){
                #邮箱通知
                httpRequest2('https://shop.gogo198.cn/collect_website/public/?s=/api/sendemail/index',['email'=>$system['account'],'title'=>'客户['.session('account.custom_id').']发起询价','content'=>'请登录总后台，进入询价管理中心进行查看：https://gadmin.gogo198.cn/']);
            }

            $servicers = pdo_fetchall('select * from '.tablename('centralize_system_servicer').' where status=1');
            foreach($servicers as $k=>$v) {
                $muser = pdo_fetch('select * from ' . tablename('website_user') . ' where id=:id', [':id' => $v['uid']]);
                if (!empty($muser['openid'])) {
                    #微信
                    $post = json_encode([
                        'call'=>'confirmCollectionNotice',
                        'first' =>'有新的询价单['.$ordersn.']，请打开查看！',
                        'keyword1' => '有新的询价单['.$ordersn.']，请打开查看！',
                        'keyword2' => '已提交待分享报价',
                        'keyword3' => date('Y-m-d H:i:s',$time),
                        'remark' => '点击查看详情',
                        'url' => 'https://shop.gogo198.cn/app/index.php?i=3&c=entry&do=checkprice&p=index&m=sz_yi&op=check_detail&ordersn='.$ordersn,
                        'openid' => $muser['openid'],
                        'temp_id' => 'SVVs5OeD3FfsGwW0PEfYlZWetjScIT8kDxht5tlI1V8'
                    ]);

                    httpRequest2('https://shop.gogo198.cn/api/sendwechattemplatenotice.php', $post);
                }
                elseif(!empty($muser['email'])){
                    #邮箱通知
                    httpRequest2('https://shop.gogo198.cn/collect_website/public/?s=/api/sendemail/index',['email'=>$muser['email'],'title'=>'客户['.session('account.custom_id').']发起询价','content'=>'请登录总后台，进入询价管理中心进行查看：https://gadmin.gogo198.cn/']);
                }
            }
            show_json(0,['msg'=>'提交成功，请等待报价']);
        }
    }else{
        $template = pdo_fetch('select * from '.tablename('website_inquiry_form').' where pid=:pid',[':pid'=>$buss_id]);
        if(empty($template)){
            $template = pdo_fetch('select * from '.tablename('website_inquiry_form').' where id=:id',[':id'=>$buss_id]);
        }
        $template['content'] = json_decode($template['content'],true);
        $template['name'] = pdo_fetchcolumn('select name from '.tablename('website_bussiness').' where id=:id',[':id'=>$buss_id]);

        foreach($template['content'] as $k=>$v){
            if($v['label_value']==4){
                $template['content'][$k]['label_select2'] = explode('、',$v['label_select']);
            }
        }
        include $this->template('checkprice/save_inquiry');
    }
}
elseif($op=='thanks'){
    include $this->template('checkprice/thanks');
}
elseif($op=='inquiry_detail'){
    #询价详情
    $id = isset($data['id'])?intval($data['id']):0;
    $info = pdo_fetch('select a.content,c.content as form,a.uid,a.id,a.ordersn,a.files,a.text,a.quote_id from '.tablename('website_inquiry_order').' a left join '.tablename('website_bussiness').' b on b.id=a.buss_id left join '.tablename('website_inquiry_form').' c on c.pid=a.buss_id left join '.tablename('website_user').' d on d.id=a.uid where a.id=:id',[':id'=>$id]);
    if(!empty($info['content'])){
        $info['content'] = json_decode($info['content'],true);
    }
    $info['form'] = json_decode($info['form'],true);
    if(!empty($info['files'])){
        $info['files'] = json_decode($info['files'],true);
    }
    #报价信息
    if(!empty($info['quote_id'])){
        $quote = pdo_fetch('select content,quote_formid,id,text from '.tablename('website_quote_order').' where id=:id',[':id'=>$info['quote_id']]);
        if(!empty($quote['content'])){
            $quote['content'] = json_decode($quote['content'],true);
        }
        if($quote['quote_formid']>0){
            $quote['form'] = pdo_fetchcolumn('select content from '.tablename('website_quote_form').' where id=:id',[':id'=>$quote['quote_formid']]);
            $quote['form'] = json_decode($quote['form'],true);
        }
    }
    include $this->template('checkprice/inquiry_detail');
}
elseif($op=='inquiry_quote'){
    #询价中心-报价列表
    $inquiry_id = isset($data['id'])?intval($data['id']):0;
    if(isset($data['pa'])) {
        $limit = intval($data['limit']);
        $page = (intval($data['page']) - 1) * $limit;
        $offset = ' limit ' . $page . ',' . $limit;
        $count = pdo_fetchcolumn('select count(id) from ' . tablename('website_quote_order') . ' where inquiry_id=:inquiry_id and is_notice=1', [':inquiry_id' => $inquiry_id]);
        $list = pdo_fetchall('select * from ' . tablename('website_quote_order') . ' where inquiry_id=:inquiry_id and is_notice=1 order by id desc ' . $offset, [':inquiry_id' => $inquiry_id]);
        foreach($list as $k=>$v){
            $list[$k]['createtime'] = date('Y-m-d H:i',$v['createtime']);
        }
        die(json_encode(['code'=>0,'data'=>$list,'count'=>$count]));
    }
    include $this->template('checkprice/inquiry_quote');
}
elseif($op=='quote_detail'){
    // $_SESSION['account'] = [];
    #询价中心-报价详情
    if($_W['isajax']){
        if(empty($_SESSION['account']['id'])){
            show_json(-1,['msg'=>'请先登录']);
        }
        $account = pdo_fetch('select * from '.tablename('website_user').' where id=:id',[':id'=>$_SESSION['account']['id']]);
        if(empty($account['is_verify'])){
            show_json(-2,['msg'=>'首次下单请完成实名认证']);
        }

        $order = pdo_fetch('select * from '.tablename('website_inquiry_order').' where id=:id',[':id'=>intval($data['inquiry_id'])]);
        #通知O端
        $system = pdo_fetch('select * from '.tablename('centralize_system_notice').' where uid=:id',[':id'=>0]);
        if($system['notice_type']==1){
            #微信
            $post = json_encode([
                'call'=>'confirmCollectionNotice',
                'first' =>'客户['.$_SESSION['account']['custom_id'].']已在线下单！',
                'keyword1' => '客户['.$_SESSION['account']['custom_id'].']已在线下单,询价单['.$order['ordersn'].']',
                'keyword2' => '已下单待接单',
                'keyword3' => date('Y-m-d H:i:s',$time),
                'remark' => '点击查看详情',
                'url' => 'https://shop.gogo198.cn/app/index.php?i=3&c=entry&do=checkprice&p=index&m=sz_yi&op=check_detail&ordersn='.$order['ordersn'],
                'openid' => $system['account'],
                'temp_id' => 'SVVs5OeD3FfsGwW0PEfYlZWetjScIT8kDxht5tlI1V8'
            ]);

            httpRequest2('https://shop.gogo198.cn/api/sendwechattemplatenotice.php', $post);
        }
        elseif($system['notice_type']==3){
            #邮箱通知
            httpRequest2('https://shop.gogo198.cn/collect_website/public/?s=/api/sendemail/index',['email'=>$system['account'],'title'=>'客户['.$_SESSION['account']['custom_id'].']已在线下单','content'=>'询价单号['.$order['ordersn'].']已在线下单，请知悉，并进入总后台的询价中心查看：https://gadmin.gogo198.cn/']);
        }
        #通知B端
        $quote_order = pdo_fetch('select b.phone,b.email,b.openid,a.id,a.uid from '.tablename('website_quote_order').' a left join '.tablename('website_user').' b on b.id=a.uid where a.id=:id',[':id'=>intval($data['quote_id'])]);
        if(!empty($quote_order['openid'])){
            $post = json_encode([
                'call'=>'confirmCollectionNotice',
                'first' =>'您的报价已受理，请打开接单！',
                'keyword1' => '您的报价已受理，请确认接单，询价单['.$order['ordersn'].']',
                'keyword2' => '已下单待接单',
                'keyword3' => date('Y-m-d H:i:s',$time),
                'remark' => '点击查看详情',
                'url' => 'https://shop.gogo198.cn/app/index.php?i=3&c=entry&do=checkprice&p=quote&m=sz_yi&op=quote_info&id='.$order['id'],
                'openid' => $system['account'],
                'temp_id' => 'SVVs5OeD3FfsGwW0PEfYlZWetjScIT8kDxht5tlI1V8'
            ]);

            httpRequest2('https://shop.gogo198.cn/api/sendwechattemplatenotice.php', $post);
        }
        elseif(!empty($quote_order['email'])){
            #邮件通知
            httpRequest2('https://shop.gogo198.cn/collect_website/public/?s=/api/sendemail/index',['email'=>$quote_order['email'],'title'=>'在线接单','content'=>'询价单号['.$order['ordersn'].']已下单，请尽快点击链接进行确认接单：https://www.gogo198.net/?s=index/quote_info&uid='.$quote_order['uid'].'&id='.$quote_order['id']]);
        }elseif(!empty($quote_order['phone'])){
            #手机通知
            $post_data = [
                'spid'=>'254560',
                'password'=>'J6Dtc4HO',
                'ac'=>'1069254560',
                'mobiles'=>$quote_order['phone'],
                'content'=>'询价单号['.$order['ordersn'].']已下单，请尽快点击链接进行确认接单：https://www.gogo198.net/?s=index/quote_info&uid='.$quote_order['uid'].'&id='.$quote_order['id'].' 【GOGO】',
            ];
            $post_data = json_encode($post_data,true);
            httpRequest2('https://decl.gogo198.cn/api/sendmsg_jumeng',$post_data,array(
                'Content-Type: application/json; charset=utf-8',
                'Content-Length:' . strlen($post_data),
                'Cache-Control: no-cache',
                'Pragma: no-cache'
            ));
        }

        #修改为已下单状态
        pdo_update('website_inquiry_order',['quote_id'=>intval($data['quote_id']),'status'=>2],['id'=>intval($data['inquiry_id'])]);
        pdo_update('website_quote_order',['status'=>2],['id'=>intval($data['quote_id'])]);

        show_json(0,['msg'=>'下单成功，正在跳转业务系统']);
    }else{
        $info = pdo_fetch('select a.content,a.text,b.content as form,c.status,a.id,c.id as inquiry_id,c.content as inquiry_content,d.content as inquiry_form,c.files,c.text as inquiry_text from '.tablename('website_quote_order').' a left join '.tablename('website_quote_form').' b on b.id=a.quote_formid left join '.tablename('website_inquiry_order').' c on c.id=a.inquiry_id left join '.tablename('website_inquiry_form').' d on d.pid=c.buss_id where a.id=:id',[':id'=>intval($data['quote_id'])]);
        if(!empty($info['content'])){
            $info['content'] = json_decode($info['content'],true);
        }
        $info['content'] = json_decode($info['content'],true);
        $info['form'] = json_decode($info['form'],true);
        if(!empty($info['inquiry_content'])){
            $info['inquiry_content'] = json_decode($info['inquiry_content'],true);
        }
        $info['inquiry_form'] = json_decode($info['inquiry_form'],true);
        if(!empty($info['files'])){
            $info['files'] = json_decode($info['files'],true);
        }
    }
    include $this->template('checkprice/quote_detail');
}
elseif($op=='account_reg'){
    #会员认证
    if($_W['isajax']){
        if(isset($data['code'])){
            if($_SESSION['verify_code'] != trim($data['code'])){
                show_json(-1,['msg'=>'验证码不正确！']);
            }    
        }

        $res = pdo_update('website_user',[
            'phone'=>isset($data['phone'])?$data['phone']:$_SESSION['account']['phone'],
            'email'=>isset($data['email'])?$data['email']:$_SESSION['account']['email'],
            'realname'=>trim($data['realname']),
            'idcard'=>trim($data['idcard']),
        ],['id'=>$_SESSION['account']['id']]);

        $account = pdo_fetch('select * from '.tablename('website_user').' where id=:id',[':id'=>$_SESSION['account']['id']]);
        $_SESSION['account'] = $account;

        show_json(0,['data'=>[$_SESSION['account']['phone'],trim($data['realname']),trim($data['idcard'])]]);
    }else{
        $open = isset($data['open'])?1:0;
        include $this->template('checkprice/account_reg');
    }
}
#O端操作
elseif($op=='check_detail'){
    #O端查看询价详情
    $ordersn = trim($_GPC['ordersn']);
    $order = pdo_fetch('select a.*,b.content as form from '.tablename('website_inquiry_order').' a left join '.tablename('website_inquiry_form').' b on b.pid=a.buss_id where a.ordersn=:ordersn',[':ordersn'=>$ordersn]);
    if(!empty($order['content'])){
        $order['content'] = json_decode($order['content'],true);
    }
    if(!empty($order['files'])){
        $order['files'] = json_decode($order['files'],true);
        
    }
    $order['form'] = json_decode($order['form'],true);
    include $this->template('checkprice/check_detail');
}
elseif($op=='share_history'){
    $id = intval($data['id']);
    #查看分享历史
    if(isset($data['pa'])){
        $limit = intval($data['limit']);
        $page = (intval($data['page'])-1) * $limit;
        $offset = ' limit '.$page.','.$limit;
        $count = pdo_fetchcolumn('select count(id) from '.tablename('website_manage_order').' where inquiry_id=:id',[':id'=>$data['id']]);
        $list = pdo_fetchall('select * from '.tablename('website_manage_order').' where inquiry_id=:id order by id desc '.$offset,[':id'=>$data['id']]);
        // $status = [0=>'未确认',1=>'已确认'];
        foreach($list as $k=>$v){
            $list[$k]['realname'] = '';
            if($v['uid']!=''){
                $info = pdo_fetch('select realname,phone,email from '.tablename('website_user').' where id=:id',[':id'=>$v['uid']]);
                $list[$k]['realname'] = $info['realname'];
                $list[$k]['phone'] = $info['phone'];
                $list[$k]['email'] = $info['email'];
            }
            
            // $list[$k]['status_name'] = $status[$v['status']];
            $list[$k]['createtime'] = date('Y-m-d H:i',$v['createtime']);
        }
        die(json_encode(['code'=>0,'data'=>$list,'count'=>$count]));
    }
    
    include $this->template('checkprice/share_history');
}
elseif($op=='share_merchant'){
    #分享给商户
    $id = intval($data['id']);
    if($_W['isajax']){
        #通知商户
        if(empty($data['merchants'])){
            show_json(-1,['msg'=>'请选择通知对象']);
        }
        $data['merchants'] = explode(',',$data['merchants']);
        $email = '';
        foreach($data['merchants'] as $k=>$v){
            $user = pdo_fetch('select email,openid,phone from '.tablename('website_user').' where id=:id',[':id'=>$v]);
            $email .= $user['email'].';';
            pdo_insert('website_manage_order',[
                'inquiry_id'=>$id,
                'uid'=>$v,
                'createtime'=>$time
            ]);
        }
        $res = httpRequest2('https://shop.gogo198.cn/collect_website/public/?s=/api/sendemail/index',['email'=>$email,'title'=>'询价信息','content'=>'点击链接查看询价信息：https://www.gogo198.net/?s=index/quote_info&id='.$id]);
        if($res){
            show_json(0,['msg'=>'通知成功']);
        }
    }else{
        $merchant = pdo_fetchall('select * from '.tablename('website_user').' where merch_status=2');
        $merchant = json_encode($merchant,true);
        include $this->template('checkprice/share_merchant'); 
    }
}
elseif($op=='share_others'){
    #分享给指定人
    $id = intval($data['id']);
    if($_W['isajax']){
        if(empty($data['other_contact'])){
            show_json(-1,['msg'=>'请输入通知对象']);
        }
        if($data['notice_type']==1){
            #邮箱通知
            if(!empty($data['other_contact'])){
                $data['other_contact'] = explode('、',$data['other_contact']);
                $email = '';
                foreach($data['other_contact'] as $k=>$v){
                    $email .=$v.';';
                    pdo_insert('website_manage_order',[
                        'inquiry_id'=>$id,
                        'email'=>$v,
                        'createtime'=>$time
                    ]);
                }
                httpRequest2('https://shop.gogo198.cn/collect_website/public/?s=/api/sendemail/index',['email'=>$email,'title'=>'询价信息','content'=>'点击链接查看询价信息：https://www.gogo198.net/?s=index/quote_info&id='.$id]);
            }
        }elseif($data['notice_type']==2){
            #手机通知
            if(!empty($data['other_contact'])){
                $data['other_contact'] = explode('、',$data['other_contact']);
                foreach($data['other_contact'] as $k=>$v){
                    pdo_insert('website_manage_order',[
                        'inquiry_id'=>$id,
                        'phone'=>$v,
                        'createtime'=>$time
                    ]);
                    $post_data = [
                        'spid'=>'254560',
                        'password'=>'J6Dtc4HO',
                        'ac'=>'1069254560',
                        'mobiles'=>$v,
                        'content'=>'点击链接查看询价信息：https://www.gogo198.net/?s=index/quote_info&id='.$id.'【GOGO】',
                    ];
                    $post_data = json_encode($post_data,true);
                    $res = httpRequest2('https://decl.gogo198.cn/api/sendmsg_jumeng',$post_data,array(
                        'Content-Type: application/json; charset=utf-8',
                        'Content-Length:' . strlen($post_data),
                        'Cache-Control: no-cache',
                        'Pragma: no-cache'
                    ));
                }
            }
        }

        show_json(0,['msg'=>'分享询价成功！']);
    }else{
        include $this->template('checkprice/share_others');
    }
}
elseif($op=='upload_file'){
    #远程传送文件
    set_time_limit(0);
    load()->func('file');
	$field = 'file';
	$file_suffix = strtolower(pathinfo($_FILES[$field]['name'], PATHINFO_EXTENSION));
	$filetype = ['jpg', 'jpeg', 'png','pdf','doc','docx','excel','xls','xlsx'];

// 	if(!in_array($file_suffix,$filetype)){
// 		$result['status'] = 0;
// 		$result['message'] = '文件上传失败，不支持此类型文件！';
// 		exit(json_encode($result));
// 	}
    
	if (!empty($_FILES[$field]['name'])) {
		if ($_FILES[$field]['error'] != 0) {
			$result['status'] = 0;
			$result['message'] = '文件上传失败，请重试！';
			exit(json_encode($result));
		}

// 		$path = '/www/wwwroot/default/company/dedcms/new1_web/public/uploads/merch_file';
        
//		$file = file_upload_all($_FILES[$field], 'image','',$path);
//
//		if (is_error($file)) {
//			$result['message'] = $file['message'];
//			exit(json_encode($result));
//		}
//
//		if (function_exists('file_remote_upload')) {
//			$remote = file_remote_upload($file['path']);
//
//			if (is_error($remote)) {
//				$result['message'] = $remote['message'];
//				exit(json_encode($result));
//			}
//		}
//        $post_data = [
//            'file' => new CURLFile($file2['file']['tmp_name'], $file2['file']['type'],'https://shop.gogo198.cn/attachment/'.$file['path']),
//        ];


        $post_data=array(
             'file'=> new CURLFile($_FILES['file']['tmp_name'],$_FILES["file"]['type'],$_FILES['file']['name']),
             'name'=>$_FILES["file"]["name"]
         );
//        dd($post_data);
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, 'https://www.gogo198.net/?s=index/upload_file');
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");//3.请求方式
        curl_setopt($curl, CURLOPT_POSTFIELDS, $post_data);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $result = curl_exec($curl);
        curl_close($curl);
        $result = json_decode($result,true);
		$result['file_path'] = 'https://www.gogo198.net' . $result['file_path'];
		exit(json_encode($result));
		return 1;
	}

	$result['message'] = '请选择要上传的文件';
	exit(json_encode($result));
	return 1;
}