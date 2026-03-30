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
//$openid='ov3-bt179fnUhamRnInQHNh4lPE0';
//公用模块
if($op=='get_voucher_info'){
    //获取凭证信息
    $id = intval($_GPC['id']);
    $sf_voucher = pdo_fetch('select * from '.tablename('customs_special_record').' where id=:id',[':id'=>$id]);
    $sf_voucher['express_voucher'] = json_decode($sf_voucher['express_voucher'],true);
    if($sf_voucher['type']==1 || $sf_voucher['type']==2){
        if($sf_voucher['method']==1){
            //人工汇总
            foreach($sf_voucher['express_voucher'] as $k => $v){
                foreach ($v as $k2=>$v2){
                    $sf_voucher['express_voucher_down'][$k][$k2] = explode('/',$v2)[4];
                }
            }
            if($sf_voucher['type']==1){
                //收款
                $sf_voucher['voucher_type'] = explode(',',$sf_voucher['voucher_type']);
                foreach($sf_voucher['voucher_type'] as $k2=>$v2){
                    if($v2==1){
                        $sf_voucher['voucher_typeName'][$k2] = '销售发票';    
                    }elseif($v2==2){
                        $sf_voucher['voucher_typeName'][$k2] = '销售收据';    
                    }elseif($v2==3){
                        $sf_voucher['voucher_typeName'][$k2] = '收入证明';    
                    }
                }
            }elseif($sf_voucher['type']==2){
                //付款
                $sf_voucher['voucher_type'] = explode(',',$sf_voucher['voucher_type']);
                foreach($sf_voucher['voucher_type'] as $k2=>$v2){
                    if($v2==1){
                        $sf_voucher['voucher_typeName'][$k2] = '付款发票';    
                    }elseif($v2==2){
                        $sf_voucher['voucher_typeName'][$k2] = '付款收据';    
                    }elseif($v2==3){
                        $sf_voucher['voucher_typeName'][$k2] = '付款证明';    
                    }
                }
            }
        }else{
            //系统汇总
            foreach($sf_voucher['express_voucher'] as $k => $v){
                $sf_voucher['express_voucher_down'][$k] = explode('/',$v)[4];
            }
            if($sf_voucher['voucher_type']==1){
                $sf_voucher['voucher_typeName'] = '发票-'.$sf_voucher['reg_number'];
            }elseif($sf_voucher['voucher_type']==2){
                $sf_voucher['voucher_typeName'] = '收据-'.$sf_voucher['reg_number'];
            }elseif($sf_voucher['voucher_type']==3){
                $sf_voucher['voucher_typeName'] = '形式-'.$sf_voucher['reg_number'];
            }
        }
    }

    //账单
    $sf_voucher['bill_file'] = json_decode($sf_voucher['bill_file'],true);
    if($sf_voucher['type']==3 && $sf_voucher['method']==0){
        foreach($sf_voucher['bill_file'] as $k => $v){
            $sf_voucher['bill_file_down'][$k] = explode('/',$v)[4];
        }
        $sf_voucher['voucher_typeName'] = '账单-'.$sf_voucher['reg_number'];
    }elseif($sf_voucher['type']==3 && $sf_voucher['method']==1){
        foreach($sf_voucher['express_voucher'] as $k => $v){
            $sf_voucher['bill_file'][$k] = $v;
            $sf_voucher['bill_file_down'][$k] = explode('/',$v)[4];
        }
        $sf_voucher['voucher_typeName'] = '账单-'.$sf_voucher['reg_number'];
    }

    //判断当前用户是否管理员、会计、商户
    if($openid=='ov3-bt8keSKg_8z9Wwi-zG1hRhwg' || $openid=='ov3-bt5vIxepEjWc51zRQNQbFSaQ'){
        $sf_voucher['identify']=1;
    }else{
        $is_accounting = pdo_fetch('select fanid from '.tablename('mc_mapping_fans').' where openid=:openid and is_accounting=1',[':openid'=>$openid]);
        if($is_accounting['fanid']>0){
            $sf_voucher['identify']=2;
        }else{
            $sf_voucher['identify']=3;
        }
    }

    show_json(1,['data'=>$sf_voucher]);
}elseif($op=="download_voucher_info"){
    $id = intval($_GPC['id']);
    $identify = intval($_GPC['identify']);
    $list = pdo_fetch('select * from '.tablename('customs_special_record').' where id=:id',[':id'=>$id]);
    $date='';
    if($list['method']==1){
        //人工
        if(!empty($list['express_voucher'])){
            $zip_name = $list['reg_number'];
            $files = [];
            $list['express_voucher'] = json_decode($list['express_voucher'],true);
            if($list['type']==1 || $list['type']==2){
                foreach($list['express_voucher'] as $v){
                    foreach($v as $k2=>$v2){
                        $date = explode('/',$v2);
                        $date = $date[2].'/'.$date[3];
                        array_push($files,IA_ROOT.'/attachment/'.$v2);   
                    }
                }
            }else{
                //账单
                foreach($list['express_voucher'] as $v){
                    $date = explode('/',$v);
                    $date = $date[2].'/'.$date[3];
                    array_push($files,IA_ROOT.'/attachment/'.$v);
                }    
            }
            
            $destination = IA_ROOT.'/attachment/zip/'.$zip_name.'.zip';
            $overwrite = false;
            $valid_files = array();
            if(is_array($files)) {
                foreach($files as $file) {
                    if(file_exists($file)) {
                        $valid_files[] = $file;
                    }
                }
            }
            if(count($valid_files)) {
                if(class_exists('ZipArchive')){
                    $zip = new \ZipArchive();
                }
                if($zip->open($destination,$overwrite ? \ZIPARCHIVE::OVERWRITE : \ZIPARCHIVE::CREATE) !== true) {
                    return false;
                }
               
                foreach($valid_files as $k=> $file) {
                    $file2 = str_replace('/www/wwwroot/gogo/attachment/images/3/'.$date,'',$file);//替换Zip中，文件的路径
                    //更名
    //                    $file2 = explode('.',$file2);//二维数组
    //                    $file2 = str_replace($file2[0],'/'.$zip_name.str_pad($k+1, 2, "0", STR_PAD_LEFT).'.'.$file2[1],$file2[0]);
    //                    $file2 = str_replace('/','/'.$zip_name.'/',$file2);

                    $zip->addFile($file,$file2);
                }

                $zip->close();
                $down_url = str_replace('/www/wwwroot/gogo','https://shop.gogo198.cn/',$destination);
                //记录数据库，xx角色（企业、记账、管理）已批量下载
                if($identify==1){
                    pdo_query('update ims_customs_special_record set guanli_batch_down=guanli_batch_down+1 where id='.$id);
                }elseif($identify==2){
                    pdo_query('update ims_customs_special_record set kuaiji_batch_down=kuaiji_batch_down+1 where id='.$id);
                }elseif($identify==3){
                    pdo_query('update ims_customs_special_record set qiye_batch_down=qiye_batch_down+1 where id='.$id);
                }

    //                header('Location:'.$down_url);
                show_json(1,['data'=>$down_url]);
    //                file_exists($destination);
            }else {
                return false;
            }
        }
    }elseif($list['method']==0){
        //系统
        if(($list['type']==1 || $list['type']==2) && !empty($list['express_voucher'])){
            $zip_name = $list['reg_number'];
            $files = [];
            $list['express_voucher'] = json_decode($list['express_voucher'],true);
            foreach($list['express_voucher'] as $k=>$v){
                $date = explode('/',$v);
                $date = $date[2].'/'.$date[3];
                array_push($files,IA_ROOT.'/attachment/'.$v);
            }
            $destination = IA_ROOT.'/attachment/zip/'.$zip_name.'.zip';
            $overwrite = false;
            $valid_files = array();
            if(is_array($files)) {
                foreach($files as $file) {
                    if(file_exists($file)) {
                        $valid_files[] = $file;
                    }
                }
            }
            if(count($valid_files)) {
                if(class_exists('ZipArchive')){
                    $zip = new \ZipArchive();
                }
                if($zip->open($destination,$overwrite ? \ZIPARCHIVE::OVERWRITE : \ZIPARCHIVE::CREATE) !== true) {
                    return false;
                }
                foreach($valid_files as $k=> $file) {
                    $file2 = str_replace('/www/wwwroot/gogo/attachment/images/3/'.$date,'',$file);//替换Zip中，文件的路径
                    //更名
    //                    $file2 = explode('.',$file2);//二维数组
    //                    $file2 = str_replace($file2[0],'/'.$zip_name.str_pad($k+1, 2, "0", STR_PAD_LEFT).'.'.$file2[1],$file2[0]);
    //                    $file2 = str_replace('/','/'.$zip_name.'/',$file2);

                    $zip->addFile($file,$file2);
                }

                $zip->close();
                $down_url = str_replace('/www/wwwroot/gogo','https://shop.gogo198.cn/',$destination);
                //记录数据库，xx角色（企业、记账、管理）已批量下载
                if($identify==1){
                    pdo_query('update ims_customs_special_record set guanli_batch_down=guanli_batch_down+1 where id='.$id);
                }elseif($identify==2){
                    pdo_query('update ims_customs_special_record set kuaiji_batch_down=kuaiji_batch_down+1 where id='.$id);
                }elseif($identify==3){
                    pdo_query('update ims_customs_special_record set qiye_batch_down=qiye_batch_down+1 where id='.$id);
                }
    //                header('Location:'.$down_url);
    //                return file_exists($destination);
                show_json(1,['data'=>$down_url]);
            }else {
                return false;
            }
        }elseif($list['type']==3 && !empty($list['bill_file'])){
            $zip_name = $list['reg_number'];
            $files = [];
            $list['bill_file'] = json_decode($list['bill_file'],true);
            foreach($list['bill_file'] as $k=>$v){
                $date = explode('/',$v);
                $date = $date[2].'/'.$date[3];
                array_push($files,IA_ROOT.'/attachment/'.$v);
            }
            $destination = IA_ROOT.'/attachment/zip/'.$zip_name.'.zip';
            $overwrite = false;
            $valid_files = array();
            if(is_array($files)) {
                foreach($files as $file) {
                    if(file_exists($file)) {
                        $valid_files[] = $file;
                    }
                }
            }
            if(count($valid_files)) {
                if(class_exists('ZipArchive')){
                    $zip = new \ZipArchive();
                }
                if($zip->open($destination,$overwrite ? \ZIPARCHIVE::OVERWRITE : \ZIPARCHIVE::CREATE) !== true) {
                    return false;
                }
                foreach($valid_files as $k=> $file) {
                    $file2 = str_replace('/www/wwwroot/gogo/attachment/images/3/'.$date,'',$file);//替换Zip中，文件的路径
                    //更名
    //                    $file2 = explode('.',$file2);//二维数组
    //                    $file2 = str_replace($file2[0],'/'.$zip_name.str_pad($k+1, 2, "0", STR_PAD_LEFT).'.'.$file2[1],$file2[0]);
    //                    $file2 = str_replace('/','/'.$zip_name.'/',$file2);

                    $zip->addFile($file,$file2);
                }

                $zip->close();
                $down_url = str_replace('/www/wwwroot/gogo','https://shop.gogo198.cn/',$destination);
                //记录数据库，xx角色（企业、记账、管理）已批量下载
                if($identify==1){
                    pdo_query('update ims_customs_special_record set guanli_batch_down=guanli_batch_down+1 where id='.$id);
                }elseif($identify==2){
                    pdo_query('update ims_customs_special_record set kuaiji_batch_down=kuaiji_batch_down+1 where id='.$id);
                }elseif($identify==3){
                    pdo_query('update ims_customs_special_record set qiye_batch_down=qiye_batch_down+1 where id='.$id);
                }
    //                header('Location:'.$down_url);
    //                return file_exists($destination);
                show_json(1,['data'=>$down_url]);
            }else {
                return false;
            }
        }
    }
}

$accounting = pdo_fetch('select is_accounting,fanid from '.tablename('mc_mapping_fans').' where openid=:openid',[':openid'=>$openid]);

if($accounting['is_accounting']!=1){
    echo '<h3>抱歉，您还没有权限，请联系客服！</h3>';exit;
}

if($op=='display'){
    include $this->template('account/bookkeeping');
}elseif($op=='voucher_sign'){
    //凭证签收-批次号
    $date = trim($_GPC['date']);
    $dateStart = strtotime($date.'-01 00:00:00');
    $dateEnd = strtotime(date('Y-m-d H:i:s', mktime(23, 59, 59, date('m', strtotime($date))+1, 00)));
    if($_W['ispost']){
        $type = intval($_GPC['type']);
        $id = explode(',',$_GPC['id']);
        $reg_id = intval($_GPC['reg_id']);//批次号id

        $data = [];
        if($type==1){
            //未收到
            $data = [
                'status'=>6
            ];
        }elseif($type==2){
            //已收到
            $data = [
                'status'=>7
            ];
        }
        foreach ($id as $k=>$v){
            if(!empty($v)){
                pdo_update('customs_special_record',$data,['id'=>$v]);    
            }
        }

        if(true){
            //签收凭证需要通知客户
            $reg_info = pdo_fetch('select openid,express_num,batch_num,express_id,submit_method from '.tablename('customs_accounting_register').' where id=:id',[':id'=>$reg_id]);
            $merch_info = pdo_fetch('select user_name from '.tablename('decl_user').' where openid=:openid',[':openid'=>$reg_info['openid']]);
            
            $status = '';
            if($data['status']==6){
                $status='未收到';
            }elseif($data['status']==7){
                $status='已收到';
            }
            $count = count($id)-1;

            $msg = '';
            $express_num = '';
            if($reg_info['submit_method']==1){
                //快递提交
                $msg = '（'.$reg_info['express_id'].'）的快递有';
                $express_num = $reg_info['express_num'];
            }elseif($reg_info['submit_method']==2){
                //电邮提交
                $msg = '的电邮有';
                $express_num = $reg_info['batch_num'];
            }

            $post = json_encode([
                'call'=>'sendNewInfoMsg',
                'first' =>'您好，您的汇总批次号['.$reg_info['batch_num'].']有'.$count.'张凭证'.$status.'。',
                'keyword1' => date('Y-m-d H:i:s',time()),
                'keyword2' => $express_num,
                'remark' => '点击查看详情',
                'url' => 'https://shop.gogo198.cn/app/index.php?i=3&c=entry&do=account&m=sz_yi&p=register&op=voucher_manage&view=3&date='.date('Y-m',time()),
                'openid' => $reg_info['openid'],
                'temp_id' => '31Kle9s64IBQwckH9ESmgoDJ1QT75oZVJeXyDJLiqmw'
            ]);

            ihttp_request('https://shop.gogo198.cn/api/sendwechattemplatenotice.php', $post);
            
            //2、通知管理员
            $post2 = json_encode([
                'call'=>'sendNewInfoMsg',
                'first' =>'商户['.$merch_info['user_name'].']的汇总批次号['.$reg_info['batch_num'].']'.$msg.$count.'张凭证'.$status.'。',
                'keyword1' => date('Y-m-d H:i:s',time()),
                'keyword2' => $express_num,
                'remark' => '',
                'url' => '',
                'openid' => $notice_manage,
                'temp_id' => '31Kle9s64IBQwckH9ESmgoDJ1QT75oZVJeXyDJLiqmw'
            ]);

            ihttp_request('https://shop.gogo198.cn/api/sendwechattemplatenotice.php', $post2);

            show_json(1,['msg'=>'凭证状态已确认！']);
        }else{
            show_json(1,['msg'=>'凭证状态无需重复确认！']);
        }
    }else{
        //获取自己的商户
        $my_merchant = pdo_fetchall('select openid from '.tablename('decl_user').' where accounting_id=:id and uniacid=3 and user_status=0',[':id'=>$accounting['fanid']]);
        $list = [];
        if(!empty($my_merchant)){
            foreach($my_merchant as $kk=>$vv){
                $list2 = pdo_fetchall('select a.id,a.batch_num,a.ids,a.express_num,a.express_id,b.user_name,a.submit_method,a.createtime from '.tablename('customs_accounting_register').' a left join '.tablename('decl_user').' b on b.openid=a.openid where a.status=1 and a.openid=:openid  and ( a.createtime >= '.$dateStart.' and a.createtime <= '.$dateEnd.' ) order by a.createtime desc',[':openid'=>$vv['openid']]);

                foreach($list2 as $k=>$v){
                    $list2[$k]['is_show'] = 0;
                    $ids = explode(',',$v['ids']);
                    foreach($ids as $kk2=>$vv2){
                        if(!empty($vv2)){
                            $list2[$k]['voucher_info'][$kk2] = pdo_fetch('select * from '.tablename('customs_special_record').' where id=:id and status=1',[':id'=>$vv2]);
                            if($list2[$k]['voucher_info'][$kk2]['id']>0){
                                $list2[$k]['voucher_info'][$kk2]['is_showQian'] = 0;
                                if($list2[$k]['voucher_info'][$kk2]['status']==1){
                                    $list2[$k]['voucher_info'][$kk2]['is_showQian'] = 1;
                                }
                                $list2[$k]['is_show'] = 1;
                            }
                        }
                    }
                    $list2[$k]['createtime'] = date('Y年m月d日',$v['createtime']);
                }
                $list = array_merge($list,$list2);
            }
        }
        include $this->template('account/voucher_sign');   
    }
}elseif($op=='voucher_signAll'){
    //凭证签收-列表
    $id = intval($_GPC['batch_id']);
    $list = pdo_fetch('select * from '.tablename('customs_accounting_register').' where id=:id',[':id'=>$id]);
    if(!empty($list)){
        $ids = explode(',',$list['ids']);
        foreach($ids as $k=>$v){
            if(!empty($v)){
                $sta = pdo_fetch('select status from '.tablename('customs_special_record').' where id=:id',[':id'=>$v]);
                if($sta['status']==1){
                    $list['voucher_info'][$k] = pdo_fetch('select * from '.tablename('customs_special_record').' where id=:id',[':id'=>$v]);
                    if(empty($list['voucher_info'][$k]['reg_number'])){
                        $list['voucher_info'][$k]['reg_number']='-';
                    }
                }
            }
        }
    }
//    print_r($list);die;
    include $this->template('account/voucher_signAll');
}elseif($op=='voucher_keep'){
    $date = trim($_GPC['date']);
    $dateStart = strtotime($date.'-01 00:00:00');
    $dateEnd = strtotime(date('Y-m-d H:i:s', mktime(23, 59, 59, date('m', strtotime($date))+1, 00)));
    if($_W['isajax']){
        $id = intval($_GPC['id']);
        $type = intval($_GPC['type']);

        if($type==1){
            //已签收未记账
            $my_merchant = pdo_fetchall('select openid from '.tablename('decl_user').' where accounting_id=:id and uniacid=3 and user_status=0',[':id'=>$accounting['fanid']]);

            if(!empty($my_merchant)){
                foreach($my_merchant as $kk=>$vv){
                    $list[$kk] = pdo_fetchall('select a.id,a.batch_num,a.ids,b.user_name from '.tablename('customs_accounting_register').' a left join '.tablename('decl_user').' b on b.openid=a.openid where a.status=1 and a.openid=:openid order by a.createtime desc',[':openid'=>$vv['openid']]);
                    foreach($list[$kk] as $k=>$v){
                        $list[$kk][$k]['is_show']=0;
                        $ids = explode(',',$v['ids']);
                        foreach($ids as $kk2=>$vv2){
                            if(!empty($vv)){
                                $list[$kk][$k]['voucher_info'][$kk2] = pdo_fetch('select * from '.tablename('customs_special_record').' where id=:id and status=7',[':id'=>$vv2]);
                                if($list[$kk][$k]['voucher_info'][$kk2]['id']>0){
                                    $list[$kk][$k]['is_show']=1;
                                }
                            }
                        }
                    }
                }
            }
            show_json(1,['data'=>$list]);
        }elseif($type==2){
            //已复核可记账
            $my_merchant = pdo_fetchall('select openid from '.tablename('decl_user').' where accounting_id=:id and uniacid=3 and user_status=0',[':id'=>$accounting['fanid']]);
            if(!empty($my_merchant)){
                foreach($my_merchant as $kk=>$vv){
                    $list[$kk] = pdo_fetchall('select a.id,a.batch_num,a.ids,b.user_name from '.tablename('customs_accounting_register').' a left join '.tablename('decl_user').' b on b.openid=a.openid where a.status=1 and a.openid=:openid order by a.createtime desc',[':openid'=>$vv['openid']]);
                    foreach($list[$kk] as $k=>$v){
                        $list[$kk][$k]['is_show']=0;
                        $ids = explode(',',$v['ids']);
                        foreach($ids as $kk2=>$vv2){
                            if(!empty($vv)){
                                $list[$kk][$k]['voucher_info'][$kk2] = pdo_fetch('select * from '.tablename('customs_special_record').' where id=:id and status=5',[':id'=>$vv2]);
                                if($list[$kk][$k]['voucher_info'][$kk2]['id']>0){
                                    $list[$kk][$k]['is_show']=1;
                                }
                            }
                        }
                    }
                }
            }
            show_json(1,['data'=>$list]);
        }elseif($type==3){
            //可记账无需复核
            $id = $_GPC['id'];
            $id = explode(',',$id);
            $merch_openid = '';
            foreach($id as $k=>$v){
                if(!empty($v)){
                    $merch_openid = pdo_fetchcolumn('select openid from '.tablename('customs_special_record').' where id=:id',[':id'=>$v]);    
                    pdo_update('customs_special_record',['status'=>4],['id'=>$v]);
                    $info = pdo_fetch('select batch_num from '.tablename('customs_accounting_register').' where find_in_set('.$v.',ids)');
                }    
            }
            
            if(true){
                //发送消息给商户
                $count = count($id)-1;
                $post = json_encode([
                    'call'=>'confirmCollectionNotice',
                    'first' =>'您好，您的汇总批次号['.$info['batch_num'].']有'.$count.'张凭证审核结果为可记账无需复核',
                    'keyword1' => '凭证审核',
                    'keyword2' => '可记账无需复核',
                    'keyword3' => date('Y-m-d H:i:s',time()),
                    'remark' => '点击查看详情',
                    'url' => 'https://shop.gogo198.cn/app/index.php?i=3&c=entry&do=account&m=sz_yi&p=register&op=voucher_manage&view=4&date='.date('Y-m',time()),
                    'openid' => $merch_openid,
                    'temp_id' => 'SVVs5OeD3FfsGwW0PEfYlZWetjScIT8kDxht5tlI1V8'
                ]);

                ihttp_request('https://shop.gogo198.cn/api/sendwechattemplatenotice.php', $post);
                
                //2、通知管理员
                $merch_info = pdo_fetch('select user_name from '.tablename('decl_user').' where openid=:openid',[':openid'=>$merch_openid]);
                $post2 = json_encode([
                    'call'=>'confirmCollectionNotice',
                    'first' =>'商户['.$merch_info['user_name'].']的汇总批次号['.$info['batch_num'].']有'.$count.'张凭证审核结果为可记账无需复核',
                    'keyword1' => '凭证审核',
                    'keyword2' => '可记账无需复核',
                    'keyword3' => date('Y-m-d H:i:s',time()),
                    'remark' => '',
                    'url' => '',
                    'openid' => $notice_manage,
                    'temp_id' => 'SVVs5OeD3FfsGwW0PEfYlZWetjScIT8kDxht5tlI1V8'
                ]);
                ihttp_request('https://shop.gogo198.cn/api/sendwechattemplatenotice.php', $post2);
                
                show_json(1,['msg'=>'确认成功！']);
            }
        }elseif($type==4){
            //不可记账待复核
            $data = [
                'status'=>3,
                'remark'=>trim($_GPC['remark']),
            ];
            $id = $_GPC['id'];
            $id = explode(',',$id);
            $merch_openid = '';
            foreach($id as $k=>$v){
                if(!empty($v)){
                    $merch_openid = pdo_fetchcolumn('select openid from '.tablename('customs_special_record').' where id=:id',[':id'=>$v]);    
                    pdo_update('customs_special_record',$data,['id'=>$v]);
                    //批次号信息
                    $info = pdo_fetch('select batch_num from '.tablename('customs_accounting_register').' where find_in_set('.$v.',ids)');
                }    
            }

            if(true){
                //发送消息给商户
                $count = count($id)-1;
                $post = json_encode([
                    'call'=>'confirmCollectionNotice',
                    'first' =>'您好，您的汇总批次号['.$info['batch_num'].']有'.$count.'张凭证审核结果为不可记账待复核',
                    'keyword1' => '凭证审核',
                    'keyword2' => '不可记账待复核',
                    'keyword3' => date('Y-m-d H:i:s',time()),
                    'remark' => '备注信息：'.$data['remark'],
                    'url' => 'https://shop.gogo198.cn/app/index.php?i=3&c=entry&do=account&m=sz_yi&p=register&op=voucher_manage&view=3&date='.date('Y-m',time()),
                    'openid' => $merch_openid,
                    'temp_id' => 'SVVs5OeD3FfsGwW0PEfYlZWetjScIT8kDxht5tlI1V8'
                ]);

                ihttp_request('https://shop.gogo198.cn/api/sendwechattemplatenotice.php', $post);
                
                //2、通知管理员
                $merch_info = pdo_fetch('select user_name from '.tablename('decl_user').' where openid=:openid',[':openid'=>$merch_openid]);
                $post2 = json_encode([
                    'call'=>'confirmCollectionNotice',
                    'first' =>'商户['.$merch_info['user_name'].']的凭证批次号['.$info['batch_num'].']有'.$count.'张凭证审核结果为不可记账待复核',
                    'keyword1' => '凭证审核',
                    'keyword2' => '不可记账待复核',
                    'keyword3' => date('Y-m-d H:i:s',time()),
                    'remark' => '记账代理的备注信息：'.$data['remark'],
                    'url' => '',
                    'openid' => $notice_manage,
                    'temp_id' => 'SVVs5OeD3FfsGwW0PEfYlZWetjScIT8kDxht5tlI1V8'
                ]);
                ihttp_request('https://shop.gogo198.cn/api/sendwechattemplatenotice.php', $post2);
                
                show_json(1,['msg'=>'填写待复核备注信息成功，等待商户提交补充中！']);
            }
        }elseif($type==5){
            //复核后确认记账
            $id = $_GPC['id'];
            $id = explode(',',$id);
            $merch_openid = '';
            foreach($id as $k=>$v){
                if(!empty($v)){
                    $merch_openid = pdo_fetchcolumn('select openid from '.tablename('customs_special_record').' where id=:id',[':id'=>$v]);    
                    pdo_update('customs_special_record',['status'=>4],['id'=>$v]);

                    $info = pdo_fetch('select batch_num from '.tablename('customs_accounting_register').' where find_in_set('.$v.',ids)');
                }    
            }
            
            // $res = pdo_update('customs_special_record',['status'=>4],['id'=>$id]);
            if(true){
                //发送消息给商户
                $count = count($id)-1;
                $post = json_encode([
                    'call'=>'confirmCollectionNotice',
                    'first' =>'您好，您的凭证批次号（'.$info['batch_num'].'）有'.$count.'张凭证审核结果为确认记账成功',
                    'keyword1' => '凭证审核',
                    'keyword2' => '确认记账成功',
                    'keyword3' => date('Y-m-d H:i:s',time()),
                    'remark' => '点击查看详情',
                    'url' => 'https://shop.gogo198.cn/app/index.php?i=3&c=entry&do=account&m=sz_yi&p=register&op=voucher_manage&view=4&date='.date('Y-m',time()),
                    'openid' => $merch_openid,
                    'temp_id' => 'SVVs5OeD3FfsGwW0PEfYlZWetjScIT8kDxht5tlI1V8'
                ]);
                ihttp_request('https://shop.gogo198.cn/api/sendwechattemplatenotice.php', $post);
                
                //2、通知管理员
                $merch_info = pdo_fetch('select user_name from '.tablename('decl_user').' where openid=:openid',[':openid'=>$merch_openid]);
                $post2 = json_encode([
                    'call'=>'confirmCollectionNotice',
                    'first' =>'商户（'.$merch_info['user_name'].'）的凭证批次号（'.$info['batch_num'].'）有'.$count.'张凭证审核结果为确认记账成功',
                    'keyword1' => '凭证审核',
                    'keyword2' => '确认记账成功',
                    'keyword3' => date('Y-m-d H:i:s',time()),
                    'remark' => '',
                    'url' => '',
                    'openid' => $notice_manage,
                    'temp_id' => 'SVVs5OeD3FfsGwW0PEfYlZWetjScIT8kDxht5tlI1V8'
                ]);
                ihttp_request('https://shop.gogo198.cn/api/sendwechattemplatenotice.php', $post2);
                
                show_json(1,['msg'=>'确认记账成功']);
            }
        }elseif($type==6){
            //复核后拒绝记账
            $data = [
                'status'=>3,
                'remark'=>trim($_GPC['remark']),
            ];
            $id = $_GPC['id'];
            $id = explode(',',$id);
            $merch_openid = '';
            foreach($id as $k=>$v){
                if(!empty($v)){
                    $merch_openid = pdo_fetchcolumn('select openid from '.tablename('customs_special_record').' where id=:id',[':id'=>$v]);
                    pdo_update('customs_special_record',$data,['id'=>$v]);
                    $info = pdo_fetch('select batch_num from '.tablename('customs_accounting_register').' where find_in_set('.$v.',ids)');
                }
            }

            if(true) {
                //发送消息给商户
                $count = count($id) - 1;
                $post = json_encode([
                    'call' => 'confirmCollectionNotice',
                    'first' => '您好，您的凭证批次号（'.$info['batch_num'].'）有' . $count . '张凭证审核结果为复核后不可记账',
                    'keyword1' => '凭证审核',
                    'keyword2' => '复核后不可记账',
                    'keyword3' => date('Y-m-d H:i:s', time()),
                    'remark' => '备注信息：' . $data['remark'],
                    'url' => 'https://shop.gogo198.cn/app/index.php?i=3&c=entry&do=account&m=sz_yi&p=register&op=voucher_manage&view=3&date='.date('Y-m',time()),
                    'openid' => $merch_openid,
                    'temp_id' => 'SVVs5OeD3FfsGwW0PEfYlZWetjScIT8kDxht5tlI1V8'
                ]);

                ihttp_request('https://shop.gogo198.cn/api/sendwechattemplatenotice.php', $post);

                //2、通知管理员
                $merch_info = pdo_fetch('select user_name from ' . tablename('decl_user') . ' where openid=:openid', [':openid' => $merch_openid]);
                $post2 = json_encode([
                    'call' => 'confirmCollectionNotice',
                    'first' => '商户（' . $merch_info['user_name'] . '）的凭证批次号（'.$info['batch_num'].'）有' . $count . '张凭证审核结果为复核后不可记账',
                    'keyword1' => '凭证审核',
                    'keyword2' => '复核后不可记账',
                    'keyword3' => date('Y-m-d H:i:s', time()),
                    'remark' => '记账代理的备注信息：' . $data['remark'],
                    'url' => '',
                    'openid' => $notice_manage,
                    'temp_id' => 'SVVs5OeD3FfsGwW0PEfYlZWetjScIT8kDxht5tlI1V8'
                ]);
                ihttp_request('https://shop.gogo198.cn/api/sendwechattemplatenotice.php', $post2);

                show_json(1, ['msg' => '填写复核拒绝备注信息成功，等待商户提交补充中！']);
            }
        }

    }else{
        //获取自己商户的凭证（‘凭证已收取状态’）
        $my_merchant = pdo_fetchall('select openid from '.tablename('decl_user').' where accounting_id=:id and uniacid=3 and user_status=0',[':id'=>$accounting['fanid']]);
        if(!empty($my_merchant)){
            foreach($my_merchant as $kk=>$vv){
                $list = pdo_fetchall('select a.id,a.batch_num,a.ids,b.user_name from '.tablename('customs_accounting_register').' a left join '.tablename('decl_user').' b on b.openid=a.openid where a.status=3 and a.openid=:openid and ( a.createtime >= '.$dateStart.' and a.createtime <= '.$dateEnd.' ) order by a.createtime desc',[':openid'=>$vv['openid']]);
                foreach($list as $k=>$v){
                    $list[$k]['is_show'] = 0;
                    $ids = explode(',',$v['ids']);
                    foreach($ids as $kk=>$vv){
                        if(!empty($vv)){
                            $list[$k]['voucher_info'][$kk] = pdo_fetch('select * from '.tablename('customs_special_record').' where id=:id and (status=1 or status=3)',[':id'=>$vv]);
                            if($list[$k]['voucher_info'][$kk]['id']>0){
                                $list[$k]['is_show']=1;
                            }
                        }
                    }
                }
            }
        }
        $view = intval($_GPC['view'])>0?intval($_GPC['view']):1;
        include $this->template('account/voucher_keep');
    }
}elseif($op=='voucher_keepAll'){
    $id = intval($_GPC['batch_id']);
    $opera = intval($_GPC['opera']);//1未记账 2已复核可记账
    $list = pdo_fetch('select * from '.tablename('customs_accounting_register').' where id=:id',[':id'=>$id]);
    if(!empty($list)){
        $ids = explode(',',$list['ids']);
        foreach($ids as $k=>$v){
            if(!empty($v)){
                $sta = pdo_fetch('select status from '.tablename('customs_special_record').' where id=:id',[':id'=>$v]);
                if($sta['status']==7 && $opera==1){
                    $list['voucher_info'][$k] = pdo_fetch('select * from '.tablename('customs_special_record').' where id=:id',[':id'=>$v]);
                    if(empty($list['voucher_info'][$k]['reg_number'])){
                        $list['voucher_info'][$k]['reg_number']='-';
                    }
                }elseif($sta['status']==5 && $opera==2){
                    $list['voucher_info'][$k] = pdo_fetch('select * from '.tablename('customs_special_record').' where id=:id',[':id'=>$v]);
                    if(empty($list['voucher_info'][$k]['reg_number'])){
                        $list['voucher_info'][$k]['reg_number']='-';
                    }
                }

            }
        }
    }
    include $this->template('account/voucher_keepAll');
}elseif($op=='voucher_info'){
    //凭证信息
    $id = intval($_GPC['id']);
    $data = pdo_fetch('select * from '.tablename('customs_special_record').' where id=:id',[':id'=>$id]);
    if($data['type']==1){
        //收款登记
        if($data['voucher_type']==1 && $data['voucher_type1']==1){
            $data['voucher_type_name'] = '增值税专用发票';
        }elseif($data['voucher_type']==1 && $data['voucher_type1']==2 && $data['voucher_type1_2']==1){
            $data['voucher_type_name'] = '增值税普通发票(折叠票)';
        }elseif($data['voucher_type']==1 && $data['voucher_type1']==2 && $data['voucher_type1_2']==2){
            $data['voucher_type_name'] = '增值税普通发票（卷式）';
        }elseif($data['voucher_type']==1 && $data['voucher_type1']==3){
            $data['voucher_type_name'] = '增值税电子普通发票';
        }elseif($data['voucher_type']==2 && $data['voucher_type2']==1 && $data['voucher_type2_1']==1){
            $data['voucher_type_name'] = '支票进账单';
        }elseif($data['voucher_type']==2 && $data['voucher_type2']==1 && $data['voucher_type2_1']==2){
            $data['voucher_type_name'] = '托收收账单';
        }elseif($data['voucher_type']==2 && $data['voucher_type2']==1 && $data['voucher_type2_1']==3){
            $data['voucher_type_name'] = '多余款通知单';
        }elseif($data['voucher_type']==2 && $data['voucher_type2']==2 && $data['voucher_type2_2']==1){
            $data['voucher_type_name'] = '收据存根联';
        }elseif($data['voucher_type']==3){
            $data['voucher_type_name'] = '形式发票';
        }

        //收款方式
        if($data['collect_type']==1){
            $data['collect_type']='现金收款';
        }elseif($data['collect_type']==2){
            $data['collect_type']='转账收款';
        }
    }elseif($data['type']==2){
        //付款登记
        if($data['voucher_type']==1 && $data['voucher_type1']==1){
            $data['voucher_type_name'] = '增值税专用发票';
        }elseif($data['voucher_type']==1 && $data['voucher_type1']==2 && $data['voucher_type1_2']==1){
            $data['voucher_type_name'] = '增值税普通发票(折叠票)';
        }elseif($data['voucher_type']==1 && $data['voucher_type1']==2 && $data['voucher_type1_2']==2){
            $data['voucher_type_name'] = '增值税普通发票（卷式）';
        }elseif($data['voucher_type']==1 && $data['voucher_type1']==3){
            $data['voucher_type_name'] = '增值税电子普通发票';
        }elseif($data['voucher_type']==2 && $data['voucher_type2']==1 && $data['voucher_type2_1']==1 && $data['voucher_type3']==1 && $data['voucher_type3_1']==1 && $data['voucher_type4_1']==1){
            $data['voucher_type_name'] = '非税收入统一票据';
        }elseif($data['voucher_type']==2 && $data['voucher_type2']==1 && $data['voucher_type2_1']==1 && $data['voucher_type3']==1 && $data['voucher_type3_1']==1 && $data['voucher_type4_1']==2){
            $data['voucher_type_name'] = '非税收入一般缴款书';
        }elseif($data['voucher_type']==2 && $data['voucher_type2']==1 && $data['voucher_type2_1']==1 && $data['voucher_type3']==1 && $data['voucher_type3_1']==2 && $data['voucher_type4_2']==1){
            $data['voucher_type_name'] = '定额票据';
        }elseif($data['voucher_type']==2 && $data['voucher_type2']==1 && $data['voucher_type2_1']==1 && $data['voucher_type3']==1 && $data['voucher_type3_1']==2 && $data['voucher_type4_2']==2){
            $data['voucher_type_name'] = '非定额票据';
        }elseif($data['voucher_type']==2 && $data['voucher_type2']==1 && $data['voucher_type2_1']==1 && $data['voucher_type3']==2 && $data['voucher_type3_2']==1){
            $data['voucher_type_name'] = '社会团体会费收据';
        }elseif($data['voucher_type']==2 && $data['voucher_type2']==1 && $data['voucher_type2_1']==1 && $data['voucher_type3']==2 && $data['voucher_type3_2']==2){
            $data['voucher_type_name'] = '医疗票据';
        }elseif($data['voucher_type']==2 && $data['voucher_type2']==1 && $data['voucher_type2_1']==1 && $data['voucher_type3']==2 && $data['voucher_type3_2']==3){
            $data['voucher_type_name'] = '捐赠收据';
        }elseif($data['voucher_type']==2 && $data['voucher_type2']==1 && $data['voucher_type2_1']==2 && $data['voucher_type5']==1){
            $data['voucher_type_name'] = '税收缴款书';
        }elseif($data['voucher_type']==2 && $data['voucher_type2']==1 && $data['voucher_type2_1']==2 && $data['voucher_type5']==2){
            $data['voucher_type_name'] = '税收收入退还书';
        }elseif($data['voucher_type']==2 && $data['voucher_type2']==1 && $data['voucher_type2_1']==2 && $data['voucher_type5']==3){
            $data['voucher_type_name'] = '税收完税证明';
        }elseif($data['voucher_type']==2 && $data['voucher_type2']==1 && $data['voucher_type2_1']==2 && $data['voucher_type5']==4 && $data['voucher_type5_1']==1){
            $data['voucher_type_name'] = '税收缴款书(出口货物劳务专用)';
        }elseif($data['voucher_type']==2 && $data['voucher_type2']==1 && $data['voucher_type2_1']==2 && $data['voucher_type5']==4 && $data['voucher_type5_1']==2){
            $data['voucher_type_name'] = '出口货物完税分割单';
        }elseif($data['voucher_type']==2 && $data['voucher_type2']==1 && $data['voucher_type2_1']==2 && $data['voucher_type5']==5){
            $data['voucher_type_name'] = '印花税专用税收票证';
        }elseif($data['voucher_type']==2 && $data['voucher_type2']==1 && $data['voucher_type2_1']==3 && $data['voucher_type6']==1){
            $data['voucher_type_name'] = '海关税款专用缴款书';
        }elseif($data['voucher_type']==2 && $data['voucher_type2']==1 && $data['voucher_type2_1']==3 && $data['voucher_type6']==2){
            $data['voucher_type_name'] = '海关货物滞报金专用票据';
        }elseif($data['voucher_type']==2 && $data['voucher_type2']==2 && $data['voucher_type2_2']==1){
            $data['voucher_type_name'] = '工资结算单';
        }elseif($data['voucher_type']==2 && $data['voucher_type2']==2 && $data['voucher_type2_2']==2){
            $data['voucher_type_name'] = '费用报销单';
        }elseif($data['voucher_type']==2 && $data['voucher_type2']==2 && $data['voucher_type2_2']==3){
            $data['voucher_type_name'] = '借款收据';
        }elseif($data['voucher_type']==2 && $data['voucher_type2']==2 && $data['voucher_type2_2']==4){
            $data['voucher_type_name'] = '领款收据';
        }elseif($data['voucher_type']==3){
            $data['voucher_type_name'] = '形式发票';
        }

        //收款方式
        if($data['collect_type']==1){
            $data['collect_type']='现金付款';
        }elseif($data['collect_type']==2){
            $data['collect_type']='转账付款';
        }
    }

    //分类名称
    if($data['content']==1){
        $data['tax_classify_inp'] = $data['tax_classify_sel'];
    }

    //币种
    $data['currency'] = pdo_fetchcolumn('select code_name from '.tablename('currency').' where code_value=:id',[':id'=>$data['currency']]);
    $data['currency2'] = pdo_fetchcolumn('select code_name from '.tablename('currency').' where code_value=:id',[':id'=>$data['currency2']]);
    $data['currency3'] = pdo_fetchcolumn('select code_name from '.tablename('currency').' where code_value=:id',[':id'=>$data['currency3']]);

    //收/付款状态
    if($data['type']==1){
        $data['type_name2'] = '收款状态';
        if($data['money_status']==1){
            $data['money_status']='已开票，应收款';
        }elseif($data['money_status']==2){
            $data['money_status']='已开票，未收款';
        }elseif($data['money_status']==3){
            $data['money_status']='未开票，预收款';
        }
    }elseif($data['type']==2){
        $data['type_name2'] = '付款状态';
        if($data['money_status']==4){
            $data['money_status']='已收票，应付款';
        }elseif($data['money_status']==5){
            $data['money_status']='已收票，未付款';
        }elseif($data['money_status']==6){
            $data['money_status']='未收票，预付款';
        }
    }
    
    //账户信息
    $data['collect_account'] = pdo_fetch('select * from '.tablename('customs_accounting_account').' where id=:id',[':id'=>$data['collect_account']]);
    if($data['collect_account']['type']==1){
        $data['collect_account']['type'] = '企业账户';
    }elseif($data['collect_account']['type']==2){
        $data['collect_account']['type'] = '个人账户';
    }elseif($data['collect_account']['type']==3){
        $data['collect_account']['type'] = '支付账户';
    }

    //文件
    $data['account_receipt'] = json_decode($data['account_receipt'],true);

    //凭证文件
    $data['express_voucher'] = json_decode($data['express_voucher'],true);

    //补充文件
    $data['other_file'] = json_decode($data['other_file'],true);

    //凭证类型
    if($data['type']==1){
        $data['type_name'] = '收款登记';
    }elseif($data['type']==2){
        $data['type_name'] = '付款登记';
    }elseif($data['type']==3){
        $data['type_name'] = '账单登记';
        $data['bill_account'] = pdo_fetch('select * from '.tablename('customs_accounting_account').' where id=:id',[':id'=>$data['bill_account_id']]);
        //账单文件
        $data['bill_file'] = json_decode($data['bill_file'],true);
    }

    //身份1管理员，2记账端，3企业端
    $identify = 2;

    //人工汇总
    if(($data['type']==1 || $data['type']==2) && $data['method']==1){
        //收款和付款操作
        $data['voucher_type'] = explode(',',$data['voucher_type']);
        $data['attach_cert'] = explode(',',$data['attach_cert']);
        $data['voucher_unit'] = explode(',',$data['voucher_unit']);
    }

    include $this->template('account/voucher_info');
}elseif($op=='reconciliation'){
    //对账管理-商户列表
    
    
    //step1:获取自己的商户
    $my_merchant = pdo_fetchall('select id,openid,user_name from '.tablename('decl_user').' where accounting_id=:id and uniacid=:uni and user_status=0 order by id desc',[':id'=>$accounting['fanid'],':uni'=>$_W['uniacid']]);
    $date = $_GPC['date'];
    include $this->template('account/reconciliation');
}elseif($op=='reconciliation_detail'){
    //对账列表
    $id = intval($_GPC['id']);
    $date = trim($_GPC['date']);
//    $true_date = date('Y-m',strtotime(date('Y-m',$date)." - 1 month"));
    $dateStart = strtotime($date.'-01 00:00:00');
    $dateEnd = strtotime(date('Y-m-d H:i:s', mktime(23, 59, 59, date('m', strtotime($date))+1, 00)));
    $my_merchant = pdo_fetch('select openid,user_name,id from '.tablename('decl_user').' where id=:id and uniacid=:uni and user_status=0',[':id'=>$id,':uni'=>$_W['uniacid']]);
    if($_W['isajax']){
        $batch_ids = intval($_GPC['batch_ids']);
        $typ = intval($_GPC['typ']);
        $id = intval($_GPC['id']);

        if($typ==1 || $typ==2){
            //step1、获取商户
            $my_merchant = pdo_fetch('select openid,user_name from '.tablename('decl_user').' where id=:id and uniacid=:uni and user_status=0',[':id'=>$id,':uni'=>$_W['uniacid']]);
            //待对账、已对账
            //step2:获取凭证汇总批次号下（已确认记账）的所有凭证
            if(!empty($my_merchant)){
                //获取该商户，归纳生成的对账信息
                $my_merchant['batch_info'] = pdo_fetch('select * from '.tablename('customs_accounting_reconciliation').' where openid=:openid and reconciliation_date=:reconciliation_date',[':openid'=>$my_merchant['openid'],':reconciliation_date'=>$date]);
            }
            show_json(1,['data'=>$my_merchant]);
        }elseif($typ==3){
            //对账无误
            $batch_ids = explode(',',$_GPC['batch_ids']);
            $true_date = trim($_GPC['true_date']);
            $recon_id = intval($_GPC['recon_id']);
            if(!empty($recon_id)){
                $batch = pdo_fetch('select user_name from '.tablename('decl_user').' where openid=:openid',[':openid'=>trim($_GPC['openid'])]);
                if($batch){
                    pdo_update('customs_accounting_reconciliation',['reconciliation_status'=>1,'reconciliation_manage_status'=>0,'reconciliation_status2'=>0],['id'=>$recon_id]);
                    $post2 = json_encode([
                        'call'=>'confirmCollectionNotice',
                        'first' => '您好，［'.$batch['user_name'].'］向［记账端］提交的［'.$true_date.'］凭证汇总已完成记账，请确认本月的对账，并尽快与［'.$batch['user_name'].'］发送最终确认。',
                        'keyword1' => '对账审核',
                        'keyword2' => '对账无误',
                        'keyword3' => date('Y-m-d H:i:s',time()),
                        'remark' => '点击查看详情',
                        'url' => 'https://shop.gogo198.cn/app/index.php?i=3&c=entry&do=account&m=sz_yi&p=register&op=reconciliation_info&id='.$recon_id,
                        'openid' => $notice_manage,
                        'temp_id' => 'SVVs5OeD3FfsGwW0PEfYlZWetjScIT8kDxht5tlI1V8'
                    ]);
                    ihttp_request('https://shop.gogo198.cn/api/sendwechattemplatenotice.php', $post2);

                    show_json(1,['msg'=>'确认对账无误成功！']);
                }
            }
        }elseif($typ==4){
            //对账有误
            $col_1_money = explode(',',$_GPC['col_1_money']);
            $col_2_money = explode(',',$_GPC['col_2_money']);
            $col_3_money = explode(',',$_GPC['col_3_money']);
            $pay_1_money = explode(',',$_GPC['pay_1_money']);
            $pay_2_money = explode(',',$_GPC['pay_2_money']);
            $pay_3_money = explode(',',$_GPC['pay_3_money']);

            $col_1_currency = explode(',',$_GPC['col_1_currency']);
            $col_1 = '';$col_2 = '';$col_3 = '';$pay_1 = '';$pay_2 = '';$pay_3 = '';
            foreach($col_1_currency as $k=>$v){
                if(!empty($v) && !empty($col_1_money[$k])){
                    $col_1_currency[$k] = explode(')',$v)[0];
                    $col_1 .= $col_1_currency[$k].')'.$col_1_money[$k].',';
                }
            }
            $col_2_currency = explode(',',$_GPC['col_2_currency']);
            foreach($col_2_currency as $k=>$v){
                if(!empty($v) && !empty($col_2_money[$k])){
                    $col_2_currency[$k] = explode(')',$v)[0];
                    $col_2 .= $col_2_currency[$k].')'.$col_2_money[$k].',';
                }
            }
            $col_3_currency = explode(',',$_GPC['col_3_currency']);
            foreach($col_3_currency as $k=>$v){
                if(!empty($v) && !empty($col_3_money[$k])){
                    $col_3_currency[$k] = explode(')',$v)[0];
                    $col_3 .= $col_3_currency[$k].')'.$col_3_money[$k].',';
                }
            }
            $pay_1_currency = explode(',',$_GPC['pay_1_currency']);
            foreach($pay_1_currency as $k=>$v){
                if(!empty($v) && !empty($pay_1_money[$k])){
                    $pay_1_currency[$k] = explode(')',$v)[0];
                    $pay_1 .= $pay_1_currency[$k].')'.$pay_1_money[$k].',';
                }
            }
            $pay_2_currency = explode(',',$_GPC['pay_2_currency']);
            foreach($pay_2_currency as $k=>$v){
                if(!empty($v) && !empty($pay_2_money[$k])){
                    $pay_2_currency[$k] = explode(')',$v)[0];
                    $pay_2 .= $pay_2_currency[$k].')'.$pay_2_money[$k].',';
                }
            }
            $pay_3_currency = explode(',',$_GPC['pay_3_currency']);
            foreach($pay_3_currency as $k=>$v){
                if(!empty($v) && !empty($pay_3_money[$k])){
                    $pay_3_currency[$k] = explode(')',$v)[0];
                    $pay_3 .= $pay_3_currency[$k].')'.$pay_3_money[$k].',';
                }
            }
            $recon_list = json_encode([$col_1,$col_2,$col_3,$pay_1,$pay_2,$pay_3],true);

            $batch_ids = explode(',',$_GPC['batch_ids']);
            $true_date = trim($_GPC['true_date']);
            $recon_id = intval($_GPC['recon_id']);
            if(!empty($recon_id)) {
                $batch = pdo_fetch('select user_name from ' . tablename('decl_user') . ' where openid=:openid', [':openid' => trim($_GPC['openid'])]);
                if ($batch) {
                    pdo_update('customs_accounting_reconciliation', ['reconciliation_status' => 2, 'reconciliation_list' => $recon_list,'reconciliation_manage_status'=>0,'reconciliation_status2'=>0], ['id' => $recon_id]);
                    $post2 = json_encode([
                        'call' => 'confirmCollectionNotice',
                        'first' => '您好，［' . $batch['user_name'] . '］向［记账端］提交的［' . $true_date . '］凭证已完成记账，请确认本月的对账，并尽快与［' . $batch['user_name'] . '］发送最终确认。',
                        'keyword1' => '对账审核',
                        'keyword2' => '对账有误',
                        'keyword3' => date('Y-m-d H:i:s', time()),
                        'remark' => '',
                        'url' => 'https://shop.gogo198.cn/app/index.php?i=3&c=entry&do=account&m=sz_yi&p=register&op=reconciliation_info&id=' . $recon_id,
                        'openid' => $notice_manage,
                        'temp_id' => 'SVVs5OeD3FfsGwW0PEfYlZWetjScIT8kDxht5tlI1V8'
                    ]);
                    ihttp_request('https://shop.gogo198.cn/api/sendwechattemplatenotice.php', $post2);

                    show_json(1, ['msg' => '确认对账有误成功！']);
                }
            }
        }
    }else{
        include $this->template('account/reconciliation_detail');
    }
}elseif($op=='watch_voucher'){
    //对账管理-查看该批次下的凭证
    $id = intval($_GPC['id']);
    $list = pdo_fetch('select * from '.tablename('customs_accounting_register').' where id=:id',[':id'=>$id]);
    $ids = explode(',',$list['ids']);
    foreach($ids as $k3=>$v3){
        if(!empty($v3)){
            if(intval($_GPC['opera'])==1){
                $list['voucher'][$k3] = pdo_fetch('select * from '.tablename('customs_special_record').' where id=:id',[':id'=>$v3]);
            }else{
//                and (status=4 or status=8 or status=9)
                $list['voucher'][$k3] = pdo_fetch('select * from '.tablename('customs_special_record').' where id=:id ',[':id'=>$v3]);
            }
//            if(empty($list['voucher'][$k3])){
//                unset($list['voucher'][$k3]);
//            }
        }
    }
    
    include $this->template('account/watch_voucher');
}elseif($op=='watch_batch'){
    //查询当月下已记账的批次号
    $uid = intval($_GPC['uid']);
    $id = intval($_GPC['id']);
    $decl_user = pdo_fetch('select user_name,openid from '.tablename('decl_user').' where id=:id',[':id'=>$uid]);
    $list = pdo_fetch('select * from '.tablename('customs_accounting_reconciliation').' where id=:id and openid=:openid',[':id'=>$id,':openid'=>$decl_user['openid']]);
    $list_batchids = explode(',',$list['batch_ids']);
    $batch_info = [];
    foreach($list_batchids as $k=>$v){
        if(!empty($v)){
            $batch_info[$k] = pdo_fetch('select * from '.tablename('customs_accounting_register').' where openid=:openid and id=:id',[':openid'=>$decl_user['openid'],':id'=>$v]);
            $batch_info[$k]['createtime'] = date('Y-m-d',$batch_info[$k]['createtime']);
        }
    }
    include $this->template('account/accounting/watch_batch');
}elseif($op=='watch_bill'){
    //对账管理-查看该批次下的凭证
    $uid = intval($_GPC['uid']);
    $opera = intval($_GPC['opera']);
    $id = intval($_GPC['id']);
    //1、查询该对账单的数据
    $totalPrice = pdo_fetch('select a.*,b.user_name from '.tablename('customs_accounting_reconciliation').' a left join '.tablename('decl_user').' b on b.openid=a.openid where a.id=:id',[':id'=>$id]);
    $totalPrice['reconciliation_sys_list'] = json_decode($totalPrice['reconciliation_sys_list'],true);
    $totalPrice['reconciliation_list'] = json_decode($totalPrice['reconciliation_list'],true);
    if(!empty($totalPrice['reconciliation_list'])){
        foreach($totalPrice['reconciliation_list'] as $k2=>$v2){
            if(!empty($v2)){
                $totalPrice['reconciliation_list'][$k2] = explode(',',$v2);
            }
        }
    }
    $currency = pdo_fetchall('select * from '.tablename('currency').' where 1');
    include $this->template('account/watch_bill');
}elseif($op=="download_voucher_batch"){
    //获取凭证批次信息
    $batch_id = intval($_GPC['batch_id']);
    $list = pdo_fetch('select a.batch_num,a.openid,a.ids,b.user_name,a.status,a.id from '.tablename('customs_accounting_register').' a left join '.tablename('decl_user').' b on b.openid=a.openid where a.id=:id and a.status=1 and a.status2=2',[':id'=>$batch_id]);
    //获取该批次凭证信息
    $ids = explode(',',$list['ids']);
    $list['is_sign']=0;
    foreach($ids as $k=>$v){
        if(!empty($v)){
            $list['voucher_info'][$k] = pdo_fetch('select id,type,reg_number,voucher_type,voucher,attach_cert,account_receipt,express_voucher,status from '.tablename('customs_special_record').' where id=:id',[':id'=>$v]);
            if($list['voucher_info'][$k]['id']>0){
                $list['voucher_info'][$k]['is_showQian'] = 0;
                if($list['voucher_info'][$k]['status']==1){
                    $list['voucher_info'][$k]['is_showQian'] = 1;
                }
                $list['is_sign'] = 1;
            }
        }
    }

    include $this->template('account/download_voucher_batch');
}elseif($op=='reconciliation_induce'){
    //对账归纳
    $user_id = intval($_GPC['id']);
    if(empty($user_id)){
        exit('参数错误');
    }

    $typ = intval($_GPC['typ']);
    $date = trim($_GPC['date']);//2022-03
    $true_date = date('Y-m',strtotime($date." + 1 month"));//2022-04
    $dateStart = strtotime($true_date.'-01 00:00:00');
    $dateEnd = strtotime(date('Y-m-d H:i:s', mktime(23, 59, 59, date('m', strtotime($true_date))+1, 00)));

    if($_W['isajax']){
        if($typ==1){
            //step1、先查询商户信息
            $decl_user = pdo_fetch('select id,user_name,openid from '.tablename('decl_user').' where id=:id',[':id'=>$user_id]);
            $ishaveLog = pdo_fetch('select * from '.tablename('customs_accounting_reconciliation').' where openid=:openid and reconciliation_date=:dat',[':openid'=>$decl_user['openid'],':dat'=>$date]);
            if($ishaveLog['id']>0){
                show_json(-1,['msg'=>'当月['.$date.']已生成对账单，请勿重复生成！如有需要请联系管理员。']);
            }
            //step2、查询该商户当月的凭证批次号
            $list = pdo_fetchall('select * from '.tablename('customs_accounting_register').' where ( createtime >= '.$dateStart.' and createtime <= '.$dateEnd.' ) and openid=:openid and status=1 and status2=2 order by id desc',[':openid'=>$decl_user['openid']]);

            //step3、获取已记账的凭证批次号
            $batch_ids='';
            if(!empty($list)){
                foreach($list as $k=>$v){
                    $list[$k]['isshow']=0;
                    $batch_ids .= $v['id'].',';
                    $ids = explode(',',$v['ids']);
                    //step4:统计金额（已收、未收、预收、已付、未付、预付）
                    foreach($ids as $k3=>$v3){
                        if(!empty($v3)){
                            $list[$k]['voucher'][$k3] = pdo_fetch('select id,type,reg_number,voucher,currency,currency2,money_status,trade_price,trade_price2,status from '.tablename('customs_special_record').' where id=:id and (status=4 or status=8 or status=9 or status=10 or status=11) and `type`!=3',[':id'=>$v3]);
                            if(!empty($list[$k]['voucher'][$k3])){
                                $list[$k]['isshow']=1;
                            }
                        }
                    }
                    if($list[$k]['isshow']==0){
                        $batch_ids = str_replace($v['id'].',','',$batch_ids);
                    }
                }
            }
            show_json(1,['dat'=>$batch_ids,'date'=>$true_date]);
        }else{
            //step1、查询该商户
            $decl_user = pdo_fetch('select id,openid,user_name from '.tablename('decl_user').' where id=:id',[':id'=>$user_id]);
            //1-1、先查看是否已有对账单数据
            $is_have_log = pdo_fetch('select id from '.tablename('customs_accounting_reconciliation').' where openid=:openid and reconciliation_date=:reconciliation_date',[':openid'=>$decl_user['openid'],':reconciliation_date'=>trim($_GPC['batchs_date'])]);
            if($is_have_log){
                show_json(-1,['msg'=>'当月的凭证批次已归纳，无需重复归纳！']);
            }
            //step2、查询该商户当月的凭证批次号
            $batchIds = explode(',',trim($_GPC['batchs_id']));
            $totalPrice = [];
            $code_name = pdo_fetchall('select code_name from '.tablename('currency').' where 1');
            foreach($code_name as $kk=>$vv){
                $code_name2 = explode('(',explode(')',$vv['code_name'])[0])[1];
                $totalPrice['all_money']['col_1'][$code_name2]=0;
                $totalPrice['all_money']['col_2'][$code_name2]=0;
                $totalPrice['all_money']['col_3'][$code_name2]=0;
                $totalPrice['all_money']['pay_1'][$code_name2]=0;
                $totalPrice['all_money']['pay_2'][$code_name2]=0;
                $totalPrice['all_money']['pay_3'][$code_name2]=0;
            }
            foreach($batchIds as $k=>$v){
                if(!empty($v)){
                    $list = pdo_fetch('select * from '.tablename('customs_accounting_register').' where id=:id',[':id'=>$v]);
                    $list_ids = explode(',',$list['ids']);
                    foreach($list_ids as $k2=>$v2){
                        if(!empty($v2)){
                            $voucher = pdo_fetch('select id,type,reg_number,voucher,currency,currency2,money_status,trade_price,trade_price2,status from '.tablename('customs_special_record').' where id=:id and (status=4 or status=8 or status=9 or status=10 or status=11) and `type`!=3',[':id'=>$v2]);
                            if($voucher['id']>0){
                                //step3:统计金额（已收、未收、预收、已付、未付、预付）
                                $currency = $voucher['currency'];
                                $currency2 = $voucher['currency2'];

                                $money_status = $voucher['money_status'];
                                $trade_price = $voucher['trade_price'];//凭证的金额
                                $trade_price2 = $voucher['trade_price2'];//实收/付的金额

                                switch($money_status){
                                    case 1:
                                        //已收
                                        $code_namee = pdo_fetchcolumn('select code_name from '.tablename('currency').' where code_value=:code',[':code'=>$currency2]);
                                        $code_namee = explode('(',explode(')',$code_namee)[0])[1];
                                        $totalPrice['all_money']['col_1'][$code_namee]+=$trade_price2;
                                        break;
                                    case 2:
                                        //未收
                                        $code_namee = pdo_fetchcolumn('select code_name from '.tablename('currency').' where code_value=:code',[':code'=>$currency]);
                                        $code_namee = explode('(',explode(')',$code_namee)[0])[1];
                                        $totalPrice['all_money']['col_2'][$code_namee]+=$trade_price;
                                        break;
                                    case 3:
                                        //预收
                                        $code_namee = pdo_fetchcolumn('select code_name from '.tablename('currency').' where code_value=:code',[':code'=>$currency2]);
                                        $code_namee = explode('(',explode(')',$code_namee)[0])[1];
                                        $totalPrice['all_money']['col_3'][$code_namee]+=$trade_price2;
                                        break;
                                    case 4:
                                        //已付
                                        $code_namee = pdo_fetchcolumn('select code_name from '.tablename('currency').' where code_value=:code',[':code'=>$currency2]);
                                        $code_namee = explode('(',explode(')',$code_namee)[0])[1];
                                        $totalPrice['all_money']['pay_1'][$code_namee]+=$trade_price2;
                                        break;
                                    case 5:
                                        //未付
                                        $code_namee = pdo_fetchcolumn('select code_name from '.tablename('currency').' where code_value=:code',[':code'=>$currency]);
                                        $code_namee = explode('(',explode(')',$code_namee)[0])[1];
                                        $totalPrice['all_money']['pay_2'][$code_namee]+=$trade_price;
                                        break;
                                    case 6:
                                        //预付
                                        $code_namee = pdo_fetchcolumn('select code_name from '.tablename('currency').' where code_value=:code',[':code'=>$currency2]);
                                        $code_namee = explode('(',explode(')',$code_namee)[0])[1];
                                        $totalPrice['all_money']['pay_3'][$code_namee]+=$trade_price2;
                                        break;
                                    default:
                                        break;
                                }
                            }
                        }
                    }
                }
            }
            //step4:清除币种为0的金额
            foreach($code_name as $kk=>$vv){
                $code_name2 = explode('(',explode(')',$vv['code_name'])[0])[1];
                if($totalPrice['all_money']['col_1'][$code_name2]==0){
                    unset($totalPrice['all_money']['col_1'][$code_name2]);
                }
                if($totalPrice['all_money']['col_2'][$code_name2]==0){
                    unset($totalPrice['all_money']['col_2'][$code_name2]);
                }
                if($totalPrice['all_money']['col_3'][$code_name2]==0){
                    unset($totalPrice['all_money']['col_3'][$code_name2]);
                }
                if($totalPrice['all_money']['pay_1'][$code_name2]==0){
                    unset($totalPrice['all_money']['pay_1'][$code_name2]);
                }
                if($totalPrice['all_money']['pay_2'][$code_name2]==0){
                    unset($totalPrice['all_money']['pay_2'][$code_name2]);
                }
                if($totalPrice['all_money']['pay_3'][$code_name2]==0){
                    unset($totalPrice['all_money']['pay_3'][$code_name2]);
                }
            }
            //生成系统统计对账数据
            $data = [
                'openid'=>$decl_user['openid'],
                'batch_ids'=>trim($_GPC['batchs_id']),
                'reconciliation_date'=>trim($_GPC['batchs_date']),
                'reconciliation_sys_list'=>json_encode($totalPrice,true),
                'createtime'=>time()
            ];

            //对账归纳
            $res = pdo_insert('customs_accounting_reconciliation',$data);
            if($res){
                show_json(1,['msg'=>'对账归纳成功，请返回上一页生成账单吧！']);
            }
        }
    }else{
        //step1、先查询商户信息
        $decl_user = pdo_fetch('select id,user_name,openid from '.tablename('decl_user').' where id=:id',[':id'=>$user_id]);
        //step2、查询该商户当月的凭证批次号
        $list = pdo_fetchall('select * from '.tablename('customs_accounting_register').' where ( createtime >= '.$dateStart.' and createtime <= '.$dateEnd.' ) and openid=:openid and status=1 and status2=2 order by id desc',[':openid'=>$decl_user['openid']]);
        //step3、获取已记账的凭证批次号
        $batch_ids='';
        if(!empty($list)){
            foreach($list as $k=>$v){
                $list[$k]['isshow']=0;
                $batch_ids .= $v['id'].',';
                $ids = explode(',',$v['ids']);
                //step4:统计金额（已收、未收、预收、已付、未付、预付）
                foreach($ids as $k3=>$v3){
                    if(!empty($v3)){
                        $list[$k]['voucher'][$k3] = pdo_fetch('select id,type,reg_number,voucher,currency,currency2,money_status,trade_price,trade_price2,status from '.tablename('customs_special_record').' where id=:id and (status=4 or status=8 or status=9 or status=10 or status=11) and `type`!=3',[':id'=>$v3]);
                        if(!empty($list[$k]['voucher'][$k3])){
                            $list[$k]['isshow']=1;
                        }
                    }
                }
                if($list[$k]['isshow']==0){
                    $batch_ids = str_replace($v['id'].',','',$batch_ids);
                }
            }
        }

        include $this->template('account/reconciliation_induce');
    }
}elseif($op=='tax_declare'){
    //税费申报
    $user_id = intval($_GPC['id']);
    $induce_id = intval($_GPC['induce_id']);
    if($_W['isajax']){
        
        if($_GPC['type']==1){
            $user = pdo_fetch('select taxes_id from '.tablename('decl_user').' where id=:id',[':id'=>intval($_GPC['user_id'])]);
            $taxes_cate = pdo_fetchall('select * from '.tablename('customs_accounting_tax_category').' where 1 and FIND_IN_SET(id,:taxes_id)',[':taxes_id'=>$user['taxes_id']]);
            show_json(1,['data'=>$taxes_cate]);
        }elseif($_GPC['type']==2){
            //查询商户有无已提交税费申报和生成对账单
            $user = pdo_fetch('select openid,user_name from '.tablename('decl_user').' where id=:id',[':id'=>intval($_GPC['uid'])]);
            $is_have_recon = pdo_fetch('select * from '.tablename('customs_accounting_reconciliation').' where reconciliation_date=:dat and openid=:openid and reconciliation_status2=1',[':dat'=>$_GPC['date'],':openid'=>$user['openid']]);
            if(empty($is_have_recon['id'])){
                show_json(-1,['msg'=>'请先完成商户['.$user['user_name'].']生成['.$_GPC['date'].']的对账单！']);
            }

            $is_have_tax = pdo_fetch('select id from '.tablename('customs_accounting_taxation_declare').' where decl_date=:dat and openid=:openid',[':dat'=>$_GPC['date'],':openid'=>$user['openid']]);
            if(!empty($is_have_tax['id'])){
                show_json(-1,['msg'=>'当前商户所选月份['.$_GPC['date'].']已进行税费申报，请勿重复申报！']);
            }
            show_json(1,['dat'=>$is_have_recon]);
        }else{
            //商户信息
            $decl = pdo_fetch('select openid,user_name from '.tablename('decl_user').' where id=:id',[':id'=>$_GPC['user_id']]);

            //查询商户有无已提交税费申报和生成对账单
            $is_have_recon = pdo_fetch('select * from '.tablename('customs_accounting_reconciliation').' where reconciliation_date=:dat and openid=:openid and reconciliation_status2=1',[':dat'=>$_GPC['decl_date'],':openid'=>$decl['openid']]);
            if(empty($is_have_recon['id'])){
                show_json(-1,['msg'=>'请先为商户['.$user['user_name'].']生成['.$_GPC['date'].']的对账单！']);
            }
            $is_havelog = pdo_fetch('select id from '.tablename('customs_accounting_taxation_declare').' where openid=:openid and decl_date="'.trim($_GPC['decl_date']).'"',[':openid'=>$decl['openid']]);
            if($is_havelog['id']>0){
                show_json(-1,['msg'=>'当前商户所选月份['.$_GPC['decl_date'].']已进行税费申报，请勿重复申报！']);
            }

            $data = [
                'openid'=>$decl['openid'],
                'induce_id'=>intval($_GPC['induce_id']),
                'decl_date'=>trim($_GPC['decl_date']),
                'buss_type'=>trim($_GPC['buss_type']),
                'buss_project'=>trim($_GPC['buss_project']),
                'currency'=>trim($_GPC['currency']),
                'income_price'=>trim($_GPC['income_price']),
                'expend_cate'=>trim($_GPC['expend_cate']),
                'expend_name'=>trim($_GPC['expend_name']),
                'expend_currency'=>trim($_GPC['expend_currency']),
                'expend_price'=>trim($_GPC['expend_price']),
                'taxes_id'=>trim($_GPC['taxes_id']),
                'taxes_cate_info'=>trim($_GPC['taxes_cate_info']),
                'taxes_currency'=>trim($_GPC['taxes_currency']),
                'taxes_price'=>trim($_GPC['taxes_price']),
                'current_profit'=>trim($_GPC['current_profit']),
                'createtime'=>time(),
                'status'=>0,//0,1登记端确认，2登记端反馈有误
            ];

            $res = pdo_insert('customs_accounting_taxation_declare',$data);
            $insert_id = pdo_insertid();
            if($res){
                //发消息给管理员
                $post = json_encode([
                    'call'=>'confirmCollectionNotice',
                    'first' =>'记账端已提交商户['.$decl['user_name'].']的税费申报，请点击进入查看，并通知登记端！',
                    'keyword1' => '税费申报',
                    'keyword2' => '已提交，待确认',
                    'keyword3' => date('Y-m-d H:i:s',time()),
                    'remark' => '点击查看详情',
                    'url' => 'https://shop.gogo198.cn/app/index.php?i=3&c=entry&do=account&m=sz_yi&p=register&op=taxes_info&id='.$insert_id,
                    'openid' => $notice_manage,
                    'temp_id' => 'SVVs5OeD3FfsGwW0PEfYlZWetjScIT8kDxht5tlI1V8'
                ]);

                ihttp_request('https://shop.gogo198.cn/api/sendwechattemplatenotice.php', $post);
                show_json(1,['msg'=>'创建税费申报成功，请等待管理员审核！']);
            }
        }
    }else{
        if($user_id>0){
            $user = pdo_fetch('select a.user_name,a.openid,a.id,c.name,d.batchs_date,a.taxes_id from '.tablename('decl_user').' a left join '.tablename('enterprise_members').' b on b.openid=a.openid left join '.tablename('enterprise_basicinfo').' c on c.member_id=b.id left join '.tablename('customs_accounting_induce').' d on d.openid=a.openid where a.id=:id and d.id=:induce_id',[':id'=>$user_id,':induce_id'=>$induce_id]);
            $is_single=1;
        }else{
            //选择商户
            $user = pdo_fetchall('select a.user_name,a.openid,a.id,d.name from '.tablename('decl_user').' a left join '.tablename('mc_mapping_fans').' b on b.fanid=a.accounting_id left join '.tablename('enterprise_members').' c on c.openid=a.openid left join '.tablename('enterprise_basicinfo').' d on d.member_id=c.id where b.openid=:openid',[':openid'=>$openid]);
            $is_single=0;
        }
        $currency = pdo_fetchall('select * from '.tablename('currency').' where 1');

        //税种
        if($user_id>0){
            $taxes_cate = pdo_fetchall('select * from '.tablename('customs_accounting_tax_category').' where 1 and FIND_IN_SET(id,:taxes_id)',[':taxes_id'=>$user['taxes_id']]);
        }else{
            $taxes_cate = pdo_fetchall('select * from '.tablename('customs_accounting_tax_category').' where pid=0');
        }

        if(!empty($taxes_cate)){
            foreach($taxes_cate as $k=>$v){
                $taxes_cate_info[$k] = pdo_fetchall('select * from '.tablename('customs_accounting_tax_category').' where pid=:pid',[':pid'=>$v['id']]);
            }
        }
    }
    include $this->template('account/tax_declare');
}elseif($op=='tax_cate_info'){
    $pid = intval($_GPC['pid']);
    $taxes_cate = pdo_fetchall('select * from '.tablename('customs_accounting_tax_category').' where pid=:pid',[':pid'=>$pid]);
    show_json(1,['data'=>$taxes_cate]);
}elseif($op=='expend_cate_info'){
    $id = intval($_GPC['id']);
    if($id==1){
        $data = ['销售成本','销货成本','业务支出','其他耗费'];
    }elseif($id==2){
        $data = ['销售费用','管理费用','财务费用'];
    }elseif($id==3){
        $data = ['资产与存货盘亏','转让财产损失','呆账损失','不可抗力损失','其他损失'];
    }
    show_json(1,['data'=>$data]);
}elseif($op=='tax_declare_edit'){
    //税费申报修改
    if($_W['isajax']){
        $tax_id = intval($_GPC['tax_id']);
        
        $data = [
            'decl_date'=>trim($_GPC['decl_date']),
            'buss_type'=>trim($_GPC['buss_type']),
            'buss_project'=>trim($_GPC['buss_project']),
            'currency'=>trim($_GPC['currency']),
            'income_price'=>trim($_GPC['income_price']),
            'expend_cate'=>trim($_GPC['expend_cate']),
            'expend_name'=>trim($_GPC['expend_name']),
            'expend_currency'=>trim($_GPC['expend_currency']),
            'expend_price'=>trim($_GPC['expend_price']),
            'taxes_id'=>trim($_GPC['taxes_id']),
            'taxes_cate_info'=>trim($_GPC['taxes_cate_info']),
            'taxes_currency'=>trim($_GPC['taxes_currency']),
            'taxes_price'=>trim($_GPC['taxes_price']),
            'current_profit'=>trim($_GPC['current_profit']),
            'status'=>0,//0,1登记端确认，2登记端反馈有误
            'manage_status'=>0,//0,1管理端确认，2管理端反馈有误
        ];

        $res = pdo_update('customs_accounting_taxation_declare',$data,['id'=>$tax_id]);
        if($res){
            //发消息给管理员
            $decl = pdo_fetch('select a.user_name from '.tablename('decl_user').' a left join '.tablename('customs_accounting_taxation_declare').' b on b.openid=a.openid where b.id=:id and a.user_status=0',[':id'=>$tax_id]);
            $post = json_encode([
                'call'=>'confirmCollectionNotice',
                'first' =>'记账端已修改商户['.$decl['user_name'].']的税费申报，请点击进入查看，并通知登记端！',
                'keyword1' => '税费申报',
                'keyword2' => '已修改，待确认',
                'keyword3' => date('Y-m-d H:i:s',time()),
                'remark' => '点击查看详情',
                'url' => 'https://shop.gogo198.cn/app/index.php?i=3&c=entry&do=account&m=sz_yi&p=register&op=taxes_info&id='.$tax_id,
                'openid' => $notice_manage,
                'temp_id' => 'SVVs5OeD3FfsGwW0PEfYlZWetjScIT8kDxht5tlI1V8'
            ]);

            ihttp_request('https://shop.gogo198.cn/api/sendwechattemplatenotice.php', $post);
            show_json(1,['msg'=>'修改税费申报成功，请等待审核！']);
        }
    }else{
        $tax_id = intval($_GPC['id']);

        $tax_info = pdo_fetch('select a.*,d.name,b.user_name,b.taxes_id as user_taxes_id from '.tablename('customs_accounting_taxation_declare').' a left join '.tablename('decl_user').' b on b.openid=a.openid left join '.tablename('enterprise_members').' c on c.openid=b.openid left join '.tablename('enterprise_basicinfo').' d on d.member_id=c.id where a.id=:id',[':id'=>$tax_id]);
        $user = pdo_fetch('select id from '.tablename('decl_user').' where openid=:openid',[':openid'=>$tax_info['openid']]);
        $tax_info['expend_type'] = explode(',',$tax_info['expend_type']);
        //营业收入
        $income = [];
        $tax_info['buss_type'] = explode(',',$tax_info['buss_type']);
        $tax_info['buss_project'] = explode(',',$tax_info['buss_project']);
        $tax_info['currency'] = explode(',',$tax_info['currency']);
        $tax_info['income_price'] = explode(',',$tax_info['income_price']);
        foreach($tax_info['buss_type'] as $k=>$v){
            if(!empty($v)){
                $income[$k] = ['name'=>$v,'project'=>$tax_info['buss_project'][$k],'currency'=>$tax_info['currency'][$k],'price'=>$tax_info['income_price'][$k]];
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
                $cost[$k] = ['name'=>$v,'project'=>$tax_info['expend_name'][$k],'currency'=>$tax_info['expend_currency'][$k],'price'=>$tax_info['expend_price'][$k]];
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
                $taxes[$k] = ['name'=>$v,'project'=>$tax_info['taxes_cate_info'][$k],'currency'=>$tax_info['taxes_currency'][$k],'price'=>$tax_info['taxes_price'][$k]];
            }
        }

        $currency = pdo_fetchall('select * from '.tablename('currency').' where 1');
        //税种
        $taxes_cate = pdo_fetchall('select * from '.tablename('customs_accounting_tax_category').' where 1 and FIND_IN_SET(id,:taxes_id)',[':taxes_id'=>$tax_info['user_taxes_id']]);
        
        if(!empty($taxes_cate)){
            foreach($taxes_cate as $k=>$v){
                $taxes_cate_info[$k] = pdo_fetchall('select * from '.tablename('customs_accounting_tax_category').' where pid=:pid',[':pid'=>$v['id']]);
            }
        }

        //本期利润
        $current_profit = explode(',',$tax_info['current_profit']);
        $profit = [];
        foreach($current_profit as $k=>$v){
            $profit[$k]['currency'] = explode('-',$v)[0];
            $profit[$k]['money'] = explode('-',$v)[1];
        }

        include $this->template('account/tax_declare_edit');
    }
}elseif($op=='calc_taxes_price'){
    //计算利润
    $data = $_GPC;
    if(empty($_GPC['income_price']) || empty($_GPC['cost_price']) || empty($_GPC['taxes_price'])){
        show_json(-1,['msg'=>'请输入内容！']);
    }
    $data['income_currency'] = explode(',',rtrim($data['income_currency'],','));
    $data['income_price'] = explode(',',rtrim($data['income_price'],','));
    $data['cost_currency'] = explode(',',rtrim($data['cost_currency'],','));
    $data['cost_price'] = explode(',',rtrim($data['cost_price'],','));
    $data['taxes_currency'] = explode(',',rtrim($data['taxes_currency'],','));
    $data['taxes_price'] = explode(',',rtrim($data['taxes_price'],','));

    foreach($data['income_currency'] as $k=>$v){
        $code_name = pdo_fetch('select code_name from '.tablename('currency').' where code_value=:code_value',[':code_value'=>$v]);
        $code_name = explode('(',explode(')',$code_name['code_name'])[0])[1];
        $dat['income_info'][$code_name] += $data['income_price'][$k];
    }

    foreach($data['cost_currency'] as $k=>$v){
        $code_name = pdo_fetch('select code_name from '.tablename('currency').' where code_value=:code_value',[':code_value'=>$v]);
        $code_name = explode('(',explode(')',$code_name['code_name'])[0])[1];
        $dat['cost_info'][$code_name] += $data['cost_price'][$k];
    }

    foreach($data['taxes_currency'] as $k=>$v){
        $code_name = pdo_fetch('select code_name from '.tablename('currency').' where code_value=:code_value',[':code_value'=>$v]);
        $code_name = explode('(',explode(')',$code_name['code_name'])[0])[1];
        $dat['taxes_info'][$code_name] += $data['taxes_price'][$k];
    }

    $final_money = [];
    $final_money_str = '';
    foreach($dat['income_info'] as $k=>$v){
        $final_money[][$k] = sprintf('%.2f',$v - floatval($dat['cost_info'][$k]) - floatval($dat['taxes_info'][$k]));
        $final_money_str .= $k.'-'.sprintf('%.2f',$v - floatval($dat['cost_info'][$k]) - floatval($dat['taxes_info'][$k])).',';
    }

    show_json(1,['data'=>$final_money,'data_str'=>rtrim($final_money_str,',')]);
}elseif($op=='socialTax_info'){
    //人员增减审核
    if($_W['isajax']){
        $type = intval($_GPC['type']);
        $date = trim($_GPC['date']);
//        $dateStart = strtotime($date.'-01 00:00:00');
//        $dateEnd = strtotime(date('Y-m-d H:i:s', mktime(23, 59, 59, date('m', strtotime($date))+1, 00)));
        if($type==1 || $type==2){
            $condition = '';
            if($type==1){
                $condition .= ' and a.status=0';
            }elseif($type==2){
                $condition .= ' and (a.status=1 or a.status=2)';
            }
            $list = pdo_fetchall('select a.*,b.user_name from '.tablename('customs_accounting_social_tax').' a left join '.tablename('decl_user').' b on b.openid=a.openid left join '.tablename('mc_mapping_fans').' c on c.fanid=b.accounting_id where c.openid=:openid'.$condition.' and a.opera_date='.$date.' order by a.id desc',[':openid'=>$openid]);
            foreach($list as $k=>$v){
                $list[$k]['info_file'] = json_decode($v['info_file'],true);
                if($v['type']==1){
                    $list[$k]['typeName'] = '社保';
                }elseif($v['type']==2){
                    $list[$k]['typeName'] = '个税';
                }
                if($v['opera_type']==1){
                    $list[$k]['operatypeName'] = '增员';
                }elseif($v['opera_type']==2){
                    $list[$k]['operatypeName'] = '减员';
                }
                if($v['status']==1){
                    $list[$k]['statusName'] = '增减成功';
                }elseif($v['status']==2){
                    $list[$k]['statusName'] = '增减失败';
                }
            }
            show_json(1,['data'=>$list]);
        }elseif($type==3){
            //查询增减员信息表
            $id = intval($_GPC['id']);
            $list = pdo_fetch('select info_file from '.tablename('customs_accounting_social_tax').' where id=:id',[':id'=>$id]);
            $list['info_file'] = json_decode($list['info_file'],true);
            show_json(1,['data'=>$list]);
        }elseif($type==4){
            //审核增减员信息
            $id = intval($_GPC['id']);
            $data = [
                'remark'=>trim($_GPC['remark']),
                'status'=>intval($_GPC['status']),
            ];
            $res = pdo_update('customs_accounting_social_tax',$data,['id'=>$id]);
            if($res){
                $info = pdo_fetch('select a.*,b.user_name from '.tablename('customs_accounting_social_tax').' a left join '.tablename('decl_user').' b on b.openid=a.openid where a.id=:id',[':id'=>$id]);
                $typeName = '';
                if($info['type']==1){
                    $typeName='社保';
                }elseif($info['type']==2){
                    $typeName='个税';
                }
                $operaName = '';
                if($info['opera_type']==1){
                    $operaName='增员';
                }elseif($info['opera_type']==2){
                    $operaName='减员';
                }
                $statusName = '';
                if($info['status']==1){
                    $statusName='成功';
                }elseif($info['status']==2){
                    $statusName='失败';
                }
                //通知登记端、管理端
                $post = json_encode([
                    'call'=>'confirmCollectionNotice',
                    'first' =>'您好！你提交的［'.$typeName.'］［'.str_replace('-','年',$info['opera_date']).'月］的['.$operaName.']申请，记账代理［确认'.$operaName.'］['.$operaName.$statusName.']，详情点击进入查看!',
                    'keyword1' => $typeName.$operaName,
                    'keyword2' => $operaName.$statusName,
                    'keyword3' => date('Y-m-d H:i:s',time()),
                    'remark' => '点击查看详情',
                    'url' => 'https://shop.gogo198.cn/app/index.php?i=3&c=entry&do=account&m=sz_yi&p=register&op=opera_manage&id='.$info['id'].'&opera=2&date='.date('Y-m',time()),
                    'openid' => $info['openid'],
                    'temp_id' => 'SVVs5OeD3FfsGwW0PEfYlZWetjScIT8kDxht5tlI1V8'
                ]);

                ihttp_request('https://shop.gogo198.cn/api/sendwechattemplatenotice.php', $post);

                $post2 = json_encode([
                    'call'=>'confirmCollectionNotice',
                    'first' =>'您好！［'.$info['user_name'].'］向其记账代理提交的['.$typeName.']［'.str_replace('-','年',$info['opera_date']).'月］的['.$operaName.']申请，其记账代理［确认'.$operaName.'］['.$operaName.$statusName.']，详情点击进入查看!',
                    'keyword1' => $typeName.$operaName,
                    'keyword2' => $operaName.$statusName,
                    'keyword3' => date('Y-m-d H:i:s',time()),
                    'remark' => '点击查看详情',
                    'url' => 'https://shop.gogo198.cn/app/index.php?i=3&c=entry&do=account&m=sz_yi&p=register&op=opera_manage&id='.$info['id'].'&opera=2',
                    'openid' => $notice_manage,
                    'temp_id' => 'SVVs5OeD3FfsGwW0PEfYlZWetjScIT8kDxht5tlI1V8'
                ]);

                ihttp_request('https://shop.gogo198.cn/api/sendwechattemplatenotice.php', $post2);
                show_json(1,['msg'=>'审核成功']);
            }
        }
    }else{
        include $this->template('account/socialTax_info');
    }
}elseif($op=='tax_manage'){
    //税费管理
    include $this->template('account/accounting/tax_manage');
}elseif($op=='tax_list'){
    //税费列表
    $date = trim($_GPC['date']);
//    $dateStart = strtotime($date.'-01 00:00:00');
//    $dateEnd = strtotime(date('Y-m-d H:i:s', mktime(23, 59, 59, date('m', strtotime($date))+1, 00)));
    if($_W['isajax']){
        $typ = intval($_GPC['typ']);
        $con = '';
        if($typ==1){
            $con=' and ( b.status=0 or b.status=2 )';
        }elseif($typ==2){
            $con=' and b.status=1';
        }
        //1、查找自己的商户
        $list = pdo_fetchall('select a.user_name,b.* from '.tablename('decl_user').' a left join '.tablename('customs_accounting_taxation_declare').' b on b.openid=a.openid where a.accounting_id=:aid '.$con.'  and b.decl_date= "'.$date.'" order by b.id',[':aid'=>$accounting['fanid']]);
        show_json(1,['data'=>$list]);
    }


    include $this->template('account/accounting/tax_list');
}