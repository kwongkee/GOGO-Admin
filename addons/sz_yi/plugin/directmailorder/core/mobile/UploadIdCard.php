<?php


global $_W;
global $_GPC;

load()->func('file');

function image_reques($base64)
{
    $url     = "https://dm-51.data.aliyun.com/rest/160601/ocr/ocr_idcard.json";
    $appcode = "504fd5f6a735437c97cd117e61cb4a24";
    $headers = [];
    array_push($headers, "Authorization:APPCODE ".$appcode);
    //根据API的要求，定义相对应的Content-Type
    array_push($headers, "Content-Type".":"."application/json; charset=UTF-8");
    $request = [
        "image"     => [
            "dataType"  => 50,
            "dataValue" => $base64,
        ],
        "configure" => [
            "dataType"  => 50,
            "dataValue" => "{\"side\":\"face\"}",
        ],
    ];
    $body    = json_encode(["inputs" => [$request]]);
    $curl    = curl_init();
    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($curl, CURLOPT_FAILONERROR, false);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_HEADER, true);
    if (1 == strpos("$".$url, "https://")) {
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
    }
    curl_setopt($curl, CURLOPT_POSTFIELDS, $body);
    $result      = curl_exec($curl);
    $header_size = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
    $rbody       = substr($result, $header_size);
    $httpCode    = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);
    if ($httpCode == 200) {
        return $rbody;
    } else {
        return sprintf("Error msg in body: %s\n", $rbody);
    }
}

$recipient = pdo_get('member_family', ['id' => $_GPC['name']], ['name']);//获取收件人
$path      = 'images/'.$_W['uniacid'].'/'.date('Y').'/'.date('m').'/';
$res       = file_upload($_FILES['file'], 'image', $path.$recipient['name'].'_'.$_GPC['type']);
if ((isset($res['errno']) && $res['errno'] == '-1')) {
    show_json(1, $res['message']);
} else if ($res['success'] != true) {
    show_json(1, '上传失败');
}
if ($_GPC['type'] == 'face') {
    $fileConten = base64_encode(@file_get_contents(ATTACHMENT_ROOT.'/'.$res['path']));
    $data       = image_reques($fileConten);
    @file_put_contents('../data/logs/yanz_directmail.txt', date('Y-m-d H:i:s', time()).'----'.$data."\n", FILE_APPEND);
    $data = json_decode(json_decode($data, true)["outputs"][0]["outputValue"]["dataValue"], true);
    if ((empty($data)) || (!$data['success'])) {
        show_json(1, '识别失败');
    }
    if ($recipient['name'] != $data['name']) {
        show_json(1, '上传身份与收件人不匹配');
    }
}
show_json(0, '验证通过');