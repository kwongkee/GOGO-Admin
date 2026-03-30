<?php

defined('IN_IA') or exit('Access Denied');

class Ck_homeworkModuleSite extends WeModuleSite {
	
	public function payResult($params) {
		global $_W;
		//现在时间
		$newtimes =  time();
		
		$fee = intval($params['fee']);
		$data = array('paystatus' => $params['result'] == 'success' ? 1 : 0);
		
		//获取订单信息
		$order_show = pdo_get('onljob_pay_order', array('orderid' => $params['tid']));
		//根据参数params中的result来判断支付是否成功
		if ($params['result'] == 'success' && $params['from'] == 'notify') {
			//此处会处理一些支付成功的业务代码
			$datap['weid'] = $order_show['weid'];
			$datap['dateline'] = time();
			$datap['uid'] = $order_show['uid'];
			//$data['name'] = $order_show['name'];
			$datap['moneytype'] = $order_show['type'];
			$datap['moneydesc'] = $order_show['moneydesc'];
			$datap['money'] = $order_show['paymoney'];
			$datap['orderid'] = $order_show['orderid'];
			$datap['accounttype'] = 1;

			if($order_show['status']==0){
                pdo_update('onljob_pay_order', array('status' => 1), array('weid' => $order_show['weid'],'orderid' => $params['tid']));
            }else{
			    //防止已完成订单重复处理
			    exit;
            }
			//存入账目-------------
			pdo_insert('onljob_accounts', $datap, true);
			//----------------------
			
			if($order_show['type'] == 'topup'){
				//修改余额
				pdo_query("UPDATE ".tablename('mc_members')." SET credit2 = credit2+{$params['fee']} WHERE uniacid = '{$_W['uniacid']}' and uid = '".$order_show['uid']."'");
			}elseif($order_show['type'] == 'vip'){
				//存入VIP期限
				$user_show = pdo_get('onljob_user', array('weid' => $_W['uniacid'],'uid' => $order_show['uid']));
				if($user_show['groupsid'] == $order_show['parentid'] && $user_show['endtime'] > $newtimes){
					//未到期续费期限
					$endtime = $order_show['vipdate'] * 86400 + $user_show['endtime'];
				}else{
					//已到期或者新购买
					$endtime = $order_show['vipdate'] * 86400 + $newtimes;
				}
				pdo_query("UPDATE ".tablename('onljob_user')." SET groupsid = '{$order_show['parentid']}', endtime = '{$endtime}' WHERE weid = '{$_W['uniacid']}' and uid = '".$order_show['uid']."'");
			}elseif($order_show['type'] == 'class'){
				
                $result = pdo_get('onljob_theclass', array('id' => $order_show['parentid'],'weid' => $order_show['weid']));
                $url_arr['t_theclass'] = $this->createMobileUrl('t_theclass_show')."&id=".$result['id']."&op=1";
                if ($result){
                    $data = array(
                        'weid' => $result['weid'],
                        'uid' => $order_show['uid'],
                        'bjid' => $result['id'],
                        'tuid' => $result['uid'],
                        'dateline' => time(),
                        'state' => 1
                    );
                    pdo_insert('onljob_theclass_apply', $data);
                    global $_W;
					//发送模板消息---------------------
					require_once ('weixin.class.php');
					$uniacid = $_W['uniacid'];
					//获取公众号配置信息
					$srdb = pdo_get('account_wechats', array('uniacid' => $uniacid));
					$appid = $srdb['key'];
					$appsecret = $srdb['secret'];
					$access_token_odl = $srdb['access_token'];
					//获取模版消息设置
					$mb_config = pdo_get('onljob_config', array('weid' => $_W['uniacid']));
	                //发送加入班级模板消息---------------------
					if(!empty($mb_config['mb_open']) && !empty($appid) && !empty($appsecret)){		
						$access_token = moban($appid,$appsecret,$access_token_odl,$uniacid);
						$pay_uesr=pdo_get('onljob_user', array('uid' => $order_show['uid'],'weid' => $order_show['weid']));
						//获取openid
						$user_openid = pdo_get('mc_mapping_fans', array('uniacid' => $_W['uniacid'],'uid' =>$result['uid']));	
						$first = "您好，有新学生购买并加入你创建的班级啦！";
						$ppurt = $url_arr['t_theclass'];
						if (preg_match('/(http:\/\/)|(https:\/\/)/i', $ppurt)) {
							$url = $ppurt;
						}else{
							$url = $_W['siteroot']."app/".$ppurt;
						}
						$addDateTime = date('Y-m-d H:i:s',time());
						$template = array(
							'touser'=> trim($user_openid['openid']),
							'template_id'=> trim($mb_config['mbid5']), 
							'url'=> $url,
							'topcolor'=>"#FF0000",
							'data'=>array(
							'first'=>array('value'=>urlencode($first),'color'=>"#00008B"),    
							'keyword1'=>array('value'=>urlencode($pay_uesr['name']),'color'=>"#00008B"),    //学生姓名keyword1     
							'keyword2'=>array('value'=>urlencode($addDateTime),'color'=>'#00008B'),        //申请时间keyword2   
							'remark'=>array('value'=>urlencode("点击查看详情。"),'color'=>'#00008B'),
							)
						);		
						$data = urldecode(json_encode($template));
						$send_result = send_template_message($data,$access_token);		
					}
					//---------------------
                }
                //平台抽成
                $config = pdo_get('onljob_config',array('weid'=>$order_show['weid']));
                if($config['bjtc']>0){
                    $money = $order_show['paymoney'] - ($order_show['paymoney'] * $config['bjtc']);
                }else{
                    $money = $order_show['paymoney'];
                }
                $classInfo = pdo_get('onljob_theclass',array('weid'=>$order_show['weid'],'id'=>$order_show['parentid']));
                pdo_query("UPDATE ".tablename('mc_members')." SET credit2 = credit2+{$money} WHERE uniacid = ".$order_show['weid'] ." and uid = '".$classInfo['uid']."'");
                //存入账目-------------
                $datat['weid'] = $order_show['weid'];
                $datat['dateline'] = time();
                $datat['uid'] = $classInfo['uid'];
                $datat['moneytype'] = $order_show['type'];
                $datat['moneydesc'] = '学生'.$order_show['moneydesc'];
                $datat['money'] = $money;
                $datat['orderid'] = $order_show['orderid'];
                $datat['accounttype'] = 2;
                pdo_insert('onljob_accounts', $datat, true);
                //----------------------
            }
			
		}
		
		if ($params['from'] == 'return') {
			if($order_show['type'] == 'zsd'){
				$urltl = $this->createMobileUrl('knowledge', array('op'=>'show','id'=>$order_show['parentid']));
			}elseif($order_show['type'] == 'vip'){
				$urltl = $this->createMobileUrl('m_index');
			}elseif($order_show['type'] == 'class'){
                $urltl = $this->createMobileUrl('theclass');
            }else{
				$urltl = $this->createMobileUrl('index');
			}
			
			if ($params['result'] == 'success') {
				message('支付成功！', $urltl, 'success');
			} else {
				message('支付失败！', $urltl, 'error');
			}
		}
		
	}

}