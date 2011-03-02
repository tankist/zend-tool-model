<?php
class Skaya_Tool_Project_Context_Zf_ExceptionMapperFile extends Skaya_Tool_Project_Context_Zf_MapperFile {
	
	protected $_mapperName = 'Exception';

	protected $_type = null;
	
	public function getName() {
		return 'exceptionMapperFile';
	}

	public function getContents() {
		$type = '';
		if ($this->_type) {
			$type = ucfirst($this->_type);
		}
		$srcPath = dirname(__FILE__) . '/src/ModelMapper' . $type . 'Exception.src';
		if (!file_exists($srcPath) || !is_readable($srcPath)) {
			throw new Zend_Tool_Project_Context_Exception('Source of the Exception mapper class was not found');
		}
		return file_get_contents($srcPath);
	}


}
