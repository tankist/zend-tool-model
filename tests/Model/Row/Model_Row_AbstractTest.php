<?php

class Model_Row_AbstractTest extends PHPUnit_Framework_TestCase {

	/**
     * @var Model_Row_Abstract
     */
	protected $_object;

	protected $_data = array(
		'key' => '12',
		'field1' => 'testField',
		'field2' => '34.5',
		'field3' => '2008',
		'field4' => null
	);

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp() {
        parent::setUp();
        Zend_Db_Table_Abstract::setDefaultAdapter(
            $this->getMockForAbstractClass('TestAdapter', array(
                array(
                    'dbname' => 'test',
                    'username' => 'test',
                    'password' => 'test'
                )
            ))
        );
        $this->_object = new TestRow(array(
            'table' => new TestTable(),
            'data' => $this->_data
        ));
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown() {

    }

	public function testClassConstructor() {
		$this->assertInstanceOf('TestRow', $this->_object);
	}

    public function testTypeHinting() {
        $this->assertInternalType('integer', $this->_object->key);
        $this->assertInternalType('string', $this->_object->field1);
        $this->assertInternalType('float', $this->_object->field2);
        $this->assertInternalType('int', $this->_object->field3);
        $this->assertInternalType('null', $this->_object->field4);
    }

    public function testValuesConvertion() {
        $this->assertEquals(12, $this->_object->key);
        $this->assertEquals('testField', $this->_object->field1);
        $this->assertEquals(34.5, $this->_object->field2);
        $this->assertEquals(2008, $this->_object->field3);
        $this->assertNull($this->_object->field4);
    }

}

require_once TESTS_PATH . '/Model/Row/_files/RowMocks.php';