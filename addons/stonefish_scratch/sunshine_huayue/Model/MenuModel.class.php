<?php 
class MenuModel
{
    static function addItem($data)
    {
        return pdo_insert('sunshine_huayue_menu', $data);
    }
    static function getListByType($type)
    {
        global $_W;
        return pdo_fetchall('select * from ' . tablename('sunshine_huayue_menu') . " where uniacid='{$_W['uniacid']}' and type='{$type}' and is_del='n' order by order_id desc");
    }
    static function updateItem($data, $where)
    {
        global $_W;
        return pdo_update('sunshine_huayue_menu', $data, $where);
    }
}