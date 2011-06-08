<?php

class Skaya_Tool_Project_Provider_XModel extends Zend_Tool_Project_Provider_Model {

	public function create($name, $module = null) {
		parent::create($name, $module);

		$modelSubname = ucfirst($name);
		$collectionName = $modelSubname . 's';

		$profile = $this->_loadProfile();

		/**
		 * @var Zend_Tool_Framework_Client_Response $response
		 */
		$response = $this->_registry->getResponse();

		if (!Skaya_Tool_Project_Provider_XCollection::hasResource($profile, $collectionName, $module)) {
			$collection = Skaya_Tool_Project_Provider_XCollection::createResource(
				$profile, $collectionName, $modelSubname, $module
			);
			if ($collection) {
				$collection->create();
				$response->appendContent('Creating collection "' . $collectionName . '" for the model "' . $modelSubname . '"');
			}
		}

		if (!Skaya_Tool_Project_Provider_XService::hasResource($profile, $modelSubname, $module)) {
			$service = Skaya_Tool_Project_Provider_XService::createResource(
				$profile, $modelSubname, $module
			);
			if ($service) {
				$service->create();
				$response->appendContent('Creating service "' . $modelSubname . '"');
			}
		}

		$this->_storeProfile();
	}

}
