<?php
// Ä£¿éLTDÌá¹©
class Movie extends \LeanCloud\LeanObject
{
	static protected $className = 'Movie';
}
Movie::registerClass();
class LeanObjectTest extends PHPUnit_Framework_TestCase
{
	static public function setUpBeforeClass()
	{
		\LeanCloud\LeanClient::initialize(getenv('LC_APP_ID'), getenv('LC_APP_KEY'), getenv('LC_APP_MASTER_KEY'));
		\LeanCloud\LeanClient::useRegion(getenv('LC_API_REGION'));
		\LeanCloud\LeanClient::setStorage(new \LeanCloud\Storage\SessionStorage());
	}

	public function testInitializePlainObjectWithoutName()
	{
		$this->setExpectedException('InvalidArgumentException', 'className is invalid.');
		new \LeanCloud\LeanObject();
	}

	public function testInitializeSubClass()
	{
		$movie = new Movie();
		$this->assertTrue($movie instanceof Movie);
		$this->assertTrue($movie instanceof \LeanCloud\LeanObject);
	}

	public function testInitializePlainObject()
	{
		$movie = new \LeanCloud\LeanObject('Movie');
		$this->assertFalse($movie instanceof Movie);
		$this->assertTrue($movie instanceof \LeanCloud\LeanObject);
	}

	public function testSetGet()
	{
		$movie = new Movie();
		$movie->set('title', 'How to train your dragon');
		$movie->set('release', 2010);
		$this->assertEquals($movie->get('title'), 'How to train your dragon');
		$this->assertEquals($movie->get('release'), 2010);
	}

	public function testeSetPreservedField()
	{
		$setOps = array('objectId' => '32a', 'createdAt' => '', 'updatedAt' => '');
		$movie = new Movie();

		foreach ($setOps as $key => $val) {
			try {
				$movie->set($key, $val);
			}
			catch (RuntimeException $exp) {
				continue;
			}

			$this->fail('Set on preserved key ' . $key . ' should throw exception.');
		}
	}

	public function testIncrement()
	{
		$movie = new Movie();
		$movie->set('score', 60);
		$this->assertEquals($movie->get('score'), 60);
		$movie->increment('score', 10);
		$this->assertEquals($movie->get('score'), 70);
		$movie->increment('score', -5);
		$this->assertEquals($movie->get('score'), 65);
	}

	public function testSaveNewObject()
	{
		$obj = new \LeanCloud\LeanObject('TestObject');
		$obj->set('name', 'Alice in wonderland');
		$obj->set('score', 81);
		$obj->save();
		$this->assertNotEmpty($obj->getObjectId());
		$this->assertNotEmpty($obj->getCreatedAt());
		$this->assertFalse($obj->isDirty());
		$this->assertEquals($obj->get('score'), 81);
		$obj->destroy();
	}

	public function testSaveFetchObject()
	{
		$obj = new \LeanCloud\LeanObject('TestObject');
		$obj->set('name', 'Alice in wonderland');
		$obj->set('score', 81);
		$obj->save();
		$this->assertNotEmpty($obj->getObjectId());
		$id = $obj->getObjectId();
		$obj2 = new \LeanCloud\LeanObject('TestObject', $id);
		$obj2->fetch();
		$this->assertEquals($obj2->get('name'), 'Alice in wonderland');
		$this->assertEquals($obj2->get('score'), 81);
		$obj->destroy();
	}

	public function testSaveExistingObject()
	{
		$obj = new \LeanCloud\LeanObject('TestObject');
		$obj->set('foo', 'bar');
		$obj->save();
		$this->assertNotEmpty($obj->getObjectId());
		$this->assertNotEmpty($obj->getCreatedAt());
		$obj->set('name', 'Alice in wonderland');
		$obj->set('score', 81);
		$obj->save();
		$this->assertNotEmpty($obj->getUpdatedAt());
		$obj2 = new \LeanCloud\LeanObject('TestObject', $obj->getObjectId());
		$obj2->fetch();
		$this->assertEquals($obj2->get('name'), 'Alice in wonderland');
		$this->assertEquals($obj2->get('score'), 81);
		$obj->destroy();
	}

	public function testGetCreatedAtAndUpdatedAt()
	{
		$obj = new \LeanCloud\LeanObject('TestObject');
		$obj->set('foo', 'bar');
		$obj->save();
		$this->assertNotEmpty($obj->getCreatedAt());
		$this->assertTrue($obj->getCreatedAt() instanceof DateTime);
		$obj->set('foo', 'baz');
		$obj->save();
		$this->assertNotEmpty($obj->getUpdatedAt());
		$this->assertTrue($obj->getUpdatedAt() instanceof DateTime);
		$obj->destroy();
	}

	public function testGetDateShouldReturnDateTime()
	{
		$obj = new \LeanCloud\LeanObject('TestObject');
		$date = new DateTime();
		$obj->set('release', $date);
		$obj->save();
		$this->assertNotEmpty($obj->getObjectId());
		$obj2 = new \LeanCloud\LeanObject('TestObject', $obj->getObjectId());
		$obj2->fetch();
		$this->assertTrue($obj2->get('release') instanceof DateTime);
		$this->assertEquals($obj->get('release'), $obj2->get('release'));
		$obj2->destroy();
	}

	public function testRelationDecode()
	{
		$a = new \LeanCloud\LeanObject('TestObject');
		$a->set('name', 'Pap');
		$rel = $a->getRelation('likes_relation');
		$b = new \LeanCloud\LeanObject('TestObject');
		$b->set('name', 'alice');
		$b->save();
		$rel->add($b);
		$a->save();
		$this->assertNotEmpty($a->getObjectId());
		$a2 = new \LeanCloud\LeanObject('TestObject', $a->getObjectId());
		$a2->fetch();
		$val = $a2->get('likes_relation');
		$this->assertTrue($val instanceof \LeanCloud\LeanRelation);
		$this->assertEquals('TestObject', $val->getTargetClassName());
		\LeanCloud\LeanObject::destroyAll(array($a, $b));
	}

	public function testAddField()
	{
		$obj = new \LeanCloud\LeanObject('TestObject');
		$obj->addIn('tags', 'frontend');
		$this->assertEquals(array('frontend'), $obj->get('tags'));
		$obj->addIn('tags', 'frontend');
		$this->assertEquals(array('frontend', 'frontend'), $obj->get('tags'));
		$obj->set('tags', array('javascript'));
		$this->assertEquals(array('javascript'), $obj->get('tags'));
		$obj->addIn('tags', 'frontend');
		$this->assertEquals(array('javascript', 'frontend'), $obj->get('tags'));
	}

	public function testAddUniqueOnField()
	{
		$obj = new \LeanCloud\LeanObject('TestObject');
		$obj->addUniqueIn('tags', 'frontend');
		$this->assertEquals(array('frontend'), $obj->get('tags'));
		$obj->addUniqueIn('tags', 'frontend');
		$this->assertEquals(array('frontend'), $obj->get('tags'));
		$obj->addUniqueIn('tags', 'javascript');
		$this->assertEquals(array('frontend', 'javascript'), $obj->get('tags'));
	}

	public function testRemoveOnField()
	{
		$obj = new \LeanCloud\LeanObject('TestObject');
		$obj->removeIn('tags', 'frontend');
		$this->assertEquals(array(), $obj->get('tags'));
		$obj->set('tags', array('frontend', 'javascript'));
		$this->assertEquals(array('frontend', 'javascript'), $obj->get('tags'));
		$obj->removeIn('tags', 'javascript');
		$this->assertEquals(array('frontend'), $obj->get('tags'));
	}

	public function testDeleteField()
	{
		$obj = new \LeanCloud\LeanObject('TestObject');
		$obj->delete('tags');
		$this->assertNull($obj->get('tags'));
		$obj->set('tags', array('frontend', 'javascript'));
		$this->assertEquals(array('frontend', 'javascript'), $obj->get('tags'));
		$obj->delete('tags');
		$this->assertNull($obj->get('tags'));
		$obj->addIn('tags', 'frontend');
		$this->assertEquals(array('frontend'), $obj->get('tags'));
	}

	public function testDestroyObject()
	{
		$obj = new \LeanCloud\LeanObject('TestObject');
		$obj->set('tags', array('frontend'));
		$obj->save();
		$this->assertNotEmpty($obj->getObjectId());
		$obj->destroy();
		$this->setExpectedException('LeanCloud\\CloudException');
		$obj->fetch();
	}

	public function testAddRelation()
	{
		$obj = new \LeanCloud\LeanObject('TestObject');
		$rel = $obj->getRelation('authors');
		$rel->add(new \LeanCloud\LeanObject('TestAuthor', 'abc101'));
		$out = $rel->encode();
		$this->assertEquals('Relation', $out['__type']);
		$this->assertEquals('TestAuthor', $out['className']);
		$val = $obj->get('authors');
		$this->assertTrue($val instanceof \LeanCloud\LeanRelation);
		$out = $val->encode();
		$this->assertEquals('Relation', $out['__type']);
		$this->assertEquals('TestAuthor', $out['className']);
	}

	public function testObjectTraverseACycle()
	{
		$a = new \LeanCloud\LeanObject('TestObject');
		$b = new \LeanCloud\LeanObject('TestObject');
		$c = new \LeanCloud\LeanObject('TestObject');
		$a->set('likes', array($b, 'foo'));
		$b->set('likes', array($c, 42));
		$c->set('likes', $a);
		$objects = array();
		$seen = array();
		\LeanCloud\LeanObject::traverse($a, $seen, function($val) use(&$objects) {
			if ($val instanceof \LeanCloud\LeanObject) {
				$objects[] = $val;
			}
		});
		$this->assertEquals(3, count($seen));
		$this->assertEquals(3, count($objects));
		$objects = array();
		$seen = array();
		\LeanCloud\LeanObject::traverse($c, $seen, function($val) use(&$objects) {
			if ($val instanceof \LeanCloud\LeanObject) {
				$objects[] = $val;
			}
		});
		$this->assertEquals(3, count($seen));
		$this->assertEquals(3, count($objects));
	}

	public function testFindUnsavedChildren()
	{
		$a = new \LeanCloud\LeanObject('TestObject');
		$b = new \LeanCloud\LeanObject('TestObject');
		$c = new \LeanCloud\LeanObject('TestObject');
		$a->set('likes', array($b, 'foo'));
		$b->set('likes', array($c, 42));
		$c->set('likes', $a);
		$unsavedChildren = $b->findUnsavedChildren();
		$this->assertContains($c, $unsavedChildren);
		$this->assertContains($a, $unsavedChildren);
		$this->assertNotContains($b, $unsavedChildren);
	}

	public function testSaveObjectWithNewChildren()
	{
		$a = new \LeanCloud\LeanObject('TestObject');
		$b = new \LeanCloud\LeanObject('TestObject');
		$c = new \LeanCloud\LeanObject('TestObject');
		$a->set('foo', 'aar');
		$b->set('foo', 'bar');
		$c->set('foo', 'car');
		$a->set('mylikes', array($b, 'foo'));
		$a->set('dislikes', array($c, 42));
		$a->save();
		$this->assertNotEmpty($a->getObjectId());
		$this->assertNotEmpty($b->getObjectId());
		$this->assertNotEmpty($c->getObjectId());
		\LeanCloud\LeanObject::destroyAll(array($a, $b, $c));
	}

	public function testSaveWithNewGrandChildren()
	{
		$a = new \LeanCloud\LeanObject('TestObject');
		$b = new \LeanCloud\LeanObject('TestObject');
		$c = new \LeanCloud\LeanObject('TestObject');
		$a->set('foo', 'aar');
		$b->set('foo', 'bar');
		$c->set('foo', 'car');
		$a->set('likes', array($b, 'foo'));
		$b->set('likes', array($c, 42));
		$this->setExpectedException('RuntimeException', 'Object without ID cannot be serialized.');
		$a->save();
	}

	public function testSetGeoPoint()
	{
		$obj = new \LeanCloud\LeanObject('TestObject');
		$obj->set('location', new \LeanCloud\GeoPoint(39.899999999999999, 116.40000000000001));
		$obj->save();
		$obj2 = new \LeanCloud\LeanObject('TestObject', $obj->getObjectId());
		$obj2->fetch();
		$loc = $obj2->get('location');
		$this->assertTrue($loc instanceof \LeanCloud\GeoPoint);
		$this->assertEquals(39.899999999999999, $loc->getLatitude());
		$this->assertEquals(116.40000000000001, $loc->getLongitude());
	}
}

?>
