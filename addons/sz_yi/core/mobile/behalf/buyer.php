<?php
if (!defined('IN_IA')) {
    exit('Access Denied');
}

global $_W;
global $_GPC;
$op = !empty($_GPC['op'])?trim($_GPC['op']):'display';
$openid = $_W['openid'];
$time = TIMESTAMP;
$data = $_GPC;
$info = pdo_fetch('select * from '.tablename('sz_yi_member').' where openid=:openid',[':openid'=>$openid]);
if(empty($info) && ($op != 'display' && $op != 'register' && $op != 'sendcode' )){
    redirect('./index.php?i=3&c=entry&do=behalf&m=sz_yi&p=buyer&op=register');
}

if($op=='display'){
    include $this->template('behalf/buyer/index');
}
elseif($op=='register'){
    $id = intval($data['id']);
    if($_W['ispost']){
        if($_SESSION['regCode'] != trim($data['code'])){
            show_json(-1,['msg'=>'验证码错误']);
        }

        if(empty($data['pic_file'][0]) || empty($data['pic_file2'][0])){
            show_json(-1,['msg'=>'请上传身份证正反面']);
        }

        #身份证号码验证
        $isidcard = is_idcard(trim($data['idcard']));
        if(!$isidcard){
            show_json(-1,['msg'=>'请输入正确的身份证号码']);
        }

        #身份证验证
//        $pass=httpRequest2('https://gather.gogo198.cn/api/idcard_verify',['idcard_z'=>$data['pic_file'][0]]);
        $pass=1;

        if($pass){
            $res = pdo_insert('sz_yi_member',[
                'openid'=>$openid,
                'uniacid'=>$_W['uniacid'],
                'realname'=>trim($data['realname']),
                'nickname'=>trim($data['realname']),
                'mobile'=>trim($data['mobile']),
                'pwd'=>md5('888888'),
                'id_card'=>trim($data['idcard']),
                'idcard_z'=>$data['pic_file'][0],
                'idcard_f'=>$data['pic_file2'][0],
                'createtime'=>$time
            ]);
            if($res){
                pdo_insert('sz_yi_member_address',[
                    'openid'=>$openid,
                    'realname'=>trim($data['realname']),
                    'mobile'=>trim($data['mobile']),
                    'address'=>trim($data['address']),
                ]);
                show_json(0,['msg'=>'注册成功']);
            }
        }
    }else{
//        $country = pdo_fetchall('select * from '.tablename('country_code').' where code_name!="无"');
        if(!empty($info)) {
            redirect('./index.php?i=3&c=entry&do=behalf&m=sz_yi&p=buyer&op=manage');
        }
        include $this->template('behalf/buyer/register');
    }
}
elseif($op=='sendcode'){
    $mobile = trim($data['mobile']);
    $ishave = pdo_fetch('select id from '.tablename('sz_yi_member').' where mobile=:mobile',[':mobile'=>$mobile]);
    if($ishave['id']){
        show_json(-1,['msg'=>'该手机号已注册']);
    }
    $code = mt_rand(11, 99) . mt_rand(11, 99) . mt_rand(11, 99);
//    $config = [
//        'SingnName' => 'Gogo购购网',
//        'parm' => [
//            'name' => '跨境代购',
//            'code' => $code,
//        ],
//        'tel' => $mobile,
//        'TemplateCode' => 'SMS_109370056'
//    ];
    $set = m('common')->getSysset();
    $this->sendSms($mobile, $code,'SMS_35030089');
//    $this->sendSms($mobile, $code,'SMS_109370056');//sz_yi/core/inc/functions.php
    $_SESSION["regCode"] = $code;
    show_json(0,['msg'=>'已发送']);
}
elseif($op=='manage'){
    $id = intval($data['id']);
    if($_W['ispost']){
        if($info['mobile'] != trim($data['mobile'])){
            if(empty($_SESSION['regCode'])){
                show_json(-1,['msg'=>'请先获取手机验证码']);   
            }
            if($_SESSION['regCode'] != trim($data['code'])){
                show_json(-1,['msg'=>'验证码错误']);
            } 
        }
        

        if(empty($data['pic_file'][0]) || empty($data['pic_file2'][0])){
            show_json(-1,['msg'=>'请上传身份证正反面']);
        }

        #身份证号码验证
        $isidcard = is_idcard(trim($data['idcard']));
        if(!$isidcard){
            show_json(-1,['msg'=>'请输入正确的身份证号码']);
        }

        #身份证验证
//        $pass=httpRequest2('https://gather.gogo198.cn/api/idcard_verify',['idcard_z'=>$data['pic_file'][0]]);
        $pass=1;

        if($pass){
            $res = pdo_update('sz_yi_member',[
                'realname'=>trim($data['realname']),
                'nickname'=>trim($data['realname']),
                'mobile'=>trim($data['mobile']),
                'id_card'=>trim($data['idcard']),
                'idcard_z'=>$data['pic_file'][0],
                'idcard_f'=>$data['pic_file2'][0],
            ],['id'=>$id]);
            if($res){
                pdo_update('sz_yi_member_address',[
                    'realname'=>trim($data['realname']),
                    'mobile'=>trim($data['mobile']),
                    'address'=>trim($data['address']),
                ],['openid'=>$openid]);
                show_json(0,['msg'=>'修改成功']);
            }
        }
    }else{
        $id = $info['id'];
        $info['address'] = pdo_fetchcolumn('select address from'.tablename('sz_yi_member_address').' where openid=:openid order by id desc',[':openid'=>$openid]);
//        $country = pdo_fetchall('select * from '.tablename('country_code').' where code_name!="无"');
        include $this->template('behalf/buyer/manage');
    }
}
elseif($op=='buy_goods'){
    $this->setHeader();

    include $this->template('behalf/buyer/buy_goods');
}
elseif($op=='get_goods'){
    $size = 6;
    $page = (intval($data['page']) - 1)*$size;
    $limit = ' limit '.$page.','.$size;
    $goods = pdo_fetchall('select * from '.tablename('behalf_goods').' where status=0 order by id desc'.$limit);
    show_json(1, array('goods' => $goods, 'pagesize' => $size));
}
elseif($op=='search_goods'){
    $keywords = trim($data['keywords']);
    $goods = pdo_fetchall('select * from '.tablename('behalf_goods').' where status=0 and `name` like "%'.$keywords.'%" order by id desc');
    show_json(1, array('list' => $goods));
}
elseif($op=='detail'){
    $goodsid = intval($data['id']);
    if(isset($data['pa'])){
        $goods = pdo_fetch('select * from '.tablename('behalf_goods').' where id=:id',[':id'=>$goodsid]);
        $goods['detail_img'] = json_decode($goods['detail_img'],true);
        #轮播图
        $pics = array_merge([$goods['main_img']],$goods['detail_img']);
        #参数
        $unit = pdo_fetchcolumn('select code_name from '.tablename('unit').' where code_value=:code_value',[':code_value'=>$goods['unit']]);
        $params = [
            ['title'=>'货品名称','value'=>$goods['name']],
            ['title'=>'规格型号','value'=>$goods['option']],
            ['title'=>'货品单位','value'=>$unit],
            ['title'=>'货品售价','value'=>$goods['price']],
        ];
        $shop = set_medias(m('common')->getSysset('shop'), 'logo');
        $shop['url'] = $this->createMobileUrl('behalf/buyer');
        $shopset = m('common')->getSysset('shop');
        $ret = array('is_admin' => $_GPC['is_admin'], 'goods' => $goods, 'shopset' => $shopset, 'pics' => $pics,'options' =>'','shop' => $shop,'params' => $params);
        $ret['detail'] = array('logo' => !empty($goods['detail_logo']) ? tomedia($goods['detail_logo']) : $shop['logo'], 'shopname' => !empty($goods['detail_shopname']) ? $goods['detail_shopname'] : $shop['name'], 'totaltitle' => trim($goods['detail_totaltitle']), 'btntext1' => trim($goods['detail_btntext1']), 'btnurl1' => !empty($goods['detail_btnurl1']) ? $goods['detail_btnurl1'] : $this->createMobileUrl('shop/list'), 'btntext2' => trim($goods['detail_btntext2']), 'btnurl2' => !empty($goods['detail_btnurl2']) ? $goods['detail_btnurl2'] : $shop['url']);
        show_json(1, $ret);
    }
    include $this->template('behalf/buyer/goods_detail');
}
elseif($op=='check_buy'){
    $id = intval($data['id']);
    #查看以前是否买过此产品(根据条件：身份证号、姓名、手机号、收货地址、货品id)
    $adr = pdo_fetch('select address from '.tablename('sz_yi_member_address').' where openid=:openid',[':openid'=>$openid]);
    $ishave = pdo_fetch('select a.id from '.tablename('behalf_order').' a left join '.tablename('sz_yi_member').' b on b.id=a.user_id left join '.tablename('sz_yi_member_address').' c2 on c2.openid=b.openid where a.good_id=:goodid and ( b.id_card=:idcard or b.realname=:realname or b.mobile=:mobile or c2.address=:address )',[':goodid'=>$id,':idcard'=>$info['id_card'],':realname'=>$info['realname'],':mobile'=>$info['mobile'],':address'=>$adr['address']]);
    if(empty($ishave)){
        show_json(1);
    }else{
        show_json(2);
    }
}
elseif($op=='sure_buy'){
    $id = intval($data['id']);
    $goods = pdo_fetch('select * from '.tablename('behalf_goods').' where id=:id and status=0',[':id'=>$id]);
    if(empty($goods)){
        show_json(-1,['msg'=>'很抱歉，货品已下架！']);
    }

    $totalprice = sprintf('%.2f',intval($data['total_num']) * $goods['price']);
    $res = pdo_insert('behalf_order',[
        'openid'=>$openid,
        'ordersn'=>m('common')->createNO('order', 'ordersn', 'DG'),
        'paysn'=>date('YmdHis').rand(11,99).rand(11,99).rand(11,99),
        'user_id'=>$info['id'],
        'merch_id'=>$goods['user_id'],
        'good_id'=>$id,
        'buy_num'=>intval($data['total_num']),
        'good_price'=>$goods['price'],
        'totalprice'=>$totalprice,
        'is_same'=>intval($data['is_same']),
        'createtime'=>$time
    ]);
    if($res){
        show_json(0,['msg'=>'下单成功，是否前往订单管理页？']);
    }
}
elseif($op=='order_manage'){
    if(isset($data['pa'])){
        $limit = intval($data['limit']);
        $page = (intval($data['page'])-1) * $limit;
        $offset = ' limit '.$page.','.$limit;
        $count = pdo_fetchcolumn('select count(id) from '.tablename('behalf_order').' where user_id=:user_id and status=0',[':user_id'=>$info['id']]);
        $list = pdo_fetchall('select * from '.tablename('behalf_order').' where user_id=:user_id order by id desc '.$offset,[':user_id'=>$info['id']]);
        $status = [0=>'未确认',1=>'已确认'];
        foreach($list as $k=>$v){
            $list[$k]['status_name'] = $status[$v['status']];
            $list[$k]['createtime'] = date('Y-m-d H:i',$v['createtime']);
        }
        die(json_encode(['code'=>0,'data'=>$list,'count'=>$count]));
    }
    include $this->template('behalf/buyer/order_manage');
}
elseif($op=='edit_order'){
    $id = intval($data['id']);
    if($_W['ispost']){
        $totalnum = intval($data['totalnum']);
        $order = pdo_fetch('select * from '.tablename('behalf_order').' where user_id=:user_id and id=:id',[':user_id'=>$info['id'],':id'=>$id]);
        $goods = pdo_fetch('select * from '.tablename('behalf_goods').' where id=:gid',[':gid'=>$order['good_id']]);
        $price = sprintf('%.2f',$goods['price'] * $totalnum);

        $res = pdo_update('behalf_order',['totalprice'=>$price,'buy_num'=>$totalnum],['user_id'=>$info['id'],'id'=>$id]);
        if($res){
            show_json(0,['msg'=>'修改成功']);
        }
    }else{
        $order = pdo_fetch('select * from '.tablename('behalf_order').' where user_id=:user_id and id=:id',[':user_id'=>$info['id'],':id'=>$id]);
        $goods = pdo_fetch('select * from '.tablename('behalf_goods').' where id=:gid',[':gid'=>$order['good_id']]);
        $unit = pdo_fetchcolumn('select * from '.tablename('unit').' where code_value=:code_value',[':code_value'=>$goods['unit']]);
        $address = pdo_fetch('select * from '.tablename('sz_yi_member_address').' where openid=:openid',[':openid'=>$openid]);
        include $this->template('behalf/buyer/edit_order');
    }
}