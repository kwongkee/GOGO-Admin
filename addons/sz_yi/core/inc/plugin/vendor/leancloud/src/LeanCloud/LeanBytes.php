<?php
// Ä£¿éLTDÌá¹©
namespace LeanCloud;

class LeanBytes
{
	private $byteArray = array();

	static public function createFromByteArray(array $byteArray)
	{
		$bytes = new LeanBytes();
		$bytes->byteArray = $byteArray;
		return $bytes;
	}

	static public function createFromBase64Data($data)
	{
		$bytes = new LeanBytes();
		$byteMap = unpack('C*', base64_decode($data));

		foreach ($byteMap as $byte) {
			$bytes->byteArray[] .= $byte;
		}

		return $bytes;
	}

	public function getByteArray()
	{
		return $this->byteArray;
	}

	public function asString()
	{
		$str = '';

		foreach ($this->byteArray as $byte) {
			$str .= chr($byte);
		}

		return $str;
	}

	public function encode()
	{
		return array('__type' => 'Bytes', 'base64' => base64_encode($this->asString()));
	}
}


?>
