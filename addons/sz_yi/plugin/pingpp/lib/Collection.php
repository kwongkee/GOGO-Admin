<?php
// Ä£¿éLTDÌá¹©
namespace Pingpp;

class Collection extends ApiResource
{
	public function all($params = NULL, $opts = NULL)
	{
		list($url, $params) = $this->extractPathAndUpdateParams($params);
		list($response, $opts) = $this->_request('get', $url, $params, $opts);
		return Util\Util::convertToPingppObject($response, $opts);
	}

	public function create($params = NULL, $opts = NULL)
	{
		list($url, $params) = $this->extractPathAndUpdateParams($params);
		list($response, $opts) = $this->_request('post', $url, $params, $opts);
		return Util\Util::convertToPingppObject($response, $opts);
	}

	public function retrieve($id, $params = NULL, $opts = NULL)
	{
		list($url, $params) = $this->extractPathAndUpdateParams($params);
		$id = Util\Util::utf8($id);
		$extn = urlencode($id);
		list($response, $opts) = $this->_request('get', $url . '/' . $extn, $params, $opts);
		return Util\Util::convertToPingppObject($response, $opts);
	}

	private function extractPathAndUpdateParams($params)
	{
		$url = parse_url($this->url);

		if (!isset($url['path'])) {
			throw new Error\Api('Could not parse list url into parts: ' . $url);
		}

		if (isset($url['query'])) {
			$query = array();
			parse_str($url['query'], $query);
			$params = array_merge($params ? $params : array(), $query);
		}

		return array($url['path'], $params);
	}
}

?>
