<?php
// 模块LTD提供
namespace api;

class Base
{
	protected $para;
	protected $error_info;

	public function __construct()
	{
		$this->aes = new \Aes('hrbin-yunzs-2016', '');
		$this->para = json_decode(urldecode($this->aes->siyuan_aes_decode(str_replace(' ', '+', $_POST['para']))), TRUE);
	}

	public function getPara()
	{
		return $this->para;
	}

	public function returnSuccess($data = array(), $msg = '成功')
	{
		if (is_array($data)) {
			array_walk_recursive($data, function(&$item) {
				if (is_null($item)) {
					$item = '';
				}

				if (is_float($item) || is_int($item)) {
					$item = (string) $item;
				}
			});
		}
		else {
			if (is_null($data)) {
				$data = '';
			}
		}

		$res = array('result' => '1', 'msg' => $msg, 'data' => $data);

		if (defined('IS_API_DOC')) {
			exit(json_encode_ex($res));
			return NULL;
		}

		if (is_test()) {
			exit(json_encode_ex($res));
			return NULL;
		}

		$this->callBackByAes($res);
	}

	public function returnError($msg = '网络繁忙')
	{
		$res = array(
			'result' => '0',
			'msg'    => $msg,
			'data'   => array()
			);

		if (defined('IS_API_DOC')) {
			exit(json_encode_ex($res));
			return NULL;
		}

		if (is_test()) {
			exit(json_encode_ex($res));
			return NULL;
		}

		$this->callBackByAes($res);
	}

	public function validate($expect_keys)
	{
		$expect_keys = explode(',', $expect_keys);

		if (is_array($this->para)) {
			$_obf_DTMTLwoZJFw9GDA0ISkhCz8UWz8yODI_ = array_keys($this->para);
			$_obf_DSIIHjFcJAs3Lw0NEgEnMQIvPQYDHjI_ = array_diff($expect_keys, $_obf_DTMTLwoZJFw9GDA0ISkhCz8UWz8yODI_);
		}

		if (0 < count($_obf_DSIIHjFcJAs3Lw0NEgEnMQIvPQYDHjI_)) {
			$_obf_DSIIHjFcJAs3Lw0NEgEnMQIvPQYDHjI_ = implode(',', $_obf_DSIIHjFcJAs3Lw0NEgEnMQIvPQYDHjI_);
			$this->returnError('缺少参数:' . $_obf_DSIIHjFcJAs3Lw0NEgEnMQIvPQYDHjI_);
		}

		return true;
	}

	protected function callBackByAes($json_data)
	{
		if (isset($_GET['is_test'])) {
			dump($json_data);
		}

		$return_data = str_replace('"', '', $this->aes->siyuan_aes_encode(json_encode_ex($json_data)));
		exit($return_data);
	}

	protected function addLog()
	{
		$data['para'] = $this->para == 'null' ? '' : json_encode_ex($this->para);
		$data['api'] = $_GET['api'];
		$data['client_ip'] = $this->getClientIp();
		$data['error_info'] = '';
		$data['is_error'] = '';
		$data['date_added'] = date('Y-m-d H:i:s');
		pdo_insert('sz_yi_api_log', $data);
	}

	public function setErrorInfo($errno, $errstr, $errfile, $errline)
	{
		switch ($errno) {
		case E_NOTICE:
		case E_USER_NOTICE:
			$error = 'Notice';
			break;

		case E_WARNING:
		case E_USER_WARNING:
			$error = 'Warning';
			break;

		case E_ERROR:
		case E_USER_ERROR:
			$error = 'Fatal Error';
			break;

		default:
			$error = 'Unknown';
			break;
		}

		$this->error_info[] = 'PHP ' . $error . ':  ' . $errstr . ' in ' . $errfile . ' on line ' . $errline;

		if (is_test()) {
			dump($this->error_info);
		}
	}

	protected function getClientIp()
	{
		if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
			$cip = $_SERVER['HTTP_CLIENT_IP'];
		}
		else if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
			$cip = $_SERVER['HTTP_X_FORWARDED_FOR'];
		}
		else {
			if (!empty($_SERVER['REMOTE_ADDR'])) {
				$cip = $_SERVER['REMOTE_ADDR'];
			}
		}

		return $cip;
	}
}


?>
