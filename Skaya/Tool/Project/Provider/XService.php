<?php

class Skaya_Tool_Project_Provider_XService extends Skaya_Tool_Project_Provider_Abstract {

	public function create($name, $module = null) {
		$profile = $this->_loadProfile(self::NO_PROFILE_THROW_EXCEPTION);
		$name = ucwords($name);

		if (preg_match('#[_-]#', $name)) {
			throw new Zend_Tool_Project_Provider_Exception('Service names should be camel cased.');
		}

		if (self::hasResource($profile, $name, $module)) {
			throw new Zend_Tool_Project_Provider_Exception('This project already has a service named ' . $name);
		}

		$request = $this->_registry->getRequest();
		$response = $this->_registry->getResponse();

		try {
			$serviceResource = self::createResource($profile, $name, $module);
		} catch (Exception $e) {
			$response->setException($e);
			return;
		}

		$response->appendContent('Creating a service at ' . $serviceResource->getContext()->getPath());
		$serviceResource->create();

		$this->_storeProfile();
	}

	/**
	 * hasResource()
	 *
	 * @param Zend_Tool_Project_Profile $profile
	 * @param string $serviceName
	 * @param string $moduleName
	 * @return Zend_Tool_Project_Profile_Resource
	 */
	public static function hasResource(Zend_Tool_Project_Profile $profile, $name, $moduleName = null) {
		if (!is_string($name)) {
			throw new Zend_Tool_Project_Provider_Exception('Zend_Tool_Project_Provider_XService::createResource() expects \"serviceName\" is the name of a service resource to check for existence.');
		}

		$servicesDirectory = self::_getservicesDirectoryResource($profile, $moduleName);
		return ($servicesDirectory && ($servicesDirectory->search(array('serviceFile' => array('serviceName' => $name)))) instanceof Zend_Tool_Project_Profile_Resource);
	}

	/**
	 * _getServicesDirectoryResource()
	 *
	 * @param Zend_Tool_Project_Profile $profile
	 * @param string $moduleName
	 * @return Zend_Tool_Project_Profile_Resource
	 */
	protected static function _getServicesDirectoryResource(Zend_Tool_Project_Profile $profile, $moduleName = null) {
		$root = self::_getServicesRootDirectory($profile, $moduleName);

		if (!$root) {
			throw new Zend_Tool_Project_Provider_Exception('Services directory root cannot be found');
		}

		return $root->search('servicesDirectory');
	}

	protected static function _getServicesRootDirectory(Zend_Tool_Project_Profile $profile, $moduleName = null) {
		if ($moduleName != null && is_string($moduleName)) {
			$profileSearchParams = array('modulesDirectory', 'moduleDirectory' => array('moduleName' => $moduleName));
		}
		else {
			$profileSearchParams = array('applicationDirectory');
		}
		return $profile->search($profileSearchParams);
	}

	public static function createResource(Zend_Tool_Project_Profile $profile, $name, $moduleName = null)
	{
		if (!is_string($name)) {
			throw new Zend_Tool_Project_Provider_Exception('Zend_Tool_Project_Provider_XService::createResource() expects \"serviceName\" is the name of a service resource to create.');
		}

		if (!($servicesDirectory = self::_getServicesDirectoryResource($profile, $moduleName))) {
			$root = self::_getServicesRootDirectory($profile, $moduleName);
			$servicesDirectory = $root->createResource('servicesDirectory');
		}

		$newservice = $servicesDirectory->createResource(
			'serviceFile',
			array('name' => $name)
		);

		return $newservice;
	}

}
