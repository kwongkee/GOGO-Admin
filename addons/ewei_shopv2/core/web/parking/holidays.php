<?php

if(!defined("IN_IA")){
  exit("Access Denied");

}
class Holidays_EweiShopV2Page extends WebPage
{
    public function main()
    {
    global $_W;
    $data=pdo_fetchall("select * from ".tablename('parking_holiday_schedule')." where uniacid=".$_W['uniacid']);
    include $this->template("parking/holiday_pirce");
    }
    public function save()
    {
        global $_W;
        global $_GPC;
        $data=['holiday_type'=>3,'pirce'=>$_GPC['price'],'uniacid'=>$_W['uniacid']];
        $result=pdo_insert("parking_holiday_schedule",$data);
        if(!empty($result)){
            message('添加成功');
        }
    }
    public function del()
    {
        global $_W;
        global $_GPC;
        $result = pdo_delete('parking_holiday_schedule', array('id' =>$_GPC['id'],'uniacid'=>$_W['uniacid']));
        if (!empty($result)) {
            echo "删除成功";
        }else{
            echo '失败';
        }
    }
}
