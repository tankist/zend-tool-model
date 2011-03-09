<?php

abstract class Skaya_Model_Abstract implements Skaya_Model_Interface {

	/**
	 * Database mapper type
	 */
	const MAPPER_DATABASE = 'db';

	/**
	 * Session mapper type
	 */
	const MAPPER_SESSION = 'session'; 

	/**
	 * Model data
	 * @var array
	 */
	protected $_data = array();

	/**
	 * Name of the Model
	 * @var string
	 */
	protected $_modelName = '';

	/**
	 * Mapper broker
	 * @var Skaya_Model_Mapper_MapperBroker
	 */
    public $mappers;

	/**
	 * Creates Model object
	 * @param array $data
	 */
	public function __construct($data = array()) {
        $this->mappers = Skaya_Model_Mapper_MapperBroker::getInstance();
		if (!empty($data)) $this->populate($data);
	}

	/**
	 * Populate Model with data
	 * @throws Skaya_Model_Exception
	 * @param array $data
	 * @return Skaya_Model_Abstract
	 */
	public function populate($data = array()) {
		if (is_object($data)) {
			if (method_exists($data, 'toArray')) {
				$data = $data->toArray();
			}
			else {
				$data = (array)$data;
			}
		}
		
		if (!is_array($data)) {
			throw new Skaya_Model_Exception('Data must be array or object');
		}
		
		foreach ($data as $key => $value) {
			$this->$key = $value;
		}
		
		return $this;
	}

	/**
	 * Returns true if no data stored in Model
	 * @return bool
	 */
	public function isEmpty() {
		return empty($this->_data);
	}

	/**
	 * Set model property
	 * @throws Skaya_Model_Exception
	 * @param  $name
	 * @param  $value
	 * @return void
	 */
	public function __set($name, $value) {
		if (!is_string($name) || empty($name)) {
			throw new Skaya_Model_Exception('Name cannot be empty');
		}
		
		$camelcaseFilter = new Zend_Filter_Word_UnderscoreToCamelCase();
		
		$setterName = 'set' . ucfirst($camelcaseFilter->filter($name));
		if (method_exists($this, $setterName)) {
			call_user_func(array($this, $setterName), $value);
		}
		else {
			$this->_data[$name] = $value;
		}
	}

	/**
	 * Get model property
	 * @param  $name
	 * @return mixed|null
	 */
	public function __get($name) {
		$camelcaseFilter = new Zend_Filter_Word_UnderscoreToCamelCase();
		$getterName = 'get' . ucfirst($camelcaseFilter->filter($name));
		
		$data = null;
		if (array_key_exists($name, $this->_data)) {
			$data = $this->_data[$name];
		}
		elseif (method_exists($this, $getterName)) {
			$data = call_user_func(array($this, $getterName));
		}
		return $data;
	} 

	/**
	 * Check whether there is some data in the model
	 * @param  $name
	 * @return bool
	 */
	public function __isset($name) {
		return array_key_exists($name, $this->_data);
	}

	/**
	 * Unset model property with given name
	 * @param  $name
	 * @return void
	 */
	public function __unset($name) {
		if (array_key_exists($name, $this->_data)) {
			unset($this->_data[$name]);
		}
	}

	/**
	 * Converts model to array
	 * @return array
	 */
	public function toArray() {
		$data = (array)$this->_data;
		foreach ($data as $key => &$value) {
			if (is_object($value)) {
				if ($value instanceof self ||
					$value instanceof Skaya_Model_Collection_Abstract) {
					$value = $value->toArray();
				}
			}
		}
		return $data;
	}

	/**
	 * Saves model data using current model's mapper
	 * @return Skaya_Model_Abstract
	 */
	public function save() {
		$data = $this->getMapper()->save($this->toArray());
		return $this->populate($data);
	}

	/**
	 * Delete model from the storage
	 * @return Skaya_Model_Abstract
	 */
	public function delete() {
		$this->getMapper()->delete($this->toArray());
		return $this;
	}

	/**
	 * Returns current model's mapper
	 * @return Skaya_Model_Mapper_Interface
	 */
    public function getMapper() {
        return $this->mappers->getMapper($this->_modelName);
    }
}
?>
