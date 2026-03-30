<?php
// 模块LTD提供
function upload_cert($fileinput)//接收微信支付上传信息
{
	global $_W;
	$path = IA_ROOT . '/addons/sz_yi/cert';
	load()->func('file');
	mkdirs($path, '0777');
	$f = $fileinput . '_' . $_W['uniacid'] . '.pem';
	$outfilename = $path . '/' . $f;
	$filename = $_FILES[$fileinput]['name'];
	$tmp_name = $_FILES[$fileinput]['tmp_name'];
	if (!empty($filename) && !empty($tmp_name)) {
		$ext = strtolower(substr($filename, strrpos($filename, '.')));

		if ($ext != '.pem') {
			$_obf_DQ8NDAw5JAoZOAovLQ41KgE7LBMICxE_ = '';

			if ($fileinput == 'weixin_cert_file') {//判断微信的证书
				$_obf_DQ8NDAw5JAoZOAovLQ41KgE7LBMICxE_ = 'CERT文件格式错误';
			}
			else if ($fileinput == 'weixin_key_file') {
				$_obf_DQ8NDAw5JAoZOAovLQ41KgE7LBMICxE_ = 'KEY文件格式错误';
			}
			else {
				if ($fileinput == 'weixin_root_file') {
					$_obf_DQ8NDAw5JAoZOAovLQ41KgE7LBMICxE_ = 'ROOT文件格式错误';
				}
			}

			message($_obf_DQ8NDAw5JAoZOAovLQ41KgE7LBMICxE_ . ',请重新上传!', '', 'error');
		}

		return file_get_contents($tmp_name);
	}

	return '';
}
//借用微信支付
function upload_jie_cert($fileinput)
{
	global $_W;
	$path = IA_ROOT . '/addons/sz_yi/cert';
	load()->func('file');
	mkdirs($path, '0777');
	$f = $fileinput . '_' . $_W['uniacid'] . '.pem';
	$outfilename = $path . '/' . $f;
	$filename = $_FILES[$fileinput]['name'];
	$tmp_name = $_FILES[$fileinput]['tmp_name'];
	if (!empty($filename) && !empty($tmp_name)) {
		$ext = strtolower(substr($filename, strrpos($filename, '.')));

		if ($ext != '.pem') {
			$_obf_DQ8NDAw5JAoZOAovLQ41KgE7LBMICxE_ = '';

			if ($fileinput == 'weixin_jie_cert_file') {
				$_obf_DQ8NDAw5JAoZOAovLQ41KgE7LBMICxE_ = 'CERT文件格式错误';
			}
			else if ($fileinput == 'weixin_jie_key_file') {
				$_obf_DQ8NDAw5JAoZOAovLQ41KgE7LBMICxE_ = 'KEY文件格式错误';
			}
			else {
				if ($fileinput == 'weixin_jie_root_file') {
					$_obf_DQ8NDAw5JAoZOAovLQ41KgE7LBMICxE_ = 'ROOT文件格式错误';
				}
			}

			message($_obf_DQ8NDAw5JAoZOAovLQ41KgE7LBMICxE_ . ',请重新上传!', '', 'error');
		}

		return file_get_contents($tmp_name);
	}

	return '';
}

function upload_alipay_cert($fileinput)
{
	global $_W;
	$path = IA_ROOT . '/addons/sz_yi/cert';
	load()->func('file');
	mkdirs($path, '0777');
	$f = $fileinput . '_' . $_W['uniacid'] . '.pem';
	$outfilename = $path . '/' . $f;
	$filename = $_FILES[$fileinput]['name'];
	$tmp_name = $_FILES[$fileinput]['tmp_name'];
	if (!empty($filename) && !empty($tmp_name)) {
		$ext = strtolower(substr($filename, strrpos($filename, '.')));

		if ($ext != '.pem') {
			$_obf_DQ8NDAw5JAoZOAovLQ41KgE7LBMICxE_ = '';

			if ($fileinput == 'weixin_cert_file') {
				$_obf_DQ8NDAw5JAoZOAovLQ41KgE7LBMICxE_ = 'CERT文件格式错误';
			}
			else if ($fileinput == 'weixin_key_file') {
				$_obf_DQ8NDAw5JAoZOAovLQ41KgE7LBMICxE_ = 'KEY文件格式错误';
			}
			else if ($fileinput == 'weixin_root_file') {
				$_obf_DQ8NDAw5JAoZOAovLQ41KgE7LBMICxE_ = 'ROOT文件格式错误';
			}
			else {
				if ($fileinput == 'alipay_cert_file') {
					$_obf_DQ8NDAw5JAoZOAovLQ41KgE7LBMICxE_ = '支付宝CERT文件格式错误';
				}
			}

			message($_obf_DQ8NDAw5JAoZOAovLQ41KgE7LBMICxE_ . ',请重新上传!', '', 'error');
		}

		$filename = 'cacert.pem';
		move_uploaded_file($tmp_name, dirname(__FILE__) . '/../../../cert/' . $filename);
	}

	return '';
}

if (!defined('IN_IA')) {
	exit('Access Denied');
}

global $_W;
global $_GPC;
$op = (empty($_GPC['op']) ? 'shop' : trim($_GPC['op']));
//获取所属公众号的所有支付信息
$setdata = pdo_fetch('select * from ' . tablename('sz_yi_sysset') . ' where uniacid=:uniacid limit 1', array(':uniacid' => $_W['uniacid']));
//序列化所有支付信息
$set = unserialize($setdata['sets']);
//$set['pay']['tgpaystatus']
//$set['pay']['tgpay']['mchid'],['key']
//echo '<pre>';print_r($set);die;
//$setting = uni_setting($_W['uniacid'], array('payment'));

$oldset = unserialize($setdata['sets']);
//echo "<pre>";print_r($set['pay']['tgpaystatus']);die;
//模板设置
if ($op == 'template') {
	$styles = array();
	$dir = IA_ROOT . '/addons/sz_yi/template/mobile/';

	if ($handle = opendir($dir)) {
		while (($file = readdir($handle)) !== false) {
			if (($file != '..') && ($file != '.') && ($file != 'app')) {
				if (is_dir($dir . '/' . $file)) {
					$styles[] = $file;
				}
			}
		}

		closedir($handle);
	}

	$styles_pc = array();
	$dir_pc = IA_ROOT . '/addons/sz_yi/template/pc/';

	if ($handle_pc = opendir($dir_pc)) {
		while (($file_pc = readdir($handle_pc)) !== false) {
			if (($file_pc != '..') && ($file_pc != '.') && ($file_pc != 'app')) {
				if (is_dir($dir_pc . '/' . $file_pc)) {
					$styles_pc[] = $file_pc;
				}
			}
		}

		closedir($handle_pc);
	}
}
//消息提醒
else if ($op == 'notice') {
	$salers = array();

	if (isset($set['notice']['openid'])) {
		if (!empty($set['notice']['openid'])) {
			$openids = array();
			$strsopenids = explode(',', $set['notice']['openid']);

			foreach ($strsopenids as $openid) {
				$openids[] = '\'' . $openid . '\'';
			}

			$salers = pdo_fetchall('select id,nickname,avatar,openid from ' . tablename('sz_yi_member') . ' where openid in (' . implode(',', $openids) . ') and uniacid=' . $_W['uniacid']);
		}
	}

	$newtype = explode(',', $set['notice']['newtype']);
}
//支付设置
else if ($op == 'pay') {
	$sec = m('common')->getSec();//获取证书
	$sec = iunserializer($sec['sec']);//解序列化
	$cert = IA_ROOT . '/addons/sz_yi/cert/cacert.pem';
}
else {
	if ($op == 'pcset') {
		ca('sysset.save.pcset');

		if (empty($set['shop']['hmenu_name'])) {
			$set['shop']['hmenu_name'] = array('首页', '全部商品', '店铺公告', '成为分销商', '会员中心');
			$set['shop']['hmenu_url'] = array($this->createMobileUrl('shop/index'), $this->createMobileUrl('shop/list', array('order' => 'sales', 'by' => 'desc')), $this->createMobileUrl('shop/notice'), $this->createPluginMobileUrl('commission'), $this->createMobileUrl('member/info'));
			$set['shop']['hmenu_id'] = array('yz01', 'yz02', 'yz03', 'yz04', 'yz05');
		}
	}
}
//提交表单
if (checksubmit()) {
	
	if ($op == 'shop') {
		$shop = (is_array($_GPC['shop']) ? $_GPC['shop'] : array());
		$set['shop']['name'] = trim($shop['name']);
		$set['shop']['cservice'] = trim($shop['cservice']);
		$set['shop']['img'] = save_media($shop['img']);
		$set['shop']['logo'] = save_media($shop['logo']);
		$set['shop']['signimg'] = save_media($shop['signimg']);
		$set['shop']['diycode'] = trim($shop['diycode']);
		$set['shop']['copyright'] = trim($shop['copyright']);
		$set['shop']['credit'] = trim($shop['credit']);
		$set['shop']['credit1'] = trim($shop['credit1']);
		plog('sysset.save.shop', '修改系统设置-商城设置');
	}
	else if ($op == 'pcset') {
		$custom = (is_array($_GPC['pcset']) ? $_GPC['pcset'] : array());
		$set['shop']['ispc'] = trim($custom['ispc']);
		$set['shop']['pctitle'] = trim($custom['pctitle']);
		$set['shop']['pckeywords'] = trim($custom['pckeywords']);
		$set['shop']['pcdesc'] = trim($custom['pcdesc']);
		$set['shop']['pccopyright'] = $pccopyright;
		$set['shop']['pccopyright'] = trim($custom['pccopyright']);
		$set['shop']['pcadv'] = !empty($custom['pcadv']) ? trim($custom['pcadv']) : '';
		$set['shop']['footercontent'] = trim(htmlspecialchars_decode($custom['footercontent']));
		$set['shop']['index'] = $custom['index'];
		$set['shop']['pclogo'] = save_media($custom['pclogo']);
		$set['shop']['reglogo'] = save_media($custom['reglogo']);
		$set['shop']['hmenu_name'] = $custom['hmenu_name'];
		$set['shop']['hmenu_url'] = $custom['hmenu_url'];
		$set['shop']['hmenu_id'] = $custom['hmenu_id'];
		$set['shop']['fmenu_name'] = $custom['fmenu_name'];
		$set['shop']['fmenu_url'] = $custom['fmenu_url'];
		$set['shop']['fmenu_id'] = $custom['fmenu_id'];
		$set['shop']['reccredit'] = $custom['reccredit'];
		$set['shop']['recmoney'] = $custom['recmoney'];
		$set['shop']['subcredit'] = $custom['subcredit'];
		$set['shop']['submoney'] = $custom['submoney'];
		$set['shop']['paytype'] = $custom['paytype'];
		$set['shop']['isreferral'] = $custom['isreferral'];
		$set['shop']['templateid'] = $custom['templateid'];
		$set['shop']['subtext'] = $custom['subtext'];
		$set['shop']['entrytext'] = $custom['entrytext'];
		$set['shop']['subpaycontent'] = $custom['subpaycontent'];
		$set['shop']['recpaycontent'] = $custom['recpaycontent'];
		$set['shop']['referrallogo'] = $custom['referrallogo'];
		plog('sysset.save.pcset', '修改系统设置-PC设置');
	}
	else if ($op == 'sms') {
		$sms = (is_array($_GPC['sms']) ? $_GPC['sms'] : array());
		$set['sms']['type'] = $sms['type'];
		$set['sms']['account'] = $sms['account'];
		$set['sms']['password'] = $sms['password'];
		$set['sms']['appkey'] = $sms['appkey'];
		$set['sms']['secret'] = $sms['secret'];
		$set['sms']['signname'] = $sms['signname'];
		$set['sms']['product'] = $sms['product'];
		$set['sms']['forget'] = $sms['forget'];
		$set['sms']['templateCode'] = $sms['templateCode'];
		$set['sms']['templateCodeForget'] = $sms['templateCodeForget'];
		plog('sysset.save.sms', '修改系统设置-短信设置');
	}
	else if ($op == 'follow') {
		$set['share'] = is_array($_GPC['share']) ? $_GPC['share'] : array();
		$set['share']['icon'] = save_media($set['share']['icon']);
		plog('sysset.save.follow', '修改系统设置-分享及关注设置');
	}
	else if ($op == 'notice') {
		$set['notice'] = is_array($_GPC['notice']) ? $_GPC['notice'] : array();

		if (is_array($_GPC['openids'])) {
			$set['notice']['openid'] = implode(',', $_GPC['openids']);
		}

		$set['notice']['newtype'] = $_GPC['notice']['newtype'];

		if (is_array($set['notice']['newtype'])) {
			$set['notice']['newtype'] = implode(',', $set['notice']['newtype']);
		}

		plog('sysset.save.notice', '修改系统设置-模板消息通知设置');
	}
	else if ($op == 'trade') {
		$set['trade'] = is_array($_GPC['trade']) ? $_GPC['trade'] : array();

		if (!$_W['isfounder']) {
			unset($set['trade']['receivetime']);
			unset($set['trade']['closordertime']);
			unset($set['trade']['paylog']);
		}
		else {
			m('cache')->set('receive_time', $set['trade']['receivetime'], 'global');
			m('cache')->set('closeorder_time', $set['trade']['closordertime'], 'global');
			m('cache')->set('paylog', $set['trade']['paylog'], 'global');
		}

		plog('sysset.save.trade', '修改系统设置-交易设置');
	}
	//点击提交支付设置调到这里
	else if ($op == 'pay') {

		
		$pluginy = p('yunpay');//云支付
		
		if ($pluginy) {
			$pay = $set['pay']['yunpay'];
		}

		$pluginapp = p('app');//APP支付

		if ($pluginapp) {
			$app_weixin = $set['pay']['app_weixin'];
			$app_alipay = $set['pay']['app_alipay'];
		}
		
		//接收所有支付设置数组。
		$set['pay'] = is_array($_GPC['pay']) ? $_GPC['pay'] : array();

		if ($pluginy) {	//云支付 true
			$set['pay']['yunpay'] = $pay;
		}

		if ($pluginapp) {//true
			$set['pay']['app_weixin'] = $app_weixin;
			$set['pay']['app_alipay'] = $app_alipay;
		}
		
		//获取微信支付证书文件   接收证书文件，并更新到sz_yi_sysset表中 sec 字段。
		if ($_FILES['weixin_cert_file']['name']) {
			$sec['cert'] = upload_cert('weixin_cert_file');
		}
		//获取微信支付秘钥文件
		if ($_FILES['weixin_key_file']['name']) {
			$sec['key'] = upload_cert('weixin_key_file');
		}
		//获取微信支付ROOT文件
		if ($_FILES['weixin_root_file']['name']) {
			$sec['root'] = upload_cert('weixin_root_file');
		}

		if ($_FILES['alipay_cert_file']['name']) {
			upload_alipay_cert('alipay_cert_file');
		}

		if (empty($sec['cert']) || empty($sec['key']) || empty($sec['root'])) {
		}
		//获取借用微信支付证书文件
		if ($_FILES['weixin_jie_cert_file']['name']) {
			$sec['jie_cert'] = upload_jie_cert('weixin_jie_cert_file');
		}
		//获取借用微信支付证书文件
		if ($_FILES['weixin_jie_key_file']['name']) {
			$sec['jie_key'] = upload_jie_cert('weixin_jie_key_file');
		}
		//获取借用微信支付证书文件
		if ($_FILES['weixin_jie_root_file']['name']) {
			$sec['jie_root'] = upload_jie_cert('weixin_jie_root_file');
		}
		//序列化更新$sec支付设置到sz_yi_sysset表中，条件等于公众号id		
		pdo_update('sz_yi_sysset', array('sec' => iserializer($sec)), array('uniacid' => $_W['uniacid']));
		plog('sysset.save.pay', '修改系统设置-支付设置');		
	}
	else if ($op == 'template') {
		$shop = (is_array($_GPC['shop']) ? $_GPC['shop'] : array());
		$set['shop']['style'] = save_media($shop['style']);
		$set['shop']['style_pc'] = save_media($shop['style_pc']);
		$set['shop']['theme'] = trim($shop['theme']);
		m('cache')->set('template_shop', $set['shop']['style']);
		m('cache')->set('template_shop_pc', $set['shop']['style_pc']);
		m('cache')->set('theme_shop', $set['shop']['theme']);
		plog('sysset.save.template', '修改系统设置-模板设置');
	}
	else if ($op == 'member') {
		$shop = (is_array($_GPC['shop']) ? $_GPC['shop'] : array());
		$set['shop']['levelname'] = trim($shop['levelname']);
		$set['shop']['levelurl'] = trim($shop['levelurl']);
		$set['shop']['leveltype'] = trim($shop['leveltype']);
		$set['shop']['term'] = trim($shop['term']);
		$set['shop']['term_time'] = trim($shop['term_time']);
		$set['shop']['term_unit'] = trim($shop['term_unit']);
		plog('sysset.save.member', '修改系统设置-会员设置');
		$set['shop']['isbindmobile'] = intval($shop['isbindmobile']);
		$set['shop']['isreferrer'] = intval($shop['isreferrer']);
	}
	else if ($op == 'category') {
		$shop = (is_array($_GPC['shop']) ? $_GPC['shop'] : array());
		$set['shop']['catlevel'] = trim($shop['catlevel']);
		$set['shop']['catshow'] = intval($shop['catshow']);
		$set['shop']['catadvimg'] = save_media($shop['catadvimg']);
		$set['shop']['catadvurl'] = trim($shop['catadvurl']);
		$set['shop']['category2'] = intval($shop['category2']);
		$set['shop']['category2name'] = trim($shop['category2name']);
		plog('sysset.save.category', '修改系统设置-分类层级设置');
	}
	else {
		if ($op == 'contact') {
			$shop = (is_array($_GPC['shop']) ? $_GPC['shop'] : array());
			$set['shop']['qq'] = trim($shop['qq']);
			$set['shop']['address'] = trim($shop['address']);
			$set['shop']['phone'] = trim($shop['phone']);
			$set['shop']['description'] = trim($shop['description']);
			plog('sysset.save.contact', '修改系统设置-联系方式设置');
		}
	}
	//接收$set[pay] 提交上来的数组判断是否为空
	if ($set['pay']['yeepay'] && (empty($set['pay']['merchantaccount']) || empty($set['pay']['merchantPrivateKey']) || empty($set['pay']['merchantPublicKey']) || empty($set['pay']['yeepayPublicKey']))) {
		message('易宝支付设置失败!', $this->createWebUrl('sysset', array('op' => $op)), 'error');
	}
	//Paypal支付
	if ($set['pay']['paypalstatus'] == 1) {
		foreach ($set['pay']['paypal'] as $paypal) {
			if (empty($paypal)) {
				message('请输入正确的Paypal支付接口信息.', $this->createWebUrl('sysset', array('op' => $op)), 'error');
			}
		}
	}
	//tgPay 通莞支付支付
	if ($set['pay']['tgpaystatus'] == 1) {
		foreach ($set['pay']['tgpay'] as $paypal) {
			if (empty($paypal)) {
				message('请输入正确的通莞支付接口信息.', $this->createWebUrl('sysset', array('op' => $op)), 'error');
			}
		}
	}
	

	//把提交上来的$set数组进行序列化，获取当前公众号ID 作为WHERE条件
	$data = array('uniacid' => $_W['uniacid'], 'sets' => iserializer($set));
	// 如果$setdata 等于空,数据为空，插入当前$data数据到sz_yi_sysset表中
	if (empty($setdata)) {
		pdo_insert('sz_yi_sysset', $data);
	}
	else {//否则就更新表中的字段
		pdo_update('sz_yi_sysset', $data, array('uniacid' => $_W['uniacid']));
	}
	//更新后查询出来一条信息
	$setdata = pdo_fetch('select * from ' . tablename('sz_yi_sysset') . ' where uniacid=:uniacid limit 1', array(':uniacid' => $_W['uniacid']));
	//把查询出来的数据保存到缓存中，命名为：sysset
	m('cache')->set('sysset', $setdata);
	//设置成功，返回当前页
	message('设置保存成功!', $this->createWebUrl('sysset', array('op' => $op)), 'success');
}

load()->func('tpl');
include $this->template('web/sysset/' . $op);
exit();

?>
