<?php
if (!defined('IN_IA')) {
    exit('Access Denied');
}

global $_W, $_GPC;
header("content-type:text/html;charset=gb2312");
require "common.php";
require_once __DIR__ . "/downloadMedia.php";
require_once __DIR__ . '/qiniusdk/autoload.php';
load()->func('communication');
load()->func('file');
$server_id = $_GPC['server_id'];
$downMedia = new downloadMedia();
// $filename = Download_media($server_id);
// $filePath = ATTACHMENT_ROOT . $filename;
$filePath = $downMedia->download_media($server_id);
chmod($filePath,777);
use Qiniu\Auth;
use Qiniu\Storage\UploadManager;
use Qiniu\Storage\BucketManager;

$data = array();

if (file_exists($filePath) && $config['audio_open'] == '1') {

    //上传七牛转码----------------------
    $numberid = date('YmdHis');

    //公匙
    $accessKey = trim($config['audio_accesskey']);
    //私匙
    $secretKey = trim($config['audio_secretkey']);
    //创建AUth对象进行调用
    $auth = new Auth($accessKey, $secretKey);
    //空间名称和队列名称
    $bucket = trim($config['audio_bucket']);
    $pipeline = trim($config['audio_pipeline']);
    //转码成功后的名字，可随程序进行改变也可自定义。
    $successkey = 'wxsuccess_' . $numberid;
    //不指定默认保存在当前空间，bucket为目标空间，后一个参数为转码之后文件名
    $savekey = \Qiniu\base64_urlSafeEncode($bucket . ':' . $successkey . '.mp3');

    //设置转码参数此处为将文件转码为音频文件且为mp3格式。其他类型格式说明请见（https://developer.qiniu.com/dora）
    $fops = "avthumb/mp3/ab/320k/ar/44100/acodec/libmp3lame";
    $fops = $fops . '|saveas/' . $savekey;
    $policy = array(
        'persistentOps' => $fops,
        'persistentPipeline' => $pipeline
    );

    //指定上传转码命令
    $uptoken = $auth->uploadToken($bucket, null, 3600, $policy);
    //$key = $mediaid.'.amr'; //七牛云中保存的amr文件名（本处原需求为将微信上传录音下载到本地服务器然后上传到七牛云进行.amr=>.mp3格式转化操作）
    // 要上传文件的本地路径
    //$filePath = ATTACHMENT_ROOT.'audios/2/2018/02/y0Tq52MqHgzRgZB1GgqqBb3OB1O9q2.amr';

    // 上传到七牛后保存的文件名
    $key = 'my-wx' . $numberid . '.amr';
    $uploadMgr = new UploadManager();

    //上传文件并转码$filePath为本地文件路径
    list($ret, $err) = $uploadMgr->putFile($uptoken, $key, $filePath);
    if ($err !== null) { //失败
        @file_put_contents(__DIR__ . "/audio.log", $err->message() . '|' . $filename . "\n", FILE_APPEND);
        //return false;
        //var_dump($savekey);
        $data['baseUrl'] = '';
    } else {//成功
        //此时七牛云中同一段音频文件有amr和MP3两个格式的两个文件同时存在，为节省空间,删除amr格式文件
        $bucketMgr = new BucketManager($auth);
        //删除
        // $bucketMgr->delete($bucket, $key);
        // var_dump($savekey);
        //此处需要查看你的空间是私有空间还是公有空间，如果是公有直接拼接使用，如果是私有，构造私有空间的需要生成的下载的链接，你绑定定在空间的域名 加 要下载的文件名
        $baseUrl = 'http://' . $config['audio_urlt'] . '/' . $successkey . '.mp3';
        //私有空间处理方法
        if ($config['audio_bucket_type'] == '1') {
            $baseUrl = $auth->privateDownloadUrl($baseUrl);
        }

        //删除本地amr格式文件
        // file_delete($filePath);
        $mp3save_path = ATTACHMENT_ROOT . '/audios/' . $_W['uniacid'] . '/' . date('Y', time()) . '/' . date('m', time());
        if (@fopen($baseUrl, 'r')) {
            //将七牛云转码好的下载到本地
            $mp3_file_name = random_filename('audio', 'mp3');
            ob_start();
            readfile($baseUrl);
            $img = ob_get_contents();
            ob_end_clean();
            $size = strlen($img);
            $fp = fopen($mp3save_path .'/'. $mp3_file_name, 'a');
            fwrite($fp, $img);
            fclose($fp);

        }
        if (file_exists($mp3save_path .'/'. $mp3_file_name)) {
            $data['mp3_filename'] = $mp3save_path .'/'. $mp3_file_name;
        } else {
            $data['mp3_filename'] = '';
        }

        $data['baseUrl'] = $baseUrl;

    }
    //----------------------------------
    $data['filePath'] = $filePath;
}else{
    //使用ffmpeg
    $mp3_file_name = random_filename('audio', 'mp3');
    $mp3save_path = ATTACHMENT_ROOT.'/'.$mp3_file_name;
    $command = "/usr/local/bin/ffmpeg -i " . $filePath . " " . $mp3save_path." 2>&1";
    $resu = shell_exec($command);
    @file_put_contents(__DIR__ . "/audio.log", $resu.'|命令|'.$command. "\n", FILE_APPEND);
    $data['baseUrl'] = 'http://shop.gogo198.cn/attachment/'.$mp3_file_name;
    $data['filePath'] = 'attachment/audios/' . $_W['uniacid'] . '/' . date('Y', time()) . '/' . date('m', time()).'/'.$mp3_file_name;
    $data['mp3_filename'] = 'attachment/audios/' . $_W['uniacid'] . '/' . date('Y', time()) . '/' . date('m', time()).'/'.$mp3_file_name;
}

if (empty($filePath) && empty($baseUrl)) {
    $data['result'] = 'error';
} else {
    $data['result'] = 'ok';
}

echo json_encode($data);
exit();