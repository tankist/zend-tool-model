<?php
interface Skaya_Model_Mapper_Decorator_Decoratable {

	/**
	 * @abstract
	 * @return Skaya_Model_Mapper_Decorator_Abstract
	 */
	public function getDecorator();

}