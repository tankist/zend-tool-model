<?php
require_once("Abstract.php");

class Skaya_Tool_Project_Provider_ModelCollection extends Skaya_Tool_Project_Provider_Abstract {
	
	public function create($name, $itemType = null, $module = null) {
		$profile = $this->_loadProfile(self::NO_PROFILE_THROW_EXCEPTION);
		$name = ucwords($name);
		
		if (!$itemType) {
			$itemType = $name;
			if (strrpos($itemType, 's') === strlen($itemType) - 1) {
				$itemType = substr($itemType, 0, -1);
			}
		}
		
		if (preg_match('#[_-]#', $name)) {
			throw new Zend_Tool_Project_Provider_Exception('collection names should be camel cased.');
		}
		
		if (self::hasResource($profile, $name, $module)) {
			throw new Zend_Tool_Project_Provider_Exception('This project already has a collection named ' . $name);
		}
		
		$request = $this->_registry->getRequest();
		$response = $this->_registry->getResponse();
		
		try {
			$collectionResource = self::createResource($profile, $name, $itemType, $module);
		} catch (Exception $e) {
			$response->setException($e);
			return;
		}
		
		$response->appendContent('Creating a collection at ' . $collectionResource->getContext()->getPath());
		$collectionResource->create();
		
		$this->_storeProfile();
	}
	
	/**
	 * hasResource()
	 *
	 * @param Zend_Tool_Project_Profile $profile
	 * @param string $collectionName
	 * @param string $moduleName
	 * @return Zend_Tool_Project_Profile_Resource
	 */
	public static function hasResource(Zend_Tool_Project_Profile $profile, $collectionName, $moduleName = null) {
		if (!is_string($collectionName)) {
			throw new Zend_Tool_Project_Provider_Exception('Zend_Tool_Project_Provider_collection::createResource() expects \"collectionName\" is the name of a collection resource to check for existence.');
		}

		$collectionsDirectory = self::_getCollectionsDirectoryResource($profile, $moduleName);
		return ($collectionsDirectory && ($collectionsDirectory->search(array('collectionFile' => array('collectionName' => $collectionName)))) instanceof Zend_Tool_Project_Profile_Resource);
	}
	
	/**
	 * _getCollectionsDirectoryResource()
	 *
	 * @param Zend_Tool_Project_Profile $profile
	 * @param string $moduleName
	 * @return Zend_Tool_Project_Profile_Resource
	 */
	protected static function _getCollectionsDirectoryResource(Zend_Tool_Project_Profile $profile, $moduleName = null)
	{
		$profileSearchParams = array();

		if ($moduleName != null && is_string($moduleName)) {
			$profileSearchParams = array('modulesDirectory', 'moduleDirectory' => array('moduleName' => $moduleName));
		}

		$profileSearchParams[] = 'collectionsDirectory';

		return $profile->search($profileSearchParams);
	}
	
	/**
	 * _getModelsDirectoryResource()
	 *
	 * @param Zend_Tool_Project_Profile $profile
	 * @param string $moduleName
	 * @return Zend_Tool_Project_Profile_Resource
	 */
	protected static function _getModelsDirectoryResource(Zend_Tool_Project_Profile $profile, $moduleName = null) {
		$profileSearchParams = array();

		if ($moduleName != null && is_string($moduleName)) {
			$profileSearchParams = array('modulesDirectory', 'moduleDirectory' => array('moduleName' => $moduleName));
		}

		$profileSearchParams[] = 'modelsDirectory';

		return $profile->search($profileSearchParams);
	}
	
	public static function createResource(Zend_Tool_Project_Profile $profile, $collectionName, $itemType, $moduleName = null)
	{
		if (!is_string($collectionName)) {
			throw new Zend_Tool_Project_Provider_Exception('Zend_Tool_Project_Provider_collection::createResource() expects \"collectionName\" is the name of a collection resource to create.');
		}
		
		if (!is_string($itemType)) {
			throw new Zend_Tool_Project_Provider_Exception('Zend_Tool_Project_Provider_collection::createResource() expects \"itemType\" is the name of a model resource that collection will be belong to.');
		}

		if (!($collectionsDirectory = self::_getCollectionsDirectoryResource($profile, $moduleName))) {
			$modelsDirectory = self::_getModelsDirectoryResource($profile, $moduleName);
			$collectionsDirectory = $modelsDirectory->createResource('collectionsDirectory');
		}

		$newCollection = $collectionsDirectory->createResource(
			'collectionFile',
			array('collectionName' => $collectionName, 'itemType' => $itemType, 'moduleName' => $moduleName)
			);

		return $newCollection;
	}
	
}
?>
