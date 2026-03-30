<?php
// 模块LTD提供
if (!defined('IN_IA')) {
    exit('Access Denied');
}

global $_W;
global $_GPC;

$op = !empty($_GPC['op'])?trim($_GPC['op']):'display';
$openid = $_W['openid'];
$notice_manage = 'ov3-bt8keSKg_8z9Wwi-zG1hRhwg';//ov3-bt8keSKg_8z9Wwi-zG1hRhwg  ov3-bt5vIxepEjWc51zRQNQbFSaQ
//step1:只有老板和我才能够进入管理端
if($openid!='ov3-bt8keSKg_8z9Wwi-zG1hRhwg' && $openid!='ov3-bt5vIxepEjWc51zRQNQbFSaQ'){
    echo '<h3>抱歉，您不是管理员！</h3>';exit;
}

if($op=='display'){
    if($_W['isajax']){
        $id = intval($_GPC['id']);
        $typ = intval($_GPC['type']);
        $date = trim($_GPC['date']);
        $dateStart = strtotime($date.'-01 00:00:00');
        $dateEnd = strtotime(date('Y-m-d H:i:s', mktime(23, 59, 59, date('m', strtotime($date))+1, 00)));
        if($typ==3){
            if($id){
                //1、处理批次号状态；
                $res = pdo_update('customs_accounting_register',['status'=>8],['id'=>$id]);
                //2、发送消息给商户；
                $info = pdo_fetch('select * from '.tablename('customs_accounting_register').' where id=:id',[':id'=>$id]);
                $post = json_encode([
                    'call'=>'confirmCollectionNotice',
                    'first' =>'您好，您的汇总批次号为('.$info['batch_num'].')已确认快递，请等待快递员上门揽件。',
                    'keyword1' => '凭证审核',
                    'keyword2' => '已确认快递',
                    'keyword3' => date('Y-m-d H:i:s',time()),
                    'remark' => '点击查看详情',
                    'url' => 'https://shop.gogo198.cn/app/index.php?i=3&c=entry&do=account&m=sz_yi&p=register&op=voucher_manage&view=2&date='.date('Y-m',time()),
                    'openid' => $info['openid'],
                    'temp_id' => 'SVVs5OeD3FfsGwW0PEfYlZWetjScIT8kDxht5tlI1V8'
                ]);

                ihttp_request('https://shop.gogo198.cn/api/sendwechattemplatenotice.php', $post);
                if($res){
                    show_json(1,['msg'=>'确认成功!']);
                }
            }
        }elseif($typ==4){
            //拒绝快递，拒绝微信
            $remark = trim($_GPC['remark']);
            $info = pdo_fetch('select * from '.tablename('customs_accounting_register').' where id=:id',[':id'=>$id]);
            pdo_update('customs_accounting_register',['remark'=>$remark,'status'=>'-3'],['id'=>$id]);
            $post = json_encode([
                'call'=>'confirmCollectionNotice',
                'first' =>'您好，您的汇总批次号为('.$info['batch_num'].')被拒绝，请与管理员联系。',
                'keyword1' => '凭证审核',
                'keyword2' => '拒绝',
                'keyword3' => date('Y-m-d H:i:s',time()),
                'remark' => '拒绝原因：'.$remark,
                'url' => 'https://shop.gogo198.cn/app/index.php?i=3&c=entry&do=account&m=sz_yi&p=register&op=voucher_manage&view=2&date='.date('Y-m',time()),
                'openid' => $info['openid'],
                'temp_id' => 'SVVs5OeD3FfsGwW0PEfYlZWetjScIT8kDxht5tlI1V8'
            ]);

            $res = ihttp_request('https://shop.gogo198.cn/api/sendwechattemplatenotice.php', $post);
            if($res){
                show_json(1,['msg'=>'拒绝成功!']);
            }
        }elseif($typ==1){
            //待确认
            $list = pdo_fetchall('select a.id,a.batch_num,a.ids,b.user_name,a.submit_method,a.method,a.createtime from '.tablename('customs_accounting_register').' a left join '.tablename('decl_user').' b on b.openid=a.openid where a.status=7 and ( a.createtime >= '.$dateStart.' and a.createtime <= '.$dateEnd.' ) order by a.createtime desc');

            foreach($list as $k=>$v){
                $ids = explode(',',$v['ids']);
                foreach($ids as $kk=>$vv){
                    if(!empty($vv)){
                        $list[$k]['voucher_info'][$kk] = pdo_fetch('select * from '.tablename('customs_special_record').' where id=:id',[':id'=>$vv]);
                    }
                }
                $list[$k]['createtime'] = date('Y年m月d日',$v['createtime']);
            }
            show_json(1,['data'=>$list]);
        }elseif($typ==2){
            //已确认
            $list = pdo_fetchall('select a.id,a.batch_num,a.ids,b.user_name,a.submit_method,a.method,a.createtime from '.tablename('customs_accounting_register').' a left join '.tablename('decl_user').' b on b.openid=a.openid where a.status=1 and ( a.createtime >= '.$dateStart.' and a.createtime <= '.$dateEnd.' ) order by a.createtime desc');

            foreach($list as $k=>$v){
                $ids = explode(',',$v['ids']);
                foreach($ids as $kk=>$vv){
                    if(!empty($vv)){
                        $list[$k]['voucher_info'][$kk] = pdo_fetch('select * from '.tablename('customs_special_record').' where id=:id',[':id'=>$vv]);
                    }
                }
                $list[$k]['createtime'] = date('Y年m月d日',$v['createtime']);
            }
            show_json(1,['data'=>$list]);
        }

    }else{
        $list = pdo_fetchall('select a.id,a.batch_num,a.ids,b.user_name,a.submit_method,a.method,a.createtime from '.tablename('customs_accounting_register').' a left join '.tablename('decl_user').' b on b.openid=a.openid where a.status=7 order by a.createtime desc');

        foreach($list as $k=>$v){
            $ids = explode(',',$v['ids']);
            foreach($ids as $kk=>$vv){
                if(!empty($vv)){
                    $list[$k]['voucher_info'][$kk] = pdo_fetch('select * from '.tablename('customs_special_record').' where id=:id',[':id'=>$vv]);
                }
            }
        }
        include $this->template('account/manage');   
    }
}elseif($op=='email'){
    //发送电子邮件
    if($_W['isajax']){
        load()->func('communication');
        $batch_id = intval($_GPC['batch_id']);
        // $email = trim($_GPC['email']);

        //查找汇总批次号
        $batch_info = pdo_fetch('select a.batch_num,b.user_name,c.openid from '.tablename('customs_accounting_register').' a left join '.tablename('decl_user').' b on b.openid=a.openid left join '.tablename('mc_mapping_fans').' c on c.fanid=b.accounting_id where a.id=:id',[':id'=>$batch_id]);
        //$infourl = 'https://shop.gogo198.cn/app/index.php?i=3&c=entry&do=account&m=sz_yi&p=bookkeeping&op=download_voucher_batch&batch_id='.$batch_id;
        $infourl = 'https://shop.gogo198.cn/app/index.php?i=3&c=entry&do=account&m=sz_yi&p=bookkeeping&op=voucher_signAll&batch_id='.$batch_id.'&date='.date('Y-m',time());
        //发送电邮通知
        $post = json_encode([
            'call'=>'collectionNotice',
            'first' =>'凭证汇总号['.$batch_info['batch_num'].']信息已收到，请点击查看与下载凭证！',
            'keyword1' => $batch_info['user_name'],
            'keyword2' => '微信签收',
            'keyword3' => date('Y-m-d H:i:s',time()),
            'keyword4' => $batch_info['batch_num'],
            'keyword5' => '微信签收',
            'remark' => '',
            'url' => $infourl,
            'openid' => $batch_info['openid'],
            'temp_id' => 'VncuMA4q8QY-2qhz7j-H7_ZFgA84l324QifPgndtCGI'
        ]);

        ihttp_request('https://shop.gogo198.cn/api/sendwechattemplatenotice.php', $post);
        
        $res = pdo_update('customs_accounting_register',['status'=>1,'status2'=>2],['id'=>$batch_id]);
        if($res){
            show_json(1,['msg'=>'发送成功，已通知记账端！']);    
        }
        return;
        //2022.02.19取消电邮通知
        //链接生成二维码
        $errorCorrectionLevel = 'L';//错误等级，忽略
        $matrixPointSize = 4;
        $url = 'https://shop.gogo198.cn/foll/public/?s=api/sendemail/index';
        require_once IA_ROOT.'/addons/sz_yi/phpqrcode.php';
        $path = '/addons/sz_yi/static/QRcode/'; //储存的地方
        if (!is_dir(IA_ROOT.$path)) {
            load()->func('file');
            mkdirs(IA_ROOT.$path); //创建文件夹
        }

//        $infourl = 'https://shop.gogo198.cn/app/index.php?i=3&c=entry&do=account&m=sz_yi&p=bookkeeping&op=download_voucher_batch&batch_id='.$batch_id;
        $infourl = 'https://shop.gogo198.cn/app/index.php?i=3&c=entry&do=account&m=sz_yi&p=bookkeeping&op=voucher_sign';
        $filename =  $path.time().'.png'; //图片文件
        QRcode::png($infourl, IA_ROOT.$filename, $errorCorrectionLevel, $matrixPointSize, 2); //生成图片
        $orderFileCcollectionImg = str_replace('/www/wwwroot/gogo','https://shop.gogo198.cn',IA_ROOT.$filename);
        
        //发送电子邮件
        $post_data = [
            'title' =>$batch_info['batch_num'],
            'email' =>$email,
            'content' => '打开链接，识别二维码： '.$orderFileCcollectionImg
        ];
        $ch = curl_init($url);//地址
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");//请求方法
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);//请求数据
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);//成功返回时返回数据，失败返回false
        curl_setopt($ch, CURLOPT_TIMEOUT, 20);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);//禁止curl验证对等证书
        //curl_setopt($ch, CURLOPT_HTTPHEADER,array('Content-Type: text/plain'));// 必须声明请求头
        $output = curl_exec($ch);//执行curl
        if (curl_errno($ch)) {
            //echo 'Curl error: ' . curl_error($ch);
            $code = curl_errno($ch);
            $msg = curl_error($ch);
            return ['result' => false, 'errorCode' => $code, 'description' => $msg];
        }
        curl_close($ch);//关闭句柄

        $res =  json_decode($output, true);
        if($res['status']==1){
            //发送电邮通知
            $post = json_encode([
                'call'=>'collectionNotice',
                'first' =>'您有一封邮件已收到，请在电脑端点击查看与下载凭证！',
                'keyword1' => $batch_info['user_name'],
                'keyword2' => '电子邮件',
                'keyword3' => date('Y-m-d H:i:s',time()),
                'keyword4' => $batch_info['batch_num'],
                'keyword5' => '电子邮件',
                'remark' => '',
                'url' => $infourl,
                'openid' => $batch_info['openid'],
                'temp_id' => 'VncuMA4q8QY-2qhz7j-H7_ZFgA84l324QifPgndtCGI'
            ]);

            ihttp_request('https://shop.gogo198.cn/api/sendwechattemplatenotice.php', $post);
            
            $res = pdo_update('customs_accounting_register',['status'=>1,'status2'=>2],['id'=>$batch_id]);
            if($res){
                show_json(1,['msg'=>'发送成功，已通知记账端！']);    
            }
        }else{
            show_json(-1,['msg'=>'发送失败，请检查邮箱地址！']);
        }
    }
}elseif($op=='menu'){
    //管理员菜单
    include $this->template('account/manager_menu');
}elseif($op=='confirm_manage'){
    //管理员菜单
    include $this->template('account/confirm_manage');
}elseif($op=='accounting_config'){
    //记账配置
    if($_W['isajax']){
        $user_id = intval($_GPC['user_id']);
        $data = [
            'accounting_id' => intval($_GPC['accounting_id']),
            'taxes_id' => trim($_GPC['taxes_id'])
        ];
        if($user_id>0){
            $res = pdo_update('decl_user',$data,['id'=>$user_id]);
            if($res){
                show_json(1,['msg'=>'配置成功']);
            }
        }
    }else{
        //查询所有商户
        $decl_user = pdo_fetchall('select user_name,id from '.tablename('decl_user').' where user_status=0 and openid <> "" order by id desc');
        //查询所有会计师
        $accounting = pdo_fetchall('select fanid,nickname,openid from '.tablename('mc_mapping_fans').' where is_accounting=1 order by fanid desc');
        //查询税种
        $tax_category = pdo_fetchall('select * from '.tablename('customs_accounting_tax_category').' where pid=0');
        include $this->template('account/accounting_config');
    }
}elseif($op=='reconciliation'){
    //对账确认
    if($_W['isajax']){
        $typ=intval($_GPC['typ']);
        $date = trim($_GPC['date']);
        $dateStart = strtotime($date.'-01 00:00:00');
        $dateEnd = strtotime(date('Y-m-d H:i:s', mktime(23, 59, 59, date('m', strtotime($date))+1, 00)));
        if($typ==1 || $typ==2){
            //查看所有商户的对账情况

            $my_merchant = pdo_fetchall('select a.*,b.user_name,b.id as uid from '.tablename('customs_accounting_reconciliation').' a left join '.tablename('decl_user').' b on b.openid=a.openid where a.reconciliation_date=:dat order by id desc',[':dat'=>$date]);
            show_json(1,['data'=>$my_merchant]);
        }
    }
    include $this->template('account/reconciliation_manage');
}elseif($op=='personal_addReduce'){
    //增减确认
    if($_W['isajax']){
        $typ=intval($_GPC['typ']);
        $date = trim($_GPC['date']);
//        $dateStart = strtotime($date.'-01 00:00:00');
//        $dateEnd = strtotime(date('Y-m-d H:i:s', mktime(23, 59, 59, date('m', strtotime($date))+1, 00)));
        if($typ==1 || $typ==2) {
            //查看所有商户的对账情况
            
            $condition = '';
            if($typ==1){
                $condition = ' and a.status=0';
            }elseif($typ==2){
                $condition = ' and (a.status=1 or a.status=2)';
            }

            $my_merchant['list'] = pdo_fetchall('select a.*,b.user_name from '.tablename('customs_accounting_social_tax').' a left join '.tablename('decl_user').' b on b.openid=a.openid where a.opera_date="'.$date.'"'.$condition.' order by a.id desc');
            
            foreach($my_merchant['list'] as $k=>$v){
//                $my_merchant['list'][$k]['opera_date'] = str_replace('-','年',$v['opera_date']).'月';
                $my_merchant['list'][$k]['info_file'] = json_decode($v['info_file'],true);
                if($v['type']==1){
                    $my_merchant['list'][$k]['typeName'] = '社保';
                }elseif($v['type']==2){
                    $my_merchant['list'][$k]['typeName'] = '个税';
                }
                if($v['opera_type']==1){
                    $my_merchant['list'][$k]['operatypeName'] = '增员';
                }elseif($v['opera_type']==2){
                    $my_merchant['list'][$k]['operatypeName'] = '减员';
                }
                if($v['status']==1){
                    $my_merchant['list'][$k]['statusName'] = '增减成功';
                }elseif($v['status']==2){
                    $my_merchant['list'][$k]['statusName'] = '增减失败';
                }
            }
            
            show_json(1,['data'=>$my_merchant]);
        }
    }
    include $this->template('account/personal_addReduce');
}elseif($op=='taxes'){
    //税费确认
    if($_W['isajax']){
        $typ=intval($_GPC['typ']);
        $date = trim($_GPC['date']);

//        $dateStart = strtotime($date.'-01 00:00:00');
//        $dateEnd = strtotime(date('Y-m-d H:i:s', mktime(23, 59, 59, date('m', strtotime($date))+1, 00)));
        if($typ==1 || $typ==2) {
            //查看所有商户的税费情况
            $condition = '';
            if($typ==1){
                $condition = ' and (a.status=0 or a.status=2)';
            }elseif($typ==2){
                $condition = ' and a.status=1';
            }

            $my_merchant['tax_info'] = pdo_fetchall('select a.*,b.user_name,b.id as user_id from '.tablename('customs_accounting_taxation_declare').' a left join '.tablename('decl_user').' b on b.openid=a.openid where a.decl_date="'.$date.'"'.$condition.' order by a.id desc');

            foreach($my_merchant['tax_info'] as $key=>$val){
                $my_merchant['tax_info'][$key]['decl_date'] = str_replace('-','年',$val['decl_date']).'月';
                //营业收入
                $my_merchant['tax_info'][$key]['buss_type'] = explode(',',$val['buss_type']);
                $my_merchant['tax_info'][$key]['buss_project'] = explode(',',$val['buss_project']);
                $my_merchant['tax_info'][$key]['currency'] = explode(',',$val['currency']);
                $my_merchant['tax_info'][$key]['income_price'] = explode(',',$val['income_price']);
                $my_merchant['tax_info'][$key]['income'] = [];
                foreach($my_merchant['tax_info'][$key]['buss_type'] as $k => $v){
                    if(!empty($v)){
                        $currency = pdo_fetchcolumn('select code_name from '.tablename('currency').' where code_value=:code_value',[':code_value'=>$my_merchant['tax_info'][$key]['currency'][$k]]);
                        $currency = explode('(',explode(')',$currency)[0])[1];
                        $my_merchant['tax_info'][$key]['income'][$k] = ['name'=>$v,'project'=>$my_merchant['tax_info'][$key]['buss_project'][$k],'currency'=>$currency,'price'=>number_format($my_merchant['tax_info'][$key]['income_price'][$k],2)];
                    }
                }

                //营运支出
                $my_merchant['tax_info'][$key]['expend_cate'] = explode(',',$val['expend_cate']);
                $my_merchant['tax_info'][$key]['expend_name'] = explode(',',$val['expend_name']);
                $my_merchant['tax_info'][$key]['expend_currency'] = explode(',',$val['expend_currency']);
                $my_merchant['tax_info'][$key]['expend_price'] = explode(',',$val['expend_price']);
                $my_merchant['tax_info'][$key]['cost'] = [];
                foreach($my_merchant['tax_info'][$key]['expend_cate'] as $k => $v){
                    if(!empty($v)){
                        if($v==1){
                            $name = '成本';
                        }else if($v==2){
                            $name = '费用';
                        }else if($v==3){
                            $name = '损失';
                        }
                        $currency = pdo_fetchcolumn('select code_name from '.tablename('currency').' where code_value=:code_value',[':code_value'=>$my_merchant['tax_info'][$key]['expend_currency'][$k]]);
                        $currency = explode('(',explode(')',$currency)[0])[1];
                        $my_merchant['tax_info'][$key]['cost'][$k] = ['name'=>$name,'project'=>$my_merchant['tax_info'][$key]['expend_name'][$k],'currency'=>$currency,'price'=>number_format($my_merchant['tax_info'][$key]['cost_price'][$k],2)];
                    }
                }

                //税金
                $my_merchant['tax_info'][$key]['taxes_id'] = explode(',',$val['taxes_id']);
                $my_merchant['tax_info'][$key]['taxes_cate_info'] = explode(',',$val['taxes_cate_info']);
                $my_merchant['tax_info'][$key]['taxes_currency'] = explode(',',$val['taxes_currency']);
                $my_merchant['tax_info'][$key]['taxes_price'] = explode(',',$val['taxes_price']);
                $my_merchant['tax_info'][$key]['taxes'] = [];
                foreach($my_merchant['tax_info'][$key]['taxes_id'] as $k => $v){
                    if(!empty($v)){
                        $taxes_name = pdo_fetchcolumn('select name from '.tablename('customs_accounting_tax_category').' where id=:id',[':id'=>$v]);
                        $project = pdo_fetchcolumn('select name from '.tablename('customs_accounting_tax_category').' where id=:id',[':id'        =>$my_merchant['tax_info'][$key]['taxes_cate_info'][$k]]);
                        $currency = pdo_fetchcolumn('select code_name from '.tablename('currency').' where code_value=:code_value',[':code_value'=>$my_merchant['tax_info'][$key]['taxes_currency'][$k]]);
                        $currency = explode('(',explode(')',$currency)[0])[1];
                        $my_merchant['tax_info'][$key]['taxes'][$k] = ['name'=>$taxes_name,'project'=>$project,'currency'=>$currency,'price'=>number_format($my_merchant['tax_info'][$key]['taxes_price'][$k],2)];
                    }
                }
            }
            
            show_json(1,['data'=>$my_merchant]);
        }
    }
    include $this->template('account/taxes_manage');
}elseif($op=='watch_taxInfo'){
    $tax_id = intval($_GPC['id']);
    $tax_info = pdo_fetch('select a.*,b.user_name,d.name from '.tablename("customs_accounting_taxation_declare").' a left join '.tablename('decl_user').' b on b.openid=a.openid left join '.tablename('enterprise_members').' c on c.openid=b.openid left join '.tablename('enterprise_basicinfo').' d on d.member_id=c.id where a.id=:id',[':id'=>$tax_id]);

        //营业收入
        $income = [];
        $tax_info['buss_type'] = explode(',',$tax_info['buss_type']);
        $tax_info['buss_project'] = explode(',',$tax_info['buss_project']);
        $tax_info['currency'] = explode(',',$tax_info['currency']);
        $tax_info['income_price'] = explode(',',$tax_info['income_price']);
        foreach($tax_info['buss_type'] as $k=>$v){
            if(!empty($v)){
                $currency = pdo_fetchcolumn('select code_name from '.tablename('currency').' where code_value=:code_value',[':code_value'=>$tax_info['currency'][$k]]);
                $currency = explode('(',explode(')',$currency)[0])[1];
                $income[$k] = ['name'=>$v,'project'=>$tax_info['buss_project'][$k],'currency'=>$currency,'price'=>number_format($tax_info['income_price'][$k],2)];
            }
        }

        //营运支出
        $tax_info['expend_cate'] = explode(',',$tax_info['expend_cate']);
        $tax_info['expend_name'] = explode(',',$tax_info['expend_name']);
        $tax_info['expend_currency'] = explode(',',$tax_info['expend_currency']);
        $tax_info['expend_price'] = explode(',',$tax_info['expend_price']);
        $cost = [];
        foreach($tax_info['expend_cate'] as $k => $v){
            if(!empty($v)){
                if($v==1){
                    $name = '成本';
                }else if($v==2){
                    $name = '费用';
                }else if($v==3){
                    $name = '损失';
                }
                $currency = pdo_fetchcolumn('select code_name from '.tablename('currency').' where code_value=:code_value',[':code_value'=>$tax_info['expend_currency'][$k]]);
                $currency = explode('(',explode(')',$currency)[0])[1];
                $cost[$k] = ['name'=>$name,'project'=>$tax_info['expend_name'][$k],'currency'=>$currency,'price'=>number_format($tax_info['expend_price'][$k],2)];
            }
        }

        //税金
        $tax_info['taxes_id'] = explode(',',$tax_info['taxes_id']);
        $tax_info['taxes_cate_info'] = explode(',',$tax_info['taxes_cate_info']);
        $tax_info['taxes_currency'] = explode(',',$tax_info['taxes_currency']);
        $tax_info['taxes_price'] = explode(',',$tax_info['taxes_price']);
        $taxes = [];
        foreach($tax_info['taxes_id'] as $k => $v){
            if(!empty($v)){
                $taxes_name = pdo_fetchcolumn('select name from '.tablename('customs_accounting_tax_category').' where id=:id',[':id'=>$v]);
                $project = pdo_fetchcolumn('select name from '.tablename('customs_accounting_tax_category').' where id=:id',[':id'=>$tax_info['taxes_cate_info'][$k]]);
                $currency = pdo_fetchcolumn('select code_name from '.tablename('currency').' where code_value=:code_value',[':code_value'=>$tax_info['taxes_currency'][$k]]);
                $currency = explode('(',explode(')',$currency)[0])[1];
                $taxes[$k] = ['name'=>$taxes_name,'project'=>$project,'currency'=>$currency,'price'=>number_format($tax_info['taxes_price'][$k],2)];
            }
        }

        //本期利润
        $current_profit = explode(',',$tax_info['current_profit']);
        $profit = [];
        foreach($current_profit as $k=>$v){
            $profit[$k]['currency'] = explode('-',$v)[0];
            $profit[$k]['money'] = explode('-',$v)[1];
        }
    include $this->template('account/watch_taxInfo');
}