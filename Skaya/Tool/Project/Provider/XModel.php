<?php

class Skaya_Tool_Project_Provider_XModel extends Zend_Tool_Project_Provider_Model {

	public function create($name, $module = null) {
		parent::create($name, $module);

		$modelSubname = ucfirst($name);
		$collectionName = $modelSubname . 's';

		$profile = $this->_loadProfile();

		$collection = Skaya_Tool_Project_Provider_XCollection::createResource(
			$profile, $collectionName, $modelSubname, $module
		);
		if ($collection) {
			$collection->create();
		}

		$service = Skaya_Tool_Project_Provider_XService::createResource(
			$profile, $modelSubname, $module
		);
		if ($service) {
			$service->create();
		}

		$this->_storeProfile();
	}

}
