<?php

interface Skaya_Model_Interface {
	
	public function save();
	
	public function delete();
	
	public function populate($data = array());
	
	public function toArray();
}
?>
