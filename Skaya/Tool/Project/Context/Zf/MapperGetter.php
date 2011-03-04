<?php
class Skaya_Tool_Project_Context_Zf_MapperGetter implements Zend_Tool_Project_Context_Interface {

	/**
	 * @var Zend_Tool_Project_Profile_Resource
	 */
	protected $_resource = null;

	/**
	 * @var Zend_Tool_Project_Profile_Resource
	 */
	protected $_mapperResource = null;

	/**
	 * @var string
	 */
	protected $_mapperPath = '';

	/**
	 * @var string
	 */
	protected $_getterName = null;

	/**
	 * init()
	 *
	 * @return Skaya_Tool_Project_Context_Zf_MapperGetter
	 */
	public function init() {
		$this->_getterName = $this->_resource->getAttribute('getterName');

		$this->_resource->setAppendable(false);
		$this->_mapperResource = $this->_resource->getParentResource();
		if (!$this->_mapperResource->getContext() instanceof Skaya_Tool_Project_Context_Zf_MapperFile) {
			require_once 'Zend/Tool/Project/Context/Exception.php';
			throw new Zend_Tool_Project_Context_Exception('mapperGetter must be a sub resource of a mapperFile');
		}
		// make the mapperFile node appendable so we can tack on the mapperGetter.
		$this->_resource->getParentResource()->setAppendable(true);
		$this->_mapperPath = $this->_mapperResource->getContext()->getPath();

		return $this;
	}

	/**
	 * getPersistentAttributes
	 *
	 * @return array
	 */
	public function getPersistentAttributes() {
		return array(
			'getterName' => $this->getGetterName()
		);
	}

	/**
	 * getName()
	 *
	 * @return string
	 */
	public function getName() {
		return 'mapperGetter';
	}

	/**
	 * setResource()
	 *
	 * @param Zend_Tool_Project_Profile_Resource $resource
	 * @return Skaya_Tool_Project_Context_Zf_MapperGetter
	 */
	public function setResource(Zend_Tool_Project_Profile_Resource $resource) {
		$this->_resource = $resource;
		return $this;
	}

	/**
	 * setGetterName()
	 *
	 * @param string $getterName
	 * @return Skaya_Tool_Project_Context_Zf_MapperGetter
	 */
	public function setGetterName($getterName) {
		$this->_getterName = $getterName;
		return $this;
	}

	/**
	 * getGetterName()
	 *
	 * @return string
	 */
	public function getGetterName() {
		return $this->_getterName;
	}

	/**
	 * create()
	 *
	 * @return Skaya_Tool_Project_Context_Zf_MapperGetter
	 */
	public function create() {
		$type = $this->_mapperResource->getType();
		$decorator = 'Skaya_Tool_Project_Context_Zf_MapperDecorator_' . ucfirst($type);
		if (!class_exists($decorator, true)) {
			$decorator = 'Skaya_Tool_Project_Context_Zf_MapperDecorator_Default';
		}

		if (!file_exists($this->_mapperPath)) {
			throw new Zend_Tool_Project_Context_Exception(
				'Could not create getter within mapper ' . $this->_mapperPath
				. ' with getter name ' . $this->_getterName
			);
		}

		$getterName = 'get' . ucfirst($this->_mapperResource->getMapperName()) . 'By' . ucfirst($this->getGetterName());
		if (self::hasGetterMethod($this->_mapperPath, $getterName)) {
			throw new Zend_Tool_Project_Context_Exception(
				'Could not create getter within mapper ' . $this->_mapperPath
				. ' with getter name ' . $this->_getterName
				. '. Getter with such name already exists'
			);
		}

		$mapperCodeGenFile = Zend_CodeGenerator_Php_File::fromReflectedFileName($this->_mapperPath, true, true);
		$getter = call_user_func(
			array($decorator, 'getMapperClassGetterMethod'),
			$this->_mapperResource->getContext(),
			$this->getGetterName()
		);

		if (!$getter) {
			throw new Zend_Tool_Project_Context_Exception(
				'Could not create getter within mapper ' . $this->_mapperPath
				. '. Mappers of this type do not allow getters'
			);
		}

		$mapperCodeGenFile->getClass()->setMethod($getter);
		file_put_contents($this->_mapperPath, $mapperCodeGenFile->generate());

		return $this;
	}

	/**
	 * hasGetterMethod()
	 *
	 * @param string $mapperPath
	 * @param string $getterName
	 * @return bool
	 */
	public static function hasGetterMethod($mapperPath, $getterName) {
		if (!file_exists($mapperPath)) {
			return false;
		}

		$mapperCodeGenFile = Zend_CodeGenerator_Php_File::fromReflectedFileName($mapperPath, true, true);
		return $mapperCodeGenFile->getClass()->hasMethod($getterName);
	}

}
