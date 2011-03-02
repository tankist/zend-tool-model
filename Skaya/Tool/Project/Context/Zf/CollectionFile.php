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
 * @version    $Id: CollectionFile.php 23484 2010-12-10 03:57:59Z mjh_ca $
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
class Skaya_Tool_Project_Context_Zf_CollectionFile extends Zend_Tool_Project_Context_Zf_AbstractClassFile
{

	/**
	 * @var string
	 */
	protected $_collectionName = 'Base';
	
	protected $_itemType = 'Base';

	/**
	 * @var string
	 */
	protected $_filesystemName = 'collectionName';

	/**
	 * init()
	 *
	 */
	public function init()
	{
		$this->_collectionName = $this->_resource->getAttribute('collectionName');
		$this->_itemType = $this->_resource->getAttribute('itemType');
		$this->_filesystemName = ucfirst($this->_collectionName) . '.php';
		parent::init();
	}

	/**
	 * getPersistentAttributes
	 *
	 * @return array
	 */
	public function getPersistentAttributes()
	{
		return array(
			'collectionName' => $this->getCollectionName(),
			'itemType' => $this->getItemType(),
			);
	}

	/**
	 * getName()
	 *
	 * @return string
	 */
	public function getName()
	{
		return 'CollectionFile';
	}

	public function getCollectionName()
	{
		return $this->_collectionName;
	}

	public function getItemType()
	{
		return $this->_itemType;
	}

	public function getContents()
	{

		$className = $this->getFullClassName($this->_collectionName, 'Model_Collection');

		$codeGenFile = new Zend_CodeGenerator_Php_File(array(
			'fileName' => $this->getPath(),
			'classes' => array(
				new Zend_CodeGenerator_Php_Class(array(
						'name' => $className,
						'extendedClass' => 'Skaya_Model_Collection_Abstract',
						'properties' => array(
							new Zend_CodeGenerator_Php_Property(array(
								'name' => '_itemType',
								'visibility' => Zend_CodeGenerator_Php_Property::VISIBILITY_PROTECTED,
								'defaultValue' => 'Model_' . $this->getItemType()
							))
						)
					))
				)
			));
		return $codeGenFile->generate();
	}


}