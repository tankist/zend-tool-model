<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_Tool
 * @subpackage Framework
 * @copyright  Copyright (c) 2005-2009 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/**
 * @category   Zend
 * @package    Zend_Tool
 * @copyright  Copyright (c) 2005-2009 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
require_once("Abstract.php");
 
class Skaya_Tool_Project_Provider_ModelSchema extends Skaya_Tool_Project_Provider_Abstract
{
	/**
	* DB adapter used for constructing model
	* 
	* @var Zend_Db_Adapter_Abstract
	*/
	protected static $_dbAdapter = null;
	protected static $_foreignKeys = array('dependent' => array(), 'references' => array());
	
	const FOREIGN_KEYS_REGEXP = '$FOREIGN KEY.*?\((.*?)\).*?REFERENCES(.*?)\((.*?)\)$im';
	/**
	 * create()
	 *
	 * @param string $name
	 */
	public function create($dsn)
	{
		$profile = $this->_loadProfileRequired();
		$modelsDirectoryResource = $profile->search('modelsDirectory');
		if (!$modelsDirectoryResource) {
			throw new Zend_Tool_Project_Provider_Exception('models directory was not found in current profile');
		}
		
		if (!($dbTableDirectory = $modelsDirectoryResource->search('DbTableDirectory'))) {
			$dbTableDirectory = $modelsDirectoryResource->createResource('DbTableDirectory');
		}
		
		if ($modelsDirectoryResource->hasChildren()) {
			$newProfile = $modelsDirectoryResource->getProfile();
		}
		else {
			$newProfile = new Zend_Tool_Project_Profile(array(
				'projectDirectory' => $profile->getAttribute('projectDirectory'),
				'profileData' => $this->_getDefaultProfile()
			));
			$newProfile->loadFromData();
			$modelsDirectoryResource->append($newProfile->search('dbTableDirectory'));
		}
		
		$tables = self::getDbAdapter($dsn)->listTables();
		foreach ($tables as $table) {
			$this->_parseForeignKeys($table);
		}
		
		foreach ($tables as $table) {
			$tableResource = $newProfile->search(array('dbTableFile' => array('dbTableName' => $table)));
			if (!$tableResource) {
				$tableResource = $newProfile->createResourceAt('dbTableDirectory', 'dbTableFile', array('dbTableName' => $table));
			}
		}
		
		foreach ($newProfile->getIterator() as $resource) {
			if ($resource->getContext() instanceOf Zend_Tool_Project_Context_Zf_DbTableFile) $resource->create();
		}
		$this->_storeProfile();
	}
	
	public static function getDbAdapter($dsn = '') {
		if (self::$_dbAdapter == null) {
			$autoloader = Zend_Loader_Autoloader::getInstance();
			if (!(self::$_dbAdapter instanceOf Zend_Db_Adapter_Abstract)) {
				$dbOptions = self::_parseDSN($dsn);
				self::$_dbAdapter = Zend_Db::factory(new Zend_Config($dbOptions));
			}
		}
		return self::$_dbAdapter;
	}
	
	public static function getForeignKeysReference($tableName) {
		$d = self::$_foreignKeys['dependent'];
		$r = self::$_foreignKeys['references'];
		return array(
			'dependent' => (array_key_exists($tableName, $d))?$d[$tableName]:array(),
			'references' => (array_key_exists($tableName, $r))?$r[$tableName]:array()
		);
	}
	
	protected static function _parseForeignKeys($tableName) {
		$dependentTables = $refTables = array();
		
		$adapter = self::getDbAdapter();
		$data = $adapter->fetchRow('SHOW CREATE TABLE '.$adapter->quoteTableAs($tableName));
		$data = $data['Create Table'];
		$keysCount = preg_match_all(self::FOREIGN_KEYS_REGEXP, $data, $keys);
		if ($keysCount > 0) {
			$filter = new Zend_Filter_Word_UnderscoreToCamelCase();
			for($i=0;$i<$keysCount, $tableFrom = $tableName, $columnFrom = trim($keys[1][$i], ' `'), $tableTo = trim($keys[2][$i], ' `'), $columnTo = trim($keys[3][$i], ' `');$i++){
				if (!array_key_exists($tableTo, $dependentTables)) {
					$dependentTables[$tableTo] = array();
				}
				$dependentTables[$tableTo][] = $tableFrom;
				
				if (!array_key_exists($tableFrom, $refTables)) {
					$refTables[$tableFrom] = array();
				}
				$referenceName = $filter->filter($tableTo);
				if (!array_key_exists($referenceName, $refTables[$tableFrom])) {
					$refTables[$tableFrom][$referenceName] = array(
						'columns' => array($columnFrom),
						'refTableClass' => $tableTo,
						'refColumns' => array($columnTo)
					);
				}
				else {
					$refTables[$tableFrom]['columns'][$referenceName] = $columnFrom;
					$refTables[$tableFrom]['refColumns'][$referenceName] = $columnTo;
				}
					
			}
		}
		
		self::$_foreignKeys['dependent'] = array_merge_recursive(self::$_foreignKeys['dependent'], $dependentTables);
		self::$_foreignKeys['references'] = array_merge(self::$_foreignKeys['references'], $refTables);
	}
	
	protected static function _parseDSN($dsn) {
		list($adapter, $dsn) = explode('://', $dsn);
		if (empty($dsn)) {
			throw new Zend_Tool_Project_Provider_Exception('Incorrect DSN passed. Use the following form: adapter://username:password@host/database');
		}
		$params = array();
		$connectionString = trim($dsn, ' /');
		list($credentials, $server) = explode('@', $connectionString);
		if (!empty($credentials)) {
			list($params['username'], $params['password']) = explode(':', $credentials);
		}
		if (!empty($server)) {
			list($params['host'], $params['dbname']) = explode('/', $server);
		}
		return array('adapter' => $adapter, 'params' => array_map('strval', $params));
	}
	
	protected function _getDefaultProfile() {
		$data = <<<EOS
<?xml version="1.0" encoding="UTF-8"?>
	<projectProfile type="default">
		<projectDirectory>
			<applicationDirectory>
				<modelsDirectory>
					<dbTableDirectory>
						<dbTableFile abstract="true" dbTableName="abstract" />
					</dbTableDirectory>
				</modelsDirectory>
			</applicationDirectory>
		</projectDirectory>
	</projectProfile>
EOS;
		return $data;
	}
}
