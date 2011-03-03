<?php
class Skaya_Model_Row extends Zend_Db_Table_Row {
	
	protected $_dataTypes = array(
		'bit' => 'int',
		'tinyint' => 'int',
		'bool' => 'bool',
		'boolean' => 'bool',
		'smallint' => 'int',
		'mediumint' => 'int',
		'int' => 'int',
		'integer' => 'int',
		'bigint' => 'float',
		'serial' => 'int',
		'float' => 'float',
		'real' => 'float',
		'numeric' => 'float',
		'money' => 'float',
		'double' => 'float',
		'double precision' => 'float',
		'double unsigned' => 'float',
		'decimal' => 'float',
		'dec' => 'float',
		'fixed' => 'float',
		'year' => 'int'
	);
	
	protected $_colTypes = array();
	
	/**
	 * Initialize object
	 *
	 * Called from {@link __construct()} as final step of object instantiation.
	 *
	 * @return void
	 */
	public function init() {
		parent::init();
		
		$table = $this->getTable();
		if ($table) {
			$cols = $table->info(Zend_Db_Table_Abstract::METADATA);
			
			$dataTypeFilter = new Zend_Filter();
			$dataTypeFilter
                    ->addFilter(new Zend_Filter_StringToLower())
                    ->addFilter(new Zend_Filter_PregReplace('$\(.*?\)$', ''))
                    ->addFilter(new Zend_Filter_StringTrim());
			foreach ($cols as $name => $col) {
				$dataType = $dataTypeFilter->filter($col['DATA_TYPE']);
				if (array_key_exists($dataType, $this->_dataTypes)) {
					if ($col['NULLABLE'] && $this->_data[$name] == null) {
						continue;
					}
					settype($this->_data[$name], $this->_dataTypes[$dataType]);
				}
			}
			foreach ($this->_colTypes as $name => $type) {
				if (array_key_exists($name, $this->_data)) {
					settype($this->_data[$name], $type);
				}
			}
		}
	}
}
?>