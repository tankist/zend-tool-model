<?php
class Skaya_Filter_Array_Collection implements Zend_Filter_Interface {
	
	protected $_collectionType = '';
	
	public function __construct($collectionType = '') {
		$this->setCollectionType($collectionType);
	}
	
	public function setCollectionType($collectionType = '') {
		$this->_collectionType = $collectionType;
		return $this;
	}
	
	public function getCollectionType() {
		return $this->_collectionType;
	}
	
	public function filter($data) {
		if (!is_array($data)) {
			if (is_object($data) && method_exists($data, 'toArray')) {
				$data = $data->toArray();
			}
			else {
				throw new Zend_Filter_Exception('Incomming data cannot be threated as array');
			}
		}
		$collectionClass = $this->getCollectionType();
		if (!class_exists($collectionClass, true)) {
			throw new Zend_Filter_Exception('Unknow collection class provided');
		}
		$collectionInstance = new $collectionClass($data);
		return $collectionInstance;
	}
}
?>
