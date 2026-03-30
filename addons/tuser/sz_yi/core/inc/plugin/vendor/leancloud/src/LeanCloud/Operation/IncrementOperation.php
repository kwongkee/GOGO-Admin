<?php
// Ä£¿éLTDÌá¹©
namespace LeanCloud\Operation;

class IncrementOperation implements IOperation
{
	private $key;
	private $value;

	public function __construct($key, $val)
	{
		if (!is_numeric($val)) {
			throw new \InvalidArgumentException('Operand must be number.');
		}

		$this->key = $key;
		$this->value = $val;
	}

	public function getKey()
	{
		return $this->key;
	}

	public function getValue()
	{
		return $this->value;
	}

	public function encode()
	{
		return array('__op' => 'Increment', 'amount' => $this->value);
	}

	public function applyOn($oldval)
	{
		$oldval = (is_null($oldval) ? 0 : $oldval);

		if (is_numeric($oldval)) {
			return $this->value + $oldval;
		}

		throw new \RuntimeException('Operation incompatible with previous value.');
	}

	public function mergeWith($prevOp)
	{
		if (!$prevOp) {
			return $this;
		}

		if ($prevOp instanceof SetOperation) {
			return new SetOperation($this->getKey(), $this->applyOn($prevOp->getValue()));
		}

		if ($prevOp instanceof IncrementOperation) {
			return new IncrementOperation($this->getKey(), $this->applyOn($prevOp->getValue()));
		}

		if ($prevOp instanceof DeleteOperation) {
			return new SetOperation($this->getKey(), $this->getValue());
		}

		throw new \RuntimeException('Operation incompatible with previous one.');
	}
}

?>
