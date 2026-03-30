<?php

namespace app\job\command;

use think\console\Command;
use think\console\Input;
use think\console\Output;
use think\Db;

date_default_timezone_set('Asia/Shanghai');

class ParkingQueue extends Command
{

    protected function configure()
    {
        $this->setName('ParkingQueue')->setDescription('停车队列');
    }

    protected function execute(Input $input, Output $output)
    {
        $output->writeln('Date Crontab job start...');
        $this->handle();
    }

    protected function handle()
    {
        while (true) {
            $data = Db::name('parking_queue')->find();
            if (empty($data)) {
                sleep(1);
            } else {
                call_user_func([$this, $data['queue']], $data);
            }
        }
    }

    protected function leave($payload)
    {
        try {
            $logic = new \app\api\logic\ParkingFeeCalculation();
            $result = $logic->sumMoney(json_decode($payload['payload'], true));
            Db::name('parking_queue')->where('id', $payload['id'])->delete();
            @file_put_contents(
                getcwd()."/runtime/log/".date("Ym", time())."/".date("d", time())."_queue.log",
                "结果:".$result."|信息".$payload['payload']."\n", FILE_APPEND
            );
        } catch (\Exception $e) {
            @file_put_contents(
                getcwd()."/runtime/log/".date("Ym", time())."/".date("d", time())."_queue.log",
                json_encode(['file' => $e->getFile(), 'line' => $e->getLine(), 'code' => $e->getCode(), 'message' => $e->getMessage()],JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)."\n",
                FILE_APPEND);
        }

    }
}