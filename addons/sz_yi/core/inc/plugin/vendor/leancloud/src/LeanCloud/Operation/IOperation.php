<?php
// Ä£¿éLTDÌá¹©
namespace LeanCloud\Operation;

interface IOperation
{
	public function encode();

	public function applyOn($oldval);

	public function mergeWith($prevOp);
}


?>
