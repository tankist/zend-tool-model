<?php
class Skaya_Controller_Action_Helper_Service extends Zend_Controller_Action_Helper_Abstract {

	/**
	 * Return service instance by its name
	 * @param  $serviceName
	 * @return Skaya_Model_Service_Abstract
	 */
	public function direct($serviceName) {
		return Skaya_Model_Service_Abstract::factory($serviceName);
	}

}
