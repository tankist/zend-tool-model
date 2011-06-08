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
 * @version    $Id: ModelFile.php 23484 2010-12-10 03:57:59Z mjh_ca $
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
class Skaya_Tool_Project_Context_Zf_ModelFile 
		extends Zend_Tool_Project_Context_Zf_ModelFile {

	public function getContents()
	{

		$className = $this->getFullClassName($this->_modelName, 'Model');

		$codeGenFile = new Zend_CodeGenerator_Php_File(array(
			'fileName' => $this->getPath(),
			'classes' => array(
				new Zend_CodeGenerator_Php_Class(array(
					'ExtendedClass' => 'Skaya_Model_Abstract',
					'Name' => $className,
					'Properties' => array(
						new Zend_CodeGenerator_Php_Property(array(
							'Name' => '_modelName',
							'Visibility' => Zend_CodeGenerator_Php_Property::VISIBILITY_PROTECTED,
							'DefaultValue' => $this->_modelName
						))
					)
				))
			)
		));
		return $codeGenFile->generate();
	}


}