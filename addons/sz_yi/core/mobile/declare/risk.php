<?php
// 模块LTD提供
if (!defined('IN_IA')) {
    exit('Access Denied');
}

/**
 * 出口风控管理
 * 2022-04-02
 */
global $_W;
global $_GPC;

$method = "GET";
$key='t3809703680';
$secret='20220324';
$config = pdo_fetch('select * from '.tablename('export_risk_gross_adjust').' where id=1');

$openid=$_W['openid'];
$op = !empty($_GPC['op'])?trim($_GPC['op']):'display';

if($op=='display'){
    include $this->template('declare/risk/index');
}elseif($op=='transaction_risk_control'){
    //交易风控
    if($_GPC['pa']==1){
        $limit = intval($_GPC['limit']);
        $page = intval($_GPC['page']) - 1;
        if ($page != 0) {
            $page = $limit * $page;
        }
        $list = pdo_fetchall('select * from '.tablename('customs_pre_declare').' where openid=:openid and withhold_status=0 order by id desc limit '.$page.",".$limit,[':openid'=>$openid]);
        foreach($list as $k=>$v){
            $list[$k]['createtime'] = date('Y-m-d H:i',$v['createtime']);
        }
        $count = pdo_fetch('select count(id) as c from '.tablename('customs_pre_declare').' where openid=:openid and withhold_status=0',[':openid'=>$openid]);
        die(json_encode(['code'=>0,'count'=>$count['c'],'data'=>$list]));
    }
    include $this->template('declare/risk/transaction_risk_control');
}elseif($op=='order_list'){
    //订单概览
    $id = intval($_GPC['id']);
    $type = intval($_GPC['typ']);
    $batch_num = !empty($_GPC['batch_num'])?$_GPC['batch_num']:'';
    $logisticsNo = !empty($_GPC['logisticsNo'])?$_GPC['logisticsNo']:'';

    if($_GPC['pa']==1){
        if($type==2){
            //系统补缺后清单表
            $orderList = [];
            $orderList[0]['default_info'] = pdo_fetch('select sum(packNo) as packNo,sum(qty) as qty,sum(grossWeight) as grossWeight from '.tablename('customs_goods_pre_log').' where logisticsNo=:logn and pre_batch_num=:p and openid=:openid',[':logn'=>$logisticsNo,':p'=>$batch_num,':openid'=>$openid]);
            $is_adjust = pdo_fetch('select value_status from '.tablename('customs_pre_declare').' where pre_batch_num=:batch_num and openid=:openid',[':batch_num'=>$batch_num,':openid'=>$openid]);
            if($is_adjust['value_status']==1){
                $money_info = pdo_fetchall('select b.currency,a.totalPrice from '.tablename('customs_goods_pre_adjust_log').' a left join '.tablename('customs_goods_pre_fill_log').' b on b.id=a.fill_id where a.logisticsNo=:logn and a.pre_batch_num=:p and a.openid=:openid',[':logn'=>$logisticsNo,':p'=>$batch_num,':openid'=>$openid]);
            }else{
                $money_info = pdo_fetchall('select a.currency,a.totalPrice from '.tablename('customs_goods_pre_fill_log').' a left join '.tablename('customs_goods_pre_log').' b on a.good_id=b.id where a.logisticsNo=:logn and b.pre_batch_num=:p and a.openid=:openid',[':logn'=>$logisticsNo,':p'=>$batch_num,':openid'=>$openid]);
            }

            if(empty($money_info)){
                $trueList = [];
                die(json_encode(['code'=>0,'data'=>$trueList]));
            }
            foreach($money_info as $k2=>$v2){
                $orderList[0]['money_info'][$v2['currency']] += $v2['totalPrice'];
            }
            $orderList[0]['logisticsNo'] = $logisticsNo;

            $trueList = [];
            foreach($orderList as $k=>$v){
                $trueList[$k]['all_num'] = $v['default_info']['packNo'].'个包裹'.$v['default_info']['qty'].'件商品';
                $trueList[$k]['all_weight'] = $v['default_info']['grossWeight'].'Kgs';
                $trueList[$k]['logisticsNo'] = $v['logisticsNo'];
                //总值
                foreach($v['money_info'] as $k2=>$v2){
                    $currency = pdo_fetch('select * from '.tablename('currency').' where code_value=:code_value',[':code_value'=>$k2]);
                    $trueList[$k]['all_money'][] = $currency['code_name'].'/'.sprintf("%.2f",$v2);
//                sprintf("%.2f",substr(sprintf("%.3f", $v2), 0, -2))
                }
            }
        }
        else{
            //原始清单
            $declare_info = pdo_fetch('select pre_batch_num from '.tablename('customs_pre_declare').' where id=:id and openid=:openid and withhold_status=0',[':id'=>$id,':openid'=>$openid]);
            //1个运单=1个订单
            $goods_info = pdo_fetchall('select distinct logisticsNo from '.tablename('customs_goods_pre_log').' where pre_batch_num=:p and openid=:openid',[':p'=>$declare_info['pre_batch_num'],':openid'=>$openid]);
            $orderList = [];
            foreach($goods_info as $k=>$v){
                $orderList[$k]['default_info'] = pdo_fetch('select sum(packNo) as packNo,sum(qty) as qty,sum(grossWeight) as grossWeight  from '.tablename('customs_goods_pre_log').' where logisticsNo=:logn and pre_batch_num=:p and openid=:openid',[':logn'=>$v['logisticsNo'],':p'=>$declare_info['pre_batch_num'],':openid'=>$openid]);
                $money_info = pdo_fetchall('select currency,totalPrice from '.tablename('customs_goods_pre_log').' where logisticsNo=:logn and pre_batch_num=:p and openid=:openid',[':logn'=>$v['logisticsNo'],':p'=>$declare_info['pre_batch_num'],':openid'=>$openid]);
                foreach($money_info as $k2=>$v2){
                    $orderList[$k]['money_info'][$v2['currency']] += $v2['totalPrice'];
                }
                $orderList[$k]['logisticsNo'] = $v['logisticsNo'];
            }

            $trueList = [];
            foreach($orderList as $k=>$v){
                $trueList[$k]['all_num'] = $v['default_info']['packNo'].'个包裹'.$v['default_info']['qty'].'件商品';
                $trueList[$k]['all_weight'] = $v['default_info']['grossWeight'].'Kgs';
                $trueList[$k]['logisticsNo'] = $v['logisticsNo'];
                //总值
                foreach($v['money_info'] as $k2=>$v2){
                    $currency = pdo_fetch('select * from '.tablename('currency').' where code_value=:code_value',[':code_value'=>$k2]);
                    $trueList[$k]['all_money'][] = $currency['code_name'].'/'.sprintf("%.2f",$v2);
//                sprintf("%.2f",substr(sprintf("%.3f", $v2), 0, -2))
                }
            }
        }

        die(json_encode(['code'=>0,'data'=>$trueList]));
    }
    include $this->template('declare/risk/order_list');
}elseif($op=='transaction_manage'){
    //交易管理
    $id = intval($_GPC['id']);
    include $this->template('declare/risk/transaction_manage');
}elseif($op=='value_manage'){
    //货值管理
    $id = intval($_GPC['id']);

    if($_GPC['pa']==1){
        //查找该预报id下的运单信息（运单号）
        $list = pdo_fetchall('select distinct b.logisticsNo,b.pre_batch_num from '.tablename('customs_pre_declare').' a left join '.tablename('customs_goods_pre_log').' b on b.pre_batch_num=a.pre_batch_num where a.id=:id and a.openid=:openid',[':id'=>$id,':openid'=>$openid]);
        foreach($list as $k=>$v){
            //每个商品的运算项信息是否为空-货值缺省补缺、每个商品的规格是否为空-规格缺省补缺
        }
        die(json_encode(['code'=>0,'data'=>$list]));
    }else{
        $pre_batch_num = pdo_fetchcolumn('select pre_batch_num from '.tablename('customs_pre_declare').' where openid=:openid and id=:id',[':openid'=>$openid,':id'=>$id]);
    }

    include $this->template('declare/risk/value_manage');
}elseif($op=='value_default'){
    //货值-缺省列表
    $logisticsNo = trim($_GPC['logisticsNo']);
    $batch_num = trim($_GPC['batch_num']);

    if($_GPC['pa']==1){
        //查询商品原始清单表，找出那些价格为空的商品
//        $list = pdo_fetchall('select a.type,a.price,a.logisticsNo,a.totalPrice,a.charge,a.chargeDate,a.unit,a.unit1,b.itemNo,b.itemName,b.id,a.onebound_gid from '.tablename('customs_goods_pre_fill_log').' a left join '.tablename('customs_goods_pre_log').' b on b.id=a.good_id where b.openid=:openid and b.pre_batch_num=:batch_num and b.logisticsNo=:logi and (a.price="" or a.price=0)',[':openid'=>$openid,':batch_num'=>$batch_num,':logi'=>$logisticsNo]);
//        $list = pdo_fetchall('select * from '.tablename('customs_goods_pre_fill_log').' where openid=:openid and pre_batch_num=:batch_num and logisticsNo=:logi and (price="" or price=0)',[':openid'=>$openid,':batch_num'=>$batch_num,':logi'=>$logisticsNo]);
//        if(empty($list)){
            $list = pdo_fetchall('select * from '.tablename('customs_goods_pre_log').' where openid=:openid and pre_batch_num=:batch_num and logisticsNo=:logi and (price="" or price=0) ',[':openid'=>$openid,':batch_num'=>$batch_num,':logi'=>$logisticsNo]);
            foreach($list as $k=>$v){
                //查找补缺清单的单价是否也为空或不存在
                $is_have = pdo_fetch('select id,`type` from '.tablename('customs_goods_pre_fill_log').' where good_id=:gid and price>0',[':gid'=>$v['id']]);
                if(!empty($is_have['id'])){
                    unset($list[$k]);
                }else{
                    $is_have = pdo_fetch('select id,`type` from '.tablename('customs_goods_pre_fill_log').' where good_id=:gid',[':gid'=>$v['id']]);
                    if(!empty($is_have['id'])){
                        $list[$k]['type']=$is_have['type'];
                    }
                }
            }

        $list = array_merge($list);
//        }

        die(json_encode(['code'=>0,'data'=>$list]));
    }

    include $this->template('declare/risk/value_default');
}elseif($op=='download_list'){
    //货值-下载清单
    $ids = $_GPC['ids'];
    $type=intval($_GPC['type']);

    $list = pdo_fetchall('select * from '.tablename('customs_goods_pre_log').' where openid=:openid and id in ('.$ids.')',[':openid'=>$openid]);


    $name = $list[0]['logisticsNo'];
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
                'title' =>'运单号',
                'field' => 'logisticsNo',
                'width' => 24
            ),
            array(
                'title' =>'件数',
                'field' => 'packNo',
                'width' => 8
            ),
            array(
                'title' =>'币种',
                'field' => 'currency',
                'width' => 8
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
            "title" => $name."货值-补缺清单",
            "columns" => $columns
        ));
    }
    else{
        //规格型号缺省
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
                'title' =>'规格型号',
                'field' => 'gmodel',
                'width' => 24
            ),
        );
        m('excel')->export($list , array(
            "title" => $name."规格-补缺清单",
            "columns" => $columns
        ));
    }


//    include $this->template('declare/risk/download_list');
}elseif($op=='upload_list'){
    //货值+规格-上传清单
    $ids = $_GPC['ids'];
    $type = intval($_GPC['type']);
    $batch_num = trim($_GPC['batch_num']);
    $res = m('excel')->import('file');
    //读取excel内容
    $is_true=1;
    $msg = '';

    if(empty($type)){
        //货值-执行数据表为空时判断
        foreach($res as $k=>$v){
            foreach($v as $kk=>$vv){
                if($kk!=9 && $kk!=10){
                    if(($kk!=12 && $kk!=13 && $kk!=8 && $kk!=4) && empty($msg)){
                        if((empty($vv) || $vv=='0.00') && empty($msg)){
                            $is_true=0;
                            $msg='第'.($k+2).'行的第'.($kk+1).'列不能为空！';
                        }
                    }elseif(empty($msg)){
                        if($kk==12 && $kk==13){
                            //查找计量单位和法定计量单位是否正确
                            $is_corr = pdo_fetchcolumn('select code_name from '.tablename('unit').' where code_value=:code_value',[':code_value'=>$vv]);
                            if(empty($is_corr)){
                                $is_true=0;
                                $msg='第'.($k+2).'行的第'.($kk+1).'列，请输入正确的计量单位编码！';
                            }
                        }

                        if($kk==8){
                            //到账时间
                            if($vv==44287){
                                $is_true=0;
                                $msg='第'.($k+2).'行的第'.($kk+1).'列，请将该列设置为文本单元格格式！';
                            }elseif(empty($vv)){
                                $is_true=0;
                                $msg='第'.($k+2).'行的第'.($kk+1).'列不能为空！';
                            }
                        }

                        if($kk==4){
                            //币种
                            $is_corr = pdo_fetchcolumn('select code_name from '.tablename('currency').' where code_value=:code_value',[':code_value'=>$vv]);
                            if(empty($is_corr)){
                                $is_true=0;
                                $msg='第'.($k+2).'行的第'.($kk+1).'列，请输入正确的币种编码！';
                            }
                        }

                    }
                }
            }
        }

        if(empty($is_true)){
            show_json(-1,['msg'=>$msg]);
        }else{
            //人工补缺，数据都不为空
            foreach($res as $k=>$v){
                $g_info = pdo_fetch('select * from '.tablename('customs_goods_pre_log').' where itemNo=:itemNo',[':itemNo'=>$v[0]]);
                $is_have = pdo_fetch('select id from '.tablename('customs_goods_pre_fill_log').' where good_id=:gid',[':gid'=>$g_info['id']]);
                if($is_have['id']>0){
                    pdo_update('customs_goods_pre_fill_log',[
                        'currency'=>trim($v[4]),
                        'price'=>trim($v[5]),
                        'totalPrice'=>trim($v[6]),
                        'charge'=>trim($v[7]),
                        'chargeDate'=>trim($v[8]),
                        'freight'=>trim($v[9]),
                        'insuredFee'=>trim($v[10]),
                        'barCode'=>trim($v[11]),
                        'unit'=>trim($v[12]),
                        'unit1'=>trim($v[13]),
                    ],['good_id'=>$g_info['id']]);
                }else{
                    pdo_insert('customs_goods_pre_fill_log',[
                        'pre_batch_num'=>$batch_num,
                        'openid'=>$openid,
                        'type'=>1,
                        'itemNo'=>trim($v[0]),
                        'itemName'=>trim($v[1]),
                        'gcode'=>$g_info['gcode'],
                        'currency'=>trim($v[4]),
                        'qty'=>$g_info['qty'],
                        'qty1'=>$g_info['qty1'],
                        'gmodel'=>$g_info['gmodel'],
                        'price'=>trim($v[5]),
                        'totalPrice'=>trim($v[6]),
                        'charge'=>trim($v[7]),
                        'chargeDate'=>trim($v[8]),
                        'logisticsNo'=>trim($v[2]),
                        'freight'=>trim($v[9]),
                        'insuredFee'=>trim($v[10]),
                        'barCode'=>trim($v[11]),
                        'grossWeight'=>$g_info['grossWeight'],
                        'netWeight'=>$g_info['netWeight'],
                        'packNo'=>trim($v[3]),
                        'goodsInfo'=>$g_info['goodsInfo'],
//						$this->getUnit($onebound_total_goods['goods_desc']['sellUnit'])
                        'unit'=>trim($v[12]),
                        'unit1'=>trim($v[13]),
                        'goodsInfo'=>$g_info['goodsInfo'],
                        'good_id'=>$g_info['id'],
                        'is_fill'=>0
                    ]);
                }
            }
            show_json(1,['msg'=>'货值补缺成功！']);
        }
    }
    else{
        foreach($res as $k=>$v) {
            $gid = pdo_fetchcolumn('select id from '.tablename('customs_goods_pre_log').' where itemNo=:itemNo',[':itemNo'=>$v[0]]);
            $is_have = pdo_fetch('select id from ' . tablename('customs_goods_pre_fill_log') . ' where good_id=:gid', [':gid' => $gid]);
            if(empty($is_have['id'])) {
                show_json(-1,['msg'=>'请先完成货值补缺!']);
            }
        }
        //规格
        foreach($res as $k=>$v){
            foreach($v as $kk=>$vv){
                if((empty($vv) || $vv=='0.00') && empty($msg)){
                    $is_true=0;
                    $msg='第'.($k+2).'行的第'.($kk+1).'列不能为空！';
                }
            }
        }

        if(empty($is_true)){
            show_json(-1,['msg'=>$msg]);
        }else{
            //人工补缺，数据都不为空
            foreach($res as $k=>$v){
                $g_info = pdo_fetch('select * from '.tablename('customs_goods_pre_log').' where itemNo=:itemNo',[':itemNo'=>trim($v[0])]);
                $is_have = pdo_fetch('select id from '.tablename('customs_goods_pre_fill_log').' where good_id=:gid',[':gid'=>$gid]);
                if($is_have['id']>0){
                    pdo_update('customs_goods_pre_fill_log',[
                        'itemName'=>trim($v[1]),
                        'gmodel'=>trim($v[2]),
                    ],['good_id'=>$g_info['id']]);
                }else{
                    pdo_insert('customs_goods_pre_fill_log',[
                        'pre_batch_num'=>$batch_num,
                        'openid'=>$openid,
                        'type'=>1,
                        'itemNo'=>trim($v[0]),
                        'itemName'=>trim($v[1]),
                        'gcode'=>$g_info['gcode'],
                        'currency'=>$g_info['currency'],
                        'qty'=>$g_info['qty'],
                        'qty1'=>$g_info['qty1'],
                        'gmodel'=>trim($v[2]),
                        'price'=>$g_info['price'],
                        'totalPrice'=>$g_info['totalPrice'],
                        'charge'=>$g_info['charge'],
                        'chargeDate'=>$g_info['chargeDate'],
                        'logisticsNo'=>$g_info['logisticsNo'],
                        'freight'=>$g_info['freight'],
                        'insuredFee'=>$g_info['insuredFee'],
                        'barCode'=>$g_info['barCode'],
                        'grossWeight'=>$g_info['grossWeight'],
                        'netWeight'=>$g_info['netWeight'],
                        'packNo'=>$g_info['packNo'],
                        'goodsInfo'=>$g_info['goodsInfo'],
//						$this->getUnit($onebound_total_goods['goods_desc']['sellUnit'])
                        'unit'=>$g_info['unit'],
                        'unit1'=>$g_info['unit1'],
                        'goodsInfo'=>$g_info['goodsInfo'],
                        'good_id'=>$g_info['id'],
                        'is_fill'=>0
                    ]);
                }
            }
            show_json(1,['msg'=>'规格补缺成功！']);
        }
    }

}elseif($op=='manual_fill_page'){
    //人工补缺
    $ids = rtrim($_GPC['ids'],',');
    $type = intval($_GPC['type']);
    $batch_num = trim($_GPC['batch_num']);
    include $this->template('declare/risk/manual_fill_page');
}elseif($op=='specific_default'){
    //规格-缺省列表
    $logisticsNo = $_GPC['logisticsNo'];
    $batch_num = $_GPC['batch_num'];
    if($_GPC['pa']==1){
        //先查询补缺后的表
//        $list = pdo_fetchall('select a.type,a.price,a.logisticsNo,a.totalPrice,a.charge,a.chargeDate,a.unit,a.unit1,b.itemNo,b.itemName,b.id,a.onebound_gid,a.gmodel from '.tablename('customs_goods_pre_fill_log').' a left join '.tablename('customs_goods_pre_log').' b on b.id=a.good_id where b.openid=:openid and b.pre_batch_num=:batch_num and b.logisticsNo=:logi and a.gmodel=""',[':openid'=>$openid,':batch_num'=>$batch_num,':logi'=>$logisticsNo]);
//        if(empty($list)) {
//            $list = pdo_fetchall('select * from ' . tablename('customs_goods_pre_log') . ' where logisticsNo=:logisticsNo and pre_batch_num=:batch_num and gmodel="" and openid=:openid', [':logisticsNo' => $logisticsNo, ':batch_num' => $batch_num, ':openid' => $openid]);
//        }

        $list = pdo_fetchall('select * from ' . tablename('customs_goods_pre_log') . ' where logisticsNo=:logisticsNo and pre_batch_num=:batch_num and gmodel="" and openid=:openid', [':logisticsNo' => $logisticsNo, ':batch_num' => $batch_num, ':openid' => $openid]);
        foreach($list as $k=>$v){
            //查找补缺清单的规格型号是否也为空或不存在
            $is_have = pdo_fetch('select id from '.tablename('customs_goods_pre_fill_log').' where good_id=:gid and gmodel!=""',[':gid'=>$v['id']]);
            if(!empty($is_have['id'])){
                unset($list[$k]);
            }
        }
        $list = array_merge($list);
        die(json_encode(['code'=>0,'data'=>$list]));
    }

    include $this->template('declare/risk/specific_default');
}elseif($op=='system_fill'){
    //货值-系统补缺
    $ids = rtrim($_GPC['ids'],',');
    $type = intval($_GPC['type']);
    $batch_num = trim($_GPC['batch_num']);

    //1、找出该订单下单价为空的商品
    $goods_origin_arr = pdo_fetchall('select itemName,logisticsNo from '.tablename('customs_goods_pre_log').' where openid=:openid and id in ('.$ids.')',[':openid'=>$openid]);
    //1.1、将相同名称的商品分割为1个
    $g_info = [];
    foreach($goods_origin_arr as $k=>$v){
        if(!m('common')->in_array_r($v['itemName'],$g_info)){
            array_push($g_info,['logisticsNo'=>$v['logisticsNo'],'itemName'=>$v['itemName']]);
        }
    }

    //1.2、查找该订单和该商品名下所有相同商品名的信息
    header('Content-type:text/html;charset=utf-8');

    foreach($g_info as $k=>$v){
//        if(mb_strlen($v['itemName'],'UTF8')>3){
//            //获取最后3个字段搜查
//            $gname = mb_substr($v['itemName'],-3,3,'utf-8');
//        }
        $gname = $v['itemName'];//全字符查找
        //根据每个商品名去查找该订单下所有相同商品名的同伴为一个数组
        $g_info[$k]['info'] = pdo_fetchall('select * from '.tablename('customs_goods_pre_log').' where logisticsNo=:logi and itemName=:itemName and pre_batch_num=:batch_num',[':logi'=>$v['logisticsNo'],':itemName'=>$v['itemName'],':batch_num'=>$batch_num]);
        //1.3、如果不用比对（订单下只有一个相同的商品），则请求数据表或万邦接口
        if(count($g_info[$k]['info'])<2){
            $goods = m('common')->onebound_data_handle($gname,$config,$method,$key,$secret);//返回按“商品名称”搜索的结果
            if(!empty($goods)){
                $count = count($goods);
                $insert_data = '';
                $true_goods = '';
                if($count>3){
                    //获取最高价，中间价，最低价
                    $price = array_column($goods,'price');
                    array_multisort($price,SORT_DESC,$goods);
                    $max_price = $goods[0]['price'];//最高价
                    $min_price = $goods[$count-1]['price'];//最低价
                    $avg_price = ($max_price+$min_price)/2;//中间价 32.6
                    //找出与中间价相临近的价钱
                    $min_value = 0;//记录最小值
                    foreach($goods as $k2=>$v2){
                        $value = abs($avg_price-$v2['price']);//27.3 11.4  25.58 27.3
                        if(empty($min_value)){
                            $min_value=$value;
                            $true_goods = $v2;
                        }elseif($min_value>=$value){
                            $min_value=$value;
                            $true_goods = $v2;
                        }
                    }
                    $platform = '';
                    if($true_goods['type']==2){
                        //1688
                        $platform = '1688';
                    }elseif($true_goods['type']==1){
                        //淘宝
                        $platform = 'taobao';
                    }

                    //商品详情为空则查询万邦接口
                    if(empty($true_goods['goods_desc'])){
                        $res = m('common')->onebound_itemGet($platform,$key,$secret,$true_goods['num_iid'],$method);
                        $insert_data = [
                            'brand'=>$res['item']['brand'],//品牌
                            'desc'=>$res['item']['desc'],//描述
                            'item_imgs'=>$res['item']['item_imgs'],//商品图片
                            'item_weight'=>$res['item']['item_weight'],//商品重量
                            'post_fee'=>$res['item']['post_fee'],//邮费
                            'ems_fee'=>$res['item']['ems_fee'],//EMS费用
                            'express_fee'=>$res['item']['express_fee'],//EMS费用
                            'sellUnit'=>$res['item']['sellUnit'],//出售单位
                            'unit'=>$res['item']['unit'],//单位
                            'desc_img'=>$res['item']['desc_img'],//详情图片列表
                        ];
                        if(!empty($res['item']['skus']['sku'])){
                            //查询同一商品价钱的规格（随机）
                            foreach($res['item']['skus']['sku'] as $k2=>$v2){
                                if($v2['price']==$true_goods['price']){
                                    array_push($insert_data,['sku'=>$v2]);
                                    break;
                                }
                            }
                        }

                        pdo_update('onebound_total_goods',['goods_desc'=>json_encode($insert_data,true)],['id'=>$true_goods['id']]);
                    }
                }
                elseif($count<3 && $count>1){
                    //获取最高价
                    $price = array_column($goods,'price');
                    array_multisort($price,SORT_DESC,$goods);
                    $max_price = $goods[0]['price'];//最高价

                    foreach($goods as $k2=>$v2){
                        if($max_price==$v2['price']){
                            $true_goods = $v2;
                        }
                    }
                    $platform = '';
                    if($true_goods['type']==2){
                        //1688
                        $platform = '1688';
                    }elseif($true_goods['type']==1){
                        //淘宝
                        $platform = 'taobao';
                    }

                    //商品详情为空则查询万邦接口
                    if(empty($true_goods['goods_desc'])){
                        $res = m('common')->onebound_itemGet($platform,$key,$secret,$true_goods['num_iid'],$method);
                        $insert_data = [
                            'brand'=>$res['item']['brand'],//品牌
                            'desc'=>$res['item']['desc'],//描述
                            'item_imgs'=>$res['item']['item_imgs'],//商品图片
                            'item_weight'=>$res['item']['item_weight'],//商品重量
                            'post_fee'=>$res['item']['post_fee'],//邮费
                            'ems_fee'=>$res['item']['ems_fee'],//EMS费用
                            'express_fee'=>$res['item']['express_fee'],//EMS费用
                            'sellUnit'=>$res['item']['sellUnit'],//出售单位
                            'unit'=>$res['item']['unit'],//单位
                            'desc_img'=>$res['item']['desc_img'],//详情图片列表
                        ];
                        if(!empty($res['item']['skus']['sku'])){
                            //查询同一商品价钱的规格（随机）
                            foreach($res['item']['skus']['sku'] as $k2=>$v2){
                                if($v2['price']==$true_goods['price']){
                                    array_push($insert_data,['sku'=>$v2]);
                                    break;
                                }
                            }
                        }
                        pdo_update('onebound_total_goods',['goods_desc'=>json_encode($insert_data,true)],['id'=>$true_goods['id']]);
                    }
                }
                elseif($count==1){
                    //获取当前价格
                    $true_goods = $goods[0];
                    $platform = '';
                    if($true_goods['type']==2){
                        //1688
                        $platform = '1688';
                    }elseif($true_goods['type']==1){
                        //淘宝
                        $platform = 'taobao';
                    }

                    //商品详情为空则查询万邦接口
                    if(empty($true_goods['goods_desc'])){
                        $res = m('common')->onebound_itemGet($platform,$key,$secret,$true_goods['num_iid'],$method);
                        $insert_data = [
                            'brand'=>$res['item']['brand'],//品牌
                            'desc'=>$res['item']['desc'],//描述
                            'item_imgs'=>$res['item']['item_imgs'],//商品图片
                            'item_weight'=>$res['item']['item_weight'],//商品重量
                            'post_fee'=>$res['item']['post_fee'],//邮费
                            'ems_fee'=>$res['item']['ems_fee'],//EMS费用
                            'express_fee'=>$res['item']['express_fee'],//EMS费用
                            'sellUnit'=>$res['item']['sellUnit'],//出售单位
                            'unit'=>$res['item']['unit'],//单位
                            'desc_img'=>$res['item']['desc_img'],//详情图片列表
                        ];
                        if(!empty($res['item']['skus']['sku'])){
                            //查询同一商品价钱的规格（随机）
                            foreach($res['item']['skus']['sku'] as $k2=>$v2){
                                if($v2['price']==$true_goods['price']){
                                    array_push($insert_data,['sku'=>$v2]);
                                    break;
                                }
                            }
                        }
                        pdo_update('onebound_total_goods',['goods_desc'=>json_encode($insert_data,true)],['id'=>$true_goods['id']]);
                    }
                }

                //1.3.1、将补缺后的数据记录在补缺后的清单表
                $is_have = pdo_fetch('select id from '.tablename('customs_goods_pre_fill_log').' where good_id=:gid',[':gid'=>$g_info[$k]['info'][0]['id']]);
                if(isset($true_goods['id'])) {
                    $onebound_total_goods = pdo_fetch('select * from ' . tablename('onebound_total_goods') . ' where id=:id', [':id' => $true_goods['id']]);
                    $onebound_total_goods['goods_desc'] = json_decode($onebound_total_goods['goods_desc'],true);
                }

                if($is_have['id']>0){
                    pdo_update('customs_goods_pre_fill_log',[
                        'price'=>$g_info[$k]['info'][0]['price']>0?$g_info[$k]['info'][0]['price']:$onebound_total_goods['price'],
                        'totalPrice'=>$g_info[$k]['info'][0]['totalPrice']>0?$g_info[$k]['info'][0]['totalPrice']:($g_info[$k]['info'][0]['qty']*$onebound_total_goods['price']),//申报数量*单价
                        'charge'=>$g_info[$k]['info'][0]['charge']>0?$g_info[$k]['info'][0]['charge']:($g_info[$k]['info'][0]['qty']*$onebound_total_goods['price']),
                        'onebound_gid'=>$true_goods['id'],
                    ],['good_id'=>$g_info[$k]['info'][0]['id']]);
                }
                elseif(isset($true_goods['id'])){
                    pdo_insert('customs_goods_pre_fill_log',[
                        'pre_batch_num'=>$g_info[$k]['info'][0]['pre_batch_num'],
                        'openid'=>$openid,
                        'type'=>1,//同品同价同重
                        'itemNo'=>$g_info[$k]['info'][0]['itemNo'],
                        'itemName'=>$g_info[$k]['info'][0]['itemName'],
                        'gcode'=>$g_info[$k]['info'][0]['gcode'],
                        'logisticsNo'=>$g_info[$k]['info'][0]['logisticsNo'],
                        'currency'=>$g_info[$k]['info'][0]['currency'],
                        'qty'=>$g_info[$k]['info'][0]['qty'],
                        'qty1'=>$g_info[$k]['info'][0]['qty1'],
                        'gmodel'=>$g_info[$k]['info'][0]['gmodel'],
                        'price'=>$g_info[$k]['info'][0]['price']>0?$g_info[$k]['info'][0]['price']:$onebound_total_goods['price'],
                        'totalPrice'=>$g_info[$k]['info'][0]['totalPrice']>0?$g_info[$k]['info'][0]['totalPrice']:($g_info[$k]['info'][0]['qty']*$onebound_total_goods['price']),//申报数量*单价
                        'charge'=>$g_info[$k]['info'][0]['charge']>0?$g_info[$k]['info'][0]['charge']:($g_info[$k]['info'][0]['qty']*$onebound_total_goods['price']),
                        'chargeDate'=>$g_info[$k]['info'][0]['chargeDate'],
                        'freight'=>$g_info[$k]['info'][0]['freight'],
                        'insuredFee'=>$g_info[$k]['info'][0]['insuredFee'],
                        'barCode'=>$g_info[$k]['info'][0]['barCode'],
                        'grossWeight'=>$g_info[$k]['info'][0]['grossWeight'],
                        'netWeight'=>$g_info[$k]['info'][0]['netWeight'],
                        'packNo'=>$g_info[$k]['info'][0]['packNo'],
                        'goodsInfo'=>$g_info[$k]['info'][0]['goodsInfo'],
//                        m('common')->getUnit($onebound_total_goods['goods_desc']['sellUnit'])
                        'unit'=>!empty($g_info[$k]['info'][0]['unit'])?$g_info[$k]['info'][0]['unit']:'',
                        'unit1'=>!empty($g_info[$k]['info'][0]['unit1'])?$g_info[$k]['info'][0]['unit1']:'',
                        'good_id'=>$g_info[$k]['info'][0]['id'],
                        'onebound_gid'=>$true_goods['id'],
                        'is_fill'=>1,
                    ]);
                }
            }
        }
        else{
            //1.4、比对同品异重
            //方法：获取最小值，然后与同品下的商品毛重逐个比对
            $calc = 0;
            $count = count($g_info[$k]['info']);
            //获取同品下的毛重最小值
            $grossWeight = array_column($g_info[$k]['info'],'grossWeight');
            array_multisort($grossWeight,SORT_ASC,$g_info[$k]['info']);
            $min_grossWeight = $g_info[$k]['info'][0]['grossWeight']*2;

            $tong_pin_price_weight = [$g_info[$k]['info'][0]];//同品同价同重
            $tong_pin_yi_price_weight = [];//同品异价异重
            $yi_pin_price_weight = [];//异品异价异重

            //分组
            foreach($g_info[$k]['info'] as $k2=>$v2){
                //1.4.1、计算毛重相差是否不足1倍
                if($k2!=0){
                    if($min_grossWeight<=$v2['grossWeight']){
                        //异品异重异价
                        array_push($yi_pin_price_weight,$v2);
                    }else{
                        if($g_info[$k]['info'][0]['price']==$v2['price']){
                            //同品同重同价
                            array_push($tong_pin_price_weight,$v2);
                        }else{
                            //同品异重异价
                            array_push($tong_pin_yi_price_weight,$v2);
                        }
                    }
                }
            }

            //同品同重同价
            if(!empty($tong_pin_price_weight)){
                m('common')->tongPinPriceWeight($tong_pin_price_weight,$config,$method,$key,$secret,$openid);
            }

            //同品异重异价,先记录，然后叫用户选择按钮操作
            if(!empty($tong_pin_yi_price_weight)){
                foreach($tong_pin_yi_price_weight as $k2=>$v2){
                    pdo_insert('customs_goods_pre_fill_log',[
                        'pre_batch_num'=>$v2['pre_batch_num'],
						'openid'=>$openid,
						'type'=>2,
						'itemNo'=>$v2['itemNo'],
						'itemName'=>$v2['itemName'],
						'gcode'=>$v2['gcode'],
						'currency'=>$v2['currency'],
						'qty'=>$v2['qty'],
						'qty1'=>$v2['qty1'],
						'gmodel'=>$v2['gmodel'],
						'price'=>$v2['price'],
						'totalPrice'=>$v2['totalPrice'],//申报数量*单价
						'charge'=>$v2['charge'],
						'chargeDate'=>$v2['chargeDate'],
						'logisticsNo'=>$v2['logisticsNo'],
						'freight'=>$v2['freight'],
						'insuredFee'=>$v2['insuredFee'],
						'barCode'=>$v2['barCode'],
						'grossWeight'=>$v2['grossWeight'],
						'netWeight'=>$v2['netWeight'],
						'packNo'=>$v2['packNo'],
						'goodsInfo'=>$v2['goodsInfo'],
//						$this->getUnit($onebound_total_goods['goods_desc']['sellUnit'])
						'unit'=>!empty($v2['unit'])?$v2['unit']:'',
						'unit1'=>!empty($v2['unit1'])?$v2['unit1']:'',
						'good_id'=>$v2['id'],
						'is_fill'=>1
                    ]);
                }
            }

            //异品异价异重
            if(!empty($yi_pin_price_weight)) {
                m('common')->yiPinPriceWeight($yi_pin_price_weight,$config,$method,$key,$secret,$openid);
            }
        }
    }

    //2、价格缺省,查看是否因接口查找为空的原因，判断有无商品数据的价格还为0
    $goods_origin_arr2 = pdo_fetchall('select id,price,totalprice,qty from '.tablename('customs_goods_pre_log').' where openid=:openid and id in ('.$ids.')',[':openid'=>$openid]);
    $need_fill_ids = '';
    foreach($goods_origin_arr2 as $k2=>$v2){
        if(floatval($v2['price'])<=0){
            $is_empty_price = pdo_fetch('select price from '.tablename('customs_goods_pre_fill_log').' where openid=:openid and good_id=:gid',[':openid'=>$openid,':gid'=>$v2['id']]);
            if(floatval($is_empty_price['price'])<=0 || !isset($is_empty_price['price'])){
                $need_fill_ids+=$v2['id']+',';
            }
        }
    }
    if(!empty($need_fill_ids)){
        show_json(-1,['msg'=>'您好！经系统补缺筛查，发现有商品还处于价格缺省，正在跳转人工补缺！','need_fill_ids'=>$need_fill_ids]);
    }else{
        show_json(1,['msg'=>'补缺成功！正在刷新请等候...']);
    }
}elseif($op=='tongPinYiPrice'){
    //同品异价
    $itemName = trim($_GPC['itemName']);
    $logisticsNo = trim($_GPC['logisticsNo']);
    $batch_num = trim($_GPC['batch_num']);
    $tong_pin_yi_price = pdo_fetchall('select *,id as fid,good_id as id from '.tablename('customs_goods_pre_fill_log').' where openid=:openid and itemName=:itemName and logisticsNo=:logisticsNo and `type`=2 and pre_batch_num=:batch_num',[':openid'=>$openid,':itemName'=>$itemName,':logisticsNo'=>$logisticsNo,':batch_num'=>$batch_num]);
    $typ = intval($_GPC['typ']);
    if($typ==1){
        //同品同价
        m('common')->tongPinPriceWeight($tong_pin_yi_price,$config,$method,$key,$secret,$openid);
    }else{
        //异品异价
        m('common')->yiPinPriceWeight($tong_pin_yi_price,$config,$method,$key,$secret,$openid);
    }

    show_json(1,['msg'=>'操作完成！正在刷新请等候...']);
}elseif($op=='system_specific_fill'){
    //规格-系统补缺
    $ids = rtrim($_GPC['ids'],',');

    //1、查询该商品有无先生成货值缺省数据
    $goods_origin_arr = pdo_fetchall('select id,price,totalprice,qty,gmodel from '.tablename('customs_goods_pre_log').' where openid=:openid and id in ('.$ids.')',[':openid'=>$openid]);
    foreach($goods_origin_arr as $k2=>$v2){
        $is_empty_price = pdo_fetch('select price,gmodel,id,onebound_gid,is_fill,itemName from '.tablename('customs_goods_pre_fill_log').' where openid=:openid and good_id=:gid',[':openid'=>$openid,':gid'=>$v2['id']]);
        if(floatval($is_empty_price['price'])<=0){
            show_json(-1,['msg'=>'商品货值存在缺少，请先完成货值缺省补缺！']);
        }else{
            //规格补缺
            if(empty($is_empty_price['is_fill'])){
                //导入时价格不为空
                pdo_update('customs_goods_pre_fill_log',['gmodel'=>$is_empty_price['itemName']],['id'=>$is_empty_price['id']]);
            }else{
                //导入时价格为空
                $gmodel = pdo_fetch('select title from '.tablename('onebound_total_goods').' where id=:id',[':id'=>$is_empty_price['onebound_gid']]);
                pdo_update('customs_goods_pre_fill_log',['gmodel'=>$gmodel['title']],['id'=>$is_empty_price['id']]);
            }
        }
    }

    show_json(1,['msg'=>'系统补缺成功！正在刷新请等候...']);
}elseif($op=='total_value_manage'){
    //总值管理
    $batch_num = trim($_GPC['batch_num']);

    //获取币种
    $currency = pdo_fetchcolumn('select currency from '.tablename('customs_goods_pre_log').' where pre_batch_num=:pre_batch_num and openid=:openid order by id asc',[':pre_batch_num'=>$batch_num,':openid'=>$openid]);
    $currency = pdo_fetchcolumn('select code_name from '.tablename('currency').' where code_value=:code_value',[':code_value'=>$currency]);
    $currency = explode('(',explode(')',$currency)[0])[1];
    //获取补缺后清单总值
    $origin_arr = pdo_fetchall('select id from '.tablename('customs_goods_pre_log').' where pre_batch_num=:pre_batch_num and openid=:openid',[':pre_batch_num'=>$batch_num,':openid'=>$openid]);
    $totalMoney = 0;
    foreach($origin_arr as $k=>$v){
        $totalMoney += pdo_fetchcolumn('select totalPrice from '.tablename('customs_goods_pre_fill_log').' where good_id=:id and openid=:openid',[':id'=>$v['id'],':openid'=>$openid]);
    }

    //获取导入时的数据或补缺后的数据
    $method = ['01-原始清单商品数据'];
    $is_have=pdo_fetch('select id from '.tablename('customs_goods_pre_fill_log').' where pre_batch_num=:batch_num and is_fill=1 and openid=:openid',[':batch_num'=>$batch_num,':openid'=>$openid]);
    if($is_have['id']){
        $method = array_merge($method,['02-补缺清单商品数据']);
    }
    $method = array_reverse($method);

    if($_GPC['pa']==1){
        $list = pdo_fetchall('select distinct b.logisticsNo,b.pre_batch_num from '.tablename('customs_pre_declare').' a left join '.tablename('customs_goods_pre_log').' b on b.pre_batch_num=a.pre_batch_num where a.pre_batch_num=:pre_batch_num and a.openid=:openid',[':pre_batch_num'=>$batch_num,':openid'=>$openid]);
        $logisticsNo = '';
        foreach($list as $k=>$v){
            $logisticsNo .= $v['logisticsNo'].',';
        }
        foreach($list as $k=>$v){
            $list[$k]['logisticsNo2'] = $logisticsNo;
        }
        die(json_encode(['code'=>0,'data'=>$list]));
    }

    include $this->template('declare/risk/total_value_manage');
}elseif($op=='category_list'){
    //分类清单
    $batch_num = $_GPC['batch_num'];
    $logisticsNo = $_GPC['logisticsNo'];
    $is_adjust = pdo_fetch('select value_status from '.tablename('customs_pre_declare').' where pre_batch_num=:batch_num and openid=:openid',[':batch_num'=>$batch_num,':openid'=>$openid]);

    if($_GPC['pa']==1){
        //同品同价同重
        if($is_adjust['value_status']==1){
            $list = pdo_fetchall('select a.price,b.itemName,b.grossWeight,b.onebound_gid,b.currency from '.tablename('customs_goods_pre_adjust_log').' a left join '.tablename('customs_goods_pre_fill_log').' b on b.id=a.fill_id where b.type=1 and a.openid=:openid and a.logisticsNo=:logn and a.pre_batch_num=:batch_num',[':openid'=>$openid,':logn'=>$logisticsNo,':batch_num'=>$batch_num]);
        }else{
            $list = pdo_fetchall('select a.price,b.itemName,b.grossWeight,a.onebound_gid,a.currency from '.tablename('customs_goods_pre_fill_log').' a left join '.tablename('customs_goods_pre_log').' b on b.id=a.good_id where a.type=1 and a.openid=:openid and a.logisticsNo=:logn and b.pre_batch_num=:batch_num',[':openid'=>$openid,':logn'=>$logisticsNo,':batch_num'=>$batch_num]);
        }

        foreach($list as $k=>$v){
            if(!empty($v['onebound_gid'])){
                $list[$k]['title'] = pdo_fetchcolumn('select title from '.tablename('onebound_total_goods').' where id=:id',[':id'=>$v['onebound_gid']]);
            }else{
                $list[$k]['title'] = '-';
            }

            $list[$k]['currency'] = pdo_fetchcolumn('select code_name from '.tablename('currency').' where code_value=:code_value',[':code_value'=>$v['currency']]);
            $list[$k]['currency'] = explode('(',explode(')',$list[$k]['currency'])[0])[1];
            $list[$k]['price'] = $list[$k]['currency'].'/'.$list[$k]['price'];
        }

        die(json_encode(['code'=>0,'data'=>$list]));
    }

    if($_GPC['pa']==2){
        //同品异价异重
        if($is_adjust['value_status']==1){
            $list = pdo_fetchall('select a.price,b.itemName,b.grossWeight,b.onebound_gid,b.currency from ' . tablename('customs_goods_pre_adjust_log') . ' a left join ' . tablename('customs_goods_pre_fill_log') . ' b on b.id=a.fill_id where b.type=2 and a.openid=:openid and a.logisticsNo=:logn and a.pre_batch_num=:batch_num', [':openid' => $openid, ':logn' => $logisticsNo, ':batch_num' => $batch_num]);
        }else {
            $list = pdo_fetchall('select a.price,b.itemName,b.grossWeight,a.onebound_gid,b.currency from ' . tablename('customs_goods_pre_fill_log') . ' a left join ' . tablename('customs_goods_pre_log') . ' b on b.id=a.good_id where a.type=2 and a.openid=:openid and a.logisticsNo=:logn and b.pre_batch_num=:batch_num', [':openid' => $openid, ':logn' => $logisticsNo, ':batch_num' => $batch_num]);
        }
        foreach($list as $k=>$v){
            $list[$k]['title'] = pdo_fetchcolumn('select title from '.tablename('onebound_total_goods').' where id=:id',[':id'=>$v['onebound_gid']]);
            $list[$k]['currency'] = pdo_fetchcolumn('select code_name from '.tablename('currency').' where code_value=:code_value',[':code_value'=>$v['currency']]);
            $list[$k]['currency'] = explode('(',explode(')',$list[$k]['currency'])[0])[1];
            $list[$k]['price'] = $list[$k]['currency'].'/'.$list[$k]['price'];
        }
        die(json_encode(['code'=>0,'data'=>$list]));
    }

    if($_GPC['pa']==3){
        //异品异价异重
        if($is_adjust['value_status']==1){
            $list = pdo_fetchall('select a.price,b.itemName,b.grossWeight,b.onebound_gid,b.currency from '.tablename('customs_goods_pre_adjust_log').' a left join '.tablename('customs_goods_pre_fill_log').' b on b.id=a.fill_id where b.type=3 and a.openid=:openid and a.logisticsNo=:logn and a.pre_batch_num=:batch_num',[':openid'=>$openid,':logn'=>$logisticsNo,':batch_num'=>$batch_num]);
        }else{
            $list = pdo_fetchall('select a.price,b.itemName,b.grossWeight,a.onebound_gid,a.currency from '.tablename('customs_goods_pre_fill_log').' a left join '.tablename('customs_goods_pre_log').' b on b.id=a.good_id where a.type=3 and a.openid=:openid and a.logisticsNo=:logn and b.pre_batch_num=:batch_num',[':openid'=>$openid,':logn'=>$logisticsNo,':batch_num'=>$batch_num]);
        }
        foreach($list as $k=>$v){
            $list[$k]['title'] = pdo_fetchcolumn('select title from '.tablename('onebound_total_goods').' where id=:id',[':id'=>$v['onebound_gid']]);
            $list[$k]['currency'] = pdo_fetchcolumn('select code_name from '.tablename('currency').' where code_value=:code_value',[':code_value'=>$v['currency']]);
            $list[$k]['currency'] = explode('(',explode(')',$list[$k]['currency'])[0])[1];
            $list[$k]['price'] = $list[$k]['currency'].'/'.$list[$k]['price'];
        }
        die(json_encode(['code'=>0,'data'=>$list]));
    }

    include $this->template('declare/risk/category_list');
}elseif($op=='getTotalMoney'){
    //根据获取指定数据去查找总值
    $method = $_GPC['method'];
    $batch_num = $_GPC['batch_num'];
    if($method=='01-原始清单商品数据'){
        $totalMoney = pdo_fetchcolumn('select sum(totalPrice) as totalPrice from '.tablename('customs_goods_pre_log').' where pre_batch_num=:batch_num and openid=:openid',[':batch_num'=>$batch_num,':openid'=>$openid]);
    }elseif($method=='02-补缺清单商品数据'){
        $totalMoney = pdo_fetchcolumn('select sum(totalPrice) as totalPrice from '.tablename('customs_goods_pre_fill_log').' where pre_batch_num=:batch_num and openid=:openid',[':batch_num'=>$batch_num,':openid'=>$openid]);
    }
    show_json(1,['totalMoney'=>$totalMoney]);
}elseif($op=='adjust_range'){
    // $a =[
    //     ['notice'=>1,'price'=>1.029007,'id'=>129,'pre_batch_num'=>'YB2022041411592640'],
    //     ['notice'=>1,'price'=>10.290074,'id'=>130,'pre_batch_num'=>'YB2022041411592640'],
    //     ['notice'=>1,'price'=>2.058015,'id'=>131,'pre_batch_num'=>'YB2022041411592640'],
    // ];
    // if(m('common')->in_array_r2(0,$a,'notice')){
    //     echo '存在';
    // }else{
    //     echo '不存在';
    // }
    // die;
    //调整幅度
    $batch_num = $_GPC['batch_num'];
    //查询当前预申报编号是否已调整总值
    $is_adjust = pdo_fetch('select value_status from '.tablename('customs_pre_declare').' where openid=:openid and pre_batch_num=:batch_num',[':openid'=>$openid,':batch_num'=>$batch_num]);
    if($is_adjust['value_status']==1){
        show_json(-1,['msg'=>'当前清单总值已确认调整总值，请勿重复调整！']);
    }

    //删除上次调整信息
    pdo_delete('declare_value_adjust_tmp',['openid'=>$openid,'pre_batch_num'=>$batch_num]);

    //1、先查询该批次号下有无全部补缺(货值、规格)
    $orderList = pdo_fetchall('select pre_batch_num,id from '.tablename('customs_goods_pre_log').' where pre_batch_num=:p and openid=:openid',[':p'=>$batch_num,':openid'=>$openid]);
    $total_price = 0;//清单补缺后的总价
    foreach($orderList as $k=>$v){
        $is_fill = pdo_fetch('select price,totalPrice,gmodel from '.tablename('customs_goods_pre_fill_log').' where good_id=:gid',[':gid'=>$v['id']]);
        if(floatval($is_fill['price'])<=0 || empty($is_fill['gmodel'])){
            show_json(-1,['msg'=>'系统检测本批次下有运单编号商品信息还未有完全补缺，请检查！']);
        }else{
            $total_price += $is_fill['totalPrice'];
        }
    }

    //2、开始调整
    $userTotalMoney = $_GPC['data']['diy_price'];//用户自定义总值
    $amp = intval($_GPC['data']['amp']);//1按比率，2按金额

    //2.1、系统设定信息
//    [percent_up] => 30
//    [percent_down] => 30
//    [value_up] => 270
//    [value_down] => 270
//    [goodsValue_up] => 2
//    [goodsValue_down] => 2
//    [daily_sales] => 10
//    [month_sales] => 10
//    [sold] => 20

    //用户比例和金额
    $amp1 = floatval(trim($_GPC['data']['amp1']));//比率
    $amp2 = floatval(trim($_GPC['data']['amp2']));//金额

    if($amp==1){
        //按比率
        if($amp1<=0){
            show_json(-1,['msg'=>'请输入正确的调整幅度比率！']);
        }
        $method = 0;
        if($userTotalMoney>$total_price){
            //调整的总价大于清单总价，调升
            $method = 1;
        }
        else{
            //调整的总价小于清单总价，调降
            $method = 2;
        }

        $res = m('common')->grossAdjustRatio($openid,$total_price,$config,$method,$userTotalMoney,$orderList,$amp1);

        if($res[0]==-1){
            show_json(-1,['msg'=>$res[1]]);
        }
        show_json(1,['msg'=>$res[1]]);

    }
    elseif($amp==2){
        //按金额

        if($amp2<=0){
            show_json(-1,['msg'=>'请输入正确的调整幅度金额！']);
        }
        $method=0;
        if($userTotalMoney>$total_price) {//2500>2123.17,调升
            //调整的总价大于清单总价，调升
            $method=1;
        }else{
            //调整的总价小于清单总价，调降
            $method=2;
        }

        $res = m('common')->grossAdjustMoney($openid,$total_price,$config,$method,$userTotalMoney,$orderList,$amp2);
        if($res[0]==-1){
            show_json(-1,['msg'=>$res[1]]);
        }
        show_json(1,['msg'=>$res[1]]);
    }
}elseif($op=='adjust_sure'){
    //清单确认
    $batch_num = $_GPC['batch_num'];
    //已确认调整
    $is_adjust = pdo_fetch('select value_status from '.tablename('customs_pre_declare').' where pre_batch_num=:batch_num and openid=:openid',[':batch_num'=>$batch_num,':openid'=>$openid]);
    if($is_adjust['value_status']==1){
        show_json(1);
    }

    //1、判断是调升还是调降
    $adjust_typ = pdo_fetch('select typ from '.tablename('declare_value_adjust_tmp').' where pre_batch_num=:batch_num and openid=:openid limit 1',[':batch_num'=>$batch_num,':openid'=>$openid]);

    //1.1、获取该批次号的临时表信息,获取同组商品id下调整后最大的价格，然后去修改补缺表里的商品价格
    if($adjust_typ['typ']==1){
        //调升获取最大值
        $update_goods_arr = pdo_fetchall('select good_id,max(calc_price) as calc_price from '.tablename('declare_value_adjust_tmp').' where pre_batch_num=:batch_num and openid=:openid group by good_id having max(times)',[':batch_num'=>$batch_num,':openid'=>$openid]);
    }else{
        //调降获取最小值
//        group by good_id,times having times>=1
        $update_goods_arr = pdo_fetchall('select good_id,min(calc_price) as calc_price from '.tablename('declare_value_adjust_tmp').' where pre_batch_num=:batch_num and openid=:openid group by good_id having max(times)',[':batch_num'=>$batch_num,':openid'=>$openid]);
    }

    foreach($update_goods_arr as $k=>$v){
        $fill_info = pdo_fetch('select * from '.tablename('customs_goods_pre_fill_log').' where id=:gid and openid=:openid',[':gid'=>$v['good_id'],':openid'=>$openid]);
        $totalPrice = $v['calc_price']*$fill_info['qty'];
        pdo_insert('customs_goods_pre_adjust_log',[
            'pre_batch_num'=>$batch_num,
            'logisticsNo'=>$fill_info['logisticsNo'],
            'fill_id'=>$fill_info['id'],
            'openid'=>$openid,
            'price'=>$v['calc_price'],
            'totalPrice'=>$totalPrice,
            'charge'=>$totalPrice,
            'good_id'=>$fill_info['good_id']
        ]);
    }
    
    //2022-04-30,如果是按完成该商品即刻判断有无超过客户端总值的话就要插入其余没超过的商品
    // if(true){
    //     $fill_info = pdo_fetchall('select logisticsNo,id,price,totalPrice,charge,good_id from '.tablename('customs_goods_pre_fill_log').' where pre_batch_num=:batch_num and openid=:openid',[':batch_num'=>$batch_num,':openid'=>$openid]);
    //     foreach ($fill_info as $k=>$v){
    //         $ishave = pdo_fetch('select id from '.tablename('customs_goods_pre_adjust_log').' where pre_batch_num=:batch_num and openid=:openid and fill_id=:fid',[':fid'=>$v['id'],':batch_num'=>$batch_num,':openid'=>$openid]);
    //         if(empty($ishave['id'])){
    //             pdo_insert('customs_goods_pre_adjust_log',[
    //                 'pre_batch_num'=>$batch_num,
    //                 'logisticsNo'=>$v['logisticsNo'],
    //                 'fill_id'=>$v['id'],
    //                 'openid'=>$openid,
    //                 'price'=>$v['price'],
    //                 'totalPrice'=>$v['totalPrice'],
    //                 'charge'=>$v['charge'],
    //                 'good_id'=>$v['good_id']
    //             ]);
    //         }
    //     }
    // }
    
    
    pdo_update('customs_pre_declare',['value_status'=>1],['pre_batch_num'=>$batch_num,'openid'=>$openid]);
    //2、修改后就删除临时表信息
    $res = pdo_delete('declare_value_adjust_tmp',['pre_batch_num'=>$batch_num,'openid'=>$openid]);
    if($res){
        show_json(1);
    }
}elseif($op=='adjust_sured'){
    //查看是否已确认
    $batch_num = $_GPC['batch_num'];

    $value_status = pdo_fetchcolumn('select `value_status` from '.tablename('customs_pre_declare').' where pre_batch_num=:batch_num and openid=:openid',[':batch_num'=>$batch_num,':openid'=>$openid]);
    show_json(1,['data'=>$value_status]);
}elseif($op=='pre_declare'){
    //预先申报
    $batch_num = trim($_GPC['batch_num']);
    //查询是否已生成预提编号
    $is_adjust_pre = pdo_fetch('select id from '.tablename('customs_adjusted_declare').' where openid=:openid and pre_batch_num=:batch_num',[':openid'=>$openid,':batch_num'=>$batch_num]);
    if(empty($is_adjust_pre['id'])){
        //生成系统预提编号
        $millisecond = round(explode(" ", microtime())[0] * 1000);
        $sys_batch_num = 'GG198' . date('Ymd', time()) . str_pad($millisecond,3,'0',STR_PAD_RIGHT) . mt_rand(111,999) . mt_rand(111,999);
        pdo_insert('customs_adjusted_declare',[
            'openid'=>$openid,
            'sys_batch_num'=>$sys_batch_num,
            'pre_batch_num'=>$batch_num,
            'createtime'=>time()
        ]);
    }
    show_json(1);
}elseif($op=='export_list'){
    //总值管理-导出清单
    $logisticsNo = rtrim($_GPC['logisticsNo'],',');
    $batch_num = trim($_GPC['batch_num']);
    $list = pdo_fetchall('select b.itemNo,b.itemName,b.gcode,b.currency,b.qty,b.qty1,b.gmodel,a.price,a.totalPrice,a.charge,b.chargeDate,a.logisticsNo,b.freight,b.insuredFee,b.barCode,b.grossWeight,b.netWeight,b.packNo,b.goodsInfo,b.unit,b.unit1 from '.tablename('customs_goods_pre_adjust_log').' a left join '.tablename('customs_goods_pre_fill_log').' b on b.id=a.fill_id where a.openid=:openid and FIND_IN_SET(a.logisticsNo,"'.$logisticsNo.'") and a.pre_batch_num=:batch_num',[':openid'=>$openid,':batch_num'=>$batch_num]);

//    foreach($list as $k=>$v){
//        if(!empty($v['onebound_gid'])){
//            $list[$k]['itemName'] = pdo_fetchcolumn('select title from '.tablename('onebound_total_goods').' where id=:id',[':id'=>$v['onebound_gid']]);
//        }
//        else{
//            $list[$k]['itemName'] = pdo_fetchcolumn('select itemName from '.tablename('customs_goods_pre_log').' where id=:gid',[':gid'=>$v['good_id']]);
//        }
//    }

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
        "title" => $batch_num."清单",
        "columns" => $columns
    ));
}elseif($op=='inspect_manage'){
    //查验管理

    include $this->template('declare/risk/inspect_manage');
}
