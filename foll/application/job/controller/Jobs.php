<?php

namespace app\job\controller;
use app\common\controller;

use think\queue\Job;
use think\Queue;

class Jobs
{
    
    public function fire ( Job $job, $data )
    {
        
        //....这里执行具体的任务
        print "seccuees";
        var_dump($data);
        $job->delete();
//        if ( $job->attempts() > 3 ) {
//            //通过这个方法可以检查这个任务已经重试了几次了
//        }
//
//
//        //如果任务执行成功后 记得删除任务，不然这个任务会重复执行，直到达到最大重试次数后失败后，执行failed方法
//        $job->delete();
//
//        // 也可以重新发布这个任务
//        $job->release($delay); //$delay为延迟时间
//
    }
    
    public function failed ( $data )
    {
        
        // ...任务达到最大重试次数后，失败了
    }
    
}