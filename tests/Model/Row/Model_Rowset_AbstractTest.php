<?php

class Model_Rowset_AbstractTest extends ControllerTestCase {

	/**
     * @var Model_Rowset_Abstract
     */
	protected $_object;

	protected $_data = array(
        array(
            'key' => '12',
            'field1' => 'testField',
            'field2' => '34.5',
            'field3' => '2008',
            'field4' => null
        ),
        array(
            'key' => '34',
            'field1' => 'testField32',
            'field2' => '125.5',
            'field3' => '2005',
            'field4' => 'chhx'
        ),
        array(
            'key' => '658',
            'field1' => 'lastTestField',
            'field2' => '65.98',
            'field3' => '1997',
            'field4' => 'bprt'
        ),
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
        $this->_object = new TestRowset(array(
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
		$this->assertType('TestRowset', $this->_object);
	}

    public function testTypeHinting() {
        $row = $this->_object[0];
        
        $this->assertType('integer', $row->key);
        $this->assertType('string', $row->field1);
        $this->assertType('float', $row->field2);
        $this->assertType('int', $row->field3);
        $this->assertType('null', $row->field4);
    }

    public function testValuesConvertion() {
        $row = $this->_object[0];
        
        $this->assertEquals(12, $row->key);
        $this->assertEquals('testField', $row->field1);
        $this->assertEquals(34.5, $row->field2);
        $this->assertEquals(2008, $row->field3);
        $this->assertNull($row->field4);
    }

}

require_once APPLICATION_PATH . '/../tests/application/models/_files/RowMocks.php';