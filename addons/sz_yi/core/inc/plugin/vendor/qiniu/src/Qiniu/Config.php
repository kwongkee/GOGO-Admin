<?php
// Ä£¿éLTDÌá¹©
namespace Qiniu;

final class Config
{
	const SDK_VER = '7.0.8';
	const BLOCK_SIZE = 4194304;
	const IO_HOST = 'http://iovip.qbox.me';
	const RS_HOST = 'http://rs.qbox.me';
	const RSF_HOST = 'http://rsf.qbox.me';
	const API_HOST = 'http://api.qiniu.com';

	private $upHost;
	private $upHostBackup;

	public function __construct(Zone $z = NULL)
	{
		if ($z === null) {
			$z = Zone::zone0();
		}

		$this->upHost = $z->upHost;
		$this->upHostBackup = $z->upHostBackup;
	}

	public function getUpHost()
	{
		return $this->upHost;
	}

	public function getUpHostBackup()
	{
		return $this->upHostBackup;
	}
}


?>
