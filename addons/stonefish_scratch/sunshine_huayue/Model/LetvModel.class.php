<?php 
class LetvModel
{
    static function addItem($data)
    {
        global $_W;
        $data['uniacid'] = $_W['uniacid'];
        $data['add_time'] = date('Y-m-d H:i:s');
        return pdo_insert('sunshine_huayue_letv', $data);
    }
    static function info($activity_id, $rid)
    {
        global $_W;
        return pdo_fetch('select * from ' . tablename('sunshine_huayue_letv') . " where uniacid={$_W['uniacid']} and activity_id='{$activity_id}' and rid='{$rid}'");
    }
}