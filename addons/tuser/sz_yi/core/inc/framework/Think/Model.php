<?php
// Ä£¿éLTDÌá¹©
namespace Think;

class Model
{
	const MODEL_INSERT = 1;
	const MODEL_UPDATE = 2;
	const MODEL_BOTH = 3;
	const MUST_VALIDATE = 1;
	const EXISTS_VALIDATE = 0;
	const VALUE_VALIDATE = 2;

	protected $db;
	private $_db = array();
	protected $pk = 'id';
	protected $autoinc = false;
	protected $tablePrefix;
	protected $name = '';
	protected $dbName = '';
	protected $connection = '';
	protected $tableName = '';
	protected $trueTableName = '';
	protected $error = '';
	protected $fields = array();
	protected $data = array();
	protected $options = array();
	protected $_validate = array();
	protected $_auto = array();
	protected $_map = array();
	protected $_scope = array();
	protected $autoCheckFields = true;
	protected $patchValidate = false;
	protected $methods = array('strict', 'order', 'alias', 'having', 'group', 'lock', 'distinct', 'auto', 'filter', 'validate', 'result', 'token', 'index', 'force');

	public function __construct($name = '', $tablePrefix = '', $connection = '')
	{
		$this->_initialize();

		if (!empty($name)) {
			if (strpos($name, '.')) {
				list($this->dbName, $this->name) = explode('.', $name);
			}
			else {
				$this->name = $name;
			}
		}
		else {
			if (empty($this->name)) {
				$this->name = $this->getModelName();
			}
		}

		if (is_null($tablePrefix)) {
			$this->tablePrefix = '';
		}
		else if ('' != $tablePrefix) {
			$this->tablePrefix = $tablePrefix;
		}
		else {
			if (!isset($this->tablePrefix)) {
				$this->tablePrefix = C('DB_PREFIX');
			}
		}

		$this->db(0, empty($this->connection) ? $connection : $this->connection, true);
	}

	protected function _initialize()
	{
	}

	public function db($linkNum = '', $config = '', $force = false)
	{
		if (('' === $linkNum) && $this->db) {
			return $this->db;
		}

		if (!isset($this->_db[$linkNum]) || $force) {
			if (!empty($config) && is_string($config) && (false === strpos($config, '/'))) {
				$config = C($config);
			}

			$this->_db[$linkNum] = Db::getInstance($config);
		}
		else {
			if (NULL === $config) {
				$this->_db[$linkNum]->close();
				unset($this->_db[$linkNum]);
				return NULL;
			}
		}

		$this->db = $this->_db[$linkNum];
		$this->_after_db();
		if (!empty($this->name) && $this->autoCheckFields) {
			$this->_checkTableInfo();
		}

		return $this;
	}

	protected function _after_db()
	{
	}

	public function getModelName()
	{
		if (empty($this->name)) {
			$name = substr(get_class($this), 0, 0 - strlen(C('DEFAULT_M_LAYER')));

			if ($pos = strrpos($name, '\\')) {
				$this->name = substr($name, $pos + 1);
			}
			else {
				$this->name = $name;
			}
		}

		return $this->name;
	}

	protected function _checkTableInfo()
	{
		if (empty($this->fields)) {
			if (C('DB_FIELDS_CACHE')) {
				echo C('DB_FIELDS_CACHE');
				$db = $this->dbName ?: C('DB_NAME');
				$fields = F('_fields/' . strtolower($db . '.' . $this->tablePrefix . $this->name));

				if ($fields) {
					$this->fields = $fields;

					if (!empty($fields['_pk'])) {
						$this->pk = $fields['_pk'];
					}

					return NULL;
				}
			}

			$this->flush();
		}
	}

	public function flush()
	{
		$this->db->setModel($this->name);
		$fields = $this->db->getFields($this->getTableName());

		if (!$fields) {
			return false;
		}

		$this->fields = array_keys($fields);
		unset($this->fields['_pk']);

		foreach ($fields as $key => $val) {
			$type[$key] = $val['type'];

			if ($val['primary']) {
				if (isset($this->fields['_pk']) && ($this->fields['_pk'] != null)) {
					if (is_string($this->fields['_pk'])) {
						$this->pk = array($this->fields['_pk']);
						$this->fields['_pk'] = $this->pk;
					}

					$this->pk[] = $key;
					$this->fields['_pk'][] = $key;
				}
				else {
					$this->pk = $key;
					$this->fields['_pk'] = $key;
				}

				if ($val['autoinc']) {
					$this->autoinc = true;
				}
			}
		}

		$this->fields['_type'] = $type;

		if (C('DB_FIELDS_CACHE')) {
			$db = $this->dbName ?: C('DB_NAME');
			F('_fields/' . strtolower($db . '.' . $this->tablePrefix . $this->name), $this->fields);
		}
	}

	public function getField($field, $sepa = NULL)
	{
		$options['field'] = $field;
		$options = $this->_parseOptions($options);

		if (isset($options['cache'])) {
			$cache = $options['cache'];
			$key = (is_string($cache['key']) ? $cache['key'] : md5($sepa . serialize($options)));
			$data = S($key, '', $cache);

			if (false !== $data) {
				return $data;
			}
		}

		$field = trim($field);
		if (strpos($field, ',') && (false !== $sepa)) {
			if (!isset($options['limit'])) {
				$options['limit'] = is_numeric($sepa) ? $sepa : '';
			}

			$resultSet = $this->db->select($options);

			if (!empty($resultSet)) {
				$_field = explode(',', $field);
				$field = array_keys($resultSet[0]);
				$key1 = array_shift($field);
				$key2 = array_shift($field);
				$cols = array();
				$count = count($_field);

				foreach ($resultSet as $result) {
					$name = $result[$key1];

					if (2 == $count) {
						$cols[$name] = $result[$key2];
					}
					else {
						$cols[$name] = is_string($sepa) ? implode($sepa, array_slice($result, 1)) : $result;
					}
				}

				if (isset($cache)) {
					S($key, $cols, $cache);
				}

				return $cols;
			}
		}
		else {
			if (true !== $sepa) {
				$options['limit'] = is_numeric($sepa) ? $sepa : 1;
			}

			$result = $this->db->select($options);

			if (!empty($result)) {
				if ((true !== $sepa) && (1 == $options['limit'])) {
					$data = reset($result[0]);

					if (isset($cache)) {
						S($key, $data, $cache);
					}

					return $data;
				}

				foreach ($result as $val) {
					$array[] = $val[$field];
				}

				if (isset($cache)) {
					S($key, $array, $cache);
				}

				return $array;
			}
		}

		return null;
	}

	public function __call($method, $args)
	{
		if (in_array(strtolower($method), $this->methods, true)) {
			$this->options[strtolower($method)] = $args[0];
			return $this;
		}

		if (in_array(strtolower($method), array('count', 'sum', 'min', 'max', 'avg'), true)) {
			$field = (isset($args[0]) ? $args[0] : '*');
			return $this->getField(strtoupper($method) . '(' . $field . ') AS tp_' . $method);
		}

		if (strtolower(substr($method, 0, 5)) == 'getby') {
			$field = parse_name(substr($method, 5));
			$where[$field] = $args[0];
			return $this->where($where)->find();
		}

		if (strtolower(substr($method, 0, 10)) == 'getfieldby') {
			$name = parse_name(substr($method, 10));
			$where[$name] = $args[0];
			return $this->where($where)->getField($args[1]);
		}

		if (isset($this->_scope[$method])) {
			return $this->scope($method, $args[0]);
		}

		E('Think\\Model' . ':' . $method . L('_METHOD_NOT_EXIST_'));
	}

	public function getLastInsID()
	{
		return $this->db->getLastInsID();
	}

	public function getLastSql()
	{
		return $this->db->getLastSql($this->name);
	}

	public function _sql()
	{
		return $this->getLastSql();
	}

	public function getPk()
	{
		return $this->pk;
	}

	public function getDbFields()
	{
		if (isset($this->options['table'])) {
			if (is_array($this->options['table'])) {
				$table = key($this->options['table']);
			}
			else {
				$table = $this->options['table'];

				if (strpos($table, ')')) {
					return false;
				}
			}

			$fields = $this->db->getFields($table);
			return $fields ? array_keys($fields) : false;
		}

		if ($this->fields) {
			$fields = $this->fields;
			unset($fields['_type']);
			unset($fields['_pk']);
			return $fields;
		}

		return false;
	}

	protected function _parseType(&$data, $key)
	{
		if (!isset($this->options['bind'][':' . $key]) && isset($this->fields['_type'][$key])) {
			$fieldType = strtolower($this->fields['_type'][$key]);

			if (false !== strpos($fieldType, 'enum')) {
				return NULL;
			}

			if ((false === strpos($fieldType, 'bigint')) && (false !== strpos($fieldType, 'int'))) {
				$data[$key] = intval($data[$key]);
				return NULL;
			}

			if ((false !== strpos($fieldType, 'float')) || (false !== strpos($fieldType, 'double'))) {
				$data[$key] = floatval($data[$key]);
				return NULL;
			}

			if (false !== strpos($fieldType, 'bool')) {
				$data[$key] = (bool) $data[$key];
			}
		}
	}

	public function field($field, $except = false)
	{
		if (true === $field) {
			$fields = $this->getDbFields();
			$field = $fields ?: '*';
		}
		else {
			if ($except) {
				if (is_string($field)) {
					$field = explode(',', $field);
				}

				$fields = $this->getDbFields();
				$field = ($fields ? array_diff($fields, $field) : $field);
			}
		}

		$this->options['field'] = $field;
		return $this;
	}

	public function getTableName()
	{
		if (empty($this->trueTableName)) {
			$tableName = (!empty($this->tablePrefix) ? $this->tablePrefix : '');

			if (!empty($this->tableName)) {
				$tableName .= $this->tableName;
			}
			else {
				$tableName .= parse_name($this->name);
			}

			$this->trueTableName = strtolower($tableName);
		}

		return (!empty($this->dbName) ? $this->dbName . '.' : '') . $this->trueTableName;
	}

	public function where($where, $parse = NULL)
	{
		if (!is_null($parse) && is_string($where)) {
			if (!is_array($parse)) {
				$parse = func_get_args();
				array_shift($parse);
			}

			$parse = array_map(array($this->db, 'escapeString'), $parse);
			$where = vsprintf($where, $parse);
		}
		else {
			if (is_object($where)) {
				$where = get_object_vars($where);
			}
		}

		if (is_string($where) && ('' != $where)) {
			$map = array();
			$map['_string'] = $where;
			$where = $map;
		}

		if (isset($this->options['where'])) {
			$this->options['where'] = array_merge($this->options['where'], $where);
		}
		else {
			$this->options['where'] = $where;
		}

		return $this;
	}

	protected function _parseOptions($options = array())
	{
		if (is_array($options)) {
			$options = array_merge($this->options, $options);
		}

		if (!isset($options['table'])) {
			$options['table'] = $this->getTableName();
			$fields = $this->fields;
		}
		else {
			$fields = $this->getDbFields();
		}

		if (!empty($options['alias'])) {
			$options['table'] .= ' ' . $options['alias'];
		}

		$options['model'] = $this->name;
		if (isset($options['where']) && is_array($options['where']) && !empty($fields) && !isset($options['join'])) {
			foreach ($options['where'] as $key => $val) {
				$key = trim($key);

				if (in_array($key, $fields, true)) {
					if (is_scalar($val)) {
						$this->_parseType($options['where'], $key);
					}
				}
				else {
					if (!is_numeric($key) && ('_' != substr($key, 0, 1)) && (false === strpos($key, '.')) && (false === strpos($key, '(')) && (false === strpos($key, '|')) && (false === strpos($key, '&'))) {
						if (!empty($this->options['strict'])) {
							E(L('_ERROR_QUERY_EXPRESS_') . ':[' . $key . '=>' . $val . ']');
						}

						unset($options['where'][$key]);
					}
				}
			}
		}

		$this->options = array();
		$this->_options_filter($options);
		return $options;
	}

	protected function _options_filter(&$options)
	{
	}

	public function join($join, $type = 'INNER')
	{
		$prefix = $this->tablePrefix;

		if (is_array($join)) {
			foreach ($join as $key => &$_join) {
				$_join = preg_replace_callback('/__([A-Z0-9_-]+)__/sU', function($match) use($prefix) {
					return $prefix . strtolower($match[1]);
				}, $_join);
				$_join = (false !== stripos($_join, 'JOIN') ? $_join : $type . ' JOIN ' . $_join);
			}

			$this->options['join'] = $join;
		}
		else {
			if (!empty($join)) {
				$join = preg_replace_callback('/__([A-Z0-9_-]+)__/sU', function($match) use($prefix) {
					return $prefix . strtolower($match[1]);
				}, $join);
				$this->options['join'][] = false !== stripos($join, 'JOIN') ? $join : $type . ' JOIN ' . $join;
			}
		}

		return $this;
	}

	public function select($options = array())
	{
		$pk = $this->getPk();
		if (is_string($options) || is_numeric($options)) {
			if (strpos($options, ',')) {
				$where[$pk] = array('IN', $options);
			}
			else {
				$where[$pk] = $options;
			}

			$options = array();
			$options['where'] = $where;
		}
		else {
			if (is_array($options) && (0 < count($options)) && is_array($pk)) {
				$count = 0;

				foreach (array_keys($options) as $key) {
					if (is_int($key)) {
						++$count;
					}
				}

				if ($count == count($pk)) {
					$i = 0;

					foreach ($pk as $field) {
						$where[$field] = $options[$i];
						unset($options[$i++]);
					}

					$options['where'] = $where;
				}
				else {
					return false;
				}
			}
			else {
				if (false === $options) {
					$options['fetch_sql'] = true;
				}
			}
		}

		$options = $this->_parseOptions($options);

		if (isset($options['cache'])) {
			$cache = $options['cache'];
			$key = (is_string($cache['key']) ? $cache['key'] : md5(serialize($options)));
			$data = S($key, '', $cache);

			if (false !== $data) {
				return $data;
			}
		}

		$resultSet = $this->db->select($options);

		if (false === $resultSet) {
			return false;
		}

		if (!empty($resultSet)) {
			if (is_string($resultSet)) {
				return $resultSet;
			}

			$resultSet = array_map(array($this, '_read_data'), $resultSet);
			$this->_after_select($resultSet, $options);

			if (isset($options['index'])) {
				$index = explode(',', $options['index']);

				foreach ($resultSet as $result) {
					$_key = $result[$index[0]];
					if (isset($index[1]) && isset($result[$index[1]])) {
						$cols[$_key] = $result[$index[1]];
					}
					else {
						$cols[$_key] = $result;
					}
				}

				$resultSet = $cols;
			}
		}

		if (isset($cache)) {
			S($key, $resultSet, $cache);
		}

		return $resultSet;
	}

	protected function _after_select(&$resultSet, $options)
	{
	}

	public function find($options = array())
	{
		if (is_numeric($options) || is_string($options)) {
			$where[$this->getPk()] = $options;
			$options = array();
			$options['where'] = $where;
		}

		$pk = $this->getPk();
		if (is_array($options) && (0 < count($options)) && is_array($pk)) {
			$count = 0;

			foreach (array_keys($options) as $key) {
				if (is_int($key)) {
					++$count;
				}
			}

			if ($count == count($pk)) {
				$i = 0;

				foreach ($pk as $field) {
					$where[$field] = $options[$i];
					unset($options[$i++]);
				}

				$options['where'] = $where;
			}
			else {
				return false;
			}
		}

		$options['limit'] = 1;
		$options = $this->_parseOptions($options);

		if (isset($options['cache'])) {
			$cache = $options['cache'];
			$key = (is_string($cache['key']) ? $cache['key'] : md5(serialize($options)));
			$data = S($key, '', $cache);

			if (false !== $data) {
				$this->data = $data;
				return $data;
			}
		}

		$resultSet = $this->db->select($options);

		if (false === $resultSet) {
			return false;
		}

		if (empty($resultSet)) {
			return null;
		}

		if (is_string($resultSet)) {
			return $resultSet;
		}

		$data = $this->_read_data($resultSet[0]);
		$this->_after_find($data, $options);

		if (!empty($this->options['result'])) {
			return $this->returnResult($data, $this->options['result']);
		}

		$this->data = $data;

		if (isset($cache)) {
			S($key, $data, $cache);
		}

		return $this->data;
	}

	protected function _after_find(&$result, $options)
	{
	}

	protected function returnResult($data, $type = '')
	{
		if ($type) {
			if (is_callable($type)) {
				return call_user_func($type, $data);
			}

			switch (strtolower($type)) {
			case 'json':
				return json_encode($data);
			case 'xml':
				return xml_encode($data);
			}
		}

		return $data;
	}

	protected function _read_data($data)
	{
		if (!empty($this->_map) && C('READ_DATA_MAP')) {
			foreach ($this->_map as $key => $val) {
				if (isset($data[$val])) {
					$data[$key] = $data[$val];
					unset($data[$val]);
				}
			}
		}

		return $data;
	}

	public function limit($offset, $length = NULL)
	{
		if (is_null($length) && strpos($offset, ',')) {
			list($offset, $length) = explode(',', $offset);
		}

		$this->options['limit'] = intval($offset) . ($length ? ',' . intval($length) : '');
		return $this;
	}

	protected function _facade($data)
	{
		if (!empty($this->fields)) {
			if (!empty($this->options['field'])) {
				$fields = $this->options['field'];
				unset($this->options['field']);

				if (is_string($fields)) {
					$fields = explode(',', $fields);
				}
			}
			else {
				$fields = $this->fields;
			}

			foreach ($data as $key => $val) {
				if (!in_array($key, $fields, true)) {
					if (!empty($this->options['strict'])) {
						E(L('_DATA_TYPE_INVALID_') . ':[' . $key . '=>' . $val . ']');
					}

					unset($data[$key]);
				}
				else {
					if (is_scalar($val)) {
						$this->_parseType($data, $key);
					}
				}
			}
		}

		if (!empty($this->options['filter'])) {
			$data = array_map($this->options['filter'], $data);
			unset($this->options['filter']);
		}

		$this->_before_write($data);
		return $data;
	}

	protected function _before_write(&$data)
	{
	}

	public function add($data = '', $options = array(), $replace = false)
	{
		if (empty($data)) {
			if (!empty($this->data)) {
				$data = $this->data;
				$this->data = array();
			}
			else {
				$this->error = L('_DATA_TYPE_INVALID_');
				return false;
			}
		}

		$data = $this->_facade($data);
		$options = $this->_parseOptions($options);

		if (false === $this->_before_insert($data, $options)) {
			return false;
		}

		$result = $this->db->insert($data, $options, $replace);
		if ((false !== $result) && is_numeric($result)) {
			$pk = $this->getPk();

			if (is_array($pk)) {
				return $result;
			}

			$insertId = $this->getLastInsID();

			if ($insertId) {
				$data[$pk] = $insertId;

				if (false === $this->_after_insert($data, $options)) {
					return false;
				}

				return $insertId;
			}

			if (false === $this->_after_insert($data, $options)) {
				return false;
			}
		}

		return $result;
	}

	protected function _before_insert(&$data, $options)
	{
	}

	protected function _after_insert($data, $options)
	{
	}

	public function addAll($dataList, $options = array(), $replace = false)
	{
		if (empty($dataList)) {
			$this->error = L('_DATA_TYPE_INVALID_');
			return false;
		}

		foreach ($dataList as $key => $data) {
			$dataList[$key] = $this->_facade($data);
		}

		$options = $this->_parseOptions($options);
		$result = $this->db->insertAll($dataList, $options, $replace);

		if (false !== $result) {
			$insertId = $this->getLastInsID();

			if ($insertId) {
				return $insertId;
			}
		}

		return $result;
	}

	public function selectAdd($fields = '', $table = '', $options = array())
	{
		$options = $this->_parseOptions($options);

		if (false === ($result = $this->db->selectInsert($fields ?: $options['field'], $table ?: $this->getTableName(), $options))) {
			$this->error = L('_OPERATION_WRONG_');
			return false;
		}

		return $result;
	}

	public function save($data = '', $options = array())
	{
		if (empty($data)) {
			if (!empty($this->data)) {
				$data = $this->data;
				$this->data = array();
			}
			else {
				$this->error = L('_DATA_TYPE_INVALID_');
				return false;
			}
		}

		$data = $this->_facade($data);

		if (empty($data)) {
			$this->error = L('_DATA_TYPE_INVALID_');
			return false;
		}

		$options = $this->_parseOptions($options);
		$pk = $this->getPk();

		if (!isset($options['where'])) {
			if (is_string($pk) && isset($data[$pk])) {
				$where[$pk] = $data[$pk];
				unset($data[$pk]);
			}
			else {
				if (is_array($pk)) {
					foreach ($pk as $field) {
						if (isset($data[$field])) {
							$where[$field] = $data[$field];
						}
						else {
							$this->error = L('_OPERATION_WRONG_');
							return false;
						}

						unset($data[$field]);
					}
				}
			}

			if (!isset($where)) {
				$this->error = L('_OPERATION_WRONG_');
				return false;
			}

			$options['where'] = $where;
		}

		if (is_array($options['where']) && isset($options['where'][$pk])) {
			$pkValue = $options['where'][$pk];
		}

		if (false === $this->_before_update($data, $options)) {
			return false;
		}

		$result = $this->db->update($data, $options);
		if ((false !== $result) && is_numeric($result)) {
			if (isset($pkValue)) {
				$data[$pk] = $pkValue;
			}

			$this->_after_update($data, $options);
		}

		return $result;
	}

	protected function _before_update(&$data, $options)
	{
	}

	protected function _after_update($data, $options)
	{
	}

	public function delete($options = array())
	{
		$pk = $this->getPk();
		if (empty($options) && empty($this->options['where'])) {
			if (!empty($this->data) && isset($this->data[$pk])) {
				return $this->delete($this->data[$pk]);
			}

			return false;
		}

		if (is_numeric($options) || is_string($options)) {
			if (strpos($options, ',')) {
				$where[$pk] = array('IN', $options);
			}
			else {
				$where[$pk] = $options;
			}

			$options = array();
			$options['where'] = $where;
		}

		if (is_array($options) && (0 < count($options)) && is_array($pk)) {
			$count = 0;

			foreach (array_keys($options) as $key) {
				if (is_int($key)) {
					++$count;
				}
			}

			if ($count == count($pk)) {
				$i = 0;

				foreach ($pk as $field) {
					$where[$field] = $options[$i];
					unset($options[$i++]);
				}

				$options['where'] = $where;
			}
			else {
				return false;
			}
		}

		$options = $this->_parseOptions($options);

		if (empty($options['where'])) {
			return false;
		}

		if (is_array($options['where']) && isset($options['where'][$pk])) {
			$pkValue = $options['where'][$pk];
		}

		if (false === $this->_before_delete($options)) {
			return false;
		}

		$result = $this->db->delete($options);
		if ((false !== $result) && is_numeric($result)) {
			$data = array();

			if (isset($pkValue)) {
				$data[$pk] = $pkValue;
			}

			$this->_after_delete($data, $options);
		}

		return $result;
	}

	protected function _before_delete($options)
	{
	}

	protected function _after_delete($data, $options)
	{
	}
}


?>
