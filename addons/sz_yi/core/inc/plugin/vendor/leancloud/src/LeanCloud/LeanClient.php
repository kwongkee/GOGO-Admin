<?php
// Ä£¿éLTDÌá¹©
namespace LeanCloud;

class LeanClient
{
	const VERSION = '0.2.0';

	static private $api = array('CN' => 'https://api.leancloud.cn', 'US' => 'https://us-api.leancloud.cn');
	static private $apiRegion = 'CN';
	static private $apiVersion = '1.1';
	static private $apiTimeout = 15;
	static private $appId;
	static private $appKey;
	static private $appMasterKey;
	static private $useMasterKey = false;
	static private $useProduction = false;
	static private $defaultHeaders;
	static private $storage;

	static public function initialize($appId, $appKey, $appMasterKey)
	{
		self::$appId = $appId;
		self::$appKey = $appKey;
		self::$appMasterKey = $appMasterKey;
		self::$defaultHeaders = array('X-LC-Id' => self::$appId, 'Content-Type' => 'application/json;charset=utf-8', 'User-Agent' => self::getVersionString());

		if (!self::$storage) {
			self::$storage = new Storage\SessionStorage();
		}

		LeanUser::registerClass();
		LeanRole::registerClass();
	}

	static private function assertInitialized()
	{
		if (!isset($appId) && !isset($appKey) && !isset($appMasterKey)) {
			throw new \RuntimeException('Client is not initialized, ' . 'please specify application key ' . 'with LeanClient::initialize.');
		}
	}

	static private function getVersionString()
	{
		return 'LeanCloud PHP SDK ' . self::VERSION;
	}

	static public function useRegion($region)
	{
		if (!isset(self::$api[$region])) {
			throw new \RuntimeException('Invalid API region: ' . $region . '.');
		}

		self::$apiRegion = $region;
	}

	static public function useProduction($flag)
	{
		self::$useProduction = ($flag ? true : false);
	}

	static public function useMasterKey($flag)
	{
		self::$useMasterKey = ($flag ? true : false);
	}

	static public function getAPIEndPoint()
	{
		return self::$api[self::$apiRegion] . '/' . self::$apiVersion;
	}

	static public function buildHeaders($sessionToken, $useMasterKey)
	{
		if (is_null($useMasterKey)) {
			$useMasterKey = self::$useMasterKey;
		}

		$h = self::$defaultHeaders;
		$h['X-LC-Prod'] = self::$useProduction ? 1 : 0;
		$timestamp = time();
		$key = ($useMasterKey ? self::$appMasterKey : self::$appKey);
		$sign = md5($timestamp . $key);
		$h['X-LC-Sign'] = $sign . ',' . $timestamp;

		if ($useMasterKey) {
			unset($h['X-LC-Sign']);
			$h['X-LC-Key'] = $key;
		}

		if ($sessionToken) {
			$h['X-LC-Session'] = $sessionToken;
		}

		return $h;
	}

	static public function request($method, $path, $data, $sessionToken = NULL, $headers = array(), $useMasterKey = NULL)
	{
		self::assertInitialized();
		$url = self::getAPIEndPoint();
		$url .= $path;
		$defaultHeaders = self::buildHeaders($sessionToken, $useMasterKey);

		if (empty($headers)) {
			$headers = $defaultHeaders;
		}
		else {
			$headers = array_merge($defaultHeaders, $headers);
		}

		if (strpos($headers['Content-Type'], '/json') !== false) {
			$json = json_encode($data);
		}

		$headersList = array_map(function($key, $val) {
			return $key . ': ' . $val;
		}, array_keys($headers), $headers);
		$req = curl_init($url);
		curl_setopt($req, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($req, CURLOPT_HTTPHEADER, $headersList);
		curl_setopt($req, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($req, CURLOPT_TIMEOUT, self::$apiTimeout);

		switch ($method) {
		case 'GET':
			if ($data) {
				curl_setopt($req, CURLOPT_URL, $url . '?' . http_build_query($data));
			}

			break;

		case 'POST':
			curl_setopt($req, CURLOPT_POST, 1);
			curl_setopt($req, CURLOPT_POSTFIELDS, $json);
			break;

		case 'PUT':
			curl_setopt($req, CURLOPT_POSTFIELDS, $json);
			curl_setopt($req, CURLOPT_CUSTOMREQUEST, $method);
		case 'DELETE':
			curl_setopt($req, CURLOPT_CUSTOMREQUEST, $method);
			break;

		default:
			break;
		}

		$resp = curl_exec($req);
		$respCode = curl_getinfo($req, CURLINFO_HTTP_CODE);
		$respType = curl_getinfo($req, CURLINFO_CONTENT_TYPE);
		$error = curl_errno($req);
		$errno = curl_errno($req);
		curl_close($req);

		if (0 < $errno) {
			throw new \RuntimeException('CURL connection (' . $url . ') error: ' . $errno . ' ' . $error, $errno);
		}

		if (strpos($respType, 'text/html') !== false) {
			throw new CloudException('Bad request', -1);
		}

		$data = json_decode($resp, true);

		if (isset($data['error'])) {
			$code = (isset($data['code']) ? $data['code'] : -1);
			throw new CloudException($code . ' ' . $data['error'], $code);
		}

		return $data;
	}

	static public function get($path, $data = NULL, $sessionToken = NULL, $headers = array(), $useMasterKey = NULL)
	{
		return self::request('GET', $path, $data, $sessionToken, $headers, $useMasterKey);
	}

	static public function post($path, $data, $sessionToken = NULL, $headers = array(), $useMasterKey = NULL)
	{
		return self::request('POST', $path, $data, $sessionToken, $headers, $useMasterKey);
	}

	static public function put($path, $data, $sessionToken = NULL, $headers = array(), $useMasterKey = NULL)
	{
		return self::request('PUT', $path, $data, $sessionToken, $headers, $useMasterKey);
	}

	static public function delete($path, $sessionToken = NULL, $headers = array(), $useMasterKey = NULL)
	{
		return self::request('DELETE', $path, null, $sessionToken, $headers, $useMasterKey);
	}

	static public function batch($requests, $sessionToken = NULL, $headers = array(), $useMasterKey = NULL)
	{
		$response = LeanClient::post('/batch', array('requests' => $requests), $sessionToken, $headers, $useMasterKey);

		if (count($requests) != count($response)) {
			throw new CloudException('Number of resquest and response ' . 'mismatch in batch operation!');
		}

		return $response;
	}

	static public function multipartEncode($file, $params, $boundary = NULL)
	{
		if (!$boundary) {
			$boundary = md5(microtime());
		}

		$body = '';

		foreach ($params as $key => $val) {
			$body .= '--' . $boundary . "\nContent-Disposition: form-data; name=\"" . $key . "\"\n\n" . $val . "\n";
		}

		if (!empty($file)) {
			$mimeType = 'application/octet-stream';

			if (isset($file['mimeType'])) {
				$mimeType = $file['mimeType'];
			}

			$filename = filter_var($file['name'], FILTER_SANITIZE_MAGIC_QUOTES);
			$body .= '--' . $boundary . "\nContent-Disposition: form-data; name=\"file\"; filename=\"" . $filename . "\"\nContent-Type: " . $mimeType . "\n\n" . $file['content'] . "\n";
		}

		$body .= '--' . $boundary . "\n";
		return $body;
	}

	static public function uploadToQiniu($token, $content, $name, $mimeType = NULL)
	{
		$boundary = md5(microtime());
		$file = array('name' => $name, 'content' => $content, 'mimeType' => $mimeType);
		$params = array('token' => $token, 'key' => $name);
		$body = static::multipartEncode($file, $params, $boundary);
		$headers[] = 'User-Agent: ' . self::getVersionString();
		$headers[] = 'Content-Type: multipart/form-data;' . ' boundary=' . $boundary;
		$headers[] = 'Content-Length: ' . strlen($body);
		$url = 'http://upload.qiniu.com';
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
		$resp = curl_exec($ch);
		$respCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		$respType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
		$error = curl_errno($ch);
		$errno = curl_errno($ch);
		curl_close($ch);

		if (0 < $errno) {
			throw new \RuntimeException('CURL connection (' . $url . ') error: ' . $errno . ' ' . $error, $errno);
		}

		$data = json_decode($resp, true);

		if (isset($data['error'])) {
			$code = (isset($data['code']) ? $data['code'] : -1);
			throw new CloudException($code . ' ' . $data['error'], $code);
		}

		return $data;
	}

	static public function encode($value)
	{
		if (is_null($value) || is_scalar($value)) {
			return $value;
		}

		if ($value instanceof \DateTime || $value instanceof \DateTimeImmutable) {
			return array('__type' => 'Date', 'iso' => self::formatDate($value));
		}

		if ($value instanceof LeanObject) {
			return $value->getPointer();
		}

		if ($value instanceof Operation\IOperation || $value instanceof GeoPoint || $value instanceof LeanBytes || $value instanceof LeanACL || $value instanceof LeanFile) {
			return $value->encode();
		}

		if (is_array($value)) {
			$res = array();

			foreach ($value as $key => $val) {
				$res[$key] = self::encode($val);
			}

			return $res;
		}

		throw new \RuntimeException('Dont know how to encode ' . gettype($value));
	}

	static public function formatDate($date)
	{
		$utc = new \DateTime($date->format('c'));
		$utc->setTimezone(new \DateTimezone('UTC'));
		$iso = $utc->format('Y-m-d\\TH:i:s.u');
		$iso = substr($iso, 0, 23) . 'Z';
		return $iso;
	}

	static public function decode($value, $key)
	{
		if (!is_array($value)) {
			return $value;
		}

		if ($key == 'ACL') {
			return new LeanACL($value);
		}

		if (!isset($value['__type'])) {
			$out = array();

			foreach ($value as $k => $v) {
				$out[$k] = self::decode($v, $k);
			}

			return $out;
		}

		$type = $value['__type'];

		if ($type == 'Date') {
			return new \DateTime($value['iso']);
		}

		if ($type == 'Bytes') {
			return LeanBytes::createFromBase64Data($value['base64']);
		}

		if ($type == 'GeoPoint') {
			return new GeoPoint($value['latitude'], $value['longitude']);
		}

		if ($type == 'File') {
			$file = new LeanFile($value['name']);
			$file->mergeAfterFetch($value);
			return $file;
		}

		if (($type == 'Pointer') || ($type == 'Object')) {
			$obj = LeanObject::create($value['className'], $value['objectId']);
			unset($value['__type']);
			unset($value['className']);

			if (!empty($value)) {
				$obj->mergeAfterFetch($value);
			}

			return $obj;
		}

		if ($type == 'Relation') {
			return new LeanRelation(null, $key, $value['className']);
		}
	}

	static public function getStorage()
	{
		return self::$storage;
	}

	static public function setStorage($storage)
	{
		self::$storage = $storage;
	}

	static public function randomFloat($min = 0, $max = 1)
	{
		$M = mt_getrandmax();
		return $min + ((mt_rand(0, $M - 1) / $M) * ($max - $min));
	}
}


?>
