<?php

interface Skaya_Model_Mapper_Db_Interface extends Skaya_Model_Mapper_Interface {

	public function search($conditions, $order = null, $count = null, $offset = null);
	
}
?>