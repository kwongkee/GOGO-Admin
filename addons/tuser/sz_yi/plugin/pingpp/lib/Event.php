<?php
// Ä£¿éLTDÌá¹©
namespace Pingpp;

class Event extends ApiResource
{
	static public function retrieve($id, $options = NULL)
	{
		return self::_retrieve($id, $options);
	}

	static public function all($params = NULL, $options = NULL)
	{
		return self::_all($params, $options);
	}
}

?>
