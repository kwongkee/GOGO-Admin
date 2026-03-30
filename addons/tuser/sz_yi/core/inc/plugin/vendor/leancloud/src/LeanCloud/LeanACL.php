<?php
// Ä£¿éLTDÌá¹©
namespace LeanCloud;

class LeanACL
{
	const PUBLIC_KEY = '*';

	private $data;

	public function __construct($val = NULL)
	{
		$this->data = array();

		if (!isset($val)) {
			return NULL;
		}

		if ($val instanceof LeanUser) {
			$this->setReadAccess($val, true);
			$this->setWriteAccess($val, true);
			return NULL;
		}

		if (is_array($val)) {
			foreach ($val as $id => $attr) {
				if (!is_string($id)) {
					throw new \RuntimeException('Invalid ACL data');
				}

				if (isset($attr['read']) || isset($attr['write'])) {
					$this->data[$id] = $attr;
				}
				else {
					throw new \RuntimeException('Invalid ACL data');
				}
			}
		}
	}

	private function setAccess($target, $accessType, $flag)
	{
		if (empty($target)) {
			throw new \InvalidArgumentException('Access target cannot be empty');
		}

		if (!in_array($accessType, array('read', 'write'))) {
			throw new \InvalidArgumentException('ACL access type must be' . ' either read or write.');
		}

		$access = array();

		if (isset($this->data[$target])) {
			$access = $this->data[$target];
		}

		$access[$accessType] = $flag;
		$this->data[$target] = $access;
	}

	private function getAccess($target, $accessType)
	{
		if (empty($target)) {
			throw new \InvalidArgumentException('Access target cannot be empty');
		}

		if (!in_array($accessType, array('read', 'write'))) {
			throw new \InvalidArgumentException('ACL access type must be' . ' either read or write.');
		}

		if (isset($this->data[$target][$accessType])) {
			return $this->data[$target][$accessType];
		}

		return false;
	}

	public function getPublicReadAccess()
	{
		return $this->getAccess(self::PUBLIC_KEY, 'read');
	}

	public function getPublicWriteAccess()
	{
		return $this->getAccess(self::PUBLIC_KEY, 'write');
	}

	public function setPublicReadAccess($flag)
	{
		$this->setAccess(self::PUBLIC_KEY, 'read', $flag);
		return $this;
	}

	public function setPublicWriteAccess($flag)
	{
		$this->setAccess(self::PUBLIC_KEY, 'write', $flag);
		return $this;
	}

	public function getRoleReadAccess($role)
	{
		if ($role instanceof LeanRole) {
			$role = $role->getName();
		}

		return $this->getAccess('role:' . $role, 'read');
	}

	public function getRoleWriteAccess($role)
	{
		if ($role instanceof LeanRole) {
			$role = $role->getName();
		}

		return $this->getAccess('role:' . $role, 'write');
	}

	public function setRoleReadAccess($role, $flag)
	{
		if ($role instanceof LeanRole) {
			$role = $role->getName();
		}

		if (!is_string($role)) {
			throw new \InvalidArgumentException('role must be either ' . 'LeanRole or string.');
		}

		$this->setAccess('role:' . $role, 'read', $flag);
		return $this;
	}

	public function setRoleWriteAccess($role, $flag)
	{
		if ($role instanceof LeanRole) {
			$role = $role->getName();
		}

		if (!is_string($role)) {
			throw new \InvalidArgumentException('role must be either ' . 'LeanRole or string.');
		}

		$this->setAccess('role:' . $role, 'write', $flag);
		return $this;
	}

	public function getReadAccess($user)
	{
		if ($user instanceof LeanUser) {
			$user = $user->getObjectId();
		}

		return $this->getAccess($user, 'read');
	}

	public function getWriteAccess($user)
	{
		if ($user instanceof LeanUser) {
			$user = $user->getObjectId();
		}

		return $this->getAccess($user, 'write');
	}

	public function setReadAccess($user, $flag)
	{
		if ($user instanceof LeanUser) {
			if (!$user->getObjectId()) {
				throw new \RuntimeException('user must be saved before ' . 'being assigned in ACL.');
			}

			$user = $user->getObjectId();
		}

		if (!is_string($user)) {
			throw new \InvalidArgumentException('user must be either ' . ' LeanUser or objectId.');
		}

		$this->setAccess($user, 'read', $flag);
		return $this;
	}

	public function setWriteAccess($user, $flag)
	{
		if ($user instanceof LeanUser) {
			if (!$user->getObjectId()) {
				throw new \RuntimeException('user must be saved before ' . 'being assigned in ACL.');
			}

			$user = $user->getObjectId();
		}

		if (!is_string($user)) {
			throw new \InvalidArgumentException('user must be either ' . ' LeanUser or objectId.');
		}

		$this->setAccess($user, 'write', $flag);
		return $this;
	}

	public function encode()
	{
		return $this->data;
	}
}


?>
