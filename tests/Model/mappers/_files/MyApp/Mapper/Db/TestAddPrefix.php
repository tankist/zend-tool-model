<?php
class MyApp_Mapper_Db_TestAddPrefix extends Skaya_Model_Mapper_Db_Abstract {

    public function getTestResponse() {
        return get_class($this);
    }

}
?>
