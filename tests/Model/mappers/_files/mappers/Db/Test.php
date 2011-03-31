<?php
class MyApp_Db_Test extends Skaya_Model_Mapper_Db_Abstract {

    public function getTestResponse() {
        return get_class($this);
    }

}
?>
