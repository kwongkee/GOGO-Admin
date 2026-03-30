<?php

namespace app\api_v3\controller;

use think\Db;
use think\Request;

/**
 * 小程序接口
 * Class WechatCarBill
 * @package app\api_v3\controller
 */
class WechatAuto
{


    public function index()
    {

        $billdata = Db::name('smallwechat_user_bill_sendlog')
        ->alias("s")
        ->join('smallwechat_user_bill b', 's.bill_id = b.id')
        ->field('s.id as sendlog_id,s.*,b.*')
        ->where(array('b.recall'=>0,'s.send_status'=>0))
        ->select();

        //print_r($billdata);

        foreach ($billdata as $k => $v) {
            // if( $v['billtypeval'] == 0 )
            // {
                //定时
                if($v['go_send_time'])
                {
                    $nowtime = time();
                    $sendtime = strtotime( $v['go_send_time'] );
                    if( $nowtime >= $sendtime )
                    {
                        $this->SendWechatTpls($v['usertypeval'],$v['send_openid'],'您有账单需要处理','您有账单需要处理','wx6d1af256d76896ba','pages/user/mybill/detail/detail?jump_home=1&id='.$v['bill_id']);
                        
                        $this->updateSendLog($v['sendlog_id'],1);
                        //创建下一次发送的账单

                        if( $v['billtypeval'] == 0 )
                        {
                            $map = array();
                            $map['openid'] = $v['openid'];
                            $map['project_name'] = $v['project_name'];
                            $map['project_data_name'] = $v['project_data_name'];
                            $map['price'] = $v['price'];
                            $map['unit'] = $v['unit'];
                            $map['num'] = $v['num'];
                            $map['billtypeval'] = $v['billtypeval'];
                            $map['send_type'] = $v['send_type'];
                            $map['usertypeval'] = $v['usertypeval'];
                            $map['send_time'] = $v['send_time'];
                            $map['send_month'] = $v['send_month'];
                            $map['send_date'] = $v['send_date'];
                            $map['send_week'] = $v['send_week'];
                            $map['project_id'] = $v['project_id'];
                            $map['project_data_id'] = $v['project_data_id'];
                            $map['send_openid'] = $v['send_openid'];
                            $map['recall'] = 0;
                            $map['status'] = 0;
                            $map['create_time'] = time();
    
                            $new_log_id = Db::name('smallwechat_user_bill')->insertGetId($map);
                            if( $new_log_id )
                            {
                                $this->insertSendLog($v['sendlog_id'],$new_log_id,0);
                            }
                        }
                        
                    }
                }

            //}
        }
        
    }

    public function insertSendLog($send_log_id,$log_id,$status)
    {
        $sendlog_data = Db::name('smallwechat_user_bill_sendlog')->where(array('id'=>$send_log_id))->find();
        $this_sendtime = $sendlog_data['go_send_time'];

        $billdata = Db::name('smallwechat_user_bill')->where(array('id'=>$log_id))->find();
        switch ($billdata['billtypeval']) {
            case '0':
                switch ($billdata['send_type']) {
                    case 'day':
                        $sendtime = date("Y-m-d H:i",strtotime("+1 day",strtotime($this_sendtime)));
                        $map['go_send_time'] = $sendtime;
                        break;
                    case 'week':
                        $sendtime = date("Y-m-d H:i",strtotime("+1weeks",strtotime($this_sendtime)));
                        $map['go_send_time'] = $sendtime;
                        break;
                    case 'month':
                        $sendtime = date("Y-m-d H:i",strtotime("+1months",strtotime($this_sendtime)));
                        $map['go_send_time'] = $sendtime;
                        break;
                    case 'year':
                        $sendtime = date("Y-m-d H:i",strtotime("+1year",strtotime($this_sendtime)));
                        $map['go_send_time'] = $sendtime;
                        break;    
                }
            break;

        }
    
        $map['bill_id'] = $log_id;
        $map['send_status'] = $status;
        $map['create_time'] = time();
        Db::name('smallwechat_user_bill_sendlog')->insert($map);
    }

    public function updateSendLog($log_id,$status)
    {
        //$logdata = Db::name('smallwechat_user_bill_sendlog')->where(array('log_ids'=>$log_id))->find();
        $map['id'] = $log_id;
        $map['send_status'] = $status;
        Db::name('smallwechat_user_bill_sendlog')->update($map);
    }

    public function SendWechatTpls($user_type,$send_openid,$msg,$remark,$appid = '',$pagepath = '')
	{
        if( $user_type == 0 )
        {
            $user = Db::name('decl_user')->field('user_name as nickname,openid')->where(array('openid'=>$send_openid))->find();
        }else{
            $user = Db::name('mc_mapping_fans')->field('nickname,openid')->where(array('openid'=>$send_openid))->find();
        }

        if($user)
        {
            $this->SendWechat(json_encode([
                'call'=>'send_pre_commit_notice',
                'msg' =>$msg,
                'name'=>$user['nickname'],
                'time'=>date('Y-m-d H:i:s',time()),
                'openid'=>$user['openid'],
                'remark'=> $remark,
                'uniacid'=>3,
                'appid' => $appid,
                'pagepath' => $pagepath
            ]));
        }
		
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