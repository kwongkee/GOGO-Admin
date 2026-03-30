<?php
// Ä£¿éLTDÌá¹©
namespace LeanCloud\Storage;

class SessionStorage implements IStorage
{
	static private $storageKey = 'LCData';

	public function __construct()
	{
		if (!isset($_SESSION[static::$storageKey])) {
			$_SESSION[static::$storageKey] = array();
		}
	}

	public function set($key, $val)
	{
		$_SESSION[static::$storageKey][$key] = $val;
	}

	public function get($key)
	{
		if (isset($_SESSION[static::$storageKey][$key])) {
			return $_SESSION[static::$storageKey][$key];
		}

		return null;
	}

	public function remove($key)
	{
		unset($_SESSION[static::$storageKey][$key]);
	}

	public function clear()
	{
		$_SESSION[static::$storageKey] = array();
	}
}

?>
