<?php
// Ä£¿éLTDÌá¹©
namespace Pingpp;

abstract class ApiResource extends PingppObject
{
	static private $HEADERS_TO_PERSIST = array('Pingpp-Version' => true);

	static public function baseUrl()
	{
		return Pingpp::$apiBase;
	}

	public function refresh()
	{
		$requestor = new ApiRequestor($this->_opts->apiKey, static::baseUrl());
		$url = $this->instanceUrl();
		list($response, $this->_opts->apiKey) = $requestor->request('get', $url, $this->_retrieveOptions, $this->_opts->headers);
		$this->refreshFrom($response, $this->_opts);
		return $this;
	}

	static public function className()
	{
		$class = get_called_class();

		if ($postfix = strrchr($class, '\\')) {
			$class = substr($postfix, 1);
		}

		if ($postfixFakeNamespaces = strrchr($class, '')) {
			$class = $postfixFakeNamespaces;
		}

		if (substr($class, 0, strlen('Pingpp')) == 'Pingpp') {
			$class = substr($class, strlen('Pingpp'));
		}

		$class = str_replace('_', '', $class);
		$name = urlencode($class);
		$name = strtolower($name);
		return $name;
	}

	static public function classUrl()
	{
		$base = static::className();
		return '/v1/' . $base . 's';
	}

	public function instanceUrl()
	{
		$id = $this['id'];
		$class = get_called_class();

		if ($id === null) {
			$message = 'Could not determine which URL to request: ' . $class . ' instance has invalid ID: ' . $id;
			throw new Error\InvalidRequest($message, null);
		}

		$id = Util\Util::utf8($id);
		$base = static::classUrl();
		$extn = urlencode($id);
		return $base . '/' . $extn;
	}

	static private function _validateParams($params = NULL)
	{
		if ($params && !is_array($params)) {
			$message = 'You must pass an array as the first argument to Pingpp API ' . 'method calls.';
			throw new Error\Api($message);
		}
	}

	protected function _request($method, $url, $params = array(), $options = NULL)
	{
		$opts = $this->_opts->merge($options);
		return static::_staticRequest($method, $url, $params, $opts);
	}

	static protected function _staticRequest($method, $url, $params, $options)
	{
		$opts = Util\RequestOptions::parse($options);
		$requestor = new ApiRequestor($opts->apiKey, static::baseUrl());
		list($response, $opts->apiKey) = $requestor->request($method, $url, $params, $opts->headers);

		foreach ($opts->headers as $k => $v) {
			if (!array_key_exists($k, self::$HEADERS_TO_PERSIST)) {
				unset($opts->headers[$k]);
			}
		}

		return array($response, $opts);
	}

	static protected function _retrieve($id, $options = NULL)
	{
		$opts = Util\RequestOptions::parse($options);
		$instance = new static($id, $opts);
		$instance->refresh();
		return $instance;
	}

	static protected function _all($params = NULL, $options = NULL)
	{
		self::_validateParams($params);
		$url = static::classUrl();
		list($response, $opts) = static::_staticRequest('get', $url, $params, $options);
		return Util\Util::convertToPingppObject($response, $opts);
	}

	static protected function _create($params = NULL, $options = NULL)
	{
		self::_validateParams($params);
		$base = static::baseUrl();
		$url = static::classUrl();
		list($response, $opts) = static::_staticRequest('post', $url, $params, $options);
		return Util\Util::convertToPingppObject($response, $opts);
	}

	protected function _save($options = NULL)
	{
		$params = $this->serializeParameters();

		if (0 < count($params)) {
			$url = $this->instanceUrl();
			list($response, $opts) = $this->_request('put', $url, $params, $options);
			$this->refreshFrom($response, $opts);
		}

		return $this;
	}

	protected function _delete($params = NULL, $options = NULL)
	{
		self::_validateParams($params);
		$url = $this->instanceUrl();
		list($response, $opts) = $this->_request('delete', $url, $params, $options);
		$this->refreshFrom($response, $opts);
		return $this;
	}
}

?>
