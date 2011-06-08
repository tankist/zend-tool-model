<?php
class Skaya_Tool_Project_Provider_Abstract extends Zend_Tool_Project_Provider_Abstract {
	
	public static function addContexts() {
		$contextRegistry = Zend_Tool_Project_Context_Repository::getInstance();
		$contextRegistry->addContextsFromDirectory(
			dirname(dirname(__FILE__)) . '/Context/Zf/', 'Skaya_Tool_Project_Context_Zf_'
		);
	}
	
	public function initialize() {
		parent::initialize();
		self::addContexts();
	}
	
}
?>
