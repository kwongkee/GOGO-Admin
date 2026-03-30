<?php
// 模块LTD提供
if (!defined('IN_IA')) {
    exit('Access Denied');
}

/**
 * 跨境政策
 * 2022-09-23
 */
global $_W;
global $_GPC;

$openid=$_W['openid'];
$op = !empty($_GPC['op'])?trim($_GPC['op']):'manage_index';
$data = $_GPC;

if($op=='manage_index'){
    //后台主页
    $title = '跨境新闻后台';

    if($data['pa']==1){
        $list = pdo_fetchall('select * from '.tablename('policy_category').' where `show`=0 and from_source=1 order by id desc');
        foreach($list as $k=>$v){
            $list[$k]['createtime'] = date('Y-m-d H:i',$v['createtime']);
            if(isset(json_decode($v['category_name'],true)['zh'])){
                $list[$k]['category_name'] = json_decode($v['category_name'],true)['zh'];
            }
        }
        die(json_encode(['code'=>0,'data'=>$list]));
    }
    include $this->template('policy/manage/manage_index');
}
elseif($op=='manage_logreg'){
    //登录注册页
    if($_W['ispost']){
        $user = pdo_fetch('select * from '.tablename('website_user').' where phone=:user_tel and merch_status=:user_status ',[':user_tel'=>trim($data['acc']),':user_status'=>0]);
        if(empty($user)){
            die(json_encode(['code'=>-1,'msg'=>'该商户不存在或未通过审核!']));
        }
//        pdo_update('enterprise_members',['uniacid'=>$_W['uniacid'],'openid'=>$openid],['mobile'=>trim($data['acc'])]);
//        pdo_update('total_merchant_account',['uniacid'=>$_W['uniacid'],'openid'=>$openid],['mobile'=>trim($data['acc'])]);
        die(json_encode(['code'=>1,'msg'=>'登录成功!']));
    }else{
        $title = '登录注册';

        //查看是否有此openid的enterprise_members
        $ishave = pdo_fetch('select id from '.tablename('website_user').' where openid=:openid and phone!=""',[':openid'=>$openid]);
        if(!empty($ishave)){
            header('Location:./index.php?i=3&c=entry&do=policy&p=manage&m=sz_yi&op=manage_index#wechat_redirect');
        }
        include $this->template('policy/manage/manage_logreg');
    }
}
elseif($op=='save_category'){
    //保存分类
    $id = intval($data['id']);

    if($id>0){
        $info = pdo_fetch('select * from '.tablename('policy_category').' where id=:id',[':id'=>$id]);
        if(isset(json_decode($info['category_name'],true)['zh'])){
            $info['category_name'] = json_decode($info['category_name'],true)['zh'];
        }
        $title=$info['category_name'];
    }else{
        $title='添加分类';
        $info['category_name']='';
    }

    if(isset($data['edit'])){
        if($id>0){
            //修改
            $res = pdo_update('policy_category',['category_name'=>trim($data['category_name']),'from_source'=>1],['id'=>$id]);
        }else{
            //添加
            $res = pdo_insert('policy_category',['category_name'=>trim($data['category_name']),'from_source'=>1, 'createtime'=>time()]);
        }
        if($res){
            die(json_encode(['code'=>0,'msg'=>'保存成功!']));
        }
    }
    include $this->template('policy/manage/save_category');
}
elseif($op=='del_category'){
    //删除分类
    $id = intval($data['id']);
    if(empty($id)){
        die(json_encode(['code'=>-1,'msg'=>'隐藏失败，缺少参数!']));
    }

    $res = pdo_update('policy_category',['show'=>1],['id'=>$id]);
    if($res){
        die(json_encode(['code'=>0,'msg'=>'隐藏成功!']));
    }
}
elseif($op=='news_list'){
    //新闻列表
    $cate_id = intval($data['cate_id']);
    $title='新闻列表';
    if($data['pa']==1){
        //管理员页面
        $list = pdo_fetchall('select * from '.tablename('policy_list').' where cate_id=:cate_id order by release_date desc',[':cate_id'=>$cate_id]);
        die(json_encode(['code'=>0,'data'=>$list]));
    }
    include $this->template('policy/manage/news_list');
}
elseif($op=='save_news') {
    //保存新闻
    $id = isset($data['id'])?intval($data['id']):0;
    $cate_id = intval($data['cate_id']);

    if($cate_id>0){
        $cate_name = pdo_fetchcolumn('select category_name from '.tablename('policy_category').' where id=:cate_id',[':cate_id'=>$cate_id]);
    }

    if($id>0){
        $info = pdo_fetch('select * from '.tablename('policy_list').' where id=:id',[':id'=>$id]);
        $info['content'] = json_decode($info['content'],true);
        $title = $info['name'];
    }else{
        $title = '添加新闻';
        $info = ['issuing_authority'=>'','document_number'=>'','name'=>'','release_date'=>'','effective_date'=>'','effect'=>'','effect_statement'=>'','link'=>'','content'=>'','avatar'=>'','desc'=>''];
    }

    if(isset($data['edit'])){
        if($id>0){
            //修改
            $res = pdo_update('policy_list',[
                'issuing_authority'=>trim($data['issuing_authority']),
                'document_number'=>trim($data['document_number']),
                'name'=>trim($data['name']),
                'release_date'=>trim($data['release_date']),
                'effective_date'=>trim($data['effective_date']),
                'effect'=>trim($data['effect']),
                'effect_statement'=>trim($data['effect_statement']),
                'link'=>trim($data['link']),
                'desc'=>trim($data['desc']),
                'content'=>json_encode(trim($data['editorValue']),true),
                'avatar'=>trim($data['pic_file'][0]),
                'from_source'=>1,
            ],['id'=>$id]);
        }else{
            //添加
            $res = pdo_insert('policy_list',[
                'cate_id'=>$cate_id,
                'issuing_authority'=>trim($data['issuing_authority']),
                'document_number'=>trim($data['document_number']),
                'name'=>trim($data['name']),
                'release_date'=>trim($data['release_date']),
                'effective_date'=>trim($data['effective_date']),
                'effect'=>trim($data['effect']),
                'effect_statement'=>trim($data['effect_statement']),
                'link'=>trim($data['link']),
                'desc'=>trim($data['desc']),
                'content'=>json_encode(trim($data['editorValue']),true),
                'avatar'=>trim($data['pic_file'][0]),
                'createtime'=>time(),
                'from_source'=>1,
            ]);
        }
        if($res){
            die(json_encode(['code'=>0,'msg'=>'保存成功!']));
        }
    }
    include $this->template('policy/manage/save_news');
}
elseif($op=='del_news'){
    //删除新闻
    $id = intval($data['id']);

    if(empty($id)){
        die(json_encode(['code'=>-1,'msg'=>'删除失败，缺少参数!']));
    }

    $res = pdo_delete('policy_list',['id'=>$id]);
    if($res){
        die(json_encode(['code'=>0,'msg'=>'删除成功!']));
    }
}
elseif($op=='check_auth'){
    //检测权限

//    $userAuth = pdo_fetchcolumn('select id from '.tablename('enterprise_members').' where openid=:openid and FIND_IN_SET(:atype,authType)',[':openid'=>$openid,':atype'=>$data['authType']]);
//
//    if($userAuth){
        die(json_encode(['code'=>0]));
//    }else{
//        die(json_encode(['code'=>-1,'msg'=>'权限不足，请联系管理员!']));
//    }
}