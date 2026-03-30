<?php
namespace app\payments\controller;

use think\Controller;
use Util\data\Sysdb;

/**
**  支付回调处理
**/
class Notify extends Controller
{
	private $redis;
	private $rds;
	private $db;
	public function __construct() {
		$config = [
			'host'	=> '127.0.0.1',
			'port'	=> '6379',
			'auth'	=> '123456',
		];
		$attr = [
			//连接超时时间，redis配置文件中默认为300秒
			'timeout'=>300,
			//选择数据库
			'db_id'=>8,
		];
		
		//实例化 Redis 缓存类
		//$this->redis = Redis::getINstance($config,$attr);
		//$this->rds = $this->redis->getRedis();
		//实例化数据库
		$this->db = new Sysdb;
	}
	// 聚合支付回调  旧的
	public function TgPay() {

		$input = file_get_contents('php://input');
		// 日志写入
		file_put_contents('../runtime/log/Notify/tgpay.txt', $input."\r\n",FILE_APPEND);
		//接收后台回调参数，并转换json格式
		$receive = json_decode($input,TRUE);
		// 数据不为空
		if (!empty($receive)) {
			// 日志写入
			//file_put_contents('../runtime/log/Notify/tgpay.txt', $input."\r\n",FILE_APPEND);
			$orderid 	= $receive['lowOrderId'];
			$uporderid  = $receive['upOrderId'];
			$payTime    = $receive['payTime'];
			$ptime		= strtotime($receive['payTime']);
			
			// 查询数据库中对应的数据
			$items = $this->db->table('payments_ordersn')->field(['paystatus','notifyurl','ordersn'])->where(['orderid'=>$orderid])->item();
			if(!empty($items) && $items['paystatus'] == '101') {// 如果状态未支付的情况才修改状态

				// 支付成功
				if(($receive['state'] == '0') && ($receive['orderDesc'] == '支付成功')) {
					
					$upData = ['uporderid'=>$uporderid,'paystatus'=>'100','paytime'=>$payTime,'p_time'=>$ptime];
					try{
						
						$this->db->startTranss();//开启事务
						
							$this->db->table('payments_ordersn')->where(['orderid'=>$orderid])->update($upData);
							
						$this->db->commits();// 事务提交
					}catch(\Exception $e){
						// 日志写入
						file_put_contents('../runtime/log/Notify/tgpayError.txt', $e."\r\n",FILE_APPEND);
						$this->db->rollbacks();//订单回滚
					}
					
					
				} else { // 支付失败
					
					$upData = ['uporderid'=>$uporderid,'paystatus'=>'102','paytime'=>$payTime,'p_time'=>$ptime];
					try{
						
						$this->db->startTranss();//开启事务
						
							$this->db->table('payments_ordersn')->where(['orderid'=>$orderid])->update($upData);
							
						$this->db->commits();// 事务提交
					}catch(\Exception $e){
						// 日志写入
						file_put_contents('../runtime/log/Notify/tgpayError.txt', $e."\r\n",FILE_APPEND);
						$this->db->rollbacks();//订单回滚
					}
					
				}
				
				$retData['payMoney']	= 	$receive['payMoney'];//交易金额
				$retData['orderDesc']	=	$receive['orderDesc'];//提示消息
				$retData['state']		=	$receive['state'];	//支付状态
				$retData['openid']		=	isset($receive['openid']) ? $receive['openid'] : ''; //用户OPENID
				$retData['payTime']		=	$receive['payTime'];//支付时间
				$retData['upOrderId']	=	$uporderid;//支付商户号
				$retData['lowOrderId']	=	$items['ordersn'];//下游订单号
				// 回调数据
				$jsonData = json_encode($retData);
				file_put_contents('../runtime/log/Notify/retData.txt',$jsonData."\r\n",FILE_APPEND);
				$this->ihttp_posts($items['notifyurl'],$jsonData);
				exit('SUCCESS');
			} else { // 否则就是支付成功直接返回成功
				exit('SUCCESS');
			}
		} else {
			exit('FAIL');
		}		
	}
	
	
	
	
	/**
	 * @数据请求提交POST json
	 * @$url:请求地址
	 * @post_data:请求数据
	 */
	public function ihttp_posts($url,$post_data) {
		//初始化	 
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,0); // 跳过证书检查
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,2);  // 从证书中检查SSL加密算法是否存在
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($ch, CURLOPT_POSTFIELDS,$post_data);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Content-Length: ' . strlen($post_data)));
		$result = curl_exec($ch);
		curl_close($ch);
		return $result;
	}
	
	
	
	
	
}