<?php
abstract class Skaya_Model_Mapper_Session_Abstract extends Skaya_Model_Mapper_Abstract implements Skaya_Model_Mapper_Session_Interface {
	
	protected static $_defaultSession = null;

	protected $_provider = 'Session';
	
	/**
	* Save data to the Session store
	* 
	* @param array $data
	* @return int
	*/
	public function save($data) {
		if ( is_array($data) ) {
			$session = $this->getSessionNamespace();
			
			$primaryKey = $this->getPrimaryKey($data);
			$firstKey = array_shift($primaryKey);
			$lastKey = array_pop($primaryKey);
			$sessionData = ($session->$firstKey)?$session->$firstKey:array();
			
			$tmpData = array();
			$tmpData[$lastKey] = $data;
			foreach ( array_reverse($primaryKey) as $key ) {
				$tmpData[$key][$lastKey] = array_shift($tmpData);
				$lastKey = $key;
			}
			
			array_push($primaryKey, $lastKey);
			$sessionData = $this->_arrayMerge($sessionData, $tmpData, $primaryKey);
			
			$session->$firstKey = $sessionData;
			
		}
		return $data;
	}
	
	/**
	* Delete item from session store
	* 
	* @param array $data
	* @return int
	*/
	public function delete($data) {
		if ( is_array($data) ) {
			$session = $this->getSessionNamespace();
			
			$primaryKey = $this->getPrimaryKey($data);
			$firstKey = array_shift($primaryKey);
			$lastKey = array_pop($primaryKey);
			$sessionData = ($session->$firstKey)?$session->$firstKey:array();
			
			$_data = &$sessionData;

			foreach ($primaryKey as $key) {
				$_data = &$_data[$key];
			}
			unset($_data[$lastKey]);

			$session->$firstKey = $sessionData;
		}
		return $data;
	}
	
	/**
	* @desc return items from storage
	*/
	public function getData() {
		
	}
	
	public function setSessionNamespace(Zend_Session_Namespace $session) {
		$this->session = $session;
		
		return $this;
	}
	
	public function getSessionNamespace() {
		if ( empty($this->session) ) {
			$this->session = self::getDefaultSessionNamespace();
		} 
		return $this->session;
	}
	
	public static function setDefaultSessionNamespace(Zend_Session_Namespace $session) {
		self::$_defaultSession = $session;
	}
	
	public static function getDefaultSessionNamespace() {
		return self::$_defaultSession;
	}
	
	protected function _arrayMerge($array1, $array2, $primaryKey) {
		$count = count($primaryKey);
		
		$res = $this->_getCommonArray($array1,$array2,1,$count);
		
		
		return $res;
	}
	
	protected function _getCommonArray($array1, $array2,$i,$count) {
		$res = $array1;
		foreach ( $array2 as $key=>$value ) {
			if ( array_key_exists($key, $array1) ) {
				if ( $i < $count ) {
					$res[$key] = $this->_getCommonArray($array1[$key],$array2[$key],$i+1,$count);
				} else {
					$res[$key] = $array2[$key];
				}
			} else {
				$res[$key] = $array2[$key];
			}
		}
		return $res;
	}
}