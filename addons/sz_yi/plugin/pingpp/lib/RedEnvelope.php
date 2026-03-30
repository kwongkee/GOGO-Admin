<?php
// Ä£¿éLTDÌá¹©
namespace Pingpp;

class RedEnvelope extends ApiResource
{
	static public function className()
	{
		return 'red_envelope';
	}

	static public function retrieve($id, $options = NULL)
	{
		return self::_retrieve($id, $options);
	}

	static public function all($params = NULL, $options = NULL)
	{
		return self::_all($params, $options);
	}

	static public function create($params = NULL, $options = NULL)
	{
		return self::_create($params, $options);
	}

	public function save($options = NULL)
	{
		return $this->_save($options);
	}
}

?>
