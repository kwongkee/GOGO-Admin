<?php
if (!defined('IN_IA')) {
exit('Access Denied');
}

class Prepaids_EweiShopV2Page extends WebPage{
    public function main(){
        global $_W;
        global $_GPC;
        $data=pdo_fetchall("select * from ".tablename("parking_prepaid_price")." where uniacid={$_W['uniacid']}");
        include $this->template("parking/prepaid_list");
    }
    public function save(){
        global $_W;
        global $_GPC;
        $Data=[
            'Minute'=>$_GPC['min'],
            'Price'=>$_GPC['prices'],
            'PriceClass'=>$_GPC['classs'],
            'uniacid'=>$_W['uniacid']
        ];
        $result = pdo_insert('parking_prepaid_price', $Data);
        if (!empty($result)) {
            header("Location:".webUrl('parking/prepaids'));
//            message('添加成功，UID为' . $uid);
        }
    }
    public function del(){
        global $_W;
        global $_GPC;
        $result = pdo_delete('parking_prepaid_price', array('id'=> $_GPC['id'],'uniacid'=>$_W['uniacid']));
        if (!empty($result)) {
          echo '删除成功';
        }
    }
}