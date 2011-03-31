<?php
class MyApp_Mapper_Session_TestSessionMapper extends Skaya_Model_Mapper_Session_Abstract {
	
	public function getTestResponse() {
        return get_class($this);
    }

	public function getPrimaryKey($data = false) {
		return 1;
	}
	
}
