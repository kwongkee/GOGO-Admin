<?php
// Ä£¿éLTDÌá¹©
class LeanAPITest extends PHPUnit_Framework_TestCase
{
	public function setUp()
	{
		\LeanCloud\LeanClient::initialize(getenv('LC_APP_ID'), getenv('LC_APP_KEY'), getenv('LC_APP_MASTER_KEY'));
		\LeanCloud\LeanClient::useRegion(getenv('LC_API_REGION'));
	}

	public function testIncrementOnStringField()
	{
		$obj = array('name' => 'alice');
		$resp = \LeanCloud\LeanClient::post('/classes/TestObject', $obj);
		$this->setExpectedException('LeanCloud\\CloudException', '111 Invalid value type for field', 111);
		$resp2 = \LeanCloud\LeanClient::put('/classes/TestObject/' . $resp['objectId'], array(
	'name' => array('__op' => 'Increment', 'amount' => 1)
	));
		\LeanCloud\LeanClient::delete('/classes/TestObject/' . $resp['objectId']);
	}

	public function testIncrementOnNewObject()
	{
		$obj = array(
			'name'  => 'alice',
			'score' => array('__op' => 'Increment', 'amount' => 1)
			);
		$resp = \LeanCloud\LeanClient::post('/classes/TestObject', $obj);
		$this->assertNotEmpty($resp['objectId']);
	}

	public function testAddOnNewObject()
	{
		$obj = array(
			'name' => 'alice',
			'tags' => array(
				'__op'    => 'Add',
				'objects' => array('frontend')
				)
			);
		$resp = \LeanCloud\LeanClient::post('/classes/TestObject', $obj);
		$this->assertNotEmpty($resp['objectId']);
		\LeanCloud\LeanClient::delete('/classes/TestObject/' . $resp['objectId']);
	}

	public function testAddUniqueOnAddField()
	{
		$obj = array(
			'name' => 'alice',
			'tags' => array(
				'__op'    => 'Add',
				'objects' => array('frontend', 'frontend')
				)
			);
		$resp = \LeanCloud\LeanClient::post('/classes/TestObject', $obj);
		$this->assertNotEmpty($resp['objectId']);
		$resp2 = \LeanCloud\LeanClient::get('/classes/TestObject/' . $resp['objectId']);
		$this->assertEquals(array('frontend', 'frontend'), $resp2['tags']);
		$resp3 = \LeanCloud\LeanClient::put('/classes/TestObject/' . $resp['objectId'], array(
	'tags' => array(
		'__op'    => 'AddUnique',
		'objects' => array('css')
		)
	));
		$resp4 = \LeanCloud\LeanClient::get('/classes/TestObject/' . $resp['objectId']);
		$this->assertEquals(array('frontend', 'frontend', 'css'), $resp4['tags']);
		\LeanCloud\LeanClient::delete('/classes/TestObject/' . $resp['objectId']);
	}

	public function testHeterogeneousObjectsInArray()
	{
		$obj = array(
			'name' => 'alice',
			'tags' => array(
				'foo',
				42,
				array('a', 'b')
				)
			);
		$resp = \LeanCloud\LeanClient::post('/classes/TestObject', $obj);
		$this->assertNotEmpty($resp['objectId']);
		\LeanCloud\LeanClient::delete('/classes/TestObject/' . $resp['objectId']);
	}

	public function testSetHashValue()
	{
		$obj = array(
			'name' => 'alice',
			'attr' => array('age' => 12, 'gender' => 'female')
			);
		$resp = \LeanCloud\LeanClient::post('/classes/TestObject', $obj);
		$this->assertNotEmpty($resp['objectId']);
		$this->setExpectedException('LeanCloud\\CloudException', NULL, 1);
		$resp2 = \LeanCloud\LeanClient::put('/classes/TestObject/' . $resp['objectId'], array(
	'attr' => array(
		'__op'    => 'add',
		'objects' => array('favColor' => 'Orange')
		)
	));
		\LeanCloud\LeanClient::delete('/classes/TestObject/' . $resp['objectId']);
	}

	public function testAddRelation()
	{
		$adds = array(
			'__op'    => 'AddRelation',
			'objects' => array(
				array('__type' => 'Pointer', 'className' => 'TestObject', 'objectId' => 'abc001')
				)
			);
		$obj = array('name' => 'alice', 'likes' => $adds);
		$resp = \LeanCloud\LeanClient::post('/classes/TestObject', $obj);
		$this->assertNotEmpty($resp['objectId']);
		\LeanCloud\LeanClient::delete('/classes/TestObject/' . $resp['objectId']);
	}

	public function testRelationBatchOp()
	{
		$adds = array(
			'__op'    => 'AddRelation',
			'objects' => array(
				array('__type' => 'Pointer', 'className' => 'TestObject', 'objectId' => 'abc001')
				)
			);
		$removes = array(
			'__op'    => 'RemoveRelation',
			'objects' => array(
				array('__type' => 'Pointer', 'className' => 'TestObject', 'objectId' => 'abc002')
				)
			);
		$obj = array(
			'name'  => 'alice',
			'likes' => array(
				'__op' => 'Batch',
				'ops'  => array($adds, $removes)
				)
			);
		$this->setExpectedException('LeanCloud\\CloudException', NULL, 301);
		$resp = \LeanCloud\LeanClient::post('/classes/TestObject', $obj);
	}

	public function testBatchOperationOnArray()
	{
		$obj = array(
			'name' => 'Batch test',
			'tags' => array()
			);
		$resp = \LeanCloud\LeanClient::post('/classes/TestObject', $obj);
		$this->assertNotEmpty($resp['objectId']);
		$adds = array(
			'__op'    => 'Add',
			'objects' => array('javascript', 'frontend')
			);
		$removes = array(
			'__op'    => 'Remove',
			'objects' => array('frontend', 'css')
			);
		$obj = array(
			'tags' => array(
				'__op' => 'Batch',
				'ops'  => array($adds, $removes)
				)
			);
		$this->setExpectedException('LeanCloud\\CloudException', NULL, 301);
		$resp = \LeanCloud\LeanClient::put('/classes/TestObject/' . $resp['objectId'], $obj);
		\LeanCloud\LeanClient::delete('/classes/TestObject/' . $obj['objectId']);
	}

	public function testBatchGet()
	{
		$obj1 = array('name' => 'alice 1');
		$obj2 = array('name' => 'alice 2');
		$resp1 = \LeanCloud\LeanClient::post('/classes/TestObject', $obj1);
		$resp2 = \LeanCloud\LeanClient::post('/classes/TestObject', $obj2);
		$this->assertNotEmpty($resp1['objectId']);
		$this->assertNotEmpty($resp2['objectId']);
		$req[] = array('path' => '/1.1/classes/TestObject/' . $resp1['objectId'], 'method' => 'GET');
		$req[] = array('path' => '/1.1/classes/TestObject/' . $resp2['objectId'], 'method' => 'GET');
		$resp = \LeanCloud\LeanClient::post('/batch', array('requests' => $req));
		$this->assertEquals(2, count($resp));
		$this->assertEquals($resp1['objectId'], $resp[0]['success']['objectId']);
		$this->assertEquals($resp2['objectId'], $resp[1]['success']['objectId']);
	}

	public function testBatchGetNotFound()
	{
		$obj = array('name' => 'alice');
		$resp = \LeanCloud\LeanClient::post('/classes/TestObject', $obj);
		$this->assertNotEmpty($resp['objectId']);
		$req[] = array('path' => '/1.1/classes/TestObject/' . $resp['objectId'], 'method' => 'GET');
		$req[] = array('path' => '/1.1/classes/TestObject/nonexistent_id', 'method' => 'GET');
		$resp2 = \LeanCloud\LeanClient::batch($req);
		$this->assertNotEmpty($resp2[0]['success']);
		$this->assertEmpty($resp2[1]['success']);
		\LeanCloud\LeanClient::delete('/classes/TestObject/' . $resp['objectId']);
	}

	public function testUserLogin()
	{
		$data = array('username' => 'testuser', 'password' => '5akf#a?^G', 'phone' => '18612340000');
		$resp = \LeanCloud\LeanClient::post('/users', $data);
		$this->assertNotEmpty($resp['objectId']);
		$this->assertNotEmpty($resp['sessionToken']);
		$id = $resp['objectId'];
		$resp = \LeanCloud\LeanClient::get('/users/me', array('session_token' => $resp['sessionToken']));
		$this->assertNotEmpty($resp['objectId']);
		\LeanCloud\LeanClient::delete('/users/' . $id, $resp['sessionToken']);
		$this->setExpectedException('LeanCloud\\CloudException', NULL, 211);
		$resp = \LeanCloud\LeanClient::get('/users/me', array('session_token' => 'non-existent-token'));
	}
}

?>
