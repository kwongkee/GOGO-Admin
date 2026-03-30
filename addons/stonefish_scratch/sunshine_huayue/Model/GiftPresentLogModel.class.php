<?php 
class GiftPresentLogModel
{
    static function addItem($data)
    {
        global $_W;
        $data['uniacid'] = $_W['uniacid'];
        $data['add_time'] = date('Y-m-d H:i:s');
        return pdo_insert('sunshine_huayue_gift_present_log', $data);
    }
}