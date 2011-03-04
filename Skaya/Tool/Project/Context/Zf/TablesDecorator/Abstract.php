<?php
abstract class Skaya_Tool_Project_Context_Zf_TablesDecorator_Abstract {

	protected static $_foreignKeys = null;

	/**
	 * @var Zend_Filter_Interface
	 */
	protected static $_tableNameFilter = null;

	public static function setTableNameFilter(Zend_Filter_Interface $tableNameFilter) {
		self::$_tableNameFilter = new Zend_Filter();
		self::$_tableNameFilter->addFilter($tableNameFilter);
		self::$_tableNameFilter->addFilter(new Zend_Filter_Word_UnderscoreToCamelCase());
	}

	public static function getTableNameFilter() {
		if (empty(self::$_tableNameFilter)) {
			self::$_tableNameFilter = new Zend_Filter();
			self::$_tableNameFilter->addFilter(new Zend_Filter_Word_UnderscoreToCamelCase());
		}
		return self::$_tableNameFilter;
	}

	public static function getTables(Zend_Db_Adapter_Abstract $adapter) {
		return $adapter->listTables();
	}

	public static function getForeignKeysReference($tableName) {
		$d = self::$_foreignKeys['dependent'];
		$r = self::$_foreignKeys['references'];
		return array(
			'dependent' => (array_key_exists($tableName, $d))?$d[$tableName]:array(),
			'references' => (array_key_exists($tableName, $r))?$r[$tableName]:array()
		);
	}

	public static function getDecoratorClass(Zend_Db_Adapter_Abstract $adapter) {
		$adapterClassName = get_class($adapter);
		$adapterType = array_pop(explode('_', $adapterClassName));
		$adapterType = 'Skaya_Tool_Project_Context_Zf_TablesDecorator_' . ucfirst($adapterType);
		if (!class_exists($adapterType, true)) {
			throw new Zend_Tool_Project_Provider_Exception('Failed to determine type of the xTables decorator');
		}
		return $adapterType;
	}

	abstract public static function parseForeignKeys(Zend_Db_Adapter_Abstract $adapter);

	abstract public function getPrimaryKey(Zend_Db_Adapter_Abstract $adapter, $tableName);

}
