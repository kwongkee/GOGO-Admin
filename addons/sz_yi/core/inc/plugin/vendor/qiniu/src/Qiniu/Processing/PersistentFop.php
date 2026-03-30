<?php
// Ä£¿éLTDÌá¹©
namespace Qiniu\Processing;

final class PersistentFop
{
	private $auth;
	private $bucket;
	private $pipeline;
	private $notify_url;
	private $force;

	public function __construct($auth, $bucket, $pipeline = NULL, $notify_url = NULL, $force = false)
	{
		$this->auth = $auth;
		$this->bucket = $bucket;
		$this->pipeline = $pipeline;
		$this->notify_url = $notify_url;
		$this->force = $force;
	}

	public function execute($key, $fops)
	{
		if (is_array($fops)) {
			$fops = implode(';', $fops);
		}

		$params = array('bucket' => $this->bucket, 'key' => $key, 'fops' => $fops);
		\_obf_UWluaXVcc2V0V2l0aG91dEVtcHR5($params, 'pipeline', $this->pipeline);
		\_obf_UWluaXVcc2V0V2l0aG91dEVtcHR5($params, 'notifyURL', $this->notify_url);

		if ($this->force) {
			$params['force'] = 1;
		}

		$data = http_build_query($params);
		$url = \Qiniu\Config::API_HOST . '/pfop/';
		$headers = $this->auth->authorization($url, $data, 'application/x-www-form-urlencoded');
		$headers['Content-Type'] = 'application/x-www-form-urlencoded';
		$response = \Qiniu\Http\Client::post($url, $data, $headers);

		if (!$response->ok()) {
			return array(null, new \Qiniu\Http\Error($url, $response));
		}

		$r = $response->json();
		$id = $r['persistentId'];
		return array($id, null);
	}

	static public function status($id)
	{
		$url = \Qiniu\Config::API_HOST . '/status/get/prefop?id=' . $id;
		$response = \Qiniu\Http\Client::get($url);

		if (!$response->ok()) {
			return array(null, new \Qiniu\Http\Error($url, $response));
		}

		return array($response->json(), null);
	}
}


?>
