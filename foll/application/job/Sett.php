<?php

namespace App\Job;

use think\queue\Job;
use think\Db;
use think\Controller;

// 不可用
class Sett{

    public function fire(Job $job , $data) {

        // 将数据写入数据库
        $ins = [];
        $ins['desc'] = '进入任务执行';

        // 先删除
        $job->delete();

        return  Db::name('jobs_test')->insert($ins);

        // 这里执行具体的任务
        $data = json_decode($data,true);

        if($this->jobDone($data)) {

            // 先删除
            $job->delete();
            // 输出数据

            return 'success';

            //print("<info>Hello Job has been done and deleted"."</info>\n");

        } else {
            // 否则延迟执行  重新发布任务
            $job->release(3);
        }

        if($job->attempts() > 3) {
            // 通过这个方法可以检查这个任务已经重试了几次了
        }

        // 如果任务执行成功后，删除任务，不然任务会重复执行，直到失败，执行failed() 方法
        $job->delete();
    }

    // 任务执行失败
    public function failed() {
        // 将数据写入数据库
        $ins = [];
        $ins['desc'] = '任务执行失败';

        return  Db::name('jobs_test')->insert($ins);
    }


    // 具体执行方法
    public function jobDone($data) {

        // 将数据写入数据库
        $ins = [];
        $ins['desc'] = json_encode($data);

       return  Db::name('jobs_test')->insert($ins);

    }
}

?>