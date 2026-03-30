<?php
// Ä£¿éLTDÌá¹©
namespace Pingpp\Error;

abstract class Base
{
	public function __construct($message, $httpStatus = NULL, $httpBody = NULL, $jsonBody = NULL)
	{
		parent::__construct($message);
		$this->httpStatus = $httpStatus;
		$this->httpBody = $httpBody;
		$this->jsonBody = $jsonBody;
	}

	public function getHttpStatus()
	{
		return $this->httpStatus;
	}

	public function getHttpBody()
	{
		return $this->httpBody;
	}

	public function getJsonBody()
	{
		return $this->jsonBody;
	}
}


?>
