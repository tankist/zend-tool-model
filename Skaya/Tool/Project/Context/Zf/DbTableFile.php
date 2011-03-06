<?php

class Skaya_Tool_Project_Context_Zf_DbTableFile extends Zend_Tool_Project_Context_Zf_AbstractClassFile {

	protected $_adapter = null;

	protected $_dbTableName;

	protected $_defaultNameFilter = null;

	public function init() {
		if ($this->_resource->hasAttribute('dbAdapter')) {
			$this->setAdapter($this->_resource->getAttribute('dbAdapter'));
		}
		if ($this->getAdapter() instanceof Zend_Db_Adapter_Abstract) {
			$decorator = Skaya_Tool_Project_Context_Zf_TablesDecorator_Abstract::getDecoratorClass($this->getAdapter());
			$this->_defaultNameFilter = call_user_func(array($decorator, 'getTableNameFilter'));
		}
		$this->setDbTableName($this->_resource->getAttribute('dbTableName'));
		return parent::init();
	}

	/**
	 * getName()
	 *
	 * @return string
	 */
	public function getName() {
		return 'dbTableFile';
	}

	public function getPersistentAttributes() {
		$persistent = array('dbTableName' => $this->getDbTableName());
		return $persistent;
	}

	public function getDefaultNamespace() {
		return 'Model_DbTable_';
	}

	public function setDbTableName($dbTableName) {
		$this->_dbTableName = $dbTableName;
		$filesystemName = $dbTableName . '.php';
		if ($filter = $this->getDefaultNameFilter() && $filter instanceof Zend_Filter_Interface) {
			$filesystemName = $filter->filter($dbTableName) . '.php';
		}
		$this->_filesystemName = $filesystemName;
	}

	public function setDefaultNameFilter(Zend_Filter_Interface $defaultNameFilter) {
		$this->_defaultNameFilter = $defaultNameFilter;
	}

	public function getDefaultNameFilter() {
		return $this->_defaultNameFilter;
	}

	public function getDbTableName() {
		return $this->_dbTableName;
	}

	public function setAdapter(Zend_Db_Adapter_Abstract $adapter) {
		$this->_adapter = $adapter;
	}

	public function getAdapter() {
		return $this->_adapter;
	}

	public function getContents() {
		$ns = $this->getDefaultNamespace();
		$className = $ns . $this->getDefaultNameFilter()->filter($this->getDbTableName());
		$decorator = Skaya_Tool_Project_Context_Zf_TablesDecorator_Abstract::getDecoratorClass($this->getAdapter());

		$foreignKeyReference = call_user_func(
			array($decorator, 'getForeignKeysReference'),
			$this->getDbTableName()
		);
		foreach ($foreignKeyReference['dependent'] as &$_d) {
			$_d = $ns . $this->getDefaultNameFilter()->filter($_d);
		}
		foreach ($foreignKeyReference['references'] as &$_d) {
			if (array_key_exists('refTableClass', $_d)) {
				$_d['refTableClass'] = $ns . $this->getDefaultNameFilter()->filter($_d['refTableClass']);
			}
		}

		$properties = array(
			array(
				'name' => '_name',
				'visibility' => Zend_CodeGenerator_Php_Property::VISIBILITY_PROTECTED,
				'defaultValue' => $this->getDbTableName()
			),
			array(
				'name' => '_primary',
				'visibility' => Zend_CodeGenerator_Php_Property::VISIBILITY_PROTECTED,
				'defaultValue' => call_user_func(
					array($decorator, 'getPrimaryKey'),
					$this->getAdapter(),
					$this->getDbTableName()
				)
			)
		);

		if (!empty($foreignKeyReference['dependent'])) {
			$properties[] = array(
				'name' => '_dependentTables',
				'visibility' => Zend_CodeGenerator_Php_Property::VISIBILITY_PROTECTED,
				'defaultValue' => $foreignKeyReference['dependent']
			);
		}

		if (!empty($foreignKeyReference['references'])) {
			$properties[] = array(
				'name' => '_referenceMap',
				'visibility' => 'protected',
				'defaultValue' => $foreignKeyReference['references']
			);
		}

		$codeGenFile = new Zend_CodeGenerator_Php_File(array(
			'fileName' => $this->getPath(),
			'classes' => array(
				new Zend_CodeGenerator_Php_Class(array(
					'name' => $className,
					'extendedClass' => 'Skaya_Model_DbTable_Abstract',
					'properties' => $properties
				))
			)
		));

		return $codeGenFile->generate();
	}
}
