<?php
// 模块LTD提供
if (!defined('IN_IA')) {
    exit('Access Denied');
}

/**
 * 预提管理
 * 2022-05-06
 */
global $_W;
global $_GPC;

$openid=$_W['openid'];
$op = !empty($_GPC['op'])?trim($_GPC['op']):'display';

if($op=='display'){

    if($_GPC['pa']==1){
        $list = pdo_fetchall('select pre_batch_num,id,check_remark from '.tablename('customs_pre_declare').' where openid=:openid and withhold_status=2 order by id desc',[':openid'=>$openid]);
        foreach($list as $k=>$v){
            $list[$k]['withhold_status'] = '已预提，未申报';
            if(empty($v['check_remark'])){
                $list[$k]['check_remark'] = '-';
            }
        }
        $count = pdo_fetch('select count(id) as c from '.tablename('customs_pre_declare').' where openid=:openid and withhold_status=2',[':openid'=>$openid]);
        die(json_encode(['code'=>0,'count'=>$count['c'],'data'=>$list]));
    }

    if($_GPC['pa']==2){
        $list = pdo_fetchall('select pre_batch_num,id,check_remark from '.tablename('customs_pre_declare').' where openid=:openid and withhold_status=3 order by id desc',[':openid'=>$openid]);
        foreach($list as $k=>$v){
            $list[$k]['withhold_status'] = '已预提，被退回';
            if(empty($v['check_remark'])){
                $list[$k]['check_remark'] = '-';
            }
        }
        $count = pdo_fetch('select count(id) as c from '.tablename('customs_pre_declare').' where openid=:openid and withhold_status=3',[':openid'=>$openid]);
        die(json_encode(['code'=>0,'count'=>$count['c'],'data'=>$list]));
    }

    if($_GPC['pa']==3){
        $list = pdo_fetchall('select pre_batch_num,id,check_remark from '.tablename('customs_pre_declare').' where openid=:openid and withhold_status=4 order by id desc',[':openid'=>$openid]);
        foreach($list as $k=>$v){
            $list[$k]['withhold_status'] = '已预提，已申报';
            if(empty($v['check_remark'])){
                $list[$k]['check_remark'] = '-';
            }
        }
        $count = pdo_fetch('select count(id) as c from '.tablename('customs_pre_declare').' where openid=:openid and withhold_status=4',[':openid'=>$openid]);
        die(json_encode(['code'=>0,'count'=>$count['c'],'data'=>$list]));
    }

    include $this->template('declare/accrual/index');
}