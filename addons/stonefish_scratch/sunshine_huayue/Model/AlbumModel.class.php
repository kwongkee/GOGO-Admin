<?php 
class AlbumModel
{
    static function getSliceList($openid, $num, $offset = 4)
    {
        global $_W;
        $list = pdo_fetchall('select * from ' . tablename('sunshine_huayue_album') . " where openid='{$openid}' and is_del='n' and uniacid={$_W['uniacid']} order by add_time desc limit {$num}");
        if (count($list) % 4 != 0) {
            $list = array_slice($list, 0, count($list) - count($list) % 4);
        }
        return $list;
    }
}