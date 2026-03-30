<?php

namespace app\api_v3\controller;

use think\Db;
use think\Request;
use think\Env;
use think\Cache;
use app\common\validate\BaseValidate;
use app\lib\exception\member_exception\MemberLoginException;
use app\lib\exception\param_exception\ParameterException;
use app\lib\exception\user_exception\UserQRCodeException;
use app\lib\tools\CurlHandler;
use app\lib\service\Tokens as TokenService;
use app\lib\restful_api\RestfulApiCode;
use app\lib\tools\ResultHandler;
use app\lib\exception\ExceptionErrorCode;
use Util\data\Sysdb;
use PHPExcel_IOFactory;
use PHPExcel;

/**
 * 小程序接口
 * Class WechatCarBill
 * @package app\api_v3\controller
 */
class WechatCarBill
{
    public function __construct(){
		$this->db = new Sysdb;
	}

    function returnHandler($result, $flag = true)
    {
        if (is_int($result) && $result <= 0)
        {
            throw new EmptyResultException();
        }
        else if (!$flag && empty($result))
        {
            throw new EmptyResultException();
        }
        else if (!$flag && ($result instanceof Collection) && $result->isEmpty() )
        {
            throw new EmptyResultException();
        }

        $statusCode = RestfulApiCode::OK;
        if (Request::instance()->isGet())
        {
            $statusCode = RestfulApiCode::OK;
        }
        else if (Request::instance()->isPost() || Request::instance()->isPut() || Request::instance()->isPatch())
        {
            $statusCode = RestfulApiCode::CREATED;
        }
        else if (Request::instance()->isDelete())
        {
            $statusCode = RestfulApiCode::NO_CONTENT;
        }
        return ResultHandler::returnJson('SUCCESS', $result, ExceptionErrorCode::SUCCESS, $statusCode);
	}
	//检测是否对账
	public function checkorder_day()
	{
		$type = input('type');
		switch ($type) {
			case 'tgpay':
				$map['pay_type'] = 'aq';
				break;
			case 'union':
				$map['pay_type'] = 'union';
				break;
			case 'fagro':
				$map['pay_type'] = 'sde';
				break;
			case 'fwechat':
				$map['pay_type'] = 'wx';
				break;	
		}

		//昨天日期
		$map['date'] = date("Ymd",strtotime("-1 day"));
		$data = Db::name('parking_pay_summary')->where($map)->find();
		if( $data )
		{
			$result['status'] = 0;
			$result['msg'] = '今天已对账,请勿重复提交';
		}else{
			$result['status'] = 1;
			$result['msg'] = '今天未对账,请对账';
		}
		return json($result);
	}
	//获取账单列表
	public function getorderlist()
	{
		$type = input('type');
		switch ($type) {
			case 'tgpay':
				$map['pay_type'] = 'aq';
				break;
			case 'union':
				$map['pay_type'] = 'union';
				break;
			case 'fagro':
				$map['pay_type'] = 'sde';
				break;
			case 'fwechat':
				$map['pay_type'] = 'wx';
				break;	
		}

		$orderlist = Db::name('parking_pay_summary')->where($map)->order('date desc')->limit(20)->select();
		
		$result['data'] = $orderlist;
		return json($result);
	}


	//获取客户对账列表
	public function getcustomsorderlist()
	{
		$type = input('type');
		switch ($type) {
			case 'aq':
				$map['pay_type'] = 'aq';
				break;
			case 'un':
				$map['pay_type'] = 'un';
				break;
			case 'sd':
				$map['pay_type'] = 'sd';
				break;
			case 'wx':
				$map['pay_type'] = 'wx';
				break;	
		}

		$orderlist = Db::name('parking_mer_summary')->where($map)->order('date desc')->limit(20)->select();
		$result['data'] = $orderlist;
		return json($result);
	}


    //一键平账
    public function onebalance() {

        $validate = new BaseValidate([
            'openid'  =>'require',
            'type'    =>'require',
            'dates'   =>'isDefault',
            'isls'    =>'require',
        ]);
        $validate->goCheck();
        $params = $validate->getParameters();

        $openid  = $params['openid'];
        $type = $params['type'];
        $dates = $params['dates'];
        $isls = $params['isls'];
		$timestr = strtotime($dates);
		//$dates = date('Ymd',$timestr);
		$inArr = ['tgpay','fwechat','fagro','union'];
		if(in_array($type,$inArr)) {
			//（聚合支付：tgpay，微信免密：Fwechat、农商免密：fagro、银联：union
			switch($type) {
				case 'tgpay':
					// 日期的订单是否存在长短款的数据
					$mistake = Db::name('parking_check_mistake')->where(['type'=>'tgpay','date'=>$dates])->select();
					if(!empty($mistake)) {

						$flag   = false;
						if($isls == '短款') {
							$foll    = [];
							$parking = [];
							foreach($mistake as $k=>$v) {
								$ordersn = 'G99198'.date('YmdHis',time()).mt_rand(010,999);
								$foll[$k]['ordersn'] 		= $v['ordersn']?$v['ordersn']:$ordersn;
								$foll[$k]['pay_account'] 	= $v['pay_account'];
								$foll[$k]['uniacid']     	= '14';
								$foll[$k]['application']    = 'parking';
								$foll[$k]['pay_type']     	= 'wechat';
								$foll[$k]['pay_status']     = '1';
								$foll[$k]['create_time']    = ($timestr+100);
								$foll[$k]['pay_time']       = $timestr;
								$foll[$k]['business_name']  = '伦教停车';
								$foll[$k]['upOrderId']     	= $v['upOrderId'];
								$foll[$k]['body']			= $v['upOrderId'].'短款冲销';
								$parking[$k]['ordersn']		= $v['ordersn']?$v['ordersn']:$ordersn;
								$parking[$k]['status']		= '已结算';

								$up = [
									'r_state'=>'success',
									//错误原因赋值
									'msg'	 => '平账'
								];
								//更新   parking_pay_wxsecret  G9919820180918174343632
								Db::name('parking_pay_poly')->where(['order_id'=>$v['upOrderId']])->update($up);
								//删除差错表中的数据
								Db::name('parking_check_mistake')->where(['upOrderId'=>$v['upOrderId'],'date'=>$v['date']])->delete();
							}

							//写入数据到foll_order表中
							Db::name('foll_order')->insertAll($foll);
							//写入对应
							Db::name('parking_order')->insertAll($parking);
							$flag	= true;//平账标识

                            $result['status'] = 1;
                            $result['msg'] = '操作成功！';

						} else if($isls == '长款') {
							$foll    = [];
							$parking = [];
							foreach($mistake as $k=>$v) {
								$ordersn = 'G99198'.date('YmdHis',time()).mt_rand(010,999);
								$foll[$k]['ordersn'] 		= $v['ordersn']?$v['ordersn']:$ordersn;
								$foll[$k]['pay_account'] 	= -$v['pay_account'];
								$foll[$k]['uniacid']     	= '14';
								$foll[$k]['application']    = 'parking';
								$foll[$k]['pay_type']     	= 'wechat';
								$foll[$k]['pay_status']     = '1';
								$foll[$k]['create_time']    = ($timestr+100);
								$foll[$k]['pay_time']       = $timestr;
								$foll[$k]['business_name']  = '伦教停车';
								$foll[$k]['upOrderId']     	= $v['upOrderId'];
								$foll[$k]['body']			= $v['upOrderId'].'长款冲销';

								$parking[$k]['ordersn']		= $v['ordersn']?$v['ordersn']:$ordersn;
								$parking[$k]['status']		= '已结算';

								$up = [
									'r_state'=>'success',
									//错误原因赋值
									'msg'	 => '平账'
								];
								//更新   parking_pay_wxsecret  G9919820180918174343632
								Db::name('parking_pay_poly')->where(['order_id'=>$v['upOrderId']])->update($up);
								//删除差错表中的数据
								Db::name('parking_check_mistake')->where(['upOrderId'=>$v['upOrderId'],'date'=>$v['date']])->delete();
							}
							//写入数据到foll_order表中
							Db::name('foll_order')->insertAll($foll);
							//写入对应
							Db::name('parking_order')->insertAll($parking);
							$flag	= true;//平账标识
                            $result['status'] = 1;
                            $result['msg'] = '操作成功！';
						}
						/**
						 * 更新统计表的状态
						 */
						//$dateW  = $mistake[0]['date'];
                        $dateW  = $dates;
						Db::name('parking_pay_summary')->where(['date'=>$dateW,'pay_type'=>'aq'])->update(['msg'=>'平账']);

						//if($flag) {//已平账   发送请求平账
							$urld = 'http://shop.gogo198.cn/foll/public/index.php?s=OrderReconcils/Analyaqs&date='.$dates;
							$this->GetUrl($urld);
						//}

					} else {

                        $result['status'] = 0;
                        $result['msg'] = '操作失败,没有差错数据';
					}
				break;

				case 'fwechat':
					$mistake = Db::name('parking_check_mistake')->where(['type'=>'fwechat','date'=>$dates])->select();
					if(!empty($mistake)) {
						$flag  = false;
						if($isls == '短款') {//短款直接不齐对应的表中添加字段，并删除差错表的数据，更新汇总表记录
							$foll  = [];
							$parking = [];
							foreach($mistake as $k=>$v) {
								$ordersn = 'G99198'.date('YmdHis',$timestr).mt_rand(010,999);
								$foll[$k]['ordersn'] 		= $v['ordersn']?$v['ordersn']:$ordersn;
								$foll[$k]['pay_account'] 	= $v['pay_account'];//$v['checkMoney'];
								$foll[$k]['uniacid']     	= '14';
								$foll[$k]['application']    = 'parking';
								$foll[$k]['pay_type']     	= 'Fwechat';
								$foll[$k]['pay_status']     = '1';
								$foll[$k]['create_time']    = ($timestr+100);
								$foll[$k]['pay_time']       = $timestr;
								$foll[$k]['business_name']  = '伦教停车';
								$foll[$k]['upOrderId']     	= $v['upOrderId'];
								$foll[$k]['body']			= $v['upOrderId'].'短款冲销';

								$parking[$k]['ordersn']		= $v['ordersn']?$v['ordersn']:$ordersn;
								$parking[$k]['status']		= '已结算';

								$up = [
									'r_state'=>'success',
									//错误原因赋值
									'msg'	 => '平账'
								];
								//更新   parking_pay_wxsecret  G9919820180918174343632
								Db::name('parking_pay_wxsecret')->where(['order_id'=>$v['upOrderId']])->update($up);
								//删除差错表中的数据
								Db::name('parking_check_mistake')->where(['upOrderId'=>$v['upOrderId'],'date'=>$v['date']])->delete();
							}
							//写入数据到foll_order表中
							Db::name('foll_order')->insertAll($foll);
							//写入对应
							Db::name('parking_order')->insertAll($parking);
							$flag = true;//平账标识
                            $result['status'] = 1;
                            $result['msg'] = '操作成功！';

						} else if($isls == '长款') {
							$foll  = [];
							$parking = [];
							foreach($mistake as $k=>$v) {
								$ordersn = 'G99198'.date('YmdHis',$timestr).mt_rand(010,999);
								$foll[$k]['ordersn'] 		= $v['ordersn']?$v['ordersn']:$ordersn;
								$foll[$k]['pay_account'] 	= -$v['pay_account'];//-$v['checkMoney'];
								$foll[$k]['uniacid']     	= '14';
								$foll[$k]['application']    = 'parking';
								$foll[$k]['pay_type']     	= 'Fwechat';
								$foll[$k]['pay_status']     = '1';
								$foll[$k]['create_time']    = ($timestr+100);
								$foll[$k]['pay_time']       = $timestr;
								$foll[$k]['business_name']  = '伦教停车';
								$foll[$k]['upOrderId']     	= $v['upOrderId'];
								$foll[$k]['body']			= $v['upOrderId'].'长款冲销';

								$parking[$k]['ordersn']		= $v['ordersn']?$v['ordersn']:$ordersn;
								$parking[$k]['status']		= '已结算';

								$up = [
									'r_state'=>'success',
									//错误原因赋值
									'msg'	 => '平账'
								];
								//更新   parking_pay_wxsecret  G9919820180918174343632
								Db::name('parking_pay_wxsecret')->where(['order_id'=>$v['upOrderId']])->update($up);
								//删除差错表中的数据
								Db::name('parking_check_mistake')->where(['upOrderId'=>$v['upOrderId'],'date'=>$v['date']])->delete();
							}
							//写入数据到foll_order表中
							Db::name('foll_order')->insertAll($foll);
							//写入对应
							Db::name('parking_order')->insertAll($parking);
							$flag  = true;//平账标识
                            $result['status'] = 1;
                            $result['msg'] = '操作成功！';
						}

						/**
						 * 更新统计表的状态
						 */
						//$dateW  = $mistake[0]['date'];
                        $dateW  = $dates;
						Db::name('parking_pay_summary')->where(['date'=>$dateW,'pay_type'=>'wx'])->update(['msg'=>'平账']);
						if($flag) {//已平账
							$urld = 'http://shop.gogo198.cn/foll/public/index.php?s=OrderReconcils/Analywxs&date='.$dates;
							$this->GetUrl($urld);
						}
					} else {

                        $result['status'] = 0;
                        $result['msg'] = '操作失败,没有差错数据';
					}
				break;

				case 'fagro':
					$mistake = Db::name('parking_check_mistake')->where(['type'=>'FAgro','date'=>$dates])->select();
					if(!empty($mistake)) {
						$flag  =  false;
						if($isls == '短款') {
							$foll  = [];
							$parking = [];
							foreach($mistake as $k=>$v) {
								$ordersn = 'G99198'.date('YmdHis',time()).mt_rand(010,999);
								$foll[$k]['ordersn'] 		= $v['ordersn']?$v['ordersn']:$ordersn;
								$foll[$k]['pay_account'] 	= $v['pay_account'];//$v['checkMoney'];
								$foll[$k]['uniacid']     	= '14';
								$foll[$k]['application']    = 'parking';
								$foll[$k]['pay_type']     	= 'FAgro';
								$foll[$k]['pay_status']     = '1';
								$foll[$k]['create_time']    = ($timestr+100);
								$foll[$k]['pay_time']       = $timestr;
								$foll[$k]['business_name']  = '伦教停车';
								$foll[$k]['upOrderId']     	= $v['upOrderId'];
								$foll[$k]['body']			= $v['upOrderId'].'短款冲销';

								$parking[$k]['ordersn']		= $v['ordersn']?$v['ordersn']:$ordersn;
								$parking[$k]['status']		= '已结算';

								$up = [
									'r_state'=>'success',
									//错误原因赋值
									'msg'	 => '平账'
								];
								//更新   parking_pay_wxsecret  G9919820180918174343632
								Db::name('parking_pay_sdesecret')->where(['order_id'=>$v['upOrderId']])->update($up);
								//删除差错表中的数据
								Db::name('parking_check_mistake')->where(['upOrderId'=>$v['upOrderId'],'date'=>$v['date']])->delete();
							}
							//写入数据到foll_order表中
							Db::name('foll_order')->insertAll($foll);
							//写入对应
							Db::name('parking_order')->insertAll($parking);
							$flag  =  true;//平账标识
                            $result['status'] = 1;
                            $result['msg'] = '操作成功！';

						} else if($isls == '长款') {
							$foll  = [];
							$parking = [];
							foreach($mistake as $k=>$v) {
								$ordersn = 'G99198'.date('YmdHis',time()).mt_rand(010,999);
								$foll[$k]['ordersn'] 		= $v['ordersn']?$v['ordersn']:$ordersn;
								$foll[$k]['pay_account'] 	= -$v['pay_account'];//$v['checkMoney'];
								$foll[$k]['uniacid']     	= '14';
								$foll[$k]['application']    = 'parking';
								$foll[$k]['pay_type']     	= 'FAgro';
								$foll[$k]['pay_status']     = '1';
								$foll[$k]['create_time']    = ($timestr+100);
								$foll[$k]['pay_time']       = $timestr;
								$foll[$k]['business_name']  = '伦教停车';
								$foll[$k]['upOrderId']     	= $v['upOrderId'];
								$foll[$k]['body']			= $v['upOrderId'].'长款冲销';

								$parking[$k]['ordersn']		= $v['ordersn']?$v['ordersn']:$ordersn;
								$parking[$k]['status']		= '已结算';

								$up = [
									'r_state'=>'success',
									//错误原因赋值
									'msg'	 => '平账'
								];
								//更新   parking_pay_wxsecret  G9919820180918174343632
								Db::name('parking_pay_sdesecret')->where(['order_id'=>$v['upOrderId']])->update($up);
								//删除差错表中的数据
								Db::name('parking_check_mistake')->where(['upOrderId'=>$v['upOrderId'],'date'=>$v['date']])->delete();
							}
							//写入数据到foll_order表中
							Db::name('foll_order')->insertAll($foll);
							//写入对应
							Db::name('parking_order')->insertAll($parking);
							$flag  =  true;//平账标识
                            $result['status'] = 1;
                            $result['msg'] = '操作成功！';
						}

						/**
						 * 更新统计表的状态
						 */
						//$dateW  = $mistake[0]['date'];
                        $dateW  = $dates;
						Db::name('parking_pay_summary')->where(['date'=>$dateW,'pay_type'=>'sde'])->update(['msg'=>'平账']);
						if($flag) {//已平账
							$urld = 'http://shop.gogo198.cn/foll/public/index.php?s=OrderReconcils/AnalySdes&date='.$dates;
							$this->GetUrl($urld);
						}
					} else {

                        $result['status'] = 0;
                        $result['msg'] = '操作失败,没有差错数据';
					}

				break;

				case 'union':
					$mistake = Db::name('parking_check_mistake')->where(['type'=>'union','date'=>$dates])->select();
					if(!empty($mistake)) {
						$flag  =  false;
						if($isls == '短款') {
							$foll 	 = [];
							$parking = [];
							foreach($mistake as $k=>$v) {
								$ordersn = 'G99198'.date('YmdHis',time()).mt_rand(010,999);
								$foll[$k]['ordersn'] 		= $v['ordersn']?$v['ordersn']:$ordersn;//$ordersn;
								$foll[$k]['pay_account'] 	= $v['pay_account'];//$v['checkMoney'];
								$foll[$k]['uniacid']     	= '14';
								$foll[$k]['application']    = 'parking';
								$foll[$k]['pay_type']     	= 'Parks';
								$foll[$k]['pay_status']     = '1';
								$foll[$k]['create_time']    = ($timestr+100);
								$foll[$k]['pay_time']       = $timestr;
								$foll[$k]['business_name']  = '伦教停车';
								$foll[$k]['upOrderId']     	= $v['upOrderId'];
								$foll[$k]['body']			= $v['upOrderId'].'短款冲销';

								$parking[$k]['ordersn']		= $v['ordersn']?$v['ordersn']:$ordersn;
								$parking[$k]['status']		= '已结算';

								$up = [
									'r_state'=>'success',
									//错误原因赋值
									'msg'	 => '平账'
								];

								//更新   parking_pay_wxsecret  G9919820180918174343632
								Db::name('parking_pay_unionsecret')->where(['order_id'=>$v['upOrderId']])->update($up);
								//删除差错表中的数据
								Db::name('parking_check_mistake')->where(['upOrderId'=>$v['upOrderId'],'date'=>$v['date']])->delete();
							}
							//写入数据到foll_order表中
							Db::name('foll_order')->insertAll($foll);
							//写入对应
							Db::name('parking_order')->insertAll($parking);
							$flag  =  true;
                            $result['status'] = 1;
                            $result['msg'] = '操作成功！';

						} else if($isls == '长款') {
							$foll = [];
							$parking = [];
							foreach($mistake as $k=>$v) {
								$ordersn = 'G99198'.date('YmdHis',time()).mt_rand(010,999);
								$foll[$k]['ordersn'] 		= $v['ordersn']?$v['ordersn']:$ordersn;
								$foll[$k]['pay_account'] 	= -$v['pay_account'];//$v['checkMoney'];
								$foll[$k]['uniacid']     	= '14';
								$foll[$k]['application']    = 'parking';
								$foll[$k]['pay_type']     	= 'Parks';
								$foll[$k]['pay_status']     = '1';
								$foll[$k]['create_time']    = ($timestr+100);
								$foll[$k]['pay_time']       = $timestr;
								$foll[$k]['business_name']  = '伦教停车';
								$foll[$k]['upOrderId']     	= $v['upOrderId'];
								$foll[$k]['body']			= $v['upOrderId'].'长款冲销';

								$parking[$k]['ordersn']		= $v['ordersn']?$v['ordersn']:$ordersn;
								$parking[$k]['status']		= '已结算';

								$up = [
									'r_state'=>'success',
									//错误原因赋值
									'msg'	 => '平账'
								];
								//更新   parking_pay_wxsecret  G9919820180918174343632
								Db::name('parking_pay_unionsecret')->where(['order_id'=>$v['upOrderId']])->update($up);
								//删除差错表中的数据
								Db::name('parking_check_mistake')->where(['upOrderId'=>$v['upOrderId'],'date'=>$v['date']])->delete();
							}
							//写入数据到foll_order表中
							Db::name('foll_order')->insertAll($foll);
							//写入对应
							Db::name('parking_order')->insertAll($parking);
							$flag = true;

                            $result['status'] = 1;
                            $result['msg'] = '操作成功！';
						}

						/**
						 * 更新统计表的状态
						 */
						//$dateW  = $mistake[0]['date'];
                        $dateW  = $dates;
						Db::name('parking_pay_summary')->where(['date'=>$dateW,'pay_type'=>'union'])->update(['msg'=>'平账']);
						if($flag) {//已平账
							$urld = 'http://shop.gogo198.cn/foll/public/index.php?s=OrderReconcils/AnalyUnions&date='.$dates;
							$this->GetUrl($urld);
						}
					} else {

                        $result['status'] = 0;
                        $result['msg'] = '操作失败,没有差错数据';
					}
				break;
			}
		}
	}

    //获取银企对账单
    public function getmredata()
    {
        $validate = new BaseValidate([
            'openid'  =>'require',
            'type'    =>'require',
            'date'    =>'isDefault'

        ]);
        $validate->goCheck();
        $params = $validate->getParameters();

        $openid  = $params['openid'];
        $type = $params['type'];
        $date = $params['date'];
        $dates = $date ? $date : date('Ymd',strtotime('-1 day'));
        $msg   = '平账';

        if($date)
        {
            $stime = Db::name('parking_check_mistake')->field('date')->where(['type'=>$type,'checkOk'=>'No','date'=>$date])->order('mid','asc')->find();
        }else{
            $stime = Db::name('parking_check_mistake')->field('date')->where(['type'=>$type,'checkOk'=>'No'])->order('mid','asc')->find();
        }

        $data  = Db::name('parking_check_mistake')->where(['type'=>$type,'checkOk'=>'No','date'=>$stime['date']])->order('upOrderId')->select();

		$orderData = Db::name('parking_mer_summary')->where(array('pay_type'=>$type,'date'=>$date))->find();
        if($data)
        {
            $result['datas'] = $data;
            $result['msg'] = $data[0]['msg'] ? $data[0]['msg'] : $msg;
			$result['date'] = $stime['date'];
        }else {
            $result['datas'] = null;
            $result['msg'] = $msg;
            $result['date'] = $dates;
        }

		$result['status'] = 1;
		$result['orderData'] = $orderData;
        $result['project'] = '伦教停车-'.$result['date'].'-';

        return $this->returnHandler($result);
    }

    //导出昨日账单
    public function lastdayexcel()
    {
        $validate = new BaseValidate([
            'openid'  =>'require',
            'type'    =>'require',
            'date'    =>'require',
        ]);
        $validate->goCheck();
        $params = $validate->getParameters();

        $openid  = $params['openid'];
        $type = $params['type'];
        $day = $params['date'];

        switch($type) {
            case 'tgpay'://聚合支付
				$up = Db::name('parking_pay_poly')->field('pay_money,order_id,low_order_id,r_state,date')->where(['date'=>$day])->select();
				if(empty($up)) {
                    $result['status'] = 0;
                    $result['msg'] = '没有对账数据可导出！';
                    $result['fileurl'] = null;
				}
				foreach($up as $key=>$val) {
					$up[$key]['r_state'] = $val['r_state']=='success'?'对账成功':'对账有误';
				}

				$fileurl = $this->ExcelsPost($up,$day,$type);

				if(!$fileurl) {
                    $result['status'] = 0;
                    $result['msg'] = '数据导出失败';
                    $result['fileurl'] = null;
				}
                $result['status'] = 1;
                $result['msg'] = '数据导出成功';
                $result['fileurl'] = $fileurl;
			break;
			case 'fwechat'://微信免密
				$up = Db::name('parking_pay_wxsecret')->field('pay_money,order_id,r_state,date,low_order_id')->where(['date'=>$day])->select();
				if(empty($up)) {
                    $result['status'] = 0;
                    $result['msg'] = '没有对账数据可导出！';
                    $result['fileurl'] = null;
				}
				foreach($up as $key=>$val) {
					$up[$key]['r_state'] = $val['r_state']=='success'?'对账成功':'对账有误';
					$up[$key]['low_order_id'] = $val['low_order_id'] ? $val['low_order_id'] : 'No';
				}
				$fileurl = $this->ExcelsPost($up,$day,$type);

				if(!$fileurl) {
                    $result['status'] = 0;
                    $result['msg'] = '数据导出失败';
                    $result['fileurl'] = null;
				}
                $result['status'] = 1;
                $result['msg'] = '数据导出成功';
                $result['fileurl'] = $fileurl;
			break;
			case 'fagro'://农商免密
				$up = Db::name('parking_pay_sdesecret')->field('pay_money,pay_orderid,pay_ordersn,r_state,date')->where(['date'=>$day])->select();
				if(empty($up)) {
                    $result['status'] = 0;
                    $result['msg'] = '没有对账数据可导出！';
                    $result['fileurl'] = null;
				}
				foreach($up as $key=>$val) {
					$up[$key]['r_state'] 	  = $val['r_state']=='success'?'对账成功':'对账有误';
					$up[$key]['order_id'] 	  = $val['pay_orderid'] ? $val['pay_orderid'] : 'No';
					$up[$key]['low_order_id'] = $val['pay_ordersn'] ? $val['pay_ordersn'] : 'No';
				}
				$fileurl = $this->ExcelsPost($up,$day,$type);

				if(!$fileurl) {
                    $result['status'] = 0;
                    $result['msg'] = '数据导出失败';
                    $result['fileurl'] = null;
				}
                $result['status'] = 1;
                $result['msg'] = '数据导出成功';
                $result['fileurl'] = $fileurl;
			break;
			case 'union'://银联
				$up = Db::name('parking_pay_unionsecret')->field('pay_money,order_id,low_order_id,r_state,date')->where(['date'=>$day])->select();
				if(empty($up)) {
                    $result['status'] = 0;
                    $result['msg'] = '没有对账数据可导出！';
                    $result['fileurl'] = null;
				}

				foreach($up as $key=>$val) {
					$up[$key]['r_state'] 	  = $val['r_state']=='success'?'对账成功':'对账有误';
				}

				$fileurl = $this->ExcelsPost($up,$day,$type);

				if(!$fileurl) {
                    $result['status'] = 0;
                    $result['msg'] = '数据导出失败';
                    $result['fileurl'] = null;
				}
                $result['status'] = 1;
                $result['msg'] = '数据导出成功';
                $result['fileurl'] = $fileurl;
			break;
        }

        return $this->returnHandler($result);
	}
	
	public function seeexcelt()
	{
		$validate = new BaseValidate([
            'openid'  =>'require',
            'type'    =>'require',
			'date'    =>'require',
			'sid'     =>'require',
        ]);
        $validate->goCheck();
        $params = $validate->getParameters();

        $openid  = $params['openid'];
        $type = $params['type'];
		$day = $params['date'];
		$sid = $params['sid'];

		$infos = Db::name('parking_mer_summary')->where(array('sid'=>$sid,'date'=>$day,'pay_type'=>$type))->find();
		$fileurl = $this->SendExcel($infos,$type,$day);
		
		if(!$fileurl) {
			$result['status'] = 0;
			$result['msg'] = '数据导出失败';
			$result['fileurl'] = null;
		}
		$result['status'] = 1;
		$result['msg'] = '数据导出成功';
		$result['fileurl'] = $fileurl;

		return $this->returnHandler($result);
	}

	public function SendExcel($infos,$type,$day) {
		
		$startTimes = strtotime($day.'00:00:00');//开始日期
		$endTime    = strtotime($day.'23:59:59');//结束日期
		$payType = '';
		$tp1	 = '';
		$names   = '';
		$p		 = '';

		switch($type) {
			case 'aq':
				$payType = '聚合支付';
				$tp  = 'wechat';
				$tp1 = 'alipay';
				$names = 'BOJH';
				$p		= 'aq';
			break;
			case 'wx':
				$payType = '微信免密';
				$tp		= 'Fwechat';
				$names = 'BOWX';
				$p		= 'wx';
			break;
			case 'un':
				$payType = '银联无感';
				$tp		= 'Parks';
				$names = 'BOUP';
				$p		= 'union';
			break;
			case 'sd':
				$payType = '农商代扣';
				$tp		= 'FAgro';
				$names = 'BOSB';
				$p		= 'sde';
			break;
		}

		$where['pay_time'] 		= ['between',"{$startTimes},{$endTime}"];
		$where['pay_status']	= 1;
		$where['upOrderId']		= ['neq',''];
		if($tp1 != '') {
			$where['pay_type'] = [['eq',$tp],['eq',$tp1],'or'];
		} else {
			$where['pay_type'] = $tp;
		}
		//统计对应支付部分的退款金额与笔数
		$payRefund = $this->db->table('parking_pay_summary')->field('refund_sum,refund_money')->where(['date'=>$day,'pay_type'=>$p])->item();

		$poly = Db::name('foll_order')->where($where)->field(['pay_time,create_time,pay_account,upOrderId,application,ordersn,RefundMoney,IsWrite,ref_auto'])->select();
		$polyArrs = [];
		if(empty($poly)) {
			$polyArrs[0]['upOrderId']	= 0;		//商户单号
			$polyArrs[0]['ordersn']  	= 0;  		//订单编号
			$polyArrs[0]['body']	    = 0;     	//费用所属
			$polyArrs[0]['create_time']	   = 0;		//交易时间
			$polyArrs[0]['date']	   	   = $day;  //账单日期
			$polyArrs[0]['pay_account']	   = 0;		//交易金额
			$polyArrs[0]['status']	       = '对账成功';//对账状态

		} else {
            $body = '';
			//账单日期，交易时间，订单编号，商户单号，交易金额，费用所属，对账状态
			foreach($poly as $key => $v) {
				if((($v['ref_auto'] == 2) || ($v['IsWrite'] == 103))) {
					$polyArrs[$key]['pay_account'] = sprintf("%.2f",($v['pay_account'] + $v['RefundMoney']));//交易金额
				} else {
					$polyArrs[$key]['pay_account'] = $v['pay_account'];//交易金额
				}

				switch($v['application']){
					case 'parking':
						$body = '路内停车';
					break;
					case 'monthCard':
						$body = '月卡服务';
					break;
					default:
						$body = '其他服务';
					break;
				}

				$polyArrs[$key]['upOrderId']= $v['upOrderId'];//商户单号
				$polyArrs[$key]['ordersn']  = $v['ordersn'];  //订单编号
				$polyArrs[$key]['body']	    = $body;     //费用所属
				$polyArrs[$key]['create_time']	   = date("Y-m-d H:i:s",$v['create_time']);//交易时间
				$polyArrs[$key]['date']	   		   = $day;    //账单日期
				$polyArrs[$key]['status']	       = '对账成功';//对账状态
			}
		}

		$fileName  = $names.$day.'.xlsx';
        $path = ROOT_PATH.'public/sendwx/'.$fileName;

            //账单日期，交易时间，订单编号，商户单号，交易金额，费用所属，对账状态
            $PHPExcel = new PHPExcel(); //实例化PHPExcel类，类似于在桌面上新建一个Excel表格
            $PHPSheet = $PHPExcel->getActiveSheet(); //获得当前活动sheet的操作对象
            $PHPSheet->setTitle('订单清算' . $day); //给当前活动sheet设置名称
            $PHPSheet->setCellValue('A1', '账单日期')
                ->setCellValue('B1', '订单时间')
                ->setCellValue('C1', '订单编号')
                ->setCellValue('D1', '商户单号')
                ->setCellValue('E1', '交易金额')
                ->setCellValue('F1', '费用所属')
                ->setCellValue('G1', '对账状态');

				$PHPSheet->getColumnDimension('A')->setWidth('10');
				$PHPSheet->getColumnDimension('B')->setWidth('20');
				$PHPSheet->getColumnDimension('C')->setWidth('25');
				$PHPSheet->getColumnDimension('D')->setWidth('25');
				$PHPSheet->getColumnDimension('E')->setWidth('10');
				$PHPSheet->getColumnDimension('F')->setWidth('10');
				$PHPSheet->getColumnDimension('G')->setWidth('10');
				$PHPSheet->getColumnDimension('H')->setWidth('10');

            $count = count($polyArrs) - 1;
            $num = 0;
            for ($i = 0; $i <= $count; $i++) {
                $num = 2 + $i;
                $PHPSheet->setCellValue("A" . $num, $polyArrs[$i]['date'])
                    ->setCellValue('B' . $num, $polyArrs[$i]['create_time'])//"\t".$polyArrs[$i]['low_order_id']."\t"
                    ->setCellValue('C' . $num, "\t" . $polyArrs[$i]['ordersn'] . "\t")
                    ->setCellValue('D' . $num, "\t" . $polyArrs[$i]['upOrderId'] . "\t")
                    ->setCellValue('E' . $num, sprintf("%.2f", $polyArrs[$i]['pay_account']))
                    ->setCellValue("F" . $num, $polyArrs[$i]['body'])
                    ->setCellValue("G" . $num, $polyArrs[$i]['status']);
            }

            $num += 2;
            $PHPSheet->setCellValue('A' . $num, '支付方式')
                ->setCellValue('B' . $num, '订单日期')
                ->setCellValue('C' . $num, '订单数量')
                ->setCellValue('D' . $num, '退款总数')
                ->setCellValue('E' . $num, '退款总额')
                ->setCellValue('F' . $num, '交易总额')
                ->setCellValue('G' . $num, '交易费用')
                ->setCellValue('H' . $num, '清算金额');
            $num += 1;
            $PHPSheet->setCellValue('A' . $num, $payType)
                ->setCellValue('B' . $num, $infos['date'])
                ->setCellValue('C' . $num, '共' . $infos['count'] . '笔')
                ->setCellValue('D' . $num, '共' . ($payRefund['refund_sum'] ? $payRefund['refund_sum'] : 0) . '笔')
                ->setCellValue('E' . $num, ($payRefund['refund_money'] ? $payRefund['refund_money'] : 0) . '元')
                ->setCellValue('F' . $num, $infos['pay_account'] . '元')
                ->setCellValue('G' . $num, $infos['pay_fee'] . '元')
                ->setCellValue('H' . $num, $infos['pay_money'] . '元');

            $PHPWriter = PHPExcel_IOFactory::createWriter($PHPExcel, 'Excel2007');
            $PHPWriter->save($path);
		
			if(file_exists($path))
			{
				$result = 'https://shop.gogo198.cn/foll/public/sendwx/'.$fileName;
			}else{
				$result = false;
			}

			return $result;

	}

    private function ExcelsPost($order,&$day,$type='') {

		$PHPExcel = new PHPExcel(); //实例化PHPExcel类，类似于在桌面上新建一个Excel表格
  		$PHPSheet = $PHPExcel->getActiveSheet(); //获得当前活动sheet的操作对象
  		$PHPSheet->setTitle('银企对账数据'.$day); //给当前活动sheet设置名称
  		$PHPSheet->setCellValue('A1','交易金额')
  				 ->setCellValue('B1','平台订单')
  				 ->setCellValue('C1','商户单号')
  				 ->setCellValue('D1','对账状态')
  				 ->setCellValue('E1','账单日期');
		  
		$PHPSheet->getColumnDimension('A')->setWidth('20');
		$PHPSheet->getColumnDimension('B')->setWidth('25');
		$PHPSheet->getColumnDimension('C')->setWidth('25');
		$PHPSheet->getColumnDimension('D')->setWidth('15');
		$PHPSheet->getColumnDimension('E')->setWidth('15');

  		$count = count($order)-1;
  		$num = 0;
  		for($i=0; $i <= $count; $i++) {
  			$num = 2+$i;
  			$PHPSheet->setCellValue("A".$num,sprintf("%.2f",$order[$i]['pay_money']))
  				 ->setCellValue('B'.$num,"\t".$order[$i]['low_order_id']."\t")
  				 ->setCellValue('C'.$num,"\t".$order[$i]['order_id']."\t")
  				 ->setCellValue('D'.$num,($order[$i]['r_state']?$order[$i]['r_state']:0))
  				 ->setCellValue('E'.$num,($order[$i]['date']?$order[$i]['date']:0));
  		}
  		$fname = '';
  		switch($type) {
  			case 'tgpay':
  				$tmsg = '聚合支付';
  				$payType = 'aq';
  				$fname   = 'BAJHbill'.$day;
  			break;
  			case 'fwechat':
  				$tmsg = '微信免密';
  				$payType = 'wx';
  				$fname   = 'BAWXbill'.$day;
  			break;
  			case 'fagro':
  				$tmsg = '农商代扣';
  				$payType = 'sde';
  				$fname   = 'BASBbill'.$day;
  			break;
  			case 'union':
  				$tmsg = '银联无感';
  				$payType = 'union';
  				$fname   = 'BAUPbill'.$day;
  			break;
  		}
  		//查询汇总数据
  		$sum = Db::name('parking_pay_summary')->where(['pay_type'=>$payType,'date'=>$day])->find();
  		if(empty($sum)){
  			return false;
  		}
  		$num += 2;
  		$PHPSheet->setCellValue('A'.$num,'商户号')
  				 ->setCellValue('B'.$num,'账单日期')
  				 ->setCellValue('C'.$num,'订单总数')
				 ->setCellValue('D'.$num,'退款总数')
				 ->setCellValue('E'.$num,'退款总额')
  				 ->setCellValue('F'.$num,'交易总额')
  				 ->setCellValue('G'.$num,'手续费额')
  				 ->setCellValue('H'.$num,'清算金额')
  				 ->setCellValue('I'.$num,'交易类型')
  				 ->setCellValue('J'.$num,'对账状态');
  		$num += 1;
  		$PHPSheet->setCellValue("A".$num,"\t".($sum['code']?$sum['code']:0)."\t")
  				 ->setCellValue('B'.$num,"\t".($sum['date']?$sum['date']:0)."\t")
  				 ->setCellValue('C'.$num,'共'.($sum['pay_sum']?$sum['pay_sum']:0).'笔')
				 ->setCellValue('D'.$num,'共'.($sum['refund_sum']?$sum['refund_sum']:0).'笔')
				 ->setCellValue('E'.$num,($sum['refund_money']?$sum['refund_money']:0).'元')
  				 ->setCellValue('F'.$num,($sum['pay_money']?$sum['pay_money']:0).'元')
  				 ->setCellValue('G'.$num,($sum['pay_fee']?$sum['pay_fee']:0).'元')
  				 ->setCellValue('H'.$num,($sum['pay_money'] - $sum['pay_fee']).'元')
  				 ->setCellValue('I'.$num,$tmsg)
  				 ->setCellValue('J'.$num,($sum['msg']?$sum['msg']:0));

  		$PHPWriter = PHPExcel_IOFactory::createWriter($PHPExcel,'Excel2007');
  		$fileName  = $fname.'.xlsx';
  		$path      = ROOT_PATH.'public/sendwx/'.$fileName;
		$PHPWriter->save($path);

        if(file_exists($path))
        {
            $result = 'https://shop.gogo198.cn/foll/public/sendwx/'.$fileName;
        }else{
            $result = false;
        }

		return $result;
	}

    //获取账单数据
    public function gettrueurl()
    {
        $validate = new BaseValidate([
            'openid'  =>'require',
            'type'    =>'require',
			'status'  =>'isDefault',
			'date'	  =>'require',
        ]);
        $validate->goCheck();
        $params = $validate->getParameters();

        $openid  = $params['openid'];
        $type = $params['type'];
		$status = $params['status'];
		$date = $params['date'];

        $userData = Db::name('smallwechat_user')->where(array('openid'=>$openid))->find();

        //boss oST8o46YGaGx0VwrVJL27vGUxViY
        //cklein oST8o41xs8oNzCG2iK5w2PMzAimQ
        if($openid == 'oST8o46YGaGx0VwrVJL27vGUxViY')
        {
            //boss对账
            if(strpos($userData['auth'],'2') !== false)
            {
                switch ($type) {
                    case 'sd':
                        $url = 'https://shop.gogo198.cn/foll/public/index.php?s=Reconcils/'.$status.'Analyaqs&type=sd';
                        break;
                    case 'un':
                        $url = 'https://shop.gogo198.cn/foll/public/index.php?s=Reconcils/'.$status.'Analyaqs&type=un';
                        break;
                    case 'wx':
                        $url = 'https://shop.gogo198.cn/foll/public/index.php?s=Reconcils/'.$status.'Analyaqs&type=wx';
                        break;
                    case 'aq':
                        $url = 'https://shop.gogo198.cn/foll/public/index.php?s=Reconcils/'.$status.'Analyaqs&type=aq';
                        break;
                }

                $result['types'] = 'manage';
                $result['status'] = 1;
                $result['trueurl'] = $url;
            }
            else
            {
                $result['status'] = 0;
                $result['types'] = 'manage';
                $result['trueurl'] = null;
                $result['msg'] = '暂无权限';
            }

        }else {
            if(strpos($userData['auth'],'2') !== false)
            {
                $where = ['pay_type'=>$type,'date'=>$date];
        		$info = $this->db->table('parking_mer_summary')->where($where)->order('sid asc')->item();

                $result['datas'] = $info;
                $result['types'] = 'customer';
                $result['status'] = 1;

            }else {
                $result['types'] = 'customer';
                $result['status'] = 0;
                $result['msg'] = '暂无权限';
            }

        }

        return $this->returnHandler($result);

    }

    //对账操作
    public function checkorder()
    {
        $validate = new BaseValidate([
            'openid' 		=>'require',
            'checkStatus'   =>'require',
			'orderDatas'    =>'require',
			'error_msg'		=>'require',
        ]);
        $validate->goCheck();
        $params = $validate->getParameters();

        $openid  = $params['openid'];
        $checkStatus = $params['checkStatus'];
        $orderDatas = $params['orderDatas'];

        $info = Db::name('parking_mer_summary')->where(array('sid'=>$orderDatas['sid']))->find();

        if($info['user_check']==1)
        {
            $result['status'] = 1;
            $result['msg'] = '该对账单已经确认,请勿重复提交';
        }
        else {
            if($checkStatus=='0')
            {
				$map['order_check'] = 'no';
				$map['error_msg'] = $params['error_msg'];
            }
            $map = array();
            $map['sid'] = $info['sid'];
			$map['user_check'] = 1;
			

            if(Db::name('parking_mer_summary')->update($map))
            {
                //微信通知管理员
                $orderStatus = $checkStatus == 1 ? '确认无误' : '有误待查';
                switch ($info['pay_type']) {
                	case 'sd':
                		$remark = '伦教停车【农商代扣】'.$info['date'].'清算'.$orderStatus;
                		break;
                	case 'un':
                		$remark = '伦教停车【银联无感】'.$info['date'].'清算'.$orderStatus;
                		break;
                	case 'wx':
                		$remark = '伦教停车【微信免密】'.$info['date'].'清算'.$orderStatus;
                		break;
                	case 'aq':
                		$remark = '伦教停车【聚合支付】'.$info['date'].'清算'.$orderStatus;
                		break;
                }

                //boss ooWwF0p_1SBnxknfhkMv5ux02U1E
                $manage_user = Db::name('mc_mapping_fans')->where(array('uniacid'=>3,'unionid'=>'ooWwF0p_1SBnxknfhkMv5ux02U1E'))->find();

                if($checkStatus==1)
                {
                    $this->SendWechat(json_encode([
                      'call'=>'send_pre_commit_notice',
                      'msg' =>'清算反馈:'.$orderStatus,
                      'name'=>$manage_user['nickname'],
                      'time'=>date('Y-m-d H:i:s',time()),
                      'openid'=>$manage_user['openid'],
                      'remark'=> $remark,
                      'uniacid'=>3,

                    ]));
                }else {
                    $this->SendWechat(json_encode([
                      'call'=>'send_pre_commit_notice',
                      'msg' =>'清算反馈:'.$orderStatus,
                      'name'=>$manage_user['nickname'],
                      'time'=>date('Y-m-d H:i:s',time()),
                      'openid'=>$manage_user['openid'],
                      'remark'=> $remark,
                      'uniacid'=>3,
                      'appid' => 'wx6d1af256d76896ba',
                      'pagepath' => 'pages/user/carbill/carbill?type='.$info['pay_type']
                    ]));
                }

                $result['status'] = 1;
                $result['msg'] = '提交成功';
            }
            else {
                $result['status'] = 0;
                $result['msg'] = '提交失败';
            }
        }
        return $this->returnHandler($result);
    }

    //发送微信通知
	public function SendWechat($data)
	{
		$url = 'http://shop.gogo198.cn/api/sendwechattemplatenotice.php';
        $client = new \GuzzleHttp\Client();
        try {
            //正常请求
            $promise = $client->request('post', $url, ["headers" => ['Content-Type' => 'application/json'],'body'=>$data]);
        } catch (GuzzleHttpExceptionClientException $exception) {
            //捕获异常 输出错误
            return $this->error($exception->getMessage());
        }
	}

    //Curl Get请求
	public function GetUrl($url) {
		//初始化
		$curl = curl_init();
		//设置捉取URL
		curl_setopt($curl,CURLOPT_URL,$url);
		//设置获取的信息以文件流的形式返回，而不是直接输出。
		curl_setopt($curl,CURLOPT_RETURNTRANSFER,1);
		//执行命令
		$res = curl_exec($curl);
		//关闭Curl请求
		curl_close($curl);
		//print_r($res);
		return $res;
	}

}
