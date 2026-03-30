<?php
if (!(defined('IN_IA'))) {
    exit('Access Denied');
}

class Head{

    /*
     * 公告
     */
    public static function announcement($uid){
        $msg=pdo_fetchall("select * from ".tablename("foll_announcement")." order by id desc limit 10");
        return $msg;
    }



    /*
     * 广告
     */
    public static function carousel($uid,$tid){
        $time=time();
        $img=pdo_fetchall("select image from ".tablename("foll_advertising")." where ({$time}>=s_time and {$time}<=s_time) and (expiration=0 and position=1) limit 0,3");
        if(!empty($img)){
            return json_decode($img,true);
        }
        return null;
    }
}