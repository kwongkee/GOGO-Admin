<?php

namespace app\job\command;

use think\console\Command;
use think\console\Input;
use think\console\Output;
use think\console\input\Argument;
use think\Log;
use think\Cache;
use app\admin\logic\UserOrderHistoryExportLogic;
use PHPExcel;
use PHPExcel_IOFactory;

class PackUserInfo extends Command {
    
    protected function configure() {
        $this->setName('PackUserInfo')->addArgument('param',Argument::REQUIRED,'参数')->setDescription('打包下载用户订单信息');
    }
    
    protected function execute(Input $input, Output $output) {
        $output->writeln('Date Crontab job start...');
        $param=json_decode($input->getArgument('param'),true);
        $param['limit']=3000;
        $param['page']=1;
        $logic=new UserOrderHistoryExportLogic();
        $file=[];
        $filePath=getcwd().'/public/uploads/excel/'.date('Ymd');
        if (!is_dir($filePath)){
            if (!mkdir($filePath)){
                return Log::write('创建目录失败');
            }
        }
        $PHPExcel = new PHPExcel();
        while (true){
            $data=$logic->getData($param);
            if (empty($data['data'])){
                break;
            }
            $PHPExcel->setActiveSheetIndex(0);
            $PHPSheet = $PHPExcel->getActiveSheet();
            $PHPSheet->setTitle('sheet1');
            $n = 2;
            $PHPSheet->setCellValue('A1','身份证号')->setCellValue('B1','性别')->setCellValue('C1','收件地址')->setCellValue('D1','收件电话');
            foreach ($data['data'] as $val) {
                $PHPSheet->setCellValue('A' . $n,"'".$val['idcard'])
                    ->setCellValue('B'.$n,$val['sex'])
                    ->setCellValue('C'.$n,$val['shipping_addr'])
                    ->setCellValue('D'.$n,"'".$val['shipping_tel']);
                ++$n;
            }
            $phpWrite = PHPExcel_IOFactory::createWriter($PHPExcel, 'Excel2007');
            $tmpFileName  = date('YmdHis').mt_rand(1111,9999) . '.xlsx';
            $phpWrite->save($filePath.'/'.$tmpFileName);
            $file[]=$tmpFileName;
            $param['page']+=1;
            sleep(1);
        }
        $zipFileName= date('YmdHis').mt_rand(1111,9999) . '.zip';
        $zip=new \ZipArchive();
        if(!$zip->open($filePath.'/'.$zipFileName,\ZipArchive::OVERWRITE)){
           return  Log::write('创建压缩文件失败');
        }
        foreach ($file as $name){
            $zip->addFile($filePath.'/'.$name,$name);
        }
        $zip->close();
        Cache::set($param['key'],$zipFileName,3600);
        $output->writeln('Date Crontab job end...');
    }
}