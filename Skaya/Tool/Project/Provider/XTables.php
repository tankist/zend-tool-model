<?php
require_once("Abstract.php");
 
class Skaya_Tool_Project_Provider_XTables extends Skaya_Tool_Project_Provider_Abstract
{
	/**
	* DB adapter used for constructing model
	* 
	* @var Zend_Db_Adapter_Abstract
	*/
	protected static $_dbAdapter = null;

	protected static $_foreignKeys = array('dependent' => array(), 'references' => array());

	/**
	 * create()
	 *
	 * @param string $name
	 */
	public function create($filterTablePrefix = null)
	{
		$profile = $this->_loadProfileRequired();
		$adapter = self::getDbAdapter($profile);

		try {
			$decorator = Skaya_Tool_Project_Context_Zf_TablesDecorator_Abstract::getDecoratorClass($adapter);
			if ($filterTablePrefix) {
				call_user_func(array($decorator, 'setTableNameFilter'), new Zend_Filter_StringTrim($filterTablePrefix));
			}
			call_user_func(array($decorator, 'parseForeignKeys'), $adapter);

			$dbTablesDirectory = self::_getDbTablesDirectory($profile);
			$tables = Skaya_Tool_Project_Context_Zf_TablesDecorator_Abstract::getTables($adapter);
			foreach($tables as $table) {
				if (!self::hasResource($profile, $table)) {
					$dbTableFile = $dbTablesDirectory->createResource(
						'dbTableFile',
						array('dbTableName' => $table, 'dbAdapter' => $adapter)
					);
					$dbTableFile->create();
				}
			}
		}
		catch (Exception $e) {
			$this->_registry->getResponse()->appendContent($e->getMessage());
			$this->_registry->getResponse()->setException($e);
		}

		//$this->_storeProfile();
	}

	public static function hasResource(Zend_Tool_Project_Profile $profile, $name) {
		$dbTableDirectory = self::_getDbTablesDirectory($profile);
		return (
			$dbTableDirectory &&
				($dbTableDirectory->search(array('dbTableFile' => array('dbTableName' => $name)))
					instanceof Zend_Tool_Project_Profile_Resource)
		);
	}

	protected static function _getDbTablesDirectory(Zend_Tool_Project_Profile $profile) {
		if (!$modelsDirectoryResource = $profile->search('modelsDirectory')) {
			$modelsDirectoryResource = $profile->createResourceAt('applicationDirectory', 'modelsDirectory');
		}
		if (!$dbTableDirectory = $modelsDirectoryResource->search('dbTableDirectory')) {
			$dbTableDirectory = $modelsDirectoryResource->createResource('dbTableDirectory');
		}
		return $dbTableDirectory;
	}
	
	public static function getDbAdapter(Zend_Tool_Project_Profile $profile = null) {
		if (self::$_dbAdapter == null) {
			$config = $profile->search('applicationConfigFile');
			if (!$config) {
				throw new Zend_Tool_Project_Provider_Exception('Project config must be initialized before');
			}
			defined('APPLICATION_PATH') ||
				define('APPLICATION_PATH', realpath(parent::_findProfileDirectory() . DIRECTORY_SEPARATOR . 'application'));
			$application = new Zend_Application('development', $config->getPath());
			self::$_dbAdapter = $application->bootstrap('db')->getBootstrap()->getResource('db');
			if (!(self::$_dbAdapter instanceof Zend_Db_Adapter_Abstract)) {
				throw new Zend_Tool_Project_Provider_Exception('Db adapter cannot be initialized');
			}
		}
		return self::$_dbAdapter;
	}

}
