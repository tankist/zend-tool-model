<?php

class Skaya_Model_Mapper_Decorator_Cache
	extends Skaya_Model_Mapper_Decorator_Abstract {

	protected $_cacheTemplates = array();

	protected $_regularParams = array('order', 'count', 'offset');

	protected $_reflectionData = array();

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
		$cache->clean(Zend_Cache::CLEANING_MODE_MATCHING_TAG, array('list'));
	}

	public function delete($data) {
		$name = $this->_mapper->getName();
		$type = $this->_mapper->getProvider();
		$cache = self::getCache();
		$cache->clean(Zend_Cache::CLEANING_MODE_MATCHING_TAG, array('list'));
	}

	/**
	 * @throws Skaya_Model_Mapper_Decorator_Exception
	 * @param  $method
	 * @param  $params
	 * @return false|mixed
	 */
	public function __call($method, $params) {
		$methods = $this->getCachableMethods();
		if (!in_array($method, $methods)) {
			return parent::__call($method, $params);
		}
		$cache_id = $this->getCacheId($method, $params);
		$cacheTags = $this->getCacheTags($method, $params);
		if (!($data = self::getCache()->load($cache_id))) {
			$data = parent::__call($method, $params);
			self::getCache()->save($data, $cache_id, $cacheTags);
		}
		return $data;
	}

	public function getCacheId($method, $params = array()) {
		$cache_id = false;
		if ($this->_mapper instanceof Skaya_Model_Mapper_Decorator_Cachable) {
			$cache_id = $this->_mapper->getCacheId($method, $params);
		}
		if (!$cache_id) {
			$cache_id = $this->_getCacheIdFromReflection($method, $params);
		}
		return $cache_id;
	}

	public function getCacheTags($method, $params = array()) {
		$cacheTags = array();
		if ($this->_mapper instanceof Skaya_Model_Mapper_Decorator_Cachable) {
			$cacheTags = $this->_mapper->getCacheTags($method, $params);
		}
		if (empty($cacheTags)) {
			$cacheTags = $this->_getCacheTagsFromReflection($method, $params);
		}
		return $cacheTags;
	}

	public function getCachableMethods() {
		if ($this->_mapper instanceof Skaya_Model_Mapper_Decorator_Cachable) {
			$methods = $this->_mapper->getCachableMethods();
		}
		else {
			$methods = $this->_getCachableMethodsFromReflection();
		}
		return $methods;
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
		$cacheIdTemplate = '';
		$cache_id = $name . '_' .$type . '_' . $method;
		/**
		 * @var ReflectionMethod $methodReflection
		 */
		$methodReflection = $reflection->getMethod($method);
		$docBlock = $methodReflection->getDocComment();
		if ($docBlock && preg_match('$@cache_id\s+([\w_\$\[\]]+)$im', $docBlock, $matches)) {
			
		}
		$regularParams = $namedParams = array();
		foreach($methodReflection->getParameters() as /** @var ReflectionParameter $parameter */$parameter) {
			$param = $parameter->getName();
			$namedParams[$param] = $value = $params[$parameter->getPosition()];
			if (in_array($param, $this->_regularParams) && $value !== null) {
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
		$tags = array($name, $type, $method);
		if ($this->_isMethodCachable($method)) {
			$reflection = $this->_getReflectionData();
			$methodOptions = $reflection['methods'][$method];
			if (array_key_exists('tags', $methodOptions)) {
				$tags = $methodOptions['tags'];
			}
		}
		return $tags;
	}

	protected function _isMethodCachable($method) {
		$reflection = $this->_getReflectionData();
		return array_key_exists($method, $reflection['methods']);
	}

	protected function _getCachableMethodsFromReflection() {
		$reflection = $this->_getReflectionData();
		return array_keys($reflection['methods']);
	}

	protected function _getReflectionData() {
		if (empty($this->_reflectionData)) {
			$methods = array();
			$reflection = new ReflectionObject($this->_mapper);
			foreach ($reflection->getMethods() as /** @var ReflectionMethod $method */$method) {
				$docBlock = $method->getDocComment();
				if ($docBlock && strpos($docBlock, '@cachable') !== false) {
					$methodOptions = array();
					if (preg_match('$@cache_tags\s+([\w\s_]+)\s+$im', $docBlock, $matches)) {
						$list = trim($matches[1]);
						$methodOptions['tags'] = preg_split('$\s+$', $list);
					}
					$methods[$method->getName()] = $methodOptions;
				}
			}
			$this->_reflectionData = array(
				'methods' => $methods,
				'saveDeleteMirror'
			);
		}
		return $this->_reflectionData;
	}

}
