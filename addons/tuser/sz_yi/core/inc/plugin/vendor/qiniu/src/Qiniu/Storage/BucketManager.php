<?php
// Ä£¿éLTDÌá¹©
namespace Qiniu\Storage;

final class BucketManager
{
	private $auth;

	public function __construct(\Qiniu\Auth $auth)
	{
		$this->auth = $auth;
	}

	public function buckets()
	{
		return $this->rsGet('/buckets');
	}

	public function listFiles($bucket, $prefix = NULL, $marker = NULL, $limit = 1000, $delimiter = NULL)
	{
		$query = array('bucket' => $bucket);
		\_obf_UWluaXVcc2V0V2l0aG91dEVtcHR5($query, 'prefix', $prefix);
		\_obf_UWluaXVcc2V0V2l0aG91dEVtcHR5($query, 'marker', $marker);
		\_obf_UWluaXVcc2V0V2l0aG91dEVtcHR5($query, 'limit', $limit);
		\_obf_UWluaXVcc2V0V2l0aG91dEVtcHR5($query, 'delimiter', $delimiter);
		$url = \Qiniu\Config::RSF_HOST . '/list?' . http_build_query($query);
		list($ret, $error) = $this->get($url);

		if ($ret === null) {
			return array(null, null, $error);
		}

		$marker = (array_key_exists('marker', $ret) ? $ret['marker'] : null);
		return array($ret['items'], $marker, null);
	}

	public function stat($bucket, $key)
	{
		$path = '/stat/' . \_obf_UWluaXVcZW50cnk_($bucket, $key);
		return $this->rsGet($path);
	}

	public function delete($bucket, $key)
	{
		$path = '/delete/' . \_obf_UWluaXVcZW50cnk_($bucket, $key);
		list(, $error) = $this->rsPost($path);
		return $error;
	}

	public function rename($bucket, $oldname, $newname)
	{
		return $this->move($bucket, $oldname, $bucket, $newname);
	}

	public function copy($from_bucket, $from_key, $to_bucket, $to_key)
	{
		$from = \_obf_UWluaXVcZW50cnk_($from_bucket, $from_key);
		$to = \_obf_UWluaXVcZW50cnk_($to_bucket, $to_key);
		$path = '/copy/' . $from . '/' . $to;
		list(, $error) = $this->rsPost($path);
		return $error;
	}

	public function move($from_bucket, $from_key, $to_bucket, $to_key)
	{
		$from = \_obf_UWluaXVcZW50cnk_($from_bucket, $from_key);
		$to = \_obf_UWluaXVcZW50cnk_($to_bucket, $to_key);
		$path = '/move/' . $from . '/' . $to;
		list(, $error) = $this->rsPost($path);
		return $error;
	}

	public function changeMime($bucket, $key, $mime)
	{
		$resource = \_obf_UWluaXVcZW50cnk_($bucket, $key);
		$encode_mime = \_obf_UWluaXVcYmFzZTY0X3VybFNhZmVFbmNvZGU_($mime);
		$path = '/chgm/' . $resource . '/mime/' . $encode_mime;
		list(, $error) = $this->rsPost($path);
		return $error;
	}

	public function fetch($url, $bucket, $key = NULL)
	{
		$resource = \_obf_UWluaXVcYmFzZTY0X3VybFNhZmVFbmNvZGU_($url);
		$to = \_obf_UWluaXVcZW50cnk_($bucket, $key);
		$path = '/fetch/' . $resource . '/to/' . $to;
		return $this->ioPost($path);
	}

	public function prefetch($bucket, $key)
	{
		$resource = \_obf_UWluaXVcZW50cnk_($bucket, $key);
		$path = '/prefetch/' . $resource;
		list(, $error) = $this->ioPost($path);
		return $error;
	}

	public function batch($operations)
	{
		$params = 'op=' . implode('&op=', $operations);
		return $this->rsPost('/batch', $params);
	}

	private function rsPost($path, $body = NULL)
	{
		$url = \Qiniu\Config::RS_HOST . $path;
		return $this->post($url, $body);
	}

	private function rsGet($path)
	{
		$url = \Qiniu\Config::RS_HOST . $path;
		return $this->get($url);
	}

	private function ioPost($path, $body = NULL)
	{
		$url = \Qiniu\Config::IO_HOST . $path;
		return $this->post($url, $body);
	}

	private function get($url)
	{
		$headers = $this->auth->authorization($url);
		$ret = \Qiniu\Http\Client::get($url, $headers);

		if (!$ret->ok()) {
			return array(null, new \Qiniu\Http\Error($url, $ret));
		}

		return array($ret->json(), null);
	}

	private function post($url, $body)
	{
		$headers = $this->auth->authorization($url, $body, 'application/x-www-form-urlencoded');
		$ret = \Qiniu\Http\Client::post($url, $body, $headers);

		if (!$ret->ok()) {
			return array(null, new \Qiniu\Http\Error($url, $ret));
		}

		$r = ($ret->body === null ? array() : $ret->json());
		return array($r, null);
	}

	static public function buildBatchCopy($source_bucket, $key_pairs, $target_bucket)
	{
		return self::twoKeyBatch('copy', $source_bucket, $key_pairs, $target_bucket);
	}

	static public function buildBatchRename($bucket, $key_pairs)
	{
		return self::buildBatchMove($bucket, $key_pairs, $bucket);
	}

	static public function buildBatchMove($source_bucket, $key_pairs, $target_bucket)
	{
		return self::twoKeyBatch('move', $source_bucket, $key_pairs, $target_bucket);
	}

	static public function buildBatchDelete($bucket, $keys)
	{
		return self::oneKeyBatch('delete', $bucket, $keys);
	}

	static public function buildBatchStat($bucket, $keys)
	{
		return self::oneKeyBatch('stat', $bucket, $keys);
	}

	static private function oneKeyBatch($operation, $bucket, $keys)
	{
		$data = array();

		foreach ($keys as $key) {
			array_push($data, $operation . '/' . \_obf_UWluaXVcZW50cnk_($bucket, $key));
		}

		return $data;
	}

	static private function twoKeyBatch($operation, $source_bucket, $key_pairs, $target_bucket)
	{
		if ($target_bucket === null) {
			$target_bucket = $source_bucket;
		}

		$data = array();

		foreach ($key_pairs as $from_key => $to_key) {
			$from = \_obf_UWluaXVcZW50cnk_($source_bucket, $from_key);
			$to = \_obf_UWluaXVcZW50cnk_($target_bucket, $to_key);
			array_push($data, $operation . '/' . $from . '/' . $to);
		}

		return $data;
	}
}


?>
