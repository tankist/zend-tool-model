<?php

interface Skaya_Model_Mapper_Interface {
    
    public function save($data);
	
	public function delete($data);

    public function getProvider();

    public function getName();

}