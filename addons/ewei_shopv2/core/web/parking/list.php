<?php



if(!defined("IN_IA")){
  exit("Access Denied");

}

/**
 *
 */
class List_EweiShopV2Page extends WebPage
{

  public function main()
  {
    global $_W;
    global $_GPC;
    if(isset($_GPC['contr'])&&isset($_GPC['search'])){
        // $nid= intval($_GPC['contr']);
        $num=$_GPC['search'];
        $participators=pdo_fetchall("select t1.id,t1.numbers,t1.status,t1.createtime,t2.Province,t2.City,t2.Area,t2.Town,t2.Committee,t2.Road,t2.Road_num,t3.ChargeClass,t4.user from ims_parking_space t1 left join ims_parking_position t2 on t2.id = t1.pid left join ims_parking_Charge t3 on t1.Cid = t3.id left join ims_parking_admin_user t4 on t1.admin_id=t4.id where t1.uniacid={$_W['uniacid']} and t1.numbers=CONCAT('{$num}')");
    }else{
        $all=pdo_fetchall("SELECT `id` FROM".tablename('parking_space'));
        $total=count($all);
        $pageindex=$_GPC['page']<1?1:intval($_GPC['page']);
        $pagesize=10;
        $pager=pagination($total,$pageindex,$pagesize);
        $p=($pageindex-1)*$pagesize;
        $participators=pdo_fetchall("select t1.id,t1.numbers,t1.status,t1.createtime,t2.Province,t2.City,t2.Area,t2.Town,t2.Committee,t2.Road,t2.Road_num,t3.ChargeClass,t4.user from ims_parking_space t1 left join ims_parking_position t2 on t2.id = t1.pid left join ims_parking_Charge t3 on t1.Cid = t3.id left join ims_parking_admin_user t4 on t1.admin_id=t4.id where t1.uniacid={$_W['uniacid']} order by id desc limit ".$p.",".$pagesize);
        }
    include $this->template("parking/parking_list");
  }

  public function Park_add()
  {
    global $_W;
    global $_GPC;
    $addr=pdo_getall('parking_deploy_district',array('pid'=>0),array('id','name'));
    $data=pdo_fetchall("select id,ChargeClass from ".tablename("parking_Charge")."where uniacid=".$_W['uniacid']);
//    $datas=pdo_fetchall("select * from ".tablename("parking_prepaid_price")." where uniacid=".$_W['uniacid']);
    include $this->template("parking/parking_add");
  }

  public function Park_save()
  {
    global $_W;
    global $_GPC;
    if($_W['isajax']){
        $num=empty($_GPC['numbers'])?mt_rand(0000,9999):$_GPC['numbers'];
         $positon=[
           'Province'=>explode(":",$_GPC['provin'])['1'],
           'City'=>explode(":",$_GPC['citys'])['1'],
           'Area'=>explode(":",$_GPC['area'])['1'],
           'Town'=>explode(":",$_GPC['town'])['1'],
           'Committee'=>explode(":",$_GPC['committee'])['1'],
           'Road'=>$_GPC['road'],
           'Road_num'=>$_GPC['road_num'],
           'uniacid'=>$_W['uniacid'],
           'createtime'=>time(),
         ];
         $longCode=pdo_get("parking_deploy_district",array('id'=>$_GPC['committee']),array('longcode'));
         $numbers='0'.$_W['uniacid'].$longCode['longcode'].$_GPC['road_num'].$num;
         $inser=pdo_insert("parking_position",$positon);
        if(!empty($inser)){
         $space=[
            'pid'=>pdo_insertid(),
            'Cid'=>$_GPC['ChargeClass'],
            'parkingnumber'=>$numbers,
            'numbers'=>$num,
            'uniacid'=>$_W['uniacid'],
            'createtime'=>time(),
         ];
         $result=pdo_insert("parking_space",$space);
         if(!empty($result)){
           show_json('1','添加成功'.$num);
         }else{
           show_json('0','添加失败');
         }
        }

    }
  }

  public function Linkage_address(){
    global $_W;
    global $_GPC;
    show_json(pdo_getall('parking_deploy_district',array('pid'=>explode(":",$_GPC['pid'])['0']),array('id','name')));
  }

  public function Park_del(){
    global $_W;
    global $_GPC;
    $pid=pdo_get("parking_space",array('id'=>$_GPC['id']),array('pid'));
    $result=pdo_delete("parking_space",array('id'=>$_GPC['id']));
    $res=pdo_delete("parking_position",array('id'=>$pid['pid']));
    if(!empty($result)||!empty($res)){
      show_json('1');
    }
  }

  public function Par_edit()
  {
      global $_W;
      global $_GPC;
     $data=array();
     $res=pdo_fetchall("select *  from ims_parking_space  where id in(".trim($_GPC['id'],',').")");
     $data['c']=pdo_fetchall("select id,ChargeClass from ".tablename("parking_Charge")."where uniacid=".$_W['uniacid']);
//     $data['pp']=pdo_fetchall("select * from ".tablename('parking_prepaid_price')." where uniacid={$_W['uniacid']}");
     foreach ($res as $key => $value) {
         $data['p'].=$value['numbers'].',';
         $data['id'].=$value['id'].',';
     }
     include $this->template("parking/parking_edit");
  }
  public function Edit_Save()
  {
      global $_W;
      global $_GPC;
      $up=pdo_query("UPDATE ims_parking_space SET Cid={$_GPC['ChargeClass']} WHERE id in(".trim($_GPC['ids'],',').")");
      if(!$up){
          show_json('0');
          exit();
      }
      show_json('1');
  }

}
