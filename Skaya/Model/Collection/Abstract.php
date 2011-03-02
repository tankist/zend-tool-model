<?php
abstract class Skaya_Model_Collection_Abstract implements ArrayAccess, Countable, IteratorAggregate {
	
	protected $_items = array();
	
	protected $_itemType = '';
	
	protected $_position = 0;
	
	public function __construct($products = array()) {
		if (!empty($products) && is_array($products)) {
			foreach ($products as $product) {
				if (is_array($product) || is_object($product)) {
					if (!class_exists($this->_itemType, true)) {
						throw new Skaya_Model_Collection_Exception('Class for the item was not found');
					}
					$reflector = new ReflectionClass($this->_itemType);
					$productInstance = $reflector->newInstanceArgs(array($product));
					$this->_items[] = $productInstance;
				}
				else {
					throw new Skaya_Model_Collection_Exception('Wrong type of the item');
				}
			}
		}
		$this->_position = 0;
	}
	
	public function count() {
		return count($this->_items);
	}
	
	public function offsetExists($offset) {
		return isset($this->_items[$offset]);
	}
	
	public function offsetGet($offset) {
		return (isset($this->_items[$offset]))?$this->_items[$offset]:null;
	}
	
	public function offsetSet($offset, $value) {
		$this->_items[$offset] = $value;
	}
	
	public function offsetUnset($offset) {
		unset($this->_items[$offset]);
	}
	
	public function getIterator() {
		return new Skaya_Model_Collection_Iterator(clone $this);
	}
	
	public function clear() {
		foreach ( $this->_items as $key=>$item ) {
			unset($this->_items[$key]);
		}
	}
	
	public function toArray() {
		$arr = array();
		foreach ( $this->_items as $item ) {
			$arr[] = $item->toArray();
		}
		return $arr;
	}
}
?>
