<?php
// 模块LTD提供
if (!defined('IN_IA')) {
    exit('Access Denied');
}

/**
 * 风险管理
 * 2022-04-28
 */

global $_W;
global $_GPC;

$openid=$_W['openid'];
$op = !empty($_GPC['op'])?trim($_GPC['op']):'display';

if($op=='display') {
    //风险管理列表
    if($_GPC['pa']==1){
        //获取预申报编号
        $limit = intval($_GPC['limit']);
        $page = intval($_GPC['page']) - 1;
        if ($page != 0) {
            $page = $limit * $page;
        }

        $list = pdo_fetchall('select pre_batch_num,createtime,withhold_status from '.tablename('customs_pre_declare').' where openid=:openid order by id desc limit '.$page.",".$limit,[":openid"=>$openid]);
        foreach($list as $k=>$v){
            $list[$k]['createtime'] = date('Y-m-d H:i',$v['createtime']);
        }
        $count = pdo_fetch('select count(id) as c from '.tablename('customs_pre_declare').' where openid=:openid',[':openid'=>$openid]);
        die(json_encode(['code'=>0,'count'=>$count['c'],'data'=>$list]));
    }

    include $this->template('declare/risk_manage/index');
}elseif($op=='is_select_glist'){
    //是否已选择商品数据
    $batch_num = $_GPC['batch_num'];
    $ishaverisk = pdo_fetch('select id from '.tablename('customs_declare_grisk_head').' where pre_batch_num=:batch_num and openid=:openid',[':batch_num'=>$batch_num,':openid'=>$openid]);

    if(!empty($ishaverisk['id'])){
        //根据关联的商品清单查找数据
        show_json(1);
    }else{
        show_json(-1);
    }
}elseif($op=='select_list'){
    //点击商品列表时，先进入选择清单数据
    $batch_num = $_GPC['pre_batch_num'];


    if($_GPC['pa']==1){
        //检查清单数据有无hscode，价格，规格
        $adjust_method = $_GPC['adjust_method'];
        $connect_glist = 0;
        if($adjust_method=='04-归类清单商品数据'){
            $list = pdo_fetch('select id from '.tablename('customs_declare_risk_list').' where openid=:openid and pre_batch_num=:batch_num and gcode=""',[':openid'=>$openid,':batch_num'=>$batch_num]);
            $connect_glist = 4;
        }else if($adjust_method=='03-调值清单商品数据'){
            $list = pdo_fetch('select a.id from '.tablename('customs_goods_pre_adjust_log').' a left join '.tablename('customs_goods_pre_fill_log').' b on b.id=a.fill_id where a.openid=:openid and a.pre_batch_num=:batch_num and b.gcode=""',[':openid'=>$openid,':batch_num'=>$batch_num]);
            $connect_glist = 3;
        }else if($adjust_method=='02-补缺清单商品数据'){
            $list = pdo_fetch('select id from '.tablename('customs_goods_pre_fill_log').' where openid=:openid and pre_batch_num=:batch_num and gcode=""',[':openid'=>$openid,':batch_num'=>$batch_num]);
            $connect_glist = 2;
        }else if($adjust_method=='01-原始清单商品数据'){
            $list = pdo_fetch('select id from '.tablename('customs_goods_pre_log').' where openid=:openid and pre_batch_num=:batch_num and gcode=""',[':openid'=>$openid,':batch_num'=>$batch_num]);
            $connect_glist = 1;
        }
        if(!empty($list['id'])){
            show_json(-1,['msg'=>'您好，您选择的商品数据还缺少商品编码，请先进行商品归类。']);
        }else{
            //记录风险表的商品清单表头
            $res = pdo_insert('customs_declare_grisk_head',[
                'pre_batch_num'=>$batch_num,
                'openid'=>$openid,
                'connect_glist'=>$connect_glist
            ]);
            if($res){
                show_json(1,['msg'=>'']);
            }
        }
    }

    $method = [];
    //1、原始清单
    $ishave1 = pdo_fetch('select id from '.tablename('customs_goods_pre_log').' where openid=:openid and pre_batch_num=:batch_num',[':openid'=>$openid,':batch_num'=>$batch_num]);
    if(!empty($ishave1['id'])){
        array_push($method,'01-原始清单商品数据');
    }
    //2、补缺清单
    $ishave2 = pdo_fetch('select id from '.tablename('customs_goods_pre_fill_log').' where openid=:openid and pre_batch_num=:batch_num and is_fill=1',[':openid'=>$openid,':batch_num'=>$batch_num]);
    if(!empty($ishave2['id'])){
        array_push($method,'02-补缺清单商品数据');
    }
    //3、调值清单
    $ishave3 = pdo_fetch('select id from '.tablename('customs_goods_pre_adjust_log').' where openid=:openid and pre_batch_num=:batch_num',[':openid'=>$openid,':batch_num'=>$batch_num]);
    if(!empty($ishave3['id'])){
        array_push($method,'03-调值清单商品数据');
    }
    //4、归类清单
    $ishave3 = pdo_fetch('select id from '.tablename('customs_declare_risk_list').' where openid=:openid and pre_batch_num=:batch_num',[':openid'=>$openid,':batch_num'=>$batch_num]);
    if(!empty($ishave3['id'])){
        array_push($method,'04-归类清单商品数据');
    }
    $method = array_reverse($method);

    include $this->template('declare/risk_manage/select_list');
}elseif($op=='goods_list'){
    //商品列表
    $batch_num = trim($_GPC['pre_batch_num']);
    
    //将该批次号下的商品插入敏感或三涉表=风险检测
    $ishavelog = pdo_fetch('select id from '.tablename('customs_declare_grisk_list').' where pre_batch_num=:batch_num and openid=:openid',[':batch_num'=>$batch_num,':openid'=>$openid]);
    if(empty($ishavelog['id'])){
        //插入三涉商品表
        $head_info = pdo_fetch('select connect_glist from '.tablename('customs_declare_grisk_head').' where pre_batch_num=:batch_num and openid=:openid',[':batch_num'=>$batch_num,':openid'=>$openid]);
        if($head_info['connect_glist']==4){
            //归类商品
            $list = pdo_fetchall('select itemName,update_itemName,itemNo,gcode from '.tablename('customs_declare_risk_list').'  where pre_batch_num=:batch_num and openid=:openid order by id desc',[':batch_num'=>$batch_num,':openid'=>$openid]);
        }elseif($head_info['connect_glist']==3){
            //调值商品
            $list = pdo_fetchall('select b.itemName,b.update_itemName,itemNo,b.gcode from '.tablename('customs_goods_pre_adjust_log').' a left join '.tablename('customs_goods_pre_fill_log').' b on b.id=a.fill_id where a.pre_batch_num=:batch_num and a.openid=:openid order by a.id desc',[':batch_num'=>$batch_num,':openid'=>$openid]);
        }elseif($head_info['connect_glist']==2){
            //补缺商品
            $list = pdo_fetchall('select itemName,update_itemName,itemNo,gcode from '.tablename('customs_goods_pre_fill_log').' where openid=:openid and pre_batch_num=:batch_num order by id desc',[':openid'=>$openid,':batch_num'=>$batch_num]);
        }elseif($head_info['connect_glist']==1){
            //原始商品
            $list = pdo_fetchall('select itemName,update_itemName,itemNo,gcode from '.tablename('customs_goods_pre_log').' where openid=:openid and pre_batch_num=:batch_num order by id desc',[':openid'=>$openid,':batch_num'=>$batch_num]);
        }

        foreach($list as $k=>$v) {
            $list[$k]['gname'] = $v['itemName'];
            if(!empty($v['update_itemName'])){
                $list[$k]['gname'] = $v['update_itemName'];
            }
            //2、检查商品数据是否三涉商品
            $hscode = pdo_fetch('select two,tax_info,regulatory_conditions,inspect_quarantine,chapter_info from ' . tablename('customs_hscode_tariffschedule_ssl') . ' where hscode=:hscode', [':hscode' => $v['gcode']]);

            $data = [
                'pre_batch_num'=>$batch_num,
                'openid'=>$openid,
                'itemName'=>$list[$k]['gname'],
                'itemNo'=>$v['itemNo'],
                'gcode'=>$v['gcode'],
            ];
            $reason = '';
            //2.1、判断是否敏感商品
            $res = m('common')->is_sensitive($hscode);
            if($res==1){
                $chapter_info = json_decode($hscode['chapter_info'],true);
                //插入数据表
                $reason = '敏感（'.$chapter_info[0][1].'）,';
                $data=array_merge($data,['is_sensitive'=>1]);
            }
            
            //2.2、判断是否三涉商品
            $tax_info = json_decode($hscode['tax_info'],true);
            if($tax_info[3][1]!='0%' && $tax_info[3][1]!='' && $tax_info[3][1]!='%'){
                //涉税
                $reason .= '涉税,';
                $data=array_merge($data,['is_tax'=>1]);
            }
            //涉证
            $regulatory_conditions = json_decode($hscode['regulatory_conditions'],true);
            if(!empty($regulatory_conditions)){
                $reason .= '涉证,';
                $data=array_merge($data,['is_cert'=>1]);
            }

            //涉检
            $inspect_quarantine = json_decode($hscode['inspect_quarantine'],true);
            if(!empty($inspect_quarantine)){
                $reason .= '涉检';
                $data=array_merge($data,['is_check'=>1]);
            }
            if(!empty($reason)){
                $data=array_merge($data,['reason'=>rtrim($reason,',')]);
                pdo_insert('customs_declare_grisk_list',$data);   
            }
        }
        
        pdo_update('customs_pre_declare',['withhold_status'=>1],['openid'=>$openid,'pre_batch_num'=>$batch_num]);
    }
    
    if($_GPC['pa']==1){
        $limit = intval($_GPC['limit']);
        $page = intval($_GPC['page']) - 1;
        if ($page != 0) {
            $page = $limit * $page;
        }
        $keyword = trim($_GPC['keywords']) ? trim($_GPC['keywords']) : '';
        $condition = '';
        
        //1、找出当前风险管理下的商品清单数据
        $head_info = pdo_fetch('select connect_glist from '.tablename('customs_declare_grisk_head').' where pre_batch_num=:batch_num and openid=:openid',[':batch_num'=>$batch_num,':openid'=>$openid]);
        if($head_info['connect_glist']==4){
            //归类商品
            if($keyword){
                $condition = ' and (itemName like "%'.$keyword.'%" or update_itemName like "%'.$keyword.'%") ';
            }
            $list = pdo_fetchall('select itemName,update_itemName,gcode,itemNo,status from '.tablename('customs_declare_risk_list').'  where pre_batch_num=:batch_num and openid=:openid and status=0 '.$condition.' order by id desc limit '.$page.",".$limit,[':batch_num'=>$batch_num,':openid'=>$openid]);
            foreach($list as $k=>$v){
                $list[$k]['gname'] = $v['itemName'];
                if(!empty($v['update_itemName'])){
                    $list[$k]['gname'] = $v['update_itemName'];
                }
            }
            $count = pdo_fetch('select count(id) as c from '.tablename('customs_declare_risk_list').' where pre_batch_num=:batch_num and openid=:openid and status=0'.$condition,[':openid'=>$openid,':batch_num'=>$batch_num]);
        }elseif($head_info['connect_glist']==3){
            //调值商品
            $condition2='';
            if($keyword){
                $condition = ' and (b.itemName like "%'.$keyword.'%" or b.update_itemName like "%'.$keyword.'%") ';
                $condition2 = ' and (itemName like "%'.$keyword.'%" or update_itemName like "%'.$keyword.'%") ';
            }
            $list = pdo_fetchall('select b.itemName as gname,b.gcode,b.itemNo,status from '.tablename('customs_goods_pre_adjust_log').' a left join '.tablename('customs_goods_pre_fill_log').' b on b.id=a.fill_id where a.pre_batch_num=:batch_num and a.openid=:openid and a.status=0'.$condition.' order by a.id desc limit '.$page.",".$limit,[':batch_num'=>$batch_num,':openid'=>$openid]);
            $count = pdo_fetch('select count(id) as c from '.tablename('customs_goods_pre_fill_log').' where pre_batch_num=:batch_num and openid=:openid and status=0'.$condition2,[':openid'=>$openid,':batch_num'=>$batch_num]);
        }elseif($head_info['connect_glist']==2){
            //补缺商品
            if($keyword){
                $condition = ' and (itemName like "%'.$keyword.'%" or update_itemName like "%'.$keyword.'%") ';
            }
            $list = pdo_fetchall('select itemName as gname,gcode,itemNo,status from '.tablename('customs_goods_pre_fill_log').' where openid=:openid and pre_batch_num=:batch_num and status=0 '.$condition.'order by id desc limit '.$page.",".$limit,[':openid'=>$openid,':batch_num'=>$batch_num]);
            $count = pdo_fetch('select count(id) as c from '.tablename('customs_goods_pre_fill_log').' where pre_batch_num=:batch_num and openid=:openid and status=0'.$condition,[':openid'=>$openid,':batch_num'=>$batch_num]);
        }elseif($head_info['connect_glist']==1){
            //原始商品
            if($keyword){
                $condition = ' and (itemName like "%'.$keyword.'%" or update_itemName like "%'.$keyword.'%") ';
            }
            $list = pdo_fetchall('select itemName as gname,gcode,itemNo,status from '.tablename('customs_goods_pre_log').' where openid=:openid and pre_batch_num=:batch_num and status=0'.$condition.' order by id desc limit '.$page.",".$limit,[':openid'=>$openid,':batch_num'=>$batch_num]);
            $count = pdo_fetch('select count(id) as c from '.tablename('customs_goods_pre_log').' where pre_batch_num=:batch_num and openid=:openid and status=0'.$condition,[':openid'=>$openid,':batch_num'=>$batch_num]);
        }

        $show_tips=0;
        foreach($list as $k=>$v){
            //2、检查商品数据是否存在敏感商品、三涉商品
            $hscode = pdo_fetch('select two,tax_info,regulatory_conditions,inspect_quarantine from '.tablename('customs_hscode_tariffschedule_ssl').' where hscode=:hscode',[':hscode'=>$v['gcode']]);

            //2.1、判断是否敏感商品
            $res = m('common')->is_sensitive($hscode);

            if($res==1){
                //是敏感
                $show_tips=1;break;
            }elseif($res==-1){
                //不是敏感商品，就查是否有三涉商品
                $tax_info = json_decode($hscode['tax_info'],true);
                if($tax_info[3][1]!='0%' && $tax_info[3][1]!='' && $tax_info[3][1]!='%'){
                    //涉税
                    $show_tips=1;break;
                }else{
                    //涉证
                    $regulatory_conditions = json_decode($hscode['regulatory_conditions'],true);
                    if(!empty($regulatory_conditions)){
                        $show_tips=1;break;
                    }else{
                        //涉检
                        $inspect_quarantine = json_decode($hscode['inspect_quarantine'],true);
                        if(!empty($inspect_quarantine)){
                            $show_tips=1;break;
                        }
                    }
                }
            }
        }

        die(json_encode(['code'=>0,'count'=>$count['c'],'data'=>$list,'show_tips'=>$show_tips]));
    }
    include $this->template('declare/risk_manage/goods_list');
}elseif($op=='getSensitiveGoods'){
    //获取敏感商品列表
    $batch_num=$_GPC['pre_batch_num'];
    $ishavelog = pdo_fetch('select id from '.tablename('customs_declare_grisk_list').' where pre_batch_num=:batch_num and openid=:openid',[':batch_num'=>$batch_num,':openid'=>$openid]);

    if(empty($ishavelog['id'])){
        //插入敏感表
        $head_info = pdo_fetch('select connect_glist from '.tablename('customs_declare_grisk_head').' where pre_batch_num=:batch_num and openid=:openid',[':batch_num'=>$batch_num,':openid'=>$openid]);
        if($head_info['connect_glist']==4){
            //归类商品
            $list = pdo_fetchall('select itemName,update_itemName,itemNo,gcode from '.tablename('customs_declare_risk_list').'  where pre_batch_num=:batch_num and openid=:openid order by id desc',[':batch_num'=>$batch_num,':openid'=>$openid]);
            foreach($list as $k=>$v){
                $list[$k]['gname'] = $v['itemName'];
                if(!empty($v['update_itemName'])){
                    $list[$k]['gname'] = $v['update_itemName'];
                }
            }
        }elseif($head_info['connect_glist']==3){
            //调值商品
            $list = pdo_fetchall('select b.itemName as gname,itemNo,b.gcode from '.tablename('customs_goods_pre_adjust_log').' a left join '.tablename('customs_goods_pre_fill_log').' b on b.id=a.fill_id where a.pre_batch_num=:batch_num and a.openid=:openid order by a.id desc',[':batch_num'=>$batch_num,':openid'=>$openid]);
        }elseif($head_info['connect_glist']==2){
            //补缺商品
            $list = pdo_fetchall('select itemName as gname,itemNo,gcode from '.tablename('customs_goods_pre_fill_log').' where openid=:openid and pre_batch_num=:batch_num order by id desc',[':openid'=>$openid,':batch_num'=>$batch_num]);
        }elseif($head_info['connect_glist']==1){
            //原始商品
            $list = pdo_fetchall('select itemName as gname,itemNo,gcode from '.tablename('customs_goods_pre_log').' where openid=:openid and pre_batch_num=:batch_num order by id desc',[':openid'=>$openid,':batch_num'=>$batch_num]);
        }

        foreach($list as $k=>$v) {
            //2、检查商品数据是否存在敏感商品、三涉商品
            $hscode = pdo_fetch('select two,tax_info,regulatory_conditions,inspect_quarantine,chapter_info from ' . tablename('customs_hscode_tariffschedule_ssl') . ' where hscode=:hscode', [':hscode' => $v['gcode']]);

            //2.1、判断是否敏感商品
            $res = m('common')->is_sensitive($hscode);
            if($res==1){
                $chapter_info = json_decode($hscode['chapter_info'],true);
                //插入数据表
                pdo_insert('customs_declare_grisk_list',[
                    'pre_batch_num'=>$batch_num,
                    'openid'=>$openid,
                    'itemName'=>$v['gname'],
                    'itemNo'=>$v['itemNo'],
                    'gcode'=>$v['gcode'],
                    'status'=>1,
                    'reason'=>$chapter_info[0][1]
                ]);
            }
        }
        show_json(1);
    }else{
        show_json(1);
    }
}elseif($op=='sensitiveGoods'){
    $batch_num=$_GPC['pre_batch_num'];
    //敏感商品列表
    if($_GPC['pa']==1){
        $limit = intval($_GPC['limit']);
        $page = intval($_GPC['page']) - 1;
        if ($page != 0) {
            $page = $limit * $page;
        }
        $keyword = trim($_GPC['keywords']) ? trim($_GPC['keywords']) : '';
        $condition = '';
        if($keyword){
            $condition = ' and (itemName like "%'.$keyword.'%") ';
        }
        $list = pdo_fetchall('select itemName as gname,itemNo,gcode,reason from '.tablename('customs_declare_grisk_list').' where openid=:openid and pre_batch_num=:batch_num and is_sensitive=1'.$condition.' order by id desc limit '.$page.",".$limit,[':openid'=>$openid,':batch_num'=>$batch_num]);
        $count = pdo_fetch('select count(id) as c from '.tablename('customs_declare_grisk_list').' where pre_batch_num=:batch_num and openid=:openid and is_sensitive=1'.$condition,[':openid'=>$openid,':batch_num'=>$batch_num]);
        die(json_encode(['code'=>0,'count'=>$count['c'],'data'=>$list]));
    }

    include $this->template('declare/risk_manage/sensitive_goods');
}elseif($op=='getThreeInvolveGoods'){
    //获取三涉商品列表
    $batch_num=$_GPC['pre_batch_num'];
    $ishavelog = pdo_fetch('select id from '.tablename('customs_declare_grisk_list').' where pre_batch_num=:batch_num and openid=:openid and status>1',[':batch_num'=>$batch_num,':openid'=>$openid]);
    if(empty($ishavelog['id'])){
        //插入三涉商品表
        $head_info = pdo_fetch('select connect_glist from '.tablename('customs_declare_grisk_head').' where pre_batch_num=:batch_num and openid=:openid',[':batch_num'=>$batch_num,':openid'=>$openid]);
        if($head_info['connect_glist']==4){
            //归类商品
            $list = pdo_fetchall('select itemName,update_itemName,itemNo,gcode from '.tablename('customs_declare_risk_list').'  where pre_batch_num=:batch_num and openid=:openid order by id desc',[':batch_num'=>$batch_num,':openid'=>$openid]);
        }elseif($head_info['connect_glist']==3){
            //调值商品
            $list = pdo_fetchall('select b.itemName,b.update_itemName,itemNo,b.gcode from '.tablename('customs_goods_pre_adjust_log').' a left join '.tablename('customs_goods_pre_fill_log').' b on b.id=a.fill_id where a.pre_batch_num=:batch_num and a.openid=:openid order by a.id desc',[':batch_num'=>$batch_num,':openid'=>$openid]);
        }elseif($head_info['connect_glist']==2){
            //补缺商品
            $list = pdo_fetchall('select itemName,update_itemName,itemNo,gcode from '.tablename('customs_goods_pre_fill_log').' where openid=:openid and pre_batch_num=:batch_num order by id desc',[':openid'=>$openid,':batch_num'=>$batch_num]);
        }elseif($head_info['connect_glist']==1){
            //原始商品
            $list = pdo_fetchall('select itemName,update_itemName,itemNo,gcode from '.tablename('customs_goods_pre_log').' where openid=:openid and pre_batch_num=:batch_num order by id desc',[':openid'=>$openid,':batch_num'=>$batch_num]);
        }

        foreach($list as $k=>$v) {
            $list[$k]['gname'] = $v['itemName'];
            if(!empty($v['update_itemName'])){
                $list[$k]['gname'] = $v['update_itemName'];
            }
            //2、检查商品数据是否三涉商品
            $hscode = pdo_fetch('select two,tax_info,regulatory_conditions,inspect_quarantine,chapter_info from ' . tablename('customs_hscode_tariffschedule_ssl') . ' where hscode=:hscode', [':hscode' => $v['gcode']]);

            //2.1、判断是否三涉商品
            //不是敏感商品，就查是否有三涉商品
            $tax_info = json_decode($hscode['tax_info'],true);
            if($tax_info[3][1]!='0%' && $tax_info[3][1]!='' && $tax_info[3][1]!='%'){
                //涉税
                pdo_insert('customs_declare_grisk_list',[
                    'pre_batch_num'=>$batch_num,
                    'openid'=>$openid,
                    'itemName'=>$v['gname'],
                    'itemNo'=>$v['itemNo'],
                    'gcode'=>$v['gcode'],
                    'status'=>2,
                    'reason'=>'涉税'
                ]);
            }
            //涉证
            $regulatory_conditions = json_decode($hscode['regulatory_conditions'],true);
            if(!empty($regulatory_conditions)){
                pdo_insert('customs_declare_grisk_list',[
                    'pre_batch_num'=>$batch_num,
                    'openid'=>$openid,
                    'itemName'=>$v['gname'],
                    'itemNo'=>$v['itemNo'],
                    'gcode'=>$v['gcode'],
                    'status'=>3,
                    'reason'=>'涉证'
                ]);
            }

            //涉检
            $inspect_quarantine = json_decode($hscode['inspect_quarantine'],true);
            if(!empty($inspect_quarantine)){
                pdo_insert('customs_declare_grisk_list',[
                    'pre_batch_num'=>$batch_num,
                    'openid'=>$openid,
                    'itemName'=>$v['gname'],
                    'itemNo'=>$v['itemNo'],
                    'gcode'=>$v['gcode'],
                    'status'=>4,
                    'reason'=>'涉检'
                ]);
            }

        }
        show_json(1);
    }else{
        show_json(1);
    }
}elseif($op=='threeInvolveGoods'){
    //三涉商品列表
    $batch_num = $_GPC['pre_batch_num'];
    $condition = '';
    $keyword = trim($_GPC['keywords']) ? trim($_GPC['keywords']) : '';
    if($keyword){
        $condition = ' and (itemName like "%'.$keyword.'%") ';
    }
    
    if($_GPC['pa']==1){
        //涉税
        $limit = intval($_GPC['limit']);
        $page = intval($_GPC['page']) - 1;
        if ($page != 0) {
            $page = $limit * $page;
        }
        $keyword = trim($_GPC['keywords']) ? trim($_GPC['keywords']) : '';
        
        $list = pdo_fetchall('select itemName as gname,itemNo,gcode,reason from '.tablename('customs_declare_grisk_list').' where openid=:openid and pre_batch_num=:batch_num and is_tax=1'.$condition.' order by id desc limit '.$page.",".$limit,[':openid'=>$openid,':batch_num'=>$batch_num]);
        $count = pdo_fetch('select count(id) as c from '.tablename('customs_declare_grisk_list').' where pre_batch_num=:batch_num and openid=:openid and is_tax=1'.$condition,[':openid'=>$openid,':batch_num'=>$batch_num]);
        die(json_encode(['code'=>0,'count'=>$count['c'],'data'=>$list]));
    }

    if($_GPC['pa']==2){
        //涉证
        $limit = intval($_GPC['limit']);
        $page = intval($_GPC['page']) - 1;
        if ($page != 0) {
            $page = $limit * $page;
        }

        $list = pdo_fetchall('select itemName as gname,itemNo,gcode,reason,file from '.tablename('customs_declare_grisk_list').' where openid=:openid and pre_batch_num=:batch_num and is_cert=1'.$condition.' order by id desc limit '.$page.",".$limit,[':openid'=>$openid,':batch_num'=>$batch_num]);
        $count = pdo_fetch('select count(id) as c from '.tablename('customs_declare_grisk_list').' where pre_batch_num=:batch_num and openid=:openid and is_cert=1'.$condition,[':openid'=>$openid,':batch_num'=>$batch_num]);
        die(json_encode(['code'=>0,'count'=>$count['c'],'data'=>$list]));
    }

    if($_GPC['pa']==3){
        //涉检
        $limit = intval($_GPC['limit']);
        $page = intval($_GPC['page']) - 1;
        if ($page != 0) {
            $page = $limit * $page;
        }
        
        $list = pdo_fetchall('select itemName as gname,itemNo,gcode,reason,file2 from '.tablename('customs_declare_grisk_list').' where openid=:openid and pre_batch_num=:batch_num and is_check=1'.$condition.' order by id desc limit '.$page.",".$limit,[':openid'=>$openid,':batch_num'=>$batch_num]);
        $count = pdo_fetch('select count(id) as c from '.tablename('customs_declare_grisk_list').' where pre_batch_num=:batch_num and openid=:openid and is_check=1'.$condition,[':openid'=>$openid,':batch_num'=>$batch_num]);
        die(json_encode(['code'=>0,'count'=>$count['c'],'data'=>$list]));
    }

    include $this->template('declare/risk_manage/threeInvolveGoods');
}elseif($op=='invo_file'){
    //涉证文件
    $itemNo = $_GPC['itemNo'];
    $hscode = $_GPC['hscode'];
    $batch_num = $_GPC['pre_batch_num'];

    if($_GPC['pa']==1){
        //上传文件
        $file = json_encode($_GPC['file'],true);
        $res = pdo_update('customs_declare_grisk_list',['file'=>$file],['itemNo'=>$itemNo,'pre_batch_num'=>$batch_num,'openid'=>$openid]);
        
        //查询同等编号下是否有其它商品需要同步文件
        $ishave = pdo_fetchcolumn('select count(id) as num from '.tablename('customs_declare_grisk_list').' where gcode=:gcode and file is null and pre_batch_num=:batch_num and openid=:openid and is_cert=1',[':gcode'=>$hscode,':batch_num'=>$batch_num,':openid'=>$openid]);
        $synchronization=0;
        if($ishave>0){
            $synchronization=1;
        }
        show_json(1,['msg'=>'提交涉证文件成功','is_synchronization'=>$synchronization]);
    }

    //先查询当前预编号-货号有无涉证文件
    $list = pdo_fetch('select file from '.tablename('customs_declare_grisk_list').' where openid=:openid and itemNo=:itemNo and pre_batch_num=:batch_num and is_cert=1',[':openid'=>$openid,':itemNo'=>$itemNo,':batch_num'=>$batch_num]);
    if(!empty($list['file'])){
        $list['file'] = json_decode($list['file'],true);
    }
    include $this->template('declare/risk_manage/invo_file');
}elseif($op=='insp_file'){
    //涉检文件
    $itemNo = $_GPC['itemNo'];
    $hscode = $_GPC['hscode'];
    $batch_num = $_GPC['pre_batch_num'];
    
    if($_GPC['pa']==1){
        //上传文件
        $file = json_encode($_GPC['file'],true);
        $res = pdo_update('customs_declare_grisk_list',['file2'=>$file],['itemNo'=>$itemNo,'pre_batch_num'=>$batch_num,'openid'=>$openid]);
        if($res){
            //查询同等编号下是否有其它商品需要同步文件
            $ishave = pdo_fetchcolumn('select count(id) as num from '.tablename('customs_declare_grisk_list').' where gcode=:gcode and file2 is null and pre_batch_num=:batch_num and openid=:openid and is_check=1',[':gcode'=>$hscode,':batch_num'=>$batch_num,':openid'=>$openid]);
            $synchronization=0;
            if($ishave>0){
                $synchronization=1;
            }
            show_json(1,['msg'=>'提交涉检文件成功','is_synchronization'=>$synchronization]);
        }
    }

    //先查询当前预编号-货号有无涉证文件
    $list = pdo_fetch('select file2 from '.tablename('customs_declare_grisk_list').' where openid=:openid and itemNo=:itemNo and pre_batch_num=:batch_num and is_check=1',[':openid'=>$openid,':itemNo'=>$itemNo,':batch_num'=>$batch_num]);
    if(!empty($list['file2'])){
        $list['file2'] = json_decode($list['file2'],true);
    }
    include $this->template('declare/risk_manage/insp_file');
}elseif($op=='synchronization'){
    //同步文件
    $hscode = $_GPC['hscode'];
    $batch_num = $_GPC['pre_batch_num'];
    $typ = $_GPC['typ'];
    
    if($typ==1){
        //涉证
    
        //待同步id
        $info = pdo_fetchall('select id from '.tablename('customs_declare_grisk_list').' where pre_batch_num=:batch_num and openid=:openid and file is null and is_cert=1 and gcode=:gcode',[':gcode'=>$hscode,':openid'=>$openid,':batch_num'=>$batch_num]);
        //原id文件
        $sample = pdo_fetch('select file from '.tablename('customs_declare_grisk_list').' where pre_batch_num=:batch_num and openid=:openid and file is not null and is_cert=1 and gcode=:gcode',[':gcode'=>$hscode,':openid'=>$openid,':batch_num'=>$batch_num]);
        foreach ($info as $k=>$v){
            pdo_update('customs_declare_grisk_list',['file'=>$sample['file']],['pre_batch_num'=>$batch_num,'openid'=>$openid,'id'=>$v['id']]);
        }
        show_json(1,['msg'=>'同步涉证文件成功']);
    }elseif($typ==2){
        //涉检
        
        //待同步id
        $info = pdo_fetchall('select id from '.tablename('customs_declare_grisk_list').' where pre_batch_num=:batch_num and openid=:openid and file2 is null and is_check=1 and gcode=:gcode',[':gcode'=>$hscode,':openid'=>$openid,':batch_num'=>$batch_num]);
        //原id文件
        $sample = pdo_fetch('select file2 from '.tablename('customs_declare_grisk_list').' where pre_batch_num=:batch_num and openid=:openid and file2 is not null and is_check=1 and gcode=:gcode',[':gcode'=>$hscode,':openid'=>$openid,':batch_num'=>$batch_num]);
        foreach ($info as $k=>$v){
            pdo_update('customs_declare_grisk_list',['file2'=>$sample['file2']],['pre_batch_num'=>$batch_num,'openid'=>$openid,'id'=>$v['id']]);
        }
        show_json(1,['msg'=>'同步涉检文件成功']);
    }
}elseif($op=='good_edit'){
    //商品编辑
    $itemNo = $_GPC['itemNo'];
    $batch_num = $_GPC['pre_batch_num'];

    if($_GPC['pa']==1){
        $glist = $_GPC['glist'];
        $itemName = trim($_GPC['itemName']);
        $gcode = trim($_GPC['gcode']);
        //1、判断编码是否正确
        $ishavecode = pdo_fetch('select two,id,basic_info,tax_info,regulatory_conditions,inspect_quarantine,chapter_info from '.tablename('customs_hscode_tariffschedule_ssl').' where hscode=:hscode',[':hscode'=>$gcode]);
        if(empty($ishavecode['id'])){
            show_json(-1,['msg'=>'商品编号不存在，请重新填写。']);
        }else{
            $ishavecode['basic_info'] = json_decode($ishavecode['basic_info'],true);
            if($ishavecode['basic_info'][5][1]=='作废'){
                show_json(-1,['msg'=>'商品编号已作废，请重新填写。']);
            }
        }
        
        $reason='';
        //敏感商品
        $sensitive = m('common')->is_sensitive($ishavecode);
        $ishaverisk = pdo_fetch('select id,is_sensitive,is_tax,is_check,is_cert,reason from '.tablename('customs_declare_grisk_list').' where pre_batch_num=:batch_num and openid=:openid and itemNo=:itemNo',[':batch_num'=>$batch_num,':openid'=>$openid,':itemNo'=>$itemNo]);
        if($sensitive==1){
            $ishavecode['chapter_info'] = json_decode($ishavecode['chapter_info'],true);
            $reason='敏感（'.$ishavecode['chapter_info'][0][1].'）';
            //是敏感
            if(!empty($ishaverisk)){
                pdo_update('customs_declare_grisk_list',['itemName'=>$itemName,'gcode'=>$gcode,'is_sensitive'=>1,'reason'=>$reason],['pre_batch_num'=>$batch_num,'openid'=>$openid,'itemNo'=>$itemNo]);
            }else{
                pdo_insert('customs_declare_grisk_list',['itemName'=>$itemName,'gcode'=>$gcode,'pre_batch_num'=>$batch_num,'openid'=>$openid,'itemNo'=>$itemNo,'reason'=>$reason,'is_sensitive'=>1]);
            }
        }else{
            //不是敏感
            if(!empty($ishaverisk)){
                $rea = explode(',',$ishaverisk['reason']);
                array_shift($rea);
                $rea = implode(',',$rea);
                pdo_update('customs_declare_grisk_list',['pre_batch_num'=>$batch_num,'openid'=>$openid,'itemNo'=>$itemNo,'is_sensitive'=>0,'reason'=>$rea],['pre_batch_num'=>$batch_num,'openid'=>$openid,'itemNo'=>$itemNo]);
            }
        }

        $msg='';
        //涉税
        $ishavecode['tax_info'] = json_decode($ishavecode['tax_info'],true);
        if($ishavetax['tax_info'][3][1]!='' && $ishavetax['tax_info'][3][1]!='0%'){
            $msg.='涉税，';
            if(empty($reason)){
                $reason.='涉税';
            }else{
                $reason.=',涉税';    
            }
            
            //修改涉税信息
            if(!empty($ishaverisk)){
                pdo_update('customs_declare_grisk_list',['itemName'=>$itemName,'gcode'=>$gcode,'is_tax'=>1,'reason'=>$reason],['pre_batch_num'=>$batch_num,'openid'=>$openid,'itemNo'=>$itemNo]);
            }else{
                pdo_insert('customs_declare_grisk_list',['itemName'=>$itemName,'gcode'=>$gcode,'pre_batch_num'=>$batch_num,'openid'=>$openid,'itemNo'=>$itemNo,'reason'=>$reason,'is_tax'=>1]);
            }
        }else{
            if(!empty($ishaverisk)){
                $reas = explode(',',$ishaverisk['reason']);
                $rea = '';
                foreach ($reas as $k2=>$v2){
                    if($v2!='涉税'){
                        $rea .= $v2.','; 
                    }
                }
                $rea = rtrim($rea,',');
                pdo_update('customs_declare_grisk_list',['pre_batch_num'=>$batch_num,'openid'=>$openid,'itemNo'=>$itemNo,'is_tax'=>0,'reason'=>$rea],['pre_batch_num'=>$batch_num,'openid'=>$openid,'itemNo'=>$itemNo]);
            }
        }

        //涉证
        $ishavecode['regulatory_conditions'] = json_decode($ishavecode['regulatory_conditions'],true);
        if(!empty($ishavecode['regulatory_conditions'])){
            $msg.='涉证，';
            if(empty($reason)){
                $reason.='涉证';
            }else{
                $reason.=',涉证';    
            }
            //修改涉证信息
            if(!empty($ishaverisk)){
                pdo_update('customs_declare_grisk_list',['itemName'=>$itemName,'gcode'=>$gcode,'file'=>'','reason'=>$reason,'is_cert'=>1],['pre_batch_num'=>$batch_num,'openid'=>$openid,'itemNo'=>$itemNo]);
            }else{
                pdo_insert('customs_declare_grisk_list',['itemName'=>$itemName,'gcode'=>$gcode,'pre_batch_num'=>$batch_num,'openid'=>$openid,'itemNo'=>$itemNo,'reason'=>$reason,'is_cert'=>1]);
            }
        }else{
            //不涉证
            if(!empty($ishaverisk)){
                $reas = explode(',',$ishaverisk['reason']);
                $rea = '';
                foreach ($reas as $k2=>$v2){
                    if($v2!='涉证'){
                        $rea .= $v2.','; 
                    }
                }
                $rea = rtrim($rea,',');
                pdo_update('customs_declare_grisk_list',['pre_batch_num'=>$batch_num,'openid'=>$openid,'itemNo'=>$itemNo,'is_cert'=>0,'reason'=>$rea],['pre_batch_num'=>$batch_num,'openid'=>$openid,'itemNo'=>$itemNo]);
            }
        }

        //涉检
        $ishavecode['inspect_quarantine'] = json_decode($ishavecode['inspect_quarantine'],true);
        if(!empty($ishavecode['inspect_quarantine'])){
            $msg.='涉检，';
            if(empty($reason)){
                $reason.='涉检';
            }else{
                $reason.=',涉检';    
            }
            //修改涉检信息
            if(!empty($ishaverisk)){
                pdo_update('customs_declare_grisk_list',['itemName'=>$itemName,'gcode'=>$gcode,'file2'=>'','reason'=>$reason,'is_check'=>1],['pre_batch_num'=>$batch_num,'openid'=>$openid,'itemNo'=>$itemNo]);
            }else{
                pdo_insert('customs_declare_grisk_list',['itemName'=>$itemName,'gcode'=>$gcode,'pre_batch_num'=>$batch_num,'openid'=>$openid,'itemNo'=>$itemNo,'reason'=>$reason,'is_check'=>1]);
            }
        }else{
            //不涉检
            if(!empty($ishaverisk)){
                $reas = explode(',',$ishaverisk['reason']);
                $rea = '';
                foreach ($reas as $k2=>$v2){
                    if($v2!='涉检'){
                        $rea .= $v2.','; 
                    }
                }
                $rea = rtrim($rea,',');
                pdo_update('customs_declare_grisk_list',['pre_batch_num'=>$batch_num,'openid'=>$openid,'itemNo'=>$itemNo,'is_check'=>0,'reason'=>$rea],['pre_batch_num'=>$batch_num,'openid'=>$openid,'itemNo'=>$itemNo]);
            }
        }

        if($glist==4){
            //修改归类清单
            pdo_update('customs_declare_risk_list',['update_itemName'=>$itemName,'gcode'=>$gcode],['pre_batch_num'=>$batch_num,'itemNo'=>$itemNo,'openid'=>$openid]);
        }elseif($glist==2 || $glist==3){
            pdo_update('customs_goods_pre_fill_log',['update_itemName'=>$itemName,'gcode'=>$gcode],['pre_batch_num'=>$batch_num,'itemNo'=>$itemNo,'openid'=>$openid]);
        }elseif($glist==1){
            pdo_update('customs_goods_pre_log',['update_itemName'=>$itemName,'gcode'=>$gcode],['pre_batch_num'=>$batch_num,'itemNo'=>$itemNo,'openid'=>$openid]);
        }
        if(!empty($msg)){
            $msg = rtrim($msg,',');
            if($msg=='涉税'){
                show_json(2,['msg'=>'修改成功！您的商品存在"'.$msg.'"情况，请知悉。']);
            }else{
                show_json(3,['msg'=>'修改成功！您的商品存在"'.$msg.'"情况，请知悉并补充文件。']);
            }
        }else{
            show_json(1,['msg'=>'修改成功！']);
        }
    }

    //查询商品清单
    $glist = pdo_fetchcolumn('select connect_glist from '.tablename('customs_declare_grisk_head').' where openid=:openid and pre_batch_num=:batch_num',[':openid'=>$openid,':batch_num'=>$batch_num]);
    if($glist==4){
        //归类清单
        $data = pdo_fetch('select itemNo,id,itemName,update_itemName,gcode from '.tablename('customs_declare_risk_list').' where openid=:openid and pre_batch_num=:batch_num and itemNo=:itemNo',[':openid'=>$openid,':batch_num'=>$batch_num,':itemNo'=>$itemNo]);
        $data['gname'] = $data['itemName'];
        if(!empty($data['update_itemName'])){
            $data['gname'] = $data['update_itemName'];
        }
    }elseif($glist==3 || $glist==2){
        //调值清单或补缺清单
        $data = pdo_fetch('select itemNo,id,itemName,update_itemName,gcode from '.tablename('customs_goods_pre_fill_log').' where openid=:openid and pre_batch_num=:batch_num and itemNo=:itemNo',[':openid'=>$openid,':batch_num'=>$batch_num,':itemNo'=>$itemNo]);
        $data['gname'] = $data['itemName'];
        if(!empty($data['update_itemName'])){
            $data['gname'] = $data['update_itemName'];
        }
    }elseif($list==1){
        $data = pdo_fetch('select itemNo,id,itemName,update_itemName,gcode from '.tablename('customs_goods_pre_log').' where openid=:openid and pre_batch_num=:batch_num and itemNo=:itemNo',[':openid'=>$openid,':batch_num'=>$batch_num,':itemNo'=>$itemNo]);
        $data['gname'] = $data['itemName'];
        if(!empty($data['update_itemName'])){
            $data['gname'] = $data['update_itemName'];
        }
    }
    include $this->template('declare/risk_manage/good_edit');
}elseif($op=='del'){
    //剔除
    $itemNo = $_GPC['itemNo'];
    $batch_num = $_GPC['pre_batch_num'];

    $glist = pdo_fetchcolumn('select connect_glist from '.tablename('customs_declare_grisk_head').' where pre_batch_num=:batch_num and openid=:openid',[':batch_num'=>$batch_num,':openid'=>$openid]);
    if($glist==4){
        //归类
        pdo_update('customs_declare_risk_list',['status'=>1],['pre_batch_num'=>$batch_num,'itemNo'=>$itemNo,'openid'=>$openid]);
    }elseif($glist==3){
        //总值
        $gid = pdo_fetchcolumn('select good_id from '.tablename('customs_goods_pre_log').' where pre_batch_num=:batch_num and itemNo=:itemNo and openid=:openid',[':pre_batch_num'=>$batch_num,':itemNo'=>$itemNo,':openid'=>$openid]);
        pdo_update('customs_goods_pre_adjust_log',['status'=>1],['pre_batch_num'=>$batch_num,'good_id'=>$gid,'openid'=>$openid]);
    }elseif($glist==2){
        //补缺
        pdo_update('customs_goods_pre_fill_log',['status'=>1],['pre_batch_num'=>$batch_num,'itemNo'=>$itemNo,'openid'=>$openid]);
    }elseif($glist==1){
        //原始
        pdo_update('customs_goods_pre_log',['status'=>1],['pre_batch_num'=>$batch_num,'itemNo'=>$itemNo,'openid'=>$openid]);
    }
    show_json(1,['msg'=>'剔除成功']);
}elseif($op=='declare'){
    //申报
    $batch_num = $_GPC['pre_batch_num'];
    //1、检测有无进行风险检测
    $is_check = pdo_fetchcolumn('select withhold_status from '.tablename('customs_pre_declare').' where pre_batch_num=:batch_num and openid=:openid',[':batch_num'=>$batch_num,':openid'=>$openid]);
    if($is_check==0){
        show_json(-1,['msg'=>'请先完成风险检测再提交申报！']);
    }
    
    //2、检测有无提供资料
    $is_havefile = pdo_fetchall('select is_check,is_cert,file,file2 from '.tablename('customs_declare_grisk_list').' where pre_batch_num=:batch_num and openid=:openid',[':batch_num'=>$batch_num,':openid'=>$openid]);
    foreach($is_havefile as $k=>$v){
        if(($v['is_check']==1 && empty($v['file2'])) || ($v['is_cert']==1 && empty($v['file']))){
            show_json(-1,['msg'=>'请先提供涉证涉检相关文件再提交！']);
        }
    }
    
    //3、提交
    pdo_update('customs_pre_declare',['withhold_status'=>2],['pre_batch_num'=>$batch_num,'openid'=>$openid]);

    //通知管理员
    $notice_manage = 'ov3-bt5vIxepEjWc51zRQNQbFSaQ';//ov3-bt8keSKg_8z9Wwi-zG1hRhwg  ov3-bt5vIxepEjWc51zRQNQbFSaQ
    $decl_user = pdo_fetch('select nickname from '.tablename('mc_mapping_fans').' where openid=:openid',[':openid'=>$openid]);
    $post = json_encode([
        'call'=>'confirmCollectionNotice',
        'first' =>'您好，用户['.$decl_user['nickname'].']已提交申报，预提编号为['.$batch_num.']',
        'keyword1' => '发起申报',
        'keyword2' => '发起成功',
        'keyword3' => date('Y-m-d H:i:s',time()),
        'remark' => '',
        'url' => '',
        'openid' => $notice_manage,
        'temp_id' => 'SVVs5OeD3FfsGwW0PEfYlZWetjScIT8kDxht5tlI1V8'
    ]);
    $res = ihttp_request('https://shop.gogo198.cn/api/sendwechattemplatenotice.php', $post);
    if($res){
        show_json(1,['msg'=>'申请预提已成功，等待总后台审核中。']);
    }
}