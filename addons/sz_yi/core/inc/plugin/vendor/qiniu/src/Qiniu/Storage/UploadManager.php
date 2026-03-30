<?php
// Ä£¿éLTDÌá¹©
namespace Qiniu\Storage;

final class UploadManager
{
	private $config;

	public function __construct(\Qiniu\Config $config = NULL)
	{
		if ($config === null) {
			$config = new \Qiniu\Config();
		}

		$this->config = $config;
	}

	public function put($upToken, $key, $data, $params = NULL, $mime = 'application/octet-stream', $checkCrc = false)
	{
		$params = self::trimParams($params);
		return FormUploader::put($upToken, $key, $data, $this->config, $params, $mime, $checkCrc);
	}

	public function putFile($upToken, $key, $filePath, $params = NULL, $mime = 'application/octet-stream', $checkCrc = false)
	{
		$file = fopen($filePath, 'rb');

		if ($file === false) {
			throw new \Exception('file can not open', 1);
		}

		$params = self::trimParams($params);
		$stat = fstat($file);
		$size = $stat['size'];

		if ($size <= \Qiniu\Config::BLOCK_SIZE) {
			$data = fread($file, $size);
			fclose($file);

			if ($data === false) {
				throw new \Exception('file can not read', 1);
			}

			return FormUploader::put($upToken, $key, $data, $this->config, $params, $mime, $checkCrc);
		}

		$up = new ResumeUploader($upToken, $key, $file, $size, $params, $mime, $this->config);
		$ret = $up->upload();
		fclose($file);
		return $ret;
	}

	static public function trimParams($params)
	{
		if ($params === null) {
			return null;
		}

		$ret = array();

		foreach ($params as $k => $v) {
			$pos = strpos($k, 'x:');
			if (($pos === 0) && !empty($v)) {
				$ret[$k] = $v;
			}
		}

		return $ret;
	}
}


?>
