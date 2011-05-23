<?php
abstract class Skaya_Model_DbTable_Abstract extends Zend_Db_Table_Abstract {
	
	protected $_rowClass = 'Skaya_Model_Row';
	protected $_rowsetClass = 'Skaya_Model_Rowset';
	/**
	* Filters array with only rows presented in current table
	* 
	* @param array $data Array with data to save
	* @return boolean
	*/
	public function filterDataByRowsNames($data) {
		$_cols = $this->info(self::COLS);
		return (!empty($_cols))?array_intersect_key($data, array_flip($_cols)):array();
	}

	/**
	* @return Zend_Db_Table_Rowset
	*/
	public function fetchAllBy($key, $value = null, $order = null, $count = null, $offset = null) {
		$where = $this->_whereValues($key, $value);
		return $this->fetchAll($where, $order, $count, $offset);
	}

	/**
	* @return Zend_Db_Table_Row
	*/
	public function fetchRowBy($key, $value = null, $order = null, $count = null, $offset = null) {
		$where = $this->_whereValues($key, $value);
		return $this->fetchRow($where, $order, $count, $offset);
	}

	public function count(Zend_Db_Table_Select $select) {
		$select->reset(Zend_Db_Select::COLUMNS)->columns(array('__count' => new Zend_Db_Expr('COUNT(*)')));
		$result = $this->_fetch($select);
		return (count($result) > 0)?(int)$result[0]['__count']:0;
	}

	public function countBy($key, $value = null) {
		$where = $this->_whereValues($key, $value);
		$select = $this->select(self::SELECT_WITH_FROM_PART);
		foreach ((array)$where as $value) {
			$select->where($value);
		}
		return $this->count($select);
	}

	/**
	* Deletes row by key-value pair
	*
	* @param mixed $key
	* @param mixed $value
	* @return int          The number of rows deleted.
	*/
	public function deleteBy($key, $value = null) {
		$where = $this->_whereValues($key, $value);
		return $this->delete($where);
	}

	public function __call($name, $arguments) {
		$actionName = '';
		foreach (array('fetchRowBy', 'fetchAllBy', 'deleteBy', 'countBy') as $_a) {
			if (strpos($name, $_a) === 0) {
				$actionName = $_a;
				break;
			}
		}

		if (empty($actionName)) throw new Skaya_Model_DbTable_Exception("Undefined method $name");
		$fetchField = substr($name, strlen($actionName));
		$arguments = $this->_splitCallArguments($fetchField, $arguments);
		return call_user_func_array(array($this, $actionName), $arguments);
	}

	/**
	* Return necessary table
	*
	* @param string $name
	* @return Skaya_Model_DbTable_Abstract
	*/
	public static function factory($name) {
		$className = $name;
		if (strpos($name, '_') === false) {
			$className = 'Model_DbTable_'.ucfirst(Zend_Filter::filterStatic($name, 'Word_UnderscoreToCamelCase'));
		}
		if (!class_exists($className, true)) {
			throw new Skaya_Model_DbTable_Exception("Table class $className not found");
		}
		return new $className();
	}

	protected function _whereValues($key, $value = null) {
		if ($value === null && is_array($key)) {
			$where = array();
			foreach ($key as $k) {
				list($k, $v) = array_values($k);
				$where[] = $this->getAdapter()->quoteInto("$k=?", $v);
			}
		}
		else {
			$key = $this->getAdapter()->quoteIdentifier($key);
			$where = $this->getAdapter()->quoteInto("$key=?", $value);
		}
		return $where;
	}

	protected function _splitCallArguments($fetchField, $arguments) {
		$fetchFields = explode('And', $fetchField);
		if (count($fetchFields) == 1) {
			array_unshift($arguments, strtolower(Zend_Filter::filterStatic($fetchField, 'Word_CamelCaseToUnderscore')));
		}
		else {
			$args = array_values($arguments);
			$arguments = array();
			for ($i=0;$i<count($fetchFields);$i++) {
				if (array_key_exists($i, $args)) array_push($arguments, array(
					strtolower(Zend_Filter::filterStatic($fetchFields[$i], 'Word_CamelCaseToUnderscore')),
					$args[$i]
				));
			}
			$arguments = array($arguments);
		}
		return $arguments;
	}
}
?>
