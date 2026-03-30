<?php

namespace app\api\controller;

use think\Controller;
use think\Db;
use think\Request;

class CrawlWebsite extends Controller
{
    public function index()
    {
        if (request()->isPost() || request()->isAjax()) {

        }else{
            $time = time();
            #查找爬取任务
            $task = Db::name('centralize_resource_list')->select();
            if(!empty($task)){
                foreach($task as $k=>$v){
                    #查找爬取列表->where(' crawl_method=1 or crawl_method=2 ')
                    $list = Db::name('centralize_crawl_list')->where(['pid'=>$v['id']])->select();
                    if(!empty($list)){
                        foreach($list as $k2=>$v2){
                            $log = Db::name('centralize_crawl_log')->where(['cid'=>$v2['id']])->order('id','desc')->find();
                            if($v2['crawl_method']==1){
                                #按时爬取
                                if($v2['crawl_cycel']==1){
                                    #按每x时爬取(废弃)
                                    #查询记录表中最近一次爬虫时间；上次爬虫时间+相应时数>=当前时间就开始爬虫并记录。
                                    $log = Db::name('centralize_crawl_log')->where(['cid'=>$v2['id']])->order('id','desc')->find();
                                    if(empty($log['time'])){
            //                            exec('python /www/wwwroot/gogo/python_code/getWebsite.py '.$v['id']." ".$v['link']);
            //                            Db::name('centralize_crawl_log')->insert([
            //                                'cid'=>$v['id'],
            //                                'time'=>$time,
            //                            ]);
                                    }else{
                                        #1小时=3600秒
                                        if($log['time'] + ($v['every_hour']*3600) >= $time){
//                                exec('python /www/wwwroot/gogo/python_code/getWebsite.py '.$v['id']." ".$v['link']);
//                            Db::name('centralize_crawl_log')->insert([
//                                'cid'=>$v['id'],
//                                'time'=>$time,
//                            ]);
                                        }
                                    }
                                }
                                elseif($v['crawl_cycel']==2){
                                    #按每日（废弃）

                                }
                                elseif($v['crawl_cycel']==3){
                                    #按每周
                                    if(empty($log)){
                                        exec('python /www/wwwroot/gogo/python_code/getWebsite.py '.$v2['id']." ".$v2['link']);
                                        Db::name('centralize_crawl_log')->insert([
                                            'cid'=>$v2['id'],
                                            'time'=>$time,
                                        ]);
                                    }else{
                                        #如果当前周数是当天，并大于执行时间（当天爬过就停止爬取）
                                        if(date("w")==0 && $v['week']==7){
                                            #星期日
                                            if(date('Y-m-d',$log['time'])!=date('Y-m-d',$time)){
                                                exec('python /www/wwwroot/gogo/python_code/getWebsite.py '.$v2['id']." ".$v2['link']);
                                                Db::name('centralize_crawl_log')->insert([
                                                    'cid'=>$v2['id'],
                                                    'time'=>$time,
                                                ]);
                                            }
                                        }elseif(date("w")==$v['week']){
                                            #星期1-6
                                            if(date('Y-m-d',$log['time'])!=date('Y-m-d',$time)){
                                                exec('python /www/wwwroot/gogo/python_code/getWebsite.py '.$v2['id']." ".$v2['link']);
                                                Db::name('centralize_crawl_log')->insert([
                                                    'cid'=>$v2['id'],
                                                    'time'=>$time,
                                                ]);
                                            }
                                        }
                                    }
                                }
                                elseif($v['crawl_cycel']==4){
                                    #按每月
                                    if(empty($log)){
                                        exec('python /www/wwwroot/gogo/python_code/getWebsite.py '.$v2['id']." ".$v2['link']);
                                        Db::name('centralize_crawl_log')->insert([
                                            'cid'=>$v2['id'],
                                            'time'=>$time,
                                        ]);
                                    }else{
                                        #判断当天数和时间是否<任务指定时间，和判断记录时间的月份是否!=当前月份才可以爬
                                        if(($time <= strtotime(date('Y-m-'.$v['month'].' '.$v['every_month']))) && (date('Y-m',$log['time'])!=date('Y-m'))){
                                            exec('python /www/wwwroot/gogo/python_code/getWebsite.py '.$v2['id']." ".$v2['link']);
                                            Db::name('centralize_crawl_log')->insert([
                                                'cid'=>$v2['id'],
                                                'time'=>$time,
                                            ]);
                                        }
                                    }
                                }
                            }elseif($v['crawl_method']==2){
                                #定时爬取
                                if(empty($log)){
                                    exec('python /www/wwwroot/gogo/python_code/getWebsite.py '.$v2['id']." ".$v2['link']);
                                    Db::name('centralize_crawl_log')->insert([
                                        'cid'=>$v2['id'],
                                        'time'=>$time,
                                    ]);
                                }else{
                                    #判断当前时间是否>=任务时间，和当天时间是否不相同（选了定时的话，每天只可以爬一次）
                                    if(($time >= strtotime($v['every_time'])) && (date('Y-m-d',$time) != date('Y-m-d',strtotime($v['every_time'])))){
                                        exec('python /www/wwwroot/gogo/python_code/getWebsite.py '.$v2['id']." ".$v2['link']);
                                        Db::name('centralize_crawl_log')->insert([
                                            'cid'=>$v2['id'],
                                            'time'=>$time,
                                        ]);
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }
}