<?php
// Ä£¿éLTDÌá¹©
namespace LeanCloud\Operation;

class ArrayOperation implements IOperation
{
	private $key;
	private $value;
	private $opType;

	public function __construct($key, $val, $opType)
	{
		if (!in_array($opType, array('Add', 'AddUnique', 'Remove'))) {
			throw new \InvalidArgumentException('Operation on array not ' . 'supported: ' . $opType . '.');
		}

		if (!is_array($val)) {
			throw new \InvalidArgumentException('Operand must be array.');
		}

		$this->key = $key;
		$this->value = $val;
		$this->opType = $opType;
	}

	public function getKey()
	{
		return $this->key;
	}

	public function getOpType()
	{
		return $this->opType;
	}

	public function getValue()
	{
		return $this->value;
	}

	public function encode()
	{
		return array('__op' => $this->getOpType(), 'objects' => \LeanCloud\LeanClient::encode($this->value));
	}

	private function add($oldval)
	{
		return array_merge($oldval, $this->getValue());
	}

	private function addUnique($oldval)
	{
		$newval = $oldval;
		$found = array();

		foreach ($oldval as $obj) {
			if ($obj instanceof LeanObject && $obj->getObjectId()) {
				$found[$obj->getObjectId()] = true;
			}
		}

		foreach ($this->getValue() as $obj) {
			if ($obj instanceof LeanObject && $obj->getObjectId()) {
				if (isset($found[$obj->getObjectId()])) {
				}
				else {
					$found[$obj->getObjectId()] = true;
					$newval[] = $obj;
				}
			}
			else {
				if (!in_array($obj, $newval)) {
					$newval[] = $obj;
				}
			}
		}

		return $newval;
	}

	private function remove($oldval)
	{
		$newval = array();
		$remove = $this->getValue();

		foreach ($oldval as $item) {
			if (!in_array($item, $remove)) {
				$newval[] = $item;
			}
		}

		return $newval;
	}

	public function applyOn($oldval)
	{
		if (!$oldval) {
			$oldval = array();
		}

		if (!is_array($oldval)) {
			throw new \RuntimeException('Operation incompatible' . ' with previous value.');
		}

		if ($this->getOpType() === 'Add') {
			return $this->add($oldval);
		}

		if ($this->getOpType() === 'AddUnique') {
			return $this->addUnique($oldval);
		}

		if ($this->getOpType() === 'Remove') {
			return $this->remove($oldval);
		}

		throw new \RuntimeException('Operation type ' . $this->getOptype() . ' not supported.');
	}

	public function mergeWith($prevOp)
	{
		if (!$prevOp) {
			return $this;
		}

		if ($prevOp instanceof SetOperation) {
			if (!is_array($prevOp->getValue())) {
				throw new \RuntimeException('Operation incompatible ' . 'with previous value.');
			}

			return new SetOperation($this->key, $this->applyOn($prevOp->getValue()));
		}

		if ($prevOp instanceof ArrayOperation && ($this->getOpType() === $prevOp->getOpType())) {
			if ($this->getOpType() === 'Remove') {
				$objects = array_merge($prevOp->getValue(), $this->getValue());
			}
			else {
				$objects = $this->applyOn($prevOp->getValue());
			}

			return new ArrayOperation($this->key, $objects, $this->getOpType());
		}

		if ($prevOp instanceof DeleteOperation) {
			if ($this->getOpType() === 'Remove') {
				return $prevOp;
			}

			return new SetOperation($this->getKey(), $this->applyOn(null));
		}

		throw new \RuntimeException('Operation incompatible with' . ' previous one.');
	}
}

?>
