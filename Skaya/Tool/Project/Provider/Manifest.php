<?php
Zend_Loader_Autoloader::getInstance()->registerNamespace('Skaya_');

class Skaya_Tool_Project_Provider_Manifest implements Zend_Tool_Framework_Manifest_ProviderManifestable {
	
	public function getProviders()
	{
		Skaya_Tool_Project_Provider_Abstract::addContexts();
		
		return array(
			new Skaya_Tool_Project_Provider_ModelSchema(),
			new Skaya_Tool_Project_Provider_ModelCollection(),
			new Skaya_Tool_Project_Provider_ModelMapper()
		);
	}
	
}
?>
