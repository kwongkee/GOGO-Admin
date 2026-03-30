<?php

if (!defined('IN_IA')) {
    exit('Access Denied');
}

header("content-type:text/html;charset=gb2312");

global $_W, $_GPC;
/**
 * 生成毫秒级时间戳
 */
function msectime()
{
    list($msec, $sec) = explode(' ', microtime());
    return (float)sprintf('%.0f', (floatval($msec) + floatval($sec)) * 1000);
}

/**
 * 随机取出字符串
 * @param  int $strlen 字符串位数
 * @return string
 */
function salt($strlen)
{
    $str  = "abcdefghkmnprstuvwxyzABCDEFGHKMNPRSTUVWXYZ23456789";
    $salt = '';
    $_len = strlen($str)-1;
    for ($i = 0; $i < $strlen; $i++) {
        $salt .= $str[mt_rand(0,$_len)];
    }
    return $salt;
}
if (empty($_GPC['upfile_b64'])){
    echo json_encode(['status'=>1,'result'=>'上传失败']);
    exit();
}
// 本地保存目录
$save_path = '/audios/'.$_W['uniacid'].'/'.date('Y',time()).'/'.date('m',time());
if( !is_dir(ATTACHMENT_ROOT.$save_path) ) {
    mkdir(iconv('UTF-8', 'GBK',ATTACHMENT_ROOT.$save_path), 0777, TRUE);
}
// 生成文件名
$filename = msectime() . salt(6) . '.mp3';
// 写入文件流到本地
file_put_contents(ATTACHMENT_ROOT.$save_path . '/' . $filename, base64_decode($_GPC['upfile_b64']));
echo json_encode(['status'=>0,'result'=>'https://shop.gogo198.cn/attachment'.$save_path.'/'.$filename]);
exit();
