<?php 
class AdminModel
{
    static function getList()
    {
        global $_W;
        static $list = array();
        if (!$list) {
            $list = pdo_fetchall('select * from ' . tablename('sunshine_huayue_admin') . " where uniacid={$_W['uniacid']} and is_del='n'");
        }
        return $list;
    }
    static function isAdmin($openid)
    {
        $info = self::info($openid);
        if ($info) {
            return true;
        } else {
            return false;
        }
    }
    static function info($openid)
    {
        global $_W;
        static $infos = array();
        if (!isset($infos[$openid])) {
            $infos[$openid] = pdo_fetch('select * from ' . tablename('sunshine_huayue_admin') . " where uniacid={$_W['uniacid']} and is_del='n' and openid='{$openid}'");
        }
        return $infos[$openid];
    }
}