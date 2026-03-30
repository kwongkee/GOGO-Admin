<?php 
class VoiceLogMode
{
    static function addItem($openid, $r_log_id, $voice_path)
    {
        global $_W;
        $data['uniacid'] = $_W['uniacid'];
        $data['openid'] = $openid;
        $data['r_log_id'] = $r_log_id;
        $data['voice_path'] = $voice_path;
        $data['add_time'] = date('Y-m-d H:i:s');
        return pdo_insert('sunshine_huayue_voice_log', $data);
    }
    static function getInfo($r_log_id)
    {
        global $_W;
        return pdo_fetch('select * from ' . tablename('sunshine_huayue_voice_log') . " where uniacid='{$_W['uniacid']}' and r_log_id='{$r_log_id}'");
    }
}