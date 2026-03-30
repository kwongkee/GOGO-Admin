<?php
// Ä£¿éLTDÌá¹©
namespace LeanCloud\Operation;

class DeleteOperation implements IOperation
{
	private $key;

	public function __construct($key)
	{
		$this->key = $key;
	}

	public function getKey()
	{
		return $this->key;
	}

	public function encode()
	{
		return array('__op' => 'Delete');
	}

	public function applyOn($oldval = NULL)
	{
		return null;
	}

	public function mergeWith($prevOp)
	{
		return $this;
	}
}

?>
