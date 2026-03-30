<?php
// Ä£¿éLTDÌá¹©
namespace Qiniu\Storage;

final class FormUploader
{
	static public function put($upToken, $key, $data, $config, $params, $mime, $checkCrc)
	{
		$fields = array('token' => $upToken);

		if ($key === null) {
			$fname = 'filename';
		}
		else {
			$fname = $key;
			$fields['key'] = $key;
		}

		if ($checkCrc) {
			$fields['crc32'] = \_obf_UWluaXVcY3JjMzJfZGF0YQ__($data);
		}

		if ($params) {
			foreach ($params as $k => $v) {
				$fields[$k] = $v;
			}
		}

		$response = \Qiniu\Http\Client::multipartPost($config->getUpHost(), $fields, 'file', $fname, $data, $mime);

		if (!$response->ok()) {
			return array(null, new \Qiniu\Http\Error($config->getUpHost(), $response));
		}

		return array($response->json(), null);
	}

	static public function putFile($upToken, $key, $filePath, $config, $params, $mime, $checkCrc)
	{
		$fields = array('token' => $upToken, 'file' => self::createFile($filePath, $mime));

		if ($key !== null) {
			$fields['key'] = $key;
		}

		if ($checkCrc) {
			$fields['crc32'] = \_obf_UWluaXVcY3JjMzJfZmlsZQ__($filePath);
		}

		if ($params) {
			foreach ($params as $k => $v) {
				$fields[$k] = $v;
			}
		}

		$fields['key'] = $key;
		$headers = array('Content-Type' => 'multipart/form-data');
		$response = \Qiniu\Http\Client::post($config->getUpHost(), $fields, $headers);

		if (!$response->ok()) {
			return array(null, new \Qiniu\Http\Error($config->getUpHost(), $response));
		}

		return array($response->json(), null);
	}

	static private function createFile($filename, $mime)
	{
		if (function_exists('curl_file_create')) {
			return curl_file_create($filename, $mime);
		}

		$value = '@' . $filename;

		if (!empty($mime)) {
			$value .= ';type=' . $mime;
		}

		return $value;
	}
}


?>
