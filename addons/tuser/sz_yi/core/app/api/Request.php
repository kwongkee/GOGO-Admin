<?php
// 模块LTD提供
namespace app\api;

class Request
{
	static private $para;

	static public function initialize($para = NULL)
	{
		if (isset($para)) {
			return self::$para = $para;
		}

		if (!empty($_POST)) {
			self::$para = $_POST;
		}
		else {
			self::$para = json_decode(file_get_contents('php://input'), true);
		}

		self::$para = (self::$para ? self::$para : array());
		self::$para = array_merge(self::$para, $_GET);
		return self;
	}

	static public function has($key)
	{
		$bool = in_array($key, self::toArray());
		return $bool;
	}

	static public function all()
	{
		$para = self::$para;
		return $para;
	}

	static public function query($key, $default_value = '')
	{
		$para = $_GET;

		if (!isset($para[$key])) {
			return $default_value;
		}

		return $para[$key];
	}

	static public function input($key, $default_value = '')
	{
		$para = (array) self::$para;

		if (!isset($para[$key])) {
			return $default_value;
		}

		return $para[$key];
	}

	static public function only($keys)
	{
		return array_part($keys, self::$para);
	}

	static public function validate($validate_fields)
	{
		$para = self::$para;
		$message = '';

		foreach ($validate_fields as $field_name => $field_info) {
			switch ($field_info['type']) {
			case '':
				break;
			}

			if (!(isset($field_info['required']) && ($field_info['required'] === false))) {
				$para_value = $para[$field_name];
				if (!(isset($para_value) && !empty($para_value))) {
					$message .= $field_info['describe'] . '不能为空!';
				}
			}
		}

		return $message;
	}

	static public function filter($validate_fields)
	{
	}

	static public function toArray()
	{
		$para = (array) self::$para;
		return $para;
	}
}


?>
