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
	 * @throws Skaya_Model_Mapper_Decorator_Exception
	 * @param Skaya_Model_Mapper_Interface $mapper
	 */
	public function __construct(Skaya_Model_Mapper_Interface $mapper) {
		if ($mapper instanceof Skaya_Model_Mapper_Decorator_Abstract) {
			throw new Skaya_Model_Mapper_Decorator_Exception('Decorators cannot be decorated');
		}
		$this->_mapper = $mapper;
	}

}