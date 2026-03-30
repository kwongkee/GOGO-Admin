<?php
// Ä£¿éLTDÌá¹©
class Aes
{
	private $key;
	private $val;

	public function __construct($key, $val)
	{
		if ($key) {
			$this->key = $key;
		}
		else {
			$this->key = 'hrbin-uchat-2015';
		}

		$this->out = '';
	}

	public function removePKCS7($instr)
	{
		$imax = strlen($instr);
		$i = 0;

		while ($i < $imax) {
			if (16 < ord($instr[$i])) {
				$this->out .= $instr[$i];
			}

			++$i;
		}

		return $this->out;
	}

	public function paddingPKCS7($data)
	{
		$block_size = 16;
		$padding_char = $block_size - (strlen($data) % $block_size);
		$data .= str_repeat(chr($padding_char), $padding_char);
		return $data;
	}

	public function aes_encode($data)
	{
		return base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $this->key, $this->paddingPKCS7($data), MCRYPT_MODE_ECB, $this->val));
	}

	public function aes_decode($data)
	{
		return mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $this->key, base64_decode($data), MCRYPT_MODE_ECB, $this->val);
	}

	public function siyuan_aes_encode($data)
	{
		return self::aes_encode($data);
	}

	public function siyuan_aes_decode($data)
	{
		return self::removePKCS7(self::aes_decode($data));
	}

	static public function strToHex($string)
	{
		$hex = '';
		$i = 0;

		while ($i < strlen($string)) {
			$hex .= dechex(ord($string[$i]));
			++$i;
		}

		$hex = strtoupper($hex);
		return $hex;
	}

	static public function swap($hex)
	{
		$string = '';
		$i = 0;

		while ($i < (strlen($hex) - 1)) {
			$string .= chr(hexdec($hex[$i + 1] . $hex[$i]));
			$i += 2;
		}

		return $string;
	}

	static public function getSecretCode($instr)
	{
		return 'X' . self::swap(self::strToHex($instr));
	}
}


?>
