<?php
// 模块LTD提供
if (!defined('IN_IA')) {
    exit('Access Denied');
}

global $_W;
global $_GPC;

$op = !empty($_GPC['op'])?trim($_GPC['op']):'display';
$openid = $_W['openid'];
if($op=='display'){
    //方块选择
    
    //判断该商户是否有"登记端"、"记账端"、"管理端"权限
    $auth = ['user'=>0,'account'=>0,'manager'=>0];
    $decl_user = pdo_fetch('select id,account_auth from '.tablename('decl_user').' where openid=:openid and user_status=0',[':openid'=>$openid]);
    if($decl_user['account_auth']=='1,'){
        $auth['user']=1;
    }
    $account = pdo_fetch('select is_accounting from '.tablename('mc_mapping_fans').' where openid=:openid',[':openid'=>$openid]);
    if($account['is_accounting']==1){
        $auth['account']=1;
    }
    if($openid=='ov3-bt5vIxepEjWc51zRQNQbFSaQ' || $openid=='ov3-bt8keSKg_8z9Wwi-zG1hRhwg'){
        $auth['manager']=1;
    }
    if(empty($auth['user']) && empty($auth['account']) && empty($auth['manager'])){
        echo '<h1>暂无权限</h1>';exit;
    }
    include $this->template('account/index');
}elseif($op=='remind'){
    //每个月5号提醒管理员去提醒商户录入凭证信息
    
    if($_W['isajax']){
        $ids = explode(',',trim($_GPC['ids']));

        foreach($ids as $k=>$v){
            if(!empty($v)){
                $merch = pdo_fetch('select openid,user_name from '.tablename('decl_user').' where id=:id and uniacid=:uni',[':id'=>$v,':uni'=>$_W['uniacid']]);
                
                //等待模板下发，发送该商户通知,暂时用“审核提醒”模板
                 $post = json_encode([
                     'call'=>'pressMoneyToMember',
                     'first' =>'您好，温馨提醒：请于本月上旬完成贵司上月所有交易/往来记账凭证的汇总，以便记账代理人能按时为贵司处理往来交易记账与当月/季度税务申报，感谢支持！',
                     'keyword1' => '交易凭证汇总的提醒',
                     'keyword2' => $merch['user_name'],
                     'keyword3' => '完成贵司上月所有交易/往来记账凭证的汇总',
                     'keyword4' => date('Y-m-d H:i:s',time()),
                     'remark' => '',
                     'url' => 'https://shop.gogo198.cn/app/index.php?i=3&c=entry&do=account&p=index&m=sz_yi&op=select',
                     'openid' => $merch['openid'],
                     //ov3-bt5vIxepEjWc51zRQNQbFSaQ ov3-bt8keSKg_8z9Wwi-zG1hRhwg
                     'temp_id' => 'h4i2gJ3MBvsU1YROldhHeoBS_A5baXrdMIxeluurI94'
                 ]);

                $post2 = json_encode([
                    'call'=>'pressMoneyToMember',
                    'first' =>'您好，温馨提醒：请完成贵司一月所有交易/往来记账凭证的汇总，以便记账代理人能按时为贵司处理往来交易记账与当月/季度税务申报，感谢支持！',
                    'keyword1' => '交易凭证汇总的提醒',
                    'keyword2' => '陈海飞',
                    'keyword3' => '完成贵司一月所有交易/往来记账凭证的汇总',
                    'keyword4' => date('Y-m-d H:i:s',time()),
                    'remark' => '',
                    'url' => 'https://shop.gogo198.cn/app/index.php?i=3&c=entry&do=account&p=index&m=sz_yi&op=select',
                    'openid' => 'ov3-btwmtF8f0B5Iv8oSGsf65_54',
                    //ov3-bt5vIxepEjWc51zRQNQbFSaQ ov3-bt8keSKg_8z9Wwi-zG1hRhwg
                    'temp_id' => 'h4i2gJ3MBvsU1YROldhHeoBS_A5baXrdMIxeluurI94'
                ]);

                 $res = ihttp_request('https://shop.gogo198.cn/api/sendwechattemplatenotice.php', $post2);
                 if($res){
                     show_json(1,['msg'=>'已通知商户，请自行退出！']);
                 }
            }
        }
    }else{
        $list = pdo_fetchall('select id,user_name,user_tel,openid from '.tablename('decl_user').' where user_status=0 and uniacid=:uni and openid !="" order by id desc',[':uni'=>$_W['uniacid']]);
    }
    include $this->template('account/remind');
}elseif($op=='tt'){
    //封存
    $post2 = json_encode([
        'call'=>'pressMoneyToMember',
        'first' =>'您好，温馨提醒：请完成贵司三月所有交易/往来记账凭证的汇总，以便记账代理人能按时为贵司处理往来交易记账与当月/季度税务申报，感谢支持！',
        'keyword1' => '交易凭证汇总的提醒',
        'keyword2' => '陈海飞',
        'keyword3' => '完成贵司三月所有交易/往来记账凭证的汇总',
        'keyword4' => date('Y-m-d H:i:s',time()),
        'remark' => '',
        'url' => 'https://shop.gogo198.cn/app/index.php?i=3&c=entry&do=account&p=index&m=sz_yi&op=select',
        'openid' => 'ov3-btwmtF8f0B5Iv8oSGsf65_54',
        //ov3-bt5vIxepEjWc51zRQNQbFSaQ ov3-bt8keSKg_8z9Wwi-zG1hRhwg
        'temp_id' => 'h4i2gJ3MBvsU1YROldhHeoBS_A5baXrdMIxeluurI94'
    ]);

    $res = ihttp_request('https://shop.gogo198.cn/api/sendwechattemplatenotice.php', $post2);
    if($res){
        echo '已通知商户，请自行退出！';
    }
}elseif($op=='select'){
    //选择按钮
    if($_W['isajax']){
        $openid = $_W['openid'];
        $timestamp=time();
        $pre_month=date('Y-m',strtotime(date('Y',$timestamp).'-'.(date('m',$timestamp)-1).'-01'));

        //查找上月凭证
        $standardTime = date('Y-m-1');
        $_lastMonthStart = strtotime("-1 month", strtotime($standardTime));
        $_lastMonthEnd = strtotime('-1 sec', strtotime($standardTime));
        $batch_info = pdo_fetchall('select id from '.tablename('customs_accounting_register').' where openid=:openid and status=1 and status2=2 and ( createtime <= :lastMonthEnd and createtime >= :lastMonthStart ) and reconciliation_status !=0 order by id desc',[':openid'=>$_W['openid'],':lastMonthEnd'=>$_lastMonthEnd,':lastMonthStart'=>$_lastMonthStart]);
        if(!empty($batch_info)){
            foreach($batch_info as $k=>$v){
                $batch_ids[] = $v['id'];
            }
            $batch_ids = implode(',',$batch_ids);
        }else{
            $batch_ids = 0;
        }
        //查找上月凭证END

        $isHaveLog = pdo_fetch('select id from '.tablename('customs_accounting_notbook').' where openid=:openid and pre_month=:pre_month',[':openid'=>$openid,'pre_month'=>$pre_month]);
        if(empty($isHaveLog)){
            $merch_info = pdo_fetch('select a.user_name,b.openid from '.tablename('decl_user').' a left join '.tablename('mc_mapping_fans').' b on b.fanid=a.accounting_id where a.openid=:openid',[':openid'=>$openid]);
            //发送消息
            $post = json_encode([
                'call'=>'confirmCollectionNotice',
                'first' =>'商户（'.$merch_info['user_name'].'）对上月无需记账，请知悉！',
                'keyword1' => '记账审核',
                'keyword2' => '无需记账',
                'keyword3' => date('Y-m-d H:i:s',time()),
                'remark' => '',
                'url' => '',
                'openid' => $merch_info['openid'],
                'temp_id' => 'SVVs5OeD3FfsGwW0PEfYlZWetjScIT8kDxht5tlI1V8'
            ]);
            ihttp_request('https://shop.gogo198.cn/api/sendwechattemplatenotice.php', $post);
            
            $post2 = json_encode([
                'call'=>'confirmCollectionNotice',
                'first' =>'商户（'.$merch_info['user_name'].'）对上月无需记账，请知悉！',
                'keyword1' => '记账审核',
                'keyword2' => '无需记账',
                'keyword3' => date('Y-m-d H:i:s',time()),
                'remark' => '',
                'url' => '',
                'openid' => 'ov3-bt8keSKg_8z9Wwi-zG1hRhwg',//ov3-bt8keSKg_8z9Wwi-zG1hRhwg ov3-bt5vIxepEjWc51zRQNQbFSaQ
                'temp_id' => 'SVVs5OeD3FfsGwW0PEfYlZWetjScIT8kDxht5tlI1V8'
            ]);
            ihttp_request('https://shop.gogo198.cn/api/sendwechattemplatenotice.php', $post2);
            pdo_insert('customs_accounting_notbook',['openid'=>$openid,'pre_month'=>$pre_month,'notice_time'=>time()]);
            show_json(1,['msg'=>'确认无需记账成功！','ids'=>$batch_ids]);
        }else{
            show_json(-1,['msg'=>'你已通知管理员上月不记账，不用重复点击！','ids'=>$batch_ids]);
        }
        
    }else{
        include $this->template('account/select');   
    }
}