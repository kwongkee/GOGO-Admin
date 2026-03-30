<?php

/**
 * 模块处理程序
 *
 * @author lirui
 * @url http://bbs.we7.cc/
 */
defined('IN_IA') or exit('Access Denied');

class Wxz_shoppingmallModuleProcessor extends WeModuleProcessor {

    public function respond() {
        global $_W;
        if ($this->module['config']['attach_url']) {
            $attach_url = $this->module['config']['attach_url'];
        } else {
            $attach_url = $_W['siteroot'] . '/' . $_W['config']['upload']['attachdir'];
        }

        $item = pdo_fetch("select * from " . tablename('wxz_shoppingmall_reply_setting') . " where rid = " . $this->rule . " AND isdel=0 and uniacid = " . $_W['uniacid']);
        $respon = array(
            'Title' => $item['title'],
            'Description' => $item['desc'],
            'PicUrl' => $attach_url . $item['img'],
            'Url' => $item['link'],
        );
        return $this->respNews($respon);
    }

}
