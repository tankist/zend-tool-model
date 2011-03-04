<?php
class Skaya_Tool_Project_Context_Zf_MapperDecorator_Default
		implements Skaya_Tool_Project_Context_Zf_MapperDecorator_Interface {

	public static function getMapperClassProperties(Skaya_Tool_Project_Context_Zf_MapperFile $mapper) {
		return array();
	}

	public static function getMapperClassMethods(Skaya_Tool_Project_Context_Zf_MapperFile $mapper) {
		return array();
	}

	public static function getMapperClassGetterMethod(Skaya_Tool_Project_Context_Zf_MapperFile $mapper, $getterName) {
		return false;
	}

}
