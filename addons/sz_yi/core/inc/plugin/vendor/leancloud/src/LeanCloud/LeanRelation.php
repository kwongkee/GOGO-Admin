<?php
// Ä£¿éLTDÌá¹©
namespace LeanCloud;

class LeanRelation
{
	private $parent;
	private $key;
	private $targetClassName;

	public function __construct($parent, $key, $className = NULL)
	{
		$this->parent = $parent;
		$this->key = $key;
		$this->targetClassName = $className;
	}

	public function encode()
	{
		return array('__type' => 'Relation', 'className' => $this->targetClassName);
	}

	public function setParentAndKey($parent, $key)
	{
		if ($this->parent && ($this->parent != $parent)) {
			throw new \RuntimeException('Relation does not belong to the object');
		}

		if ($this->key && ($this->key != $key)) {
			throw new \RuntimeException('Relation does not belong to the field');
		}

		$this->parent = $parent;
		$this->key = $key;
	}

	public function getTargetClassName()
	{
		return $this->targetClassName;
	}

	public function add($objects)
	{
		if (!is_array($objects)) {
			$objects = array($objects);
		}

		$op = new Operation\RelationOperation($this->key, $objects, null);
		$this->parent->set($this->key, $op);

		if (!$this->targetClassName) {
			$this->targetClassName = $op->getTargetClassName();
		}
	}

	public function remove($objects)
	{
		if (!is_array($objects)) {
			$objects = array($objects);
		}

		$op = new Operation\RelationOperation($this->key, null, $objects);
		$this->parent->set($this->key, $op);

		if (!$this->targetClassName) {
			$this->targetClassName = $op->getTargetClassName();
		}
	}

	public function getQuery()
	{
		if ($this->targetClassName) {
			$query = new LeanQuery($this->targetClassName);
		}
		else {
			$query = new LeanQuery($this->parent->getClassName());
			$query->addOption('redirectClassNameForKey', $this->key);
		}

		$query->relatedTo($this->key, $this->parent);
		return $query;
	}

	public function getReverseQuery(LeanObject $child)
	{
		$query = new LeanQuery($this->parent->getClassName());
		$query->equalTo($this->key, $child->getPointer());
		return $query;
	}
}


?>
