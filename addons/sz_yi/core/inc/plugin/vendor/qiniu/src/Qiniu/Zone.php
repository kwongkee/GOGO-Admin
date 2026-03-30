<?php
// Ä£¿éLTDÌá¹©
namespace Qiniu;

final class Zone
{
	public $upHost;
	public $upHostBackup;

	public function __construct($upHost, $upHostBackup)
	{
		$this->upHost = $upHost;
		$this->upHostBackup = $upHostBackup;
	}

	static public function zone0()
	{
		return new self('http://up.qiniu.com', 'http://upload.qiniu.com');
	}

	static public function zone1()
	{
		return new self('http://up-z1.qiniu.com', 'http://upload-z1.qiniu.com');
	}
}


?>
