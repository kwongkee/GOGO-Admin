<?php
// Ä£¿éLTDÌá¹©
class RelationOperationTest extends PHPUnit_Framework_TestCase
{
	static public function setUpBeforeClass()
	{
		\LeanCloud\LeanClient::initialize(getenv('LC_APP_ID'), getenv('LC_APP_KEY'), getenv('LC_APP_MASTER_KEY'));
		\LeanCloud\LeanClient::useRegion(getenv('LC_API_REGION'));
	}

	public function testBothEmpty()
	{
		$this->setExpectedException('InvalidArgumentException', 'Operands are empty.');
		$op = new \LeanCloud\Operation\RelationOperation('foo', array(), NULL);
	}

	public function testAddOpEncode()
	{
		$child1 = new \LeanCloud\LeanObject('TestObject', 'ab123');
		$op = new \LeanCloud\Operation\RelationOperation('foo', array($child1), NULL);
		$out = $op->encode();
		$this->assertEquals('AddRelation', $out['__op']);
		$this->assertEquals($child1->getPointer(), $out['objects'][0]);
	}

	public function testAddUnsavedObjects()
	{
		$child1 = new \LeanCloud\LeanObject('TestObject');
		$this->setExpectedException('RuntimeException', 'Cannot add unsaved object to relation.');
		$op = new \LeanCloud\Operation\RelationOperation('foo', array($child1), NULL);
	}

	public function testAddDuplicateObjects()
	{
		$child1 = new \LeanCloud\LeanObject('TestObject', 'ab123');
		$op = new \LeanCloud\Operation\RelationOperation('foo', array($child1, $child1), NULL);
		$out = $op->encode();
		$this->assertEquals('AddRelation', $out['__op']);
		$this->assertEquals(1, count($out['objects']));
		$this->assertEquals($child1->getPointer(), $out['objects'][0]);
	}

	public function testRemoveOpEncode()
	{
		$child1 = new \LeanCloud\LeanObject('TestObject', 'ab123');
		$op = new \LeanCloud\Operation\RelationOperation('foo', NULL, array($child1));
		$out = $op->encode();
		$this->assertEquals('RemoveRelation', $out['__op']);
		$this->assertEquals($child1->getPointer(), $out['objects'][0]);
	}

	public function testRemoveDuplicateObjects()
	{
		$child1 = new \LeanCloud\LeanObject('TestObject', 'ab123');
		$op = new \LeanCloud\Operation\RelationOperation('foo', NULL, array($child1, $child1));
		$out = $op->encode();
		$this->assertEquals('RemoveRelation', $out['__op']);
		$this->assertEquals(1, count($out['objects']));
		$this->assertEquals($child1->getPointer(), $out['objects'][0]);
	}

	public function testAddWinsOverRemove()
	{
		$child1 = new \LeanCloud\LeanObject('TestObject', 'ab101');
		$op = new \LeanCloud\Operation\RelationOperation('foo', array($child1), array($child1));
		$out = $op->encode();
		$this->assertEquals('AddRelation', $out['__op']);
		$this->assertEquals(1, count($out['objects']));
		$this->assertEquals($child1->getPointer(), $out['objects'][0]);
	}

	public function testAddAndRemove()
	{
		$child1 = new \LeanCloud\LeanObject('TestObject', 'ab101');
		$child2 = new \LeanCloud\LeanObject('TestObject', 'ab102');
		$child3 = new \LeanCloud\LeanObject('TestObject', 'ab103');
		$op = new \LeanCloud\Operation\RelationOperation('foo', array($child1, $child2), array($child2, $child3));
		$out = $op->encode();
		$this->assertEquals('Batch', $out['__op']);
		$adds = $out['ops'][0];
		$this->assertEquals('AddRelation', $adds['__op']);
		$this->assertEquals(array($child1->getPointer(), $child2->getPointer()), $adds['objects']);
		$removes = $out['ops'][1];
		$this->assertEquals('RemoveRelation', $removes['__op']);
		$this->assertEquals(array($child3->getPointer()), $removes['objects']);
	}

	public function testMultipleClassesNotAllowed()
	{
		$child1 = new \LeanCloud\LeanObject('TestObject', 'abc101');
		$child2 = new \LeanCloud\LeanObject('Test2Object', 'bac102');
		$this->setExpectedException('RuntimeException', 'Object type incompatible with ' . 'relation.');
		$op = new \LeanCloud\Operation\RelationOperation('foo', array($child1), array($child2));
	}

	public function testApplyOperation()
	{
		$child1 = new \LeanCloud\LeanObject('TestObject', 'abc101');
		$op = new \LeanCloud\Operation\RelationOperation('foo', array($child1), NULL);
		$parent = new \LeanCloud\LeanObject('Test2Object');
		$val = $op->applyOn(NULL, $parent);
		$this->assertTrue($val instanceof \LeanCloud\LeanRelation);
		$out = $val->encode();
		$this->assertEquals('TestObject', $out['className']);
	}

	public function testMergeWithNull()
	{
		$child1 = new \LeanCloud\LeanObject('TestObject', 'abc101');
		$op = new \LeanCloud\Operation\RelationOperation('foo', array($child1), NULL);
		$op2 = $op->mergeWith(NULL);
		$this->assertTrue($op2 instanceof \LeanCloud\Operation\RelationOperation);
		$this->assertEquals($op->encode(), $op2->encode());
	}

	public function testMergeWithRelationOperation()
	{
		$child1 = new \LeanCloud\LeanObject('TestObject', 'abc101');
		$op = new \LeanCloud\Operation\RelationOperation('foo', array($child1), NULL);
		$child2 = new \LeanCloud\LeanObject('TestObject', 'abc102');
		$op2 = new \LeanCloud\Operation\RelationOperation('foo', NULL, array($child2));
		$op3 = $op->mergeWith($op2);
		$this->assertTrue($op3 instanceof \LeanCloud\Operation\RelationOperation);
		$out = $op3->encode();
		$this->assertEquals('Batch', $out['__op']);
		$this->assertEquals(array($child1->getPointer()), $out['ops'][0]['objects']);
		$this->assertEquals(array($child2->getPointer()), $out['ops'][1]['objects']);
	}
}

?>
