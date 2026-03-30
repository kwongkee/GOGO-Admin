<?php
// Ä£¿éLTDÌá¹©
class ArrayOperationTest extends PHPUnit_Framework_TestCase
{
	public function testInvalidOp()
	{
		$this->setExpectedException('InvalidArgumentException', 'Operation on array not supported: Set.');
		new \LeanCloud\Operation\ArrayOperation('tags', array('frontend', 'javascript'), 'Set');
	}

	public function testInvalidArray()
	{
		$this->setExpectedException('InvalidArgumentException', 'Operand must be array.');
		new \LeanCloud\Operation\ArrayOperation('tags', 'frontend', 'Add');
	}

	public function testOperationEncode()
	{
		$op = new \LeanCloud\Operation\ArrayOperation('tags', array('frontend', 'javascript'), 'Add');
		$out = $op->encode();
		$this->assertEquals('Add', $out['__op']);
		$this->assertEquals(array('frontend', 'javascript'), $out['objects']);
		$op = new \LeanCloud\Operation\ArrayOperation('tags', array('frontend', 'javascript'), 'AddUnique');
		$out = $op->encode();
		$this->assertEquals('AddUnique', $out['__op']);
		$this->assertEquals(array('frontend', 'javascript'), $out['objects']);
		$op = new \LeanCloud\Operation\ArrayOperation('tags', array('frontend', 'javascript'), 'Remove');
		$out = $op->encode();
		$this->assertEquals('Remove', $out['__op']);
		$this->assertEquals(array('frontend', 'javascript'), $out['objects']);
	}

	public function testApplyAddToNonArray()
	{
		$op = new \LeanCloud\Operation\ArrayOperation('tags', array('frontend', 'javascript'), 'Add');
		$this->setExpectedException('RuntimeException', 'Operation incompatible with ' . 'previous value.');
		$op->applyOn(42);
	}

	public function testApplyAddToNull()
	{
		$op = new \LeanCloud\Operation\ArrayOperation('tags', array('frontend', 'javascript'), 'Add');
		$val = $op->applyOn(NULL);
		$this->assertEquals(array('frontend', 'javascript'), $val);
	}

	public function testApplyAddToArray()
	{
		$op = new \LeanCloud\Operation\ArrayOperation('tags', array('frontend', 'javascript'), 'Add');
		$val = $op->applyOn(array('css'));
		$this->assertEquals(array('css', 'frontend', 'javascript'), $val);
		$val = $op->applyOn(array());
		$this->assertEquals(array('frontend', 'javascript'), $val);
	}

	public function testApplyAddUniqueToNonArray()
	{
		$op = new \LeanCloud\Operation\ArrayOperation('tags', array('frontend', 'javascript'), 'AddUnique');
		$this->setExpectedException('RuntimeException', 'Operation incompatible with ' . 'previous value.');
		$op->applyOn(42);
	}

	public function testApplyAddUniqueToNull()
	{
		$op = new \LeanCloud\Operation\ArrayOperation('tags', array(
	'foo',
	'foo',
	42,
	42,
	1.1000000000000001,
	1.1000000000000001,
	array('a', 'b'),
	array('a', 'b')
	), 'AddUnique');
		$val = $op->applyOn(NULL);
		$this->assertEquals(array(
	'foo',
	42,
	1.1000000000000001,
	array('a', 'b')
	), $val);
	}

	public function testApplyAddUniqueToArray()
	{
		$op = new \LeanCloud\Operation\ArrayOperation('tags', array(
	'foo',
	'foo',
	42,
	42,
	1.1000000000000001,
	1.1000000000000001,
	array('a', 'b'),
	array('a', 'b')
	), 'AddUnique');
		$val = $op->applyOn(array());
		$this->assertEquals(array(
	'foo',
	42,
	1.1000000000000001,
	array('a', 'b')
	), $val);
		$op = new \LeanCloud\Operation\ArrayOperation('tags', array(
	'foo',
	42,
	1.1000000000000001,
	array('a', 'b')
	), 'AddUnique');
		$val = $op->applyOn(array(
	'foo',
	42,
	1.1000000000000001,
	array('a', 'b')
	));
		$this->assertEquals(array(
	'foo',
	42,
	1.1000000000000001,
	array('a', 'b')
	), $val);
	}

	public function testApplyRemoveToNonArray()
	{
		$op = new \LeanCloud\Operation\ArrayOperation('tags', array('frontend', 'javascript'), 'Remove');
		$this->setExpectedException('RuntimeException', 'Operation incompatible with ' . 'previous value.');
		$op->applyOn(1.1000000000000001);
	}

	public function testApplyRemoveToNull()
	{
		$op = new \LeanCloud\Operation\ArrayOperation('tags', array('frontend', 'javascript'), 'Remove');
		$val = $op->applyOn(NULL);
		$this->assertEmpty($val);
	}

	public function testApplyRemoveToArray()
	{
		$op = new \LeanCloud\Operation\ArrayOperation('tags', array('frontend', 'javascript'), 'Remove');
		$val = $op->applyOn(array());
		$this->assertEmpty($val);
		$val = $op->applyOn(array('frontend', 'foo', 'bar'));
		$this->assertEquals(array('foo', 'bar'), $val);
		$op = new \LeanCloud\Operation\ArrayOperation('tags', array(
	42,
	1.1000000000000001,
	array('a', 'b')
	), 'Remove');
		$val = $op->applyOn(array(
	'frontend',
	42,
	1.1000000000000001,
	array('a', 'b')
	));
		$this->assertEquals(array('frontend'), $val);
	}

	public function testMergeToIncompatibleSetOperation()
	{
		$op = new \LeanCloud\Operation\ArrayOperation('tags', array('frontend', 'javascript'), 'Add');
		$this->setExpectedException('RuntimeException', 'Operation incompatible ' . 'with previous value.');
		$op2 = $op->mergeWith(new \LeanCloud\Operation\SetOperation('tags', 42));
	}

	public function testMergeWithNull()
	{
		$op = new \LeanCloud\Operation\ArrayOperation('tags', array('frontend', 'javascript'), 'Add');
		$op2 = $op->mergeWith(NULL);
		$this->assertTrue($op2 instanceof \LeanCloud\Operation\ArrayOperation);
		$out = $op2->encode();
		$this->assertEquals('Add', $out['__op']);
		$this->assertEquals(array('frontend', 'javascript'), $out['objects']);
		$op = new \LeanCloud\Operation\ArrayOperation('tags', array('frontend', 'javascript'), 'AddUnique');
		$op2 = $op->mergeWith(NULL);
		$out = $op2->encode();
		$this->assertTrue($op2 instanceof \LeanCloud\Operation\ArrayOperation);
		$this->assertEquals('AddUnique', $out['__op']);
		$this->assertEquals(array('frontend', 'javascript'), $out['objects']);
		$op = new \LeanCloud\Operation\ArrayOperation('tags', array('frontend', 'javascript'), 'Remove');
		$op2 = $op->mergeWith(NULL);
		$out = $op2->encode();
		$this->assertTrue($op2 instanceof \LeanCloud\Operation\ArrayOperation);
		$this->assertEquals('Remove', $out['__op']);
		$this->assertEquals(array('frontend', 'javascript'), $out['objects']);
	}

	public function testMergeToSetOperation()
	{
		$op = new \LeanCloud\Operation\ArrayOperation('tags', array('frontend', 'javascript'), 'Add');
		$op2 = $op->mergeWith(new \LeanCloud\Operation\SetOperation('tags', array('css')));
		$this->assertTrue($op2 instanceof \LeanCloud\Operation\SetOperation);
		$out = $op2->encode();
		$this->assertEquals(array('css', 'frontend', 'javascript'), $out);
		$op = new \LeanCloud\Operation\ArrayOperation('tags', array('css', 'frontend', 'frontend'), 'AddUnique');
		$op2 = $op->mergeWith(new \LeanCloud\Operation\SetOperation('tags', array('css')));
		$this->assertTrue($op2 instanceof \LeanCloud\Operation\SetOperation);
		$out = $op2->encode();
		$this->assertEquals(array('css', 'frontend'), $out);
		$op = new \LeanCloud\Operation\ArrayOperation('tags', array('frontend', 'javascript'), 'Remove');
		$op2 = $op->mergeWith(new \LeanCloud\Operation\SetOperation('tags', array('css', 'frontend', 'javascript')));
		$this->assertTrue($op2 instanceof \LeanCloud\Operation\SetOperation);
		$out = $op2->encode();
		$this->assertEquals(array('css'), $out);
	}

	public function testMergeAddToAdd()
	{
		$op = new \LeanCloud\Operation\ArrayOperation('tags', array('frontend', 'javascript'), 'Add');
		$op2 = $op->mergeWith(new \LeanCloud\Operation\ArrayOperation('tags', array('foo'), 'Add'));
		$this->assertTrue($op2 instanceof \LeanCloud\Operation\ArrayOperation);
		$out = $op2->encode();
		$this->assertEquals('Add', $out['__op']);
		$this->assertEquals(array('foo', 'frontend', 'javascript'), $out['objects']);
	}

	public function testMergeAddUniqueToAddUnique()
	{
		$op = new \LeanCloud\Operation\ArrayOperation('tags', array('frontend', 'javascript'), 'AddUnique');
		$op2 = $op->mergeWith(new \LeanCloud\Operation\ArrayOperation('tags', array('foo', 'frontend'), 'AddUnique'));
		$this->assertTrue($op2 instanceof \LeanCloud\Operation\ArrayOperation);
		$out = $op2->encode();
		$this->assertEquals('AddUnique', $out['__op']);
		$this->assertEquals(array('foo', 'frontend', 'javascript'), $out['objects']);
	}

	public function testMergeRemoveToRemove()
	{
		$op = new \LeanCloud\Operation\ArrayOperation('tags', array('frontend', 'javascript'), 'Remove');
		$op2 = $op->mergeWith(new \LeanCloud\Operation\ArrayOperation('tags', array('foo'), 'Remove'));
		$this->assertTrue($op2 instanceof \LeanCloud\Operation\ArrayOperation);
		$out = $op2->encode();
		$this->assertEquals('Remove', $out['__op']);
		$this->assertEquals(array('foo', 'frontend', 'javascript'), $out['objects']);
	}

	public function testMergeAddWithDelete()
	{
		$op = new \LeanCloud\Operation\ArrayOperation('tags', array('frontend', 'javascript'), 'Add');
		$op2 = $op->mergeWith(new \LeanCloud\Operation\DeleteOperation('tags'));
		$this->assertTrue($op2 instanceof \LeanCloud\Operation\SetOperation);
		$out = $op2->encode();
		$this->assertEquals(array('frontend', 'javascript'), $out);
	}

	public function testMergeAddUniqueWithDelete()
	{
		$op = new \LeanCloud\Operation\ArrayOperation('tags', array('frontend', 'frontend', 'javascript'), 'AddUnique');
		$op2 = $op->mergeWith(new \LeanCloud\Operation\DeleteOperation('tags'));
		$this->assertTrue($op2 instanceof \LeanCloud\Operation\SetOperation);
		$out = $op2->encode();
		$this->assertEquals(array('frontend', 'javascript'), $out);
	}

	public function testMergeRemoveWithDelete()
	{
		$op = new \LeanCloud\Operation\ArrayOperation('tags', array('frontend', 'javascript'), 'Remove');
		$op2 = $op->mergeWith(new \LeanCloud\Operation\DeleteOperation('tags'));
		$this->assertTrue($op2 instanceof \LeanCloud\Operation\DeleteOperation);
	}
}

?>
