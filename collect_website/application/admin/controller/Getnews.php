<?php
namespace app\admin\controller;

use app\admin\controller;
use think\Request;
use think\Db;
use WebSocket\Client;

class Getnews extends Auth
{
    function xfyun(){
        $addr = "wss://spark-api.xf-yun.com/v3.1/chat";
        //密钥信息，在开放平台-控制台中获取：https://console.xfyun.cn/services/cbm
        $Appid = "3a8d3d36";
        $Apikey = "98f52ae7f736acf64cdbfd4e53ec6fc8";
        // $XCurTime =time();
        $ApiSecret ="MjU0MTgwYWUyY2Q1OWY5Y2E0Y2E3ZTJm";
        // $XCheckSum ="";

        // $data = $this->getBody("你是谁？");
        $authUrl = $this->assembleAuthUrl("GET",$addr,$Apikey,$ApiSecret);
        //创建ws连接对象
        $client = new Client($authUrl);

        // 连接到 WebSocket 服务器
        if ($client) {
            try {
                // 发送数据到 WebSocket 服务器
                $data = $this->getBody($Appid,"依据国内外的网络最新资讯，整理(昨天)2023年11月8日有关电商包裹跨境集运的新闻资讯，要求：以发布时间由早到晚为索引单列，列表内容包括：资讯标题、发布时间、资讯出处、发布链接、不多于50个字的内容概要");
                $client->send($data);

                // 从 WebSocket 服务器接收数据
                $answer = "";
                while(true){
                    $response = $client->receive();
                    $resp = json_decode($response,true);
                    $code = $resp["header"]["code"];
                    echo "从服务器接收到的数据： " . $response;
                    if(0 == $code){
                        $status = $resp["header"]["status"];
                        if($status != 2){
                            $content = $resp['payload']['choices']['text'][0]['content'];
                            $answer .= $content;
                        }else{
                            $content = $resp['payload']['choices']['text'][0]['content'];
                            $answer .= $content;
                            $total_tokens = $resp['payload']['usage']['text']['total_tokens'];
                            print("\n本次消耗token用量：\n");
                            print($total_tokens);
                            break;
                        }
                    }else{
                        echo "服务返回报错".$response;
                        break;
                    }
                }

                print("\n返回结果为：\n");
                print($answer);
            } catch (Exception $e) {
                echo "WebSocket连接或数据发送出错：" . $e->getMessage();
            }
        } else {
            echo "无法连接到 WebSocket 服务器";
        }
    }

    /**
     * 发送post请求
     * @param string $url 请求地址
     * @param array $post_data post键值对数据
     * @return string
     */
    function http_request($url, $post_data, $headers) {
        $postdata = http_build_query($post_data);
        $options = array(
            'http' => array(
                'method' => 'POST',
                'header' => $headers,
                'content' => $postdata,
                'timeout' => 15 * 60 // 超时时间（单位:s）
            )
        );
        $context = stream_context_create($options);
        $result = file_get_contents($url, false, $context);

        echo $result;

        return "success";
    }

    //构造参数体
    function getBody($appid,$question){
        $header = array(
            "app_id" => $appid,
            "uid" => "12345"
        );

        $parameter = array(
            "chat" => array(
                "domain" => "generalv3",
                "temperature" => 0.5,
                "max_tokens" => 2048
            )
        );

        $payload = array(
            "message" => array(
                "text" => array(
                    // 需要联系上下文时，要按照下面的方式上传历史对话
                    // array("role" => "user", "content" => "你是谁"),
                    // array("role" => "assistant", "content" => "....."),
                    // ...省略的历史对话
                    array("role" => "user", "content" => $question)
                )
            )
        );

        $json_string = json_encode(array(
            "header" => $header,
            "parameter" => $parameter,
            "payload" => $payload
        ));

        return $json_string;

    }
    //鉴权方法
    function assembleAuthUrl($method, $addr, $apiKey, $apiSecret) {
        if ($apiKey == "" && $apiSecret == "") { // 不鉴权
            return $addr;
        }

        $ul = parse_url($addr); // 解析地址
        if ($ul === false) { // 地址不对，也不鉴权
            return $addr;
        }
        // // $date = date(DATE_RFC1123); // 获取当前时间并格式化为RFC1123格式的字符串
        $timestamp = time();
        $rfc1123_format = gmdate("D, d M Y H:i:s \G\M\T", $timestamp);
        // $rfc1123_format = "Mon, 31 Jul 2023 08:24:03 GMT";


        // 参与签名的字段 host, date, request-line
        $signString = array("host: " . $ul["host"], "date: " . $rfc1123_format, $method . " " . $ul["path"] . " HTTP/1.1");

        // 对签名字符串进行排序，确保顺序一致
        // ksort($signString);

        // 将签名字符串拼接成一个字符串
        $sgin = implode("\n", $signString);
//        print( $sgin);
        // 对签名字符串进行HMAC-SHA256加密，得到签名结果
        $sha = hash_hmac('sha256', $sgin, $apiSecret,true);
//        print("signature_sha:\n");
//        print($sha);
        $signature_sha_base64 = base64_encode($sha);

        // 将API密钥、算法、头部信息和签名结果拼接成一个授权URL
        $authUrl = "api_key=\"$apiKey\", algorithm=\"hmac-sha256\", headers=\"host date request-line\", signature=\"$signature_sha_base64\"";

        // 对授权URL进行Base64编码，并添加到原始地址后面作为查询参数
        $authAddr = $addr . '?' . http_build_query(array(
                'host' => $ul['host'],
                'date' => $rfc1123_format,
                'authorization' => base64_encode($authUrl),
            ));

        return $authAddr;
    }
}