<?php
// Ä£¿éLTDÌá¹©
namespace Pingpp;

class Pingpp
{
	const VERSION = '2.1.3';

	static public $apiKey;
	static public $apiBase = 'https://api.pingxx.com';
	static public $apiVersion = '2015-10-10';
	static public $verifySslCerts = true;
	static public $privateKeyPath;
	static public $privateKey;

	static public function getApiKey()
	{
		return self::$apiKey;
	}

	static public function setApiKey($apiKey)
	{
		self::$apiKey = $apiKey;
	}

	static public function getApiVersion()
	{
		return self::$apiVersion;
	}

	static public function setApiVersion($apiVersion)
	{
		self::$apiVersion = $apiVersion;
	}

	static public function getVerifySslCerts()
	{
		return self::$verifySslCerts;
	}

	static public function setVerifySslCerts($verify)
	{
		self::$verifySslCerts = $verify;
	}

	static public function getPrivateKeyPath()
	{
		return self::$privateKeyPath;
	}

	static public function setPrivateKeyPath($path)
	{
		self::$privateKeyPath = $path;
	}

	static public function getPrivateKey()
	{
		return self::$privateKey;
	}

	static public function setPrivateKey($key)
	{
		self::$privateKey = $key;
	}
}


?>
