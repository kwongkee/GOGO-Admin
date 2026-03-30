<?php

namespace app\job\command;

//use think\Controller;
use think\console\Command;
use think\console\Input;
use think\console\Output;
use think\Db;
use think\Cache;

class ParkingData extends Command
{
    
    protected function configure ()
    {
        $this->setName('ParkingData')->setDescription('停车数据');
    }
    
    protected function execute ( Input $input, Output $output )
    {
        $output->writeln('Date Crontab job start...');
        //        @file_put_contents(getcwd().'/runtime/log/test.txt',date('H:i:s',time())."\n",FILE_APPEND);
        $this->save();
        $this->restData();
        $output->writeln('Date Crontab job end...');
    }
    
    /**
     * 每天晚上2359记录到数据库
     */
    public function save(){
        Db::startTrans();
        try{
            Db::name('parking_data')->insert([
               'time'   => date('Y-m-d',time()),
                'stopIn'=>Cache::get('stopIn'),
                'wxConfirm' =>Cache::get('wxConfirm'),
                'devPay' => Cache::get('devPay'),
                'timeOut'=>Cache::get('timeOut'),
                'excepOrder'=>Cache::get('excepOrder'),
                'uniacid'=>0
            ]);
            Db::commit();
        }catch (\Exception $exception){
            Db::rollback();
        }
    }
    
    /**
     * 重置redis停车数据
     */
    public function restData(){
        Cache::rm('stopIn');
        Cache::rm('wxConfirm');
        Cache::rm('devPay');
        Cache::rm('timeOut');
        Cache::rm('excepOrder');
    }

    
}