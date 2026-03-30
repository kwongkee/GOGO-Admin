<?php
// 模块LTD提供
//if (!defined('IN_IA')) {
//    exit('Access Denied');
//}

global $_W;
global $_GPC;

$openid=$_W['openid'];
$op = !empty($_GPC['op'])?trim($_GPC['op']):'display';

if($op=='display'){
    include $this->template('declare/index');
}elseif($op=='add_declare'){

    if($_W['isajax']){

        $vouyage_arr = [$_GPC['license_place'],$_GPC['license_place2'],$_GPC['license_num1'],$_GPC['license_num2'],$_GPC['license_num3'],$_GPC['license_num4'],$_GPC['license_num5']];
        if(!empty($_GPC['license_num6'])){
            array_push($vouyage_arr,$_GPC['license_num6']);
        }
        $voyage_no = implode(',',$vouyage_arr);
        $batch_num = trim($_GPC['pre_batch_num']);
        $data = [
            'ebp_default'=>intval($_GPC['ebp_default']),
            'ebc_default'=>intval($_GPC['ebc_default']),
            'pay_default'=>intval($_GPC['pay_default']),
            'logi_default'=>intval($_GPC['logi_default']),
            'ebpCode'=>trim($_GPC['ebpCode']),
            'ebpName'=>trim($_GPC['ebpName']),
            'ebcCode'=>trim($_GPC['ebcCode']),
            'ebcName'=>trim($_GPC['ebcName']),
            'ebc_tele_phone'=>trim($_GPC['ebc_tele_phone']),
            'payCode'=>trim($_GPC['payCode']),
            'payName'=>trim($_GPC['payName']),
            'logisticsCode'=>trim($_GPC['logisticsCode']),
            'logisticsName'=>trim($_GPC['logisticsName']),
            'loctNo'=>trim($_GPC['loctNo']),
            'customs_code'=>trim($_GPC['customs_code']),
            'port_code'=>trim($_GPC['port_code']),
            'ie_date'=>trim($_GPC['ie_date']),
            'pod'=>trim($_GPC['pod']),
            'country_code'=>trim($_GPC['country_code']),
            'voyage_no'=>$voyage_no,
            'transport_code'=>intval($_GPC['transport_code']),
            'traf_name'=>trim($_GPC['traf_name']),
            'oceanLad'=>trim($_GPC['oceanLad']),
            'iac_name'=>trim($_GPC['iac_name']),
            'iac_code'=>trim($_GPC['iac_code']),
            'ems_no'=>trim($_GPC['ems_no']),
            'pre_batch_num'=>$batch_num,
            'transport_file'=>json_encode($_GPC['transport_file'],true),
            'list_file'=>json_encode($_GPC['list_file'],true),
            'openid'=>$openid,
            'createtime'=>time(),
//            'status'=>0
        ];

        $res = pdo_insert('customs_pre_declare',$data);
        if($res){
            //查找该预报编号下的商品单价是否不为空，若不为空，则插入onebound商品库
            $insert_goods_arr = pdo_fetchall('select * from '.tablename('customs_goods_pre_log').' where pre_batch_num=:batch_num and openid=:openid and price!=0',[':batch_num'=>$batch_num,':openid'=>$openid]);
            if(!empty($insert_goods_arr)){
                foreach($insert_goods_arr as $k=>$v){
                    $is_have = pdo_fetch('select id from '.tablename('onebound_total_goods').' where title=:title and price=:price',[':title'=>$v['itemName'],':price'=>$v['price']]);
                    if(empty($is_have['id'])){
                        pdo_insert('onebound_total_goods',[
                            'type'=>3,
                            'title'=>$v['itemName'],
                            'price'=>$v['price'],
                            'pre_gid'=>$v['id'],
                            'keywords'=>$v['itemName'],
                        ]);
                    }
                }
            }
            show_json(1,['msg'=>'新增预报成功！']);
        }
    }
    else{
        //监管场所代码
        $loctcode = pdo_fetchall('select * from '.tablename('loctcode').' where 1');
        $customs_codes = pdo_fetchall('select * from '.tablename('customs_codes').' where 1');
        $port_code = pdo_fetchall('select * from '.tablename('port_code').' where 1');
        $country_code = pdo_fetchall('select * from '.tablename('country_code').' where 1');
        $transport = pdo_fetchall('select * from '.tablename('transport').' where 1');
        foreach($transport as $k=>$v){
            $name = '';
            $code = '';
            if($v['code_value']==2){
                //原数据
                $name = $transport[0]['code_name'];
                $code = $transport[0]['code_value'];
                //替换
                $transport[0]['code_name'] = $v['code_name'];
                $transport[0]['code_value'] = $v['code_value'];
                $transport[$k]['code_name'] = $name;
                $transport[$k]['code_value'] = $code;
            }
            if($v['code_value']==4){
                //原数据
                $name = $transport[1]['code_name'];
                $code = $transport[1]['code_value'];
                //替换
                $transport[1]['code_name'] = $v['code_name'];
                $transport[1]['code_value'] = $v['code_value'];
                $transport[$k]['code_name'] = $name;
                $transport[$k]['code_value'] = $code;
            }
            if($v['code_value']==5){
                //原数据
                $name = $transport[2]['code_name'];
                $code = $transport[2]['code_value'];
                //替换
                $transport[2]['code_name'] = $v['code_name'];
                $transport[2]['code_value'] = $v['code_value'];
                $transport[$k]['code_name'] = $name;
                $transport[$k]['code_value'] = $code;
            }
            if($v['code_value']==6){
                //原数据
                $name = $transport[3]['code_name'];
                $code = $transport[3]['code_value'];
                //替换
                $transport[3]['code_name'] = $v['code_name'];
                $transport[3]['code_value'] = $v['code_value'];
                $transport[$k]['code_name'] = $name;
                $transport[$k]['code_value'] = $code;
            }

        }
    }

    include $this->template('declare/add_declare');
}elseif($op=='manage_declare'){
    //管理预报
    include $this->template('declare/manage_declare');
}elseif($op=='pre_declare_list'){
    //预报列表
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

    include $this->template('declare/pre_declare_list');
}elseif($op=='declare_list'){
    //申报列表
    include $this->template('declare/declare_list');
}elseif($op=='lading_edit'){
    //修改提单
    $id = intval($_GPC['id']);
    if($_W['isajax']){
        $data = [
            'ebp_default'=>intval($_GPC['ebp_default']),
            'ebc_default'=>intval($_GPC['ebc_default']),
            'pay_default'=>intval($_GPC['pay_default']),
            'logi_default'=>intval($_GPC['logi_default']),
            'ebpCode'=>trim($_GPC['ebpCode']),
            'ebpName'=>trim($_GPC['ebpName']),
            'ebcCode'=>trim($_GPC['ebcCode']),
            'ebc_tele_phone'=>trim($_GPC['ebc_tele_phone']),
            'payCode'=>trim($_GPC['payCode']),
            'payName'=>trim($_GPC['payName']),
            'logisticsCode'=>trim($_GPC['logisticsCode']),
            'logisticsName'=>trim($_GPC['logisticsName']),
            'loctNo'=>trim($_GPC['loctNo']),
            'customs_code'=>trim($_GPC['customs_code']),
            'port_code'=>trim($_GPC['port_code']),
            'ie_date'=>trim($_GPC['ie_date']),
            'pod'=>trim($_GPC['pod']),
            'country_code'=>trim($_GPC['country_code']),
            'voyage_no'=>trim($_GPC['voyage_no']),
            'transport_code'=>intval($_GPC['transport_code']),
            'traf_name'=>trim($_GPC['traf_name']),
            'oceanLad'=>trim($_GPC['oceanLad']),
            'transport_file'=>json_encode($_GPC['transport_file'],true),
        ];

        $res = pdo_update('customs_pre_declare',$data,['id'=>$id,'openid'=>$openid]);
        if($res){
            show_json(1,['msg'=>'修改预报提单成功！']);
        }
    }else{
        $data = pdo_fetch('select * from '.tablename('customs_pre_declare').' where openid=:openid and id=:id',[':openid'=>$openid,':id'=>$id]);
        $data['transport_file'] = json_decode($data['transport_file'],true);
        $data['voyage_no'] = explode(',',$data['voyage_no']);
        //监管场所代码
        $loctcode = pdo_fetchall('select * from '.tablename('loctcode').' where 1');
        $customs_codes = pdo_fetchall('select * from '.tablename('customs_codes').' where 1');
        $port_code = pdo_fetchall('select * from '.tablename('port_code').' where 1');
        $country_code = pdo_fetchall('select * from '.tablename('country_code').' where 1');
        $transport = pdo_fetchall('select * from '.tablename('transport').' where 1');
        foreach($transport as $k=>$v){
            $name = '';
            $code = '';
            if($v['code_value']==2){
                //原数据
                $name = $transport[0]['code_name'];
                $code = $transport[0]['code_value'];
                //替换
                $transport[0]['code_name'] = $v['code_name'];
                $transport[0]['code_value'] = $v['code_value'];
                $transport[$k]['code_name'] = $name;
                $transport[$k]['code_value'] = $code;
            }
            if($v['code_value']==4){
                //原数据
                $name = $transport[1]['code_name'];
                $code = $transport[1]['code_value'];
                //替换
                $transport[1]['code_name'] = $v['code_name'];
                $transport[1]['code_value'] = $v['code_value'];
                $transport[$k]['code_name'] = $name;
                $transport[$k]['code_value'] = $code;
            }
            if($v['code_value']==5){
                //原数据
                $name = $transport[2]['code_name'];
                $code = $transport[2]['code_value'];
                //替换
                $transport[2]['code_name'] = $v['code_name'];
                $transport[2]['code_value'] = $v['code_value'];
                $transport[$k]['code_name'] = $name;
                $transport[$k]['code_value'] = $code;
            }
            if($v['code_value']==6){
                //原数据
                $name = $transport[3]['code_name'];
                $code = $transport[3]['code_value'];
                //替换
                $transport[3]['code_name'] = $v['code_name'];
                $transport[3]['code_value'] = $v['code_value'];
                $transport[$k]['code_name'] = $name;
                $transport[$k]['code_value'] = $code;
            }

        }
    }
    include $this->template('declare/lading_edit');
}elseif($op=='list_add'){
    //新增清单
    $id = intval($_GPC['id']);

    if($_W['isajax']){
        $data = pdo_fetch('select pre_batch_num,list_file from '.tablename('customs_pre_declare').' where id=:id and openid=:openid',[':id'=>$id,':openid'=>$openid]);
        $data['list_file'] = json_decode($data['list_file'],true);
        if(!empty($data['list_file'])){
            foreach($_GPC['list_file'] as $k=>$v){
                array_push($data['list_file'],$v);
            }
        }
        $res = pdo_update('customs_pre_declare',['list_file'=>json_encode($data['list_file'],true)],['id'=>$id]);
        if($res){
            //查找该预报编号下的商品单价是否不为空，若不为空，则插入onebound商品库
            $insert_goods_arr = pdo_fetchall('select * from '.tablename('customs_goods_pre_log').' where pre_batch_num=:batch_num and openid=:openid and price!=0',[':batch_num'=>$data['pre_batch_num'],':openid'=>$openid]);
            if(!empty($insert_goods_arr)){
                foreach($insert_goods_arr as $k=>$v){
                    $is_have = pdo_fetch('select id from '.tablename('onebound_total_goods').' where title=:title and price=:price',[':title'=>$v['itemName'],':price'=>$v['price']]);
                    if(empty($is_have['id'])){
                        pdo_insert('onebound_total_goods',[
                            'type'=>3,
                            'title'=>$v['itemName'],
                            'price'=>$v['price'],
                            'pre_gid'=>$v['id'],
                            'keywords'=>$v['itemName'],
                        ]);
                    }
                }
            }
            show_json(1,['msg'=>'新增清单成功']);
        }
    }

    include $this->template('declare/list_add');
}elseif($op=='list_edit'){
    //修改清单
    $id = intval($_GPC['id']);

    if($_GPC['pa']==1){
        $limit = intval($_GPC['limit']);
        $page = intval($_GPC['page']) - 1;
        if ($page != 0) {
            $page = $limit * $page;
        }
        $keyword = trim($_GPC['keywords']) ? trim($_GPC['keywords']) : '';
        $condition = '';
        if($keyword){
            $condition = 'and a.itemName like "%'.$keyword.'%"';
        }

        $list = pdo_fetchall('select a.* from '.tablename('customs_goods_pre_log').' a left join '.tablename('customs_pre_declare').' b on b.pre_batch_num=a.pre_batch_num where b.openid=:openid and b.id=:id '.$condition.' limit '.$page.",".$limit,[':openid'=>$openid,':id'=>$id]);

        $count = pdo_fetch('select count(a.id) as c from '.tablename('customs_goods_pre_log').' a left join '.tablename('customs_pre_declare').' b on b.pre_batch_num=a.pre_batch_num where b.openid=:openid and b.id=:id '.$condition,[':openid'=>$openid,':id'=>$id]);
        die(json_encode(['code'=>0,'count'=>$count['c'],'data'=>$list]));
    }

    include $this->template('declare/list_edit');
}elseif($op=='goods_edit'){
    $id = intval($_GPC['id']);

    if($_W['isajax']){
        $data = [
            'itemNo'=>trim($_GPC['itemNo']),
            'itemName'=>trim($_GPC['itemName']),
            'gcode'=>trim($_GPC['gcode']),
            'currency'=>trim($_GPC['currency']),
            'qty'=>trim($_GPC['qty']),
            'qty1'=>trim($_GPC['qty1']),
            'gmodel'=>trim($_GPC['gmodel']),
            'price'=>trim($_GPC['price']),
            'totalPrice'=>trim($_GPC['totalPrice']),
            'charge'=>trim($_GPC['charge']),
            'chargeDate'=>trim($_GPC['chargeDate']),
            'logisticsNo'=>trim($_GPC['logisticsNo']),
            'freight'=>trim($_GPC['freight']),
            'insuredFee'=>trim($_GPC['insuredFee']),
            'barCode'=>trim($_GPC['barCode']),
            'grossWeight'=>trim($_GPC['grossWeight']),
            'netWeight'=>trim($_GPC['netWeight']),
            'packNo'=>trim($_GPC['packNo']),
            'goodsInfo'=>trim($_GPC['goodsInfo']),
            'unit'=>trim($_GPC['unit']),
            'unit1'=>trim($_GPC['unit1']),
        ];
        if(!empty($data['gcode'])){
            $hscode = pdo_fetch('select id from '.tablename('customs_hscode_tariffschedule_ssl').' where hscode=:hscode',[':hscode'=>$data['gcode']]);
            if(empty($hscode['id'])){
                show_json(-1,['msg'=>'请输入正确的商品编码！']);
            }
        }
        $res = pdo_update('customs_goods_pre_log',$data,['id'=>$id,'openid'=>$openid]);
        if($res){
            $is_have = pdo_fetch('select id from '.tablename('customs_goods_pre_fill_log').' where good_id=:gid and openid=:openid and is_fill=0',[':gid'=>$id,':openid'=>$openid]);
            if($is_have['id']){
                //修改补缺表的原始清单数据
                pdo_update('customs_goods_pre_fill_log',$data,['good_id'=>$id,'openid'=>$openid,'is_fill'=>0]);
            }
            show_json(1,['msg'=>'修改成功']);
        }
    }else{
        $data = pdo_fetch('select * from '.tablename('customs_goods_pre_log').' where id=:id and openid=:openid',[':id'=>$id,':openid'=>$openid]);
    }

    include $this->template('declare/goods_edit');
}