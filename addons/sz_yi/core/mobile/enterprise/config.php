<?php
// 模块LTD提供
if (!defined('IN_IA')) {
    exit('Access Denied');
}
global $_W;
global $_GPC;

$op = (!empty($_GPC['op']) ? $_GPC['op'] : 'display');
$openid = m('user')->getOpenid();
$popenid = m('user')->islogin();
$openid = ($openid ? $openid : $popenid);
$member = m('member')->getMember($openid);
$uniacid = $_W['uniacid'];

//1、先判断当前账号是否商户并且是否管理员
$user = pdo_fetch('select id from '.tablename('decl_user').' where openid=:openid and user_status=0',[':openid'=>$openid]);
if(empty($user)){
    header('Location:./index.php?i=3&c=entry&do=enterprise&m=sz_yi&p=register');
}

if($op=='display'){
    //操作员配置

    if($_GPC['pa']==1){
        //获取操作员列表
        $limit = intval($_GPC['limit']);
        $page = intval($_GPC['page']) - 1;
        if ($page != 0) {
            $page = $limit * $page;
        }
        $keyword = trim($_GPC['keywords']) ? trim($_GPC['keywords']) : '';
        $condition = '';
        if($keyword){
            $condition = ' and a.mobile = "'.$keyword.'"';
        }

        $list = pdo_fetchall('select a.name,a.id,a.mobile,cc.name as job_name from '.tablename('enterprise_staff').' a left join '.tablename('enterprise_members').' b on b.id=a.enterprise_id left join '.tablename('merchant_job_config').' cc on cc.id=a.job_id where b.openid=:openid '.$condition.' order by createtime desc',[':openid'=>$_W['openid']]);
        $count = pdo_fetch('select count(a.id) as c from '.tablename('enterprise_staff').' a left join '.tablename('enterprise_members').' b on b.id=a.enterprise_id left join '.tablename('merchant_job_config').' c on c.id=a.job_id where b.openid=:openid '.$condition,[':openid'=>$_W['openid']]);
        die(json_encode(['code'=>0,'count'=>$count['c'],'data'=>$list]));
    }
    include $this->template('enterprise/config');
}elseif($op=='get_user'){


}elseif($op=='save_info'){
    $id = intval($_GPC['id']);
    if($_GPC['pa']==1){
        $dat = $_GPC;
        //查询该岗位下，员工的限制数量
        $job_info = pdo_fetch('select * from '.tablename('merchant_job_config').' where id=:id',[':id'=>$dat['job']]);
        $job_limit_num = pdo_fetchcolumn('select count(a.id) from '.tablename('enterprise_staff').' a left join '.tablename('enterprise_members').' b on b.id=a.enterprise_id where b.openid=:openid and a.job_id=:job_id',[':openid'=>$_W['openid'],':job_id'=>$job_info['id']]);
        if($job_limit_num>=$job_info['num']){
            show_json(-1,['msg'=>'操作失败，该岗位['.$job_info['name'].']添加已经到达系统上限！']);
        }

        if(empty($dat['id'])){
            //添加
            $ishaveMem = pdo_fetch('select id from '.tablename('sz_yi_member').' where mobile=:mob',[':mob'=>trim($dat['mobile'])]);
            if(empty($ishaveMem['id'])){
                pdo_insert('sz_yi_member',['uniacid'=>3,'level'=>0,'createtime'=>time(),'realname'=>trim($dat['name']),'mobile'=>trim($dat['mobile']),'pwd'=>md5(88888)]);
            }
            //插入该岗位
            $basicinfo = pdo_fetch('select id from '.tablename('enterprise_members').' where openid=:openid',[':openid'=>$_W['openid']]);
            $res = pdo_insert('enterprise_staff',['name'=>trim($dat['name']),'mobile'=>trim($dat['mobile']),'job_id'=>intval($dat['job']),'createtime'=>time(),'enterprise_id'=>$basicinfo['id']]);
            if($res){
                //发送短信，扫描二维码关联微信，待做

                show_json(1,['msg'=>'添加成功！']);
            }
        }elseif($dat['id']>0){
            //编辑

        }
    }

    $data = [];
    if($id>0){
        $data = pdo_fetch('select * from '.tablename('enterprise_staff').' where id=:id',[':id'=>$id]);
    }
    $job_list = pdo_fetchall('select * from '.tablename('merchant_job_config').' where `name`!=:namee order by id desc',[':namee'=>'管理员']);

    include $this->template('enterprise/save_info');
}elseif($op=='del_staff'){
    //删除员工
    $id = intval($_GPC['id']);
    $res = pdo_delete('enterprise_staff',['id'=>$id]);
    if($res){
        show_json(1,['msg'=>'删除员工成功！']);
    }
}