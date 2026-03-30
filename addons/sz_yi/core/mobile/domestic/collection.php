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

if($op=='project') {
    if(!empty($_W['openid'])){
        //step2:获取商户的项目配置列表
        $list = pdo_fetchall('select project_name,id from '.tablename('customs_project').' where openid=:openid and uniacid=:uni order by id desc',array(':openid'=>$_W['openid'],':uni'=>$_W['uniacid']));

        include $this->template('domestic/project');
    }else{
        exit('请先关注公众号并授权登录！');
    }
}elseif($op=='add_project'){
    if($_W['isajax']){
        $project_name = trim($_GPC['project_name']);
        $res = pdo_insert('customs_project',array('uniacid'=>$_W['uniacid'],'project_name'=>$project_name,'openid'=>$_W['openid'],'createtime'=>time()));
        if($res){
            show_json(1);
        }
    }else{
        include $this->template('domestic/add_project');
    }
}elseif($op=='edit_project'){
    if($_W['isajax']){
        if(intval($_GPC['id'])==0){
            exit('参数错误');
        }
        $res = pdo_update('customs_project',['project_name'=>trim($_GPC['project_name'])],['id'=>intval($_GPC['id'])]);
        if($res){
            show_json(1);
        }
    }else{
        $project = pdo_fetch('select * from '.tablename('customs_project').' where id=:id and uniacid=:uni and openid=:openid',array(':id'=>$_GPC['id'],':uni'=>$_W['uniacid'],':openid'=>$_W['openid']));
        include $this->template('domestic/edit_project');
    }
}elseif($op=='search'){
    //收款查询
    $status = 1;
    $list = pdo_fetchall('select * from '.tablename('customs_collection').' where send_openid=:openid and uniacid=:uni and status=:status order by createtime desc',array(':openid'=>$_W['openid'],':uni'=>$_W['uniacid'],':status'=>1));
    $list2 = pdo_fetchall('select * from '.tablename('customs_collection').' where send_openid=:openid and uniacid=:uni and status=:status and createtime !="" order by createtime desc',array(':openid'=>$_W['openid'],':uni'=>$_W['uniacid'],':status'=>0));

    include $this->template('domestic/search');
}elseif($op=='search_detail'){
    if(intval($_GPC['id'])==0){
        exit('参数错误');
    }
    $tixian_status = [0=>'待提现',-1=>'审核失败',1=>'待审核',2=>'已审核'];
    $data = pdo_fetch('select * from '.tablename('customs_collection').' where send_openid=:openid and uniacid=:uni and id=:id',array(':openid'=>$_W['openid'],':uni'=>$_W['uniacid'],':id'=>intval($_GPC['id'])));
    $other_pay = pdo_fetch('select * from '.tablename('customs_collection_otherpay').' where orderid=:orderid',[':orderid'=>$data['id']]);

    //付款依据
    if($data['basic']==1){
        $data['basic'] = '合同';
        $data['contract_file'] = json_decode($data['contract_file'],true);
    }elseif($data['basic']==2){
        $data['basic'] = '订单';
        $data['orderdemo'] = json_decode($data['orderdemo'],true);
    }elseif($data['basic']==3){
        $data['basic'] = '说明';
    }

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

    include $this->template('domestic/search_detail');
}elseif($op=='fee'){
    //查看自己的提现费率
    $fee = pdo_fetchcolumn('select rate_info from '.tablename('decl_user').' where uniacid=:uni and openid=:openid',array(':uni'=>$_W['uniacid'],':openid'=>$_W['openid']));
    if(!empty($fee)){
        $fee = json_decode($fee,true);
    }
    include $this->template('domestic/fee');
}elseif($op=='notyetPay'){
    //查询还未支付的订单
    $list = pdo_fetchall('select a.ordersn,a.status,a.send_openid,b.user_name,a.id,a.createtime from '.tablename('customs_collection').' a left join '.tablename('decl_user').' b on b.openid=a.send_openid where a.openid=:openid and a.uniacid=:uni and a.status=0 and a.createtime!="" order by a.createtime desc',array(':openid'=>$_W['openid'],':uni'=>$_W['uniacid']));
    foreach($list as $k=>$v){
        $list[$k]['createtime'] = date('Y-m-d H:i:s',$v['createtime']);
    }

    include $this->template('domestic/notyet_pay');
}