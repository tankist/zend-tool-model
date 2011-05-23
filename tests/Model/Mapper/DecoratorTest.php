<?php

class Mapper_DecoratorTest extends PHPUnit_Framework_TestCase {

	/**
	 * @var Skaya_Model_Mapper_Abstract
	 */
	protected $_mapper;

	/**
	 * @var Skaya_Model_Mapper_Decorator_Abstract
	 */
	protected $_decorator;

	public function setUp() {
		Skaya_Model_Mapper_MapperBroker::addPath(realpath(dirname(__FILE__)) . '/../mappers/_files/MyApp/Mapper/', 'MyApp_Mapper');
		$broker = Skaya_Model_Mapper_MapperBroker::getInstance();
		$this->_mapper = $broker->getMapper('User', 'Test');
		$this->_decorator = new TestDecorator($this->_mapper);
	}

	public function testType() {
		$this->assertInstanceOf('Skaya_Model_Mapper_Decorator_Abstract', $this->_decorator);
	}

	public function testDecorate() {
		$userdata = $this->_decorator->getUserByUsername('TestUser');
		$this->assertInternalType('array', $userdata);
		$this->assertArrayHasKey('decorated', $userdata);
		$this->assertTrue($userdata['decorated']);
		$userdata = $this->_mapper->getUserByUsername('TestUser');
		$this->assertInternalType('array', $userdata);
		$this->assertArrayNotHasKey('decorated', $userdata);
	}

	public function testMagicCall() {
		$userdata = $this->_mapper->getUserByEmail('test@test.com');
		$this->assertInternalType('array', $userdata);
		$this->assertArrayNotHasKey('decorated', $userdata);
		$userdata = $this->_decorator->getUserByEmail('test@test.com');
		$this->assertInternalType('array', $userdata);
		$this->assertArrayHasKey('decorated', $userdata);
		$this->assertTrue($userdata['decorated']);
	}

	/**
	 * @expectedException Skaya_Model_Mapper_Decorator_Exception
	 * @return void
	 */
	public function testWrongMethodCall() {
		$this->_decorator->callSomethingUndefined();
	}

}

class TestDecorator extends Skaya_Model_Mapper_Decorator_Abstract {

	public function getUserByUsername($username) {
		$userdata = $this->_mapper->getUserByUsername($username);
		$userdata['decorated'] = true;
		return $userdata;
	}

	public function __call($method, $params) {
		$userdata = parent::__call($method, $params);
		if (is_array($userdata)) {
			$userdata['decorated'] = true;
		}
		return $userdata;
	}

}