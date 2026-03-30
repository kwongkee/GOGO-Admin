<?php
// 模块LTD提供
if (!defined('IN_IA')) {
    exit('Access Denied');
}

/**
 * 申报风控
 * 2022-04-18
 */

global $_W;
global $_GPC;

$openid=$_W['openid'];
$op = !empty($_GPC['op'])?trim($_GPC['op']):'display';

if($op=='display'){
    //申报风控列表

    if($_GPC['pa']==1){
        $limit = intval($_GPC['limit']);
        $page = intval($_GPC['page']) - 1;
        if ($page != 0) {
            $page = $limit * $page;
        }
        $list = pdo_fetchall('select * from '.tablename('customs_adjusted_declare').' where openid=:openid order by id desc limit '.$page.",".$limit,[':openid'=>$openid]);
        foreach($list as $k=>$v){
            $list[$k]['createtime'] = date('Y-m-d H:i',$v['createtime']);
        }
        $count = pdo_fetch('select count(id) as c from '.tablename('customs_adjusted_declare').' where openid=:openid',[':openid'=>$openid]);
        die(json_encode(['code'=>0,'count'=>$count['c'],'data'=>$list]));
    }

    include $this->template('declare/report_risk/index');
}elseif($op=='risk_list'){
    //风控列表
    $sys_batch_num = trim($_GPC['sys_batch_num']);
    $pre_batch_num = trim($_GPC['pre_batch_num']);

    if($_GPC['pa']==1){
        //查询该批次号下所有的订单
        $list = pdo_fetchall('select distinct logisticsNo,pre_batch_num from '.tablename('customs_goods_pre_log').' where openid=:openid and pre_batch_num=:batch_num',[':openid'=>$openid,':batch_num'=>$pre_batch_num]);
        die(json_encode(['code'=>0,'data'=>$list]));
    }
    include $this->template('declare/report_risk/risk_list');
}elseif($op=='goods_cateFill'){
    //获取该批次号下所有处理后的商品数据，供用户选择
    $batch_num = $_GPC['batch_num'];
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
    $method = array_reverse($method);

    include $this->template('declare/report_risk/goods_cateFill');
}elseif($op=='system_fill'){
    //系统补缺-商品编码归类
    $batch_num = $_GPC['batch_num'];
    $adjust_method = $_GPC['adjust_method'];
//    $logisticsNo = $_GPC['logisticsNo'];
    $connect_glist = 0;
    //1、找出该预报编号下商品编码为空的商品
    if($adjust_method == '01-原始清单商品数据'){
        $list = pdo_fetchall('select id,itemName,itemNo,gcode,unit,unit1,logisticsNo from '.tablename('customs_goods_pre_log').' where pre_batch_num=:batch_num and openid=:openid',[':batch_num'=>$batch_num,':openid'=>$openid]);
        $connect_glist=1;
    }elseif($adjust_method == '02-补缺清单商品数据'){
        $list = pdo_fetchall('select id,itemName,itemNo,gcode,unit,unit1,logisticsNo from '.tablename('customs_goods_pre_fill_log').' where pre_batch_num=:batch_num and openid=:openid',[':batch_num'=>$batch_num,':openid'=>$openid]);
        $connect_glist=2;
    } elseif($adjust_method == '03-调值清单商品数据'){
        $list = pdo_fetchall('select a.id,b.itemName,b.itemNo,b.gcode,b.unit,b.unit1,b.logisticsNo from '.tablename('customs_goods_pre_adjust_log').' a left join '.tablename('customs_goods_pre_fill_log').' b on b.id=a.fill_id where a.pre_batch_num=:batch_num and a.openid=:openid',[':batch_num'=>$batch_num,':openid'=>$openid]);
        $connect_glist=3;
    }

    // if(empty($list)){
    //     show_json(-1,['msg'=>'补缺已完成，无需再补缺！']);
    // }else{
        $empty_ids='';
        foreach($list as $k=>$v){
            if(!empty($v['gcode'])){
                //插入归类商品表
                $ishave = pdo_fetch('select id from '.tablename('customs_declare_risk_list').' where pre_batch_num=:batch_num and itemNo=:itemNo and itemName=:itemName and openid=:openid',[':batch_num'=>$batch_num,':itemNo'=>$v['itemNo'],':itemName'=>$v['itemName'],':openid'=>$openid]);
                if(empty($ishave['id'])){
                    //插入归类商品数据表
                    pdo_insert('customs_declare_risk_list',[
                        'pre_batch_num'=>$batch_num,
                        'openid'=>$openid,
                        'itemNo'=>$v['itemNo'],
                        'itemName'=>$v['itemName'],
                        'gcode'=>$v['gcode'],
                        'logisticsNo'=>trim($v['logisticsNo']),
                        'unit'=>$v['unit'],
                        'unit1'=>$v['unit'],
                        'connect_glist'=>$connect_glist,
                    ]);
                }
            }else{
                //1、先查询数据表，有无此品名商品
                $hscode = pdo_fetchall('select basic_info,hscode,id from '.tablename('customs_hscode_tariffschedule_ssl').' where `name` like "%'.$v['itemName'].'%"');
    
                if(empty($hscode)){
                    //2、若查找不到，则通过品名查找归类通的品名关键字
                    $hscode2 = pdo_fetchcolumn('select hscode from '.tablename('customs_hscode_gui_lei_tong').' where keyword=:keyword order by id asc',[':keyword'=>$v['itemName']]);
                    if(empty($hscode2)){
                        //3、查询归类通/HSCIQ
                        $hscode2 = m('common')->getGuiLeiTong($v['itemName']);
                    }
                    $hscode = pdo_fetchall('select basic_info,hscode,id from '.tablename('customs_hscode_tariffschedule_ssl').' where hscode=:hscode',[':hscode'=>$hscode2]);
                }
                
                if(empty($hscode)){
                    $empty_ids .= $v['id'].',';
                }else{
                    $count = count($hscode);
                    foreach($hscode as $k2=>$v2){
                        $basic_info = json_decode($v2['basic_info'],true);
                        if($basic_info[11]!='作废'){
                            //获取hscode、unit、unit1
                            $unit='';
                            if($basic_info[3][1]!='' || $basic_info[3][1]!='无'){
                                $unit = pdo_fetchcolumn('select code_value from '.tablename('unit').' where code_name=:code_name',[':code_name'=>$basic_info[3][1]]);
                            }
    
                            //插入归类商品表
                            $ishave = pdo_fetch('select id from '.tablename('customs_declare_risk_list').' where pre_batch_num=:batch_num and itemNo=:itemNo and itemName=:itemName and openid=:openid',[':batch_num'=>$batch_num,':itemNo'=>$v['itemNo'],':itemName'=>$v['itemName'],':openid'=>$openid]);
                            if($ishave['id']>0){
                                pdo_update('customs_declare_risk_list',[
                                    'gcode'=>$v2['hscode'],
                                    'unit'=>$unit,
                                    'unit1'=>$unit,
                                    'logisticsNo'=>$v['logisticsNo'],
                                    'connect_glist'=>$connect_glist,
                                ],['pre_batch_num'=>$batch_num,'openid'=>$openid,'itemNo'=>$v['itemNo']]);
                            }else{
                                //插入归类商品数据表
                                pdo_insert('customs_declare_risk_list',[
                                    'pre_batch_num'=>$batch_num,
                                    'openid'=>$openid,
                                    'itemNo'=>$v['itemNo'],
                                    'itemName'=>$v['itemName'],
                                    'gcode'=>$v2['hscode'],
                                    'logisticsNo'=>$v['logisticsNo'],
                                    'unit'=>$unit,
                                    'unit1'=>$unit,
                                    'connect_glist'=>$connect_glist,
                                ]);
                            }
                        }else{
                            if($basic_info[11]=='作废' && $count==1){
                                //2、若查找不到，则通过品名查找归类通的品名关键字，待做
                                $empty_ids .= $v['id'].',';
                            }
                        }
                    }
                }
            }
        }

        if(!empty($empty_ids)){
            show_json(-2,['msg'=>'您好！经系统补缺筛查，发现有商品还处于归类缺省，正在跳转人工补缺！','need_fill_ids'=>$empty_ids]);
        }else{
            show_json(1,['msg'=>'补缺完成！']);
        }
    // }
}elseif($op=='manual_fill_page'){
    //人工补缺
    $adjust_method = $_GPC['adjust_method'];
    $batch_num = $_GPC['pre_batch_num'];
    if(empty($_GPC['ids'])){
        //人工补缺
        $connect_glist = 0;
        if($adjust_method == '01-原始清单商品数据'){
            $rg = pdo_fetchall('select id from '.tablename('customs_goods_pre_log').' where openid=:openid and pre_batch_num=:batch_num and gcode=""',[':openid'=>$openid,':batch_num'=>$batch_num]);
            $connect_glist = 1;
        }elseif($adjust_method == '02-补缺清单商品数据'){
            $rg = pdo_fetchall('select good_id as id from '.tablename('customs_goods_pre_fill_log').' where openid=:openid and pre_batch_num=:batch_num and gcode=""',[':openid'=>$openid,':batch_num'=>$batch_num]);
            $connect_glist = 2;
        }elseif($adjust_method == '03-调值清单商品数据'){
            $rg = pdo_fetchall('select b.good_id as id from '.tablename('customs_goods_pre_adjust_log').' a left join '.tablename('customs_goods_pre_fill_log').' b on b.id=a.fill_id where a.openid=:openid and a.pre_batch_num=:batch_num and b.gcode=""',[':openid'=>$openid,':batch_num'=>$batch_num]);
            $connect_glist = 3;
        }

        $ids = '';
        foreach($rg as $k=>$v){
            $ids .= $v['id'].',';
        }
        $ids = rtrim($ids,',');
    }else{
        //系统补缺返回
        $ids = rtrim($_GPC['ids'],',');
    }
    $type = intval($_GPC['type']);

    include $this->template('declare/report_risk/manual_fill_page');
}elseif($op=='download_list'){
    //人工补缺-归类缺省
    $ids = $_GPC['ids'];
    $adjust_method = $_GPC['adjust_method'];
    $type=intval($_GPC['type']);
    $list = pdo_fetchall('select * from '.tablename('customs_goods_pre_log').' where openid=:openid and id in ('.$ids.')',[':openid'=>$openid]);
    $name = $list[0]['pre_batch_num'];
    if(empty($type)){
        //hscode缺省
        $columns = array(
            array(
                'title' => '企业商品货号',
                'field' => 'itemNo',
                'width' => 16
            ),
            array(
                'title' =>'商品名称',
                'field' => 'itemName',
                'width' => 24
            ),
            array(
                'title' =>'商品编码',
                'field' => 'gcode',
                'width' => 12
            ),
            array(
                'title' =>'申报计量单位(中文)',
                'field' => 'unit',
                'width' => 8
            ),
            array(
                'title' =>'法定计量单位(中文)',
                'field' => 'unit1',
                'width' => 8
            ),
            array(
                'title' =>'物流运单编号',
                'field' => 'logisticsNo',
                'width' => 16
            ),
        );
        m('excel')->export($list , array(
            "title" => $name."商品归类-补缺清单",
            "columns" => $columns
        ));
    }
}elseif($op=='upload_list'){
    //商品归类-上传清单
    $ids = $_GPC['ids'];
    $type = intval($_GPC['type']);
    $batch_num = $_GPC['pre_batch_num'];
    $connect_glist = intval($_GPC['connect_glist']);
    $res = m('excel')->import('file');
    //读取excel内容
    $is_true=1;
    $msg = '';
    if(empty($type)){
        foreach($res as $k=>$v){
            foreach($v as $kk=>$vv) {
                //企业商品货号
                if($kk==0 && empty($vv)){
                    $is_true=0;
                    $msg='第'.($k+2).'行的第'.($kk+1).'列，请保留原有企业商品货号！';
                }

                //商品编码
                if($kk==2){
                    $ishave = pdo_fetchcolumn('select id from '.tablename('customs_hscode_tariffschedule_ssl').' where hscode=:hscode',[':hscode'=>trim($vv)]);
                    if(empty($ishave)){
                        $is_true=0;
                        $msg='第'.($k+2).'行的第'.($kk+1).'列，请输入正确的商品编码！';
                    }
                }
                //申报计量单位&&法定计量单位
                if($kk==3 || $kk==4){
                    //查找计量单位和法定计量单位是否正确
                    $is_corr = pdo_fetchcolumn('select code_value from '.tablename('unit').' where code_name=:code_name',[':code_name'=>trim($vv)]);
                    if(empty($is_corr)){
                        $is_true=0;
                        $msg='第'.($k+2).'行的第'.($kk+1).'列，请输入正确的计量单位（中文）！';
                    }
                }

                //物流运单编号
                if($kk==5 && empty($vv)){
                    $is_true=0;
                    $msg='第'.($k+2).'行的第'.($kk+1).'列，请保留原有物流运单编号！';
                }
            }
        }

        if(empty($is_true)){
            show_json(-1,['msg'=>$msg]);
        }else {
            //人工补缺，数据都不为空
            foreach($res as $k=>$v){
                //查询表是否有此品名和hscode
                $ishave = pdo_fetch('select id from '.tablename('customs_hscode_gui_lei_tong').' where hscode=:hscode and keyword=:keyword',[':hscode'=>trim($v[2]),':keyword'=>$v[1]]);
                if(empty($ishave['id'])){
                    //记录用户插入的数据
                    pdo_insert('customs_hscode_gui_lei_tong',['hscode'=>trim($v[2]),'keyword'=>trim($v[1])]);
                }
                $unit = pdo_fetchcolumn('select code_value from '.tablename('unit').' where code_name=:code_name',[':code_name'=>trim($v[3])]);
                $unit1 = pdo_fetchcolumn('select code_value from '.tablename('unit').' where code_name=:code_name',[':code_name'=>trim($v[4])]);

                $ishave = pdo_fetch('select id from '.tablename('customs_declare_risk_list').' where itemNo=:itemNo and openid=:openid',[':itemNo'=>trim($v[0]),':openid'=>$openid]);
                if($ishave['id']){
                    //修改
                    pdo_update('customs_declare_risk_list',['gcode'=>trim($v[2]),'unit'=>$unit,'unit1'=>$unit1,'connect_glist'=>$connect_glist,'logisticsNo'=>trim($v[5])],['pre_batch_num'=>$batch_num,'openid'=>$openid,'itemNo'=>trim($v[0])]);
                }else{
                    //插入归类商品清单表
                    pdo_insert('customs_declare_risk_list',[
                        'pre_batch_num'=>$batch_num,
                        'openid'=>$openid,
                        'itemNo'=>trim($v[0]),
                        'itemName'=>trim($v[1]),
                        'gcode'=>trim($v[2]),
                        'logisticsNo'=>trim($v[5]),
                        'unit'=>$unit,
                        'unit1'=>$unit1,
                        'connect_glist'=>$connect_glist
                    ]);
                }
            }
            show_json(1,['msg'=>'商品归类补缺成功！']);
        }
    }
}elseif($op=='goods_list'){
    //商品列表
    $logisticsNo = trim($_GPC['logisticsNo']);
    $pre_batch_num = trim($_GPC['pre_batch_num']);

    if($_GPC['pa']==1){
        //查询该批次号下所有的订单
        $limit = intval($_GPC['limit']);
        $page = intval($_GPC['page']) - 1;
        if ($page != 0) {
            $page = $limit * $page;
        }
        $keyword = trim($_GPC['keywords']) ? trim($_GPC['keywords']) : '';
        $condition = '';
        if($keyword){
            $condition = ' and (itemName like "%'.$keyword.'%" or update_itemName like "%'.$keyword.'%") ';
        }
        //查询归类表
        $list = pdo_fetchall('select id,itemName,update_itemName,gcode from '.tablename('customs_declare_risk_list').' where openid=:openid and pre_batch_num=:batch_num '.$condition.' order by id desc limit '.$page.",".$limit,[':openid'=>$openid,':batch_num'=>$pre_batch_num]);
        foreach($list as $k=>$v){
            $list[$k]['gname'] = $v['itemName'];
            if(!empty($v['update_itemName'])){
                $list[$k]['gname'] = $v['update_itemName'];
            }
        }
        $count = pdo_fetch('select count(id) as c from '.tablename('customs_declare_risk_list').' where openid=:openid and pre_batch_num=:batch_num'.$condition,[':openid'=>$openid,':batch_num'=>$pre_batch_num]);
        die(json_encode(['code'=>0,'count'=>$count['c'],'data'=>$list]));
    }
    include $this->template('declare/report_risk/goods_list');
}elseif($op=='good_edit'){
    //修改商品
    $id = intval($_GPC['id']);
    $type = intval($_GPC['type']);
    $pre_batch_num = trim($_GPC['pre_batch_num']);

    if($_W['isajax']){
        $hscode = trim($_GPC['gcode']);
        $is_corr = pdo_fetch('select id,basic_info from '.tablename('customs_hscode_tariffschedule_ssl').' where hscode=:hscode',[':hscode'=>$hscode]);
        if(empty($is_corr['id'])){
            show_json(-1,['msg'=>'请输入正确的商品编码！']);
        }else{
            $is_corr['basic_info'] = json_decode($is_corr['basic_info'],true);
            if($is_corr['basic_info'][5][1]=='作废'){
                show_json(-1,['msg'=>'当前编码已作废，请输入正确的商品编码！']);
            }
            $unit = pdo_fetchcolumn('select code_value from '.tablename('unit').' where code_name=:code_name',[':code_name'=>$is_corr['basic_info'][3][1]]);
            pdo_update('customs_declare_risk_list',['update_itemName'=>trim($_GPC['itemName']),'gcode'=>$hscode,'unit'=>$unit,'unit1'=>$unit],['id'=>$id,'openid'=>$openid]);
            show_json(1,['msg'=>'修改成功！']);
        }
    }else{
        $data = pdo_fetch('select * from '.tablename('customs_declare_risk_list').' where id=:id and openid=:openid',[':id'=>$id,':openid'=>$openid]);
        $data['gname'] = $data['itemName'];
        if(!empty($data['update_itemName'])){
            $data['gname'] = $data['update_itemName'];
        }
    }

    include $this->template('declare/report_risk/good_edit');
}elseif($op=='download_goods'){
    //导出商品清单
    $batch_num = $_GPC['pre_batch_num'];

//    $sys_batch_num = pdo_fetchcolumn('select sys_batch_num from '.tablename('customs_adjusted_declare').' where pre_batch_num=:batch_num and openid=:openid',[':batch_num'=>$batch_num,':openid'=>$openid]);
//    $list = pdo_fetchall('select b.itemNo,a.good_id,a.onebound_gid,b.gcode,a.currency,b.qty,b.qty1,a.gmodel,a.update_price,a.update_totalPrice,a.update_charge,a.chargeDate,a.logisticsNo,a.freight,a.insuredFee,a.barCode,b.grossWeight,b.netWeight,b.packNo,b.goodsInfo,a.unit,a.unit1 from '.tablename('customs_goods_pre_fill_log').' a left join '.tablename('customs_goods_pre_log').' b on b.id=a.good_id where a.openid=:openid and b.pre_batch_num=:batch_num',[':openid'=>$openid,':batch_num'=>$batch_num]);
    $list = pdo_fetchall('select update_itemName,itemName,id,connect_glist,gcode,itemNo,unit,unit1 from '.tablename('customs_declare_risk_list').' where pre_batch_num=:batch_num and openid=:openid',[':batch_num'=>$batch_num,':openid'=>$openid]);
    foreach($list as $k=>$v){
        $list[$k]['gname'] = $v['itemName'];
        if(!empty($v['update_itemName'])){
            $list[$k]['gname'] = $v['update_itemName'];
        }
        if($v['connect_glist']==3){
            //调值
            $glist = pdo_fetch('select a.price,a.totalPrice,a.charge,b.currency,b.qty,b.qty1,b.gmodel,b.chargeDate,b.logisticsNo,b.freight,b.insuredFee,b.barCode,b.grossWeight,b.netWeight,b.packNo,b.goodsInfo from '.tablename('customs_goods_pre_adjust_log').' a left join '.tablename('customs_goods_pre_fill_log').' b on b.id=a.fill_id where b.pre_batch_num=:batch_num and b.itemNo=:itemNo and b.openid=:openid',[':batch_num'=>$batch_num,':itemNo'=>$v['itemNo'],':openid'=>$openid]);
            $list[$k]['price'] = $glist['price'];
            $list[$k]['totalPrice'] = $glist['totalPrice'];
            $list[$k]['charge'] = $glist['charge'];
            $list[$k]['chargeDate'] = $glist['chargeDate'];
            $list[$k]['currency'] = $glist['currency'];
            $list[$k]['qty'] = $glist['qty'];
            $list[$k]['qty1'] = $glist['qty1'];
            $list[$k]['gmodel'] = $glist['gmodel'];
            $list[$k]['logisticsNo'] = $glist['logisticsNo'];
            $list[$k]['freight'] = $glist['freight'];
            $list[$k]['insuredFee'] = $glist['insuredFee'];
            $list[$k]['barCode'] = $glist['barCode'];
            $list[$k]['insuredFee'] = $glist['insuredFee'];
            $list[$k]['grossWeight'] = $glist['grossWeight'];
            $list[$k]['netWeight'] = $glist['netWeight'];
            $list[$k]['packNo'] = $glist['packNo'];
            $list[$k]['goodsInfo'] = $glist['goodsInfo'];
        }
    }

    $columns = array(
        array(
            'title' => '企业商品货号',
            'field' => 'itemNo',
            'width' => 16
        ),
        array(
            'title' =>'商品名称',
            'field' => 'gname',
            'width' => 24
        ),
        array(
            'title' =>'商品编码',
            'field' => 'gcode',
            'width' => 24
        ),
        array(
            'title' =>'币种',
            'field' => 'currency',
            'width' => 8
        ),
        array(
            'title' =>'申报数量',
            'field' => 'qty',
            'width' => 12
        ),
        array(
            'title' =>'法定数量',
            'field' => 'qty1',
            'width' => 12
        ),
        array(
            'title' =>'规格型号',
            'field' => 'gmodel',
            'width' => 24
        ),
        array(
            'title' =>'FOB单价',
            'field' => 'price',
            'width' => 12
        ),
        array(
            'title' =>'FOB总价',
            'field' => 'totalPrice',
            'width' => 12
        ),
        array(
            'title' =>'收款金额',
            'field' => 'charge',
            'width' => 12
        ),
        array(
            'title' =>'到账时间',
            'field' => 'chargeDate',
            'width' => 12
        ),
        array(
            'title' =>'物流运单编号',
            'field' => 'logisticsNo',
            'width' => 24
        ),
        array(
            'title' =>'运费',
            'field' => 'freight',
            'width' => 12
        ),
        array(
            'title' =>'保价费',
            'field' => 'insuredFee',
            'width' => 12
        ),
        array(
            'title' =>'条形码',
            'field' => 'barCode',
            'width' => 12
        ),
        array(
            'title' =>'毛重',
            'field' => 'grossWeight',
            'width' => 12
        ),
        array(
            'title' =>'净重',
            'field' => 'netWeight',
            'width' => 12
        ),
        array(
            'title' =>'件数',
            'field' => 'packNo',
            'width' => 8
        ),
        array(
            'title' =>'主要货物信息',
            'field' => 'goodsInfo',
            'width' => 24
        ),
        array(
            'title' =>'申报计量单位',
            'field' => 'unit',
            'width' => 16
        ),
        array(
            'title' =>'法定计量单位',
            'field' => 'unit1',
            'width' => 16
        ),
    );
    m('excel')->export($list , array(
        "title" => $batch_num."商品清单",
        "columns" => $columns
    ));
}
elseif($op=='declare_risk'){
    //2022-04-24 申报风控~~~另一种理解
    include $this->template('declare/report_risk/declare_risk_index');
}elseif($op=='goods_cate'){
    //商品归类
    if($_GPC['pa']==1){
        $limit = intval($_GPC['limit']);
        $page = intval($_GPC['page']) - 1;
        if ($page != 0) {
            $page = $limit * $page;
        }
        $list = pdo_fetchall('select * from '.tablename('customs_declare_risk_list').' where openid=:openid order by id desc limit '.$page.",".$limit,[':openid'=>$openid]);
        $count = pdo_fetch('select count(id) as c from '.tablename('customs_declare_risk_list').' where openid=:openid',[':openid'=>$openid]);
        die(json_encode(['code'=>0,'count'=>$count['c'],'data'=>$list]));
    }
    include $this->template('declare/report_risk/goods_cate');
}elseif($op=='system_fill_page'){
    //系统补缺
//    include('simple_html_dom.php');
    set_time_limit(0);
    if($_GPC['pa']==1){
        $res = m('excel')->import('file');
        //读取excel内容
        $is_true=1;
        $msg = '';
        if(empty($type)){
            foreach($res as $k=>$v){
                foreach($v as $kk=>$vv) {
                    //商品货号
                    if($kk==0 && empty($vv)){
                        $is_true=0;
                        $msg='第'.($k+2).'行的第'.($kk+1).'列，请输入正确的商品货号！';
                    }
                    //商品编码
                    if($kk==1 && empty($vv)){
                        $is_true=0;
                        $msg='第'.($k+2).'行的第'.($kk+1).'列，请输入正确的商品名称！';
                    }
                }
            }

            if(empty($is_true)){
                show_json(-1,['msg'=>$msg]);
            }else {
                //系统补缺，数据都不为空
                foreach($res as $k=>$v){
                    $list = pdo_fetchall('select id,itemName from '.tablename('customs_declare_risk_list').' where  openid=:openid and itemNo=:itemNo',[':openid'=>$openid,':itemNo'=>$v[0]]);
                    if(empty($list)){
                        pdo_insert('customs_declare_risk_list',[
                            'openid'=>$openid,
                            'itemNo'=>trim($v[0]),
                            'itemName'=>trim($v[1]),
                            'gcode'=>trim($v[2]),
                            'currency'=>trim($v[3]),
                            'qty'=>trim($v[4]),
                            'qty1'=>trim($v[5]),
                            'gmodel'=>trim($v[6]),
                            'price'=>trim($v[7]),
                            'totalPrice'=>trim($v[8]),
                            'charge'=>trim($v[9]),
                            'chargeDate'=>trim($v[10]),
                            'logisticsNo'=>trim($v[11]),
                            'freight'=>trim($v[12]),
                            'insuredFee'=>trim($v[13]),
                            'barCode'=>trim($v[14]),
                            'grossWeight'=>trim($v[15]),
                            'netWeight'=>trim($v[16]),
                            'packNo'=>trim($v[17]),
                            'goodsInfo'=>trim($v[18]),
                            'unit'=>trim($v[19]),
                            'unit1'=>trim($v[20]),
                        ]);
                    }
                    else{
                        pdo_update('customs_declare_risk_list',[
                            'itemName'=>trim($v[1]),
                            'gcode'=>trim($v[2]),
                            'currency'=>trim($v[3]),
                            'qty'=>trim($v[4]),
                            'qty1'=>trim($v[5]),
                            'gmodel'=>trim($v[6]),
                            'price'=>trim($v[7]),
                            'totalPrice'=>trim($v[8]),
                            'charge'=>trim($v[9]),
                            'chargeDate'=>trim($v[10]),
                            'logisticsNo'=>trim($v[11]),
                            'freight'=>trim($v[12]),
                            'insuredFee'=>trim($v[13]),
                            'barCode'=>trim($v[14]),
                            'grossWeight'=>trim($v[15]),
                            'netWeight'=>trim($v[16]),
                            'packNo'=>trim($v[17]),
                            'goodsInfo'=>trim($v[18]),
                            'unit'=>trim($v[19]),
                            'unit1'=>trim($v[20]),
                        ],[
                            'openid'=>$openid,
                            'itemNo'=>trim($v[0]),
                        ]);
                    }
                }

//                sleep(2);
                //查询风控列表hscode为空的商品
                $list = pdo_fetchall('select id,itemName from '.tablename('customs_declare_risk_list').' where gcode="" and openid=:openid',[':openid'=>$openid]);
                $empty_ids='';
                foreach($list as $k=>$v){
                    //1、先查询数据表，有无此品名商品
                    $hscode = pdo_fetchall('select basic_info,hscode,id from '.tablename('customs_hscode_tariffschedule_ssl').' where `name` like "%'.$v['itemName'].'%"');
                    if(empty($hscode)){
                        //2、若查找不到，则通过品名查找归类通的品名关键字
                        $hscode2 = pdo_fetchcolumn('select hscode from '.tablename('customs_hscode_gui_lei_tong').' where keyword=:keyword order by id asc',[':keyword'=>$v['itemName']]);
                        if(empty($hscode2)){
                            //3、查询归类通/HSCIQ
                            $hscode2 = m('common')->getGuiLeiTong($v['itemName']);
                        }
                        $hscode = pdo_fetchall('select basic_info,hscode,id from '.tablename('customs_hscode_tariffschedule_ssl').' where hscode=:hscode',[':hscode'=>$hscode2]);
                    }

                    if(empty($hscode)){
                        $empty_ids .= $v['id'].',';
                    }else{
                        $count = count($hscode);
                        foreach($hscode as $k2=>$v2){
                            $basic_info = json_decode($v2['basic_info'],true);
                            if($basic_info[11]!='作废'){
                                //获取hscode、unit、unit1
                                $unit='';
                                if($basic_info[3][1]!='' || $basic_info[3][1]!='无'){
                                    $unit = pdo_fetchcolumn('select code_value from '.tablename('unit').' where code_name=:code_name',[':code_name'=>$basic_info[3][1]]);
                                }

                                pdo_update('customs_declare_risk_list',['gcode'=>$v2['hscode'],'unit'=>$unit,'unit1'=>$unit],['id'=>$v['id'],'openid'=>$openid]);
                            }else{
                                if($basic_info[11]=='作废' && $count==1){
                                    //2、若查找不到，则通过品名查找归类通的品名关键字，待做
                                    $empty_ids .= $v['id'].',';
                                }
                            }
                        }
                    }
                }

                if(!empty($empty_ids)){
                    show_json(-2,['msg'=>'您好！经系统补缺筛查，发现有商品还处于归类缺省，正在跳转人工补缺！','need_fill_ids'=>$empty_ids]);
                }else{
                    show_json(1,['msg'=>'系统补缺完成！']);
                }

            }
        }
    }
    include $this->template('declare/report_risk/system_fill_page');
}elseif($op=='manual_fill_page2'){
    //人工补缺
    $ids = rtrim($_GPC['ids'],',');

    $type = intval($_GPC['type']);
    include $this->template('declare/report_risk/manual_fill_page2');
}elseif($op=='upload_list2'){
    //上传清单
    $ids = $_GPC['ids'];
    $type = intval($_GPC['type']);
    $res = m('excel')->import('file');
    //读取excel内容
    $is_true=1;
    $msg = '';
    if(empty($type)){
        foreach($res as $k=>$v){
            foreach($v as $kk=>$vv) {
                //企业商品货号
                if($kk==0 && empty($vv)){
                    $is_true=0;
                    $msg='第'.($k+2).'行的第'.($kk+1).'列，请保留原有企业商品货号！';
                }

                //商品编码
                if($kk==2){
                    $ishave = pdo_fetchcolumn('select id from '.tablename('customs_hscode_tariffschedule_ssl').' where hscode=:hscode',[':hscode'=>trim($vv)]);
                    if(empty($ishave)){
                        $is_true=0;
                        $msg='第'.($k+2).'行的第'.($kk+1).'列，请输入正确的商品编码！';
                    }
                }
                //申报计量单位&&法定计量单位
                if($kk==3 || $kk==4){
                    //查找计量单位和法定计量单位是否正确
                    $is_corr = pdo_fetchcolumn('select code_value from '.tablename('unit').' where code_name=:code_name',[':code_name'=>trim($vv)]);
                    if(empty($is_corr)){
                        $is_true=0;
                        $msg='第'.($k+2).'行的第'.($kk+1).'列，请输入正确的计量单位编码！';
                    }
                }
            }
        }

        if(empty($is_true)){
            show_json(-1,['msg'=>$msg]);
        }else {
            //人工补缺，数据都不为空
            foreach($res as $k=>$v){
                //查询表是否有此品名和hscode
                $ishave = pdo_fetch('select id from '.tablename('customs_hscode_gui_lei_tong').' where hscode=:hscode and keyword=:keyword',[':hscode'=>trim($v[2]),':keyword'=>$v[1]]);
                if(empty($ishave['id'])){
                    //记录用户插入的数据
                    pdo_insert('customs_hscode_gui_lei_tong',['hscode'=>trim($v[2]),'keyword'=>trim($v[1])]);
                }
                $unit = pdo_fetchcolumn('select code_value from '.tablename('unit').' where code_name=:code_name',[':code_name'=>trim($v[3])]);
                $unit1 = pdo_fetchcolumn('select code_value from '.tablename('unit').' where code_name=:code_name',[':code_name'=>trim($v[4])]);
                pdo_update('customs_declare_risk_list',['gcode'=>trim($v[2]),'unit'=>$unit,'unit1'=>$unit1],['itemNo'=>$v[0],'openid'=>$openid]);
            }
            show_json(1,['msg'=>'人工补缺成功！']);
        }
    }
}elseif($op=='download_list2'){
    //下载清单
    $ids = $_GPC['ids'];
    $type=intval($_GPC['type']);
    $list = pdo_fetchall('select * from '.tablename('customs_declare_risk_list').' where openid=:openid and id in ('.$ids.')',[':openid'=>$openid]);
    if(empty($type)){
        //货值缺省
        $columns = array(
            array(
                'title' => '企业商品货号',
                'field' => 'itemNo',
                'width' => 16
            ),
            array(
                'title' =>'商品名称',
                'field' => 'itemName',
                'width' => 24
            ),
            array(
                'title' =>'商品编码',
                'field' => 'gcode',
                'width' => 12
            ),
            array(
                'title' =>'申报计量单位(中文)',
                'field' => 'unit',
                'width' => 8
            ),
            array(
                'title' =>'法定计量单位(中文)',
                'field' => 'unit1',
                'width' => 8
            ),
        );
        m('excel')->export($list , array(
            "title" => "商品归类-人工补缺清单",
            "columns" => $columns
        ));
    }
}elseif($op=='good_edit2'){
    //商品编辑
    $id = intval($_GPC['id']);
    $pre_batch_num = trim($_GPC['pre_batch_num']);

    if($_W['isajax']){
        $hscode = trim($_GPC['gcode']);
        $is_corr = pdo_fetch('select id,basic_info from '.tablename('customs_hscode_tariffschedule_ssl').' where hscode=:hscode',[':hscode'=>$hscode]);
        if(empty($is_corr['id'])){
            show_json(-1,['msg'=>'请输入正确的商品编码！']);
        }else{
            $is_corr['basic_info'] = json_decode($is_corr['basic_info'],true);
            if($is_corr['basic_info'][5][1]=='作废'){
                show_json(-1,['msg'=>'当前编码已作废，请输入正确的商品编码！']);
            }
            $unit = pdo_fetchcolumn('select code_value from '.tablename('unit').' where code_name=:code_name',[':code_name'=>$is_corr['basic_info'][3][1]]);
            pdo_update('customs_declare_risk_list',['gcode'=>$hscode,'unit'=>$unit,'unit1'=>$unit],['id'=>$id,'openid'=>$openid]);
            show_json(1,['msg'=>'修改成功！']);
        }
    }else{
        $data = pdo_fetch('select * from '.tablename('customs_declare_risk_list').' where id=:id and openid=:openid',[':id'=>$id,':openid'=>$openid]);
    }

    include $this->template('declare/report_risk/good_edit');
}elseif($op=='many_good_edit'){
    $ids = trim($_GPC['ids']);
    $type = intval($_GPC['type']);

    if($_W['isajax']){
        $gcode = $_GPC['gcode'];
        $gname = $_GPC['itemName'];
        $ids_arr = explode(',',$ids);

       foreach($gcode as $k=>$v){
           $is_corr = pdo_fetch('select id,basic_info from '.tablename('customs_hscode_tariffschedule_ssl').' where hscode=:hscode',[':hscode'=>trim($v)]);
           if(empty($is_corr['id'])){
               show_json(-1,['msg'=>'请输入正确的商品编码！']);
           }else {
               $is_corr['basic_info'] = json_decode($is_corr['basic_info'], true);
               if ($is_corr['basic_info'][5][1] == '作废') {
                   show_json(-1, ['msg' => '当前编码('.$v.')已作废，请输入正确的商品编码！']);
               }
               $unit = pdo_fetchcolumn('select code_value from '.tablename('unit').' where code_name=:code_name',[':code_name'=>$is_corr['basic_info'][3][1]]);
               pdo_update('customs_declare_risk_list',['update_itemName'=>trim($gname[$k]),'gcode'=>trim($v),'unit'=>$unit,'unit1'=>$unit],['id'=>$ids_arr[$k],'openid'=>$openid]);
           }
       }
       show_json(1,['msg'=>'修改成功！']);
    }
    $list = pdo_fetchall('select * from '.tablename('customs_declare_risk_list').' where openid=:openid and id in ('.$ids.') order by id desc',[':openid'=>$openid]);
    foreach($list as $k=>$v){
        $list[$k]['gname'] = $v['itemName'];
        if(!empty($v['update_itemName'])){
            $list[$k]['gname'] = $v['update_itemName'];
        }
    }

    include $this->template('declare/report_risk/many_good_edit');
}elseif($op=='download_goods2'){
    //导出商品清单
    $list = pdo_fetchall('select itemNo,itemName,gcode,currency,qty,qty1,gmodel,price,totalPrice,charge,chargeDate,logisticsNo,freight,insuredFee,barCode,grossWeight,netWeight,packNo,goodsInfo,unit,unit1 from '.tablename('customs_declare_risk_list').'  where openid=:openid ',[':openid'=>$openid]);

    $columns = array(
        array(
            'title' => '企业商品货号',
            'field' => 'itemNo',
            'width' => 16
        ),
        array(
            'title' =>'商品名称',
            'field' => 'itemName',
            'width' => 24
        ),
        array(
            'title' =>'商品编码',
            'field' => 'gcode',
            'width' => 24
        ),
        array(
            'title' =>'币种',
            'field' => 'currency',
            'width' => 8
        ),
        array(
            'title' =>'申报数量',
            'field' => 'qty',
            'width' => 12
        ),
        array(
            'title' =>'法定数量',
            'field' => 'qty1',
            'width' => 12
        ),
        array(
            'title' =>'规格型号',
            'field' => 'gmodel',
            'width' => 24
        ),
        array(
            'title' =>'FOB单价',
            'field' => 'price',
            'width' => 12
        ),
        array(
            'title' =>'FOB总价',
            'field' => 'totalPrice',
            'width' => 12
        ),
        array(
            'title' =>'收款金额',
            'field' => 'charge',
            'width' => 12
        ),
        array(
            'title' =>'到账时间',
            'field' => 'chargeDate',
            'width' => 12
        ),
        array(
            'title' =>'物流运单编号',
            'field' => 'logisticsNo',
            'width' => 24
        ),
        array(
            'title' =>'运费',
            'field' => 'freight',
            'width' => 12
        ),
        array(
            'title' =>'保价费',
            'field' => 'insuredFee',
            'width' => 12
        ),
        array(
            'title' =>'条形码',
            'field' => 'barCode',
            'width' => 12
        ),
        array(
            'title' =>'毛重',
            'field' => 'grossWeight',
            'width' => 12
        ),
        array(
            'title' =>'净重',
            'field' => 'netWeight',
            'width' => 12
        ),
        array(
            'title' =>'件数',
            'field' => 'packNo',
            'width' => 8
        ),
        array(
            'title' =>'主要货物信息',
            'field' => 'goodsInfo',
            'width' => 24
        ),
        array(
            'title' =>'申报计量单位',
            'field' => 'unit',
            'width' => 16
        ),
        array(
            'title' =>'法定计量单位',
            'field' => 'unit1',
            'width' => 16
        ),
    );
    m('excel')->export($list , array(
        "title" => "商品清单",
        "columns" => $columns
    ));
}