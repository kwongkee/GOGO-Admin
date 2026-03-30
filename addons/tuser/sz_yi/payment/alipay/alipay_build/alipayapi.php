<?php
// 模块LTD提供
echo "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">\r\n<html>\r\n<head>\r\n\t<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\">\r\n\t<title>支付宝批量付款到支付宝账户有密接口接口</title>\r\n</head>\r\n";
require_once 'alipay.config.php';
require_once 'lib/alipay_submit.class.php';
$notify_url = 'http://商户网关地址/batch_trans_notify-PHP-UTF-8/notify_url.php';
$email = $_POST['WIDemail'];
$account_name = $_POST['WIDaccount_name'];
$pay_date = $_POST['WIDpay_date'];
$batch_no = $_POST['WIDbatch_no'];
$batch_fee = $_POST['WIDbatch_fee'];
$batch_num = $_POST['WIDbatch_num'];
$detail_data = $_POST['WIDdetail_data'];
$parameter = array('service' => 'batch_trans_notify', 'partner' => trim($alipay_config['partner']), 'notify_url' => $notify_url, 'email' => $email, 'account_name' => $account_name, 'pay_date' => $pay_date, 'batch_no' => $batch_no, 'batch_fee' => $batch_fee, 'batch_num' => $batch_num, 'detail_data' => $detail_data, '_input_charset' => trim(strtolower($alipay_config['input_charset'])));
$alipaySubmit = new AlipaySubmit($alipay_config);
$html_text = $alipaySubmit->buildRequestForm($parameter, 'get', '确认');
echo $html_text;
echo "</body>\r\n</html>";

?>
