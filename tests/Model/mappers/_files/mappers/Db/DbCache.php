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

	/**
	 * @cachable
	 * @param  $id
	 * @cache_tags item
	 * @cache_id item_{$id}
	 * @return array|bool
	 */
	public function getItemById($id) {
		return (array_key_exists($id, $this->_items))?$this->_items[$id]:false;
	}

	/**
	 * @cachable
	 * @cache_tags list items
	 * @param null $order
	 * @param null $count
	 * @param null $offset
	 * @return array
	 */
	public function getItemsList($order = null, $count = null, $offset = null) {
		return $this->_items;
	}

	/**
	 * @cachable
	 * @cache_id item_{$data[id]}
	 * @cache_tags item
	 * @param array $data
	 * @return mixed
	 */
	public function getItemByDataBlob($data = array()) {
		return (array_key_exists($data['id'], $this->_items))?$this->_items[$data['id']]:false;
	}

}