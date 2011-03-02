<?php
class Skaya_Tool_Project_Context_Zf_InterfaceModelFile extends Skaya_Tool_Project_Context_Zf_ModelFile {
	
	protected $_modelName = 'Interface';
	
	public function getName() {
		return 'InterfaceModelFile';
	}

	public function getContents() {
		$srcPath = dirname(__FILE__) . '/src/ModelInterface.src';
		if (!file_exists($srcPath) || !is_readable($srcPath)) {
			throw new Zend_Tool_Project_Context_Exception('Source of the Interface was not found');
		}
		return file_get_contents($srcPath);
	}


}