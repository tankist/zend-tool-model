<?php
class Skaya_Model_Mapper_Decorator_Db extends Skaya_Model_Mapper_Decorator_Abstract {

	public function save($data) {
		$name = $this->_mapper->getName();
		$type = $this->_mapper->getProvider();
	}

	public function delete($data) {
		$name = $this->_mapper->getName();
		$type = $this->_mapper->getProvider();
	}

	public function search($conditions, $order = null, $count = null, $offset = null) {

	}

}
