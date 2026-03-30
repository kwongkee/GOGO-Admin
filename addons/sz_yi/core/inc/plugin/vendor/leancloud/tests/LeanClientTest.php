<?php
// Ä£¿éLTDÌá¹©
class LeanClientTest extends PHPUnit_Framework_TestCase
{
	public function setUp()
	{
		\LeanCloud\LeanClient::initialize(getenv('LC_APP_ID'), getenv('LC_APP_KEY'), getenv('LC_APP_MASTER_KEY'));
		\LeanCloud\LeanClient::useRegion(getenv('LC_API_REGION'));
		\LeanCloud\LeanClient::useMasterKey(false);
	}

	public function testGetAPIEndpoint()
	{
		\LeanCloud\LeanClient::useRegion('CN');
		$this->assertEquals(\LeanCloud\LeanClient::getAPIEndpoint(), 'https://api.leancloud.cn/1.1');
	}

	public function testUseInvalidRegion()
	{
		$this->setExpectedException('RuntimeException', 'Invalid API region');
		\LeanCloud\LeanClient::useRegion('cn-bla');
	}

	public function testUseRegion()
	{
		\LeanCloud\LeanClient::useRegion('US');
		$this->assertEquals(\LeanCloud\LeanClient::getAPIEndpoint(), 'https://us-api.leancloud.cn/1.1');
	}

	public function testUseMasterKeyByDefault()
	{
		\LeanCloud\LeanClient::useMasterKey(true);
		$headers = \LeanCloud\LeanClient::buildHeaders('token', NULL);
		$this->assertContains('master', $headers['X-LC-Sign']);
		$headers = \LeanCloud\LeanClient::buildHeaders('token', true);
		$this->assertContains('master', $headers['X-LC-Sign']);
		$headers = \LeanCloud\LeanClient::buildHeaders('token', false);
		$this->assertNotContains('master', $headers['X-LC-Sign']);
	}

	public function testNotUseMasterKeyByDefault()
	{
		\LeanCloud\LeanClient::useMasterKey(false);
		$headers = \LeanCloud\LeanClient::buildHeaders('token', NULL);
		$this->assertNotContains('master', $headers['X-LC-Sign']);
		$headers = \LeanCloud\LeanClient::buildHeaders('token', false);
		$this->assertNotContains('master', $headers['X-LC-Sign']);
		$headers = \LeanCloud\LeanClient::buildHeaders('token', true);
		$this->assertContains('master', $headers['X-LC-Sign']);
	}

	public function testRequestServerDate()
	{
		$data = \LeanCloud\LeanClient::request('GET', '/date', NULL);
		$this->assertEquals($data['__type'], 'Date');
	}

	public function testRequestUnauthorized()
	{
		\LeanCloud\LeanClient::initialize(getenv('LC_APP_ID'), 'invalid key', 'invalid master key');
		$this->setExpectedException('LeanCloud\\CloudException', 'Unauthorized');
		$data = \LeanCloud\LeanClient::request('POST', '/classes/TestObject', array('name' => 'alice', 'story' => 'in wonderland'));
		\LeanCloud\LeanClient::delete('/classes/TestObject/' . $data['objectId']);
	}

	public function testRequestTestObject()
	{
		$data = \LeanCloud\LeanClient::request('POST', '/classes/TestObject', array('name' => 'alice', 'story' => 'in wonderland'));
		$this->assertArrayHasKey('objectId', $data);
		$id = $data['objectId'];
		$data = \LeanCloud\LeanClient::request('GET', '/classes/TestObject/' . $id, NULL);
		$this->assertEquals($data['name'], 'alice');
		\LeanCloud\LeanClient::delete('/classes/TestObject/' . $data['objectId']);
	}

	public function testPostCreateTestObject()
	{
		$data = \LeanCloud\LeanClient::post('/classes/TestObject', array('name' => 'alice', 'story' => 'in wonderland'));
		$this->assertArrayHasKey('objectId', $data);
		\LeanCloud\LeanClient::delete('/classes/TestObject/' . $data['objectId']);
	}

	public function testGetTestObject()
	{
		$data = \LeanCloud\LeanClient::post('/classes/TestObject', array('name' => 'alice', 'story' => 'in wonderland'));
		$this->assertArrayHasKey('objectId', $data);
		$obj = \LeanCloud\LeanClient::get('/classes/TestObject/' . $data['objectId']);
		$this->assertEquals($obj['name'], 'alice');
		$this->assertEquals($obj['story'], 'in wonderland');
		\LeanCloud\LeanClient::delete('/classes/TestObject/' . $obj['objectId']);
	}

	public function testUpdateTestObject()
	{
		$data = \LeanCloud\LeanClient::post('/classes/TestObject', array('name' => 'alice', 'story' => 'in wonderland'));
		$this->assertArrayHasKey('objectId', $data);
		\LeanCloud\LeanClient::put('/classes/TestObject/' . $data['objectId'], array('name' => 'Hiccup', 'story' => 'How to train your dragon'));
		$obj = \LeanCloud\LeanClient::get('/classes/TestObject/' . $data['objectId']);
		$this->assertEquals($obj['name'], 'Hiccup');
		$this->assertEquals($obj['story'], 'How to train your dragon');
		\LeanCloud\LeanClient::delete('/classes/TestObject/' . $obj['objectId']);
	}

	public function testDeleteTestObject()
	{
		$data = \LeanCloud\LeanClient::post('/classes/TestObject', array('name' => 'alice', 'story' => 'in wonderland'));
		$this->assertArrayHasKey('objectId', $data);
		\LeanCloud\LeanClient::delete('/classes/TestObject/' . $data['objectId']);
		$obj = \LeanCloud\LeanClient::get('/classes/TestObject/' . $data['objectId']);
		$this->assertEmpty($obj);
	}

	public function testDecodeDate()
	{
		$date = new DateTime();
		$type = array('__type' => 'Date', 'iso' => \LeanCloud\LeanClient::formatDate($date));
		$this->assertEquals($date, \LeanCloud\LeanClient::decode($type, NULL));
	}

	public function testDecodeDateWithTimeZone()
	{
		$zones = array('Asia/Shanghai', 'America/Los_Angeles', 'Asia/Tokyo', 'Europe/London');

		foreach ($zones as $zone) {
			$date = new DateTime('now', new DateTimeZone($zone));
			$type = array('__type' => 'Date', 'iso' => \LeanCloud\LeanClient::formatDate($date));
			$this->assertEquals($date, \LeanCloud\LeanClient::decode($type, NULL));
		}
	}

	public function testDecodeRelation()
	{
		$type = array('__type' => 'Relation', 'className' => 'TestObject');
		$val = \LeanCloud\LeanClient::decode($type, NULL);
		$this->assertTrue($val instanceof \LeanCloud\LeanRelation);
		$this->assertEquals('TestObject', $val->getTargetClassName());
	}

	public function testDecodePointer()
	{
		$type = array('__type' => 'Pointer', 'className' => 'TestObject', 'objectId' => 'abc101');
		$val = \LeanCloud\LeanClient::decode($type, NULL);
		$this->assertTrue($val instanceof \LeanCloud\LeanObject);
		$this->assertEquals('TestObject', $val->getClassName());
	}

	public function testDecodeObject()
	{
		$type = array(
			'__type'    => 'Object',
			'className' => 'TestObject',
			'objectId'  => 'abc101',
			'name'      => 'alice',
			'tags'      => array('fiction', 'bar')
			);
		$val = \LeanCloud\LeanClient::decode($type, NULL);
		$this->assertTrue($val instanceof \LeanCloud\LeanObject);
		$this->assertEquals('TestObject', $val->getClassName());
		$this->assertEquals($type['name'], $val->get('name'));
		$this->assertEquals($type['tags'], $val->get('tags'));
	}

	public function testDecodeBytes()
	{
		$type = array('__type' => 'Bytes', 'base64' => base64_encode('Hello'));
		$val = \LeanCloud\LeanClient::decode($type, NULL);
		$this->assertTrue($val instanceof \LeanCloud\LeanBytes);
		$this->assertEquals(array(72, 101, 108, 108, 111), $val->getByteArray());
	}

	public function testDecodeUserObject()
	{
		$type = array('__type' => 'Object', 'className' => '_User', 'objectId' => 'abc101', 'username' => 'alice', 'email' => 'alice@example.com');
		$val = \LeanCloud\LeanClient::decode($type, NULL);
		$this->assertTrue($val instanceof \LeanCloud\LeanUser);
		$this->assertEquals($type['objectId'], $val->getObjectId());
		$this->assertEquals($type['username'], $val->getUsername());
		$this->assertEquals($type['email'], $val->getEmail());
	}

	public function testDecodeUserPointer()
	{
		$type = array('__type' => 'Pointer', 'className' => '_User', 'objectId' => 'abc101');
		$val = \LeanCloud\LeanClient::decode($type, NULL);
		$this->assertTrue($val instanceof \LeanCloud\LeanUser);
		$this->assertEquals($type['objectId'], $val->getObjectId());
	}

	public function testDecodeFile()
	{
		$type = array('__type' => 'File', 'objectId' => 'abc101', 'name' => 'favicon.ico', 'url' => 'https://leancloud.cn/favicon.ico');
		$val = \LeanCloud\LeanClient::decode($type, NULL);
		$this->assertTrue($val instanceof \LeanCloud\LeanFile);
		$this->assertEquals($type['objectId'], $val->getObjectId());
		$this->assertEquals($type['name'], $val->getName());
		$this->assertEquals($type['url'], $val->getUrl());
	}

	public function testDecodeACL()
	{
		$type = array(
			'*'          => array('read' => true, 'write' => false),
			'user123'    => array('write' => true),
			'role:admin' => array('write' => true)
			);
		$val = \LeanCloud\LeanClient::decode($type, 'ACL');
		$this->assertTrue($val instanceof \LeanCloud\LeanACL);
		$this->assertTrue($val->getPublicReadAccess());
		$this->assertFalse($val->getPublicWriteAccess());
		$this->assertTrue($val->getRoleWriteAccess('admin'));
		$this->assertTrue($val->getWriteAccess('user123'));
	}

	public function testDecodeRecursiveObjectWithACL()
	{
		$acl = array(
			'id102' => array('write' => true)
			);
		$type = array(
			'__type'    => 'Object',
			'className' => 'TestObject',
			'objectId'  => 'id101',
			'name'      => 'alice',
			'ACL'       => $acl,
			'parent'    => array('__type' => 'Object', 'className' => 'TestObject', 'objectId' => 'id102', 'name' => 'jill', 'ACL' => $acl)
			);
		$val = \LeanCloud\LeanClient::decode($type, NULL);
		$this->assertTrue($val instanceof \LeanCloud\LeanObject);
		$this->assertEquals('alice', $val->get('name'));
		$this->assertTrue($val->getACL() instanceof \LeanCloud\LeanACL);
		$parent = $val->get('parent');
		$this->assertTrue($parent instanceof \LeanCloud\LeanObject);
		$this->assertEquals('jill', $parent->get('name'));
		$this->assertTrue($parent->getACL() instanceof \LeanCloud\LeanACL);
	}

	public function testDecodeGeoPoint()
	{
		$type = array('__type' => 'GeoPoint', 'latitude' => 39.899999999999999, 'longitude' => 116.40000000000001);
		$val = \LeanCloud\LeanClient::decode($type, NULL);
		$this->assertTrue($val instanceof \LeanCloud\GeoPoint);
		$this->assertEquals(39.899999999999999, $val->getLatitude());
		$this->assertEquals(116.40000000000001, $val->getLongitude());
	}
}

?>
