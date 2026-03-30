<?php

if (!defined('IN_IA')) {
    exit('Access Denied');
}

global $_W, $_GPC;
require "common.php";

$resu = pdo_get('onljob_jifens',['uid'=>$_W['member']['uid'],'weid'=>$_W['uniacid']]);

if (!$_W['ispost']){
    include $this->template('jifen');
}else{
    $urlt = $this->createMobileUrl('jifen');
    if ($_GPC['score']==""||$_GPC['level']==""){
        message_app('不允许为空！',array(''),'error');
    }

    $resu = pdo_get('onljob_jifens',['uid'=>$_W['member']['uid'],'weid'=>$_W['uniacid']]);
    if (empty($resu)){
        pdo_insert('onljob_jifens',[
            'uid'=>$_W['member']['uid'],
            'score'=>$_GPC['score'],
            'level'=>$_GPC['level'],
            'create_time'=>time(),
            'weid'=>$_W['uniacid']
        ]);
    }else{
        pdo_update('onljob_jifens',[
            'score'=>$_GPC['score'],
            'level'=>$_GPC['level'],
        ],['id'=>$resu['id']]);
    }
    message_app('提交成功！',array($urlt),'success');
}
