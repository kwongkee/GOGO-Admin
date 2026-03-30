<?php
// Ä£¿éLTDÌá¹©
namespace LeanCloud;

class LeanQuery
{
	private $className;
	private $select;
	private $include;
	private $limit;
	private $skip;
	private $order;
	private $extraOption;

	public function __construct($queryClass)
	{
		if (is_string($queryClass)) {
			$this->className = $queryClass;
		}
		else if (is_subclass_of($queryClass, 'LeanObject')) {
			$this->className = $queryClass::$className;
		}
		else {
			throw new \InvalidArgumentException('Query class invalid.');
		}

		$this->where = array();
		$this->select = array();
		$this->include = array();
		$this->order = array();
		$this->limit = -1;
		$this->skip = 0;
		$this->extraOption = array();
	}

	public function getClassName()
	{
		return $this->className;
	}

	private function _addCondition($key, $op, $val)
	{
		$this->where[$key][$op] = LeanClient::encode($val);
	}

	public function equalTo($key, $val)
	{
		$this->where[$key] = LeanClient::encode($val);
		return $this;
	}

	public function notEqualTo($key, $val)
	{
		$this->_addCondition($key, '$ne', $val);
		return $this;
	}

	public function lessThan($key, $val)
	{
		$this->_addCondition($key, '$lt', $val);
		return $this;
	}

	public function lessThanOrEqualTo($key, $val)
	{
		$this->_addCondition($key, '$lte', $val);
		return $this;
	}

	public function greaterThan($key, $val)
	{
		$this->_addCondition($key, '$gt', $val);
		return $this;
	}

	public function greaterThanOrEqualTo($key, $val)
	{
		$this->_addCondition($key, '$gte', $val);
		return $this;
	}

	public function containedIn($key, $vals)
	{
		$this->_addCondition($key, '$in', $vals);
		return $this;
	}

	public function notContainedIn($key, $vals)
	{
		$this->_addCondition($key, '$nin', $vals);
		return $this;
	}

	public function containsAll($key, $vals)
	{
		$this->_addCondition($key, '$all', $vals);
		return $this;
	}

	public function sizeEqualTo($key, $val)
	{
		$this->_addCondition($key, '$size', $val);
		return $this;
	}

	public function exists($key)
	{
		$this->_addCondition($key, '$exists', true);
		return $this;
	}

	public function notExists($key)
	{
		$this->_addCondition($key, '$exists', false);
		return $this;
	}

	public function contains($key, $val)
	{
		$this->_addCondition($key, '$regex', $val);
		return $this;
	}

	public function startsWith($key, $val)
	{
		$this->_addCondition($key, '$regex', '^' . $val);
		return $this;
	}

	public function endsWith($key, $val)
	{
		$this->_addCondition($key, '$regex', $val . '$');
		return $this;
	}

	public function matches($key, $regex, $modifiers = '')
	{
		$this->_addCondition($key, '$regex', $regex);

		if (!empty($modifiers)) {
			$this->_addCondition($key, '$options', $modifiers);
		}

		return $this;
	}

	public function matchesInQuery($key, $query)
	{
		$this->_addCondition($key, '$inQuery', array('where' => $query->where, 'className' => $query->getClassName()));
		return $this;
	}

	public function notMatchInQuery($key, $query)
	{
		$this->_addCondition($key, '$notInQuery', array('where' => $query->where, 'className' => $query->getClassName()));
		return $this;
	}

	public function matchesFieldInQuery($key, $queryKey, $query)
	{
		$this->_addCondition($key, '$select', array(
	'key'   => $queryKey,
	'query' => array('where' => $query->where, 'className' => $query->getClassName())
	));
		return $this;
	}

	public function notMatchFieldInQuery($key, $queryKey, $query)
	{
		$this->_addCondition($key, '$dontSelect', array(
	'key'   => $queryKey,
	'query' => array('where' => $query->where, 'className' => $query->getClassName())
	));
		return $this;
	}

	public function relatedTo($key, $obj)
	{
		$this->where['$relatedTo'] = array('key' => $key, 'object' => $obj->getPointer());
		return $this;
	}

	public function near($key, GeoPoint $point)
	{
		$this->_addCondition($key, '$nearSphere', $point);
		return $this;
	}

	public function withinRadians($key, GeoPoint $point, $distance)
	{
		$this->near($key, $point);
		$this->_addCondition($key, '$maxDistanceInRadians', $distance);
		return $this;
	}

	public function withinKilometers($key, GeoPoint $point, $distance)
	{
		$this->near($key, $point);
		$this->_addCondition($key, '$maxDistanceInKilometers', $distance);
		return $this;
	}

	public function withinMiles($key, GeoPoint $point, $distance)
	{
		$this->near($key, $point);
		$this->_addCondition($key, '$maxDistanceInMiles', $distance);
		return $this;
	}

	public function withinBox($key, GeoPoint $southwest, GeoPoint $northeast)
	{
		$this->_addCondition($key, '$within', array(
	'$box' => array($southwest, $northeast)
	));
		return $this;
	}

	public function select($keys)
	{
		if (!is_array($keys)) {
			$keys = func_get_args();
		}

		$this->select = array_merge($this->select, $keys);
		return $this;
	}

	public function _include($keys)
	{
		if (!is_array($keys)) {
			$keys = func_get_args();
		}

		$this->include = array_merge($this->include, $keys);
		return $this;
	}

	public function limit($n)
	{
		$this->limit = $n;
		return $this;
	}

	public function skip($n)
	{
		$this->skip = $n;
		return $this;
	}

	public function ascend($key)
	{
		$this->order = array($key);
		return $this;
	}

	public function descend($key)
	{
		$this->order = array('-' . $key);
		return $this;
	}

	public function addAscend($key)
	{
		$this->order[] = $key;
		return $this;
	}

	public function addDescend($key)
	{
		$this->order[] = '-' . $key;
		return $this;
	}

	static private function composeQuery($op, $queries)
	{
		$className = $queries[0]->getClassName();
		$conds = array();

		foreach ($queries as $q) {
			if ($q->getClassName() != $className) {
				throw new \RuntimeException('Query class incompatible.');
			}

			$conds[] = $q->where;
		}

		$query = new LeanQuery($className);
		$query->where[$op] = $conds;
		return $query;
	}

	static public function orQuery($queries)
	{
		if (!is_array($queries)) {
			$queries = func_get_args();
		}

		return self::composeQuery('$or', $queries);
	}

	static public function andQuery($queries)
	{
		if (!is_array($queries)) {
			$queries = func_get_args();
		}

		return self::composeQuery('$and', $queries);
	}

	public function addOption($key, $val)
	{
		$this->extraOption[$key] = $val;
		return $this;
	}

	public function encode()
	{
		$out = array();

		if (!empty($this->extraOption)) {
			$out = $this->extraOption;
		}

		if (!empty($this->where)) {
			$out['where'] = json_encode($this->where);
		}

		if (!empty($this->select)) {
			$out['keys'] = implode(',', $this->select);
		}

		if (!empty($this->include)) {
			$out['include'] = implode(',', $this->include);
		}

		if (0 < $this->skip) {
			$out['skip'] = $this->skip;
		}

		if (-1 < $this->limit) {
			$out['limit'] = $this->limit;
		}

		if (!empty($this->order)) {
			$out['order'] = implode(',', $this->order);
		}

		return $out;
	}

	public function get($objectId)
	{
		$this->equalTo('objectId', $objectId);
		return $this->first();
	}

	public function first()
	{
		$objects = $this->find($this->skip, 1);

		if (empty($objects)) {
			throw new CloudException('Object not found.', 101);
		}

		return $objects[0];
	}

	public function find($skip = -1, $limit = -1)
	{
		$params = $this->encode();

		if (0 <= $skip) {
			$params['skip'] = $skip;
		}

		if (0 <= $limit) {
			$params['limit'] = $limit;
		}

		$resp = LeanClient::get('/classes/' . $this->getClassName(), $params);
		$objects = array();

		foreach ($resp['results'] as $props) {
			$obj = LeanObject::create($this->getClassName());
			$obj->mergeAfterFetch($props);
			$objects[] = $obj;
		}

		return $objects;
	}

	public function count()
	{
		$params = $this->encode();
		$params['limit'] = 0;
		$params['count'] = 1;
		$resp = LeanClient::get('/classes/' . $this->getClassName(), $params);
		return $resp['count'];
	}

	static public function doCloudQuery($cql, $pvalues = array())
	{
		$data = array('cql' => $cql);

		if (!empty($pvalues)) {
			$data['pvalues'] = json_encode(LeanClient::encode($pvalues));
		}

		$resp = LeanClient::get('/cloudQuery', $data);
		$objects = array();

		foreach ($resp['results'] as $val) {
			$obj = LeanObject::create($resp['className'], $val['objectId']);
			$obj->mergeAfterFetch($val);
			$objects[] = $obj;
		}

		$resp['results'] = $objects;
		return $resp;
	}
}


?>
