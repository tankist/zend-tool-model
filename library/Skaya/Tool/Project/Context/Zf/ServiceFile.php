<?php

class Skaya_Tool_Project_Context_Zf_ServiceFile extends Zend_Tool_Project_Context_Zf_AbstractClassFile {

	/**
	 * @var string
	 */
	protected $_serviceName = 'Base';

	/**
	 * @var string
	 */
	protected $_filesystemName = 'serviceName';

	/**
	 * init()
	 *
	 */
	public function init() {
		$this->_serviceName = $this->_resource->getAttribute('name');
		$this->_filesystemName = ucfirst($this->_serviceName) . '.php';
		parent::init();
	}

	/**
	 * getPersistentAttributes
	 *
	 * @return array
	 */
	public function getPersistentAttributes() {
		return array(
			'name' => $this->getServiceName()
		);
	}

	/**
	 * getName()
	 *
	 * @return string
	 */
	public function getName() {
		return 'serviceFile';
	}

	public function getServiceName() {
		return $this->_serviceName;
	}

	public function getContents() {

		$className = $this->getFullClassName($this->_serviceName, 'Service');

		$modelSubname = ucfirst($this->_serviceName);
		$modelName = $this->getFullClassName($modelSubname, 'Model');
		$mapperName = strtolower($this->_serviceName);

		$getItemsBody = <<<EOS
		\${$mapperName}sBlob = \$this->_mappers->{$mapperName}->get{$modelSubname}s(\$order, \$count, \$offset);
return new Model_Collection_{$modelSubname}s(\${$mapperName}sBlob);

EOS;
		$getItemsPaginatorBody = <<<EOS
		\$paginator = \$this->_mappers->{$mapperName}->get{$modelSubname}sPaginator(\$order);
\$paginator->addFilter(new Skaya_Filter_Array_Collection('Model_Collection_{$modelSubname}s'));
return \$paginator;
		
EOS;
		$getItemByIdBody = <<<EOS
		\${$mapperName}Data = \$this->_mappers->{$mapperName}->get{$modelSubname}ById(\$id);
return new $modelName(\${$mapperName}Data);
EOS;
		$createBody = <<<EOS
		if (array_key_exists('id', \$data)) {
	unset(\$data['id']);
}
return new $modelName(\$data);
EOS;

		$methods = array(
			new Zend_CodeGenerator_Php_Method(array(
				'name' => 'create',
				'static' => true,
				'parameters' => array(
					new Zend_CodeGenerator_Php_Parameter(array(
						'name' => 'data',
						'defaultValue' => array()
					))
				),
				'body' => $createBody
			)),
			new Zend_CodeGenerator_Php_Method(array(
				'name' => 'get' . $modelSubname . 'ById',
				'parameters' => array(
					new Zend_CodeGenerator_Php_Parameter(array(
						'name' => 'id'
					))
				),
				'body' => $getItemByIdBody
			)),
			new Zend_CodeGenerator_Php_Method(array(
				'name' => 'get' . $modelSubname . 's',
				'parameters' => array(
					new Zend_CodeGenerator_Php_Parameter(array(
						'name' => 'order',
						'defaultValue' => null
					)),
					new Zend_CodeGenerator_Php_Parameter(array(
						'name' => 'count',
						'defaultValue' => null
					)),
					new Zend_CodeGenerator_Php_Parameter(array(
						'name' => 'offset',
						'defaultValue' => null
					))
				),
				'body' => $getItemsBody
			)),
			new Zend_CodeGenerator_Php_Method(array(
				'name' => 'get' . $modelSubname . 'sPaginator',
				'parameters' => array(
					new Zend_CodeGenerator_Php_Parameter(array(
						'name' => 'order',
						'defaultValue' => null
					))
				),
				'body' => $getItemsPaginatorBody
			))
		);

		$codeGenFile = new Zend_CodeGenerator_Php_File(array(
			'fileName' => $this->getPath(),
			'classes' => array(
				new Zend_CodeGenerator_Php_Class(array(
					'extendedClass' => 'Skaya_Model_Service_Abstract',
					'name' => $className,
					'methods' => $methods
				))
			)
		));
		return $codeGenFile->generate();
	}


}