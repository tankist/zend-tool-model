<?php
class Skaya_Application_Resource_Model extends Zend_Application_Resource_ResourceAbstract {
	
	protected $_params = array();
	
	protected $_defaultMapperType = '';
	
	/**
	 * Set the adapter params
	 *
	 * @param  $adapter string
	 * @return Skaya_Application_Resource_Model
	 */
	public function setParams(array $params) {
		$this->_params = $params;
		return $this;
	}

	/**
	 * Adapter parameters
	 *
	 * @return array
	 */
	public function getParams() {
		return $this->_params;
	}
	
	public function setDefaultMapperType($mapperType) {
		$this->_defaultMapperType = $mapperType;
		return $this;
	}
	
	public function getDefaultMapperType() {
		return $this->_defaultMapperType;
	}
	
	public function init() {
		if (null !== ($mapperType = $this->getDefaultMapperType())) {
			Skaya_Model_Mapper_MapperBroker::setDefaultProvider($mapperType);
		}
	}
}
?>
