<?php

abstract class Skaya_Model_Service_Abstract {
	
	protected static $_instances = array();

    /**
     * Mappers broker
     *
     * @var Skaya_Model_Mapper_MapperBroker|null
     */
    protected $_mappers = null;
	
	protected function __construct() {
        $this->_mappers = Skaya_Model_Mapper_MapperBroker::getInstance();
    }
	
	protected function __clone() {}
	
	public static function factory($serviceName) {
		if (!array_key_exists($serviceName, self::$_instances)) {
			$className = "Service_$serviceName";
			self::$_instances[$serviceName] = new $className();
		}
		return self::$_instances[$serviceName];
	}
	
	abstract public static function create($data = array());
	
}