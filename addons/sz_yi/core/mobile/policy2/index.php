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
    //显示分类
    $manage = 0;
    if($openid=='ov3-bt8keSKg_8z9Wwi-zG1hRhwg' || $openid=='ov3-bt5vIxepEjWc51zRQNQbFSaQ'){
        $manage = 1;
    }

    $list = pdo_fetchall('select * from '.tablename('policy_category').' where 1 and `show`=0 order by id desc');

    include $this->template('policy/index');
}elseif($op=='save_category'){
    //保存分类
    $id = intval($_GPC['id']);

    if($id>0){
        $data = pdo_fetch('select * from '.tablename('policy_category').' where id=:id',[':id'=>$id]);
    }

    if($_GPC['edit']==1){
        if($id>0){
            //修改
            $res = pdo_update('policy_category',[
                'category_name'=>trim($_GPC['category_name']),
            ],['id'=>$id]);
        }else{
            //添加
            $res = pdo_insert('policy_category',[
                'category_name'=>trim($_GPC['category_name']),
                'createtime'=>time()
            ]);
        }
        if($res){
            show_json(1,['msg'=>'保存成功！']);
        }
    }

    include $this->template('policy/save_category');
}elseif($op=='del_category'){
    $id = intval($_GPC['id']);
    if(empty($id)){
        show_json(-1,['msg'=>'隐藏失败，缺少参数！']);
    }

    $res = pdo_update('policy_category',['show'=>1],['id'=>$id]);
    if($res){
        show_json(1,['msg'=>'隐藏成功！']);
    }
}elseif($op=='category_list'){
    //分类列表

    if($_GPC['pa']==1){
        //管理员页面
        $list = pdo_fetchall('select * from '.tablename('policy_category').' where 1 and `show`=0 order by id desc');
        foreach($list as $k=>$v){
            $list[$k]['createtime'] = date('Y-m-d H:i',$v['createtime']);
        }
        die(json_encode(['code'=>0,'data'=>$list]));
    }

    include $this->template('policy/category_list');
}elseif($op=='column_list'){
    //栏目列表
    $cate_id = intval($_GPC['cate_id']);

    if($_GPC['pa']==1){
        //管理员页面
        $list = pdo_fetchall('select * from '.tablename('policy_list').' where cate_id=:cate_id order by release_date desc',[':cate_id'=>$cate_id]);
//        foreach($list as $k=>$v){
//            $list[$k]['release_date'] = date('Y-m-d H:i',$v['createtime']);
//        }
        die(json_encode(['code'=>0,'data'=>$list]));
    }
    include $this->template('policy/column_list');
}elseif($op=='save_column'){
    //保存栏目
    $id = intval($_GPC['id']);
    $cate_id = intval($_GPC['cate_id']);

    if($cate_id>0){
        $cate_name = pdo_fetchcolumn('select category_name from '.tablename('policy_category').' where id=:cate_id',[':cate_id'=>$cate_id]);
    }

    if($id>0){
        $data = pdo_fetch('select * from '.tablename('policy_list').' where id=:id',[':id'=>$id]);
    }

    if($_GPC['edit']==1){
        if($id>0){
            //修改
            $res = pdo_update('policy_list',[
                'issuing_authority'=>trim($_GPC['issuing_authority']),
                'document_number'=>trim($_GPC['document_number']),
                'name'=>trim($_GPC['name']),
                'release_date'=>trim($_GPC['release_date']),
                'effective_date'=>trim($_GPC['effective_date']),
                'effect'=>trim($_GPC['effect']),
                'effect_statement'=>trim($_GPC['effect_statement']),
                'link'=>trim($_GPC['link']),
            ],['id'=>$id]);
        }else{
            //添加
            $res = pdo_insert('policy_list',[
                'cate_id'=>$cate_id,
                'issuing_authority'=>trim($_GPC['issuing_authority']),
                'document_number'=>trim($_GPC['document_number']),
                'name'=>trim($_GPC['name']),
                'release_date'=>trim($_GPC['release_date']),
                'effective_date'=>trim($_GPC['effective_date']),
                'effect'=>trim($_GPC['effect']),
                'effect_statement'=>trim($_GPC['effect_statement']),
                'link'=>trim($_GPC['link']),
                'createtime'=>time()
            ]);
        }
        if($res){
            show_json(1,['msg'=>'保存成功！']);
        }
    }

    include $this->template('policy/save_column');
}elseif($op=='del_column'){
    $id = intval($_GPC['id']);
    if(empty($id)){
        show_json(-1,['msg'=>'删除失败，缺少参数！']);
    }

    $res = pdo_delete('policy_list',['id'=>$id]);
    if($res){
        show_json(1,['msg'=>'删除成功！']);
    }
}