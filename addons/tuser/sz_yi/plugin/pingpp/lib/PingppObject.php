<?php
// Ä£¿éLTDÌá¹©
namespace Pingpp;

class PingppObject implements \ArrayAccess
{
	static public $permanentAttributes;
	static public $nestedUpdatableAttributes;
	protected $_opts;
	protected $_values;
	protected $_unsavedValues;
	protected $_transientValues;
	protected $_retrieveOptions;

	static public function init()
	{
		self::$permanentAttributes = new Util\Set(array('_opts', 'id'));
		self::$nestedUpdatableAttributes = new Util\Set(array('metadata'));
	}

	public function __construct($id = NULL, $opts = NULL)
	{
		$this->_opts = $opts ? $opts : new Util\RequestOptions();
		$this->_values = array();
		$this->_unsavedValues = new Util\Set();
		$this->_transientValues = new Util\Set();
		$this->_retrieveOptions = array();

		if (is_array($id)) {
			foreach ($id as $key => $value) {
				if ($key != 'id') {
					$this->_retrieveOptions[$key] = $value;
				}
			}

			$id = $id['id'];
		}

		if ($id) {
			$this->id = $id;
		}
	}

	public function __set($k, $v)
	{
		if ($v === '') {
			throw new \InvalidArgumentException('You cannot set \'' . $k . '\'to an empty string. ' . 'We interpret empty strings as NULL in requests. ' . 'You may set obj->' . $k . ' = NULL to delete the property');
		}

		if (self::$nestedUpdatableAttributes->includes($k) && isset($this->$k) && is_array($v)) {
			$this->$k->replaceWith($v);
		}
		else {
			$this->_values[$k] = $v;
		}

		if (!self::$permanentAttributes->includes($k)) {
			$this->_unsavedValues->add($k);
		}
	}

	public function __isset($k)
	{
		return isset($this->_values[$k]);
	}

	public function __unset($k)
	{
		unset($this->_values[$k]);
		$this->_transientValues->add($k);
		$this->_unsavedValues->discard($k);
	}

	public function __get($k)
	{
		if (array_key_exists($k, $this->_values)) {
			return $this->_values[$k];
		}

		if ($this->_transientValues->includes($k)) {
			$class = get_class($this);
			$attrs = join(', ', array_keys($this->_values));
			$message = 'Pingpp Notice: Undefined property of ' . $class . ' instance: ' . $k . '. ' . 'HINT: The ' . $k . ' attribute was set in the past, however. ' . 'It was then wiped when refreshing the object ' . 'with the result returned by Pingpp\'s API, ' . 'probably as a result of a save(). The attributes currently ' . 'available on this object are: ' . $attrs;
			error_log($message);
			return null;
		}

		$class = get_class($this);
		error_log('Pingpp Notice: Undefined property of ' . $class . ' instance: ' . $k);
		return null;
	}

	public function offsetSet($k, $v)
	{
		$this->$k = $v;
	}

	public function offsetExists($k)
	{
		return array_key_exists($k, $this->_values);
	}

	public function offsetUnset($k)
	{
		unset($this->$k);
	}

	public function offsetGet($k)
	{
		return array_key_exists($k, $this->_values) ? $this->_values[$k] : null;
	}

	public function keys()
	{
		return array_keys($this->_values);
	}

	static public function constructFrom($values, $opts)
	{
		$obj = new static(isset($values->id) ? $values->id : null);
		$obj->refreshFrom($values, $opts);
		return $obj;
	}

	public function refreshFrom($values, $opts, $partial = false)
	{
		$this->_opts = $opts;

		if ($partial) {
			$removed = new Util\Set();
		}
		else {
			$removed = array_diff(array_keys($this->_values), array_keys(get_object_vars($values)));
		}

		foreach ($removed as $k) {
			if (self::$permanentAttributes->includes($k)) {
				continue;
			}

			unset($this->$k);
		}

		foreach ($values as $k => $v) {
			if (self::$permanentAttributes->includes($k)) {
				continue;
			}

			if (self::$nestedUpdatableAttributes->includes($k) && is_object($v)) {
				$this->_values[$k] = AttachedObject::constructFrom($v, $opts);
			}
			else {
				$this->_values[$k] = Util\Util::convertToPingppObject($v, $opts);
			}

			$this->_transientValues->discard($k);
			$this->_unsavedValues->discard($k);
		}
	}

	public function serializeParameters()
	{
		$params = array();

		if ($this->_unsavedValues) {
			foreach ($this->_unsavedValues->toArray() as $k) {
				$v = $this->$k;

				if ($v === NULL) {
					$v = '';
				}

				$params[$k] = $v;
			}
		}

		foreach (self::$nestedUpdatableAttributes->toArray() as $property) {
			if (isset($this->$property) && $this->$property instanceof PingppObject) {
				$params[$property] = $this->$property->serializeParameters();
			}
		}

		return $params;
	}

	public function jsonSerialize()
	{
		return $this->__toStdObject();
	}

	public function __toJSON()
	{
		if (defined('JSON_PRETTY_PRINT')) {
			return json_encode($this->__toStdObject(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
		}

		return json_encode($this->__toStdObject());
	}

	public function __toString()
	{
		return $this->__toJSON();
	}

	public function __toArray($recursive = false)
	{
		if ($recursive) {
			return Util\Util::convertPingppObjectToArray($this->_values);
		}

		return $this->_values;
	}

	public function __toStdObject()
	{
		return Util\Util::convertPingppObjectToStdObject($this->_values);
	}
}
PingppObject::init();

?>
