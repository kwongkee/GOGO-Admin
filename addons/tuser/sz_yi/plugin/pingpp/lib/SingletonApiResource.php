<?php
// Ä£¿éLTDÌá¹©
namespace Pingpp;

abstract class SingletonApiResource extends ApiResource
{
	static protected function _singletonRetrieve($options = NULL)
	{
		$opts = Util\RequestOptions::parse($options);
		$instance = new static(null, $opts);
		$instance->refresh();
		return $instance;
	}

	static public function classUrl()
	{
		$base = static::className();
		return '/v1/' . $base;
	}

	public function instanceUrl()
	{
		return static::classUrl();
	}
}

?>
