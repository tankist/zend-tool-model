<?php
class Skaya_Tool_Project_Context_Zf_AbstractModelFile extends Skaya_Tool_Project_Context_Zf_ModelFile {
	
	protected $_modelName = 'Abstract';
	
	public function getName() {
		return 'abstractModelFile';
	}

	public function getContents() {
		$srcPath = dirname(__FILE__) . '/src/ModelAbstract.src';
		if (!file_exists($srcPath) || !is_readable($srcPath)) {
			throw new Zend_Tool_Project_Context_Exception('Source of the Abstract Model class was not found');
		}
		return file_get_contents($srcPath);
	}


}