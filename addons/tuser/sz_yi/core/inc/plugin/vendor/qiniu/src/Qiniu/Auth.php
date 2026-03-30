<?php
// Ä£¿éLTDÌá¹©
namespace Qiniu;

final class Auth
{
	private $accessKey;
	private $secretKey;
	static private $policyFields = array('callbackUrl', 'callbackBody', 'callbackHost', 'callbackBodyType', 'callbackFetchKey', 'returnUrl', 'returnBody', 'endUser', 'saveKey', 'insertOnly', 'detectMime', 'mimeLimit', 'fsizeMin', 'fsizeLimit', 'persistentOps', 'persistentNotifyUrl', 'persistentPipeline', 'deleteAfterDays');
	static private $deprecatedPolicyFields = array('asyncOps');

	public function __construct($accessKey, $secretKey)
	{
		$this->accessKey = $accessKey;
		$this->secretKey = $secretKey;
	}

	public function sign($data)
	{
		$hmac = hash_hmac('sha1', $data, $this->secretKey, true);
		return $this->accessKey . ':' . \_obf_UWluaXVcYmFzZTY0X3VybFNhZmVFbmNvZGU_($hmac);
	}

	public function signWithData($data)
	{
		$data = \_obf_UWluaXVcYmFzZTY0X3VybFNhZmVFbmNvZGU_($data);
		return $this->sign($data) . ':' . $data;
	}

	public function signRequest($urlString, $body, $contentType = NULL)
	{
		$url = parse_url($urlString);
		$data = '';

		if (array_key_exists('path', $url)) {
			$data = $url['path'];
		}

		if (array_key_exists('query', $url)) {
			$data .= '?' . $url['query'];
		}

		$data .= "\n";
		if (($body !== null) && ($contentType === 'application/x-www-form-urlencoded')) {
			$data .= $body;
		}

		return $this->sign($data);
	}

	public function verifyCallback($contentType, $originAuthorization, $url, $body)
	{
		$authorization = 'QBox ' . $this->signRequest($url, $body, $contentType);
		return $originAuthorization === $authorization;
	}

	public function privateDownloadUrl($baseUrl, $expires = 3600)
	{
		$deadline = time() + $expires;
		$pos = strpos($baseUrl, '?');

		if ($pos !== false) {
			$baseUrl .= '&e=';
		}
		else {
			$baseUrl .= '?e=';
		}

		$baseUrl .= $deadline;
		$token = $this->sign($baseUrl);
		return $baseUrl . '&token=' . $token;
	}

	public function uploadToken($bucket, $key = NULL, $expires = 3600, $policy = NULL, $strictPolicy = true)
	{
		$deadline = time() + $expires;
		$scope = $bucket;

		if ($key !== null) {
			$scope .= ':' . $key;
		}

		$args = array();
		$args = self::copyPolicy($args, $policy, $strictPolicy);
		$args['scope'] = $scope;
		$args['deadline'] = $deadline;
		$b = json_encode($args);
		return $this->signWithData($b);
	}

	static private function copyPolicy(&$policy, $originPolicy, $strictPolicy)
	{
		if ($originPolicy === null) {
			return array();
		}

		foreach ($originPolicy as $key => $value) {
			if (in_array((string) $key, self::$deprecatedPolicyFields, true)) {
				throw new \InvalidArgumentException($key . ' has deprecated');
			}

			if (!$strictPolicy || in_array((string) $key, self::$policyFields, true)) {
				$policy[$key] = $value;
			}
		}

		return $policy;
	}

	public function authorization($url, $body = NULL, $contentType = NULL)
	{
		$authorization = 'QBox ' . $this->signRequest($url, $body, $contentType);
		return array('Authorization' => $authorization);
	}
}


?>
