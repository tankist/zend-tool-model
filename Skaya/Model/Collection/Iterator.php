<?php
class Skaya_Model_Collection_Iterator implements Iterator {
	
	protected $_items = null;
	
	protected $_position = 0;
	
	public function __construct(Skaya_Model_Collection_Abstract $collection) {
		$this->_items = $collection;
		$this->_position = 0;
	}
	
	public function rewind() {
		$this->_position = 0;
	}
	
	public function current() {
		return $this->_items[$this->_position];
	}
	
	public function key() {
		return $this->_position;
	}
	
	public function next() {
		++$this->_position;
	}
	
	public function valid() {
		return ($this->_position < count($this->_items));
	}
}
?>
