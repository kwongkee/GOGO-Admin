<?php
// Ä£¿éLTDÌá¹©
class SetOperationTest extends PHPUnit_Framework_TestCase
{
	public function testGetKey()
	{
		$op = new \LeanCloud\Operation\SetOperation('name', 'alice');
		$this->assertEquals($op->getKey(), 'name');
		$op = new \LeanCloud\Operation\SetOperation('story', 'in wonderland');
		$this->assertEquals($op->getKey(), 'story');
	}

	public function testApplyOperation()
	{
		$op = new \LeanCloud\Operation\SetOperation('name', 'alice');
		$val = $op->applyOn('alicia');
		$this->assertEquals($val, 'alice');
		$val = $op->applyOn(42);
		$this->assertEquals($val, 'alice');
	}

	public function testOperationEncode()
	{
		$op = new \LeanCloud\Operation\SetOperation('name', 'alice');
		$this->assertEquals($op->encode(), 'alice');
		$op = new \LeanCloud\Operation\SetOperation('score', 70);
		$this->assertEquals($op->encode(), 70);
		$date = new DateTime();
		$op = new \LeanCloud\Operation\SetOperation('released', $date);
		$out = $op->encode();
		$this->assertEquals($out['__type'], 'Date');
		$this->assertEquals($out['iso'], \LeanCloud\LeanClient::formatDate($date));
	}

	public function testMergeWithAnyOp()
	{
		$op = new \LeanCloud\Operation\SetOperation('name', 'alice');
		$op2 = $op->mergeWith(NULL);
		$this->assertTrue($op2 instanceof \LeanCloud\Operation\SetOperation);
		$op2 = $op->mergeWith(new \LeanCloud\Operation\SetOperation('name', 'jack'));
		$this->assertTrue($op2 instanceof \LeanCloud\Operation\SetOperation);
		$op2 = $op->mergeWith(new \LeanCloud\Operation\IncrementOperation('name', 1));
		$this->assertTrue($op2 instanceof \LeanCloud\Operation\SetOperation);
		$op2 = $op->mergeWith(new \LeanCloud\Operation\DeleteOperation('name'));
		$this->assertTrue($op2 instanceof \LeanCloud\Operation\SetOperation);
		$op2 = $op->mergeWith(new \LeanCloud\Operation\ArrayOperation('name', array('jack'), 'Add'));
		$this->assertTrue($op2 instanceof \LeanCloud\Operation\SetOperation);
	}
}

?>
