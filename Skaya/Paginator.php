<?php
class Skaya_Paginator extends Zend_Paginator {
	
	protected $_cacheSalt = '';

	public function setCacheSalt($salt) {
		$this->_cacheSalt = $salt;
		return $this;
	}

	public function getCacheSalt() {
		return $this->_cacheSalt;
	}

	protected function _getCacheId($page = null) {
		$cacheSalt = $this->getCacheSalt();
		if ($cacheSalt != '') {
			$cacheSalt = '_' . $cacheSalt;
		}
		return parent::_getCacheId($page) . $cacheSalt;
	}
	
	public function addFilter($filter) {
		if ($this->_filter instanceOf Zend_Filter && method_exists($this->_filter, 'addFilter')) {
			//Filter already exists and contains other filters - simply add new filter to chain
			$this->_filter->addFilter($filter);
		}
		else {
			if ($this->_filter instanceOf Zend_Filter_Interface) {
				//Filter already exists but it's not a container - create new container and add there current filter and new one
				$containerFilter = new Zend_Filter();
				$containerFilter->addFilter($this->_filter)->addFilter($filter);
				$filter = $containerFilter;
			}
			$this->setFilter($filter);
		}
	}
	
	public static function factory($data, $adapter = self::INTERNAL_ADAPTER, array $prefixPaths = null) {
		$paginator = parent::factory($data, $adapter, $prefixPaths);
		return new self($paginator->getAdapter());
	}
	
}
?>
