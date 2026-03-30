<?php 
class GiftOrderModel
{
    function __construct()
    {
    }
    static function addItem($data)
    {
        global $_W;
        $data['uniacid'] = $_W['uniacid'];
        $data['add_time'] = date('Y-m-d H:i:s');
        $r = pdo_insert('sunshine_huayue_gift_order', $data);
        if ($r) {
            return pdo_insertid();
        }
        return false;
    }
    static function info($id)
    {
        global $_W;
        return pdo_fetch('select * from ' . tablename('sunshine_huayue_gift_order') . " where uniacid='{$_W['uniacid']}' and id='{$id}'");
    }
    static function getList($openid)
    {
        global $_W;
        return pdo_fetchall('select * from ' . tablename('sunshine_huayue_gift_order') . " where uniacid='{$_W['uniacid']}' and openid='{$openid}' order by add_time desc");
    }
}