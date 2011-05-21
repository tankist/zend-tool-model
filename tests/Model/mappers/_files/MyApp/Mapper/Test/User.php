<?php

require_once("Abstract.php");
 
class MyApp_Mapper_Test_User extends MyApp_Mapper_Test_AbstractSkaya {

	protected $_fieldMapping = array(
		'firstName' => 'first_name'
	);

    protected $_provider = 'Test';

	public static $userdata = array();

	public function getUserByUsername($username) {
		$userdata = self::$userdata[$username];
		return $userdata;
	}

	public function getUserByEmail($email) {
		$userdata = self::$userdata[$email];
		return $userdata;
	}

}
