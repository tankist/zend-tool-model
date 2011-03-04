<?php

class Skaya_Tool_Project_Context_Zf_MapperDecorator_Db
	extends Skaya_Tool_Project_Context_Zf_MapperDecorator_Default
	implements Skaya_Tool_Project_Context_Zf_MapperDecorator_Interface {

	public static function getMapperClassProperties(Skaya_Tool_Project_Context_Zf_MapperFile $mapper) {
		$parameters = parent::getMapperClassProperties($mapper);
		if ($mapperName = $mapper->getMapperName()) {
			$parameters += array(
				new Zend_CodeGenerator_Php_Property(array(
					'const' => true,
					'name' => 'TABLE_NAME',
					'defaultValue' => $mapperName . 's'
				)),
				new Zend_CodeGenerator_Php_Property(array(
					'name' => '_mapperTableName',
					'visibility' => Zend_CodeGenerator_Php_Property::VISIBILITY_PROTECTED,
					'defaultValue' => new Zend_CodeGenerator_Php_Property_DefaultValue(array(
						'value' => 'self::TABLE_NAME',
						'type' => Zend_CodeGenerator_Php_Property_DefaultValue::TYPE_CONSTANT
					))
				)),
			);
		}
		return $parameters;
	}

	public static function getMapperClassMethods(Skaya_Tool_Project_Context_Zf_MapperFile $mapper) {
		$methods = parent::getMapperClassMethods($mapper);

		$getItemsBody = <<<EOS
		\$<token>Table = self::_getTableByName(self::TABLE_NAME);
\$<token>Blob = \$<token>Table->fetchAll(null, \$order, \$count, \$offset);
return \$this->getMappedArrayFromData(\$<token>Blob);

EOS;
		$getItemsPaginatorBody = <<<EOS
		\$<token>Table = self::_getTableByName(self::TABLE_NAME);
\$select = \$<token>Table->select();
if (\$order) {
	\$select->order(\$this->_mapOrderStatement(\$order));
}
\$paginator = Skaya_Paginator::factory(\$select, 'DbSelect');
\$paginator->addFilter(new Zend_Filter_Callback(array(
	'callback' => array(\$this, 'getMappedArrayFromData')
)));
return \$paginator;
EOS;
		$getItemByIdBody = <<<EOS
		\$<token>Table = self::_getTableByName(self::TABLE_NAME);
\$<token>Blob = \$<token>Table->fetchRowById(\$<token>_id);
return \$this->getMappedArrayFromData(\$<token>Blob);
EOS;

		if ($mapperName = $mapper->getMapperName()) {
			$methodsToken = strtolower($mapperName);
			$methodsUcToken = ucfirst($mapperName);
			$methods += array(
				new Zend_CodeGenerator_Php_Method(array(
					'name' => 'get' . $methodsUcToken . 'ById',
					'parameters' => array(
						new Zend_CodeGenerator_Php_Parameter(array(
							'name' => 'id'
						))
					),
					'body' => str_replace('<token>', $methodsToken, $getItemByIdBody)
				)),
				new Zend_CodeGenerator_Php_Method(array(
					'name' => 'get' . $methodsUcToken . 's',
					'parameters' => array(
						new Zend_CodeGenerator_Php_Parameter(array(
							'name' => 'order',
							'defaultValue' => null
						)),
						new Zend_CodeGenerator_Php_Parameter(array(
							'name' => 'count',
							'defaultValue' => null
						)),
						new Zend_CodeGenerator_Php_Parameter(array(
							'name' => 'offset',
							'defaultValue' => null
						))
					),
					'body' => str_replace('<token>', $methodsToken, $getItemsBody)
				)),
				new Zend_CodeGenerator_Php_Method(array(
					'name' => 'get' . $methodsUcToken . 'sPaginator',
					'parameters' => array(
						new Zend_CodeGenerator_Php_Parameter(array(
							'name' => 'order',
							'defaultValue' => null
						))
					),
					'body' => str_replace('<token>', $methodsToken, $getItemsPaginatorBody)
				))
			);
		}
		return $methods;
	}
	
	public static function getMapperClassGetterMethod(Skaya_Tool_Project_Context_Zf_MapperFile $mapper, $entity) {
		$itemName = $mapper->getMapperName();
		$itemName = strtolower(substr($itemName, 0, 1)) . substr($itemName, 1);
		$entity = strtolower(substr($entity, 0, 1)) . substr($entity, 1);
		$ucEntity = ucfirst($entity);

		$getItemByEntityBody = <<<EOS
		\${$itemName}Table = self::_getTableByName(self::TABLE_NAME);
\${$itemName}Blob = \${$itemName}Table->fetchRowBy{$ucEntity}(\${$entity});
return \$this->getMappedArrayFromData(\${$itemName}Blob);
EOS;
		
		return new Zend_CodeGenerator_Php_Method(array(
			'name' => 'get' . ucfirst($itemName) . 'By' . $ucEntity,
			'parameters' => array(
				new Zend_CodeGenerator_Php_Parameter(array(
					'name' => $entity
				))
			),
			'body' => $getItemByEntityBody
		));
	}

}
