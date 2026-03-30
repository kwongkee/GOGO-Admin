<?php
namespace app\api\controller;

use think\Controller;
use think\Request;
use think\Response;
use think\Db;

class CentralizeInfo extends Controller{
    //获取新线路信息
    public function getLine(Request $request,Response $response){
        $result = $this->http_get("api/Channel/Get");
        $isnotice = 0;
        foreach($result as $k=>$v){
            $is_have = Db::name('shipping_channel')->where(['ChannelCode'=>$v['ChannelCode']])->find();

            if(empty($is_have['ChannelCode'])){
                Db::name('shipping_channel')->insert([
                    'Base_ChannelInfoID'=>$v['base_Channelinfoid'],
                    'ChannelCode'=>$v['ChannelCode'],
                    'CnName'=>$v['CnName'],
                    'EnName'=>$v['enname'],
                    'RefTime'=>$v['reftime'],
                    'ShortenImage'=>$v['shortenimage'],
                ]);
                $isnotice=1;
            }

            #爬取线路介绍
            if(!empty($is_have['subChannelInfoID'])){
                $content = file_get_contents('https://www.pfcexpress.com/webservice/APIWebService.asmx/ChannelInfo_sub?subChannelInfoID='.$is_have['subChannelInfoID']);
                #解析xml
                $content = json_decode(json_encode(simplexml_load_string($content),true),true)[0];
                $res = Db::name('shipping_channel')->where(['ChannelCode'=>$v['ChannelCode']])->update(['content'=>$content]);
            }
        }
        if($isnotice==1){
            #通知管理员
            sendWechatMsg(json_encode([
                'call'=>'confirmCollectionNotice',
                'first' =>'管理员你好，PFC皇家物流有新的渠道，请进入总后台配置子渠道代码！',
                'keyword1' => '新渠道通知',
                'keyword2' => '已通知',
                'keyword3' => date('Y-m-d H:i:s',time()),
                'remark' => '',
                'url' => '',
                'openid' => 'ov3-bt8keSKg_8z9Wwi-zG1hRhwg',//ov3-bt8keSKg_8z9Wwi-zG1hRhwg ov3-bt5vIxepEjWc51zRQNQbFSaQ
                'temp_id' => 'SVVs5OeD3FfsGwW0PEfYlZWetjScIT8kDxht5tlI1V8'
            ]));
        }
    }

    private function http_get($acition){
        $serverurl = "http://api.pfcexpress.com:81/";
        $apikey = "aeae3d3c-bcaa-4442-8849-ec61bbf8def4125730";
        $headers=array('Authorization: '.'Bearer '.$apikey,'Content-type: application/json');
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $serverurl.$acition);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $json = curl_exec($ch);
        curl_close($ch);
        $result=json_decode($json, true);

        return $result;
    }

    //获取皇家物流内页信息
    public function getinfo(Request $request,Response $response){
        echo exec('python '.$_SERVER['DOCUMENT_ROOT'].'/python_code/getPfc.py');
    }

    //判断皇家内页内容距上次爬到的是否不一样
    public function contrast(Request $request){
        #如果有不同时，就将id进行保存，管理员打开即可看到不同的内容
        $time = time();
        $menu = Db::name('centralize_pfc_menu_list')->select();
        $change_ids = [];
        foreach($menu as $k=>$v){
            #获取该标题最新的2条记录
            $content = Db::name('centralize_pfc_list')->where(['pid'=>$v['id']])->order('id desc')->limit(2)->select();
            if(count($content)==2){
                //进行对比
                foreach($content as $k2=>$v2){
                    $content[$k2]['content'] = json_decode($v2['content'],true);
                }
                //引用对比文件
                $dir = $_SERVER['DOCUMENT_ROOT'].'/foll/vendor/htmldiff';
                require_once($dir."/html_diff.php");

                //开始对比(上一条，最新一条)
                $con = html_diff($content[1]['content'],$content[0]['content'],true);
                //判断有无出现class="diff-html-added/diff-html-removed"
                if(strpos($con,"diff-html-added") || strpos($con,"diff-html-removed")){
                    //记录ids
                    $change_ids[]['ids'] = $content[1]['id'].','.$content[0]['id'];
                }
            }
        }
        
        #上架3个月后自动删除
        $content =Db::name('centralize_pfc_list')->select();
        foreach($content as $k=>$v){
            if(!empty($v['shelftime'])){
                if($v['shelftime'] <= $time-7776000){
                    Db::name('centralize_pfc_list')->where(['id'=>$v['id']])->delete();
                }
            }
        }
        
        #统计出现不同的ids,并发微信通知
        if(!empty($change_ids)){
            sendWechatMsg(json_encode([
                'call'=>'confirmCollectionNotice',
                'first' =>'管理员你好，PFC皇家物流集运服务距上次记录对比有差异,请点击查看！',
                'keyword1' => '内容有差异',
                'keyword2' => '已通知',
                'keyword3' => date('Y-m-d H:i:s',$time),
                'remark' => '',
                'url' => 'https://shop.gogo198.cn/app/index.php?i=3&c=entry&do=pfcexpress&p=index&m=sz_yi',
                'openid' => 'ov3-bt8keSKg_8z9Wwi-zG1hRhwg',//ov3-bt8keSKg_8z9Wwi-zG1hRhwg ov3-bt5vIxepEjWc51zRQNQbFSaQ
                'temp_id' => 'SVVs5OeD3FfsGwW0PEfYlZWetjScIT8kDxht5tlI1V8'
            ]));
        }
    }
}