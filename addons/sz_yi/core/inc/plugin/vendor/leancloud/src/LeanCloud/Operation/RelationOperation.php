<?php
// Ä£¿éLTDÌá¹©
namespace LeanCloud\Operation;

class RelationOperation implements IOperation
{
	private $key;
	private $targetClassName;
	private $objects_to_add = array();
	private $objects_to_remove = array();

	public function __construct($key, $adds, $removes)
	{
		if (empty($adds) && empty($removes)) {
			throw new \InvalidArgumentException('Operands are empty.');
		}

		$this->key = $key;
		$this->remove($removes);
		$this->add($adds);
	}

	public function getKey()
	{
		return $this->key;
	}

	public function getTargetClassName()
	{
		return $this->targetClassName;
	}

	public function encode()
	{
		$adds = array(
			'__op'    => 'AddRelation',
			'objects' => array()
			);
		$removes = array(
			'__op'    => 'RemoveRelation',
			'objects' => array()
			);

		foreach ($this->objects_to_add as $obj) {
			$adds['objects'][] = $obj->getPointer();
		}

		foreach ($this->objects_to_remove as $obj) {
			$removes['objects'][] = $obj->getPointer();
		}

		if (empty($this->objects_to_remove)) {
			return $adds;
		}

		if (empty($this->objects_to_add)) {
			return $removes;
		}

		return array(
	'__op' => 'Batch',
	'ops'  => array($adds, $removes)
	);
	}

	private function add($objects)
	{
		if (empty($objects)) {
			return NULL;
		}

		if (!$this->targetClassName) {
			$this->targetClassName = current($objects)->getClassName();
		}

		foreach ($objects as $obj) {
			if (!$obj->getObjectId()) {
				throw new \RuntimeException('Cannot add unsaved object' . ' to relation.');
			}

			if ($obj->getClassName() !== $this->targetClassName) {
				throw new \RuntimeException('Object type incompatible' . ' with relation.');
			}

			if (isset($this->objects_to_remove[$obj->getObjectID()])) {
				unset($this->objects_to_remove[$obj->getObjectID()]);
			}

			$this->objects_to_add[$obj->getObjectId()] = $obj;
		}
	}

	private function remove($objects)
	{
		if (empty($objects)) {
			return NULL;
		}

		if (!$this->targetClassName) {
			$this->targetClassName = current($objects)->getClassName();
		}

		foreach ($objects as $obj) {
			if (!$obj->getObjectId()) {
				throw new \RuntimeException('Cannot remove unsaved object' . ' from relation.');
			}

			if ($obj->getClassName() !== $this->targetClassName) {
				throw new \RuntimeException('Object type incompatible' . ' with relation.');
			}

			if (isset($this->objects_to_add[$obj->getObjectID()])) {
				unset($this->objects_to_add[$obj->getObjectID()]);
			}

			$this->objects_to_remove[$obj->getObjectId()] = $obj;
		}
	}

	public function applyOn($relation, $object = NULL)
	{
		if (!$relation) {
			return new \LeanCloud\LeanRelation($object, $this->getKey(), $this->getTargetClassName());
		}

		if (!$relation instanceof \LeanCloud\LeanRelation) {
			throw new \RuntimeException('Operation incompatible with ' . 'previous value.');
		}

		return $relation;
	}

	public function mergeWith($prevOp)
	{
		if (!$prevOp) {
			return $this;
		}

		if ($prevOp instanceof RelationOperation) {
			$adds = array_merge($this->objects_to_add, $prevOp->objects_to_add);
			$removes = array_merge($this->objects_to_remove, $prevOp->objects_to_remove);
			return new RelationOperation($this->getKey(), $adds, $removes);
		}

		throw new \RuntimeException('Operation incompatible with ' . 'previous one.');
	}
}

?>
