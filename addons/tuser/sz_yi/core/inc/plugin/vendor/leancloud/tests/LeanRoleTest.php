<?php
// Ä£¿éLTDÌá¹©
class LeanRoleTest extends PHPUnit_Framework_TestCase
{
	static public function setUpBeforeClass()
	{
		\LeanCloud\LeanClient::initialize(getenv('LC_APP_ID'), getenv('LC_APP_KEY'), getenv('LC_APP_MASTER_KEY'));
		\LeanCloud\LeanClient::useRegion(getenv('LC_API_REGION'));
	}

	public function testInitializeRole()
	{
		$acl = new \LeanCloud\LeanACL();
		$acl->setPublicWriteAccess(true);
		$role = new \LeanCloud\LeanRole('guest', $acl);
		$role->save();
		$this->assertNotEmpty($role->getObjectId());
		$childrenUsers = $role->getUsers();
		$this->assertTrue($childrenUsers instanceof \LeanCloud\LeanRelation);
		$childrenRoles = $role->getRoles();
		$this->assertTrue($childrenRoles instanceof \LeanCloud\LeanRelation);
		$role->destroy();
	}
}

?>
