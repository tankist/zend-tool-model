<?php

abstract class Skaya_Model_Mapper_Decorator_Abstract
	extends Skaya_Model_Mapper_Abstract
		implements Skaya_Model_Mapper_Interface {

	/**
	 * @var Skaya_Model_Mapper_Interface
	 */
	protected $_mapper;

    protected $_provider = 'Decorator';

	/**
	 * @param Skaya_Model_Mapper_Interface $mapper
	 */
	public function __construct(Skaya_Model_Mapper_Interface $mapper) {
		$this->_mapper = $mapper;
	}

	/**
	 * @throws Skaya_Model_Mapper_Decorator_Exception
	 * @param  $method
	 * @param  $params
	 * @return false|mixed
	 */
	public function __call($method, $params) {
		if (method_exists($this->_mapper, $method)) {
			return call_user_func_array(array($this->_mapper, $method), $params);
		}
		throw new Skaya_Model_Mapper_Decorator_Exception('Mapper method "'. $method .'" was not found');
	}

	public function save($data) {
		return $this->_mapper->save($data);
	}

	public function delete($data) {
		return $this->_mapper->delete($data);
	}

}