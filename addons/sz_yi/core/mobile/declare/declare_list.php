<?php
// 模块LTD提供
//if (!defined('IN_IA')) {
//    exit('Access Denied');
//}

global $_W;
global $_GPC;

$openid=$_W['openid'];
$op = !empty($_GPC['op'])?trim($_GPC['op']):'display';

if($op=='display'){

    //已申报未运抵
    if($_GPC['pa']==1){
        $list = pdo_fetchall('select pre_batch_num,id,declare_status from '.tablename('customs_pre_declare').' where openid=:openid and declare_status=1 order by id desc',[':openid'=>$openid]);
        foreach($list as $k=>$v){
            $list[$k]['declare_status'] = '已申报未运抵';
        }
        $count = pdo_fetch('select count(id) as c from '.tablename('customs_pre_declare').' where openid=:openid and declare_status=1',[':openid'=>$openid]);
        die(json_encode(['code'=>0,'count'=>$count['c'],'data'=>$list]));
    }

    //已运抵未通关
    if($_GPC['pa']==2){
        $list = pdo_fetchall('select pre_batch_num,id,declare_status from '.tablename('customs_pre_declare').' where openid=:openid and declare_status=2 order by id desc',[':openid'=>$openid]);
        foreach($list as $k=>$v){
            $list[$k]['declare_status'] = '已运抵未通关';
        }
        $count = pdo_fetch('select count(id) as c from '.tablename('customs_pre_declare').' where openid=:openid and declare_status=2',[':openid'=>$openid]);
        die(json_encode(['code'=>0,'count'=>$count['c'],'data'=>$list]));
    }

    //已通关未离境
    if($_GPC['pa']==3){
        $list = pdo_fetchall('select pre_batch_num,id,declare_status from '.tablename('customs_pre_declare').' where openid=:openid and declare_status=3 order by id desc',[':openid'=>$openid]);
        foreach($list as $k=>$v){
            $list[$k]['declare_status'] = '已通关未离境';
        }
        $count = pdo_fetch('select count(id) as c from '.tablename('customs_pre_declare').' where openid=:openid and declare_status=3',[':openid'=>$openid]);
        die(json_encode(['code'=>0,'count'=>$count['c'],'data'=>$list]));
    }

    //已离境已结关
    if($_GPC['pa']==4){
        $list = pdo_fetchall('select pre_batch_num,id,declare_status from '.tablename('customs_pre_declare').' where openid=:openid and declare_status=4 order by id desc',[':openid'=>$openid]);
        foreach($list as $k=>$v){
            $list[$k]['declare_status'] = '已离境已结关';
        }
        $count = pdo_fetch('select count(id) as c from '.tablename('customs_pre_declare').' where openid=:openid and declare_status=4',[':openid'=>$openid]);
        die(json_encode(['code'=>0,'count'=>$count['c'],'data'=>$list]));
    }

    include $this->template('declare/declare_list/index');
}