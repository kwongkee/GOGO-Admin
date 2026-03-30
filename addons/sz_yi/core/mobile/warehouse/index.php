<?php
/**
 * User: 仓储系统-仓储管理
 * Date: 2022/7/25
 * Time: 14:11
 */
// 模块LTD提供
if (!defined('IN_IA')) {
    exit('Access Denied');
}
check_login();

global $_W;
global $_GPC;
$op = !empty($_GPC['op'])?trim($_GPC['op']):'display';
$openid = $_W['openid'];
$notice_manage = 'ov3-bt8keSKg_8z9Wwi-zG1hRhwg';//ov3-bt8keSKg_8z9Wwi-zG1hRhwg  ov3-bt5vIxepEjWc51zRQNQbFSaQ
$time = TIMESTAMP;
$warehouse_id = $_SESSION['warehouse_manager']['warehouse_id'];//仓库id
$data = $_GPC;

if($op=='display'){
    $manager = $_SESSION['warehouse_manager'];
    $menu_index=1;

    //获取包裹订单各数量
    //1、包裹验货
    $data['inspection_num'] = pdo_fetchcolumn('select count(a.id) from '.tablename('centralize_parcel_order').' a left join '.tablename('centralize_transport_detail').' c2 on c2.orderid=a.id where a.status=2 and c2.program_type=3 and a.warehouse_id=:warehouse_id ',[':warehouse_id'=>$warehouse_id]);
    //分拆、合并时提交的开包验货申请
    $data['inspection_num2'] = pdo_fetchcolumn('select count(a.id) from '.tablename('centralize_parcel_order').' a left join '.tablename('centralize_transport_detail').' c2 on c2.orderid=a.id where a.kaibao_apply=1 and c2.program_type=3 and c2.status=92 and a.warehouse_id=:warehouse_id',[':warehouse_id'=>$warehouse_id]);
    $data['inspection_num'] += $data['inspection_num2'];

    //2、包裹入库
    $data['in_warehouse_num'] = pdo_fetchcolumn('select count(a.id) from '.tablename('centralize_parcel_order').' a left join '.tablename('centralize_transport_detail').' c2 on c2.orderid=a.id where a.status=3 and c2.program_type=5 and a.warehouse_id=:warehouse_id',[':warehouse_id'=>$warehouse_id]);

    //3、包裹退运
    $data['return_num'] = pdo_fetchcolumn('select count(id) from '.tablename('centralize_parcel_order').' where status=4 and pay_status="" and warehouse_id=:warehouse_id',[':warehouse_id'=>$warehouse_id]);

    //4、包裹弃运
    $data['abandon_num'] = pdo_fetchcolumn('select count(id) from '.tablename('centralize_parcel_order').' where status=5 and pay_status="" and warehouse_id=:warehouse_id',[':warehouse_id'=>$warehouse_id]);

    //5、包裹分拆
    $data['spinoff_num'] = pdo_fetchcolumn('select count(a.id) from '.tablename('centralize_parcel_order').' a left join '.tablename('centralize_parcel_order_spin').' e on e.id=a.spin_id where a.status=3 and a.warehouse_id=:warehouse_id and e.status!=3 ',[':warehouse_id'=>$warehouse_id]);

    //6、包裹附加
    $data['attach_num'] = pdo_fetchcolumn('select count(id) from '.tablename('centralize_parcel_order').' where status=3 and warehouse_id=:warehouse_id and ( status2=86 or status2=87 )',[':warehouse_id'=>$warehouse_id]);

    //7、包裹剔除
    $data['reject_num'] = pdo_fetchcolumn('select count(id) from '.tablename('centralize_parcel_order').' where status=3 and warehouse_id=:warehouse_id and ( status2=88 or status2=89 )',[':warehouse_id'=>$warehouse_id]);

    //8、包裹合并
    $data['merge_num'] = pdo_fetchcolumn('select count(id) from '.tablename('centralize_parcel_merge_order').' where status2=85 and ( status=0 or status=1 ) and warehouse_id=:wid',[':wid'=>$warehouse_id]);

    //9、包裹出库
    $data['exWarehouse_num'] = pdo_fetchcolumn('select count(id) from '.tablename('centralize_parcel_merge_order').' where status2=85 and status>=2 and warehouse_id=:wid',[':wid'=>$warehouse_id]);

    include $this->template('warehouse/index/index');
}
elseif($op=='scan_warehousing'){
    //确认运抵
    $title = '确认运抵';

    //快递公司
    $express_company = pdo_fetchall('select * from '.tablename('customs_express_company_code').' where 1');
    //包裹查验状态
    $inspection_status = pdo_fetchall('select * from '.tablename('centralize_inspection_status').' where 1');
    //获取验货状态
    $consolidation_status = pdo_fetchall('select * from '.tablename('centralize_consolidation_status').' where pid=2 and status=1');
    include $this->template('warehouse/index/scan_warehousing');
}
elseif($op=='get_order_info'){
    //根据快递单号查询有无此数据
    if($data['type']==1){
        //1、先判断包裹有无已验货
        $is_done = pdo_fetch('select id from '.tablename('centralize_parcel_order').' where express_no=:express_no and status>=1 and warehouse_id=:warehouse_id',[':express_no'=>$data['express_no'],':warehouse_id'=>$warehouse_id]);
        if(!empty($is_done['id'])){
            show_json(-1,['msg'=>'该包裹已扫描，请勿重复操作！']);
        }else{
            $is_done2 = pdo_fetch('select id from '.tablename('centralize_no_main_part_list').' where express_no=:express_no and warehouse_id=:warehouse_id',[':express_no'=>$data['express_no'],':warehouse_id'=>$warehouse_id]);
            if(!empty($is_done2['id'])){
                show_json(-1,['msg'=>'该包裹已扫描，请勿重复操作！']);
            }
        }
    }

    //2、查询包裹预报数据/没有预报的话就填写信息后待认领
    $info = pdo_fetch('select * from '.tablename('centralize_parcel_order').' where express_no=:express_no and warehouse_id=:warehouse_id',[':express_no'=>$data['express_no'],':warehouse_id'=>$warehouse_id]);
    if(!empty($info)){
        //包裹货品
        $info_goods = pdo_fetchall('select * from '.tablename('centralize_parcel_order_goods').' where orderid=:orderid',[':orderid'=>$info['id']]);
        //发往仓库
        $info['warehouse_id'] = pdo_fetchcolumn('select warehouse_name from '.tablename('centralize_warehouse_list').' where id=:id',[':id'=>$info['warehouse_id']]);
        //快递公司
        $info['express_id'] = pdo_fetchcolumn('select name from '.tablename('customs_express_company_code').' where id=:id',[':id'=>$info['express_id']]);
        foreach($info_goods as $k=>$v){
            //商品分类
            $cate_item = pdo_fetchall('select catName from '.tablename('jd_goods_category').' where find_in_set(id,:cate_item)',[':cate_item'=>$v['cateid']]);
            $info_goods[$k]['cate_item'] = '';
            foreach($cate_item as $kk=>$vv){
                $info_goods[$k]['cate_item'] .= $vv['catName'].',';
            }
            $info_goods[$k]['cate_item'] = rtrim($info_goods[$k]['cate_item'],',');

            //商品属性
            $good_item = pdo_fetchall('select title from '.tablename('centralize_goods_value').' where find_in_set(id,:good_item)',[':good_item'=>$v['itemid']]);
            $info_goods[$k]['good_item'] = '';
            foreach($good_item as $kk=>$vv){
                $info_goods[$k]['good_item'] .= $vv['title'].',';
            }
            $info_goods[$k]['good_item'] = rtrim($info_goods[$k]['good_item'],',');

            //商品单位
            $info_goods[$k]['unit'] = pdo_fetchcolumn('select code_name from '.tablename('unit').' where code_value=:code_value',[':code_value'=>$v['unit']]);
        }

        //用户信息
        $info['user_info'] = pdo_fetch('select `name`,mobile,email,id from '.tablename('centralize_user').' where id=:id',[':id'=>$info['user_id']]);

        //获取运抵状态
        $status = pdo_fetchall('select * from '.tablename('centralize_consolidation_status').' where pid=2 and status=1');
        show_json(1,['msg'=>'查询成功','data'=>$info,'goods_data'=>$info_goods,'consolidation_status'=>$status]);
    }else{
        show_json(2,['msg'=>'查询失败，暂无预报数据']);
    }
}
elseif($op=='sure_receive'){
    //确认仓库收货
    
    $id = intval($data['id']);

    if(empty($id)){
        //无预报数据，插入待认领列表
        $no_main = pdo_fetch('select id from '.tablename('centralize_no_main_part_list').' where express_no=:express_no',[':express_no'=>trim($data['express_no'])]);
        if(!empty($no_main['id'])){
            show_json(-1,['msg'=>'该包裹已扫描，请勿重复操作！']);
        }else{
            //获取验货状态
//            $consolidation_status = pdo_fetchall('select * from '.tablename('centralize_consolidation_status').' where pid=2 and status=1');
//            $service_series = [];
//            foreach($consolidation_status as $k=>$v){
//                if(empty($data['service_'.$v['code']])){
//                    show_json(-1,['msg'=>'提交的数据不能为空！']);
//                }
//                $service_series = array_merge($service_series,[
//                    'service_'.$v['code']=>sprintf('%.2f',trim($data['service_'.$v['code']])),
//                ]);
//            }
            if(empty($data['express_no']) || empty($data['express_id'])){
                show_json(-1,['msg'=>'提交的数据不能为空！']);
            }
            $res = pdo_insert('centralize_no_main_part_list',[
                'opera_id'=>$_SESSION['warehouse_manager']['id'],
                'express_no'=>trim($data['express_no']),
                'express_id'=>intval($data['express_id']),
                'warehouse_id'=>$warehouse_id,
//                'service_series'=>json_encode($service_series,true),
                'status'=>0,
                'createtime'=>$time,
            ]);
            if($res){
                //插入物流信息
                insert_transport_detail([
                    'express_no'=>$data['express_no'],
                    'type'=>1,
                    'program_type'=>-1,
                    'user_id'=>0,
                    'opera_id'=>$_SESSION['warehouse_manager']['id'],
                    'status'=>intval($data['status']),
                    'remark'=>trim($data['remark']),
                    'createtime'=>$time,
                ]);
            }
        }
    }
    else{
        //有预报数据
        $user = pdo_fetch('select a.user_id,b.openid,a.express_no,c.warehouse_name,a.status from '.tablename('centralize_parcel_order').' a left join '.tablename('centralize_user').' b on a.user_id=b.id left join '.tablename('centralize_warehouse_list').' c on c.id=a.warehouse_id where a.id=:id',[':id'=>$id]);
        if($user['status'] !=0 ){
            show_json(-1,['msg'=>'该包裹已扫描，请勿重复操作！']);
        }

        //修改包裹状态、信息、验货收费
//        $consolidation_status = pdo_fetchall('select * from '.tablename('centralize_consolidation_status').' where pid=2 and status=1');
//        $service_series = [];
//        foreach($consolidation_status as $k=>$v){
//            if(empty($data['service_'.$v['code']])){
//                show_json(-1,['msg'=>'提交的数据不能为空！']);
//            }
//            $service_series = array_merge($service_series,[
//                'service_'.$v['code']=>sprintf('%.2f',trim($data['service_'.$v['code']])),
//            ]);
//        }

        $res = pdo_update('centralize_parcel_order',[
            'status'=>1,
//            'service_series'=>json_encode($service_series,true)
        ],['id'=>$id]);
        if($res){
            //微信通知用户【仓库确认收货】，先判断有无openid
            if(!empty($user['openid'])){
                $post2 = json_encode([
                    'call'=>'confirmCollectionNotice',
                    'first' =>'您好，您的快递['.$user['express_no'].']已到达['.$user['warehouse_name'].']仓库暂存区，请登录查看！',
                    'keyword1' => '快递['.$user['express_no'].']',
                    'keyword2' => '已到达仓库暂存区',
                    'keyword3' => date('Y-m-d H:i:s',$time),
                    'remark' => '',
                    'url' => 'https://decl.gogo198.cn/centralize/parcel/index',
                    'openid' => $user['openid'],
                    'temp_id' => 'SVVs5OeD3FfsGwW0PEfYlZWetjScIT8kDxht5tlI1V8'
                ]);
                ihttp_request('https://shop.gogo198.cn/api/sendwechattemplatenotice.php', $post2);
            }else{
                //发短信...（待做）
            }

            //插入物流信息
            insert_transport_detail([
                'express_no'=>$user['express_no'],
                'orderid'=>$id,
                'type'=>1,
                'program_type'=>2,//仓库确认收货
                'user_id'=>$user['user_id'],
                'opera_id'=>$_SESSION['warehouse_manager']['id'],
                'status'=>intval($data['status']),
                'remark'=>trim($data['remark']),
                'createtime'=>$time,
            ]);
        }
    }
    show_json(0,['msg'=>'操作成功']);
}
elseif($op=='to_be_inspection'){
    $title = '包裹验货';
    if($data['ispost']==1){
        $list = pdo_fetch('select a.express_no,a.kaibao_apply,a.ordersn,a.id as orderid,a.status2,b.id,b.name,b.mobile,c2.createtime,d.name as code_name from '.tablename('centralize_parcel_order').' a left join '.tablename('centralize_user').' b on a.user_id=b.id left join '.tablename('centralize_transport_detail').' c2 on c2.orderid=a.id left join '.tablename('centralize_consolidation_status').' d on d.code=a.status2 where a.status=2 and c2.program_type=3 and a.express_no=:express_no and a.warehouse_id=:warehouse_id',[':express_no'=>trim($data['express_no']),':warehouse_id'=>$warehouse_id]);
        if(empty($list)){
            //分拆、合并时提交的开包验货申请
            $list = pdo_fetch('select a.express_no,a.kaibao_apply,a.ordersn,a.id as orderid,a.status2,b.id,b.name,b.mobile,c2.createtime,d.name as code_name from '.tablename('centralize_parcel_order').' a left join '.tablename('centralize_user').' b on a.user_id=b.id left join '.tablename('centralize_transport_detail').' c2 on c2.orderid=a.id left join '.tablename('centralize_consolidation_status').' d on d.code=a.status2 where a.kaibao_apply=1 and c2.program_type=3 and c2.status=92 and a.express_no=:express_no and a.warehouse_id=:warehouse_id',[':express_no'=>trim($data['express_no']),':warehouse_id'=>$warehouse_id]);
            $list['status2']=92;
        }
        $list['program_type'] = pdo_fetchcolumn('select program_type from '.tablename('centralize_transport_detail').' where orderid=:oid order by id desc',[':oid'=>$list['orderid']]);
        show_json(0,['data'=>$list]);
    }else{
        //查询全部待验货的包裹
        $list = pdo_fetchall('select a.express_no,a.kaibao_apply,a.ordersn,a.id as orderid,a.status2,b.id,b.name,b.mobile,c2.createtime,d.name as code_name from '.tablename('centralize_parcel_order').' a left join '.tablename('centralize_user').' b on a.user_id=b.id left join '.tablename('centralize_transport_detail').' c2 on c2.orderid=a.id left join '.tablename('centralize_consolidation_status').' d on d.code=a.status2 where a.status=2 and c2.program_type=3 and a.warehouse_id=:warehouse_id order by c2.createtime desc',[':warehouse_id'=>$warehouse_id]);

        //分拆、合并时提交的开包验货申请
        $list2 = pdo_fetchall('select a.express_no,a.kaibao_apply,a.ordersn,a.id as orderid,a.status2,b.id,b.name,b.mobile,c2.createtime,d.name as code_name from '.tablename('centralize_parcel_order').' a left join '.tablename('centralize_user').' b on a.user_id=b.id left join '.tablename('centralize_transport_detail').' c2 on c2.orderid=a.id left join '.tablename('centralize_consolidation_status').' d on d.code=a.status2 where a.kaibao_apply=1 and c2.program_type=3 and c2.status=92 and a.warehouse_id=:warehouse_id order by c2.createtime desc ',[':warehouse_id'=>$warehouse_id]);
        if(!empty($list2)){
            foreach($list2 as $k=>$v){
                $list2[$k]['status2'] = 92;
            }
            $list = array_merge($list,$list2);
        }

        foreach($list as $k=>$v){
            $list[$k]['program_type'] = pdo_fetchcolumn('select program_type from '.tablename('centralize_transport_detail').' where orderid=:oid order by id desc',[':oid'=>$v['orderid']]);
        }
    }
    include $this->template('warehouse/index/to_be_inspection');
}
elseif($op=='show_inspection_box'){
    $order = pdo_fetch('select * from '.tablename('centralize_parcel_order').' where id=:id and warehouse_id=:warehouse_id',[':id'=>$data['orderid'],':warehouse_id'=>$warehouse_id]);

    if($data['status2']==92){
        $goods = pdo_fetchall('select * from '.tablename('centralize_parcel_order_goods').' where orderid=:oid',[':oid'=>$data['orderid']]);
        foreach($goods as $k=>$v){
            $cate_item = pdo_fetchall('select * from '.tablename('jd_goods_category').' where find_in_set(id,:id)',[':id'=>$v['cateid']]);
            $goods[$k]['cate_item'] = '';
            foreach($cate_item as $kk=>$vv){
                $goods[$k]['cate_item'] .= $vv['catName'].',';
            }
            $goods[$k]['cate_item'] = rtrim($goods[$k]['cate_item'],',');

            //属性
            $good_item = pdo_fetchall('select * from '.tablename('centralize_goods_value').' where find_in_set(id,:id)',[':id'=>$v['itemid']]);
            $goods[$k]['good_item'] = '';
            foreach($good_item as $kk=>$vv){
                $goods[$k]['good_item'] .= $vv['title'].',';
            }
            $goods[$k]['good_item'] = rtrim($goods[$k]['good_item'],',');
        }
        //包裹屬性
        $good_item = pdo_fetchall('select * from '.tablename('centralize_goods_value').' where 1');
        $good_item = json_encode($good_item,true);

        //包裹類型/产品分类,一级分类
        $cate_item = pdo_fetchall('select * from '.tablename('jd_goods_category').' where status=1 and catLevel=1');
        $cate_item = json_encode($cate_item,true);

        //单位
        $unit = pdo_fetchall('select * from '.tablename('unit').' where 1');
    }

    //集运状态(正常流程)
//    $consolidation_status = pdo_fetchall('select * from '.tablename('centralize_consolidation_status').' where ( code=902 or code=903 or code=904 ) and status=1');
//    foreach($consolidation_status as $k=>$v){
//        $consolidation_status[$k]['child'] = pdo_fetchall('select * from '.tablename('centralize_consolidation_status').' where pid=:pid and status=1',[':pid'=>$v['id']]);
//    }
    //集运状态(损毁)
    $damage_status = pdo_fetchall('select * from '.tablename('centralize_consolidation_status').' where pid=21 and status=1');
    include $this->template('warehouse/index/show_inspection_box');
}
elseif($op=='sure_inspection'){
    //确认验货
    
    $id = intval($data['orderid']);
    $pic_file = json_encode($data['pic_file'],true);

    $user = pdo_fetch('select a.user_id,b.openid,a.express_no,a.kaibao_apply,c.warehouse_name,a.status,a.status2,a.ordersn,a.service_series,a.is_kaibao from '.tablename('centralize_parcel_order').' a left join '.tablename('centralize_user').' b on a.user_id=b.id left join '.tablename('centralize_warehouse_list').' c on c.id=a.warehouse_id where a.id=:id and a.warehouse_id=:warehouse_id',[':id'=>$id,':warehouse_id'=>$warehouse_id]);
    $user['program_type'] = pdo_fetchcolumn('select ifnull(program_type,0) from '.tablename('centralize_transport_detail').' where orderid=:oid and program_type=4',[':oid'=>$id]);
    if($user['program_type']==4 && $user['is_kaibao']==1){
        show_json(-1,['msg'=>'该包裹已验货，请勿重复操作！']);
    }
//    $origin_service = json_decode($user['service_series'],true);
//    $consolidation_status = pdo_fetchall('select * from '.tablename('centralize_consolidation_status').' where ( pid=3 or pid=4 or pid=5 ) and status=1');
//    foreach($consolidation_status as $k=>$v){
//        $origin_service = array_merge($origin_service,[
//            'service_'.$v['code']=>sprintf('%.2f',trim($data['service_'.$v['code']])),
//        ]);
//    }
    //将验货费用插入记录表
    //插入服务费用表
    pdo_insert('centralize_order_fee_log',[
        'type'=>1,//1国内订单，2国外订单
        'user_id'=>$user['user_id'],
        'orderid'=>$id,
        'service_status'=>$data['status2'],
        'service_price'=>trim($data['service_price']),
        'paytype'=>'',//支付方式，1余额，2微信hk，3转数快
        'status'=>0,//0未支付，1已支付
        'createtime'=>$time,
        'paytime'=>'',
    ]);
    if($data['damage_status']==0){
        $ordersn=$user['ordersn'];
    }else{
        $ordersn=substr_replace($user['ordersn'],$data['damage_status'],-2,2);
    }
    if($data['status2']==91){
        //外包查验时修改包裹状态、信息
        $res = pdo_update('centralize_parcel_order',[
            'ordersn'=>$ordersn,
            'true_volumn'=>trim($data['length'].'*'.$data['width'].'*'.$data['height']),
            'true_grosswt'=>trim($data['true_grosswt']),
            'pic_file'=>$pic_file,
//            'service_series'=>json_encode($origin_service,true),
//            'status'=>2,
            'status2'=>$data['damage_status']==0?$user['status2']:$data['damage_status'],
            'is_kaibao'=>0
        ],['id'=>$id]);
    }elseif($data['status2']==92){
        if($user['kaibao_apply']==1){
            $res = pdo_update('centralize_parcel_order',[
                'ordersn'=>$ordersn,
                'pic_file'=>$pic_file,
                'kaibao_apply'=>2,
                'status2'=>$data['damage_status']==0?$user['status2']:$data['damage_status'],
                'is_kaibao'=>1
            ],['id'=>$id]);
        }else{
            $res = pdo_update('centralize_parcel_order',[
                'ordersn'=>$ordersn,
                'pic_file'=>$pic_file,
//                'service_series'=>json_encode($origin_service,true),
//                'status'=>2,
                'status2'=>$data['damage_status']==0?$user['status2']:$data['damage_status'],
                'is_kaibao'=>1
            ],['id'=>$id]);
        }

        //开包查验时修改货物信息
        foreach($data['gid'] as $k=>$v){
            pdo_update('centralize_parcel_order_goods',[
                'name'=>$data['name'][$k],
                'brand'=>$data['brand'][$k],
                'num'=>$data['num'][$k],
                'unit'=>$data['unit'][$k],
                'netwt'=>$data['netwt'][$k],
                'grosswt'=>$data['grosswt'][$k],
                'true_volumn'=>$data['length'][$k].'*'.$data['width'][$k].'*'.$data['height'][$k],
            ],['id'=>$v]);
        }
    }

    //精确到xx方式查验
    $status = pdo_fetchcolumn('select `name` from '.tablename('centralize_consolidation_status').' where code=:code',[':code'=>$data['status2']]);
    if($res){
        //微信通知用户，先判断有无openid
        if(!empty($user['openid'])){
            $post2 = json_encode([
                'call'=>'confirmCollectionNotice',
                'first' =>'您好，您的快递包裹['.$user['express_no'].']已验货，请查看！',
                'keyword1' => '快递包裹['.$user['express_no'].']',
                'keyword2' => '已'.$status,
                'keyword3' => date('Y-m-d H:i:s',$time),
                'remark' => trim($data['remark']),
                'url' => 'https://decl.gogo198.cn/centralize/parcel/index',
                'openid' => $user['openid'],
                'temp_id' => 'SVVs5OeD3FfsGwW0PEfYlZWetjScIT8kDxht5tlI1V8'
            ]);
            ihttp_request('https://shop.gogo198.cn/api/sendwechattemplatenotice.php', $post2);
        }else{
            //发短信...（待做）
        }

        //插入物流信息
        insert_transport_detail([
            'express_no'=>$user['express_no'],
            'orderid'=>$id,
            'type'=>1,
            'program_type'=>4,
            'user_id'=>$user['user_id'],
            'opera_id'=>$_SESSION['warehouse_manager']['id'],
            'status'=>intval($data['status2']),
            'remark'=>trim($data['remark']),
            'createtime'=>$time,
        ]);
    }

    show_json(0,['msg'=>'操作成功']);
}
elseif($op=='to_be_warehousing'){
    //包裹入库列表
    $title = '包裹入库';
    if($data['ispost']==1){
        $list = pdo_fetch('select a.express_no,a.ordersn,a.id as orderid,a.status2,b.id,b.name,b.mobile,c2.createtime,d.name as code_name from '.tablename('centralize_parcel_order').' a left join '.tablename('centralize_user').' b on a.user_id=b.id left join '.tablename('centralize_transport_detail').' c2 on c2.express_no=a.express_no left join '.tablename('centralize_consolidation_status').' d on d.code=a.status2 where a.status=3 and c2.program_type=5 and a.express_no=:express_no and a.warehouse_id=:warehouse_id',[':express_no'=>trim($data['express_no']),':warehouse_id'=>$warehouse_id]);
        show_json(0,['data'=>$list]);
    }else{
        //查询全部待入库的包裹
        $list = pdo_fetchall('select a.express_no,a.ordersn,a.id as orderid,a.status2,b.id,b.name,b.mobile,c2.createtime,d.name as code_name from '.tablename('centralize_parcel_order').' a left join '.tablename('centralize_user').' b on a.user_id=b.id left join '.tablename('centralize_transport_detail').' c2 on c2.express_no=a.express_no left join '.tablename('centralize_consolidation_status').' d on d.code=a.status2 where a.status=3 and c2.program_type=5 and a.warehouse_id=:warehouse_id order by c2.createtime desc',[':warehouse_id'=>$warehouse_id]);

        //分类
        $cate = pdo_fetchall('select * from '.tablename('jd_goods_category').' where catLevel=1');

        //属性
        $item = pdo_fetchall('select * from '.tablename('centralize_goods_value').' where 1');

        //货物单位
        $unit = pdo_fetchall('select * from '.tablename('unit').' where 1');

    }
    include $this->template('warehouse/index/to_be_warehousing');
}
elseif($op=='show_warehousing_box'){
    //入库操作列表
    
    $order = pdo_fetch('select a.express_no,a.shelf_number,a.ordersn,a.id as orderid,a.status2,b.id,b.name,b.mobile from '.tablename('centralize_parcel_order').' a left join '.tablename('centralize_user').' b on a.user_id=b.id where a.status=3 and a.id=:oid and a.user_id=:uid and a.warehouse_id=:warehouse_id',[':oid'=>$data['orderid'],':uid'=>$data['uid'],':warehouse_id'=>$warehouse_id]);
    $order['program_type'] = pdo_fetchcolumn('select program_type from '.tablename('centralize_transport_detail').' where express_no=:express_no and program_type=6',[':express_no'=>$order['express_no']]);
    $order['consolidation_status'] = pdo_fetchcolumn('select name from '.tablename('centralize_consolidation_status').' where code=:code',[':code'=>$order['status2']]);
    $goods = pdo_fetchall('select * from '.tablename('centralize_parcel_order_goods').' where orderid=:orderid and user_id=:uid',[':orderid'=>$data['orderid'],':uid'=>$data['uid']]);
    foreach($goods as $k=>$v){
        $cate_item = pdo_fetchall('select * from '.tablename('jd_goods_category').' where find_in_set(id,:id)',[':id'=>$v['cateid']]);
        $goods[$k]['cate_item'] = '';
        foreach($cate_item as $kk=>$vv){
            $goods[$k]['cate_item'] .= $vv['catName'].',';
        }
        $goods[$k]['cate_item'] = rtrim($goods[$k]['cate_item'],',');

        //属性
        $good_item = pdo_fetchall('select * from '.tablename('centralize_goods_value').' where find_in_set(id,:id)',[':id'=>$v['itemid']]);
        $goods[$k]['good_item'] = '';
        foreach($good_item as $kk=>$vv){
            $goods[$k]['good_item'] .= $vv['title'].',';
        }
        $goods[$k]['good_item'] = rtrim($goods[$k]['good_item'],',');

        //单位
        $goods[$k]['unit'] = pdo_fetchcolumn('select code_name from '.tablename('unit').' where code_value=:code_value',[':code_value'=>$v['unit']]);
    }
    include $this->template('warehouse/index/show_warehousing_box');
}
elseif($op=='sure_warehousing'){
    //确认入库
    $user = pdo_fetch('select a.user_id,b.openid,a.id as orderid,a.express_no,c.warehouse_name,a.status from '.tablename('centralize_parcel_order').' a left join '.tablename('centralize_user').' b on a.user_id=b.id left join '.tablename('centralize_warehouse_list').' c on c.id=a.warehouse_id where a.id=:id and a.warehouse_id=:warehouse_id',[':id'=>$data['orderid'],':warehouse_id'=>$warehouse_id]);
    $user['program_type'] = pdo_fetchcolumn('select ifnull(program_type,0) from '.tablename('centralize_transport_detail').' where orderid=:oid and program_type=6',[':oid'=>$id]);
    if($user['program_type']==6 && $user['status'] ==3 ){
        show_json(-1,['msg'=>'该包裹已入库，请勿重复操作！']);
    }

    //1、修改订单信息
    //插入服务费用表
    pdo_insert('centralize_order_fee_log',[
        'type'=>1,//1国内订单，2国外订单
        'user_id'=>$user['user_id'],
        'orderid'=>$data['orderid'],
        'service_status'=>$data['status2'],
        'service_price'=>trim($data['service_price']),
        'paytype'=>'',//支付方式，1余额，2微信hk，3转数快
        'status'=>0,//0未支付，1已支付
        'createtime'=>$time,
        'paytime'=>'',
    ]);
    $res = pdo_update('centralize_parcel_order',[
        'shelf_number'=>trim($data['shelf_number']),
//        'status'=>3
    ],['id'=>$data['orderid'],'user_id'=>$data['uid']]);

    //2、插入盘点记录
    $goods = pdo_fetchall('select id from '.tablename('centralize_parcel_order_goods').' where orderid=:oid',[':oid'=>$data['orderid']]);
    foreach($goods as $k=>$v){
        pdo_insert('warehouse_inventory_list',[
            'warehouse_id'=>$warehouse_id,
            'user_id'=>$user['user_id'],
            'good_id'=>$v['id'],
            'orderid'=>$data['orderid'],
            'status'=>0,
            'createtime'=>$time
        ]);
    }

    if($res){
        //微信通知用户，先判断有无openid
        if(!empty($user['openid'])){
            $post2 = json_encode([
                'call'=>'confirmCollectionNotice',
                'first' =>'您好，您的快递包裹['.$user['express_no'].']已入库，请查看！',
                'keyword1' => '快递包裹['.$user['express_no'].']',
                'keyword2' => '已入库',
                'keyword3' => date('Y-m-d H:i:s',$time),
                'remark' => '',
                'url' => 'https://decl.gogo198.cn/centralize/parcel/index',
                'openid' => $user['openid'],
                'temp_id' => 'SVVs5OeD3FfsGwW0PEfYlZWetjScIT8kDxht5tlI1V8'
            ]);
            ihttp_request('https://shop.gogo198.cn/api/sendwechattemplatenotice.php', $post2);
        }else{
            //发短信...（待做）
        }

        //插入物流信息
        insert_transport_detail([
            'express_no'=>$user['express_no'],
            'orderid'=>$data['orderid'],
            'type'=>1,
            'program_type'=>6,
            'user_id'=>$user['user_id'],
            'opera_id'=>$_SESSION['warehouse_manager']['id'],
            'status'=>intval($data['status2']),
            'remark'=>'',
            'createtime'=>$time,
        ]);
    }
    show_json(0,['msg'=>'操作成功']);
}
elseif($op=='to_be_return'){
    //包裹退运列表
    
    $title = '包裹退运';
    if($data['ispost']==1){
        $list = pdo_fetch('select a.warehouse_return_info,a.pay_status,a.express_no,a.ordersn,a.id as orderid,a.status2,b.id,b.name,b.mobile,c2.createtime,d.name as code_name,c2.remark from '.tablename('centralize_parcel_order').' a left join '.tablename('centralize_user').' b on a.user_id=b.id left join '.tablename('centralize_transport_detail').' c2 on c2.express_no=a.express_no left join '.tablename('centralize_consolidation_status').' d on d.code=a.status2 where a.status=4 and c2.program_type=7 and a.express_no=:express_no and a.warehouse_id=:warehouse_id',[':express_no'=>trim($data['express_no']),':warehouse_id'=>$warehouse_id]);
        $is_return = pdo_fetch('select ifnull(id,0) as id from '.tablename('centralize_transport_detail').' where orderid=:oid and program_type=8',[':oid'=>$list['orderid']]);
        if($is_return['id']>0){
            $list['returned'] = '已退运';
        }else{
            $list['returned'] = '';
        }
        show_json(0,['data'=>$list]);
    }else{
        //查询全部待退运的包裹
        $list = pdo_fetchall('select a.warehouse_return_info,a.pay_status,a.express_no,a.ordersn,a.id as orderid,a.status2,b.id,b.name,b.mobile,c2.createtime,d.name as code_name,c2.remark from '.tablename('centralize_parcel_order').' a left join '.tablename('centralize_user').' b on a.user_id=b.id left join '.tablename('centralize_transport_detail').' c2 on c2.express_no=a.express_no left join '.tablename('centralize_consolidation_status').' d on d.code=a.status2  where a.status=4 and c2.program_type=7 and a.warehouse_id=:warehouse_id order by c2.createtime desc',[':warehouse_id'=>$warehouse_id]);
        foreach($list as $k=>$v){
            $is_return = pdo_fetch('select ifnull(id,0) as id from '.tablename('centralize_transport_detail').' where orderid=:oid and program_type=8',[':oid'=>$v['orderid']]);
            if($is_return['id']>0){
                $list[$k]['returned'] = '已退运';
            }else{
                $list[$k]['returned'] = '';
            }
        }
        //分类
        $cate = pdo_fetchall('select * from '.tablename('jd_goods_category').' where catLevel=1');

        //属性
        $item = pdo_fetchall('select * from '.tablename('centralize_goods_value').' where 1');

        //货物单位
        $unit = pdo_fetchall('select * from '.tablename('unit').' where 1');

    }
    include $this->template('warehouse/index/to_be_return');
}
elseif($op=='show_return_box'){
    $order = pdo_fetch('select * from '.tablename('centralize_parcel_order').' where id=:oid and warehouse_id=:warehouse_id',[':oid'=>$data['orderid'],':warehouse_id'=>$warehouse_id]);
    $order['return_info'] = json_decode($order['return_info'],true);

    $goods = pdo_fetchall('select * from '.tablename('centralize_parcel_order_goods').' where orderid=:oid',[':oid'=>$data['orderid']]);
    foreach($goods as $k=>$v){
        $cate_item = pdo_fetchall('select * from '.tablename('jd_goods_category').' where find_in_set(id,:id)',[':id'=>$v['cateid']]);
        $goods[$k]['cate_item'] = '';
        foreach($cate_item as $kk=>$vv){
            $goods[$k]['cate_item'] .= $vv['catName'].',';
        }
        $goods[$k]['cate_item'] = rtrim($goods[$k]['cate_item'],',');

        //属性
        $good_item = pdo_fetchall('select * from '.tablename('centralize_goods_value').' where find_in_set(id,:id)',[':id'=>$v['itemid']]);
        $goods[$k]['good_item'] = '';
        foreach($good_item as $kk=>$vv){
            $goods[$k]['good_item'] .= $vv['title'].',';
        }
        $goods[$k]['good_item'] = rtrim($goods[$k]['good_item'],',');
    }
    //包裹屬性
    $good_item = pdo_fetchall('select * from '.tablename('centralize_goods_value').' where 1');
    $good_item = json_encode($good_item,true);

    //包裹類型/产品分类,一级分类
    $cate_item = pdo_fetchall('select * from '.tablename('jd_goods_category').' where status=1 and catLevel=1');
    $cate_item = json_encode($cate_item,true);

    //单位
    $unit = pdo_fetchall('select * from '.tablename('unit').' where 1');


    //集运状态
    $consolidation_status = pdo_fetchall('select * from '.tablename('centralize_consolidation_status').' where ( code=902 or code=903 or code=904 ) and status=1');
    foreach($consolidation_status as $k=>$v){
        $consolidation_status[$k]['child'] = pdo_fetchall('select * from '.tablename('centralize_consolidation_status').' where pid=:pid and status=1',[':pid'=>$v['id']]);
    }

    //快递公司
    $express_company = pdo_fetchall('select * from '.tablename('customs_express_company_code').' where 1');

    //查询是否已退运
    $is_return = pdo_fetch('select ifnull(id,0) as id,remark from '.tablename('centralize_transport_detail').' where orderid=:oid and program_type=8',[':oid'=>$data['orderid']]);
    if($is_return['id']>0){
        $order['warehouse_return_info'] = json_decode($order['warehouse_return_info'],true);
    }
    include $this->template('warehouse/index/show_return_box');
}
elseif($op=='sure_return'){
    
    //确认退运
    $user = pdo_fetch('select a.user_id,b.openid,a.express_no,c.warehouse_name,a.status,d.program_type from '.tablename('centralize_parcel_order').' a left join '.tablename('centralize_user').' b on a.user_id=b.id left join '.tablename('centralize_warehouse_list').' c on c.id=a.warehouse_id  left join '.tablename('centralize_transport_detail').' d on d.express_no=a.express_no where a.id=:id and a.warehouse_id=:warehouse_id order by d.id desc',[':id'=>$data['orderid'],':warehouse_id'=>$warehouse_id]);
    if($user['program_type'] == 8 ){
        show_json(-1,['msg'=>'该包裹已退运，请勿重复操作！']);
    }

    //1、修改订单信息
    //插入服务费用表
    pdo_insert('centralize_order_fee_log',[
        'type'=>1,//1国内订单，2国外订单
        'user_id'=>$user['user_id'],
        'orderid'=>$data['orderid'],
        'service_status'=>$data['status2'],
        'service_price'=>trim($data['service_price']),
        'paytype'=>'',//支付方式，1余额，2微信hk，3转数快
        'status'=>0,//0未支付，1已支付
        'createtime'=>$time,
        'paytime'=>'',
    ]);
    $res = pdo_update('centralize_parcel_order',[
        'warehouse_return_info'=>json_encode([
            'express_company'=>$data['express_company'],
            'express_no'=>trim($data['express_no']),
        ],true),
//        'status'=>4
    ],['id'=>$data['orderid'],'user_id'=>$user['user_id']]);

    if($res){
        //微信通知用户，先判断有无openid
        if(!empty($user['openid'])){
            $post2 = json_encode([
                'call'=>'confirmCollectionNotice',
                'first' =>'您好，您的快递包裹['.$user['express_no'].']已退运，请查看！',
                'keyword1' => '快递包裹['.$user['express_no'].']',
                'keyword2' => '已退运',
                'keyword3' => date('Y-m-d H:i:s',$time),
                'remark' => trim($data['remark']),
                'url' => 'https://decl.gogo198.cn/centralize/parcel/index',
                'openid' => $user['openid'],
                'temp_id' => 'SVVs5OeD3FfsGwW0PEfYlZWetjScIT8kDxht5tlI1V8'
            ]);
            ihttp_request('https://shop.gogo198.cn/api/sendwechattemplatenotice.php', $post2);
        }else{
            //发短信...（待做）
        }

        //插入物流信息
        insert_transport_detail([
            'express_no'=>$user['express_no'],
            'orderid'=>$data['orderid'],
            'type'=>1,
            'program_type'=>8,
            'user_id'=>$user['user_id'],
            'opera_id'=>$_SESSION['warehouse_manager']['id'],
            'status'=>intval($data['status2']),
            'remark'=>trim($data['remark']),
            'createtime'=>$time,
        ]);
    }
    show_json(0,['msg'=>'操作成功']);
}
elseif($op=='to_be_abandon'){
    //弃货
    $title = '包裹弃货';
    if($data['ispost']==1){
        $list = pdo_fetch('select a.warehouse_abandon_info,a.pay_status,a.express_no,a.ordersn,a.id as orderid,a.status2,b.id,b.name,b.mobile,c2.createtime,d.name as code_name,c2.remark from '.tablename('centralize_parcel_order').' a left join '.tablename('centralize_user').' b on a.user_id=b.id left join '.tablename('centralize_transport_detail').' c2 on c2.express_no=a.express_no left join '.tablename('centralize_consolidation_status').' d on d.code=a.status2 where a.status=5 and c2.program_type=9 and a.express_no=:express_no and a.warehouse_id=:warehouse_id order by c2.id desc',[':express_no'=>trim($data['express_no']),':warehouse_id'=>$warehouse_id]);
        $is_abandon = pdo_fetch('select ifnull(id,0) as id from '.tablename('centralize_transport_detail').' where orderid=:oid and program_type=10',[':oid'=>$list['orderid']]);
        if($is_abandon['id']>0){
            $list['abandoned'] = '已弃货';
        }else{
            $list['abandoned'] = '';
        }
        show_json(0,['data'=>$list]);
    }else{
        //查询全部待入库的包裹
        $list = pdo_fetchall('select a.warehouse_abandon_info,a.pay_status,a.express_no,a.ordersn,a.id as orderid,a.status2,b.id,b.name,b.mobile,c2.createtime,d.name as code_name,c2.remark from '.tablename('centralize_parcel_order').' a left join '.tablename('centralize_user').' b on a.user_id=b.id left join '.tablename('centralize_transport_detail').' c2 on c2.express_no=a.express_no left join '.tablename('centralize_consolidation_status').' d on d.code=a.status2  where a.status=5 and c2.program_type=9 and a.warehouse_id=:warehouse_id order by c2.id desc',[':warehouse_id'=>$warehouse_id]);
        foreach($list as $k=>$v){
            $is_abandon = pdo_fetch('select ifnull(id,0) as id from '.tablename('centralize_transport_detail').' where orderid=:oid and program_type=10',[':oid'=>$v['orderid']]);
            if($is_abandon['id']>0){
                $list[$k]['abandoned'] = '已弃货';
            }else{
                $list[$k]['abandoned'] = '';
            }
        }
        //分类
        $cate = pdo_fetchall('select * from '.tablename('jd_goods_category').' where catLevel=1');

        //属性
        $item = pdo_fetchall('select * from '.tablename('centralize_goods_value').' where 1');

        //货物单位
        $unit = pdo_fetchall('select * from '.tablename('unit').' where 1');

    }
    include $this->template('warehouse/index/to_be_abandon');
}
elseif($op=='show_abandon_box'){
    $order = pdo_fetch('select * from '.tablename('centralize_parcel_order').' where id=:oid and warehouse_id=:warehouse_id',[':oid'=>$data['orderid'],':warehouse_id'=>$warehouse_id]);

    $goods = pdo_fetchall('select * from '.tablename('centralize_parcel_order_goods').' where orderid=:oid',[':oid'=>$data['orderid']]);
    foreach($goods as $k=>$v){
        $cate_item = pdo_fetchall('select * from '.tablename('jd_goods_category').' where find_in_set(id,:id)',[':id'=>$v['cateid']]);
        $goods[$k]['cate_item'] = '';
        foreach($cate_item as $kk=>$vv){
            $goods[$k]['cate_item'] .= $vv['catName'].',';
        }
        $goods[$k]['cate_item'] = rtrim($goods[$k]['cate_item'],',');

        //属性
        $good_item = pdo_fetchall('select * from '.tablename('centralize_goods_value').' where find_in_set(id,:id)',[':id'=>$v['itemid']]);
        $goods[$k]['good_item'] = '';
        foreach($good_item as $kk=>$vv){
            $goods[$k]['good_item'] .= $vv['title'].',';
        }
        $goods[$k]['good_item'] = rtrim($goods[$k]['good_item'],',');
    }
    //包裹屬性
    $good_item = pdo_fetchall('select * from '.tablename('centralize_goods_value').' where 1');
    $good_item = json_encode($good_item,true);

    //包裹類型/产品分类,一级分类
    $cate_item = pdo_fetchall('select * from '.tablename('jd_goods_category').' where status=1 and catLevel=1');
    $cate_item = json_encode($cate_item,true);

    //单位
    $unit = pdo_fetchall('select * from '.tablename('unit').' where 1');


    //集运状态
    $consolidation_status = pdo_fetchall('select * from '.tablename('centralize_consolidation_status').' where ( code=902 or code=903 or code=904 ) and status=1');
    foreach($consolidation_status as $k=>$v){
        $consolidation_status[$k]['child'] = pdo_fetchall('select * from '.tablename('centralize_consolidation_status').' where pid=:pid and status=1',[':pid'=>$v['id']]);
    }

    //查询是否已退运
    $is_abandon = pdo_fetch('select ifnull(id,0) as id,remark from '.tablename('centralize_transport_detail').' where orderid=:oid and program_type=10',[':oid'=>$data['orderid']]);
    if($is_abandon['id']>0){
        $order['warehouse_abandon_info'] = json_decode($order['warehouse_abandon_info'],true);
    }

    include $this->template('warehouse/index/show_abandon_box');
}
elseif($op=='sure_abandon'){
    
    //确认弃货
    $user = pdo_fetch('select a.user_id,b.openid,a.express_no,c.warehouse_name,a.status,d.program_type from '.tablename('centralize_parcel_order').' a left join '.tablename('centralize_user').' b on a.user_id=b.id left join '.tablename('centralize_warehouse_list').' c on c.id=a.warehouse_id  left join '.tablename('centralize_transport_detail').' d on d.express_no=a.express_no where a.id=:id and a.warehouse_id=:warehouse_id order by d.id desc',[':id'=>$data['orderid'],':warehouse_id'=>$warehouse_id]);
    if($user['program_type'] == 10 ){
        show_json(-1,['msg'=>'该包裹已弃货，请勿重复操作！']);
    }

    //1、修改订单信息
    //插入服务费用表
    pdo_insert('centralize_order_fee_log',[
        'type'=>1,//1国内订单，2国外订单
        'user_id'=>$user['user_id'],
        'orderid'=>$data['orderid'],
        'service_status'=>$data['status2'],
        'service_price'=>trim($data['service_price']),
        'paytype'=>'',//支付方式，1余额，2微信hk，3转数快
        'status'=>0,//0未支付，1已支付
        'createtime'=>$time,
        'paytime'=>'',
    ]);
    $res = pdo_update('centralize_parcel_order',[
        'warehouse_abandon_info'=>json_encode([
            'abandon_address'=>trim($data['abandon_address']),
            'abandon_addressee'=>trim($data['abandon_addressee']),
            'abandon_tel'=>trim($data['abandon_tel']),
        ],true),
//        'status'=>5
    ],['id'=>$data['orderid'],'user_id'=>$user['user_id']]);

    if($res){
        //微信通知用户，先判断有无openid
        if(!empty($user['openid'])){
            $post2 = json_encode([
                'call'=>'confirmCollectionNotice',
                'first' =>'您好，您的快递包裹['.$user['express_no'].']已弃货，请查看！',
                'keyword1' => '快递包裹['.$user['express_no'].']',
                'keyword2' => '已弃货',
                'keyword3' => date('Y-m-d H:i:s',$time),
                'remark' => trim($data['remark']),
                'url' => 'https://decl.gogo198.cn/centralize/parcel/index',
                'openid' => $user['openid'],
                'temp_id' => 'SVVs5OeD3FfsGwW0PEfYlZWetjScIT8kDxht5tlI1V8'
            ]);
            ihttp_request('https://shop.gogo198.cn/api/sendwechattemplatenotice.php', $post2);
        }else{
            //发短信...（待做）
        }

        //插入物流信息
        insert_transport_detail([
            'express_no'=>$user['express_no'],
            'orderid'=>$data['orderid'],
            'type'=>1,
            'program_type'=>10,
            'user_id'=>$user['user_id'],
            'opera_id'=>$_SESSION['warehouse_manager']['id'],
            'status'=>intval($data['status2']),
            'remark'=>trim($data['remark']),
            'createtime'=>$time,
        ]);
    }
    show_json(0,['msg'=>'操作成功']);
}
elseif($op=='to_be_spinoff'){
    $title = '包裹分拆';
    if($data['ispost']==1){
//        and ( c2.program_type=11 or c2.program_type=12 or c2.program_type=13 or c2.program_type=14 )
        $list = pdo_fetch('select a.express_no,a.ordersn,a.id as orderid,a.status2,b.id,b.name,b.mobile,d.name as code_name,e.status as spin_status from '.tablename('centralize_parcel_order').' a left join '.tablename('centralize_user').' b on a.user_id=b.id left join '.tablename('centralize_consolidation_status').' d on d.code=a.status2 left join '.tablename('centralize_parcel_order_spin').' e on e.id=a.spin_id where a.status=3 and a.express_no=:express_no and a.warehouse_id=:warehouse_id and e.status!=3 order by e.createtime desc',[':express_no'=>trim($data['express_no']),':warehouse_id'=>$warehouse_id]);
        $list['createtime'] = pdo_fetchcolumn('select createtime from '.tablename('centralize_transport_detail').' where orderid=:oid order by id desc',[':oid'=>$list['orderid']]);
        switch($list['spin_status']){
            case -2:
                $list[$k]['spin_status']='包裹已损毁，等待买家操作';break;
            case -1:
                $list['spin_status']='拒绝分拆';break;
            case 0:
                $list['spin_status']='等待审核';break;
            case 1:
                $list['spin_status']='分拆完成';break;
//            case 2:
//                $list['spin_status']='已付款，待分拆';break;
//            case 3:
//                $list['spin_status']='已分拆';break;
        }
        show_json(0,['data'=>$list]);
    }else{
        //查询全部待分拆的包裹
        $list = pdo_fetchall('select a.express_no,a.ordersn,a.id as orderid,a.status2,b.id,b.name,b.mobile,d.name as code_name,e.status as spin_status from '.tablename('centralize_parcel_order').' a left join '.tablename('centralize_user').' b on a.user_id=b.id left join '.tablename('centralize_consolidation_status').' d on d.code=a.status2 left join '.tablename('centralize_parcel_order_spin').' e on e.id=a.spin_id where a.status=3 and a.warehouse_id=:warehouse_id and e.status!=3 order by e.createtime desc',[':warehouse_id'=>$warehouse_id]);

        foreach($list as $k=>$v){
            $list[$k]['createtime'] = pdo_fetchcolumn('select createtime from '.tablename('centralize_transport_detail').' where orderid=:oid order by id desc',[':oid'=>$v['orderid']]);
            switch($v['spin_status']){
                case -2:
                    $list[$k]['spin_status']='包裹已损毁，等待买家操作';break;
                case -1:
                    $list[$k]['spin_status']='拒绝分拆';break;
                case 0:
                    $list[$k]['spin_status']='等待审核';break;
                case 1:
                    $list[$k]['spin_status']='分拆完成';break;
//                case 2:
//                    $list[$k]['spin_status']='已付款，待分拆';break;
//                case 3:
//                    $list[$k]['spin_status']='已分拆';break;
            }
        }

        //分类
        $cate = pdo_fetchall('select * from '.tablename('jd_goods_category').' where catLevel=1');

        //属性
        $item = pdo_fetchall('select * from '.tablename('centralize_goods_value').' where 1');

        //货物单位
        $unit = pdo_fetchall('select * from '.tablename('unit').' where 1');

    }
    include $this->template('warehouse/index/to_be_spinoff');
}
elseif($op=='show_spinoff_box'){

    $order = pdo_fetch('select * from '.tablename('centralize_parcel_order').' where id=:oid and warehouse_id=:warehouse_id',[':oid'=>$data['orderid'],':warehouse_id'=>$warehouse_id]);

    $goods = pdo_fetchall('select * from '.tablename('centralize_parcel_order_goods').' where orderid=:oid',[':oid'=>$data['orderid']]);
    foreach($goods as $k=>$v){
        $cate_item = pdo_fetchall('select * from '.tablename('jd_goods_category').' where find_in_set(id,:id)',[':id'=>$v['cateid']]);
        $goods[$k]['cate_item'] = '';
        foreach($cate_item as $kk=>$vv){
            $goods[$k]['cate_item'] .= $vv['catName'].',';
        }
        $goods[$k]['cate_item'] = rtrim($goods[$k]['cate_item'],',');

        //属性
        $good_item = pdo_fetchall('select * from '.tablename('centralize_goods_value').' where find_in_set(id,:id)',[':id'=>$v['itemid']]);
        $goods[$k]['good_item'] = '';
        foreach($good_item as $kk=>$vv){
            $goods[$k]['good_item'] .= $vv['title'].',';
        }
        $goods[$k]['good_item'] = rtrim($goods[$k]['good_item'],',');

        //体积
        $volumn = explode('*',$v['true_volumn']);
        $goods[$k]['length'] = $volumn[0];
        $goods[$k]['width'] = $volumn[1];
        $goods[$k]['height'] = $volumn[2];
    }
    //包裹屬性
    $good_item = pdo_fetchall('select * from '.tablename('centralize_goods_value').' where 1');
    $good_item = json_encode($good_item,true);

    //包裹類型/产品分类,一级分类
    $cate_item = pdo_fetchall('select * from '.tablename('jd_goods_category').' where status=1 and catLevel=1');
    $cate_item = json_encode($cate_item,true);

    //单位
    $unit = pdo_fetchall('select * from '.tablename('unit').' where 1');

    //分拆信息
    $spin_info = pdo_fetch('select * from '.tablename('centralize_parcel_order_spin').' where id=:spin_id',[':spin_id'=>$order['spin_id']]);
    $spin_info['spin_info'] = json_decode($spin_info['spin_info'],true);
    foreach($spin_info['spin_info'] as $k=>$v){
        $spin_info['spin_info'][$k]['gname'] = pdo_fetchcolumn('select name from '.tablename('centralize_parcel_order_goods').' where id=:id',[':id'=>$v['gid']]);
    }
    //查看包裹是否损毁
    if($spin_info['status']==-2){
        $spin_info['damage_info'] = pdo_fetch('select a.status,a.remark,b.name from '.tablename('centralize_transport_detail').' a left join '.tablename('centralize_consolidation_status').' b on b.code=a.status where a.orderid=:oid and a.program_type=-3',[':oid'=>$order['id']]);
    }

    //集运状态
//    $consolidation_status = pdo_fetchall('select * from '.tablename('centralize_consolidation_status').' where ( code=902 or code=903 or code=904 ) and status=1');
//    foreach($consolidation_status as $k=>$v){
//        $consolidation_status[$k]['child'] = pdo_fetchall('select * from '.tablename('centralize_consolidation_status').' where pid=:pid and status=1',[':pid'=>$v['id']]);
//    }

    //集运状态(损毁)
    $damage_status = pdo_fetchall('select * from '.tablename('centralize_consolidation_status').' where pid=21 and status=1');

    include $this->template('warehouse/index/show_spinoff_box');
}
elseif($op=='sure_spinoff'){
    //确认分拆
    $spin_info = pdo_fetch('select * from '.tablename('centralize_parcel_order_spin').' where orderid=:oid',[':oid'=>$data['orderid']]);
    $data['spin_status'] = $data['spin_status']==2?3:$data['spin_status'];//已支付状态下提交作已完成分拆状态
    if(($spin_info['status']==$data['spin_status']) && $data['damage_status']==0){
        show_json(-1,['msg'=>'请勿重复操作！']);
    }

    $user = pdo_fetch('select a.*,b.openid,c.warehouse_name,d.program_type from '.tablename('centralize_parcel_order').' a left join '.tablename('centralize_user').' b on a.user_id=b.id left join '.tablename('centralize_warehouse_list').' c on c.id=a.warehouse_id  left join '.tablename('centralize_transport_detail').' d on d.orderid=a.id where a.id=:id and a.warehouse_id=:warehouse_id order by d.id desc',[':id'=>$data['orderid'],':warehouse_id'=>$warehouse_id]);

    $status = $data['status2'];
    $program_type = 0;

    if($data['damage_status']==0){
//        if($data['spin_status']==1){
//            //审核通过
//            $program_type=12;
//            $first = '审核通过，请查看！';
//            $status2 = '审核通过';
//
//            //增加分拆服务费用
//            $origin_service = json_decode($user['service_series'],true);
//            $origin_service = array_merge($origin_service,[
//                'service_84'=>sprintf('%.2f',trim($data['service_84'])),
//            ]);
//            pdo_update('centralize_parcel_order',['service_series'=>json_encode($origin_service,true)],['id'=>$data['orderid'],'user_id'=>$user['user_id']]);
//        }
        if($data['spin_status']==1){
            //审核通过，完成分拆
            $first = '已分拆，请查看！';
            $status2 = '已分拆';
            $program_type=14;

            pdo_begin();
            try {
                //2、插入新的快递包裹订单
                pdo_insert('centralize_parcel_order',[
                    'user_id'=>$user['user_id'],
                    'ordersn'=>$user['user_id'].date('mdhi').$status,
                    'warehouse_id'=>$user['warehouse_id'],
                    'express_id'=>$user['express_id'],
                    'express_no'=>$user['express_no'],
                    'packingSwitch1'=>$user['packingSwitch1'],
                    'packingSwitch2'=>$user['packingSwitch2'],
                    'packingSwitch3'=>$user['packingSwitch3'],
                    'agree'=>1,
                    'status'=>3,
                    'pic_file'=>$user['pic_file'],
                    'status2'=>$status,
                    'kaibao_apply'=>2,
                    'is_kaibao'=>1,
                    'shelf_number'=>trim($data['shelf_number']),
                    'service_series'=>$user['service_series'],
                    'spin_id'=>$spin_info['id'],
                    'createtime'=>$time,
                ]);
                $order_id = pdo_insertid();

                //5、插入物流信息
                insert_transport_detail([
                    'express_no'=>$user['express_no'],
                    'orderid'=>$order_id,
                    'type'=>1,
                    'program_type'=>$program_type,
                    'user_id'=>$user['user_id'],
                    'opera_id'=>$_SESSION['warehouse_manager']['id'],
                    'status'=>$status,
                    'remark'=>trim($data['remark']),
                    'createtime'=>$time,
                ]);

                //3、插入包裹商品表
                $spin_info['spin_info'] = json_decode($spin_info['spin_info'],true);
                foreach($spin_info['spin_info'] as $k=>$v){
                    $order_goods = pdo_fetch('select * from '.tablename('centralize_parcel_order_goods').' where id=:gid',[':gid'=>$v['gid']]);

                    $true_volumn = trim($data['spin_length'][$k]).'*'.trim($data['spin_width'][$k]).'*'.trim($data['spin_height'][$k]);
                    pdo_insert('centralize_parcel_order_goods',[
                        'user_id'=>$user['user_id'],
                        'orderid'=>$order_id,
                        'name'=>$order_goods['name'],
                        'money'=>$order_goods['money'],
                        'brand'=>$order_goods['brand'],
                        'cateid'=>$order_goods['cateid'],
                        'itemid'=>$order_goods['itemid'],
                        'num'=>$v['num'],
                        'netwt'=>$order_goods['netwt'],
                        'grosswt'=>$order_goods['grosswt'],
                        'unit'=>$order_goods['unit'],
                        'true_volumn'=>$true_volumn,
                        'createtime'=>$time,
                    ]);
                }

                //3、修改原始订单商品表货物信息
                $spin_info['origin_info'] = json_decode($spin_info['origin_info'],true);
                foreach($spin_info['origin_info'] as $k=>$v){
                    $order_goods = pdo_fetch('select * from '.tablename('centralize_parcel_order_goods').' where id=:gid',[':gid'=>$v['gid']]);
                    $true_volumn = '';
                    foreach($data['gid'] as $kk=>$vv){
                        if($vv==$v['gid']){
                            $true_volumn = trim($data['origin_length'][$k]).'*'.trim($data['origin_width'][$k]).'*'.trim($data['origin_height'][$k]);
                        }
                    }
                    if(empty($v['num'])){
                        pdo_delete('centralize_parcel_order_goods',['id'=>$v['gid']]);
                    }else{
                        pdo_update('centralize_parcel_order_goods',['num'=>$v['num'],'true_volumn'=>$true_volumn],['id'=>$v['gid']]);
                    }
                }
                pdo_commit();
            } catch (\Exception $e) {
                pdo_rollback();
                show_json(-1,['msg'=>'操作失败']);
            }

        }
        elseif($data['spin_status']==-1){
            //拒绝分拆
            $first = '审核不通过，请查看！';
            $status2 = '审核不通过';
            $program_type=13;
        }
    }else{
        //损毁
        $damage_title = pdo_fetchcolumn('select name from '.tablename('centralize_consolidation_status').' where code=:code',[':code'=>$data['damage_status']]);
        $first = '遭到'.$damage_title.'，请查看！';
        $status2 = $damage_title;
        $status=$data['damage_status'];
        $program_type=-3;
        $data['spin_status'] = -2;
        pdo_update('centralize_parcel_order',[
            'ordersn'=>substr_replace($user['ordersn'],$data['damage_status'],-2,2),
            'status2'=>$data['damage_status'],
        ],['id'=>$user['id'],'user_id'=>$user['user_id']]);

    }

    //3、修改分拆表信息
    //插入服务费用表
    pdo_insert('centralize_order_fee_log',[
        'type'=>1,//1国内订单，2国外订单
        'user_id'=>$user['user_id'],
        'orderid'=>$data['orderid'],
        'service_status'=>$data['status2'],
        'service_price'=>trim($data['service_price']),
        'paytype'=>'',//支付方式，1余额，2微信hk，3转数快
        'status'=>0,//0未支付，1已支付
        'createtime'=>$time,
        'paytime'=>'',
    ]);
    pdo_update('centralize_parcel_order_spin',['status'=>$data['spin_status']],['orderid'=>$data['orderid'],'user_id'=>$user['user_id']]);

    //4、微信通知用户，先判断有无openid
    if(!empty($user['openid'])){
        $post2 = json_encode([
            'call'=>'confirmCollectionNotice',
            'first' =>'您好，您的快递包裹['.$user['express_no'].']'.$first,
            'keyword1' => '快递包裹['.$user['express_no'].']',
            'keyword2' => $status2,
            'keyword3' => date('Y-m-d H:i:s',$time),
            'remark' => trim($data['remark']),
            'url' => 'https://decl.gogo198.cn/centralize/parcel/index',
            'openid' => $user['openid'],
            'temp_id' => 'SVVs5OeD3FfsGwW0PEfYlZWetjScIT8kDxht5tlI1V8'
        ]);
        ihttp_request('https://shop.gogo198.cn/api/sendwechattemplatenotice.php', $post2);
    }else{
        //发短信...（待做）
    }

    //5、插入物流信息
    insert_transport_detail([
        'express_no'=>$user['express_no'],
        'orderid'=>$user['id'],
        'type'=>1,
        'program_type'=>$program_type,
        'user_id'=>$user['user_id'],
        'opera_id'=>$_SESSION['warehouse_manager']['id'],
        'status'=>$status,
        'remark'=>trim($data['remark']),
        'createtime'=>$time,
    ]);

    show_json(0,['msg'=>'操作成功']);
}
elseif($op=='to_be_attachment'){
    $title = '包裹附加';
    if($data['ispost']==1){
//        and ( c2.program_type=11 or c2.program_type=12 or c2.program_type=13 or c2.program_type=14 )
        $list = pdo_fetch('select a.express_no,a.ordersn,a.id as orderid,a.status2,b.id,b.name,b.mobile,d.name as code_name from '.tablename('centralize_parcel_order').' a left join '.tablename('centralize_user').' b on a.user_id=b.id left join '.tablename('centralize_consolidation_status').' d on d.code=a.status2 where a.status=3 and a.express_no=:express_no and a.warehouse_id=:warehouse_id and ( a.status2=86 or a.status2=87 ) order by a.createtime desc',[':express_no'=>trim($data['express_no']),':warehouse_id'=>$warehouse_id]);
        $detail = pdo_fetch('select createtime,remark from '.tablename('centralize_transport_detail').' where orderid=:oid  and program_type=19',[':oid'=>$list['orderid']]);
        switch($detail['program_type']){
            case 19:
                $list['attach_status']='包裹附加申请';break;
//            case 20:
//                $list['attach_status']='包裹附加成功';break;
            case 21:
                $list['attach_status']='包裹附加拒绝';break;
            case 22:
                $list['attach_status']='包裹附加完成';break;
        }
//        if($list['pay_status']==1 && $detail['program_type']!=22){
//            $list['attach_status'] = '买家已付款，待包裹附加';
//        }
        $list['program_type'] = $detail['program_type'];
        $list['createtime'] = $detail['createtime'];
        $list['remark'] = $detail['remark'];

        show_json(0,['data'=>$list]);
    }else{
        //查询全部附加的包裹
//        ,a.pay_status
        $list = pdo_fetchall('select a.express_no,a.ordersn,a.id as orderid,a.status2,b.id,b.name,b.mobile,d.name as code_name from '.tablename('centralize_parcel_order').' a left join '.tablename('centralize_user').' b on a.user_id=b.id left join '.tablename('centralize_consolidation_status').' d on d.code=a.status2 where a.status=3 and a.warehouse_id=:warehouse_id and ( a.status2=86 or a.status2=87 ) order by a.createtime desc',[':warehouse_id'=>$warehouse_id]);

        foreach($list as $k=>$v){
            $detail = pdo_fetch('select createtime,remark,program_type from '.tablename('centralize_transport_detail').' where orderid=:oid and program_type=19',[':oid'=>$v['orderid']]);
            switch($detail['program_type']){
                case 19:
                    $list[$k]['attach_status']='包裹附加申请';break;
//                case 20:
//                    $list[$k]['attach_status']='包裹附加通过，待买家付款';break;
                case 21:
                    $list[$k]['attach_status']='包裹附加拒绝';break;
                case 22:
                    $list[$k]['attach_status']='包裹附加完成';break;
            }
//            if($v['pay_status']==1){
//                $list[$k]['attach_status'] = '买家已付款，待包裹附加';
//            }
            $list[$k]['program_type'] = $detail['program_type'];
            $list[$k]['createtime'] = $detail['createtime'];
            $list[$k]['remark'] = $detail['remark'];
        }

        //分类
        $cate = pdo_fetchall('select * from '.tablename('jd_goods_category').' where catLevel=1');

        //属性
        $item = pdo_fetchall('select * from '.tablename('centralize_goods_value').' where 1');

        //货物单位
        $unit = pdo_fetchall('select * from '.tablename('unit').' where 1');

    }
    include $this->template('warehouse/index/to_be_attachment');
}
elseif($op=='show_attachment_box'){
    $order = pdo_fetch('select * from '.tablename('centralize_parcel_order').' where id=:oid and warehouse_id=:warehouse_id',[':oid'=>$data['orderid'],':warehouse_id'=>$warehouse_id]);
//    $order['service_series'] = json_decode($order['service_series'],true);
//    $service_series = $order['service_series']['service_'.$order['status2']];
    $goods = pdo_fetchall('select * from '.tablename('centralize_parcel_order_goods').' where orderid=:oid',[':oid'=>$data['orderid']]);
    foreach($goods as $k=>$v){
        $cate_item = pdo_fetchall('select * from '.tablename('jd_goods_category').' where find_in_set(id,:id)',[':id'=>$v['cateid']]);
        $goods[$k]['cate_item'] = '';
        foreach($cate_item as $kk=>$vv){
            $goods[$k]['cate_item'] .= $vv['catName'].',';
        }
        $goods[$k]['cate_item'] = rtrim($goods[$k]['cate_item'],',');

        //属性
        $good_item = pdo_fetchall('select * from '.tablename('centralize_goods_value').' where find_in_set(id,:id)',[':id'=>$v['itemid']]);
        $goods[$k]['good_item'] = '';
        foreach($good_item as $kk=>$vv){
            $goods[$k]['good_item'] .= $vv['title'].',';
        }
        $goods[$k]['good_item'] = rtrim($goods[$k]['good_item'],',');
    }
    //包裹屬性
    $good_item = pdo_fetchall('select * from '.tablename('centralize_goods_value').' where 1');
    $good_item = json_encode($good_item,true);

    //包裹類型/产品分类,一级分类
    $cate_item = pdo_fetchall('select * from '.tablename('jd_goods_category').' where status=1 and catLevel=1');
    $cate_item = json_encode($cate_item,true);

    //单位
    $unit = pdo_fetchall('select * from '.tablename('unit').' where 1');

    //查询附加状态
    $attach_info = pdo_fetch('select * from '.tablename('centralize_transport_detail').' where orderid=:oid order by id desc',[':oid'=>$data['orderid']]);

    include $this->template('warehouse/index/show_attachment_box');
}
elseif($op=='sure_attachment'){
    //确认附加
    $user = pdo_fetch('select a.user_id,a.service_series,a.ordersn,b.openid,a.express_no,c.warehouse_name,a.status,d.program_type from '.tablename('centralize_parcel_order').' a left join '.tablename('centralize_user').' b on a.user_id=b.id left join '.tablename('centralize_warehouse_list').' c on c.id=a.warehouse_id  left join '.tablename('centralize_transport_detail').' d on d.express_no=a.express_no where a.id=:id and a.warehouse_id=:warehouse_id order by d.id desc',[':id'=>$data['orderid'],':warehouse_id'=>$warehouse_id]);
    if($user['program_type'] == 22 ){
        show_json(-1,['msg'=>'该包裹已附加，请勿重复操作！']);
    }

    if($data['c_status']==1){
        //同意
        //增加分拆服务费用
//        $origin_service = json_decode($user['service_series'],true);
//        $origin_service = array_merge($origin_service,[
//            'service_'.$data['status2']=>sprintf('%.2f',trim($data['service_'.$data['status2']])),
//        ]);
//        pdo_update('centralize_parcel_order',['service_series'=>json_encode($origin_service,true)],['id'=>$data['orderid'],'user_id'=>$user['user_id']]);
        $first='已同意包裹附加';
        $program_type=22;
        //插入服务费用表
        pdo_insert('centralize_order_fee_log',[
            'type'=>1,//1国内订单，2国外订单
            'user_id'=>$user['user_id'],
            'orderid'=>$data['orderid'],
            'service_status'=>$data['status2'],
            'service_price'=>trim($data['service_price']),
            'paytype'=>'',//支付方式，1余额，2微信hk，3转数快
            'status'=>0,//0未支付，1已支付
            'createtime'=>$time,
            'paytime'=>'',
        ]);
    }elseif($data['c_status']==-1){
        //拒绝
        $first='已拒绝包裹附加';
        $program_type=21;
    }
//    elseif($data['c_status']==2){
//        //立即附加
//        $first='已对包裹附加';
//        $program_type=22;
//    }

    $status = pdo_fetchcolumn('select name from '.tablename('centralize_consolidation_status').' where code=:code',[':code'=>$data['status2']]);
    //微信通知用户，先判断有无openid
    if(!empty($user['openid'])){
        $post2 = json_encode([
            'call'=>'confirmCollectionNotice',
            'first' =>'您好，您的包裹订单['.$user['ordersn'].']'.$first.'，请查看！',
            'keyword1' => '包裹订单['.$user['ordersn'].']',
            'keyword2' => $status,
            'keyword3' => date('Y-m-d H:i:s',$time),
            'remark' => trim($data['remark']),
            'url' => 'https://decl.gogo198.cn/centralize/parcel/index',
            'openid' => $user['openid'],
            'temp_id' => 'SVVs5OeD3FfsGwW0PEfYlZWetjScIT8kDxht5tlI1V8'
        ]);
        ihttp_request('https://shop.gogo198.cn/api/sendwechattemplatenotice.php', $post2);
    }else{
        //发短信...（待做）
    }

    //插入物流信息
    insert_transport_detail([
        'express_no'=>$user['express_no'],
        'orderid'=>$data['orderid'],
        'type'=>1,
        'program_type'=>$program_type,
        'user_id'=>$user['user_id'],
        'opera_id'=>$_SESSION['warehouse_manager']['id'],
        'status'=>intval($data['status2']),
        'remark'=>trim($data['remark']),
        'createtime'=>$time,
    ]);

    show_json(0,['msg'=>'操作成功']);
}
elseif($op=='to_be_rejection'){
    $title = '包裹剔除';
    if($data['ispost']==1){
//        and ( c2.program_type=11 or c2.program_type=12 or c2.program_type=13 or c2.program_type=14 ),a.pay_status
        $list = pdo_fetch('select a.express_no,a.ordersn,a.id as orderid,a.status2,b.id,b.name,b.mobile,d.name as code_name from '.tablename('centralize_parcel_order').' a left join '.tablename('centralize_user').' b on a.user_id=b.id left join '.tablename('centralize_consolidation_status').' d on d.code=a.status2 where a.status=3 and a.express_no=:express_no and a.warehouse_id=:warehouse_id and ( a.status2=88 or a.status2=89 ) order by a.createtime desc',[':express_no'=>trim($data['express_no']),':warehouse_id'=>$warehouse_id]);
        $detail = pdo_fetch('select createtime,remark from '.tablename('centralize_transport_detail').' where orderid=:oid and program_type=23',[':oid'=>$list['orderid']]);
        switch($detail['program_type']){
            case 23:
                $list['attach_status']='包裹剔除申请';break;
//            case 24:
//                $list['attach_status']='包裹剔除通过，待买家付款';break;
            case 25:
                $list['attach_status']='包裹剔除拒绝';break;
            case 26:
                $list['attach_status']='包裹剔除完成';break;
        }
//        if($list['pay_status']==2){
//            $list['attach_status'] = '买家已付款，待包裹剔除';
//        }
        $list['program_type'] = $detail['program_type'];
        $list['createtime'] = $detail['createtime'];
        $list['remark'] = $detail['remark'];

        show_json(0,['data'=>$list]);
    }else{
        //查询全部附加的包裹
//        ,a.pay_status
        $list = pdo_fetchall('select a.express_no,a.ordersn,a.id as orderid,a.status2,b.id,b.name,b.mobile,d.name as code_name from '.tablename('centralize_parcel_order').' a left join '.tablename('centralize_user').' b on a.user_id=b.id left join '.tablename('centralize_consolidation_status').' d on d.code=a.status2 where a.status=3 and a.warehouse_id=:warehouse_id and ( a.status2=88 or a.status2=89 ) order by a.createtime desc',[':warehouse_id'=>$warehouse_id]);

        foreach($list as $k=>$v){
            $detail = pdo_fetch('select createtime,remark,program_type from '.tablename('centralize_transport_detail').' where orderid=:oid and program_type=23',[':oid'=>$v['orderid']]);
            switch($detail['program_type']){
                case 23:
                    $list[$k]['attach_status']='包裹剔除申请';break;
//                case 24:
//                    $list[$k]['attach_status']='包裹剔除通过，待买家付款';break;
                case 25:
                    $list[$k]['attach_status']='包裹剔除拒绝';break;
                case 26:
                    $list[$k]['attach_status']='包裹剔除完成';break;
            }
//            if($v['pay_status']==2 && $detail['program_type']!=26){
//                $list[$k]['attach_status'] = '买家已付款，待包裹剔除';
//            }
            $list[$k]['program_type'] = $detail['program_type'];
            $list[$k]['createtime'] = $detail['createtime'];
            $list[$k]['remark'] = $detail['remark'];
        }

        //分类
        $cate = pdo_fetchall('select * from '.tablename('jd_goods_category').' where catLevel=1');

        //属性
        $item = pdo_fetchall('select * from '.tablename('centralize_goods_value').' where 1');

        //货物单位
        $unit = pdo_fetchall('select * from '.tablename('unit').' where 1');

    }
    include $this->template('warehouse/index/to_be_rejection');
}
elseif($op=='show_rejection_box'){
    $order = pdo_fetch('select * from '.tablename('centralize_parcel_order').' where id=:oid and warehouse_id=:warehouse_id',[':oid'=>$data['orderid'],':warehouse_id'=>$warehouse_id]);
//    $order['service_series'] = json_decode($order['service_series'],true);
//    $service_series = $order['service_series']['service_'.$order['status2']];
    $goods = pdo_fetchall('select * from '.tablename('centralize_parcel_order_goods').' where orderid=:oid',[':oid'=>$data['orderid']]);
    foreach($goods as $k=>$v){
        $cate_item = pdo_fetchall('select * from '.tablename('jd_goods_category').' where find_in_set(id,:id)',[':id'=>$v['cateid']]);
        $goods[$k]['cate_item'] = '';
        foreach($cate_item as $kk=>$vv){
            $goods[$k]['cate_item'] .= $vv['catName'].',';
        }
        $goods[$k]['cate_item'] = rtrim($goods[$k]['cate_item'],',');

        //属性
        $good_item = pdo_fetchall('select * from '.tablename('centralize_goods_value').' where find_in_set(id,:id)',[':id'=>$v['itemid']]);
        $goods[$k]['good_item'] = '';
        foreach($good_item as $kk=>$vv){
            $goods[$k]['good_item'] .= $vv['title'].',';
        }
        $goods[$k]['good_item'] = rtrim($goods[$k]['good_item'],',');
    }
    //包裹屬性
    $good_item = pdo_fetchall('select * from '.tablename('centralize_goods_value').' where 1');
    $good_item = json_encode($good_item,true);

    //包裹類型/产品分类,一级分类
    $cate_item = pdo_fetchall('select * from '.tablename('jd_goods_category').' where status=1 and catLevel=1');
    $cate_item = json_encode($cate_item,true);

    //单位
    $unit = pdo_fetchall('select * from '.tablename('unit').' where 1');

    //查询附加状态
    $attach_info = pdo_fetch('select * from '.tablename('centralize_transport_detail').' where orderid=:oid order by id desc',[':oid'=>$data['orderid']]);

    include $this->template('warehouse/index/show_rejection_box');
}
elseif($op=='sure_rejection'){
    //确认剔除
    $user = pdo_fetch('select a.user_id,a.service_series,a.ordersn,b.openid,a.express_no,c.warehouse_name,a.status,d.program_type from '.tablename('centralize_parcel_order').' a left join '.tablename('centralize_user').' b on a.user_id=b.id left join '.tablename('centralize_warehouse_list').' c on c.id=a.warehouse_id  left join '.tablename('centralize_transport_detail').' d on d.express_no=a.express_no where a.id=:id and a.warehouse_id=:warehouse_id order by d.id desc',[':id'=>$data['orderid'],':warehouse_id'=>$warehouse_id]);
    if($user['program_type'] == 26 ){
        show_json(-1,['msg'=>'该包裹已剔除，请勿重复操作！']);
    }

    if($data['c_status']==1){
        //同意
        //增加分拆服务费用
//        $origin_service = json_decode($user['service_series'],true);
//        $origin_service = array_merge($origin_service,[
//            'service_'.$data['status2']=>sprintf('%.2f',trim($data['service_'.$data['status2']])),
//        ]);
//        pdo_update('centralize_parcel_order',['service_series'=>json_encode($origin_service,true)],['id'=>$data['orderid'],'user_id'=>$user['user_id']]);
        $first='已同意包裹剔除';
        $program_type=26;
        //插入服务费用表
        pdo_insert('centralize_order_fee_log',[
            'type'=>1,//1国内订单，2国外订单
            'user_id'=>$user['user_id'],
            'orderid'=>$data['orderid'],
            'service_status'=>$data['status2'],
            'service_price'=>trim($data['service_price']),
            'paytype'=>'',//支付方式，1余额，2微信hk，3转数快
            'status'=>0,//0未支付，1已支付
            'createtime'=>$time,
            'paytime'=>'',
        ]);
    }elseif($data['c_status']==-1){
        //拒绝
        $first='已拒绝包裹剔除';
        $program_type=25;
    }
//    elseif($data['c_status']==2){
//        //立即剔除
//        $first='已对包裹剔除';
//        $program_type=26;
//    }

    $status = pdo_fetchcolumn('select name from '.tablename('centralize_consolidation_status').' where code=:code',[':code'=>$data['status2']]);
    //微信通知用户，先判断有无openid
    if(!empty($user['openid'])){
        $post2 = json_encode([
            'call'=>'confirmCollectionNotice',
            'first' =>'您好，您的包裹订单['.$user['ordersn'].']'.$first.'，请查看！',
            'keyword1' => '包裹订单['.$user['ordersn'].']',
            'keyword2' => $status,
            'keyword3' => date('Y-m-d H:i:s',$time),
            'remark' => trim($data['remark']),
            'url' => 'https://decl.gogo198.cn/centralize/parcel/index',
            'openid' => $user['openid'],
            'temp_id' => 'SVVs5OeD3FfsGwW0PEfYlZWetjScIT8kDxht5tlI1V8'
        ]);
        ihttp_request('https://shop.gogo198.cn/api/sendwechattemplatenotice.php', $post2);
    }else{
        //发短信...（待做）
    }

    //插入物流信息
    insert_transport_detail([
        'express_no'=>$user['express_no'],
        'orderid'=>$data['orderid'],
        'type'=>1,
        'program_type'=>$program_type,
        'user_id'=>$user['user_id'],
        'opera_id'=>$_SESSION['warehouse_manager']['id'],
        'status'=>intval($data['status2']),
        'remark'=>trim($data['remark']),
        'createtime'=>$time,
    ]);

    show_json(0,['msg'=>'操作成功']);
}
elseif($op=='to_be_merge'){
    $title = '包裹合并';
    $list = pdo_fetchall('select a.*,b.id as user_id,b.name as user_name,b.mobile from '.tablename('centralize_parcel_merge_order').' a left join '.tablename('centralize_user').' b on b.id=a.user_id where a.status2=85 and ( a.status=0 or a.status=1 ) and a.warehouse_id=:wid order by a.id desc',[':wid'=>$warehouse_id]);

    foreach($list as $k=>$v){
        $detail = pdo_fetch('select * from '.tablename('centralize_transport_detail').' where orderid=:oid and `type`=2 order by id desc',[':oid'=>$v['id']]);
        switch($detail['program_type']){
            case 15:
                $detail['merge_status']='包裹合并申请';break;
//            case 16:
//                $detail['merge_status']='包裹合并通过，待买家支付';break;
            case 17:
                $detail['merge_status']='包裹合并拒绝';break;
            case 18:
                $detail['merge_status']='包裹合并完成';break;
        }
        $list[$k]['merge_info'] = $detail;
    }
    include $this->template('warehouse/index/to_be_merge');
}
elseif($op=='show_merge_box'){
    //1、查询合并包裹的商品
    $merge_order = pdo_fetch('select * from '.tablename('centralize_parcel_merge_order').' where id=:oid',[':oid'=>$data['orderid']]);
    $merge_order['parcel_ids'] = explode(',',$merge_order['parcel_ids']);
    $goods = [];
    foreach($merge_order['parcel_ids'] as $kk=>$vv){
        $parcel_order = pdo_fetch('select * from '.tablename('centralize_parcel_order').' where id=:oid',[':oid'=>$vv]);
        $parcel_goods = pdo_fetchall('select * from '.tablename('centralize_parcel_order_goods').' where orderid=:oid',[':oid'=>$vv]);
        foreach($parcel_goods as $k=>$v){
            $cate_item = pdo_fetchall('select * from '.tablename('jd_goods_category').' where find_in_set(id,:id)',[':id'=>$v['cateid']]);
            $parcel_goods[$k]['cate_item'] = '';
            foreach($cate_item as $kk=>$vv){
                $parcel_goods[$k]['cate_item'] .= $vv['catName'].',';
            }
            $parcel_goods[$k]['cate_item'] = rtrim($parcel_goods[$k]['cate_item'],',');

            //属性
            $good_item = pdo_fetchall('select * from '.tablename('centralize_goods_value').' where find_in_set(id,:id)',[':id'=>$v['itemid']]);
            $parcel_goods[$k]['good_item'] = '';
            foreach($good_item as $kk=>$vv){
                $parcel_goods[$k]['good_item'] .= $vv['title'].',';
            }
            $parcel_goods[$k]['good_item'] = rtrim($parcel_goods[$k]['good_item'],',');

            //货架号
            $parcel_goods[$k]['shelf_number'] = $parcel_order['shelf_number'];
        }
        $goods = array_merge($goods,$parcel_goods);
    }

//    if($merge_order['status']==1){
//        $merge_order['service_series'] = json_decode($merge_order['service_series'],true);
//        $service_price = $merge_order['service_series']['service_'.$merge_order['status2']];
//    }
    include $this->template('warehouse/index/show_merge_box');
}
elseif($op=='sure_merge'){
    //确认合并
    $user = pdo_fetch('select a.*,b.openid,d.program_type from '.tablename('centralize_parcel_merge_order').' a left join '.tablename('centralize_user').' b on a.user_id=b.id left join '.tablename('centralize_warehouse_list').' c on c.id=a.warehouse_id left join '.tablename('centralize_transport_detail').' d on d.orderid=a.id where a.id=:oid and a.warehouse_id=:warehouse_id and d.type=2 order by d.id desc',[':oid'=>$data['orderid'],':warehouse_id'=>$warehouse_id]);
    if($user['program_type'] == 18 ){
        show_json(-1,['msg'=>'该包裹已合并，请勿重复操作！']);
    }

    if($data['c_status']==1){
        //同意
        //增加合并服务费用
//        $origin_service = [
//            'service_'.$data['status2']=>sprintf('%.2f',trim($data['service_'.$data['status2']])),
//        ];
//'service_series'=>json_encode($origin_service,true),
        pdo_update('centralize_parcel_merge_order',['status'=>1],['id'=>$data['orderid'],'user_id'=>$user['user_id']]);
        $first='已同意包裹合并';
        $program_type=18;

        $parcel_order = explode(',',$user['parcel_ids']);
        foreach($parcel_order as $k=>$v) {
            pdo_update('centralize_parcel_order', ['status' => 6], ['id' => $v, 'is_merge' => 1]);
        }
        //插入服务费用表
        pdo_insert('centralize_order_fee_log',[
            'type'=>2,//1国内订单，2集运订单
            'user_id'=>$user['user_id'],
            'orderid'=>$data['orderid'],
            'service_status'=>$data['status2'],
            'service_price'=>trim($data['service_price']),
            'paytype'=>'',//支付方式，1余额，2微信hk，3转数快
            'status'=>0,//0未支付，1已支付
            'createtime'=>$time,
            'paytime'=>'',
        ]);
    }elseif($data['c_status']==-1){
        //拒绝
        $first='已拒绝包裹合并';
        $program_type=17;

        $parcel_order = explode(',',$user['parcel_ids']);
        foreach($parcel_order as $k=>$v){
            pdo_update('centralize_parcel_order',['is_merge'=>0],['id'=>$v,'is_merge'=>1]);
            $order = pdo_fetch('select ordersn,id,express_no from '.tablename('centralize_parcel_order').' where id=:id',[':id'=>$v]);
            //插入物流信息
            insert_transport_detail([
                'express_no'=>$order['express_no'],
                'orderid'=>$order['id'],
                'type'=>1,
                'program_type'=>$program_type,
                'user_id'=>$user['user_id'],
                'opera_id'=>$_SESSION['warehouse_manager']['id'],
                'status'=>intval($data['status2']),
                'remark'=>trim($data['remark']),
                'createtime'=>$time,
            ]);
        }
        //删除集运订单信息
        pdo_delete('centralize_parcel_merge_order',['id'=>$data['orderid']]);
    }
//    elseif($data['c_status']==2){
//        //立即合并
//        $first='已对包裹合并';
//        $program_type=18;
//    }

    $status = pdo_fetchcolumn('select name from '.tablename('centralize_consolidation_status').' where code=:code',[':code'=>$data['status2']]);
    //微信通知用户，先判断有无openid
    if(!empty($user['openid'])){
        $post2 = json_encode([
            'call'=>'confirmCollectionNotice',
            'first' =>'您好，您的集运订单['.$user['ordersn'].']'.$first.'，请查看！',
            'keyword1' => '集运订单['.$user['ordersn'].']',
            'keyword2' => $status,
            'keyword3' => date('Y-m-d H:i:s',$time),
            'remark' => trim($data['remark']),
            'url' => 'https://decl.gogo198.cn/centralize/parcel/index',
            'openid' => $user['openid'],
            'temp_id' => 'SVVs5OeD3FfsGwW0PEfYlZWetjScIT8kDxht5tlI1V8'
        ]);
        ihttp_request('https://shop.gogo198.cn/api/sendwechattemplatenotice.php', $post2);
    }else{
        //发短信...（待做）
    }

    //插入物流信息
    insert_transport_detail([
        'express_no'=>$user['ordersn'],
        'orderid'=>$data['orderid'],
        'type'=>2,
        'program_type'=>$program_type,
        'user_id'=>$user['user_id'],
        'opera_id'=>$_SESSION['warehouse_manager']['id'],
        'status'=>intval($data['status2']),
        'remark'=>trim($data['remark']),
        'createtime'=>$time,
    ]);

    show_json(0,['msg'=>'操作成功']);
}
elseif($op=='to_be_exWarehouse'){
    $title = '包裹出库';
    $list = pdo_fetchall('select a.*,b.id as user_id,b.name as user_name,b.mobile from '.tablename('centralize_parcel_merge_order').' a left join '.tablename('centralize_user').' b on b.id=a.user_id where a.status2=85 and a.status>=2 and a.warehouse_id=:wid order by a.id desc',[':wid'=>$warehouse_id]);

    foreach($list as $k=>$v){
//        $detail = pdo_fetch('select * from '.tablename('centralize_transport_detail').' where orderid=:oid and `type`=2 order by id desc',[':oid'=>$v['id']]);
//        switch($detail['program_type']){
        switch($v['status']){
            case 2:
                $list[$k]['order_status'] = '待发货';break;
            case 3:
                $list[$k]['order_status'] = '待收货';break;
            case 4:
                $list[$k]['order_status'] = '已完成';break;
        }

        //取货方式
        switch($v['method_id']){
            case 1:
                $list[$k]['method_name']='配送上门';
                //获取超值线路
                $line = pdo_fetchall('select * from '.tablename('centralize_extra_value_line').' where 1 order by id desc');//name id
                break;
            case 2:
                $list[$k]['method_name']='定点自提';
                //获取定点自提
//                $line = pdo_fetchall('select * from '.tablename('centralize_self_lift_point').' where status=1 order by id desc');//name id
                break;
            case 3:
                $list[$k]['method_name']='仓库自提';
                //填写仓库地址、联系人、联系电话
                break;
        }
    }
    include $this->template('warehouse/index/to_be_exWarehouse');
}
elseif($op=='show_exWarehouse_box'){
    //1、查询合并包裹的商品
    $merge_order = pdo_fetch('select * from '.tablename('centralize_parcel_merge_order').' where id=:oid',[':oid'=>$data['orderid']]);
    $merge_order['parcel_ids'] = explode(',',$merge_order['parcel_ids']);
    $goods = [];
    foreach($merge_order['parcel_ids'] as $kk=>$vv){
        $parcel_order = pdo_fetch('select * from '.tablename('centralize_parcel_order').' where id=:oid',[':oid'=>$vv]);
        $parcel_goods = pdo_fetchall('select * from '.tablename('centralize_parcel_order_goods').' where orderid=:oid',[':oid'=>$vv]);
        foreach($parcel_goods as $k=>$v){
            $cate_item = pdo_fetchall('select * from '.tablename('jd_goods_category').' where find_in_set(id,:id)',[':id'=>$v['cateid']]);
            $parcel_goods[$k]['cate_item'] = '';
            foreach($cate_item as $kk=>$vv){
                $parcel_goods[$k]['cate_item'] .= $vv['catName'].',';
            }
            $parcel_goods[$k]['cate_item'] = rtrim($parcel_goods[$k]['cate_item'],',');

            //属性
            $good_item = pdo_fetchall('select * from '.tablename('centralize_goods_value').' where find_in_set(id,:id)',[':id'=>$v['itemid']]);
            $parcel_goods[$k]['good_item'] = '';
            foreach($good_item as $kk=>$vv){
                $parcel_goods[$k]['good_item'] .= $vv['title'].',';
            }
            $parcel_goods[$k]['good_item'] = rtrim($parcel_goods[$k]['good_item'],',');

            //货架号
            $parcel_goods[$k]['shelf_number'] = $parcel_order['shelf_number'];
        }
        $goods = array_merge($goods,$parcel_goods);
    }

    //2、收货地址
    if($merge_order['method_id']==1){
        $address = pdo_fetch('select * from '.tablename('centralize_user_address').' where id=:id',[':id'=>$merge_order['address_id']]);
    }

    //3、运往国家
    $country = pdo_fetch('select * from '.tablename('country_code').' where code_value=:cid',[':cid'=>$merge_order['country_code']]);

    //4、取货方式
    switch($data['method_id']){
        case 1:
            $method_name='配送上门';
            //获取超值线路
            $line = pdo_fetchall('select * from '.tablename('centralize_extra_value_line').' where 1 order by id desc');//name id
            break;
        case 2:
            $method_name='定点自提';
            //获取定点自提
            $point = pdo_fetchall('select * from '.tablename('centralize_self_lift_point').' where status=1 order by id desc');//name id
            break;
        case 3:
            $method_name='仓库自提';
            //填写仓库地址、联系人、联系电话
            break;
    }

    //5、判断是否已发货
    if($merge_order['status']>=3){
        if($merge_order['logistics_method']==1){
            //传统物流

        }elseif($merge_order['logistics_method']==2){
            //电商物流
            if($merge_order['line_id']>0){
                $line = pdo_fetchcolumn('select `name` from '.tablename('centralize_extra_value_line').' where id=:id',[':id'=>$merge_order['line_id']]);
            }else{
                $line = '无可用线路';
            }
        }
        $merge_order['transport_method'] = pdo_fetchcolumn('select code_name from '.tablename('centralize_logistics_list').' where code_value=:code_value',[':code_value'=>$merge_order['transport_method']]);
        if($merge_order['method_id']==2 || $merge_order['method_id']==3){
            $express_info = json_decode($merge_order['express_info'],true);
        }
    }

    $merge_order['express_name'] = pdo_fetchcolumn('select `name` from '.tablename('customs_express_company_code').' where id=:id',[':id'=>$merge_order['express_id']]);

    $express_company = pdo_fetchall('select * from '.tablename('customs_express_company_code').' where 1');

    //传统物流1和电商物流2
    $logistics_1 = pdo_fetchall('select * from '.tablename('centralize_logistics_list').' where type=1');
    $logistics_2 = pdo_fetchall('select * from '.tablename('centralize_logistics_list').' where type=2');

    include $this->template('warehouse/index/show_exWarehouse_box');
}
elseif($op=='sure_exWarehouse'){
    //1、判断该包裹是否已发货
    $merge_order = pdo_fetch('select a.*,b.openid from '.tablename('centralize_parcel_merge_order').' a left join '.tablename('centralize_user').' b on b.id=a.user_id where a.id=:oid',[':oid'=>$data['orderid']]);
    if($merge_order['status']>=3){
        show_json(-1,['msg'=>'包裹已发货，请勿重复操作！']);
    }

    //2、记录数据表
    $upd_data = ['status'=>3,'line_id'=>$data['line_id'],'express_id'=>$data['express_id'],'sendtime'=>$time,'logistics_method'=>$data['logistics_method']];
    if($upd_data['logistics_method']==1){
        //传统物流
        $upd_data['transport_method'] = $data['transport_method1'];
        $upd_data['express_no'] = $data['express_no1'];
    }elseif($upd_data['logistics_method']==2){
        //电商物流
        $upd_data['transport_method'] = $data['transport_method2'];
        $upd_data['express_no'] = $data['express_no2'];
    }

    if($data['method_id']==2 || $data['method_id']==3){
        $upd_data = array_push($upd_data,[
            'express_info'=>json_encode([
                'pick_point_address'=>trim($data['pick_point_address']),
                'pick_point_contacter'=>trim($data['pick_point_contacter']),
                'pick_point_mobile'=>trim($data['pick_point_mobile']),
            ],true)
        ]);
    }
    $res = pdo_update('centralize_parcel_merge_order',$upd_data,['id'=>$data['orderid']]);

    //3、修改包裹商品表为出库状态
    $parcel_ids = explode(',',$merge_order['parcel_ids']);
    foreach($parcel_ids as $k => $v){
        pdo_update('centralize_parcel_order_goods',['status'=>1],['orderid'=>$v]);
        $all_goods = pdo_fetchall('select id from '.tablename('centralize_parcel_order_goods').' where orderid=:oid',[':oid'=>$v]);
        //3.1、记录商品出库（方便盘点）
        foreach($all_goods as $kk=>$vv){
            pdo_insert('warehouse_inventory_list',[
                'warehouse_id'=>$warehouse_id,
                'user_id'=>$merge_order['user_id'],
                'good_id'=>$vv['id'],
                'orderid'=>$v,
                'status'=>1,
                'createtime'=>$time
            ]);
        }
    }

    if($res){
        //4、通知买家
        //微信通知用户，先判断有无openid
        if(!empty($merge_order['openid'])){
            $post2 = json_encode([
                'call'=>'confirmCollectionNotice',
                'first' =>'您好，您的包裹集运订单['.$merge_order['ordersn'].']已发货，请查看！',
                'keyword1' => '集运订单['.$merge_order['ordersn'].']',
                'keyword2' => '已发货',
                'keyword3' => date('Y-m-d H:i:s',$time),
                'remark' => trim($data['remark']),
                'url' => 'https://decl.gogo198.cn/centralize/parcel/index',
                'openid' => $merge_order['openid'],
                'temp_id' => 'SVVs5OeD3FfsGwW0PEfYlZWetjScIT8kDxht5tlI1V8'
            ]);
            ihttp_request('https://shop.gogo198.cn/api/sendwechattemplatenotice.php', $post2);
        }else{
            //发短信...（待做）
        }

        //插入物流信息
        insert_transport_detail([
            'express_no'=>$merge_order['ordersn'],
            'orderid'=>$merge_order['id'],
            'type'=>2,
            'program_type'=>27,
            'user_id'=>$merge_order['user_id'],
            'opera_id'=>$_SESSION['warehouse_manager']['id'],
            'status'=>intval($data['status2']),
            'remark'=>trim($data['remark']),
            'createtime'=>$time,
        ]);

        show_json(0,['msg'=>'操作成功']);
    }
}
elseif($op=='setting'){
    //仓储设置
    $consolidation_status = pdo_fetchall('select * from '.tablename('centralize_consolidation_status').' where pid=16 and status=1');
    $centralize_status = pdo_fetchall('select * from '.tablename('centralize_consolidation_status').' where ( pid=2 or pid=3 or pid=4 or pid=5 or id=17 or id=18 or pid=19 or pid=20 ) and status=1');
    if($data['ispost']==1){
        $is_have = pdo_fetchcolumn('select id from '.tablename('warehouse_setting').' where warehouse_id=:wid',[':wid'=>$warehouse_id]);
        $content = [];
        foreach($consolidation_status as $k=>$v){
            if($v['code']==81){
                //免费仓储
                $content = array_merge($content,[[
                    'service_'.$v['code']=>$data['service_'.$v['code']]
                ]]);
            }elseif($v['code']==82){
                //计费仓储

            }elseif($v['code']==83){
                //超期仓储
                $content = array_merge($content,[[
                    'service_'.$v['code'][0]=>$data['service_'.$v['code']][0],
                    'service_'.$v['code'][1]=>$data['service_'.$v['code']][1],
                ]]);
            }
        }

        if($is_have>0){
            pdo_update('warehouse_setting',['content'=>json_encode($content,true)],['warehouse_id'=>$warehouse_id]);
        }else{
            pdo_insert('warehouse_setting',['content'=>json_encode($content,true),'warehouse_id'=>$warehouse_id]);
        }

        show_json(0,['msg'=>'操作成功']);
    }else{
        $warehouse_setting = pdo_fetchall('select * from '.tablename('warehouse_setting'));
        $warehouse_setting['content'] = json_decode($warehouse_setting['content'],true);
        $title = '仓储设置';

        include $this->template('warehouse/index/setting');
    }
}