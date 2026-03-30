<?php
// 模块LTD提供
if (!defined('IN_IA')) {
    exit('Access Denied');
}

/**
 * 跨境政策
 * 2022-04-14
 */

global $_W;
global $_GPC;

$openid=$_W['openid'];
$op = !empty($_GPC['op'])?trim($_GPC['op']):'display';

if($op=='display'){
    //显示该分类下的栏目
    $cate_id = intval($_GPC['cate_id']);
    if($cate_id>0){
        $category_name = pdo_fetchcolumn('select category_name from '.tablename('policy_category').' where id=:id',[':id'=>$cate_id]);
    }
    if($_GPC['pa']==1){
        $list = pdo_fetchall('select * from '.tablename('policy_list').' where cate_id=:cate_id order by release_date desc',[':cate_id'=>$cate_id]);
        die(json_encode(['code'=>0,'data'=>$list]));
    }

    include $this->template('policy/column/index');
}elseif($op=='watch_column'){
    //查看栏目
    $id = intval($_GPC['id']);
    if(empty($id)){
        show_json(-1,['msg'=>'参数错误']);
    }

    $data = pdo_fetch('select * from '.tablename('policy_list').' where id=:id',[':id'=>$id]);
    $cate_name = pdo_fetchcolumn('select category_name from '.tablename('policy_category').' where id=:id',[':id'=>$data['cate_id']]);
    include $this->template('policy/column/watch_column');
}elseif($op=='testing'){
    //检测是否商户
    $is_user = pdo_fetch('select id from '.tablename('decl_user').' where openid=:openid',[':openid'=>$openid]);
    if(empty($is_user)){
        show_json(-1,['msg'=>'请先注册商户！']);
    }else{
        show_json(1);
    }
}elseif($op=='share'){
    //生成二维码
    $id = intval($_GPC['id']);

    require_once IA_ROOT.'/addons/sz_yi/phpqrcode.php';
    $path = '/addons/sz_yi/static/QRcode/'; //储存的地方
    if (!is_dir(IA_ROOT.$path)) {
        load()->func('file');
        mkdirs(IA_ROOT.$path); //创建文件夹
    }

    $infourl = 'https://shop.gogo198.cn/app/index.php?i=3&c=entry&p=column&do=policy&m=sz_yi&op=watch_column&id='.$id;
    $filename =  $path.time().'.png'; //图片文件
    QRcode::png($infourl, IA_ROOT.$filename, $errorCorrectionLevel, $matrixPointSize, 2); //生成图片
    $orderFileCcollectionImg = str_replace('/www/wwwroot/gogo','https://shop.gogo198.cn',IA_ROOT.$filename);
    if($orderFileCcollectionImg){
        show_json(1,['msg'=>'获取二维码成功！','qrcode'=>$orderFileCcollectionImg]);
    }else{
        show_json(1,['msg'=>'获取二维码失败！']);
    }
}