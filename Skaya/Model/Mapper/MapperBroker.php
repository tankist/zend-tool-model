<?php
/**
 * @package    Model
 * @subpackage Skaya_Model_Mapper
 */
class Skaya_Model_Mapper_MapperBroker
{
	/**
	 * @var Zend_Loader_PluginLoader_Interface
	 */
	protected static $_pluginLoader;

	/**
	 * $_Mappers - Mapper array
	 *
	 * @var array
	 */
	protected static $_stack = array();
	
	/**
	 * Default mapper provider
	 * @var string
	 */
	protected static $_defaultProvider;

	/**
	 * Default instance of the broker
	 * @var Skaya_Model_Mapper_MapperBroker
	 */
	protected static $_instance = null;
	
	/**
	 * Sets default mapper provider
	 * @param string $provider 
	 */
	public static function setDefaultProvider($provider) {
		self::$_defaultProvider = $provider;
	}
	
	/**
	 * Returns default mapper provider
	 * @return string
	 */
	public static function getDefaultProvider() {
		return self::$_defaultProvider;
	}

	/**
	 * Set PluginLoader for use with broker
	 *
	 * @param  Zend_Loader_PluginLoader_Interface $loader
	 * @return void
	 */
	public static function setPluginLoader($loader)
	{
		if ((null !== $loader) && (!$loader instanceof Zend_Loader_PluginLoader_Interface)) {
			throw new Skaya_Model_Mapper_Exception('Invalid plugin loader provided to MapperBroker');
		}
		self::$_pluginLoader = $loader;
	}

	/**
	 * Retrieve PluginLoader
	 *
	 * @return Zend_Loader_PluginLoader
	 */
	public static function getPluginLoader()
	{
		if (null === self::$_pluginLoader) {
			self::$_pluginLoader = new Zend_Loader_PluginLoader();
		}
		return self::$_pluginLoader;
	}

	/**
	 * addPrefix() - Add repository of Mappers by prefix
	 *
	 * @param string $prefix
	 */
	static public function addPrefix($prefix)
	{
		$prefix = rtrim($prefix, '_');
		$path   = str_replace('_', DIRECTORY_SEPARATOR, $prefix);
		self::getPluginLoader()->addPrefixPath($prefix, $path);
	}

	/**
	 * addPath() - Add path to repositories where Action_Mappers could be found.
	 *
	 * @param string $path
	 * @param string $prefix Optional; defaults to 'Zend_Controller_Action_Mapper'
	 * @return void
	 */
	static public function addPath($path, $prefix = 'Model_Mapper')
	{
		self::getPluginLoader()->addPrefixPath($prefix, $path);
	}

	/**
	 * addMapper() - Add Mapper objects
	 *
	 * @param Skaya_Model_Mapper_Interface $mapper
	 * @return void
	 */
	static public function addMapper(Skaya_Model_Mapper_Interface $mapper)
	{
		self::getStack($mapper->getProvider())->push($mapper);
		return;
	}

	/**
	 * resetMappers()
	 *
	 * @return void
	 */
	static public function resetMappers($provider = null)
	{
		if ($provider == null) {
			self::$_stack = null;
		}
		else {
			$provider = self::_normalizeMapperName($provider);
			self::$_stack[$provider] = null;
		}
		return;
	}

	/**
	 * Retrieve or initialize a Mapper statically
	 *
	 * Retrieves a Mapper object statically, loading on-demand if the Mapper
	 * does not already exist in the stack. Always returns a Mapper, unless
	 * the Mapper class cannot be found.
	 *
	 * @param  string $name
	 * @return Skaya_Model_Mapper_Interface
	 */
	public static function getStaticMapper($name, $provider = null)
	{
		$name  = self::_normalizeMapperName($name);
		$stack = self::getStack($provider);

		if (!isset($stack->{$name})) {
			self::_loadMapper($name, $provider);
		}

		return $stack->{$name};
	}

	/**
	 * getExistingMapper() - get Mapper by name
	 *
	 * Static method to retrieve Mapper object. Only retrieves Mappers already
	 * initialized with the broker (either via addMapper() or on-demand loading
	 * via getMapper()).
	 *
	 * Throws an exception if the referenced Mapper does not exist in the
	 * stack; use {@link hasMapper()} to check if the Mapper is registered
	 * prior to retrieving it.
	 *
	 * @param  string $name
	 * @return Skaya_Model_Mapper_Interface
	 * @throws Skaya_Model_Exception
	 */
	public static function getExistingMapper($name, $provider = null)
	{
		$name  = self::_normalizeMapperName($name);
		$stack = self::getStack($provider);

		if (!isset($stack->{$name})) {
			throw new Skaya_Model_Mapper_Exception('Mapper "' . $name . '" has not been registered with the Mapper broker');
		}

		return $stack->{$name};
	}

	/**
	 * Return all registered Mappers as Mapper => object pairs
	 *
	 * @return array
	 */
	public static function getExistingMappers($provider = null)
	{
		return self::getStack($provider)->getMappersByName();
	}

	/**
	 * Is a particular Mapper loaded in the broker?
	 *
	 * @param  string $name
	 * @return boolean
	 */
	public static function hasMapper($name, $provider = null)
	{
		$name = self::_normalizeMapperName($name);
		return isset(self::getStack($provider)->{$name});
	}

	/**
	 * Remove a particular Mapper from the broker
	 *
	 * @param  string $name
	 * @return boolean
	 */
	public static function removeMapper($name, $provider = null)
	{
		$name = self::_normalizeMapperName($name);
		$stack = self::getStack($provider);
		if (isset($stack->{$name})) {
			unset($stack->{$name});
            return true;
		}

		return false;
	}

	/**
	 * Lazy load the priority stack and return it
	 *
	 * @return Zend_Controller_Action_MapperBroker_PriorityStack
	 */
	public static function getStack($provider = null)
	{
		if ($provider == null) {
			$provider = self::getDefaultProvider();
		}
		$provider = self::_normalizeMapperName($provider);
		if (self::$_stack === null ||
			!array_key_exists($provider, self::$_stack) ||
			!(self::$_stack[$provider] instanceof Skaya_Model_Mapper_MapperBroker_PriorityStack)) {
			self::$_stack[$provider] = new Skaya_Model_Mapper_MapperBroker_PriorityStack();
		}

		return self::$_stack[$provider];
	}

	/**
	 * Constructor
	 *
	 * @return void
	 */
	protected function __construct() {}

	/**
	 * Singleton
	 * @static
	 * @return Skaya_Model_Mapper_MapperBroker
	 */
	public static function getInstance() {
		if (empty(self::$_instance)) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * getMapper() - get Mapper by name
	 *
	 * @param  string $name
	 * @return Skaya_Model_Mapper_Interface
	 */
	public function getMapper($name, $provider = null)
	{
		$name  = self::_normalizeMapperName($name);

		if (!$provider) {
			$provider = self::getDefaultProvider();
		}
		$provider = self::_normalizeMapperName($provider);
		$stack = self::getStack($provider);

		if (!isset($stack->{$name})) {
			self::_loadMapper($name, $provider);
		}

		$mapper = $stack->{$name};

		return $mapper;
	}

	/**
	 * Method overloading
	 *
	 * @param  string $method
	 * @param  array $args
	 * @return Skaya_Model_Mapper_Interface
	 * @throws Skaya_Model_Exception if no mapper exists
	 */
	public function __call($method, $args)
	{
		$provider = array_shift($args);
		return $this->getMapper($method, $provider);
	}

	/**
	 * Retrieve Mapper by name as object property
	 *
	 * @param  string $name
	 * @return Skaya_Model_Mapper_Interface
	 */
	public function __get($name)
	{
		return $this->getMapper($name);
	}

	/**
	 * Normalize Mapper name for lookups
	 *
	 * @param  string $name
	 * @return string
	 */
	protected static function _normalizeMapperName($name)
	{
		if (strpos($name, '_') !== false) {
			$name = str_replace(' ', '', ucwords(str_replace('_', ' ', $name)));
		}

		return ucfirst($name);
	}

	/**
	 * Load a Mapper
	 *
	 * @param  string $name
	 * @return void
	 */
	protected static function _loadMapper($name, $provider = null)
	{
		try {
			if ($provider == null) {
				$provider = self::getDefaultProvider();
			}
			$provider = ucfirst($provider);
			$className = $provider . '_' . ucfirst($name);
			$class = self::getPluginLoader()->load($className);
		} catch (Zend_Loader_PluginLoader_Exception $e) {
			throw new Skaya_Model_Mapper_Exception('Mapper by name ' . $className . ' not found', 0, $e);
		}

		$mapper = new $class();

		if (!($mapper instanceof Skaya_Model_Mapper_Interface)) {
			throw new Skaya_Model_Mapper_Exception('Mapper name ' . $name . ' -> class ' . $class . ' is not of type Skaya_Model_Mapper_Interface');
		}

		$mapper->init();

		self::getStack($provider)->push($mapper);
	}
}
