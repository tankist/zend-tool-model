<?php

require_once 'Zend/Db/Adapter/Pdo/Mysql.php';

abstract class TestAdapter extends Zend_Db_Adapter_Pdo_Mysql {

	protected $_columns = array(
		'key' => array(
			'SCHEMA_NAME' => 'test',
			'TABLE_NAME' => 'test',
			'COLUMN_NAME' => 'key',
			'COLUMN_POSITION' => 1,
			'DEFAULT' => 0,
			'NULLABLE' => false,
			'DATA_TYPE' => 'INTEGER',
            'PRIMARY' => true
		),
		'field1' => array(
            'SCHEMA_NAME' => 'test',
			'TABLE_NAME' => 'test',
			'COLUMN_NAME' => 'field1',
			'COLUMN_POSITION' => 2,
			'DEFAULT' => '',
			'NULLABLE' => false,
			'DATA_TYPE' => 'VARCHAR',
            'LENGTH' => 100,
            'PRIMARY' => false
        ),
		'field2' => array(
            'SCHEMA_NAME' => 'test',
			'TABLE_NAME' => 'test',
			'COLUMN_NAME' => 'field2',
			'COLUMN_POSITION' => 3,
			'DEFAULT' => 0.00,
			'NULLABLE' => false,
            'PRECISION' => 2,
			'DATA_TYPE' => 'DOUBLE',
            'LENGTH' => 10,
            'PRIMARY' => false
        ),
		'field3' => array(
            'SCHEMA_NAME' => 'test',
			'TABLE_NAME' => 'test',
			'COLUMN_NAME' => 'field3',
			'COLUMN_POSITION' => 4,
			'DEFAULT' => 0,
			'NULLABLE' => false,
            'DATA_TYPE' => 'YEAR',
            'LENGTH' => 4,
            'PRIMARY' => false
        ),
		'field4' => array(
            'SCHEMA_NAME' => 'test',
			'TABLE_NAME' => 'test',
			'COLUMN_NAME' => 'field4',
			'COLUMN_POSITION' => 5,
			'DEFAULT' => '',
			'NULLABLE' => true,
            'DATA_TYPE' => 'CHAR',
            'LENGTH' => 4,
            'PRIMARY' => false
        )
	);

	public function describeTable($tableName, $schemaName = null) {
		return $this->_columns;
	}

}

require_once 'Zend/Db/Table.php';

class TestTable extends Zend_Db_Table {

	protected $_name = 'test';

	protected $_primary = 'key';

}

require_once 'Zend/Db/Table/Row.php';
require_once 'Zend/Db/Table/Rowset.php';

class TestRow extends Skaya_Model_Row {

}

class TestRowset extends Skaya_Model_Rowset {

}