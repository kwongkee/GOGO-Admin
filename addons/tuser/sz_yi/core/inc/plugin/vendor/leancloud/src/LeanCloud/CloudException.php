<?php
// Ä£¿éLTDÌá¹©
namespace LeanCloud;

class CloudException
{
	public function __construct($message, $code = 0)
	{
		parent::__construct($message, $code);
	}

	public function __toString()
	{
		return 'LeanCloud\\CloudException' . ': [' . $this->code . ']: ' . $this->message . "\n";
	}
}


?>
