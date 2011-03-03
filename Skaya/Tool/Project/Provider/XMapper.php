<?php
class Skaya_Tool_Project_Provider_XMapper extends Skaya_Tool_Project_Provider_Abstract {
	
	public function create($name, $type = null, $module = null) {
		$profile = $this->_loadProfile();
		$name = ucwords($name);
		
		if (preg_match('#[_-]#', $name)) {
			throw new Zend_Tool_Project_Provider_Exception('Mapper names should be camel cased.');
		}
		
		if (self::hasResource($profile, $name, $type, $module)) {
			throw new Zend_Tool_Project_Provider_Exception('This project already has a mapper named ' . $name);
		}
		
		$request = $this->_registry->getRequest();
		$response = $this->_registry->getResponse();
		
		try {
			$mapperResource = self::createResource($profile, $name, $type, $module);
		} catch (Exception $e) {
			$response->setException($e);
			return;
		}
		
		$response->appendContent('Creating a mapper at ' . $mapperResource->getContext()->getPath());
		$mapperResource->create();
		
		$this->_storeProfile();
	}

    public function getItem($name, $type, $entity, $module = null) {
        
    }

    public function getItems($name, $type, $module = null) {

    }

    public function getItemsPaginator($name, $type, $module = null) {
        
    }
	
	/**
	 * hasResource()
	 *
	 * @param Zend_Tool_Project_Profile $profile
	 * @param string $mapperName
	 * @param string $moduleName
	 * @return Zend_Tool_Project_Profile_Resource
	 */
	public static function hasResource(Zend_Tool_Project_Profile $profile, $mapperName, $type, $moduleName = null) {
		if (!is_string($mapperName)) {
			throw new Zend_Tool_Project_Provider_Exception('Skaya_Tool_Project_Provider_ModelMapper::createResource() expects "mapperName" is the name of a mapper resource to check for existence.');
		}

		$mappersDirectory = self::_getMappersDirectoryResource($profile, $moduleName);
		return ($mappersDirectory && ($mappersDirectory->search(array('mapperFile' => array('mapperName' => $mapperName, 'type' => $type)))) instanceof Zend_Tool_Project_Profile_Resource);
	}
	
	/**
	 * _getMappersDirectoryResource()
	 *
	 * @param Zend_Tool_Project_Profile $profile
	 * @param string $moduleName
	 * @return Zend_Tool_Project_Profile_Resource
	 */
	protected static function _getMappersDirectoryResource(Zend_Tool_Project_Profile $profile, $moduleName = null)
	{
		$profileSearchParams = array();

		if ($moduleName != null && is_string($moduleName)) {
			$profileSearchParams = array('modulesDirectory', 'moduleDirectory' => array('moduleName' => $moduleName));
		}

		$profileSearchParams[] = 'mappersDirectory';

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
	
	public static function createResource(Zend_Tool_Project_Profile $profile, $mapperName, $type, $moduleName = null)
	{
		if (!is_string($mapperName)) {
			throw new Zend_Tool_Project_Provider_Exception('Skaya_Tool_Project_Provider_ModelMapper::createResource() expects "mapper-name" is the name of a mapper resource to create.');
		}
		
		if (!is_string($type)) {
			throw new Zend_Tool_Project_Provider_Exception('Skaya_Tool_Project_Provider_ModelMapper::createResource() expects "type" is the name of a model resource that mapper will be belong to.');
		}

		if (!($mappersDirectory = self::_getMappersDirectoryResource($profile, $moduleName))) {
			$modelsDirectory = self::_getModelsDirectoryResource($profile, $moduleName);
			$mapperCreateAtDirectory = $mappersDirectory = $modelsDirectory->createResource('mappersDirectory');
		}
		
		/**
		* Create special mapper directory
		*/
		if ($type) {
			$typedMappersDirectory = $mappersDirectory->search('mappersDirectory', array('type' => $type));
			if (!$typedMappersDirectory) {
				$typedMappersDirectory = $mappersDirectory->createResource('mappersDirectory', array('type' => $type));
				$typedMappersDirectory->getPath();
			}
			if ($typedMappersDirectory) {
				$mapperCreateAtDirectory = $typedMappersDirectory;
			}
		}

		$newMapper = $mapperCreateAtDirectory->createResource(
			'mapperFile',
			array('mapperName' => $mapperName, 'type' => $type, 'moduleName' => $moduleName)
			);

		return $newMapper;
	}
	
}