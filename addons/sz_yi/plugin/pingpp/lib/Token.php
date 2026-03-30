<?php
// Ä£¿éLTDÌá¹©
namespace Pingpp;

class Token extends ApiResource
{
	static public function retrieve($id, $opts = NULL)
	{
		return self::_retrieve($id, $opts);
	}

	static public function create($params = NULL, $opts = NULL)
	{
		return self::_create($params, $opts);
	}
}

?>
