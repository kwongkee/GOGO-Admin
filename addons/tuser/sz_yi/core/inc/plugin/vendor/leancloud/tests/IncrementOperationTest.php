<?php
// Ä£¿éLTDÌá¹©
class IncrementOperationTest extends PHPUnit_Framework_TestCase
{
	public function testGetKey()
	{
		$op = new \LeanCloud\Operation\IncrementOperation('score', 1);
		$this->assertEquals($op->getKey(), 'score');
	}

	public function testApplyOperation()
	{
		$op = new \LeanCloud\Operation\IncrementOperation('score', 20);
		$val = $op->applyOn(2);
		$this->assertEquals($val, 22);
		$val = $op->applyOn(22);
		$this->assertEquals($val, 42);
		$val = $op->applyOn('7');
		$this->assertEquals($val, 27);
	}

	public function testIncrementOnString()
	{
		$op = new \LeanCloud\Operation\IncrementOperation('score', 1);
		$this->setExpectedException('RuntimeException', 'Operation incompatible with previous value.');
		$op->applyOn('alice');
	}

	public function testIncrementNonNumericAmount()
	{
		$this->setExpectedException('InvalidArgumentException', 'Operand must be number.');
		$op = new \LeanCloud\Operation\IncrementOperation('score', 'a');
	}

	public function testOperationEncode()
	{
		$op = new \LeanCloud\Operation\IncrementOperation('score', 2);
		$out = $op->encode();
		$this->assertEquals($out['__op'], 'Increment');
		$this->assertEquals($out['amount'], 2);
		$op = new \LeanCloud\Operation\IncrementOperation('score', -2);
		$out = $op->encode();
		$this->assertEquals($out['__op'], 'Increment');
		$this->assertEquals($out['amount'], -2);
	}

	public function testMergeWithNull()
	{
		$op = new \LeanCloud\Operation\IncrementOperation('score', 2);
		$op2 = $op->mergeWith(NULL);
		$out = $op2->encode();
		$this->assertEquals($out['__op'], 'Increment');
		$this->assertEquals($out['amount'], 2);
	}

	public function testMergeWithSetOperation()
	{
		$op = new \LeanCloud\Operation\IncrementOperation('score', 2);
		$op2 = $op->mergeWith(new \LeanCloud\Operation\SetOperation('score', 40));
		$this->assertTrue($op2 instanceof \LeanCloud\Operation\SetOperation);
		$this->assertEquals($op2->getValue(), 42);
	}

	public function testMergeWithIncrementOperation()
	{
		$op = new \LeanCloud\Operation\IncrementOperation('score', 2);
		$op2 = $op->mergeWith(new \LeanCloud\Operation\IncrementOperation('score', 3));
		$this->assertTrue($op2 instanceof \LeanCloud\Operation\IncrementOperation);
		$this->assertEquals($op2->getValue(), 5);
	}

	public function testMergeWithDelete()
	{
		$op = new \LeanCloud\Operation\IncrementOperation('score', 2);
		$op2 = $op->mergeWith(new \LeanCloud\Operation\DeleteOperation('score'));
		$this->assertTrue($op2 instanceof \LeanCloud\Operation\SetOperation);
		$this->assertEquals($op2->getValue(), 2);
	}
}

?>
