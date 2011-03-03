<?php
Zend_Loader_Autoloader::getInstance()->registerNamespace('Skaya_');

class Skaya_Tool_Project_Provider_Manifest implements Zend_Tool_Framework_Manifest_ProviderManifestable {
	
	public function getProviders()
	{
		Skaya_Tool_Project_Provider_Abstract::addContexts();
		
		return array(
			new Skaya_Tool_Project_Provider_XTables(),
			new Skaya_Tool_Project_Provider_XCollection(),
			new Skaya_Tool_Project_Provider_XMapper(),
			new Skaya_Tool_Project_Provider_XModel(),
			new Skaya_Tool_Project_Provider_XService()
		);
	}
	
}
?>
