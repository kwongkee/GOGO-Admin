<?php
// 模块LTD提供
if (!defined('IN_IA')) {
    exit('Access Denied');
}

global $_W;
global $_GPC;

$op = !empty($_GPC['op'])?trim($_GPC['op']):'display';

if(!empty($_W['openid'])){
    //step1:先判断该用户有无申请商户
    $is_user = pdo_fetch('select a.id,b.user_status from '.tablename('enterprise_members').' a left join '.tablename('decl_user').' b on b.openid=a.openid where a.openid=:openid and a.uniacid=:uni limit 1',array(':openid'=>$_W['openid'],':uni'=>$_W['uniacid']));

    if(empty($is_user)){
        //先去成为商户
        header('Location: '.$_W['siteroot'].'./app/index.php?i='.$_W['uniacid'].'&c=entry&do=enterprise&m=sz_yi&p=register');
    }elseif($is_user['user_status']==1 ){
        exit('商户已被禁用，请联系管理员！');
    }elseif($is_user['user_status']==2){
        exit('商户正在审核，请耐心等待！');
    }
}

if($op=='account'){
    //国内私户公户列表
    $status=1;
    $list = pdo_fetchall('select id,account from '.tablename('customs_bank_account').' where openid=:openid and uniacid=:uni and account_type2=1',array(':openid'=>$_W['openid'],':uni'=>$_W['uniacid']));
    $list2 = pdo_fetchall('select id,account from '.tablename('customs_bank_account').' where openid=:openid and uniacid=:uni and account_type2=2',array(':openid'=>$_W['openid'],':uni'=>$_W['uniacid']));

    include $this->template('domestic/account');
}elseif($op=='add_account'){
    if($_W['isajax']){
        $data = explode(',',$_GPC['data']);
        $insert_data = [
            'uniacid'=>$_W['uniacid'],
            'openid'=>$_W['openid'],
            'account_type2'=>$data[0],
            'money_type'=>$data[1],
            'account_name'=>$data[2],
            'bank_name'=>$data[3],
            'bank_org_name'=>$data[4],
            'account'=>$data[5],
            'status'=>0,
            'account_type'=>$data[6],
            'createtime'=>time(),
        ];

        $res = pdo_insert('customs_bank_account',$insert_data);
        if($res){
            show_json(1);
        }
    }else{
        $bank_list = pdo_fetchall('select * from '.tablename('bank_list').' where 1');
        include $this->template('domestic/add_account');
    }
}elseif($op=='account_detail'){
    if($_W['isajax']){
        $dat = $_GPC['data'];
        $dat = explode(',',$dat);
        $id = $dat[7];
        if(intval($id)==0){
            show_json(-1,'参数错误');
        }

        $res = pdo_update('customs_bank_account',[
            'account_type2'=>$dat[0],
            'money_type'=>$dat[1],
            'account_name'=>$dat[2],
            'bank_name'=>$dat[3],
            'bank_org_name'=>$dat[4],
            'account'=>$dat[5],
            'account_type'=>$dat[6]
        ],[
            'id'=>$id,
            'openid'=>$_W['openid']
        ]);

        if($res){
            show_json(1);
        }
    }else{
        if(intval($_GPC['id'])==0){
            exit('参数错误');
        }
        $data = pdo_fetch('select * from '.tablename('customs_bank_account').' where openid=:openid and id=:id and uniacid=:uni',array(':openid'=>$_W['openid'],':id'=>intval($_GPC['id']),':uni'=>$_W['uniacid']));
        $bank_list = pdo_fetchall('select * from '.tablename('bank_list').' where 1');
        include $this->template('domestic/account_detail');
    }
}elseif($op=='withdrawal'){
    $tixian_status = [0=>'待提现',-1=>'审核失败',1=>'待审核',2=>'已审核'];
    //提现管理
    if($_W['ajax']){

    }else{
        //step1:获取该商户可提现的订单号
        $status=1;
        $list = pdo_fetchall('select * from '.tablename('customs_collection').' where status=1 and (tixian_status=-1 or tixian_status=0 or tixian_status=1) and pay_type!=3 and send_openid=:openid and uniacid=:uni order by createtime desc',array(':openid'=>$_W['openid'],':uni'=>$_W['uniacid']));
        $list2 = pdo_fetchall('select * from '.tablename('customs_collection').' where status=1 and tixian_status=2 and pay_type!=3 and send_openid=:openid and uniacid=:uni order by createtime desc',array(':openid'=>$_W['openid'],':uni'=>$_W['uniacid']));

        include $this->template('domestic/withdrawal');
    }
}elseif($op=='withdrawal_detail'){
    //订单详情
    if(intval($_GPC['id'])==0){
        exit('参数错误');
    }
    $tixian_status = [0=>'待提现',-1=>'审核失败',1=>'待审核',2=>'已审核'];
    $data = pdo_fetch('select * from '.tablename('customs_collection').' where send_openid=:openid and uniacid=:uni and id=:id',array(':openid'=>$_W['openid'],':uni'=>$_W['uniacid'],':id'=>intval($_GPC['id'])));
    //获取提现费率信息
    $data['fee_info'] = pdo_fetch('select * from '.tablename('customs_domestic_withdrawal').' where find_in_set(:oid,orderid) and openid=:openid',array(':oid'=>$data['id'],':openid'=>$_W['openid']));
    //获取银行账号信息
    $data['account_info'] = pdo_fetch('select * from '.tablename('customs_bank_account').' where id=:account_id and openid=:openid',array(':account_id'=>$data['fee_info']['account_id'],':openid'=>$_W['openid']));

    $name = '';
    if($data['trade_type']==1){
        $name = pdo_fetchcolumn('select title from '.tablename('sz_yi_goods').' where id=:id',array(':id'=>$data['good_id']));
        $data['trade_type_name'] = '商品';
    }elseif($data['trade_type']==2){
        $name = pdo_fetchcolumn('select project_name from '.tablename('customs_project').' where id=:id',array(':id'=>$data['project_id']));
        $data['trade_type_name'] = '项目';
    }elseif($data['trade_type']==3){
        $data['trade_type_name'] = '服务';
        $data['service_info'] = json_decode($data['service_info'],true);
        foreach($data['service_info'] as $k=>$v){
            $data['service_info'][$k] = explode(',',$v);
        }
        $name = '多项服务';
    }

    include $this->template('domestic/withdrawal_detail');
}elseif($op=='pressMoney'){
    //催款
    $list = pdo_fetchall('select * from '.tablename('customs_collection').' where press_money_type!="" and press_money_day!="" and send_openid=:openid and status=0 and uniacid=:uni order by id desc',[':openid'=>$_W['openid'],':uni'=>$_W['uniacid']]);
    include $this->template('domestic/pressmoney_list');
}elseif($op=='pressMoney_add'){
    if($_W['isajax']){
        $id = intval(explode(',',$_GPC['orderid'])[0]);
        $res = pdo_update('customs_collection',['press_money_type'=>intval($_GPC['press_money_type']),'press_money_day'=>intval($_GPC['press_money_day'])],['id'=>$id,'uniacid'=>$_W['uniacid'],'send_openid'=>$_W['openid']]);
        if($res){
            show_json('1','添加成功');
        }else{
            show_json('-1','添加失败');
        }
    }else{
        //找出未配置催款方式、未支付的订单
        $list = pdo_fetchall('select * from '.tablename('customs_collection').' where press_money_type is null and press_money_day is null and createtime is not null and send_openid=:openid and status=0 and uniacid=:uni order by id desc',[':openid'=>$_W['openid'],':uni'=>$_W['uniacid']]);
        include $this->template('domestic/pressmoney_add');
    }
}elseif($op=='withdraw_woyao'){
    if($_W['isajax']){
        pdo_begin();
        try{
            //step1:获取数据
            $data['orderid'] = trim($_GPC['orderid']);
            $data['type'] = intval($_GPC['type']);
            if($data['type']==1){
                $data['account_id'] = intval($_GPC['private_account']);
            }elseif($data['type']==2){
                $data['account_id'] = intval($_GPC['common_account']);
            }
            $data['uniacid'] = $_W['uniacid'];
            $data['openid'] = $_W['openid'];
            $data['withdraw_money'] = trim($_GPC['withdraw_money']);//提现金额
            $data['createtime'] = time();
            $data['check_status'] = 0;//-1审核不通过，0待审核，1已审核-已汇款
            $data['withdrawal_expenses_rate'] = trim($_GPC['withdrawal_expenses_rate']);//提现费率
            $data['withdrawal_expenses'] = trim($_GPC['withdrawal_expenses']);//提现费用
            $data['true_money'] = trim($_GPC['true_money']);//预计到账
            pdo_insert('customs_domestic_withdrawal',$data);

            //step2:更改订单状态
            $orderid = explode(',',$data['orderid']);
            foreach($orderid as $k=>$v){
                if(!empty($v)){
                    pdo_update('customs_collection',['tixian_status'=>1,'tixian_remark'=>''],['id'=>$v,'send_openid'=>$data['openid']]);
                }
            }
            pdo_commit();

            //step3：发送消息给老板
            load()->func('communication');
//            ov3-bt8keSKg_8z9Wwi-zG1hRhwg ov3-bt5vIxepEjWc51zRQNQbFSaQ
            $user_name = pdo_fetchcolumn('select user_name from '.tablename('decl_user').' where uniacid=:uni and openid=:openid',array(':uni'=>$_W['uniacid'],':openid'=>$data['openid']));
            //订单号
            $orderid = explode(',',$data['orderid'])[0];
            $ordersn = pdo_fetchcolumn('select ordersn from '.tablename('customs_collection').' where id=:id and send_openid=:openid',array(':id'=>$orderid,':openid'=>$_W['openid']));
//            $post = json_encode([
//                'call'=>'sendTextToFans',
//                'msg' =>'商户（'.$user_name.'）已提交申请国内提现！请尽快审核',
//                'touser' =>'ov3-bt8keSKg_8z9Wwi-zG1hRhwg',//老板openid
//                'uniacid'=>$data['uniacid']
//            ]);
            $post = json_encode([
                'call'=>'sendApplyMsg',
                'title' =>'国内提现提交',
                'user_name' => $user_name,
                'time' => date('Y-m-d H:i:s',time()),
                'remark' => '订单号:'.$ordersn,
                'url' => '',
                'openid' => 'ov3-bt8keSKg_8z9Wwi-zG1hRhwg',
                'uniacid' =>3
            ]);
            ihttp_request('https://shop.gogo198.cn/api/sendwechattemplatenotice.php', $post);

            show_json('1','创建提现成功，正在审核中');
        }catch(\Exception $e){
            pdo_rollback();
            show_json('-1','系统错误');
        }

    }else{
        //获取私户银行卡号
        $account_p = pdo_fetchall('select * from '.tablename('customs_bank_account').' where uniacid=:uni and openid=:openid and account_type2=1 order by createtime desc',array(':uni'=>$_W['uniacid'],':openid'=>$_W['openid']));
        $account_c = pdo_fetchall('select * from '.tablename('customs_bank_account').' where uniacid=:uni and openid=:openid and account_type2=2 order by createtime desc',array(':uni'=>$_W['uniacid'],':openid'=>$_W['openid']));
        //可提现订单号（已支付、未提现）
        $list = pdo_fetchall('select * from '.tablename('customs_collection').' where status=1 and (tixian_status=0 or tixian_status=-1) and pay_type!=3 and send_openid=:openid and uniacid=:uni order by createtime desc',array(':openid'=>$_W['openid'],':uni'=>$_W['uniacid']));
        //获取费率
        $fee = pdo_fetchcolumn('select rate_info from '.tablename('decl_user').' where openid=:openid and uniacid=:uni',array(':openid'=>$_W['openid'],':uni'=>$_W['uniacid']));
//        {"industry":"1","bankcard_rate":"0.006","creditcard_rate":"0.007","rate_limit":"10"}
        if(empty($fee)){
            $fee='请联系管理人员配置费率！';
        }else{
            $fee = json_decode($fee,true);
            //判断付款人的支付方式，待做
        }
        include $this->template('domestic/withdrawal_woyao');
    }
}elseif($op=='getOrderPrice'){
    $orderid = explode(',',$_GPC['orderid']);
    $total_money = 0;//提现金额
    $fee_money = 0;//提现费用
    $true_money=0;//实际到账
    foreach($orderid as $k=>$v){
        if(!empty($v)){
            $total_money +=pdo_fetchcolumn('select total_money from '.tablename('customs_collection').' where send_openid=:openid and id=:id and uniacid=:uni',array(':openid'=>$_W['openid'],':id'=>$v,':uni'=>$_W['uniacid']));
        }
    }
    if($total_money>0){
        //获取费率
        $fee = pdo_fetchcolumn('select rate_info from '.tablename('decl_user').' where openid=:openid and uniacid=:uni',array(':openid'=>$_W['openid'],':uni'=>$_W['uniacid']));
        $fee = json_decode($fee,true);
        $fee_money = sprintf('%.2f',$total_money*$_GPC['fee']);
        if($fee_money>$fee['rate_limit']){
            //提现费用大于费率封顶金额时，采用封顶金额
            $fee_money=$fee['rate_limit'];
        }
        $true_money = sprintf('%.2f',$total_money - $fee_money);
    }
    show_json(1,array('money'=>$total_money,'fee_money'=>$fee_money,'true_money'=>$true_money));
}elseif($op=='getaccountfee'){
    //获取银行卡费率
    $account_id = intval($_GPC['account_id']);
    $account_type = pdo_fetchcolumn('select account_type from '.tablename('customs_bank_account').' where id=:id and openid=:openid',array(':id'=>$account_id,':openid'=>$_W['openid']));
    //获取商户费率
    $fee = pdo_fetchcolumn('select rate_info from '.tablename('decl_user').' where openid=:openid and uniacid=:uni',array(':openid'=>$_W['openid'],':uni'=>$_W['uniacid']));
    if(empty($fee)){
        show_json(-1,array('msg'=>'请联系管理人员配置费率！'));
    }
    $fee = json_decode($fee,true);
    if($account_type==1){
        //储蓄卡
        show_json(1,array('fee'=>$fee['bankcard_rate']));
    }elseif($account_type==2){
        //信用卡
        show_json(1,array('fee'=>$fee['creditcard_rate']));
    }
}
