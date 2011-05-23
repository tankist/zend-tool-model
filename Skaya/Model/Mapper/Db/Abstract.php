<?php
abstract class Skaya_Model_Mapper_Db_Abstract extends Skaya_Model_Mapper_Abstract implements Skaya_Model_Mapper_Db_Interface {
	
	protected static $_tables = array();
	
	protected $_mapperTableName = '';

	protected $_provider = 'Db';
	
	/**
	* Creates (if necessary) and returns table class instance
	* 
	* @param string $name
	* @return Skaya_Model_DbTable_Abstract
	* @throws Skaya_Model_Mapper_Db_Exception
	*/
	protected static function _getTableByName($name) {
		$name = ucfirst($name);
		if (!array_key_exists($name, self::$_tables)) {
			self::$_tables[$name] = Skaya_Model_DbTable_Abstract::factory($name);
		}
		return self::$_tables[$name];
	}
	
	/**
	* Save data to the DB store
	* 
	* @param array $data
	* @return int
	*/
	public function save($data) {
		$unmappedData = $this->unmap($data);
		$row = $this->_findOrCreateRowByData($unmappedData);
		$row->save();
		return $this->getMappedArrayFromData($row);
	}
	
	/**
	* Delete row linked to data
	* 
	* @param array $data
	* @return int
	*/
	public function delete($data) {
		$row = $this->_findOrCreateRowByData($data);
		return $row->delete();
	}
	
	/**
	* Search information by specially formed search condition
	* 
	* @example 'products.name%test&products.price>10&stores.state=active&partners.id=1'
	* 
	* @param string $conditions
	* @param mixed $order
	* @param int $count
	* @param int $offset
	* @return array
	*/
	public function search($conditions, $order = null, $count = null, $offset = null) {
		$select = $this->_prepareSearchQuery($conditions, $order, $count, $offset);
		$searchResult = self::_getTableByName($this->_mapperTableName)->fetchAll($select);
		return $this->getMappedArrayFromData($searchResult);
	}
	
	/**
	* Search information by specially formed search condition and return results as paginator object
	* 
	* @param string $conditions
	* @param mixed $order
	* @return Skaya_Paginator
	*/
	public function getSearchPaginator($conditions, $order = null) {
		$select = $this->_prepareSearchQuery($conditions, $order);
		$paginator = Skaya_Paginator::factory($select, 'DbSelect');
		$paginator->addFilter(new Zend_Filter_Callback(array(
			'callback' => array($this, 'getMappedArrayFromData')
		)));
		return $paginator;
	}
	
	public function getRawArrayFromData($data) {
		if ($data instanceOf Zend_Db_Table_Row_Abstract ||
		    $data instanceOf Zend_Db_Table_Rowset_Abstract) {
			return $data->toArray();
		}
		
		return parent::getRawArrayFromData($data);
	}
	
	/**
	* Convert search condition string to array with search terms and tables defined
	* 
	* @param string $conditions
	* @return array
	*/
	protected function _parseSearchConditions($conditions) {
		$subConditionsTerms = $quotedTerms = array();
		if (strpos($conditions, '[') !== false) {
			//Parse brackets
			$bracketsPartsCount = preg_match_all('$\[(.+?)\]$i', $conditions, $subConditions);
			if ($bracketsPartsCount > 0) {
				$subConditions = $subConditions[1];
				$subConditionIndex = 0;
				foreach ($subConditions as $subCondition) {
					$subConditionsTerms['subcondition_' . ++$subConditionIndex] = $this->_parseSearchConditions($subCondition);
					$conditions = str_replace('[' . $subCondition . ']', 'subcondition=subcondition_' . $subConditionIndex, $conditions);
				}
			}
		}
		if (strpos($conditions, "'") !== false) {
			//Parse quoted parts
			$quotedPartsCount = preg_match_all('$\'([^\']+)\'$i', $conditions, $quotedParts);
			if ($quotedPartsCount > 0) {
				$quotedParts = $quotedParts[1];
				$quotedPartIndex = 0;
				foreach ($quotedParts as $_qPart) {
					$quotedTerms['__quotedStr_' . ++$quotedPartIndex] = $_qPart;
					$conditions = str_replace("'" . $_qPart . "'", '__quotedStr_' . $quotedPartIndex, $conditions);
				}
			}
		}
		$partsCount = preg_match_all('$(?<connector>[\?&\|]*)(?<field>[^&=><%\|]+)(?<operation>[=><%]{1,2})(?<value>[^&=><%\|]+)$i', $conditions, $parts);
		$searchTerms = $tables = array();
		for ($i=0;$i<$partsCount;$i++) {
			$table = '';
			$field = $parts['field'][$i];
			if ($field == 'subcondition' && array_key_exists($parts['value'][$i], $subConditionsTerms)) {
				$subCondition = $subConditionsTerms[$parts['value'][$i]];
				$tables = array_merge($tables, $subCondition['tables']);
				$searchTerms[] = array(
					'subcondition' => $subCondition['terms'],
					'connector' => $parts['connector'][$i]
				);
				continue;
			}
			if (strpos($parts['field'][$i], '.') !== false) {
				list($table, $field) = explode('.', $parts['field'][$i]);
				$tables[] = $table;
			}
			
			$value = $parts['value'][$i];
			if (array_key_exists($value, $quotedTerms)) {
				$value = $quotedTerms[$value];
			}

			$searchTerms[] = array(
				'table' => $table,
				'field' => $field,
				'operation' => $parts['operation'][$i],
				'value' => $value,
				'connector' => $parts['connector'][$i]
			);
		}
		return array('terms' => $searchTerms, 'tables' => array_unique($tables));
	}
	
	/**
	* Converts formed search terms array to Zend_Db_Table_Select object
	* 
	* @param string $conditions
	* @param mixed $order
	* @param int $count
	* @param int $offset
	* @return Zend_Db_Table_Select
	*/
	protected function _prepareSearchQuery($conditions, $order = null, $count = null, $offset = null) {
		$mainTable = self::_getTableByName($this->_mapperTableName);
		$select = $mainTable->select(false)
			->from(array($this->_mapperTableName => $mainTable->info(Skaya_Model_DbTable_Abstract::NAME)));
		$terms = $this->_parseSearchConditions($conditions);
		
		$tables = array($this->_mapperTableName => $mainTable);
		if (!empty($terms['tables'])) {
			foreach ($terms['tables'] as $tableName) {
				if (!array_key_exists($tableName, $tables)) {
					try {
						/**
						* @var Zend_Db_Table
						*/
						$tableInstance = $tables[$tableName] = self::_getTableByName($tableName);
						$fullTableName = $tableInstance->info(Skaya_Model_DbTable_Abstract::NAME);
						$tableClass = get_class($tableInstance);
						$_tables = array_values($tables);
						$_tablesAliases = array_keys($tables);
						$tablesCount = count($tables);
						$reference = array();
						for ($i=0;$i<$tablesCount;$i++) {
							/**
							* @var Zend_Db_Table
							*/
							$referenceTable = $_tables[$i];
							$referenceTableClass = get_class($referenceTable);
							$referenceRules = array();
							
							try {
								
								$reference = $tableInstance->getReference($referenceTableClass);
								$reference['table'] = $tableInstance;
								$reference['tableAlias'] = $tableName;
								$reference['referenceTable'] = $referenceTable;
								$reference['referenceTableAlias'] = $_tablesAliases[$i];
								
								$this->_joinTable($select, $reference);
								break;
							}
							catch (Exception $e) {}
							try {
								$reference = $referenceTable->getReference($tableClass);
								$reference['table'] = $referenceTable;
								$reference['tableAlias'] = $_tablesAliases[$i];
								$reference['referenceTable'] = $tableInstance;
								$reference['referenceTableAlias'] = $tableName;
								
								$this->_joinTable($select, $reference);
								break;
							}
							catch (Exception $e) {}
							try {
								//Trying to find many-to-many reference
								$commonDependentTable = array_shift(array_intersect(
									$tableInstance->getDependentTables(), 
									$referenceTable->getDependentTables()
								));
								if (!empty($commonDependentTable)) {
									$commonDependentTableInstance = self::_getTableByName($commonDependentTable);
									
									$reference = $commonDependentTableInstance->getReference($referenceTableClass);
									$reference['table'] = $commonDependentTableInstance;
									$reference['tableAlias'] = strtolower($commonDependentTable);
									$reference['referenceTable'] = $referenceTable;
									$reference['referenceTableAlias'] = $_tablesAliases[$i];
									
									$this->_joinTable($select, $reference);
									
									$reference = $commonDependentTableInstance->getReference($tableClass);
									$reference['table'] = $commonDependentTableInstance;
									$reference['tableAlias'] = strtolower($commonDependentTable);
									$reference['referenceTable'] = $tableInstance;
									$reference['referenceTableAlias'] = $tableName;
									
									$this->_joinTable($select, $reference);
									break;
								}
							}
							catch (Exception $e) {}
						}
					}
					catch (Exception $e) {
						continue;
					}
				}
			}
		}
		//Set where
		if (!empty($terms['terms'])) {
			foreach ($terms['terms'] as $term) {
				$whereCondition = '';
				$adapter = $select->getAdapter();
				$whereFunction = ($term['connector'] == '|')?'orWhere':'where';
				if (array_key_exists('subcondition', $term) && is_array($term['subcondition'])) {
					$conditions = array();
					foreach ($term['subcondition'] as $subCondition) {
						list($fieldName, $value) = each($this->unmap(array(
							$subCondition['field'] => $subCondition['value']
						)));
						if (!empty($fieldName)) {
							$fieldName = $adapter->quoteIdentifier(array($subCondition['table'], $fieldName));
							$orAnd = ($subCondition['connector'] == '|')?' OR ':' AND ';
							if (empty($conditions)) {
								$orAnd = '';
							}
							$operation = $subCondition['operation'];
							$placeholder = '?';
							if ($operation == '%') {
								$operation = 'LIKE';
								$value = "%$value%";
							}
							if (strpos($value, '(') !== false) {
								$value = new Zend_Db_Expr($value);
							}
							$conditions[] = $orAnd . $adapter->quoteInto(sprintf("%s %s %s", $fieldName, $operation, $placeholder), $value);
						}
					}
					$condition = join('', $conditions);
				}
				else {
					list($fieldName, $value) = each($this->unmap(array(
						$term['field'] => $term['value']
					)));
					if (!empty($fieldName)) {
						if (strpos($fieldName, '(') !== false) {
							$fieldName = new Zend_Db_Expr($fieldName);
						} else {
							$fieldName = $adapter->quoteIdentifier(array($term['table'], $fieldName));
						}
						$operation = $term['operation'];
						$placeholder = '?';
						if ($operation == '%') {
							$operation = 'LIKE';
							$value = "%$value%";
						}
						if (strpos($value, '(') !== false) {
							$value = new Zend_Db_Expr($value);
						}
						
						$condition = $adapter->quoteInto(sprintf("%s %s %s", $fieldName, $operation, $placeholder), $value);
					}
				}
				
				$select->$whereFunction($condition);
			}
		}
		$select->order($this->_mapOrderStatement($order));
		$select->limit($count, $offset);
		return $select;
	}
	
	protected function _mapOrderStatement($order = array()) {
		$_tmpOrderArray = array();
		foreach ((array)$order as $key => $value) {
			if (is_numeric($key)) {
				if (preg_match('/(.*\W)(' . Zend_Db_Select::SQL_ASC . '|' . Zend_Db_Select::SQL_DESC . ')\b/si', $order, $matches)) {
					$value = trim($matches[1]);
					$direction = $matches[2];
				}
				$value = key($this->unmap(array($value => '')));
			}
			else {
				list($value, $direction) = each($this->unmap(array($key => $value)));
			}
			if (empty($direction)) {
				$direction = Zend_Db_Select::SQL_ASC;
			}
			if (!empty($value)) {
				$value .= ' ' . $direction;
				$_tmpOrderArray[] = $value;
			}
		}
		return $_tmpOrderArray;
	}

    protected function _mapDateTimeField($value) {
        if ($value instanceof Zend_Date) {
            return new Zend_Db_Expr('FROM_UNIXTIME(' . $value->getTimestamp() . ')');
        }
        if (is_int($value)) {
            return new Zend_Db_Expr('FROM_UNIXTIME(' . $value . ')');
        }
        return $value;
    }
	
	protected function _joinTable(Zend_Db_Table_Select $select, array $reference) {
		$definitions = array();
		for ($i=0;$i<count($reference[Skaya_Model_DbTable_Abstract::COLUMNS]);$i++) {
			$tableColumn = $reference['table']->getAdapter()
				->quoteIdentifier($reference[Skaya_Model_DbTable_Abstract::COLUMNS][$i]);
			$referenceTableColumn = $reference['referenceTable']->getAdapter()
				->quoteIdentifier($reference[Skaya_Model_DbTable_Abstract::REF_COLUMNS][$i]);
			$definitions[] = $reference['tableAlias'].".$tableColumn = {$reference['referenceTableAlias']}.$referenceTableColumn";
		}
		/**
		 * @var Zend_Db_Table_Abstract $table
		 */
		$table = $reference['table'];
		$tableAlias = $reference['tableAlias'];
		if (array_key_exists($tableAlias, $select->getPart(Zend_Db_Table_Select::FROM))) {
			//Prevent double joining
			$table = $reference['referenceTable'];
			$tableAlias = $reference['referenceTableAlias'];
		}
		$select
			->setIntegrityCheck(false)
			->joinInner(
				array($tableAlias => $table->info(Skaya_Model_DbTable_Abstract::NAME)), 
				join(' AND ', $definitions), 
				array()
			);
	}
	
	/**
	* Try to find data identified by PK. If nothing was found tries to create new filtered data
	* 
	* @param array $data
	* @return Zend_Db_Table_Row_Abstract
	*/
	protected function _findOrCreateRowByData($data) {
		$table = self::_getTableByName($this->_mapperTableName);
		$data = $table->filterDataByRowsNames($data);
		
		$primary = $table->info(Skaya_Model_DbTable_Abstract::PRIMARY);
		$primaryValues = array_filter(array_intersect_key($data, array_flip($primary)));
		if (count($primaryValues) != count($primary)) {
			$row = $table->createRow($data);
		}
		else {
			/**
			 * @var Zend_Db_Table_Rowset_Abstract $rowSet
			 */
			$rowSet = call_user_func_array(array($table, 'find'), $primaryValues);
			if ($rowSet instanceOf Zend_Db_Table_Rowset_Abstract && count($rowSet->toArray())>0 ) {
				$row = $rowSet->current();
				$row->setFromArray($data);
			}
			else {
				$row = $table->createRow($data);
			}
		}
		return $row;
	}
}
?>
