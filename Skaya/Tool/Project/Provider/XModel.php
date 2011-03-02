<?php

class Skaya_Tool_Project_Provider_XModel extends Zend_Tool_Project_Provider_Model {

	public function create($name, $module = null) {
		parent::create($name, $module);
		
		$profile = $this->_loadProfile(self::NO_PROFILE_THROW_EXCEPTION);

		if (!($modelsDirectory = self::_getModelsDirectoryResource($profile, $module))) {
            if ($module) {
                $exceptionMessage = 'A model directory for module "' . $module . '" was not found.';
            } else {
                $exceptionMessage = 'A model directory was not found.';
            }
            throw new Zend_Tool_Project_Provider_Exception($exceptionMessage);
        }
		
		self::_createCustomModel($modelsDirectory, 'Abstract', 'abstractModelFile');
		self::_createCustomModel($modelsDirectory, 'Interface', 'interfaceModelFile');
		self::_createCustomModel($modelsDirectory, 'Exception', 'exceptionModelFile');

		$this->_storeProfile();
	}
	
	protected static function _createCustomModel(Zend_Tool_Project_Profile_Resource $modelsDirectory, $modelName, $modelResourceType = 'modelFile') {
		if (!$modelsDirectory) {
			throw new Zend_Tool_Project_Provider_Exception('models directory was not found');
		}
		$modelParams = array('modelName' => $modelName);
		$model = false;
		if (!$modelsDirectory->search(array($modelResourceType => $modelParams))) {
			$model = $modelsDirectory->createResource($modelResourceType, $modelParams);
			$model->create();
		}
		return $model;
	}

}
