<?php

class Skaya_Model_Mapper_Decorator_Cache
	extends Skaya_Model_Mapper_Decorator_Abstract {

	protected $_cacheTemplates = array();

	protected $_reflectionData = array();

	protected $_enabled = true;

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
		$cache = self::getCache();
        $data = $this->_mapper->save($data);
        if (!($cacheTags = $this->getCacheTags('save', array($data)))) {
            $cacheTags = array('list');
        }
		$cache->clean(Zend_Cache::CLEANING_MODE_MATCHING_TAG, $cacheTags);
        if ($cache_id = $this->getCacheId('save', array($data))) {
            $cache->remove($cache_id);
        }
        return $data;
	}

	public function delete($data) {
		$cache = self::getCache();
        $data = $this->_mapper->delete($data);
        if (!($cacheTags = $this->getCacheTags('delete', array($data)))) {
            $cacheTags = array('list');
        }
		$cache->clean(Zend_Cache::CLEANING_MODE_MATCHING_TAG, $cacheTags);
        if ($cache_id = $this->getCacheId('delete', array($data))) {
            $cache->remove($cache_id);
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
		$methods = $this->getCachableMethods();
		if (!$this->getEnabled() || !in_array($method, $methods)) {
			return parent::__call($method, $params);
		}
		if (!($cache = self::getCache())) {
			throw new Skaya_Model_Mapper_Decorator_Exception('Cache engine was not defined');
		}
		$cache_id = $this->getCacheId($method, $params);
		$cacheTags = $this->getCacheTags($method, $params);
		if (!($data = $cache->load($cache_id))) {
			$data = parent::__call($method, $params);
			$cache->save($data, $cache_id, $cacheTags);
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
		$classReflection = $this->_getReflectionData();
		$cacheIdTemplate = (isset($classReflection['methods'][$method]['cache_id']))
							?$classReflection['methods'][$method]['cache_id']:'';
		$cache_id = $name . '_' .$type . '_' . $method;
		/**
		 * @var ReflectionMethod $methodReflection
		 */
		$methodReflection = $reflection->getMethod($method);
		$namedParams = array();
		foreach($methodReflection->getParameters() as /** @var ReflectionParameter $parameter */$parameter) {
			$param = $parameter->getName();
			$namedParams[$param] = $value = $params[$parameter->getPosition()];
			if (empty($cacheIdTemplate) && !empty($value)) {
                if (!is_scalar($value)) {
                    $value = var_export($value);
                }
                $cache_id .= '_' . trim(preg_replace('$[^a-zA-Z0-9_]+$i', "_", $value), '_');
			}
		}
		if (!empty($cacheIdTemplate) && preg_match_all('$\{\$([^\{\}]+)\}$i', $cacheIdTemplate, $matches)) {
			$cache_id = $cacheIdTemplate;
			$vars = $matches[1];
			foreach ($vars as $var) {
				$varValue = self::_parseVariable($var, $namedParams);
                if (!is_scalar($varValue)) {
                    $varValue = var_export($varValue, true);
                }
				$cache_id = str_replace('{$' . $var . '}', (string)$varValue, $cache_id);
			}
		}
		return (strlen($cache_id) > 30)?md5($cache_id):$cache_id;
	}

	protected static function _parseVariable($variable, $stack) {
		if (!$variable) {
			return $stack;
		}
		if (is_scalar($stack)) {
			return (!$variable)?$stack:false;
		}
		if (strpos('[', $variable) === 0) {
			$close = strpos(']', $variable);
			if ($close === false) {
				return false;
			}
			$index = substr($variable, 1, $close - 1);
			if (!$index || !is_array($stack) || !array_key_exists($index, $stack)) {
				return false;
			}
			$stack = $stack[$index];
			$variable = substr($variable, $close);
			return self::_parseVariable($variable, $stack);
		}
		elseif (strpos('->', $variable) === 0) {
			if (preg_match('$[a-z0-9_]+$i', substr($variable, 2), $matches)) {
				$index = $matches[0];
				if (!$index || !is_object($stack) || !property_exists($stack, $index)) {
					return false;
				}
				$stack = $stack->$index;
				$variable = substr($variable, 2 + strlen($index));
				return self::_parseVariable($variable, $stack);
			}
		}
		else {
			switch (gettype($stack)) {
				case 'array' :
					$stack = (is_array($stack) && array_key_exists($variable, $stack))?$stack[$variable]:false;
					break;
				case 'object' :
					$stack = (is_object($stack) && property_exists($stack, $variable))?$stack->$variable:false;
					break;
			}
			return $stack;
		}
		return false;
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
					if (preg_match('$@cache_id\s+([^\s]+)$im', $docBlock, $matches)) {
						$methodOptions['cache_id'] = trim($matches[1]);
					}
					$methods[$method->getName()] = $methodOptions;
				}
			}
			$this->_reflectionData = array(
				'methods' => $methods
			);
		}
		return $this->_reflectionData;
	}

	public function setEnabled($enabled) {
		$this->_enabled = $enabled;
		return $this;
	}

	public function getEnabled() {
		return $this->_enabled;
	}

}
