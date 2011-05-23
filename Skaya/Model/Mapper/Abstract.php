<?php

require_once "Interface.php";

abstract class Skaya_Model_Mapper_Abstract implements Skaya_Model_Mapper_Interface {
	
	protected $_fieldMapping = array();
	
	protected $_reverseFieldMapping = array();

    protected $_provider = null;
	
	public function __construct() {
		if (!empty($this->_fieldMapping) && empty($this->_reverseFieldMapping)) {
			$this->_reverseFieldMapping = array_flip($this->_fieldMapping);
		}
	}
    
    public function init() {
        
    }
	
	public function map($data = array()) {
		$mappedData = array();
		foreach ((array)$data as $key => $value) {
			$mappedName = (array_key_exists($key, $this->_reverseFieldMapping))?$this->_reverseFieldMapping[$key]:$key;
			$mappedData[$mappedName] = $value;
		}
		return $mappedData;
	}
	
	public function unmap($data = array()) {
		$mappedData = array();
		foreach ((array)$data as $key => $value) {
			$mappedName = (array_key_exists($key, $this->_fieldMapping))?$this->_fieldMapping[$key]:$key;
			$mappedData[$mappedName] = $value;
		}
		return $mappedData;
	}
	
	public function getRawArrayFromData($data) {
		if (is_array($data)) {
			return $data;
		}
		
		if (is_object($data)) {
			return (array)$data;
		}
		
		return array();
	}

	public function getMappedArrayFromData($data) {
		$data = $this->getRawArrayFromData($data);
		if (gettype(current($data)) != 'array' && gettype(current($data)) != 'object') {
			return $this->map($data);
		}
		$newData = array();
		foreach ((array)$data as $row) {
			if (is_object($row)) {
				$row = $this->getRawArrayFromData($row);
			}
			$newData[] = $this->map($row);
		}
		return $newData;
	}

	/**
	 * @return string
	 */
	public function getName() {
		$fullClassName = get_class($this);
		if (strpos($fullClassName, '_') !== false) {
			$mapperName = strrchr($fullClassName, '_');
			return ltrim($mapperName, '_');
		} elseif (strpos($fullClassName, '\\') !== false) {
			$mapperName = strrchr($fullClassName, '\\');
			return ltrim($mapperName, '\\');
		} else {
			return $fullClassName;
		}
	}

	public function getProvider() {
		return $this->_provider;
	}

}