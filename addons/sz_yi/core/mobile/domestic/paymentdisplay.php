<?php
// 模块LTD提供
if (!defined('IN_IA')) {
    exit('Access Denied');
}

global $_W;
global $_GPC;

$op = !empty($_GPC['op'])?trim($_GPC['op']):'display';
$pa = !empty($_GPC['pa'])?trim($_GPC['pa']):0;
if($op=='display'){
    //显示配置
    if($pa==1){
        //保存配置
        $manual_end_time = explode('T',$_GPC['config']['manual_end_datetime']);
        $manual_end_time = strtotime($manual_end_time[0].' '.$manual_end_time[1]);
        $now_time = time();
        if($_GPC['config']['display_type'] == 'manual'){
            if($manual_end_time<$now_time){
                show_json(-1,'结束时间不能早于当前时间');
            }
        }else{
            $now_time = '';
            $manual_end_time = '';
        }

        $res = pdo_update('miniprogram_payment_display',['display_type'=>$_GPC['config']['display_type'],'manual_start_time'=>$now_time,'manual_end_time'=>$manual_end_time,'interval_x'=>intval($_GPC['config']['interval_x']),'interval_y'=>intval($_GPC['config']['interval_y']),'random_probability'=>intval($_GPC['config']['random_probability']),'is_hide'=>0],['id'=>1]);

        if($res){
            show_json(0,'成功保存配置！');
        }
    }
    elseif($pa==2){
        //清空配置

        $res = pdo_update('miniprogram_payment_display',['display_type'=>'','manual_start_time'=>'','manual_end_time'=>'','interval_x'=>0,'interval_y'=>0,'random_probability'=>0,'is_hide'=>0],['id'=>1]);

        if($res){
            show_json(0,'成功清空配置！');
        }
    }
    elseif($pa==3){
        //强制隐藏
        $res = pdo_update('miniprogram_payment_display',['display_type'=>'','manual_start_time'=>'','manual_end_time'=>'','interval_x'=>0,'interval_y'=>0,'random_probability'=>0,'is_hide'=>1],['id'=>1]);

        if($res){
            show_json(0,'成功隐藏！');
        }
    }

    $info = pdo_fetch('select * from '.tablename('miniprogram_payment_display').' where id=1');
    if(!empty($info['manual_start_time']) && !empty($info['manual_end_time'])){
        $info['manual_start_time'] = date('Y-m-d H:i:s',$info['manual_start_time']);
        $info['manual_end_time'] = date('Y-m-d H:i:s',$info['manual_end_time']);
    }
    $is_show_online_pay = 0;//0不显示，1显示
    $time = time();

    if(strtotime($info['manual_start_time'])<$time && strtotime($info['manual_end_time'])>$time){
        #手动显示
        $is_show_online_pay = 1;

    }
    elseif($info['interval_x']>0 && $info['interval_y']>0){
        #间隔显示
//        $count = Db::name('miniprogram_order_list')->whereRaw('company_id=33 and status=1 and pay_method=1')->count();
//        if($count > 0 && ($count % $info['interval_x'] == 0)){
            $is_show_online_pay = 1;
//        }
    }
    elseif($info['random_probability']>0){
        #随机显示
//        $random = mt_rand(1, 100);
//        if($random <= $info['random_probability']){
            $is_show_online_pay = 1;
//        }
    }
    else{
        if(empty($info['manual_start_time']) && empty($info['manual_end_time']) && empty($info['interval_x']) && empty($info['interval_y']) && empty($info['random_probability'])){
            //没有任何配置
            $is_show_online_pay = 1;
        }
    }

    if($info['is_hide']==1) {
        #强制隐藏
        $is_show_online_pay = 0;
    }

    include $this->template('domestic/paymentdisplay');
}