<?php
// 模块LTD提供
if (!defined('IN_IA')) {
	exit('Access Denied');
}

global $_W;
global $_GPC;

$operation = (!empty($_GPC['op']) ? $_GPC['op'] : 'display');

$enterprise_members = pdo_fetch('select * from ' . tablename('enterprise_members') . ' where openid=:openid and uniacid=:uniacid limit 1', array(':uniacid' => $_W['uniacid'], ':openid' => $_W['openid']));
$legal_info = pdo_fetch('select * from '.tablename('enterprise_basicinfo').' where member_id=:mid',[':mid'=>$enterprise_members['id']]);

if(!$enterprise_members['mobile'] || !$enterprise_members)
{
    $url = 'Location:/app/index.php?i=' . $_W['uniacid'] . '&c=entry&do=enterprise&m=sz_yi&p=register';
	return header($url);
}

if (($operation == 'register') && $_W['ispost']) {

	// 查询是否已经注册,商户账户合计表
	$reg = pdo_fetch('select * from ' . tablename('total_merchant_account') . ' where mobile=:mobile limit 1', array(':mobile' => $enterprise_members['mobile']));
	if( $reg )
	{
		show_json(1, array('msg' => '该账号已经注册！'));
		return false;
	}else{
		$basic_info = pdo_fetch('select * from ' . tablename('enterprise_basicinfo') . ' where member_id=:member_id limit 1', array(':member_id' => $enterprise_members['id']));
		if(empty($basic_info['operName'])){
            $basic_info['operName'] = $enterprise_members['realname'];
        }
		$user_data = [
			'unique_id'=>md5(($enterprise_members['mobile'].date('YmdHis'))),
			'mobile'=>$enterprise_members['mobile'],
			'password'=>password_hash($enterprise_members['mobile'], PASSWORD_DEFAULT),
			'uniacid' => $_W['uniacid'],
			'user_name' => $basic_info['operName'],
			'company_name' =>$basic_info['name'],
			'create_time'=>time(),
			'desc' =>'',
			'status'=>1,
			'user_email'=>'',
			'address'=>$basic_info['address'],
			'company_tel'=>'',
			'account_type'=>2,
			'openid' => $_W['openid'],
			'enterprise_id' => $basic_info['member_id']
		];
		pdo_insert('total_merchant_account', $user_data);
		$uid = pdo_insertid();

		// 微信通知boss
		$messages = array(
			'first' => array('value' => '您有一个新商户注册', 'color' => '#73a68d'),
            'keyword1' => array('value' => $basic_info['operName'], 'color' => '#73a68d'),
			'keyword2' => array('value' => $enterprise_members['mobile'], 'color' => '#73a68d'),
			'keyword3' => array('value' => date('Y-m-d H:i:s',time()), 'color' => '#73a68d'),
			'remark' => array('value' => '请点击登录审核！', 'color' => '#73a68d')
		);
		$boss_openid = 'ov3-bt8keSKg_8z9Wwi-zG1hRhwg';//ov3-bt5vIxepEjWc51zRQNQbFSaQ   ov3-bt8keSKg_8z9Wwi-zG1hRhwg
		$template_id = 'uCKu03A9w-KMWdubLLj8InB82NYKUFInfJIHqpsgHr4';
		//2021-09-24,https问题
// 		$check_url = $this->createMobileUrl('member/account_check',array('uid'=>$uid));
        $check_url = 'http://shop.gogo198.cn/app/index.php?i=3&c=entry&p=account_check&uid='.$uid.'&do=member&m=sz_yi';
		file_put_contents($_SERVER['DOCUMENT_ROOT'].'/13hhh.txt',json_encode($check_url));
		m('message')->sendTplNotice($boss_openid, $template_id, $messages, $check_url);
		show_json(1, array('msg' => '注册成功'));
	}

}else if($operation == 'finish2'){
	include $this->template('enterprise/finish2');
}elseif($operation=='save_zfb_form'){
    //废弃
    $_SESSION['zfb_form'] = $_GPC['form'];

    if(!empty($_SESSION['zfb_form'])){
        //form标签存储在session后，链接生成二维码
        $errorCorrectionLevel = 'L';//错误等级，忽略
        $matrixPointSize = 4;
        require_once IA_ROOT.'/addons/sz_yi/phpqrcode.php';
        $path = '/addons/sz_yi/static/QRcode/'; //储存的地方
        if (!is_dir(IA_ROOT.$path)) {
            load()->func('file');
            mkdirs(IA_ROOT.$path); //创建文件夹
        }
        $infourl = 'https://shop.gogo198.cn/app/index.php?i=3&c=entry&do=enterprise&m=sz_yi&p=finish&op=open_zfb_form';
        $filename =  $path.time().'.png'; //图片文件
        QRcode::png($infourl, IA_ROOT.$filename, $errorCorrectionLevel, $matrixPointSize, 2); //生成图片
        $orderFileCcollectionImg = str_replace('/www/wwwroot/gogo','https://shop.gogo198.cn',IA_ROOT.$filename);
        pdo_update('enterprise_members',['code_img'=>$orderFileCcollectionImg],['openid'=>$_W['openid']]);
        show_json(1,['img'=>$orderFileCcollectionImg]);
    }
}elseif($operation=='open_zfb_form'){
    //废弃
    if(empty($_W['openid'])){
        //当用户没有关注公众号要先关注
        header('Location:https://mp.weixin.qq.com/mp/profile_ext?action=home&__biz=MzA4MDQyMTMxMQ==&scene=110#wechat_redirect');
    }
    $zfb_form = $_SESSION['zfb_form'];
//    $zfb_form = json_decode(json_encode($_SESSION['zfb_form'],true),true);
    echo htmlspecialchars_decode($zfb_form,ENT_QUOTES);exit;
    include $this->template('enterprise/open_zfb_form');
}elseif($operation=='send_msg') {
    //发送短信消息给法人
    $mobile = $_GPC['mobile'];
    $realname = $_GPC['realname'];

    //1、先记录支付宝返回的form内容
    $res = pdo_fetch('select id from ' . tablename('enterprise_legaler_verify') . ' where mobile=:mobile and realname=:realname', [':mobile' => $mobile, ':realname' => $realname]);
    if ($res['id']) {
        show_json(1, ['msg' => '您已通知过法人，请勿重复通知！谢谢配合。']);
    }
    pdo_update('enterprise_members', ['realname' => $realname], ['mobile' => $mobile]);
    pdo_insert('enterprise_legaler_verify', ['mobile' => $mobile, 'html_info' => $_GPC['form'], 'realname' => $realname]);
    $insertId = pdo_insertid();
    $url_mobile = base64_encode($insertId);
    $url = 'api/sendSmsMsg.php?mbe=' . $url_mobile;
    $res = send_user_check_sms2($mobile, 'SMS_242701821', json_encode(['name' => $realname, 'code' => $url]));
//    print_r($res);die;
    if($res['sub_msg']=='参数超过长度限制'){
        show_json(1,['msg'=>'通知失败！']);
    }elseif($res['sub_msg']!='参数超过长度限制'){
        show_json(1,['msg'=>'通知成功！']);
    }
}else{
	include $this->template('enterprise/finish');
}
