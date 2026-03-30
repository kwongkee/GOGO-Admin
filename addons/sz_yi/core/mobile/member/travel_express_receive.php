<?php

defined('IN_IA') or exit('Access Denied');

global $_W;
global $_GPC;

$operation = (!empty($_GPC['op']) ? $_GPC['op'] : 'display');
$openid = m('user')->getOpenid();
$popenid = m('user')->islogin();
$openid = ($openid ? $openid : $popenid);
$member = m('member')->getMember($openid);

//模糊匹配
function search($a,$keys) {
    $arr=$result=array();
    foreach ($a as $key => $value) {
        foreach ($value as $valu) {
            if(strstr($valu, $keys) !== false)
            {
                array_push($arr, $key);
            }
        }
    }
    foreach ($arr as $key => $value) {
        if(array_key_exists($value,$a)){
            array_push($result, $a[$value]);
        }
    }
    return $result;
}

if ($operation == 'display') {
    
    //先查询是否有地址
    $condition_addr = ' and openid=:openid and deleted=0 and  `uniacid` = :uniacid  ';
    $params_addr = array(':uniacid' => $_W['uniacid'], ':openid' => $openid);
    $sql_addr = 'SELECT COUNT(*) FROM ' . tablename('sz_yi_member_address') . ' where 1 ' . $condition_addr . ' ORDER BY `id` DESC ';
    $addr = pdo_fetchcolumn($sql_addr, $params_addr);
    if($addr==0)
    {
        header('Location: ' . $this->createMobileUrl('member/travel_express_address_add'));
    }

    $condition = ' and pid=0 and is_delete=0 ';
    $params = array();
    $sql = 'SELECT * FROM ' . tablename('customs_travelexpress_cates') . ' where 1 ' . $condition . ' ORDER BY `id` ASC ';
    $cates = pdo_fetchall($sql, $params);

    //拒收物品
    $sql = 'SELECT * FROM ' . tablename('postal_refuse_list') . ' ORDER BY `id` ASC ';
    $refuse_list = pdo_fetchall($sql, $params);
    include $this->template('member/travel_express/receive');
}else if ($operation == 'get_child') {
    $pid = intval($_GPC['pid']);
    $condition = ' and pid=:pid and is_delete=0 ';
    $params = array('pid'=>$pid);
    $sql = 'SELECT * FROM ' . tablename('customs_travelexpress_cates') . ' where 1 ' . $condition . ' ORDER BY `id` ASC ';
    $child = pdo_fetchall($sql, $params);
    show_json(1, array('child' => $child));
}else if($operation == 'get_brand') {
    $cate_id = intval($_GPC['cate_id']);
    $condition = ' and cate_id=:cate_id and is_delete=0 ';
    $params = array('cate_id'=>$cate_id);
    $sql = 'SELECT * FROM ' . tablename('customs_travelexpress_brand') . ' where 1 ' . $condition . ' ORDER BY `id` ASC ';
    $brand_data = pdo_fetchall($sql, $params);
    show_json(1, array('brand_data' => $brand_data));
    
}else if($operation == 'edit') {
    $condition = ' and pid=0 and is_delete=0 ';
    $params = array();
    $sql = 'SELECT * FROM ' . tablename('customs_travelexpress_cates') . ' where 1 ' . $condition . ' ORDER BY `id` ASC ';
    $cates = pdo_fetchall($sql, $params);

    $id = intval($_GPC['id']);
    $data = pdo_fetch('select * from ' . tablename('customs_travelexpress_order') . ' where id=:id ORDER BY `id` ASC limit 1 ', array(':id' => $id));
    $imgfile = explode(',',rtrim($data['imgfile'], ','));

    //拒收物品
    $sql = 'SELECT * FROM ' . tablename('postal_refuse_list') . ' ORDER BY `id` ASC ';
    $refuse_list = pdo_fetchall($sql, $params);
    include $this->template('member/travel_express/receive_edit');
}else if($operation == 'submit' && $_W['ispost']) {
    //查询没有完成的订单
    $old_order = pdo_fetch('select * from ' . tablename('customs_travelexpress_order') . ' where status=0 and uniacid=:uniacid and openid=:openid ORDER BY `id` ASC limit 1 ', array(':uniacid' => $_W['uniacid'],'openid'=>$openid));

    $id = intval($_GPC['id']);
    $data['openid'] = $openid;
    $data['uniacid'] = $_W['uniacid'];
    $data['brand_cn_other'] = $_GPC['brand_cn_other'];
    $data['brand_en_other'] = $_GPC['brand_en_other'];
    $data['good_name'] = $_GPC['good_name'];
    $data['model'] = $_GPC['model'];
    $data['material'] = $_GPC['material'];
    $data['specs'] = $_GPC['specs'];
    $data['specs2'] = $_GPC['specs2'];
    $data['specs3'] = $_GPC['specs3'];
    $data['num'] = $_GPC['num'];
    $data['weight'] = $_GPC['weight'];
    $data['imgfile'] = $_GPC['imgfile'];
    $data['cates'] = $_GPC['cates'];
    $data['cates_c'] = $_GPC['cates_c'];
    $data['brand_cn'] = $_GPC['brand_cn'];
    $data['brand_en'] = $_GPC['brand_en'];
    $data['item_no'] = $_GPC['item_no'];

    //判断是否含有拒收品牌
    $sql = 'SELECT * FROM ' . tablename('postal_refuse_list') . ' ORDER BY `id` ASC ';
    $refuse_list = pdo_fetchall($sql, $params);
    if($data['brand_cn_other']!='' && $data['cates_c'] == 0)
    {
        $num = count( search($refuse_list,$data['brand_cn_other']) );
        if( $num>0 )
        {
            show_json(0, array('msg' => '该品牌不能提交申报！'));
        }
    }

    if($old_order)
    {
        $data['ordersn'] = $old_order['ordersn'];
    }else{

        $billno = date('YmdHis') . random(6, true);
		while (1) {
			$count = pdo_fetchcolumn('select count(*) from ' . tablename('customs_travelexpress_order') . ' where ordersn=:billno limit 1', array(':billno' => $billno));

			if ($count <= 0) {
				break;
			}
			$billno = date('YmdHis') . random(6, true);
        }
        $data['ordersn'] = $billno;
    }

    //查询重量总数量
    $conditions = ' and openid=:openid and ordersn=:ordersn and  `uniacid` = :uniacid  ';
    $paramss = array(':uniacid' => $_W['uniacid'], ':openid' => $openid, ':ordersn'=>$data['ordersn']);
    $sqls = 'SELECT SUM(weight) FROM ' . tablename('customs_travelexpress_order') . ' where 1 ' . $conditions . ' ORDER BY `id` DESC ';
    $weights = pdo_fetchcolumn($sqls, $paramss);
    $weight_total = $data['weight'] + $weights;
    if($weight_total > 13)
    {
        show_json(0, array('msg' => '每批次包裹重量不能超出13KG!'));
    }

    //判断包裹数量
    $condition_n = ' and openid=:openid and ordersn=:ordersn and  `uniacid` = :uniacid  ';
    $params_n = array(':uniacid' => $_W['uniacid'], ':openid' => $openid, ':ordersn'=>$data['ordersn']);
    $sql_n = 'SELECT COUNT(*) FROM ' . tablename('customs_travelexpress_order') . ' where 1 ' . $condition_n . ' ORDER BY `id` DESC ';
    $nums = pdo_fetchcolumn($sql_n, $params_n);
    $nums_total = $nums;
    if($nums_total >= 3)
    {
        show_json(0, array('msg' => '同一批次同一收件人不能超过3个包裹!'));
    }

    if (empty($id)) {
        $data['create_time'] = time();
        pdo_insert('customs_travelexpress_order', $data);
    }else{
        pdo_update('customs_travelexpress_order', $data, array('id' => $id, 'uniacid' => $_W['uniacid']));
    }
    show_json(1, array('msg' => '保存成功'));
    
}


