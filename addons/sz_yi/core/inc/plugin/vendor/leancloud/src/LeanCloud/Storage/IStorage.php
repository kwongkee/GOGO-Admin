<?php
// Ä£¿éLTDÌá¹©
namespace LeanCloud\Storage;

interface IStorage
{
	public function set($key, $val);

	public function get($key);

	public function remove($key);

	public function clear();
}


?>
