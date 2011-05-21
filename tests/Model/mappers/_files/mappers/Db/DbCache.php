<?php
class MyApp_Db_DbCache
	extends Skaya_Model_Mapper_Db_Abstract
		implements Skaya_Model_Mapper_Decorator_Decoratable {

	protected $_items = array(
		'Item 0',
		'Item 1',
		'Item 2',
		'Item 3',
		'Item 4',
		'Item 5'
	);

	public function getDecorator() {
		return new Skaya_Model_Mapper_Decorator_Cache($this);
	}

	public function getItemById($id) {
		return (array_key_exists($id, $this->_items))?$this->_items[$id]:false;
	}

	public function getItemsList($order = null, $count = null, $offset = null) {
		return $this->_items;
	}

}
