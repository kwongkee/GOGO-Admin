<?php
// 模块LTD提供
class ucException
{
	public function errorMessage()
	{
		return $this->getMessage();
	}
}

class InterfaceController
{
	public $para;
	public $error_info = '';
	public $limit;

	public function __construct()
	{
		if (method_exists($this, '_initialize')) {
			$this->_initialize();
		}
	}

	public function _initialize()
	{
		$this->aes = new Aes('', '');

		if ($this->request->server['REQUEST_METHOD'] == 'POST') {
			$this->para = json_decode(urldecode($this->aes->siyuan_aes_decode(str_replace(' ', '+', $this->request->post['para']))), true);
		}

		set_error_handler(array(&$this, 'setErrorInfo'));
	}

	public function validate($data, $rule)
	{
		try {
			if (empty($data)) {
				throw new Exception('参数不能为空');
			}

			$_obf_DSoZNAwqHD4OBDYsATkFFy0iKQwqFhE_ = array_diff($rule, array_keys($data));
			$error_info = array();

			foreach ($_obf_DSoZNAwqHD4OBDYsATkFFy0iKQwqFhE_ as $value) {
				$error_info[] = sprintf('缺少参数:\'%s\'', $value);
			}

			$error_info = implode(',', $error_info);

			if (!empty($error_info)) {
				throw new Exception($error_info);
				return NULL;
			}
		}
		catch (Exception $e) {
			$this->error($e->getMessage());
		}
	}

	public function checkResultAndReturn($result)
	{
		if ($result === NULL) {
			$this->error('网络繁忙');
		}

		if (empty($result)) {
			$this->error('暂无数据');
		}

		$this->success($result);
	}

	public function success($data = array(), $msg = '成功')
	{
		$res = array('result' => '1', 'msg' => $msg, 'data' => $data);
		$this->callBackByAes($res);
	}

	public function error($msg = '网络繁忙')
	{
		$res = array(
			'result' => '0',
			'msg'    => $msg,
			'data'   => array()
			);
		$this->callBackByAes($res);
	}

	public function callBackByAes($json_data)
	{
		$return_data = str_replace('"', '', $this->aes->siyuan_aes_encode(json_encode($json_data, JSON_UNESCAPED_UNICODE)));
		exit($return_data);
	}

	public function getSqlLog()
	{
		$this->load->model('interface/log');
		return $this->model_interface_log->getSqlLog();
	}

	public function addLog()
	{
		$data['para'] = $this->para == 'null' ? '' : json_encode($this->para, JSON_UNESCAPED_UNICODE);
		$data['interface'] = $interface_name = $this->getInterfaceName();
		$data['client_ip'] = $this->getClientIp();
		$data['error_info'] = json_encode($this->error_info, JSON_UNESCAPED_UNICODE);
		$data['is_error'] = (int) !empty($this->error_info);
		$this->load->model('interface/log');
		$r = $this->model_interface_log->addLog($data);
	}

	public function getInterfaceName()
	{
		$route = $this->request->get['route'];
		return substr($route, strpos($route, '/'));
	}

	public function getClientIp()
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

	public function setErrorInfo($errno, $errstr, $errfile, $errline)
	{
		switch ($errno) {
		case 8:
		case 1024:
			$error = 'Notice';
			break;

		case 2:
		case 512:
			$error = 'Warning';
			break;

		case 1:
		case 256:
			$error = 'Fatal Error';
			break;

		default:
			$error = 'Unknown';
			break;
		}

		$this->error_info[] = 'PHP ' . $error . ':  ' . $errstr . ' in ' . $errfile . ' on line ' . $errline;
	}

	public function sent_message($customer_id_array, $message)
	{
		$customer_id_array_str = json_encode($customer_id_array, JSON_UNESCAPED_UNICODE);
		$post_data = "{\"from_peer\": \"58\",\n                \"to_peers\": " . $customer_id_array_str . ",\n                \"message\": \"{\\\"_lctype\\\":-1,\\\"_lctext\\\":\\\"" . $message . "\\\", \\\"_lcattrs\\\":{ \\\"clientId\\\":\\\"58\\\", \\\"clientName\\\":\\\"优产助手\\\", \\\"clientIcon\\\":\\\"http://192.168.1.108/image/icon.png\\\" }}\"\n                , \"conv_id\": \"56ced170816dfa46979cbc23\", \"transient\": false}";
		$data = json_decode($post_data, true);
		$lean_push = new \LeanCloud\LeanMessage($data);
		$response = $lean_push->send();
		return $response;
	}

	public function __call($name, $arguments)
	{
		$interface_name = $this->getInterfaceName();
		$this->error(sprintf('不存在\'%s\'接口', $interface_name));
	}
}


?>
