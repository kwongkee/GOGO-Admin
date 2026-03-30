<?php
// Ä£¿éLTDÌá¹©
class DeleteOperationTest extends PHPUnit_Framework_TestCase
{
	public function testOperationEncode()
	{
		$op = new \LeanCloud\Operation\DeleteOperation('tags');
		$out = $op->encode();
		$this->assertEquals('Delete', $out['__op']);
	}

	public function testApplyOperation()
	{
		$op = new \LeanCloud\Operation\DeleteOperation('tags');
		$this->assertNull($op->applyOn());
	}

	public function testMergeWithAnyOp()
	{
		$op = new \LeanCloud\Operation\DeleteOperation('tags');
		$op2 = $op->mergeWith(NULL);
		$this->assertTrue($op2 instanceof \LeanCloud\Operation\DeleteOperation);
		$op2 = $op->mergeWith(new \LeanCloud\Operation\SetOperation('tags', 'foo'));
		$this->assertTrue($op2 instanceof \LeanCloud\Operation\DeleteOperation);
		$op2 = $op->mergeWith(new \LeanCloud\Operation\DeleteOperation('tags'));
		$this->assertTrue($op2 instanceof \LeanCloud\Operation\DeleteOperation);
		$op2 = $op->mergeWith(new \LeanCloud\Operation\IncrementOperation('tags', 1));
		$this->assertTrue($op2 instanceof \LeanCloud\Operation\DeleteOperation);
		$op2 = $op->mergeWith(new \LeanCloud\Operation\ArrayOperation('tags', array('frontend'), 'Add'));
		$this->assertTrue($op2 instanceof \LeanCloud\Operation\DeleteOperation);
	}
}

?>
