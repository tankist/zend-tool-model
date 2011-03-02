<?php
class Skaya_Tool_Project_Context_Zf_ExceptionModelFile extends Skaya_Tool_Project_Context_Zf_ModelFile {
	
	protected $_modelName = 'Exception';
	
	public function getName() {
		return 'ExceptionModelFile';
	}

	public function getContents() {
		$srcPath = dirname(__FILE__) . '/src/ModelException.src';
		if (!file_exists($srcPath) || !is_readable($srcPath)) {
			throw new Zend_Tool_Project_Context_Exception('Source of the Exception Model class was not found');
		}
		return file_get_contents($srcPath);
	}


}