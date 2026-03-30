<?php

/**
 * 消息相关类
 */
class Msg {

    /**
     * 添加用户消息
     * @param type $uid
     * @param type $type 1.系统消息 2.转赠积分 3.小票审核通过
     * @param type $title
     * @param type $desc
     */
    public static function addMsg($uid, $type, $title, $desc) {
        global $_W;
        if (!$uid || !$title || !$type) {
            return;
        }
        $insertMsgData = array(
            'uniacid' => $_W['uniacid'],
            'type' => $type,
            'title' => $title,
            'desc' => $desc,
            'create_at' => time(),
        );
        pdo_insert('wxz_shoppingmall_msg', $insertMsgData);
        $msgId = pdo_insertid();
        //插入用户信息表
        $insertMsgUserData = array(
            'uniacid' => $_W['uniacid'],
            'fans_id' => $uid,
            'msg_id' => $msgId,
            'create_at' => time(),
        );
        return pdo_insert('wxz_shoppingmall_fans_msg', $insertMsgUserData);
    }

}
