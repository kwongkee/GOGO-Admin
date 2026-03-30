<?php

/**
 *
 */
 if (!(defined('IN_IA'))) {
 	exit('Access Denied');
 }

class Index_EweiShopV2Page extends Page
{

    function main()
    {
        global $_W;
        global $_GPC;
        $all=pdo_fetchall("SELECT `id` FROM".tablename('parking_verified')."WHERE audit_status='0' ORDER BY `id` DESC");
        $total=count($all);
        $pageindex=$_GPC['page']<1?1:intval($_GPC['page']);
        $pagesize=10;
        $pager=pagination($total,$pageindex,$pagesize);
        $p=($pageindex-1)*$pagesize;
        $participators=pdo_fetchall("SELECT * FROM ".tablename('parking_verified')."WHERE audit_status='0' ORDER BY `id` DESC LIMIT ".$p.','.$pagesize);
        include $this->template('parking/signing');
    }

    function Save(){
        global $_W;
        global $_GPC;
        $province=explode(":",$_GPC['resProvince'])['0'];
        $city=explode(':',$_GPC['resCity'])['0'];
        $area=explode(":",$_GPC['resArea'])['0'];
        $num=$_GPC['num'];
        $addr=$_GPC['addr'];
        $dz=$province.$city.$area.$addr;
        if($province!=''||$num!=''||$addr!=''){
            $data=[
                'addr'=>$dz,
                'deednum'=>$num,
                'audit_status'=>'1',
            ];
            $innerId=pdo_update("parking_verified",$data,array('id'=>$_GPC['id']));
            if($innerId){
                show_json('1','提交成功');
            }else{
                show_json('0','提交失败');
            }

        }
    }
}
