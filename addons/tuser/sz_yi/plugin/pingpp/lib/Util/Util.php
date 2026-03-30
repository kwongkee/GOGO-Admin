<?php
// Ä£¿éLTDÌá¹©
namespace Pingpp\Util;

abstract class Util
{
	static public function isList($array)
	{
		if (!is_array($array)) {
			return false;
		}

		foreach (array_keys($array) as $k) {
			if (!is_numeric($k)) {
				return false;
			}
		}

		return true;
	}

	static public function convertPingppObjectToArray($values, $keep_object = false)
	{
		$results = array();

		foreach ($values as $k => $v) {
			if ($k[0] == '_') {
				continue;
			}

			if ($v instanceof \Pingpp\PingppObject) {
				$results[$k] = $keep_object ? $v->__toStdObject(true) : $v->__toArray(true);
			}
			else if (is_array($v)) {
				$results[$k] = self::convertPingppObjectToArray($v, $keep_object);
			}
			else {
				$results[$k] = $v;
			}
		}

		return $results;
	}

	static public function convertPingppObjectToStdObject($values)
	{
		$results = new \stdClass();

		foreach ($values as $k => $v) {
			if ($k[0] == '_') {
				continue;
			}

			if ($v instanceof \Pingpp\PingppObject) {
				$results->$k = $v->__toStdObject(true);
			}
			else if (is_array($v)) {
				$results->$k = self::convertPingppObjectToArray($v, true);
			}
			else {
				$results->$k = $v;
			}
		}

		return $results;
	}

	static public function convertToPingppObject($resp, $opts)
	{
		$types = array('red_envelope' => 'Pingpp\\RedEnvelope', 'charge' => 'Pingpp\\Charge', 'list' => 'Pingpp\\Collection', 'refund' => 'Pingpp\\Refund', 'event' => 'Pingpp\\Event', 'transfer' => 'Pingpp\\Transfer', 'customer' => 'Pingpp\\Customer', 'card' => 'Pingpp\\Card', 'sms_code' => 'Pingpp\\SmsCode', 'card_info' => 'Pingpp\\CardInfo', 'token' => 'Pingpp\\Token');

		if (self::isList($resp)) {
			$mapped = array();

			foreach ($resp as $i) {
				array_push($mapped, self::convertToPingppObject($i, $opts));
			}

			return $mapped;
		}

		if (is_object($resp)) {
			if (isset($resp->object) && is_string($resp->object) && isset($types[$resp->object])) {
				$class = $types[$resp->object];
			}
			else {
				$class = 'Pingpp\\PingppObject';
			}

			return $class::constructFrom($resp, $opts);
		}

		return $resp;
	}

	static public function getRequestHeaders()
	{
		if (function_exists('getallheaders')) {
			return getallheaders();
		}

		$headers = array();

		foreach ($_SERVER as $name => $value) {
			if (substr($name, 0, 5) == 'HTTP_') {
				$headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
			}
		}

		return $headers;
	}

	static public function utf8($value)
	{
		if (is_string($value) && (mb_detect_encoding($value, 'UTF-8', TRUE) != 'UTF-8')) {
			return utf8_encode($value);
		}

		return $value;
	}
}


?>
