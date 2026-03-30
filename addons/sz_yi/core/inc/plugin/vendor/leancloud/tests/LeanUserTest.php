<?php
// Ä£¿éLTDÌá¹©
class LeanUserTest extends PHPUnit_Framework_TestCase
{
	static public function setUpBeforeClass()
	{
		\LeanCloud\LeanClient::initialize(getenv('LC_APP_ID'), getenv('LC_APP_KEY'), getenv('LC_APP_MASTER_KEY'));
		\LeanCloud\LeanClient::useRegion(getenv('LC_API_REGION'));
		\LeanCloud\LeanClient::setStorage(new \LeanCloud\Storage\SessionStorage());
		$user = new \LeanCloud\LeanUser();
		$user->setUsername('alice');
		$user->setPassword('blabla');

		try {
			$user->signUp();
			return NULL;
		}
		catch (\LeanCloud\CloudException $ex) {
		}
	}

	static public function tearDownAfterClass()
	{
		try {
			$user = \LeanCloud\LeanUser::logIn('alice', 'blabla');
			return NULL;
		}
		catch (\LeanCloud\CloudException $ex) {
			$user->destroy();
		}
	}

	public function setUp()
	{
		\LeanCloud\LeanUser::logOut();
		$this->openToken = array();
		$this->openToken['openid'] = '0395BA18A';
		$this->openToken['expires_in'] = '36000';
		$this->openToken['access_token'] = 'QaQF4C0j5Th5ed331b56ddMwm8WC';
	}

	public function testSetGetFields()
	{
		$user = new \LeanCloud\LeanUser();
		$user->setUsername('alice');
		$user->setEmail('alice@example.com');
		$user->setMobilePhoneNumber('18612340000');
		$this->assertEquals('alice', $user->getUsername());
		$this->assertEquals('alice@example.com', $user->getEmail());
		$this->assertEquals('18612340000', $user->getMobilePhoneNumber());
		$user->set('age', 24);
		$this->assertEquals(24, $user->get('age'));
	}

	public function testSaveNewUser()
	{
		$user = new \LeanCloud\LeanUser();
		$user->setUsername('alice');
		$user->setPassword('blabla');
		$this->setExpectedException('LeanCloud\\CloudException', 'Cannot save new user, please signUp first.');
		$user->save();
	}

	public function testUserSignUp()
	{
		$user = new \LeanCloud\LeanUser();
		$user->setUsername('alice2');
		$user->setPassword('blabla');
		$user->signUp();
		$this->assertNotEmpty($user->getObjectId());
		$this->assertNotEmpty($user->getSessionToken());
		$user->destroy();
	}

	public function testUserUpdate()
	{
		$user = \LeanCloud\LeanUser::logIn('alice', 'blabla');
		$user->setEmail('alice@example.com');
		$user->set('age', 24);
		$user->save();
		$this->assertNotEmpty($user->getUpdatedAt());
		$user2 = \LeanCloud\LeanUser::become($user->getSessionToken());
		$this->assertEquals('alice@example.com', $user2->getEmail());
		$this->assertEquals(24, $user2->get('age'));
	}

	public function testUserLogIn()
	{
		$user = \LeanCloud\LeanUser::logIn('alice', 'blabla');
		$this->assertNotEmpty($user->getObjectId());
		$this->assertEquals($user, \LeanCloud\LeanUser::getCurrentUser());
	}

	public function testBecome()
	{
		$user = \LeanCloud\LeanUser::logIn('alice', 'blabla');
		$user2 = \LeanCloud\LeanUser::become($user->getSessionToken());
		$this->assertNotEmpty($user2->getObjectId());
		$this->assertEquals($user2, \LeanCloud\LeanUser::getCurrentUser());
	}

	public function testLogOut()
	{
		$user = \LeanCloud\LeanUser::logIn('alice', 'blabla');
		$this->assertEquals($user, \LeanCloud\LeanUser::getCurrentUser());
		\LeanCloud\LeanUser::logOut();
		$this->assertNull(\LeanCloud\LeanUser::getCurrentUser());
	}

	public function testUpdatePassword()
	{
		$user = new \LeanCloud\LeanUser();
		$user->setUsername('alice3');
		$user->setPassword('blabla');
		$user->signUp();
		$this->assertNotEmpty($user->getObjectId());
		$this->assertNotEmpty($user->getSessionToken());
		$id = $user->getObjectId();
		$token = $user->getSessionToken();
		$user->updatePassword('blabla', 'yadayada');
		$this->assertNotEquals($token, $user->getSessionToken());
		$user->destroy();
	}

	public function testVerifyMobilePhone()
	{
		$this->setExpectedException('LeanCloud\\CloudException', NULL, 603);
		\LeanCloud\LeanUser::verifyMobilePhone('000000');
	}

	public function testLogInWithLinkedService()
	{
		$user = \LeanCloud\LeanUser::logIn('alice', 'blabla');
		$user->linkWith('weixin', $this->openToken);
		$auth = $user->get('authData');
		$this->assertEquals($this->openToken, $auth['weixin']);
		$user2 = \LeanCloud\LeanUser::logInWith('weixin', $this->openToken);
		$this->assertEquals($user->getUsername(), $user2->getUsername());
		$this->assertEquals($user->getSessionToken(), $user2->getSessionToken());
		$user2->unlinkWith('weixin');
	}

	public function testSignUpWithLinkedService()
	{
		$user = \LeanCloud\LeanUser::logInWith('weixin', $this->openToken);
		$this->assertNotEmpty($user->getSessionToken());
		$this->assertNotEmpty($user->getObjectId());
		$this->assertEquals($user, \LeanCloud\LeanUser::getCurrentUser());
		$user->destroy();
	}

	public function testUnlinkService()
	{
		$user = \LeanCloud\LeanUser::logInWith('weixin', $this->openToken);
		$token = $user->getSessionToken();
		$authData = $user->get('authData');
		$this->assertEquals($this->openToken, $authData['weixin']);
		$user->unlinkWith('weixin');
		$user2 = \LeanCloud\LeanUser::become($token);
		$authData = $user2->get('authData');
		$this->assertTrue(!isset($authData['weixin']));
		$user2->destroy();
	}
}

?>
