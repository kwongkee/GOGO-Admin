<?php


if(!defined("IN_IA")){
  exit("Access Denied");

}

class User_EweiShopV2Page extends WebPage
{
  public function main()

  {   global $_W;
      global $_GPC;
      $all=pdo_fetchall("SELECT `id` FROM".tablename('parking_authorize'));
      $total=count($all);
      $pageindex=$_GPC['page']<1?1:intval($_GPC['page']);
      $pagesize=10;
      $pager=pagination($total,$pageindex,$pagesize);
      $p=($pageindex-1)*$pagesize;
//      LIMIT ".$p.','.$pagesize
      $OrderNum=pdo_fetchall("SELECT COUNT(*) as num FROM ".tablename('parking_authorize')." tb1 LEFT JOIN ".tablename('parking_order')." tb2 ON tb2.openid=tb1.openid where tb1.uniacid={$_W['uniacid']} and pay_status='0'");
      $UserRes=pdo_fetchall("select * from ".tablename("parking_authorize")." where uniacid=".$_W['uniacid']." LIMIT ".$p.','.$pagesize);
      foreach ($UserRes as $key => &$value) {
          $value['auth_type']=unserialize($value['auth_type']);
          $value['createtime']=date("Y-m-d H:i:s",$value['createtime']);
      }
      $type=['Credit_Card'=>'银联'];
      include $this->template("parking/user/index");
  }


  // public function User_add()
  // {
  //   global $_W;
  //   global $_GPC;
  //   include $this->template("parking/user/add");
  // }
  //

}
