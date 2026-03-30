<?php
namespace app\admin\controller;

use think\Request;
use app\admin\controller;
use think\Loader;
use think\Session;
use think\Db;
//use think\Cache;
use Util\data\Redis;
//use think\cache\driver\Redis;

class Reconciliations //extends Auth
{
	public function __construct(){
		$config = [
			'host'	=> '127.0.0.1',
			'port'	=> '6379',
			'auth'	=> '123456',
		];
		$attr = [
			//连接超时时间，redis配置文件中默认为300秒
			'timeout'=>300,
			//选择数据库
			'db_id'=>1,
		];
		
		$this->redis = null;
		//实例化 Redis 缓存类
		$this->redis = Redis::getINstance($config,$attr);
		$this->rs = $this->redis->getRedis();
	}
	//银企对账；
	public function index() {
		//$res = $this->rs->zadd('jr','111');
		/*$this->rs->set('j','abc');
		echo $this->rs->get('j');*/
		
		//var_dump($this->rs->set('as','abc111'));
		//echo '值为：'.$this->rs->del('as');
		//dump($this->rs);
		
		/*$this->rs->sadd('w3k','redis');
		$this->rs->sadd('w3k','mysql');
		$this->rs->sadd('w3k','mongodb');*/
		$add = [
			'name'=>'Json',
			'age'=>28,
			'sex'=>1
		];
		
		/*$add = json_encode($add);		
		$this->rs->sadd('w3k',$add);
		//$this->rs->del('w3k');
		$res = $this->rs->smembers('w3k');
		echo '<pre>';
		print_r($res);
		
		$this->rs->sadd('w3h','11111');
		$this->rs->sadd('w3h','mongodb');
		$this->rs->sadd('w3h','mong22odb');
		$res1 = $this->rs->smembers('w3h');
		print_r($res1);
		
		$sd = $this->rs->sdiff('w3k','w3h');
		print_r($sd);*/
		
		return view("reconcil/",[
            'title'=>'运营审核列表',
        ]);
        
		/*$sd1 = $this->rs->sdiff('w3h','w3k');
		print_r($sd1);*/
		
	}
	
	/**
	 * 下载对账文件；
	 */
	public function Downfile()
	{
		//聚合支付地址
		$aqUrl = 'http://shop.gogo198.cn/payment/wechat/loadBill.php';
		//微信免密
		$wxUrl = 'http://shop.gogo198.cn/payment/Frx/Frx.php';
		
		
		//农商免密   无需下载，直接使用；使用前判断该文件是否存在，不存在则获取；
		//$sdeUrl = '../../../../../home/sdebank/TRANYGK0400000005020180710.txt';
		
	}
	
	//Curl 请求；
	private function postCurl($url,$data=[])
	{
		
	}
	
	/**
	 * 聚合支付txt对账文件解析入库
	 */
	private function poly(){
		
	}
	
	/**
	 * 微信免密txt对账文件解析入库
	 */
	private function wxsecret(){
		
	}
	
	/**
	 * 银联无感txt对账文件解析入库
	 */
	private function unionsecret(){
		
	}
	
	/**
	 * 农商免密txt对账文件解析入库
	 */
	private function sdesecret(){
		$path = '../../../../../home/sdebank/TRANYGK0400000005020180702.txt';
		$content = file_get_contents($path,'r');
		$contents= explode("\n",$content);//explode()函数以","为标识符进行拆分
		if(!empty($contents)){
        	$data_1 =array(
               'pay_sum'         => (int)(substr($contents[0],0,8)),//支付总笔数
               'pay_money'       => (float)(substr($contents[0],8,17)),//支付总金额
            );
        }
		echo '<pre>';
		print_r($contents);
	}
}
?>