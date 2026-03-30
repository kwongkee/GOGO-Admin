<?php

namespace app\job\command;
use think\Controller;
use think\console\Command;
use think\console\Input;
use think\console\Output;
use think\Db;
use Util\data\Reconclis;
use Util\data\Redis;
use Util\data\Sysdb;
use think\Session;
use think\Request;
use think\Loader;
use think\CURLFile;
use PHPExcel_IOFactory;
use PHPExcel;

class ReconCilJob extends Command
{
    
    protected function configure ()
    {
        $this->setName('ReconCilJob')->setDescription('跨境电商商户对账');
    }
    
    protected function execute ( Input $input, Output $output )
    {
        $output->writeln('Date Crontab job start...');
//      @file_put_contents(getcwd().'/runtime/log/test.txt',date('H:i:s',time())."\n",FILE_APPEND);
        $this->Generating();
        $output->writeln('Date Crontab job end...');
    }
    
    //执行获取对账写入表
    protected function Generating ()
    {
    	try{
    		$account = Reconclis::instance();
    		$account->Generatingbill();
        	return true;
        	
    	} catch (\Exception $exception) {
	        	
	        throw new \Exception($exception->getMessage());
	    }
        
    }
   	
}