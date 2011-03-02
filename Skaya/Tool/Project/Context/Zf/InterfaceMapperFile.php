<?php
class Skaya_Tool_Project_Context_Zf_InterfaceMapperFile extends Skaya_Tool_Project_Context_Zf_AbstractMapperFile {
	
	protected $_mapperName = 'Interface';

	protected $_type = null;
	
	public function getName() {
		return 'interfaceMapperFile';
	}

	public function getContents() {
		$type = '';
		if ($this->_type) {
			$type = ucfirst($this->_type);
		}
		$srcPath = dirname(__FILE__) . '/src/ModelMapper' . $type . 'Interface.src';
		if (!file_exists($srcPath) || !is_readable($srcPath)) {
			throw new Zend_Tool_Project_Context_Exception('Source of the mapper Interface was not found');
		}
		return file_get_contents($srcPath);
	}


}