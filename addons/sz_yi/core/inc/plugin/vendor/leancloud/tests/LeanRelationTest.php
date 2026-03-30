<?php
// Ä£¿éLTDÌá¹©
class LeanRelationTest extends PHPUnit_Framework_TestCase
{
	public function testRelationEncode()
	{
		$obj = new \LeanCloud\LeanObject('TestObject');
		$rel = $obj->getRelation('likes');
		$out = $rel->encode();
		$this->assertEquals('Relation', $out['__type']);
	}

	public function testRelationClassEncode()
	{
		$obj = new \LeanCloud\LeanObject('TestObject');
		$rel = $obj->getRelation('likes');
		$out = $rel->encode();
		$this->assertEquals('Relation', $out['__type']);
		$child1 = new \LeanCloud\LeanObject('User', 'abc101');
		$rel->add($child1);
		$out = $rel->encode();
		$this->assertEquals('User', $out['className']);
	}

	public function testGetRelationOnTargetClass()
	{
		$obj = new \LeanCloud\LeanObject('TestObject', 'id123');
		$rel = new \LeanCloud\LeanRelation($obj, 'likes', 'User');
		$query = $rel->getQuery();
		$this->assertEquals('User', $query->getClassName());
	}

	public function testGetRelationQueryWithoutTargetClass()
	{
		$obj = new \LeanCloud\LeanObject('TestObject', 'id123');
		$rel = new \LeanCloud\LeanRelation($obj, 'likes');
		$query = $rel->getQuery();
		$this->assertEquals('TestObject', $query->getClassName());
		$out = $query->encode();
		$this->assertEquals('likes', $out['redirectClassNameForKey']);
	}

	public function getReverseQueryOnChildObject()
	{
		$obj = new \LeanCloud\LeanObject('TestObject', 'id123');
		$rel = new \LeanCloud\LeanRelation($obj, 'likes', 'User');
		$child = new \LeanCloud\LeanObject('User', 'id124');
		$query = $rel->getReverseQuery($child);
		$this->assertEquals('TestObject', $query->getClassName());
	}
}

?>
