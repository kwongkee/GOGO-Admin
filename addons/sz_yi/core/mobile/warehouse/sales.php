<?php
/**
 * 仓储系统-销售管理
 * User: Administrator
 * Date: 2022/8/16
 * Time: 14:27
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
$time = TIMESTAMP;
$warehouse_id = $_SESSION['warehouse_manager']['warehouse_id'];//仓库id
$data = $_GPC;

if($op=='display'){
    $menu_index=2;

    include $this->template('warehouse/sales/index');
}
elseif($op=='goods'){
    $title='产品管理';
    if($data['pa']==1){
        $limit = intval($_GPC['limit']);
        $page = intval($_GPC['page']) - 1;
        if ($page != 0) {
            $page = $limit * $page;
        }
        $keyword = trim($_GPC['keywords']) ? trim($_GPC['keywords']) : '';
        $condition = '';
        if($keyword){
            $condition = 'and a.name like "%'.$keyword.'%"';
        }
        $list = pdo_fetchall('select a.*,b.name as username from '.tablename('centralize_parcel_order_goods').' a left join '.tablename('centralize_user').' b on b.id=a.user_id left join '.tablename('centralize_parcel_order').' c on c.id=a.orderid where c.warehouse_id=:warehouse_id '.$condition.' and a.status=0 order by a.id desc limit '.$page.",".$limit,[':warehouse_id'=>$warehouse_id]);
        foreach($list as $k=>$v){
            $list[$k]['unit'] = pdo_fetchcolumn('select code_name from '.tablename('unit').' where code_value=:code_value',[':code_value'=>$v['unit']]);
            $list[$k]['createtime'] = date('Y-m-d H:i',$v['createtime']);
        }
        $count = pdo_fetch('select count(id) as c from '.tablename('centralize_parcel_order_goods').' where 1 and status=0');
        die(json_encode(['code'=>0,'count'=>$count['c'],'data'=>$list]));
    }else{
        include $this->template('warehouse/sales/goods');
    }
}
elseif($op=='goods_detail'){
    if($data['pa']==1){
        //修改
        $res = pdo_update('centralize_parcel_order_goods',[
            'name'=>trim($data['name']),
            'money'=>trim($data['money']),
            'brand'=>trim($data['brand']),
//            'cate_item'=>trim($data['cate_item']),
//            'good_item'=>trim($data['good_item']),
            'num'=>trim($data['num']),
            'unit'=>trim($data['unit_name']),
            'netwt'=>trim($data['netwt']),
            'grosswt'=>trim($data['grosswt']),
            'true_volumn'=>trim($data['true_volumn']),
        ],['id'=>$data['gid']]);
        if($res){
            show_json(0,['msg'=>'修改成功']);
        }
    }else{
        $goods = pdo_fetch('select a.*,b.code_name as unit_name,c.shelf_number,c.ordersn,c.express_no from '.tablename('centralize_parcel_order_goods').' a left join '.tablename('unit').' b on b.code_value=a.unit left join '.tablename('centralize_parcel_order').' c on c.id=a.orderid where a.id=:gid',[':gid'=>$data['id']]);
        //类型
        $cate_item = pdo_fetchall('select * from '.tablename('jd_goods_category').' where find_in_set(id,:id)',[':id'=>$goods['cateid']]);
        $goods['cate_item'] = '';
        foreach($cate_item as $kk=>$vv){
            $goods['cate_item'] .= $vv['catName'].',';
        }
        $goods['cate_item'] = rtrim($goods['cate_item'],',');

        //属性
        $good_item = pdo_fetchall('select * from '.tablename('centralize_goods_value').' where find_in_set(id,:id)',[':id'=>$goods['itemid']]);
        $goods['good_item'] = '';
        foreach($good_item as $kk=>$vv){
            $goods['good_item'] .= $vv['title'].',';
        }
        $goods['good_item'] = rtrim($goods['good_item'],',');

        //单位
        $unit = pdo_fetchall('select * from '.tablename('unit').' where 1');
        include $this->template('warehouse/sales/goods_detail');
    }
}
elseif($op=='order'){
    $title='订单管理';
    $limit = intval($_GPC['limit']);
    $page = intval($_GPC['page']) - 1;
    if ($page != 0) {
        $page = $limit * $page;
    }
    $keyword = trim($_GPC['keywords']) ? trim($_GPC['keywords']) : '';
    $condition = '';
    if($keyword){
        $condition = 'and a.ordersn like "%'.$keyword.'%"';
    }
    if($data['pa']==1){
        //原始包裹单号
        $list = pdo_fetchall('select a.*,b.name as nickname from '.tablename('centralize_parcel_order').'a left join '.tablename('centralize_user').' b on b.id=a.user_id where a.status!=6 and warehouse_id=:warehouse_id '.$condition.' order by a.id desc limit '.$page.",".$limit,[':warehouse_id'=>$warehouse_id]);
        foreach($list as $k=>$v){
            $list[$k]['createtime'] = date('Y-m-d H:i',$v['createtime']);
        }
        $count = pdo_fetch('select count(id) as c from '.tablename('centralize_parcel_order').' where status!=6');
        die(json_encode(['code'=>0,'count'=>$count['c'],'data'=>$list]));
    }
    elseif($data['pa']==2){
        //合并包裹单号
        $list = pdo_fetchall('select a.*,b.name as nickname from '.tablename('centralize_parcel_merge_order').'a left join '.tablename('centralize_user').' b on b.id=a.user_id where warehouse_id=:warehouse_id '.$condition.' order by a.id desc limit '.$page.",".$limit,[':warehouse_id'=>$warehouse_id]);
        foreach($list as $k=>$v){
            $list[$k]['createtime'] = date('Y-m-d H:i',$v['createtime']);
        }
        $count = pdo_fetch('select count(id) as c from '.tablename('centralize_parcel_merge_order').' where 1');
        die(json_encode(['code'=>0,'count'=>$count['c'],'data'=>$list]));
    }
    elseif($data['pa']==3){
        //修改
        $order = pdo_fetch('select a.*,b.openid from '.tablename('centralize_parcel_order').' a left join '.tablename('centralize_user').' b on b.id=a.user_id where a.id=:id',[':id'=>$data['orderid']]);

        $upd_data = [
            'shelf_number'=>trim($data['shelf_number']),
        ];

        if(!empty($data['status2'])){
            $upd_data = array_merge($upd_data,[
                'status2'=>$data['status2'],
                'ordersn'=>substr_replace($order['ordersn'],$data['status2'],-2,2),
            ]);

            //插入物流信息
            insert_transport_detail([
                'express_no'=>$order['express_no'],
                'orderid'=>$order['id'],
                'type'=>1,
                'program_type'=>-3,
                'user_id'=>$order['user_id'],
                'opera_id'=>$_SESSION['warehouse_manager']['id'],
                'status'=>$data['status2'],
                'remark'=>trim($data['remark']),
                'createtime'=>$time,
            ]);
            $damage_title = pdo_fetchcolumn('select name from '.tablename('centralize_consolidation_status').' where code=:code',[':code'=>$data['status2']]);
            if(!empty($user['openid'])){
                $post2 = json_encode([
                    'call'=>'confirmCollectionNotice',
                    'first' =>'您好，您的快递包裹['.$order['express_no'].']'.'遭到'.$damage_title.'，请查看！',
                    'keyword1' => '快递包裹['.$order['express_no'].']',
                    'keyword2' => $damage_title,
                    'keyword3' => date('Y-m-d H:i:s',$time),
                    'remark' => trim($data['remark']),
                    'url' => 'https://decl.gogo198.cn/centralize/parcel/index',
                    'openid' => $order['openid'],
                    'temp_id' => 'SVVs5OeD3FfsGwW0PEfYlZWetjScIT8kDxht5tlI1V8'
                ]);
                ihttp_request('https://shop.gogo198.cn/api/sendwechattemplatenotice.php', $post2);
            }else{
                //发短信...（待做）
            }
        }

        $res = pdo_update('centralize_parcel_order',$upd_data,['id'=>$data['orderid']]);
        if($res){
            show_json(1,['msg'=>'操作成功']);
        }
    }

    include $this->template('warehouse/sales/order');
}
elseif($op=='order_detail'){
    //查找原始订单商品信息
    $order = pdo_fetch('select a.*,b.name as username,b.mobile from '.tablename('centralize_parcel_order').' a left join '.tablename('centralize_user').' b on b.id=a.user_id where a.id=:oid and a.warehouse_id=:warehouse_id',[':oid'=>$data['id'],':warehouse_id'=>$warehouse_id]);

    $goods = pdo_fetchall('select * from '.tablename('centralize_parcel_order_goods').' where orderid=:oid',[':oid'=>$data['id']]);
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

    switch($order['status']){
        case 0:
            $order['status_name']='已预报';break;
        case 1:
            $order['status_name']='已收货';break;
        case 2:
            $order['status_name']='已查验';break;
        case 3:
            $order['status_name']='已入库';break;
        case 4:
            $order['status_name']='已退运';break;
        case 5:
            $order['status_name']='已弃货';break;
        case 6:
            $order['status_name']='已合并';break;
    }

    //查询订单流程状态
    $order['program_typeName'] = pdo_fetchcolumn('select program_type from '.tablename('centralize_transport_detail').' where orderid=:oid and `type`=1 order by id desc',[':oid'=>$data['id']]);
    switch($order['program_typeName']){
        case -3:
            $order['program_typeName'] = '包裹已损毁';break;
        case -2:
            $order['program_typeName'] = '包裹已认领';break;
        case -1:
            $order['program_typeName'] = '仓库已收货，待认领';break;
        case 1:
            $order['program_typeName'] = '包裹预报登记';break;
        case 2:
            $order['program_typeName'] = '包裹已收货';break;
        case 3:
            $order['program_typeName'] = '包裹验货申请中';break;
        case 4:
            $order['program_typeName'] = '包裹验货完成';break;
        case 5:
            $order['program_typeName'] = '包裹入库申请';break;
        case 6:
            $order['program_typeName'] = '包裹入库完成';break;
        case 7:
            $order['program_typeName'] = '包裹退运申请';break;
        case 8:
            $order['program_typeName'] = '包裹退运完成';break;
        case 9:
            $order['program_typeName'] = '包裹弃货申请';break;
        case 10:
            $order['program_typeName'] = '包裹弃货完成';break;
        case 16:
            $order['program_typeName'] = '包裹合并通过，待支付';break;
        case 19:
            $order['program_typeName'] = '包裹附加申请';break;
        case 20:
            $order['program_typeName'] = '包裹附加通过';break;
        case 21:
            $order['program_typeName'] = '包裹附加拒绝';break;
        case 22:
            $order['program_typeName'] = '包裹附加完成';break;
        case 23:
            $order['program_typeName'] = '包裹剔除申请';break;
        case 24:
            $order['program_typeName'] = '包裹剔除通过';break;
        case 25:
            $order['program_typeName'] = '包裹剔除拒绝';break;
        case 26:
            $order['program_typeName'] = '包裹剔除完成';break;
        case 27:
            $order['program_typeName'] = '包裹已发货';break;
    }

    //获取损毁状态
    $consolidation_status = pdo_fetchall('select * from '.tablename('centralize_consolidation_status').' where pid=21 and status=1');

    include $this->template('warehouse/sales/order_detail');
}
elseif($op=='merge_order_detail'){
    if($data['pa']==1){
        //损毁
        $order = pdo_fetch('select a.*,b.openid from '.tablename('centralize_parcel_merge_order').' a left join '.tablename('centralize_user').' b on b.id=a.user_id where a.id=:id',[':id'=>$data['orderid']]);
        if(!empty($data['status2'])){
            $order['ordersn'] = substr_replace($order['ordersn'],$data['status2'],-2,2);
            print_r($order);die;
            //插入物流信息
            insert_transport_detail([
                'express_no'=>$order['ordersn'],
                'orderid'=>$order['id'],
                'type'=>2,
                'program_type'=>-3,
                'user_id'=>$order['user_id'],
                'opera_id'=>$_SESSION['warehouse_manager']['id'],
                'status'=>$data['status2'],
                'remark'=>trim($data['remark']),
                'createtime'=>$time,
            ]);
            $damage_title = pdo_fetchcolumn('select name from '.tablename('centralize_consolidation_status').' where code=:code',[':code'=>$data['status2']]);
            if(!empty($user['openid'])){
                $post2 = json_encode([
                    'call'=>'confirmCollectionNotice',
                    'first' =>'您好，您的合并包裹['.$order['ordersn'].']'.'遭到'.$damage_title.'，请查看！',
                    'keyword1' => '合并包裹['.$order['ordersn'].']',
                    'keyword2' => $damage_title,
                    'keyword3' => date('Y-m-d H:i:s',$time),
                    'remark' => trim($data['remark']),
                    'url' => 'https://decl.gogo198.cn/centralize/parcel/index',
                    'openid' => $order['openid'],
                    'temp_id' => 'SVVs5OeD3FfsGwW0PEfYlZWetjScIT8kDxht5tlI1V8'
                ]);
                ihttp_request('https://shop.gogo198.cn/api/sendwechattemplatenotice.php', $post2);
            }else{
                //发短信...（待做）
            }
            $res = pdo_update('centralize_parcel_merge_order',[
                'ordersn'=>$order['ordersn'],
                'status'=>-2,
                'status2'=>$data['status2'],
            ],['id'=>$data['orderid']]);
            if($res){
                show_json(1,['msg'=>'操作成功']);
            }
        }
        show_json(1,['msg'=>'操作失败，无修改']);
    }else{
        //查找合并订单商品信息
        $merge_order = pdo_fetch('select a.*,b.name as username,b.mobile from '.tablename('centralize_parcel_merge_order').' a left join '.tablename('centralize_user').' b on b.id=a.user_id where a.id=:oid and a.warehouse_id=:warehouse_id',[':oid'=>$data['id'],':warehouse_id'=>$warehouse_id]);
        $merge_order['parcel_ids'] = explode(',',$merge_order['parcel_ids']);
        $merge_order['address_info'] = pdo_fetch('select a.user_name,a.mobile,a.address,b.code_name as country_name from '.tablename('centralize_user_address').' a left join '.tablename('country_code').' b on b.code_value=a.country_id where a.id=:id',[':id'=>$merge_order['address_id']]);
        $merge_order['country_code'] = pdo_fetchcolumn('select code_name from '.tablename('country_code').' where code_value=:code_value',[':code_value'=>$merge_order['country_code']]);

        //取货方式
        switch($merge_order['method_id']){
            case 1:
                $merge_order['method_name'] = '配送上门';break;
            case 2:
                $merge_order['method_name'] = '定点自提';break;
            case 3:
                $merge_order['method_name'] = '仓库自提';break;
        }

        //订单产品
        foreach($merge_order['parcel_ids'] as $kk=>$vv){
            $parcel_order = pdo_fetch('select * from '.tablename('centralize_parcel_order').' where id=:oid',[':oid'=>$vv]);
            $parcel_goods = pdo_fetchall('select * from '.tablename('centralize_parcel_order_goods').' where orderid=:oid',[':oid'=>$vv]);
            $goods = [];
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

        //订单状态
        switch($merge_order['status']){
            case 0:
                $merge_order['status_name']='合并待审核';break;
            case 1:
                $merge_order['status_name']='待付款';break;
            case 2:
                $merge_order['status_name']='待发货';break;
            case 3:
                $merge_order['status_name']='待收货';break;
            case 4:
                $merge_order['status_name']='已完成';break;
        }

        //订单流程状态
        $merge_order['program_typeName'] = pdo_fetchcolumn('select program_type from '.tablename('centralize_transport_detail').' where orderid=:oid and `type`=2 order by id desc',[':oid'=>$data['id']]);
        switch($merge_order['program_typeName']){
            case -2:
                $merge_order['program_typeName'] = '包裹待申请验货';break;
            case 2:
                $merge_order['program_typeName'] = '已收货';break;
            case 3:
                $merge_order['program_typeName'] = '包裹验货申请中';break;
            case 4:
                $merge_order['program_typeName'] = '包裹验货完成';break;
            case 5:
                $merge_order['program_typeName'] = '包裹入库申请';break;
            case 6:
                $merge_order['program_typeName'] = '包裹入库完成';break;
            case 7:
                $merge_order['program_typeName'] = '包裹退运申请';break;
            case 8:
                $merge_order['program_typeName'] = '包裹退运完成';break;
            case 9:
                $merge_order['program_typeName'] = '包裹弃货申请';break;
            case 10:
                $merge_order['program_typeName'] = '包裹弃货完成';break;
            case 16:
                $merge_order['program_typeName'] = '包裹合并通过，待支付';break;
            case 19:
                $merge_order['program_typeName'] = '包裹附加申请';break;
            case 20:
                $merge_order['program_typeName'] = '包裹附加通过';break;
            case 21:
                $merge_order['program_typeName'] = '包裹附加拒绝';break;
            case 22:
                $merge_order['program_typeName'] = '包裹附加完成';break;
            case 23:
                $merge_order['program_typeName'] = '包裹剔除申请';break;
            case 24:
                $merge_order['program_typeName'] = '包裹剔除通过';break;
            case 25:
                $merge_order['program_typeName'] = '包裹剔除拒绝';break;
            case 26:
                $merge_order['program_typeName'] = '包裹剔除完成';break;
            case 27:
                $merge_order['program_typeName'] = '包裹已发货';break;
        }
        //获取损毁状态
        $consolidation_status = pdo_fetchall('select * from '.tablename('centralize_consolidation_status').' where pid=21 and status=1');

        include $this->template('warehouse/sales/merge_order_detail');
    }
}
elseif($op=='customer'){
    $title='客户管理';

    if($data['pa']==1){
        $limit = intval($_GPC['limit']);
        $page = intval($_GPC['page']) - 1;
        if ($page != 0) {
            $page = $limit * $page;
        }
        $keyword = trim($_GPC['keywords']) ? trim($_GPC['keywords']) : '';
        $condition = '';
        if($keyword){
            $condition = 'and name like "%'.$keyword.'%"';
        }
        $list = pdo_fetchall('select * from '.tablename('centralize_user').' where 1 '.$condition.' order by id desc limit '.$page.",".$limit);
        foreach($list as $k=>$v){
            $list[$k]['createtime'] = date('Y-m-d H:i',$v['createtime']);
        }
        $count = pdo_fetch('select count(id) as c from '.tablename('centralize_user').' where 1');
        die(json_encode(['code'=>0,'count'=>$count['c'],'data'=>$list]));
    }else{
        include $this->template('warehouse/sales/customer');
    }
}
elseif($op=='customer_detail'){
    $user = pdo_fetch('select * from '.tablename('centralize_user').' where id=:id',[':id'=>$data['id']]);
    include $this->template('warehouse/sales/customer_detail');
}
elseif($op=='settlement'){
    $title='结算管理';
    if($data['pa']==1){
        //获取支付项目
        $pay_project = [
            ['ordersn'=>'123456','name'=>'新','money'=>'100.00','paytype'=>'余额支付','payitem'=>'包裹入库','createtime'=>'2022-08-16 17:55'],
            ['ordersn'=>'1234567777777','name'=>'新','money'=>'300.00','paytype'=>'余额支付','payitem'=>'包裹出库','createtime'=>'2022-08-16 17:54'],
        ];
        die(json_encode(['code'=>0,'count'=>2,'data'=>$pay_project]));
    }

    include $this->template('warehouse/sales/settlement');
}
elseif($op=='export_goods_excel'){
    //导出货物

    //查找包裹合并订单
    $merge_order = pdo_fetch('select * from '.tablename('centralize_parcel_merge_order').' where id=:id',[':id'=>$data['id']]);

    //查找货物
    $merge_order['parcel_ids'] = explode(',',$merge_order['parcel_ids']);
    $goods = [];
    foreach($merge_order['parcel_ids'] as $k=>$v){
        $order_goods = pdo_fetchall('select * from '.tablename('centralize_parcel_order_goods').' where orderid=:oid',[':oid'=>$v]);
        $goods = array_merge($goods,$order_goods);
    }

    foreach($goods as $k =>$v){
        $goods[$k]['express_no'] = $merge_order['express_no'];
    }


    $columns = array(
        array('title' => '企业商品货号', 'field' => 'id', 'width' => 8),
        array('title' => '商品名称', 'field' => 'name', 'width' => 12),
        array('title' => '商品编码', 'field' => 'empty', 'width' => 8),
        array('title' => '币制', 'field' => 'empty', 'width' => 8),
        array('title' => '申报数量', 'field' => 'num', 'width' => 12),
        array('title' => '法定数量', 'field' => 'empty', 'width' => 12),
        array('title' => '规格型号', 'field' => 'empty', 'width' => 12),
        array('title' => 'FOB单价', 'field' => 'empty', 'width' => 12),
        array('title' => 'FOB总价', 'field' => 'empty', 'width' => 12),
        array('title' => '收款金额', 'field' => 'empty', 'width' => 12),
        array('title' => '到账时间', 'field' => 'empty', 'width' => 12),
        array('title' => '物流运单编号', 'field' => 'express_no', 'width' => 18),
        array('title' => '运费', 'field' => 'empty', 'width' => 12),
        array('title' => '保价费', 'field' => 'empty', 'width' => 12),
        array('title' => '条形码', 'field' => 'empty', 'width' => 12),
        array('title' => '毛重', 'field' => 'grosswt', 'width' => 12),
        array('title' => '净重', 'field' => 'netwt', 'width' => 12),
        array('title' => '件数', 'field' => 'num', 'width' => 12),
        array('title' => '主要货物信息', 'field' => 'empty', 'width' => 12),
        array('title' => '申报计量单位', 'field' => 'unit', 'width' => 12),
        array('title' => '法定计量单位', 'field' => 'empty', 'width' => 12),
    );

    $res = m('excel')->export($goods, array('title' => '订单数据-', 'columns' => $columns));
    if($res){
        show_json(0,['msg'=>'导出成功']);
    }
}
elseif($op=='inventory'){
    $title='盘点管理';
    //xx时间入库/出库，xx订单号，xx货物，xx数量，
    $limit = intval($_GPC['limit']);
    $page = intval($_GPC['page']) - 1;
    if ($page != 0) {
        $page = $limit * $page;
    }
    $keyword = trim($_GPC['keywords']) ? trim($_GPC['keywords']) : '';
    $condition = '';
    if($keyword){
        $condition = 'and a.ordersn like "%'.$keyword.'%"';
    }
    if($data['pa']==1){
        //查询该仓库订单的货物信息
        $list = pdo_fetchall('select a.*,b.name as user_name,c.name as good_name,c.num,c.unit,d.ordersn from '.tablename('warehouse_inventory_list').' a left join '.tablename('centralize_user').' b on b.id=a.user_id left join '.tablename('centralize_parcel_order_goods').' c on c.id=a.good_id left join '.tablename('centralize_parcel_order').' d on d.id=a.orderid where a.warehouse_id=:wid order by a.id desc limit '.$page.",".$limit,[':wid'=>$warehouse_id]);
        foreach($list as $k=>$v){
            $list[$k]['createtime'] = date('Y-m-d H:i',$v['createtime']);
            if(empty($v['status'])){
                $list[$k]['status']='入库';
            }elseif($v['status']==1){
                $list[$k]['status']='出库';
            }
            $unit = pdo_fetchcolumn('select code_name from '.tablename('unit').' where code_value=:code_value',[':code_value'=>$v['unit']]);
            $list[$k]['num'] .= ' '.$unit;
        }
        $count = pdo_fetch('select count(id) as c from '.tablename('warehouse_inventory_list').' where warehouse_id=:wid',[':wid'=>$warehouse_id]);
        die(json_encode(['code'=>0,'count'=>$count['c'],'data'=>$list]));
    }
    include $this->template('warehouse/sales/inventory');
}