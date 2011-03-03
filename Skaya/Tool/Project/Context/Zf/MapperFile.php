<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_Tool
 * @subpackage Framework
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: MapperFile.php 23484 2010-12-10 03:57:59Z mjh_ca $
 */

/**
 * This class is the front most class for utilizing Zend_Tool_Project
 *
 * A profile is a hierarchical set of resources that keep track of
 * items within a specific project.
 *
 * @category   Zend
 * @package    Zend_Tool
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Skaya_Tool_Project_Context_Zf_MapperFile extends Zend_Tool_Project_Context_Zf_AbstractClassFile
{

	/**
	 * @var string
	 */
	protected $_mapperName = 'Base';
	
	protected $_type = null;

	/**
	 * @var string
	 */
	protected $_filesystemName = 'mapperName';

	/**
	 * init()
	 *
	 */
	public function init()
	{
		$this->_mapperName = $this->_resource->getAttribute('mapperName');
		$this->_type = $this->_resource->getAttribute('type');
		$this->_filesystemName = ucfirst($this->_mapperName) . '.php';
		parent::init();
	}

	/**
	 * getPersistentAttributes
	 *
	 * @return array
	 */
	public function getPersistentAttributes()
	{
		$attributes = array(
			'mapperName' => $this->getMapperName()
		);
		if ($type = $this->getType()) {
			$attributes['type'] = $type;
		}
		return $attributes;
	}

	/**
	 * getName()
	 *
	 * @return string
	 */
	public function getName()
	{
		return 'mapperFile';
	}

	public function getMapperName()
	{
		return $this->_mapperName;
	}
	
	public function getType()
	{
		return $this->_type;
	}

	public function getContents()
	{
		$mapperType = (!empty($this->_type))?$this->_type . '_':'';
		$className = $this->getFullClassName($this->getMapperName(), 'Model_Mapper_' . $mapperType);
		$methods = Skaya_Tool_Project_Context_Zf_MapperDecorator_Default::getMapperClassMethods($this);
		$properties = Skaya_Tool_Project_Context_Zf_MapperDecorator_Default::getMapperClassProperties($this);
		if ($mapperType) {
			$mapperDecorator = 'Skaya_Tool_Project_Context_Zf_MapperDecorator_' . ucfirst($this->getType());
			if (class_exists($mapperDecorator, true)) {
				$methods = call_user_func(array($mapperDecorator, 'getMapperClassMethods'), $this);
				$properties = call_user_func(array($mapperDecorator, 'getMapperClassProperties'), $this);
			}
		}

		$codeGenFile = new Zend_CodeGenerator_Php_File(array(
			'fileName' => $this->getPath(),
			'classes' => array(
				new Zend_CodeGenerator_Php_Class(array(
					'name' => $className,
					'extendedClass' => 'Skaya_Model_Mapper_' . $mapperType . 'Abstract',
					'properties' => $properties,
					'methods' => $methods
				))
			)
		));
		return $codeGenFile->generate();
	}


}