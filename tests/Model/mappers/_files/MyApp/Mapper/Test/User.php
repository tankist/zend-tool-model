<?php

require_once("Abstract.php");
 
class MyApp_Mapper_Test_User extends MyApp_Mapper_Test_AbstractSkaya {

	protected $_userdata = array(
		'id' => 1,
		'first_name' => 'Test User'
	);

	protected $_fieldMapping = array(
		'firstName' => 'first_name'
	);

    protected $_provider = 'Test';

	public static $userdata = array();

	public function getUserByUsername($username) {
		return $this->getMappedArrayFromData($this->_userdata);
	}

	public function getUserByEmail($email) {
		return $this->getMappedArrayFromData($this->_userdata);
	}

}
