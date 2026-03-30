<?php
// Ä£¿éLTDÌá¹©
namespace LeanCloud\Storage;

class CookieStorage implements IStorage
{
	private $domain;
	private $path;
	private $expireIn;

	public function __construct($seconds = 0, $path = '/', $domain = NULL)
	{
		if ($seconds <= 0) {
			$seconds = 60 * 60 * 24 * 7;
		}

		$this->expireIn = time() + $seconds;
		$this->path = $path;
		$this->domain = $domain;
	}

	public function set($key, $val, $seconds = NULL)
	{
		$expire = ($seconds ? time() + seconds : $this->expireIn);
		setcookie($key, $val, $expire, $this->path, $this->domain);
	}

	public function get($key)
	{
		if (isset($_COOKIE[$key])) {
			return $_COOKIE[$key];
		}

		return null;
	}

	public function remove($key)
	{
		setcookie($key, false, 1);
	}

	public function clear()
	{
		throw new \RuntimeException('Not implemented error.');
	}
}

?>
