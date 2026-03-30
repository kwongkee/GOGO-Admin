<?php
// Ä£¿éLTDÌá¹©
namespace LeanCloud;

class LeanObject
{
	static private $_registeredClasses = array();
	private $_className;
	private $_data;
	private $_operationSet;

	public function __construct($className = NULL, $objectId = NULL)
	{
		$class = get_called_class();
		$name = static::getRegisteredClassName();
		$className = ($className ? $className : $name);
		if (!$className || (($class !== 'LeanCloud\\LeanObject') && ($className !== $name))) {
			throw new \InvalidArgumentException('className is invalid.');
		}

		$this->_className = $className;
		$this->_data = array();
		$this->_operationSet = array();
		$this->_data['objectId'] = $objectId;
	}

	static public function create($className, $objectId = NULL)
	{
		if (isset(self::$_registeredClasses[$className])) {
			return new self::$_registeredClasses[$className]($className, $objectId);
		}

		return new LeanObject($className, $objectId);
	}

	static public function registerClass()
	{
		if (isset($className)) {
			$class = get_called_class();
			$name = static::$className;

			if (isset(self::$_registeredClasses[$name])) {
				$prevClass = self::$_registeredClasses[$name];

				if ($class !== $prevClass) {
					throw new \RuntimeException('className \'' . $name . '\' ' . 'has already been registered.');
					return NULL;
				}
			}
			else {
				self::$_registeredClasses[static::$className] = get_called_class();
				return NULL;
			}
		}
		else {
			throw new \RuntimeException('Cannot register class without ' . '::className.');
		}
	}

	static private function getRegisteredClassName()
	{
		return array_search(get_called_class(), self::$_registeredClasses);
	}

	public function getClassName()
	{
		return $this->_className;
	}

	public function getPointer()
	{
		if (!$this->getObjectId()) {
			throw new \RuntimeException('Object without ID cannot ' . 'be serialized.');
		}

		return array('__type' => 'Pointer', 'className' => $this->getClassName(), 'objectId' => $this->getObjectId());
	}

	public function getObjectId()
	{
		return $this->get('objectId');
	}

	public function getCreatedAt()
	{
		return $this->get('createdAt');
	}

	public function getUpdatedAt()
	{
		return $this->get('updatedAt');
	}

	public function set($key, $val)
	{
		if (in_array($key, array('objectId', 'createdAt', 'updatedAt'))) {
			throw new \RuntimeException('Preserved field could not be set.');
		}

		if (!$val instanceof Operation\IOperation) {
			$val = new Operation\SetOperation($key, $val);
		}

		$this->_applyOperation($val);
		return $this;
	}

	public function setACL(LeanACL $acl)
	{
		return $this->set('ACL', $acl);
	}

	public function getACL()
	{
		return $this->get('ACL');
	}

	public function delete($key)
	{
		$this->_applyOperation(new Operation\DeleteOperation($key));
		return $this;
	}

	public function get($key)
	{
		if (!isset($this->_data[$key])) {
			return null;
		}

		$val = $this->_data[$key];

		if ($val instanceof LeanRelation) {
			return $this->getRelation($key);
		}

		return $this->_data[$key];
	}

	public function increment($key, $amount = 1)
	{
		$this->_applyOperation(new Operation\IncrementOperation($key, $amount));
		return $this;
	}

	private function _getPreviousOp($key)
	{
		if (isset($this->_operationSet[$key])) {
			return $this->_operationSet[$key];
		}

		return null;
	}

	private function _applyOperation($operation)
	{
		$key = $operation->getKey();
		$oldval = $this->get($key);
		$newval = $operation->applyOn($oldval, $this);

		if ($newval !== null) {
			$this->_data[$key] = $newval;
		}
		else {
			if (isset($this->_data[$key])) {
				unset($this->_data[$key]);
			}
		}

		$prevOp = $this->_getPreviousOp($key);
		$newOp = ($prevOp ? $operation->mergeWith($prevOp) : $operation);
		$this->_operationSet[$key] = $newOp;
	}

	public function addIn($key, $val)
	{
		$this->_applyOperation(new Operation\ArrayOperation($key, array($val), 'Add'));
		return $this;
	}

	public function addUniqueIn($key, $val)
	{
		$this->_applyOperation(new Operation\ArrayOperation($key, array($val), 'AddUnique'));
		return $this;
	}

	public function removeIn($key, $val)
	{
		$this->_applyOperation(new Operation\ArrayOperation($key, array($val), 'Remove'));
		return $this;
	}

	public function isDirty()
	{
		return !empty($this->_operationSet);
	}

	private function getSaveData()
	{
		return LeanClient::encode($this->_operationSet);
	}

	public function save()
	{
		if (!$this->isDirty()) {
			return NULL;
		}

		return self::saveAll(array($this));
	}

	private function _mergeData($data)
	{
		foreach (array('createdAt', 'updatedAt') as $key) {
			if (isset($data[$key]) && is_string($data[$key])) {
				$data[$key] = array('__type' => 'Date', 'iso' => $data[$key]);
			}
		}

		foreach ($data as $key => $val) {
			$this->_data[$key] = LeanClient::decode($val, $key);
		}
	}

	public function mergeAfterSave($data)
	{
		$this->_operationSet = array();
		$this->_mergeData($data);
	}

	public function mergeAfterFetch($data)
	{
		foreach ($data as $key => $val) {
			if (isset($this->_operationSet[$key])) {
				unset($this->_operationSet[$key]);
			}
		}

		$this->_mergeData($data);
	}

	public function fetch()
	{
		static::fetchAll(array($this));
	}

	public function fetchAll($objects)
	{
		$batch = array();

		foreach ($objects as $obj) {
			if (!$obj->getObjectId()) {
				throw new \RuntimeException('Cannot fetch object without ID.');
			}

			$batch[$obj->getObjectId()] = $obj;
		}

		if (empty($batch)) {
			return NULL;
		}

		$requests = array();
		$objects = array();

		foreach ($batch as $obj) {
			$requests[] = array('path' => '/1.1/classes/' . $obj->getClassName() . '/' . $obj->getObjectId(), 'method' => 'GET');
			$objects[] = $obj;
		}

		$sessionToken = LeanUser::getCurrentSessionToken();
		$response = LeanClient::batch($requests, $sessionToken);
		$errors = array();

		foreach ($objects as $i => $obj) {
			if (isset($response[$i]['success'])) {
				if (!empty($response[$i]['success'])) {
					$obj->mergeAfterFetch($response[$i]['success']);
				}
				else {
					$errors[] = array('request' => $requests[$i], 'error' => 'Object not found.');
				}
			}
			else {
				$errors[] = array('request' => $requests[$i], 'error' => $response[$i]['error']);
			}
		}

		if (0 < count($errors)) {
			throw new CloudException('Batch requests error: ' . json_encode($errors));
		}
	}

	public function destroy()
	{
		if (!$this->getObjectId()) {
			return false;
		}

		return self::destroyAll(array($this));
	}

	public function getQuery()
	{
		return new LeanQuery($this->getClassName());
	}

	public function getRelation($key)
	{
		$val = (isset($this->_data[$key]) ? $this->_data[$key] : null);

		if ($val) {
			if ($val instanceof LeanRelation) {
				$val->setParentAndKey($this, $key);
				return $val;
			}

			throw new \RuntimeException('Field ' . $key . ' is not relation.');
		}

		return new LeanRelation($this, $key);
	}

	static public function traverse($value, &$seen, $func)
	{
		if ($value instanceof LeanObject) {
			if (!in_array($value, $seen)) {
				$seen[] = $value;
				static::traverse($value->_data, $seen, $func);
				$func($value);
				return NULL;
			}
		}
		else {
			if (is_array($value)) {
				foreach ($value as $val) {
					if (is_array($val)) {
						static::traverse($val, $seen, $func);
					}
					else if ($val instanceof LeanObject) {
						static::traverse($val, $seen, $func);
					}
					else {
						$func($val);
					}
				}

				return NULL;
			}

			$func($value);
		}
	}

	public function findUnsavedChildren()
	{
		$unsavedChildren = array();
		$seen = array($this);
		static::traverse($this->_data, $seen, function($val) use(&$unsavedChildren) {
			if ($val instanceof LeanObject || $val instanceof LeanFile) {
				if ($val->isDirty()) {
					$unsavedChildren[] = $val;
				}
			}
		});
		return $unsavedChildren;
	}

	static public function saveAll($objects)
	{
		if (empty($objects)) {
			return NULL;
		}

		$unsavedChildren = array();

		foreach ($objects as $obj) {
			$unsavedChildren = array_merge($unsavedChildren, $obj->findUnsavedChildren());
		}

		$children = array();

		foreach ($unsavedChildren as $obj) {
			if ($obj instanceof LeanFile) {
				$obj . save();
			}
			else {
				if ($obj instanceof LeanObject) {
					if (!in_array($obj, $children)) {
						$children[] = $obj;
					}
				}
			}
		}

		static::batchSave($children);
		static::batchSave($objects);
	}

	static private function batchSave($objects, $batchSize = 20)
	{
		if (empty($objects)) {
			return NULL;
		}

		$batch = array();
		$remaining = array();
		$count = 0;

		foreach ($objects as $obj) {
			if (!$obj->isDirty()) {
				continue;
			}

			if ($batchSize < $count) {
				$remaining[] = $obj;
				++$count;
				continue;
			}

			++$count;
			$batch[] = $obj;
		}

		$path = '/1.1/classes';
		$requests = array();
		$objects = array();

		foreach ($batch as $obj) {
			$req = array('body' => $obj->getSaveData());

			if ($obj->getObjectId()) {
				$req['method'] = 'PUT';
				$req['path'] = $path . '/' . $obj->getClassName() . '/' . $obj->getObjectId();
			}
			else {
				$req['method'] = 'POST';
				$req['path'] = $path . '/' . $obj->getClassName();
			}

			$requests[] = $req;
			$objects[] = $obj;
		}

		$sessionToken = LeanUser::getCurrentSessionToken();
		$response = LeanClient::batch($requests, $sessionToken);
		$errors = array();

		foreach ($objects as $i => $obj) {
			if (isset($response[$i]['success'])) {
				$obj->mergeAfterSave($response[$i]['success']);
			}
			else {
				$errors[] = array('request' => $requests[$i], 'error' => $response[$i]['error']);
			}
		}

		if (0 < count($errors)) {
			throw new CloudException('Batch requests error: ' . json_encode($errors));
		}

		static::batchSave($remaining, $batchSize);
	}

	static public function destroyAll($objects)
	{
		$batch = array();

		foreach ($objects as $obj) {
			if (!$obj->getObjectId()) {
				throw new \RuntimeException('Cannot destroy object without ID');
			}

			$batch[$obj->getObjectId()] = $obj;
		}

		if (empty($batch)) {
			return NULL;
		}

		$requests = array();
		$objects = array();

		foreach ($batch as $obj) {
			$requests[] = array('path' => '/1.1/classes/' . $obj->getClassName() . '/' . $obj->getObjectId(), 'method' => 'DELETE');
			$objects[] = $obj;
		}

		$sessionToken = LeanUser::getCurrentSessionToken();
		$response = LeanClient::batch($requests, $sessionToken);
		$errors = array();

		foreach ($objects as $i => $obj) {
			if (isset($response[$i]['error'])) {
				$errors[] = array('request' => $requests[$i], 'error' => $response[$i]['error']);
			}
		}

		if (0 < count($errors)) {
			throw new CloudException('Batch requests error: ' . json_encode($errors));
		}
	}
}


?>
