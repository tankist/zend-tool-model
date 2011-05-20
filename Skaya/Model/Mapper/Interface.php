<?php

interface Skaya_Model_Mapper_Interface {
    
    public function save($data);
	
	public function delete($data);
	
	public function search($conditions, $order = null, $count = null, $offset = null);

    public function getProvider();
	
}