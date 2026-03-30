<?php

if (!(defined('IN_IA'))) {
    exit('Access Denied');
}

class Admin_EweiShopV2Page extends WebPage
{
    public function main(){
        global $_W;
        global $_GPC;
        if(!empty($_GPC['search'])){
            $participators=pdo_fetchall("select * from ".tablename("parking_admin_user")." where uniacid={$_W['uniacid']} and user='".$_GPC['search']."'");
        }else{
            $all=pdo_fetchall("SELECT count(id)as num FROM".tablename('parking_admin_user'));
            $pageindex=$_GPC['page']<1?1:intval($_GPC['page']);
            $pagesize=10;
            $pager=pagination($all[0]['num'],$pageindex,$pagesize);
            $p=($pageindex-1)*$pagesize;
            $participators=pdo_fetchall("select * from ".tablename("parking_admin_user")." where uniacid={$_W['uniacid']} order by id desc limit ".$p.','.$pagesize);
        }
        include $this->template("parking/admin_user");
    }
    public function adminUserAdd(){
        global $_W;
        global $_GPC;
        if(!$_W['isajax']){
            include $this->template("parking/admin_adduser");
        }else{
           if(empty($_GPC['tel'])){show_json(0,'手机号必填');}
           $data=[
               'uniacid'=>$_W['uniacid'],
               'user'=>$_GPC['username'],
               'passwd'=> md5($_GPC['pass']),
               'phone'=>$_GPC['tel'],
               'createtime'=>time(),
               'numbering'=>mt_rand(000000,999999)
           ];
           $boole=pdo_get("parking_admin_user",array('uniacid'=>$_W['uniacid'],'phone'=>$_GPC['tel']),array('id'));
           if(!empty($boole)){
               show_json(0,'已存在');
           }
           $inserBoole=pdo_insert("parking_admin_user",$data);
           if(!empty($inserBoole)){
               show_json(1,'添加成功');
           }
           show_josn(0,'添加失败');
        }
    }

    public function updateUserStatus(){
        global $_W;
        global $_GPC;
        if(pdo_update("parking_admin_user",array('status'=>$_GPC['status']),array('id'=>$_GPC['id']))){
            show_json(1);
        }
            show_json(0);
    }

    public function editUserInformation(){
        global $_W;
        global $_GPC;
        if($_W['isajax']){
            if(empty($_GPC['tel'])){show_json(0,'请填写必填项');}
            if(empty($_GPC['pass'])){
                $bool=pdo_update("parking_admin_user",array('phone'=>$_GPC['tel'],'user'=>$_GPC['username']),array('id'=>$_GPC['id']));
            }else{
                $pass=md5($_GPC['pass']);
                $bool=pdo_update("parking_admin_user",array('phone'=>$_GPC['tel'],'user'=>$_GPC['username'],'passwd'=>$pass),array('id'=>$_GPC['id']));
            }
            if(!empty($bool)){
                show_json(1);
            }
            show_json(0);
        }else{
            if(!empty($_GPC['id'])){
                $userData=pdo_get("parking_admin_user",array('id'=>$_GPC['id'],'uniacid'=>$_W['uniacid']));
            }
            include $this->template("parking/admin_updateuser");
        }
    }

    public function delUser(){
        global $_W;
        global $_GPC;
        if(pdo_delete("parking_admin_user",array('id'=>$_GPC['id'],'uniacid'=>$_W['uniacid']))){
            show_json(1);
        }
        show_json(0);
    }

    public function admin_list(){
        global $_W;
        $data=pdo_fetchall("select id,user from .".tablename("parking_admin_user")." where uniacid={$_W['uniacid']}");
        if(!empty($data)){
            show_json(1,$data);
        }
        show_json(0,"请先添加管理员");
    }


    public function addParkingAdminUser(){
        global $_W;
        global $_GPC;
        if(!empty($_GPC['id'])&&!empty($_GPC['aid'])){
            $sql="update ".tablename("parking_space")." set admin_id=".$_GPC['aid']." where id in(".rtrim($_GPC['id'],',').")";
            if(!empty(pdo_query($sql))){
                show_json(1);
            }
            show_json(0);
        }
    }



}