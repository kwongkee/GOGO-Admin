<?php
// 模块LTD提供
namespace Qiniu;

if (!defined('QINIU_FUNCTIONS_VERSION')) {
	define('QINIU_FUNCTIONS_VERSION', Config::SDK_VER);
	function _obf_UWluaXVcY3JjMzJfZmlsZQ__($file)
	{
		$hash = hash_file('crc32b', $file);
		$array = unpack('N', pack('H*', $hash));
		return sprintf('%u', $array[1]);
	}
	function _obf_UWluaXVcY3JjMzJfZGF0YQ__($data)
	{
		$hash = hash('crc32b', $data);
		$array = unpack('N', pack('H*', $hash));
		return sprintf('%u', $array[1]);
	}
	function _obf_UWluaXVcYmFzZTY0X3VybFNhZmVFbmNvZGU_($data)
	{
		$find = array('+', '/');
		$replace = array('-', '_');
		return str_replace($find, $replace, base64_encode($data));
	}
	function _obf_UWluaXVcYmFzZTY0X3VybFNhZmVEZWNvZGU_($str)
	{
		$find = array('-', '_');
		$replace = array('+', '/');
		return base64_decode(str_replace($find, $replace, $str));
	}
	function _obf_UWluaXVcanNvbl9kZWNvZGU_($json, $assoc = false, $depth = 512)
	{
		static $jsonErrors = array(JSON_ERROR_DEPTH => 'JSON_ERROR_DEPTH - Maximum stack depth exceeded', JSON_ERROR_STATE_MISMATCH => 'JSON_ERROR_STATE_MISMATCH - Underflow or the modes mismatch', JSON_ERROR_CTRL_CHAR => 'JSON_ERROR_CTRL_CHAR - Unexpected control character found', JSON_ERROR_SYNTAX => 'JSON_ERROR_SYNTAX - Syntax error, malformed JSON', JSON_ERROR_UTF8 => 'JSON_ERROR_UTF8 - Malformed UTF-8 characters, possibly incorrectly encoded');

		if (empty($json)) {
			return null;
		}

		$data = \json_decode($json, $assoc, $depth);

		if (JSON_ERROR_NONE !== json_last_error()) {
			$last = json_last_error();
			throw new \InvalidArgumentException('Unable to parse JSON data: ' . (isset($jsonErrors[$last]) ? $jsonErrors[$last] : 'Unknown error'));
		}

		return $data;
	}
	function _obf_UWluaXVcZW50cnk_($bucket, $key)
	{
		$en = $bucket;

		if (!empty($key)) {
			$en = $bucket . ':' . $key;
		}

		return base64_urlSafeEncode($en);
	}
	function _obf_UWluaXVcc2V0V2l0aG91dEVtcHR5(&$array, $key, $value)
	{
		if (!empty($value)) {
			$array[$key] = $value;
		}

		return $array;
	}
	function _obf_UWluaXVcdGh1bWJuYWls($url, $mode, $width, $height, $format = NULL, $quality = NULL, $interlace = NULL, $ignoreError = 1)
	{
		static $imageUrlBuilder;

		if (is_null($imageUrlBuilder)) {
			$imageUrlBuilder = new Processing\ImageUrlBuilder();
		}

		return call_user_func_array(array($imageUrlBuilder, 'thumbnail'), func_get_args());
	}
	function _obf_UWluaXVcd2F0ZXJJbWc_($url, $image, $dissolve = 100, $gravity = 'SouthEast', $dx = NULL, $dy = NULL, $watermarkScale = NULL)
	{
		static $imageUrlBuilder;

		if (is_null($imageUrlBuilder)) {
			$imageUrlBuilder = new Processing\ImageUrlBuilder();
		}

		return call_user_func_array(array($imageUrlBuilder, 'waterImg'), func_get_args());
	}
	function _obf_UWluaXVcd2F0ZXJUZXh0($url, $text, $font = '黑体', $fontSize = 0, $fontColor = NULL, $dissolve = 100, $gravity = 'SouthEast', $dx = NULL, $dy = NULL)
	{
		static $imageUrlBuilder;

		if (is_null($imageUrlBuilder)) {
			$imageUrlBuilder = new Processing\ImageUrlBuilder();
		}

		return call_user_func_array(array($imageUrlBuilder, 'waterText'), func_get_args());
	}
}

?>
