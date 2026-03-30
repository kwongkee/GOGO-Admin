<?php
defined('IN_IA') or exit('Access Denied');

global $_W;
global $_GPC;

$operation = (!empty($_GPC['op']) ? $_GPC['op'] : 'display');
$openid = m('user')->getOpenid();
//$member = m('member')->getMember($openid);
$id = intval($_GPC['id']);
$data = pdo_fetch('select * from '.tablename('customer_authorization_app').' where openid=:openid and id=:id',[':openid'=>$openid,':id'=>intval($_GPC['id'])]);
if(empty($data)){
    exit('参数错误');
}

if ($operation == 'display') {
    //请求授权页面
    if(empty($id)){
        exit('参数错误');
    }
    //step1:获取授权的信息
    $data['app_type'] = explode('/',$data['app_type']);
    $data['app_type_cn'] = [];
    foreach($data['app_type'] as $k=>$v){
        switch($v){
            case 'platform_list':
                $data['app_type_cn'][$k]='平台清单';
                break;
            case 'declare_list':
                $data['app_type_cn'][$k]='清单申报';
                break;
            case 'logistics_dome':
                $data['app_type_cn'][$k]='物流单证';
                break;
            case 'offshore_collection':
                $data['app_type_cn'][$k]='离岸收款';
                break;
            case 'offshore_exchange':
                $data['app_type_cn'][$k]='离岸换汇';
                break;
            case 'withdrawal':
                $data['app_type_cn'][$k]='结汇提现';
                break;
            case 'offshore_transfer':
                $data['app_type_cn'][$k]='离岸转账';
                break;
        }
    }
    include $this->template('member/app_authorization/index');
}elseif($operation == 'update'){
    $customer_app = '';

    if(!empty($_GPC['customer_app'])){
        foreach($_GPC['customer_app'] as $k=>$v){
            if(!empty($v)){
                $customer_app .= $v.'/';
            }
        }
    }
    $insert_data = [];
    $insert_data['customer_app'] = substr($customer_app,0,strlen($customer_app)-1);
    $insert_data['status'] = 1;
    $insert_data['create_at'] = time();//授权时间

    $res = pdo_update('customer_authorization_app',$insert_data,['id'=>$id]);
    if($res){
        echo 1;exit;
//        return ['status'=>1,'msg'=>'授权成功'];
    }else{
        echo -1;exit;
//        return ['status'=>-1,'msg'=>'授权失败'];
    }
}