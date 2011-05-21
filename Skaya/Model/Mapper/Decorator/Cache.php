<?php

class Skaya_Model_Mapper_Decorator_Cache
	extends Skaya_Model_Mapper_Decorator_Abstract {

	protected $_cacheTemplates = array();

	protected $_regularParams = array('order', 'count', 'offset');

	/**
	 * @var Zend_Cache_Core
	 */
	protected static $_cache;

	/**
	 * @static
	 * @param Zend_Cache_Core $cache
	 * @return void
	 */
	public static function setCache(Zend_Cache_Core $cache) {
		self::$_cache = $cache;
	}

	/**
	 * @static
	 * @return Zend_Cache_Core
	 */
	public static function getCache() {
		return self::$_cache;
	}

	public function save($data) {
		$name = $this->_mapper->getName();
		$type = $this->_mapper->getProvider();
		$cache = self::getCache();
		$cache->clean(Zend_Cache::CLEANING_MODE_MATCHING_TAG, array('search', 'list'));
	}

	public function delete($data) {
		$name = $this->_mapper->getName();
		$type = $this->_mapper->getProvider();
		$cache = self::getCache();
		$cache->clean(Zend_Cache::CLEANING_MODE_MATCHING_TAG, array('search', 'list'));
	}

	public function search($conditions, $order = null, $count = null, $offset = null) {
		$params = func_get_args();
		$cache_id = $this->getCacheId('search', $params);
		$cacheTags = $this->getCacheTags('search', $params);
		if (!in_array('search', $cacheTags)) {
			$cacheTags[] = 'search';
		}
		if (!($data = self::getCache()->load($cache_id))) {
			$data = call_user_func_array(array($this->_mapper, 'search'), $params);
			self::getCache()->save($data, $cache_id, $cacheTags);
		}
		return $data;
	}

	/**
	 * @throws Skaya_Model_Mapper_Decorator_Exception
	 * @param  $method
	 * @param  $params
	 * @return false|mixed
	 */
	public function __call($method, $params) {
		$cache_id = $this->getCacheId($method, $params);
		$cacheTags = $this->getCacheTags($method, $params);
		if (!($data = self::getCache()->load($cache_id))) {
			$data = parent::__call($method, $params);
			self::getCache()->save($data, $cache_id, $cacheTags);
		}
		return $data;
	}

	public function getCacheId($method, $params = array()) {
		if ($this->_mapper instanceof Skaya_Model_Mapper_Decorator_Cachable) {
			$cache_id = $this->_mapper->getCacheId($method, $params);
		}
		else {
			$cache_id = $this->_getCacheIdFromReflection($method, $params);
		}
		return $cache_id;
	}

	public function getCacheTags($method, $params = array()) {
		if ($this->_mapper instanceof Skaya_Model_Mapper_Decorator_Cachable) {
			$cacheTags = $this->_mapper->getCacheTags($method, $params);
		}
		else {
			$cacheTags = $this->_getCacheTagsFromReflection($method, $params);
		}
		return $cacheTags;
	}

	/**
	 * @param  $method
	 * @param  $params
	 * @return string
	 */
	protected function _getCacheIdFromReflection($method, $params) {
		$reflection = new ReflectionObject($this->_mapper);
		$name = $this->_mapper->getName();
		$type = $this->_mapper->getProvider();
		$cache_id = $name . '_' .$type . '_' . $method;
		/**
		 * @var ReflectionMethod $methodReflection
		 */
		$methodReflection = $reflection->getMethod($method);
		$regularParams = array();
		foreach($methodReflection->getParameters() as /** @var ReflectionParameter $parameter */$parameter) {
			$param = $parameter->getName();
			$value = $params[$parameter->getPosition()];
			if (in_array($param, $this->_regularParams)) {
				$regularParams[$param] = $value;
			}
			elseif (!empty($value)) {
				$cache_id .= ':' . $value;
			}
		}
		if (!empty($regularParams)) {
			$cache_id .= '/' . str_replace(array("\r", "\n"), " ", print_r($regularParams, true));
		}
		return md5($cache_id);
	}

	/**
	 * @param  $method
	 * @param  $params
	 * @return array
	 */
	protected function _getCacheTagsFromReflection($method, $params) {
		$name = $this->_mapper->getName();
		$type = $this->_mapper->getProvider();
		return array($name, $type, $method);
	}

}
