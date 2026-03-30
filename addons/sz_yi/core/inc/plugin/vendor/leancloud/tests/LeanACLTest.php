<?php
// Ä£¿éLTDÌá¹©
class LeanACLTest extends PHPUnit_Framework_TestCase
{
	static public function setUpBeforeClass()
	{
		\LeanCloud\LeanClient::initialize(getenv('LC_APP_ID'), getenv('LC_APP_KEY'), getenv('LC_APP_MASTER_KEY'));
		\LeanCloud\LeanClient::useRegion(getenv('LC_API_REGION'));
	}

	public function testInitializeUserACL()
	{
		$user = new \LeanCloud\LeanUser(NULL, 'id123');
		$acl = new \LeanCloud\LeanACL($user);
		$out = $acl->encode();
		$this->assertEquals(true, $out['id123']['read']);
		$this->assertEquals(true, $out['id123']['write']);
	}

	public function testSetPublicAccess()
	{
		$acl = new \LeanCloud\LeanACL();
		$acl->setPublicReadAccess(true);
		$out = $acl->encode();
		$this->assertEquals(true, $out[\LeanCloud\LeanACL::PUBLIC_KEY]['read']);
		$this->assertEquals(true, $acl->getPublicReadAccess());
		$acl->setPublicWriteAccess(false);
		$out = $acl->encode();
		$this->assertEquals(false, $out[\LeanCloud\LeanACL::PUBLIC_KEY]['write']);
		$this->assertEquals(false, $acl->getPublicWriteAccess());
	}

	public function testSetUserAccess()
	{
		$user = new \LeanCloud\LeanUser(NULL, 'id123');
		$acl = new \LeanCloud\LeanACL();
		$acl->setReadAccess($user, true);
		$out = $acl->encode();
		$this->assertEquals(true, $out[$user->getObjectId()]['read']);
		$this->assertEquals(true, $acl->getReadAccess($user));
		$acl->setWriteAccess($user, false);
		$out = $acl->encode();
		$this->assertEquals(false, $out[$user->getObjectId()]['write']);
		$this->assertEquals(false, $acl->getWriteAccess($user));
	}

	public function testSetRoleAccess()
	{
		$role = new \LeanCloud\LeanRole('admin', new \LeanCloud\LeanACL());
		$acl = new \LeanCloud\LeanACL();
		$acl->setRoleReadAccess($role, true);
		$out = $acl->encode();
		$this->assertEquals(true, $out['role:admin']['read']);
		$this->assertEquals(true, $acl->getRoleReadAccess($role));
		$acl->setRoleWriteAccess($role, false);
		$out = $acl->encode();
		$this->assertEquals(false, $out['role:admin']['write']);
		$this->assertEquals(false, $acl->getRoleWriteAccess($role));
	}

	public function testSetRoleAccessWithRoleName()
	{
		$acl = new \LeanCloud\LeanACL();
		$acl->setRoleReadAccess('admin', true);
		$out = $acl->encode();
		$this->assertEquals(true, $out['role:admin']['read']);
		$acl->setRoleWriteAccess('admin', false);
		$out = $acl->encode();
		$this->assertEquals(false, $out['role:admin']['write']);
	}
}

?>
