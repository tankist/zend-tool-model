<?php
interface Skaya_Model_Mapper_Decorator_Cachable {

	public function getCacheId($method, $params = array());

	public function getCacheTags($method, $params = array());

	public function getCachableMethods();

}