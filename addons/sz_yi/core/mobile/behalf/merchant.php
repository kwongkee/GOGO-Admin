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
$info = pdo_fetch('select * from '.tablename('decl_user').' where openid=:openid',[':openid'=>$openid]);
if(empty($info) && $op != 'display' && $op != 'register' && $op != 'sendcode'){
    redirect('./index.php?i=3&c=entry&do=behalf&m=sz_yi&p=merchant&op=register');
}

if($op=='display'){
    include $this->template('behalf/merchant/index');
}
elseif($op=='register'){
    $id = intval($data['id']);
    if($_W['ispost']){
        if($data['type']==1){
            if($_SESSION['regCode'] != trim($data['code'])){
                show_json(-1,['msg'=>'验证码错误']);
            }
            if(empty($data['user_tel']) || empty($data['area_code'])){
                show_json(-1,'请输入手机信息');
            }
        }elseif($data['type']==2){
            if($_SESSION['regCode'] != trim($data['code2'])){
                show_json(-1,['msg'=>'验证码错误']);
            }
            if(empty($data['user_email'])){
                show_json(-1,'请输入邮箱信息');
            }
        }

        $behalf_info = [
            'abbreviation' => trim($data['abbreviation']),
            'sbrand' => $data['pic_file'][0],
            'address_country' => $data['address_country'],
            'type' => $data['type'],
            'area_code' => trim($data['area_code']),
        ];

        if($id>0){
            $res = pdo_update('decl_user',[
                'user_name' => trim($data['user_name']),
                'user_tel' => intval($data['type'])==1?trim($data['user_tel']):'',
                'user_email' => intval($data['type'])==2?trim($data['user_email']):'',
                'remark' => trim($data['remark']),
                'address' => trim($data['address']),
                'behalf_info' => json_encode($behalf_info, true)
            ],['id'=>$id]);
            $em_id = pdo_fetch('select enterprise_id from '.tablename('decl_user').' where id=:id',[':id'=>$id]);
            pdo_update('centralize_manage_person',[
                'name'=>trim($data['user_name']),
                'tel'=>intval($data['type'])==1?trim($data['user_tel']):'',
                'email'=>intval($data['type'])==2?trim($data['user_email']):'',
                'country_code'=>intval($data['type'])==1?trim($data['area_code']):'',
            ],['enterprise_id'=>$em_id['enterprise_id']]);
            if($res){
                show_json(0, ['msg'=>'修改成功']);
            }
        }
        else{
            pdo_insert('centralize_manage_person',[
                'name'=>trim($data['user_name']),
                'type'=>1,
                'tel'=>intval($data['type'])==1?trim($data['user_tel']):'',
                'email'=>intval($data['type'])==2?trim($data['user_email']):'',
                'country_code'=>intval($data['type'])==1?trim($data['area_code']):'',
                'createtime'=>$time,
                'status'=>1
            ]);
            $res = pdo_insertid();
            //企业信息
            if($res) {
                pdo_insert('enterprise_members', [
                    'uniacid' => 3,
                    'nickname' => trim($data['user_name']),
                    'realname' => trim($data['user_name']),
                    'mobile' => intval($data['type']) == 1 ? trim($data['user_tel']) : '',
                    'reg_type' => 1,
                    'create_at' => $time,
                    'centralizer_id' => $res,
                    'is_verify' => 1
                ]);
                $em_id = pdo_insertid();
                pdo_update('centralize_manage_person',  ['enterprise_id' => $em_id],['id' => $res]);
                pdo_insert('enterprise_basicinfo', [
                    'member_id' => $em_id,
                    'name' => trim($data['user_name']),
                    'operName' => trim($data['user_name']),
                    'orgNo' => '',
                    'create_at' => $time,
                ]);
                $basic_id = pdo_insertid();
                $unique_id = '';
                if ($data['type'] == 1) {
                    $unique_id = md5((trim($data['user_tel']) . date('YmdHis')));
                } elseif ($data['type'] == 2) {
                    $unique_id = md5((trim($data['user_email']) . date('YmdHis')));
                }
                pdo_insert('total_merchant_account', [
                    'unique_id' => $unique_id,
                    'mobile' => trim($data['user_tel']),
                    'password' => password_hash(trim($data['user_tel']), PASSWORD_DEFAULT),
                    'uniacid' => 3,
                    'user_name' => trim($data['user_name']),
                    'company_name' => '',
                    'create_time' => $time,
                    'desc' => '',
                    'status' => 0,
                    'user_email' => '',
                    'address' => '',
                    //'address'=>$basic_info['address'],
                    'company_tel' => '',
                    'account_type' => 2,
                    'openid' => '',
                    'enterprise_id' => $em_id
                ]);
                pdo_insert('decl_user', [
                    'openid'=>$openid,
                    'user_name' => trim($data['user_name']),
                    'user_tel' => intval($data['type'])==1?trim($data['user_tel']):'',
                    'user_email' => intval($data['type'])==2?trim($data['user_email']):'',
                    'user_password' => md5('888888'),
                    'uniacid' => 3,
                    'plat_id' => 1,
                    'created_at' => date('Y-m-d H:i:s'),
                    'user_status' => 0,
                    'buss_id' => 3,
                    'remark' => trim($data['remark']),
                    'company_name' => '',
                    'company_num' => '',#不知道是什么
                    'address' => trim($data['address']),
                    'enterprise_id' => $em_id,
                    'behalf_info' => json_encode($behalf_info, true)
                ]);

                show_json(0, ['msg'=>'注册成功']);
            }
        }
    }else{
        $country = pdo_fetchall('select * from '.tablename('country_code').' where code_name!="无"');

        if(empty($info)){
            $info['behalf_info'] = ['address_country'=>142,'type'=>1];
        }else{
            redirect('./index.php?i=3&c=entry&do=behalf&m=sz_yi&p=merchant&op=manage');
            $info['behalf_info'] = json_decode($info['behalf_info'],true);
            $id = $info['id'];
        }
        include $this->template('behalf/merchant/register');
    }
}
elseif($op=='manage'){
    $id = intval($data['id']);
    if($_W['ispost']){
        if($data['type']==1){
            if($info['user_tel'] != trim($data['user_tel'])){
                if(empty($_SESSION['regCode'])){
                    show_json(-1,['msg'=>'请先获取手机验证码']);   
                }
                if($_SESSION['regCode'] != trim($data['code'])){
                    show_json(-1,['msg'=>'验证码错误']);
                }
                if(empty($data['user_tel']) || empty($data['area_code'])){
                    show_json(-1,'请输入手机信息');
                }
            }
        }elseif($data['type']==2){
            if($info['user_email'] != trim($data['user_email'])){
                if(empty($_SESSION['regCode'])){
                    show_json(-1,['msg'=>'请先获取邮箱验证码']);   
                }
                if($_SESSION['regCode'] != trim($data['code2'])){
                    show_json(-1,['msg'=>'验证码错误']);
                }
                if(empty($data['user_email'])){
                    show_json(-1,'请输入邮箱信息');
                }
            }
        }

        $behalf_info = [
            'abbreviation' => trim($data['abbreviation']),
            'sbrand' => $data['pic_file'][0],
            'address_country' => $data['address_country'],
            'type' => $data['type'],
            'area_code' => trim($data['area_code']),
        ];

        if($id>0){
            $res = pdo_update('decl_user',[
                'user_name' => trim($data['user_name']),
                'user_tel' => intval($data['type'])==1?trim($data['user_tel']):'',
                'user_email' => intval($data['type'])==2?trim($data['user_email']):'',
                'remark' => trim($data['remark']),
                'address' => trim($data['address']),
                'behalf_info' => json_encode($behalf_info, true)
            ],['id'=>$id]);
            $em_id = pdo_fetch('select enterprise_id from '.tablename('decl_user').' where id=:id',[':id'=>$id]);
            pdo_update('centralize_manage_person',[
                'name'=>trim($data['user_name']),
                'tel'=>intval($data['type'])==1?trim($data['user_tel']):'',
                'email'=>intval($data['type'])==2?trim($data['user_email']):'',
                'country_code'=>intval($data['type'])==1?trim($data['area_code']):'',
            ],['enterprise_id'=>$em_id['enterprise_id']]);
            if($res){
                show_json(0, ['msg'=>'修改成功']);
            }
        }
        else{
            pdo_insert('centralize_manage_person',[
                'name'=>trim($data['user_name']),
                'type'=>1,
                'tel'=>intval($data['type'])==1?trim($data['user_tel']):'',
                'email'=>intval($data['type'])==2?trim($data['user_email']):'',
                'country_code'=>intval($data['type'])==1?trim($data['area_code']):'',
                'createtime'=>$time,
                'status'=>1
            ]);
            $res = pdo_insertid();
            //企业信息
            if($res) {
                pdo_insert('enterprise_members', [
                    'uniacid' => 3,
                    'nickname' => trim($data['user_name']),
                    'realname' => trim($data['user_name']),
                    'mobile' => intval($data['type']) == 1 ? trim($data['user_tel']) : '',
                    'reg_type' => 1,
                    'create_at' => $time,
                    'centralizer_id' => $res,
                    'is_verify' => 1
                ]);
                $em_id = pdo_insertid();
                pdo_update('centralize_manage_person',  ['enterprise_id' => $em_id],['id' => $res]);
                pdo_insert('enterprise_basicinfo', [
                    'member_id' => $em_id,
                    'name' => trim($data['user_name']),
                    'operName' => trim($data['user_name']),
                    'orgNo' => '',
                    'create_at' => $time,
                ]);
                $basic_id = pdo_insertid();
                $unique_id = '';
                if ($data['type'] == 1) {
                    $unique_id = md5((trim($data['user_tel']) . date('YmdHis')));
                } elseif ($data['type'] == 2) {
                    $unique_id = md5((trim($data['user_email']) . date('YmdHis')));
                }
                pdo_insert('total_merchant_account', [
                    'unique_id' => $unique_id,
                    'mobile' => trim($data['user_tel']),
                    'password' => password_hash(trim($data['user_tel']), PASSWORD_DEFAULT),
                    'uniacid' => 3,
                    'user_name' => trim($data['user_name']),
                    'company_name' => '',
                    'create_time' => $time,
                    'desc' => '',
                    'status' => 0,
                    'user_email' => '',
                    'address' => '',
                    //'address'=>$basic_info['address'],
                    'company_tel' => '',
                    'account_type' => 2,
                    'openid' => '',
                    'enterprise_id' => $em_id
                ]);
                pdo_insert('decl_user', [
                    'openid'=>$openid,
                    'user_name' => trim($data['user_name']),
                    'user_tel' => intval($data['type'])==1?trim($data['user_tel']):'',
                    'user_email' => intval($data['type'])==2?trim($data['user_email']):'',
                    'user_password' => md5('888888'),
                    'uniacid' => 3,
                    'plat_id' => 1,
                    'created_at' => date('Y-m-d H:i:s'),
                    'user_status' => 0,
                    'buss_id' => 3,
                    'remark' => trim($data['remark']),
                    'company_name' => '',
                    'company_num' => '',#不知道是什么
                    'address' => trim($data['address']),
                    'enterprise_id' => $em_id,
                    'behalf_info' => json_encode($behalf_info, true)
                ]);

                show_json(0, ['msg'=>'注册成功']);
            }
        }
    }else{
        $country = pdo_fetchall('select * from '.tablename('country_code').' where code_name!="无"');
        $info['behalf_info'] = json_decode($info['behalf_info'],true);
        $id = $info['id'];

        include $this->template('behalf/merchant/manage');
    }
}
elseif($op=='sendcode'){
    $type = intval($data['type']);
    $code = mt_rand(11, 99) . mt_rand(11, 99) . mt_rand(11, 99);

    if($type==1){
        #手机
        $mobile = trim($data['mobile']);
        $ishave = pdo_fetch('select id from '.tablename('decl_user').' where user_tel=:mobile',[':mobile'=>$mobile]);
        if($ishave['id']){
            show_json(-1,['msg'=>'该手机号已注册']);
        }
        $set = m('common')->getSysset();
        $this->sendSms($mobile, $code,'SMS_35030089');
    }elseif($type==2){
        #邮箱
        $email = trim($data['email']);
//        $isreal = checkEmail($email);
//        if(!$isreal){
//            show_json(-1,['msg'=>'请输入正确的邮箱号']);
//        }
        $ishave = pdo_fetch('select id from '.tablename('decl_user').' where user_email=:email',[':email'=>$email]);
        if($ishave['id']){
            show_json(-1,['msg'=>'该邮箱号已注册']);
        }
        $r=httpRequest2('https://admin.gogo198.cn/collect_website/public/?s=/api/sendemail/index',['email'=>$email,'title'=>'注册成为购购网用户','content'=>'验证码：'.$code.'，您正在注册成为Gogo购购网用户，感谢您的支持！']);
    }

    $_SESSION["regCode"] = $code;
    show_json(0,['msg'=>'已发送']);
}
elseif($op=='goods_manage'){
    include $this->template('behalf/merchant/goods_manage');
}
elseif($op=='product_manage'){
    if(isset($data['pa'])){
        $limit = intval($data['limit']);
        $page = (intval($data['page'])-1) * $limit;
        $offset = ' limit '.$page.','.$limit;
        $count = pdo_fetchcolumn('select count(id) from '.tablename('behalf_goods').' where user_id=:user_id and status=0',[':user_id'=>$info['id']]);
        $list = pdo_fetchall('select * from '.tablename('behalf_goods').' where user_id=:user_id and status=0 order by id desc '.$offset,[':user_id'=>$info['id']]);
        foreach($list as $k=>$v){
            $list[$k]['createtime'] = date('Y-m-d H:i',$v['createtime']);
        }
        die(json_encode(['code'=>0,'data'=>$list,'count'=>$count]));
    }
    include $this->template('behalf/merchant/product_manage');
}
elseif($op=='save_product'){
    $id = intval($data['id']);

    if($_W['ispost']){

        if($id>0){
            $res = pdo_update('behalf_goods',[
                'name'=>trim($data['name']),
                'main_img'=>$data['pic_file'][0],
                'detail_img'=>json_encode($data['pic_file2'],true),
                'desc'=>trim($data['desc']),
                'option'=>trim($data['option']),
                'unit'=>trim($data['unit']),
                'price'=>trim($data['price']),
                'remark'=>trim($data['remark']),
            ],['id'=>$id]);

            if($res){
                show_json(0,['msg'=>'修改成功']);
            }
        }else{
            $res = pdo_insert('behalf_goods',[
                'user_id'=>$info['id'],
                'name'=>trim($data['name']),
                'main_img'=>$data['pic_file'][0],
                'detail_img'=>json_encode($data['pic_file2'],true),
                'desc'=>trim($data['desc']),
                'option'=>trim($data['option']),
                'unit'=>trim($data['unit']),
                'price'=>trim($data['price']),
                'remark'=>trim($data['remark']),
                'createtime'=>$time
            ]);

            if($res){
                show_json(0,['msg'=>'新增成功']);
            }
        }
    }else{
        if($id>0){
            $list = pdo_fetch('select * from '.tablename('behalf_goods').' where user_id=:user_id and status=0 and id=:id',[':user_id'=>$info['id'],':id'=>$id]);
            if(!empty($list['detail_img'])){
                $list['detail_img'] = json_decode($list['detail_img'],true);
            }
        }
        $unit = pdo_fetchall('select * from '.tablename('unit').' where 1');
        include $this->template('behalf/merchant/save_product');
    }
}
elseif($op=='del_product'){
    $id = intval($data['id']);
    $res = pdo_update('behalf_goods',['status'=>-1],['id'=>$id]);
    if($res){
        show_json(0,['msg'=>'删除成功']);
    }
}
elseif($op=='order_manage'){
    #订单管理
    if(isset($data['pa'])){
        $limit = intval($data['limit']);
        $page = (intval($data['page'])-1) * $limit;
        $offset = ' limit '.$page.','.$limit;
        $count = pdo_fetchcolumn('select count(id) from '.tablename('behalf_order').' where merch_id=:merch_id',[':merch_id'=>$info['id']]);
        $list = pdo_fetchall('select a.*,b.realname from '.tablename('behalf_order').' a left join '.tablename('sz_yi_member').' b on b.id=a.user_id where a.merch_id=:merch_id order by a.id desc '.$offset,[':merch_id'=>$info['id']]);
        $status = [0=>'未确认',1=>'已确认'];
        foreach($list as $k=>$v){
            $list[$k]['status_name'] = $status[$v['status']];
            $list[$k]['createtime'] = date('Y-m-d H:i',$v['createtime']);
        }
        die(json_encode(['code'=>0,'data'=>$list,'count'=>$count]));
    }else{
        include $this->template('behalf/merchant/order_manage');
    }
}
elseif($op=='make_express'){
    #运单制作
    $id = intval($data['id']);
    if($_W['ispost']){
        $res = pdo_update('behalf_order',['express_id'=>$data['express_id']],['id'=>$id]);
        if($res){
            show_json(0,['msg'=>'提交成功']);
        }
    }else{
        $order = pdo_fetch('select * from '.tablename('behalf_order').' where id=:id',[':id'=>$id]);
        $express = pdo_fetchall('select * from '.tablename('behalf_express_company').' where 1 order by id desc');
        include $this->template('behalf/merchant/make_express');
    }
}
elseif($op=='sure_order'){
    #订单确认
    $id = intval($data['id']);
    $res = pdo_update('behalf_order',['status'=>1],['id'=>$id]);
    if($res){
        show_json(0,['msg'=>'确认成功']);
    }
}
elseif($op=='order_detail'){
    #订单详情
    $id = intval($data['id']);
    $order = pdo_fetch('select * from '.tablename('behalf_order').' where merch_id=:merch_id and id=:id',[':merch_id'=>$info['id'],':id'=>$id]);
    $buyer = pdo_fetch('select * from '.tablename('sz_yi_member').' where openid=:openid',[':openid'=>$order['openid']]);
    $goods = pdo_fetch('select * from '.tablename('behalf_goods').' where id=:gid',[':gid'=>$order['good_id']]);
    $unit = pdo_fetchcolumn('select * from '.tablename('unit').' where code_value=:code_value',[':code_value'=>$goods['unit']]);
    $address = pdo_fetch('select * from '.tablename('sz_yi_member_address').' where openid=:openid',[':openid'=>$order['openid']]);
    include $this->template('behalf/merchant/order_detail');
}