<?php
// 模块LTD提供
namespace app\api;

class Base
{
	protected $error_info;

	public function __construct()
	{
		Request::initialize();
		$log = $this->addLog();
	}

	public function getPara()
	{
		$para = Request::toArray();
		return $para;
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

	protected function callBackByAes($json_data)
	{
		header('Content-Type: application/json');

		if (isset($_GET['is_test'])) {
			dump($json_data);
		}

		$return_data = json_encode_ex($json_data);
		exit($return_data);
	}

	protected function addLog()
	{
		$data['para'] = $this->getPara() == 'null' ? '' : json_encode_ex($this->getPara());
		$data['api'] = $_GET['api'];
		$data['client_ip'] = $this->getClientIp();
		$data['error_info'] = '';
		$data['is_error'] = '';
		$data['date_added'] = date('Y-m-d H:i:s');
		return pdo_insert('sz_yi_api_log', $data);
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
