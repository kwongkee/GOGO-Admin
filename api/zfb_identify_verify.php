<?php
/**
 * 用于支付宝身份认证后的回调操作
 * 2022-05-26
 **/

include "../framework/bootstrap.inc.php";
include "../framework/class/weixin.account.class.php";

//处理
$realname = trim($_GET['cert_name']);
$idcard = trim($_GET['cert_no']);
$mobile = trim($_GET['phone_no']);
$reg_type = intval($_GET['reg_type']);
$is_attestation = intval($_GET['is_attestation']);
$is_merch = intval($_GET['is_merch']);
//$is_merch = 1;
$collection_id = intval($_GET['collection_id']);

if($is_attestation==1){
    #用户实名认证
    $info = pdo_fetch('select * from '.tablename('customs_collection').' where id=:id',[':id'=>$collection_id]);
    $output = 0;
    if($info['is_attestation']==1){
        $output=1;
    }else{
        //查询身份验证记录有无成功
        $url = 'https://decl.gogo198.cn/api/verify_log?certify_id='.$info['certify_id'];
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, '');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 20);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $output = curl_exec($ch);
        if (curl_errno($ch)) {
            $code = curl_errno($ch);
            $msg = curl_error($ch);
            return ['result' => false, 'errorCode' => $code, 'description' => $msg];
        }
        curl_close($ch);
    }

    if($output==1){
        //验证成功
        pdo_update('customs_collection',['is_attestation'=>1],['id'=>$collection_id]);
        pdo_update('sz_yi_member',['is_attestation'=>1],['openid'=>$info['openid']]);
        //通知付款人
        $post_data = [
            'spid'=>'254560',
            'password'=>'J6Dtc4HO',
            'ac'=>'1069254560',
            'mobiles'=>$info['payer_tel'],
            'content'=>'您好，你已通过第三方的实名认证，及同意付款人（'.$info['payer_name'].'）给您的应付款对账事宜，请依据您已确认的应付款事项与金额如期足额付款，如有疑问，请与收款人联系商洽，感谢您的支持！ 【GOGO】',
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
        $result=curl_exec($ch);
        curl_close($ch);

        echo '<link rel="stylesheet" href="../addons/sz_yi/template/mobile/default/enterprise/static/css/test_rx.css">
              <meta name="viewport" content="width=device-width,minimum-scale=1.0,maximum-scale=1.0,user-scalable=no">
              <style>*{margin:0;padding:0;}</style>
              <div class="wjdt_title">
                  <div class="header" style="height:2.5rem;">
                       <h3 style="font-size:1.2rem;top:0.7rem;">尽职调查</h3>
                  </div>
                  <div class="dtks_box">
                    <div class="finish">
                      <div class="pic"><img src="../addons/sz_yi/template/mobile/default/enterprise/static/images/icon.png" alt="" style="width:7.5rem;"></div>
                      <div class="finish-txt" style="margin: 0 0 2rem;">
                        <p style="padding-top: 15px;font-size:0.9rem;">恭喜您，本次认证成功，请返回并刷新支付页面，敬请知悉！</p>
                      </div>
                    </div>
                  </div>
              </div>
              <div class="footer" style="height:3rem;line-height:3rem;font-size:1rem;">
                  <img src="../addons/sz_yi/template/mobile/default/enterprise/static/images/logo.png" alt="" style="width:7.2rem;top:0.3rem;"> &nbsp;&nbsp;技术支持
              </div>';exit;
    }elseif($output==2){
        pdo_delete('enterprise_legaler_verify',['mobile'=>$info['payer_tel']]);
        
        echo '<link rel="stylesheet" href="../addons/sz_yi/template/mobile/default/enterprise/static/css/test_rx.css">
              <meta name="viewport" content="width=device-width,minimum-scale=1.0,maximum-scale=1.0,user-scalable=no">
              <style>*{margin:0;padding:0;}</style>
              <div class="wjdt_title">
                  <div class="header" style="height:2.5rem;">
                       <h3 style="font-size:1.2rem;top:0.7rem;">尽职调查</h3>
                  </div>
                  <div class="dtks_box">
                    <div class="finish">
                      <div class="finish-txt" style="margin: 0 0 2rem;">
                        <p style="padding-top: 15px;font-size:0.9rem;">抱歉，本次认证失败，请联系客服查看原因，敬请知悉！</p>
                      </div>
                    </div>
                  </div>
              </div>
              <div class="footer" style="height:3rem;line-height:3rem;font-size:1rem;">
                  <img src="../addons/sz_yi/template/mobile/default/enterprise/static/images/logo.png" alt="" style="width:7.2rem;top:0.3rem;"> &nbsp;&nbsp;技术支持
              </div>';exit;
    }
    
    
}
elseif($is_merch==1){
    #新商户认证
    $company_id = intval($_GET['company_id']);
    $enterprise_members = pdo_fetch('select * from '.tablename('website_user_company').' where id=:id',[':id'=>$company_id]);
    $output = 0;

    if(empty($enterprise_members)){
        echo '<h2>系统查询不到任何信息</h2>';exit;
    }else {
        if ($enterprise_members['status'] == 0) {
            #认证正常（成功）
            $output = 1;
        } else {
            //查询身份验证记录有无成功
            $url = 'https://decl.gogo198.cn/api/verify_log?certify_id=' . $enterprise_members['certify_id'];
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($ch, CURLOPT_POSTFIELDS, '');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 20);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            $output = curl_exec($ch);
            if (curl_errno($ch)) {
                $code = curl_errno($ch);
                $msg = curl_error($ch);
                return ['result' => false, 'errorCode' => $code, 'description' => $msg];
            }
            curl_close($ch);
        }
    }

    if($output==1){
        //验证成功

        if($enterprise_members['status']==-1){
            #商户认证-待认证
            pdo_update('website_user_company',['status'=>0,'is_verify'=>1],['id'=>$company_id]);
            pdo_update('website_user',['merch_status'=>2,'is_verify'=>1],['id'=>$enterprise_members['user_id']]);
        }elseif($enterprise_members['status']==1){
            #商户认证-注销
            pdo_update('website_user_company',['status'=>-1],['id'=>$company_id]);
        }

        #通知O端进行审批
//        $system = pdo_fetch('select * from '.tablename('centralize_system_notice').' where uid=0');
        $servicers = pdo_fetchall('select * from '.tablename('centralize_system_servicer').' where status=1');
        foreach($servicers as $k=>$v){
            $user = pdo_fetch('select * from '.tablename('website_user').' where id=:id',[':id'=>$v['uid']]);
            if(!empty($user['openid'])){
                $post = json_encode([
                    'call'=>'confirmCollectionNotice',
                    'first' =>'商户['.$enterprise_members['realname'].']提交商户企业关联认证，请登录总后台查看！',
                    'keyword1' => '商户['.$enterprise_members['realname'].']提交商户企业关联认证，请登录总后台查看！',
                    'keyword2' => '已提交待审核',
                    'keyword3' => date('Y-m-d H:i:s',time()),
                    'remark' => '点击查看详情',
                    'url' => 'https://gadmin.gogo198.cn/',
                    'openid' => $user['openid'],
                    'temp_id' => 'SVVs5OeD3FfsGwW0PEfYlZWetjScIT8kDxht5tlI1V8'
                ]);

                $ch2=curl_init();
                curl_setopt($ch2,CURLOPT_URL,'https://shop.gogo198.cn/api/sendwechattemplatenotice.php');
                curl_setopt($ch2,CURLOPT_RETURNTRANSFER,1);
                curl_setopt($ch2,CURLOPT_SSL_VERIFYPEER,false);
                curl_setopt($ch2,CURLOPT_SSL_VERIFYHOST,false);
                curl_setopt($ch2,CURLOPT_POST,1);
                curl_setopt($ch2,CURLOPT_POSTFIELDS,$post);
                curl_setopt($ch2, CURLOPT_HTTPHEADER,[]);
                $output=curl_exec($ch2);
                curl_close($ch2);
            }
            elseif(!empty($user['email'])){
                #邮箱通知
                $url = 'https://shop.gogo198.cn/collect_website/public/?s=/api/sendemail/index';
                $data = ['email'=>$user['email'],'title'=>'商户['.$enterprise_members['custom_id'].']提交商户企业关联','content'=>'请登录总后台，进入商户管理进行审批：https://gadmin.gogo198.cn/'];
                $head = [];

                $ch2=curl_init();
                curl_setopt($ch2,CURLOPT_URL,$url);
                curl_setopt($ch2,CURLOPT_RETURNTRANSFER,1);
                curl_setopt($ch2,CURLOPT_SSL_VERIFYPEER,false);
                curl_setopt($ch2,CURLOPT_SSL_VERIFYHOST,false);
                curl_setopt($ch2,CURLOPT_POST,1);
                curl_setopt($ch2,CURLOPT_POSTFIELDS,$data);
                curl_setopt($ch2, CURLOPT_HTTPHEADER,$head);
                $output=curl_exec($ch2);
                curl_close($ch2);
            }
        }
//        if($system['notice_type']==1){
//            $post = json_encode([
//                'call'=>'confirmCollectionNotice',
//                'first' =>'商户['.$enterprise_members['realname'].']提交商户企业关联认证，请登录总后台查看！',
//                'keyword1' => '商户['.$enterprise_members['realname'].']提交商户企业关联认证，请登录总后台查看！',
//                'keyword2' => '已提交待审核',
//                'keyword3' => date('Y-m-d H:i:s',time()),
//                'remark' => '点击查看详情',
//                'url' => 'https://gadmin.gogo198.cn/',
//                'openid' => $system['account'],
//                'temp_id' => 'SVVs5OeD3FfsGwW0PEfYlZWetjScIT8kDxht5tlI1V8'
//            ]);
//
//            $ch2=curl_init();
//            curl_setopt($ch2,CURLOPT_URL,'https://shop.gogo198.cn/api/sendwechattemplatenotice.php');
//            curl_setopt($ch2,CURLOPT_RETURNTRANSFER,1);
//            curl_setopt($ch2,CURLOPT_SSL_VERIFYPEER,false);
//            curl_setopt($ch2,CURLOPT_SSL_VERIFYHOST,false);
//            curl_setopt($ch2,CURLOPT_POST,1);
//            curl_setopt($ch2,CURLOPT_POSTFIELDS,$post);
//            curl_setopt($ch2, CURLOPT_HTTPHEADER,[]);
//            $output=curl_exec($ch2);
//            curl_close($ch2);
//        }
//        elseif($system['notice_type']==3){
//            #邮箱通知
//            $url = 'https://shop.gogo198.cn/collect_website/public/?s=/api/sendemail/index';
//            $data = ['email'=>$system['account'],'title'=>'商户['.$enterprise_members['custom_id'].']提交商户企业关联','content'=>'请登录总后台，进入商户管理进行审批：https://gadmin.gogo198.cn/'];
//            $head = [];
//
//            $ch2=curl_init();
//            curl_setopt($ch2,CURLOPT_URL,$url);
//            curl_setopt($ch2,CURLOPT_RETURNTRANSFER,1);
//            curl_setopt($ch2,CURLOPT_SSL_VERIFYPEER,false);
//            curl_setopt($ch2,CURLOPT_SSL_VERIFYHOST,false);
//            curl_setopt($ch2,CURLOPT_POST,1);
//            curl_setopt($ch2,CURLOPT_POSTFIELDS,$data);
//            curl_setopt($ch2, CURLOPT_HTTPHEADER,$head);
//            $output=curl_exec($ch2);
//            curl_close($ch2);
//        }

        echo '<link rel="stylesheet" href="../addons/sz_yi/template/mobile/default/enterprise/static/css/test_rx.css">
              <meta name="viewport" content="width=device-width,minimum-scale=1.0,maximum-scale=1.0,user-scalable=no">
              <style>*{margin:0;padding:0;}</style>
              <div class="wjdt_title">
                  <div class="header" style="height:2.5rem;">
                       <h3 style="font-size:1.2rem;top:0.7rem;">尽职调查</h3>
                  </div>
                  <div class="dtks_box">
                    <div class="finish">
                      <div class="pic"><img src="../addons/sz_yi/template/mobile/default/enterprise/static/images/icon.png" alt="" style="width:7.5rem;"></div>
                      <div class="finish-txt" style="margin: 0 0 2rem;">
                        <p style="padding-top: 15px;font-size:0.9rem;">恭喜您，本次认证成功，贵司【'.$enterprise_members['realname'].'】的验证已被通过，敬请知悉！</p>
                      </div>
                    </div>
                  </div>
              </div>
              <div class="footer" style="height:3rem;line-height:3rem;font-size:1rem;">
                  <img src="../addons/sz_yi/template/mobile/default/enterprise/static/images/logo.png" alt="" style="width:7.2rem;top:0.3rem;"> &nbsp;&nbsp;技术支持
              </div>';exit;

    }
    elseif($output==2){
        //验证失败

        pdo_update('website_user_company',['status'=>-1,'certify_id'=>''],['id'=>$company_id]);

        pdo_delete('enterprise_legaler_verify',['company_id'=>$company_id]);

        echo '<link rel="stylesheet" href="../addons/sz_yi/template/mobile/default/enterprise/static/css/test_rx.css">
          <meta name="viewport" content="width=device-width,minimum-scale=1.0,maximum-scale=1.0,user-scalable=no">
          <style>*{margin:0;padding:0;}</style>
          <div class="wjdt_title">
              <div class="header" style="height:2.5rem;">
                   <h3 style="font-size:1.2rem;top:0.7rem;">尽职调查</h3>
              </div>
              <div class="dtks_box">
                <div class="finish">
                  <div class="pic"><img src="../addons/sz_yi/template/mobile/default/enterprise/static/images/icon2.png" alt="" style="width:7.5rem;"></div>
                  <div class="finish-txt" style="margin: 0 0 2rem;">
                    <p style="padding-top: 15px;font-size:0.9rem;">好抱歉，本次认证失败，贵司'.$enterprise_members['realname'].'的验证未予通过，敬请知悉！</p>
                  </div>
                </div>
              </div>
          </div>
          <div class="footer" style="height:3rem;line-height:3rem;font-size:1rem;">
              <img src="../addons/sz_yi/template/mobile/default/enterprise/static/images/logo.png" alt="" style="width:7.2rem;top:0.3rem;"> &nbsp;&nbsp;技术支持
          </div>';exit;
    }
}
elseif($is_merch==2){
    #赊账用户认证
    #新商户认证
    $enterprise_members = pdo_fetch('select * from '.tablename('website_user').' where idcard=:idcard and phone=:mobile limit 1',[':idcard'=>$idcard,':mobile'=>$mobile]);
    if(empty($enterprise_members)){
        $enterprise_members = pdo_fetch('select * from '.tablename('website_user').' where idcard=:idcard limit 1',[':idcard'=>$idcard]);
    }
    $output = 0;

    if(empty($enterprise_members)){
        exit;
    }else {
        if ($enterprise_members['is_verify'] == 1) {
            $output = 1;
        } else {
            //查询身份验证记录有无成功
            $url = 'https://decl.gogo198.cn/api/verify_log?certify_id=' . $enterprise_members['certify_id'];
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($ch, CURLOPT_POSTFIELDS, '');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 20);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            $output = curl_exec($ch);
            if (curl_errno($ch)) {
                $code = curl_errno($ch);
                $msg = curl_error($ch);
                return ['result' => false, 'errorCode' => $code, 'description' => $msg];
            }
            curl_close($ch);
        }
    }

    if($output==1){
        //验证成功

        if($enterprise_members['is_verify']==0){
            #个人认证
            pdo_update('website_user',['is_verify'=>1],['realname'=>$realname,'idcard'=>$idcard,'phone'=>$mobile]);
        }


        echo '<link rel="stylesheet" href="../addons/sz_yi/template/mobile/default/enterprise/static/css/test_rx.css">
              <meta name="viewport" content="width=device-width,minimum-scale=1.0,maximum-scale=1.0,user-scalable=no">
              <style>*{margin:0;padding:0;}</style>
              <div class="wjdt_title">
                  <div class="header" style="height:2.5rem;">
                       <h3 style="font-size:1.2rem;top:0.7rem;">尽职调查</h3>
                  </div>
                  <div class="dtks_box">
                    <div class="finish">
                      <div class="pic"><img src="../addons/sz_yi/template/mobile/default/enterprise/static/images/icon.png" alt="" style="width:7.5rem;"></div>
                      <div class="finish-txt" style="margin: 0 0 2rem;">
                        <p style="padding-top: 15px;font-size:0.9rem;">恭喜您，本次认证成功，'.$enterprise_members['realname'].'的验证已被通过，敬请知悉！</p>
                      </div>
                    </div>
                  </div>
              </div>
              <div class="footer" style="height:3rem;line-height:3rem;font-size:1rem;">
                  <img src="../addons/sz_yi/template/mobile/default/enterprise/static/images/logo.png" alt="" style="width:7.2rem;top:0.3rem;"> &nbsp;&nbsp;技术支持
              </div>';exit;

    }
    elseif($output==2){
        //验证失败

        pdo_update('website_user',['is_verify'=>0,'certify_id'=>''],['realname'=>$realname,'idcard'=>$idcard,'phone'=>$mobile]);

        pdo_delete('enterprise_legaler_verify',['mobile'=>$enterprise_members['phone']]);

        echo '<link rel="stylesheet" href="../addons/sz_yi/template/mobile/default/enterprise/static/css/test_rx.css">
          <meta name="viewport" content="width=device-width,minimum-scale=1.0,maximum-scale=1.0,user-scalable=no">
          <style>*{margin:0;padding:0;}</style>
          <div class="wjdt_title">
              <div class="header" style="height:2.5rem;">
                   <h3 style="font-size:1.2rem;top:0.7rem;">尽职调查</h3>
              </div>
              <div class="dtks_box">
                <div class="finish">
                  <div class="pic"><img src="../addons/sz_yi/template/mobile/default/enterprise/static/images/icon2.png" alt="" style="width:7.5rem;"></div>
                  <div class="finish-txt" style="margin: 0 0 2rem;">
                    <p style="padding-top: 15px;font-size:0.9rem;">好抱歉，本次认证失败，'.$enterprise_members['realname'].'的验证未予通过，敬请知悉！</p>
                  </div>
                </div>
              </div>
          </div>
          <div class="footer" style="height:3rem;line-height:3rem;font-size:1rem;">
              <img src="../addons/sz_yi/template/mobile/default/enterprise/static/images/logo.png" alt="" style="width:7.2rem;top:0.3rem;"> &nbsp;&nbsp;技术支持
          </div>';exit;
    }
}
elseif($is_merch==3){
    #个人认证信息完善
    $phone_no = trim($_GET['phone_no']);
    $cert_no = trim($_GET['cert_no']);
    $cert_name = trim($_GET['cert_name']);

    $enterprise_members = pdo_fetch('select * from '.tablename('website_user').' where phone=:phone and idcard=:cert_no and realname=:cert_name',[':phone'=>$phone_no,':cert_no'=>$cert_no,':cert_name'=>$cert_name]);
    $enterprise_company = pdo_fetch('select * from '.tablename('website_user_company').' where mobile=:phone and idcard=:cert_no and realname=:cert_name order by id desc',[':phone'=>$phone_no,':cert_no'=>$cert_no,':cert_name'=>$cert_name]);
    $output = 0;

    if(empty($enterprise_company)){
        echo '<h2>系统查询不到任何信息</h2>';exit;
    }else {
        if ($enterprise_company['status'] == 0) {
            #认证正常（成功）
            $output = 1;
        } else {
            //查询身份验证记录有无成功
            $url = 'https://decl.gogo198.cn/api/verify_log?certify_id=' . $enterprise_company['certify_id'];
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($ch, CURLOPT_POSTFIELDS, '');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 20);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            $output = curl_exec($ch);
            if (curl_errno($ch)) {
                $code = curl_errno($ch);
                $msg = curl_error($ch);
                return ['result' => false, 'errorCode' => $code, 'description' => $msg];
            }
            curl_close($ch);
        }
    }

    if($output==1){
        //验证成功

        if($enterprise_members['is_verify']==0){
            #商户认证-待认证
            pdo_update('website_user',['is_verify'=>1,'merch_status'=>2],['id'=>$enterprise_company['user_id']]);
        }
        pdo_update('website_user_company',['status'=>0],['id'=>$enterprise_company['id']]);

        #通知O端进行审批
//        $system = pdo_fetch('select * from '.tablename('centralize_system_notice').' where uid=0');
//        if($system['notice_type']==1){
//            $post = json_encode([
//                'call'=>'confirmCollectionNotice',
//                'first' =>'商户['.$enterprise_company['company'].']已认证为商户企业，请登录总后台查看！',
//                'keyword1' => '商户['.$enterprise_company['company'].']已认证为商户企业，请登录总后台查看！',
//                'keyword2' => '已提交待审核',
//                'keyword3' => date('Y-m-d H:i:s',time()),
//                'remark' => '点击查看详情',
//                'url' => 'https://gadmin.gogo198.cn/',
//                'openid' => $system['account'],
//                'temp_id' => 'SVVs5OeD3FfsGwW0PEfYlZWetjScIT8kDxht5tlI1V8'
//            ]);
//
//            $ch2=curl_init();
//            curl_setopt($ch2,CURLOPT_URL,'https://shop.gogo198.cn/api/sendwechattemplatenotice.php');
//            curl_setopt($ch2,CURLOPT_RETURNTRANSFER,1);
//            curl_setopt($ch2,CURLOPT_SSL_VERIFYPEER,false);
//            curl_setopt($ch2,CURLOPT_SSL_VERIFYHOST,false);
//            curl_setopt($ch2,CURLOPT_POST,1);
//            curl_setopt($ch2,CURLOPT_POSTFIELDS,$post);
//            curl_setopt($ch2, CURLOPT_HTTPHEADER,[]);
//            $output=curl_exec($ch2);
//            curl_close($ch2);
//        }
//        elseif($system['notice_type']==3){
//            #邮箱通知
//            $url = 'https://shop.gogo198.cn/collect_website/public/?s=/api/sendemail/index';
//            $data = ['email'=>$system['account'],'title'=>'商户['.$enterprise_company['company'].']已认证为商户企业','content'=>'请登录总后台，进入商户管理进行审批：https://gadmin.gogo198.cn/'];
//            $head = [];
//
//            $ch2=curl_init();
//            curl_setopt($ch2,CURLOPT_URL,$url);
//            curl_setopt($ch2,CURLOPT_RETURNTRANSFER,1);
//            curl_setopt($ch2,CURLOPT_SSL_VERIFYPEER,false);
//            curl_setopt($ch2,CURLOPT_SSL_VERIFYHOST,false);
//            curl_setopt($ch2,CURLOPT_POST,1);
//            curl_setopt($ch2,CURLOPT_POSTFIELDS,$data);
//            curl_setopt($ch2, CURLOPT_HTTPHEADER,$head);
//            $output=curl_exec($ch2);
//            curl_close($ch2);
//        }
        $servicers = pdo_fetchall('select * from '.tablename('centralize_system_servicer').' where status=1');
        foreach($servicers as $k=>$v){
            $user = pdo_fetch('select * from '.tablename('website_user').' where id=:id',[':id'=>$v['uid']]);
            if(!empty($user['openid'])){
                $post = json_encode([
                    'call'=>'confirmCollectionNotice',
                    'first' =>'商户['.$enterprise_members['company'].']已认证为商户企业，请登录总后台查看！',
                    'keyword1' => '商户['.$enterprise_members['company'].']已认证为商户企业，请登录总后台查看！',
                    'keyword2' => '已提交待审核',
                    'keyword3' => date('Y-m-d H:i:s',time()),
                    'remark' => '点击查看详情',
                    'url' => 'https://gadmin.gogo198.cn/',
                    'openid' => $user['openid'],
                    'temp_id' => 'SVVs5OeD3FfsGwW0PEfYlZWetjScIT8kDxht5tlI1V8'
                ]);

                $ch2=curl_init();
                curl_setopt($ch2,CURLOPT_URL,'https://shop.gogo198.cn/api/sendwechattemplatenotice.php');
                curl_setopt($ch2,CURLOPT_RETURNTRANSFER,1);
                curl_setopt($ch2,CURLOPT_SSL_VERIFYPEER,false);
                curl_setopt($ch2,CURLOPT_SSL_VERIFYHOST,false);
                curl_setopt($ch2,CURLOPT_POST,1);
                curl_setopt($ch2,CURLOPT_POSTFIELDS,$post);
                curl_setopt($ch2, CURLOPT_HTTPHEADER,[]);
                $output=curl_exec($ch2);
                curl_close($ch2);
            }
            elseif(!empty($user['email'])){
                #邮箱通知
                $url = 'https://shop.gogo198.cn/collect_website/public/?s=/api/sendemail/index';
                $data = ['email'=>$user['email'],'title'=>'商户['.$enterprise_members['company'].']已认证为商户企业','content'=>'请登录总后台，进入商户管理进行审批：https://gadmin.gogo198.cn/'];
                $head = [];

                $ch2=curl_init();
                curl_setopt($ch2,CURLOPT_URL,$url);
                curl_setopt($ch2,CURLOPT_RETURNTRANSFER,1);
                curl_setopt($ch2,CURLOPT_SSL_VERIFYPEER,false);
                curl_setopt($ch2,CURLOPT_SSL_VERIFYHOST,false);
                curl_setopt($ch2,CURLOPT_POST,1);
                curl_setopt($ch2,CURLOPT_POSTFIELDS,$data);
                curl_setopt($ch2, CURLOPT_HTTPHEADER,$head);
                $output=curl_exec($ch2);
                curl_close($ch2);
            }
        }

        echo '<html><head>'.
                '<meta charset="utf-8">'.
                '<link rel="stylesheet" href="../addons/sz_yi/template/mobile/default/enterprise/static/css/test_rx.css">'.
                '<meta name="renderer" content="webkit"><meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1"><meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=0">'.
                '<style>body{max-width: 100%;width:100%;height:100%;}*{margin:0;padding:0;}</style>'.
              '</head>'.
              '<body>'.
                  '<div class="wjdt_title">'.
                      '<div class="header" style="height:2.5rem;">'.
                           '<h3 style="font-size:1.2rem;top:0.7rem;">尽职调查</h3>'.
                      '</div>'.
                      '<div class="dtks_box">'.
                        '<div class="finish">'.
                          '<div class="pic"><img src="../addons/sz_yi/template/mobile/default/enterprise/static/images/icon.png" alt="" style="width:7.5rem;"></div>'.
                          '<div class="finish-txt" style="margin: 0 0 2rem;">'.
                            '<p style="padding-top: 15px;font-size:1rem;">恭喜您，本次认证成功，'.$enterprise_company['company'].'的验证已被通过，敬请知悉！</p>'.
                            '<p style="padding-top: 15px;font-size:1rem;"><span style="color:#e60000;font-size:20px;font-weight:800;">*</span>注意：请在手机手动切换原浏览器，关闭和重新打开当前页面，方可关联您的企业！</p>'.
                          '</div>'.
                        '</div>'.
                      '</div>'.
                  '</div>'.
                  '<div class="footer" style="height:3rem;line-height:3rem;font-size:1rem;">'.
                      '<img src="../addons/sz_yi/template/mobile/default/enterprise/static/images/logo.png" alt="" style="width:7.2rem;top:0.3rem;"> &nbsp;&nbsp;技术支持'.
                  '</div>'.
              '</body></html>';exit;

    }
    elseif($output==2){
        //验证失败

        pdo_update('website_user',['is_verify'=>0,'certify_id'=>'','idcard'=>''],['id'=>$enterprise_company['user_id']]);
        pdo_update('website_user_company',['status'=>-1,'certify_id'=>'','idcard'=>''],['id'=>$enterprise_company['id']]);

        pdo_delete('enterprise_legaler_verify',['mobile'=>$enterprise_company['mobile']]);

        echo '<meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=0">
            <link rel="stylesheet" href="../addons/sz_yi/template/mobile/default/enterprise/static/css/test_rx.css">
          <meta name="viewport" content="width=device-width,minimum-scale=1.0,maximum-scale=1.0,user-scalable=no">
          <style>body{max-width: 100%;}*{margin:0;padding:0;}</style>
          <div class="wjdt_title">
              <div class="header" style="height:2.5rem;">
                   <h3 style="font-size:1.2rem;top:0.7rem;">尽职调查</h3>
              </div>
              <div class="dtks_box">
                <div class="finish">
                  <div class="pic"><img src="../addons/sz_yi/template/mobile/default/enterprise/static/images/icon2.png" alt="" style="width:7.5rem;"></div>
                  <div class="finish-txt" style="margin: 0 0 2rem;">
                    <p style="padding-top: 15px;font-size:1rem;">好抱歉，本次认证失败，'.$enterprise_company['company'].'的验证未予通过，敬请知悉！</p>
                    <p style="padding-top: 15px;font-size:1rem;"><span style="color:#e60000;font-size:20px;font-weight:800;">*</span>注意：请在手机手动切换原浏览器，关闭和重新打开当前页面，重新填写正确的验证信息！</p>
                  </div>
                </div>
              </div>
          </div>
          <div class="footer" style="height:3rem;line-height:3rem;font-size:1rem;">
              <img src="../addons/sz_yi/template/mobile/default/enterprise/static/images/logo.png" alt="" style="width:7.2rem;top:0.3rem;"> &nbsp;&nbsp;技术支持
          </div>';exit;
    }
}
else{
    
    //商户认证（废弃）
    $enterprise_members = pdo_fetch('select * from '.tablename('enterprise_members').' where idcard=:idcard and mobile=:mobile limit 1',[':idcard'=>$idcard,':mobile'=>$mobile]);
    $output = 0;
    
    if(empty($enterprise_members)){
        exit;
    }else{
        if($enterprise_members['is_verify']==1){
            $output=1;
        }else{
            //查询身份验证记录有无成功
            $url = 'https://decl.gogo198.cn/api/verify_log?certify_id='.$enterprise_members['certify_id'];
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($ch, CURLOPT_POSTFIELDS, '');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 20);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            $output = curl_exec($ch);
            if (curl_errno($ch)) {
                $code = curl_errno($ch);
                $msg = curl_error($ch);
                return ['result' => false, 'errorCode' => $code, 'description' => $msg];
            }
            curl_close($ch);
        }
    }
    
    // file_put_contents(IA_ROOT.'/1234.txt',json_encode($output));
    if($output==1){
        //验证成功
        if($reg_type==2){
            //个人
            $res = pdo_update('enterprise_members',['is_verify'=>1],['realname'=>$realname,'idcard'=>$idcard,'mobile'=>$mobile]);    
        }
        elseif($reg_type==1){
            //企业
            $res = pdo_update('enterprise_members',['is_verify'=>1],['id'=>$enterprise_members['id']]);
            pdo_update('centralize_manage_person',['tel'=>$mobile],['id'=>$enterprise_members['centralizer_id']]);
            pdo_update('total_merchant_account',['mobile'=>$mobile],['enterprise_id'=>$enterprise_members['id']]);
            $manage_person = pdo_fetch('select email from '.tablename('centralize_manage_person').' where id=:id',[':id'=>$enterprise_members['centralizer_id']]);
            $company_info = pdo_fetch('select `name`,address from '.tablename('enterprise_basicinfo').' where member_id=:member_id',[':member_id'=>$enterprise_members['id']]);
            $is_have_decluser = pdo_fetch('select id from '.tablename('decl_user').' where user_tel=:tel and user_email=:email',[':tel'=>$enterprise_members['mobile'],':email'=>$manage_person['email']]);
            if(empty($is_have_decluser['id'])){
                pdo_update('decl_user',[
                    'user_name'=>$enterprise_members['realname'],
                    'user_tel'=>$enterprise_members['mobile'],
                    'user_email'=>$manage_person['email'],
                ],['enterprise_id'=>$enterprise_members['id']]);
//                pdo_insert('decl_user',[
//                    'user_name'=>$enterprise_members['realname'],
//                    'user_tel'=>$enterprise_members['mobile'],
//                    'user_email'=>$manage_person['email'],
//                    'user_password'=>md5('888888'),
//                    'uniacid'=>3,
//                    'plat_id'=>1,
//                    'created_at'=>date('Y-m-d H:i:s'),
//                    'user_status'=>0,
//                    'buss_id'=>3,
//                    'company_name'=>$company_info['name'],
//                    'company_num'=>'',#不知道是什么
//                    'address'=>$company_info['address'],
//                    'enterprise_id'=>$enterprise_members['id'],
//                ]);
            }
    
            pdo_update('centralize_manage_person',['status'=>1,'enterprise_id'=>$enterprise_members['id']],['id'=>$enterprise_members['centralizer_id']]);
    
    //        if($enterprise_members['entrance']==1){
    //            #拖车订单
    //            pdo_update('customs_freight_order',['']);
    //        }
        }
    
        echo '<link rel="stylesheet" href="../addons/sz_yi/template/mobile/default/enterprise/static/css/test_rx.css">
              <meta name="viewport" content="width=device-width,minimum-scale=1.0,maximum-scale=1.0,user-scalable=no">
              <style>*{margin:0;padding:0;}</style>
              <div class="wjdt_title">
                  <div class="header" style="height:2.5rem;">
                       <h3 style="font-size:1.2rem;top:0.7rem;">尽职调查</h3>
                  </div>
                  <div class="dtks_box">
                    <div class="finish">
                      <div class="pic"><img src="../addons/sz_yi/template/mobile/default/enterprise/static/images/icon.png" alt="" style="width:7.5rem;"></div>
                      <div class="finish-txt" style="margin: 0 0 2rem;">
                        <p style="padding-top: 15px;font-size:0.9rem;">恭喜您，本次认证成功，贵司'.$enterprise_members['nickname'].'的验证已被通过，敬请知悉！</p>
                      </div>
                    </div>
                  </div>
              </div>
              <div class="footer" style="height:3rem;line-height:3rem;font-size:1rem;">
                  <img src="../addons/sz_yi/template/mobile/default/enterprise/static/images/logo.png" alt="" style="width:7.2rem;top:0.3rem;"> &nbsp;&nbsp;技术支持
              </div>';exit;
        
    }
    elseif($output==2){
        //验证失败
        if($reg_type==2){
            //个人
            pdo_delete('enterprise_members',['realname'=>$realname,'idcard'=>$idcard,'mobile'=>$mobile]);
        }elseif($reg_type==1){
            //法人
    //        pdo_delete('enterprise_members',['idcard'=>$idcard,'mobile'=>$mobile]);
    //        pdo_delete('total_merchant_account',['mobile'=>$mobile]);
        }
        if($enterprise_members['id']>0){
    //        pdo_delete('enterprise_basicinfo',['member_id'=>$enterprise_members['id']]);
        }
        pdo_delete('enterprise_legaler_verify',['mobile'=>$enterprise_members['mobile']]);
        
        echo '<link rel="stylesheet" href="../addons/sz_yi/template/mobile/default/enterprise/static/css/test_rx.css">
          <meta name="viewport" content="width=device-width,minimum-scale=1.0,maximum-scale=1.0,user-scalable=no">
          <style>*{margin:0;padding:0;}</style>
          <div class="wjdt_title">
              <div class="header" style="height:2.5rem;">
                   <h3 style="font-size:1.2rem;top:0.7rem;">尽职调查</h3>
              </div>
              <div class="dtks_box">
                <div class="finish">
                  <div class="pic"><img src="../addons/sz_yi/template/mobile/default/enterprise/static/images/icon2.png" alt="" style="width:7.5rem;"></div>
                  <div class="finish-txt" style="margin: 0 0 2rem;">
                    <p style="padding-top: 15px;font-size:0.9rem;">好抱歉，本次认证失败，贵司'.$enterprise_members['nickname'].'的验证未予通过，敬请知悉！</p>
                  </div>
                </div>
              </div>
          </div>
          <div class="footer" style="height:3rem;line-height:3rem;font-size:1rem;">
              <img src="../addons/sz_yi/template/mobile/default/enterprise/static/images/logo.png" alt="" style="width:7.2rem;top:0.3rem;"> &nbsp;&nbsp;技术支持
          </div>';exit;
    }
}



// $post  = json_decode(file_get_contents('php://input'),true);
// file_put_contents(IA_ROOT.'/123.txt',json_encode($post));
