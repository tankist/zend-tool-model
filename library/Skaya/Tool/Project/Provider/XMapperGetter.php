<?php
class Skaya_Tool_Project_Provider_XMapperGetter extends Skaya_Tool_Project_Provider_Abstract {

	public function create($entity, $name, $type, $module = null) {
		$profile = $this->_loadProfile();

		if (preg_match('#[_-]#', $entity)) {
            throw new Zend_Tool_Project_Provider_Exception('Entity names should be camel cased.');
        }
		$entity = ucfirst($entity);
		$name = ucfirst($name);
		$type = ucfirst($type);

		if (self::hasResource($profile, $entity, $name, $type, $module)) {
            throw new Zend_Tool_Project_Provider_Exception('This mapper (' . $name . ') of type ' . $type . ' already has an getter named (' . $entity . ')');
        }

		$entityResource = self::createResource($profile, $entity, $name, $type, $module);

		// get request/response object
        $response = $this->_registry->getResponse();

		$response->appendContent(
			'Creating an getter named ' . $entityResource->getContext()->getGetterName()
			);

		$this->_storeProfile();
	}

	/**
     * createResource()
     *
     * @param Zend_Tool_Project_Profile $profile
     * @param string $entity
     * @param string $mapper
     * @param string $type
     * @param string $module
     * @return Zend_Tool_Project_Profile_Resource
     */
	public static function createResource(Zend_Tool_Project_Profile $profile, $entity, $mapper, $type, $module = null) {
		if (!$entity || !is_string($entity)) {
			throw new Zend_Tool_Project_Provider_Exception('Skaya_Tool_Project_Provider_XMapperGetter::createResource() expects \"entity\" is the name of an entity resource to create.');
		}
		if (!$mapper || !is_string($mapper)) {
			throw new Zend_Tool_Project_Provider_Exception('Skaya_Tool_Project_Provider_XMapperGetter::createResource() expects \"mapper\" is the name of a mapper resource to create.');
		}
		if (!$type || !is_string($type)) {
			throw new Zend_Tool_Project_Provider_Exception('Skaya_Tool_Project_Provider_XMapperGetter::createResource() expects \"type\" is the name of a type of the mapper resource to create.');
		}

		$mapperResource = self::_getMapperResource($profile, $mapper, $type, $module);
		$getter = $mapperResource->createResource('mapperGetter', array('getterName' => $entity));
		if ($getter) {
			$getter->create();
		}
		return $getter;
	}

	/**
	 * hasResource()
	 *
	 * @param Zend_Tool_Project_Profile $profile
	 * @param string $entity
	 * @param string $mapper
	 * @param string $type
	 * @param string $module
	 * @return Zend_Tool_Project_Profile_Resource
	 */
	public static function hasResource(Zend_Tool_Project_Profile $profile, $entity, $mapper, $type, $module = null) {
		$mapperResource = self::_getMapperResource($profile, $mapper, $type, $module);
		if (!$mapperResource) {
			throw new Zend_Tool_Project_Provider_Exception('Mapper '. $mapper .' type ' . $type . ' cannot be found');
		}
		return ($mapperResource->search(array('mapperGetter' => array('getterName' => $entity)))
		            instanceof Zend_Tool_Project_Profile_Resource);
	}

	/**
	 * _getMapperResource()
	 *
	 * @param Zend_Tool_Project_Profile $profile
	 * @param string $mapper
	 * @param string $type
	 * @param string $module
	 * @return Zend_Tool_Project_Profile_Resource
	 */
	protected static function _getMapperResource(Zend_Tool_Project_Profile $profile, $mapper, $type, $module = null) {
		$mappersDirecory = self::_getMappersDirectoryResource($profile, $module);
		if (!$mappersDirecory) {
			throw new Zend_Tool_Project_Provider_Exception('Mappers directory cannot be found');
		}
		return $mappersDirecory->search(array('mapperFile' => array('mapperName' => $mapper, 'type' => $type)));
	}

	/**
	 * _getMappersDirectoryResource()
	 *
	 * @param Zend_Tool_Project_Profile $profile
	 * @param string $module
	 * @return Zend_Tool_Project_Profile_Resource
	 */
	protected static function _getMappersDirectoryResource(Zend_Tool_Project_Profile $profile, $module = null)
	{
		$profileSearchParams = array();

		if ($module != null && is_string($module)) {
			$profileSearchParams = array('modulesDirectory', 'moduleDirectory' => array('moduleName' => $module));
		}

		$profileSearchParams[] = 'mappersDirectory';

		return $profile->search($profileSearchParams);
	}

}
