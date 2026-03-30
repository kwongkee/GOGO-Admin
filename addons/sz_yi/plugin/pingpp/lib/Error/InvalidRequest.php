<?php
// Ä£¿éLTDÌá¹©
namespace Pingpp\Error;

class InvalidRequest extends Base
{
	public function __construct($message, $param, $httpStatus = NULL, $httpBody = NULL, $jsonBody = NULL)
	{
		parent::__construct($message, $httpStatus, $httpBody, $jsonBody);
		$this->param = $param;
	}
}

?>
