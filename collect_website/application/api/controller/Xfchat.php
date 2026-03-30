<?php

namespace app\api\controller;

use think\Controller;
use think\Db;
use think\Request;
use WebSocket\Client;

//讯飞ai接口 2023-11-10
class Xfchat extends Controller
{
    function xfyun(){
//        $answer = '1. 资讯标题：京东全球售跨境物流解决方案发布 发布时间：2022-05-18 09:30 资讯出处：京东物流 原文章链接：https://www.jd.com/ 内容概要：京东物流发布全球售跨境物流解决方案，提供全程可追踪、一站式服务。 2. 资讯标题：阿里巴巴国际站推出“跨境集运”服务 发布时间：2022-05-18 14:00 资讯出处：阿里巴巴国际站 原文章链接：https://www.alibaba.com/ 内容概要：阿里巴巴国际站推出“跨境集运”服务，助力中小企业拓展海外市场。 3. 资讯标题：亚马逊全球开店推出新跨境物流服务 发布时间：2022-05-18 16:30 资讯出处：亚马逊全球开店 原文章链接：https://www.amazon.cn/ 内容概要：亚马逊全球开店推出新跨境物流服务，提升卖家发货效率。 4. 资讯标题：顺丰速运推出跨境电商专属服务 发布时间：2022-05-18 19:00 资讯出处：顺丰速运 原文章链接：https://www.sf-express.com/ 内容概要：顺丰速运推出跨境电商专属服务，满足消费者多样化需求。';
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
                $question = Db::name('centralize_aichat_question')->where(['id'=>1])->find()['question'];
                $question = str_replace('yyyy年mm月dd日',date('Y年m月d日',strtotime("-1 day")),$question);
                $data = $this->getBody($Appid,$question);
                $client->send($data);

                // 从 WebSocket 服务器接收数据
                $answer = '';
                while(true){
                    $response = $client->receive();
                    $resp = json_decode($response,true);
                    $code = $resp["header"]["code"];
//                    echo "从服务器接收到的数据： " . $response;
                    if(0 == $code){
                        $status = $resp["header"]["status"];
                        if($status != 2){
                            $content = $resp['payload']['choices']['text'][0]['content'];
                            $answer .= $content;
//                            array_push($answer,$content);
                        }else{
                            $content = $resp['payload']['choices']['text'][0]['content'];
                            $answer .= $content;
//                            array_push($answer,$content);
                            $total_tokens = $resp['payload']['usage']['text']['total_tokens'];
                            print("本次消耗token用量：\n");
                            print($total_tokens.'\n');
                            break;
                        }
                    }else{
                        echo "服务返回报错".$response;
                        break;
                    }
                }
                $answer = explode('。',$answer);
//                print("\n返回结果为：\n");
//                print($answer);
//                die;
                foreach($answer as $k=>$v){
                    if(!empty($v)){
                        $i=1+$k;
                        $news = str_replace($i.'. 新闻标题：','',$answer[$k]);
                        $news = str_replace('发布时间：','@@',$news);
                        $news = str_replace('发布媒体：','@@',$news);
                        $news = str_replace('发布链接：','@@',$news);
                        $news = str_replace('内容概要：','@@',$news);
                        $news = explode('@@',$news);

                        Db::name('centralize_crossborder_news')->insert([
                            'pid'=>46,
                            'title'=>trim($news[0]),
                            'time'=>trim($news[1]),
                            'info_source'=>trim($news[2]),
                            'link'=>trim($news[3]),
                            'descs'=>trim($news[4]),
                            'status'=>1
                        ]);
                    }
                }
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

    function gogo_news(){
        require $_SERVER['DOCUMENT_ROOT'].'/collect_website/application/admin/controller/simple_html_dom.php';

        $list = Db::name('website_crossborder_news')->where(['status'=>0])->select();
        foreach($list as $k=>$v){
            #1、去重
            $recurring = Db::name('website_crossborder_news')->where(['title'=>$v['title']])->select();
            if(count($recurring)>1){
                foreach($recurring as $k2=>$v2){
                    if($k2!=0){
                        Db::name('website_crossborder_news')->where(['id'=>$v2['id']])->delete();
                    }
                }
            }
            #2、清楚链接为空的
            if(empty($v['link'])){
                Db::name('website_crossborder_news')->where(['id'=>$v['id']])->delete();
            }

            #3、修改时间为yyyy-mm-dd格式
            if(strstr($v['time'],':')){
                $time = explode(' ',$v['time'])[0];
                Db::name('website_crossborder_news')->where(['id'=>$v['id']])->update(['time'=>$time]);
            }

            #2、清楚标题为空的
            if(empty($v['title'])){
                Db::name('website_crossborder_news')->where(['id'=>$v['id']])->delete();
            }
        }

        #4、获取原文链接&上架
        $list2 = Db::name('website_crossborder_news')->where(['status'=>0])->select();
        foreach($list2 as $k=>$v){
            // 创建一个新的 HTML DOM 对象并从给定 URL 加载页面内容
            try{
                $html = file_get_html($v['link']);
            }catch (\Exception $e) {
                $html = file_get_html(str_replace('https','http',$v['link']));
            }

            // 使用 CSS 选择器查找特定元素
//            $elements = $html->find('.target-url-content');
            $elements = $html->find('a.text-sm');

            // 遍历找到的元素并输出它们的文本内容
            foreach ($elements as $element) {
                Db::name('website_crossborder_news')->where(['status'=>0,'id'=>$v['id']])->update([
                    'status'=>1,
                    'link'=>trim($element->plaintext),
                    'pid'=>46//菜单id
                ]);
            }
            // 释放内存，清理资源
            $html->clear();
        }

        echo 'success';
    }
}