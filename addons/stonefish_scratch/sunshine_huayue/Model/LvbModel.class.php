<?php 
class LvbModel
{
    static function addItem($data)
    {
        global $_W;
        $data['uniacid'] = $_W['uniacid'];
        $data['add_time'] = date('Y-m-d H:i:s');
        return pdo_insert('sunshine_huayue_lvb', $data);
    }
    static function info($channel_id, $rid)
    {
        global $_W;
        return pdo_fetch('select * from ' . tablename('sunshine_huayue_lvb') . " where uniacid={$_W['uniacid']} and channel_id='{$channel_id}' and rid='{$rid}'");
    }
}