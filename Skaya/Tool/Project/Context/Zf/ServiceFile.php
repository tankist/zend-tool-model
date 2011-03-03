<?php

class Skaya_Tool_Project_Context_Zf_ServiceFile extends Zend_Tool_Project_Context_Zf_AbstractClassFile {

	/**
	 * @var string
	 */
	protected $_serviceName = 'Base';

	/**
	 * @var string
	 */
	protected $_filesystemName = 'ServiceName';

	/**
	 * init()
	 *
	 */
	public function init() {
		$this->_serviceName = $this->_resource->getAttribute('serviceName');
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
			'serviceName' => $this->getServiceName()
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

		$codeGenFile = new Zend_CodeGenerator_Php_File(array(
		                                                    'fileName' => $this->getPath(),
		                                                    'classes' => array(
			                                                    new Zend_CodeGenerator_Php_Class(array(
			                                                                                          'extendedClass' => 'Skaya_Model_Service_Abstract',
			                                                                                          'name' => $className
			                                                                                     ))
		                                                    )
		                                               ));
		return $codeGenFile->generate();
	}


}