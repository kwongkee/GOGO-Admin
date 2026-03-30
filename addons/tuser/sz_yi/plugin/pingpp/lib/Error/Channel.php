<?php
// Ä£¿éLTDÌá¹©
namespace Pingpp\Error;

class Channel extends Base
{
	public function __construct($message, $errcode, $param, $httpStatus = NULL, $httpBody = NULL, $jsonBody = NULL)
	{
		parent::__construct($message, $httpStatus, $httpBody, $jsonBody);
		$this->errcode = $errcode;
		$this->param = $param;
	}
}

?>
