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
 * @package    Zend_Controller
 * @subpackage Model
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: PriorityStack.php 20096 2010-01-06 02:05:09Z bkarwin $
 */

/**
 * @category   Zend
 * @package    Model
 * @subpackage Mapper
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Skaya_Model_Mapper_MapperBroker_PriorityStack implements IteratorAggregate, ArrayAccess, Countable
{

    protected $_mappersByPriority = array();
    protected $_mappersByNameRef  = array();
    protected $_nextDefaultPriority = 1;

    /**
     * Magic property overloading for returning mapper by name
     *
     * @param string $mapperName    The mapper name
     * @return Skaya_Model_Mapper_Abstract
     */
    public function __get($mapperName)
    {
        if (!array_key_exists($mapperName, $this->_mappersByNameRef)) {
            return false;
        }

        return $this->_mappersByNameRef[$mapperName];
    }

    /**
     * Magic property overloading for returning if mapper is set by name
     *
     * @param string $mapperName    The mapper name
     * @return Skaya_Model_Mapper_Abstract
     */
    public function __isset($mapperName)
    {
        return array_key_exists($mapperName, $this->_mappersByNameRef);
    }

    /**
     * Magic property overloading for unsetting if mapper is exists by name
     *
     * @param string $mapperName    The mapper name
     * @return Skaya_Model_Mapper_Abstract
     */
    public function __unset($mapperName)
    {
        return $this->offsetUnset($mapperName);
    }

    /**
     * push mapper onto the stack
     *
     * @param Skaya_Model_Mapper_Abstract $mapper
     * @return Skaya_Model_MapperBroker_PriorityStack
     */
    public function push(Skaya_Model_Mapper_Abstract $mapper)
    {
        $this->offsetSet($this->getNextFreeHigherPriority(), $mapper);
        return $this;
    }

    /**
     * Return something iterable
     *
     * @return array
     */
    public function getIterator()
    {
        return new ArrayObject($this->_mappersByPriority);
    }

    /**
     * offsetExists()
     *
     * @param int|string $priorityOrMapperName
     * @return Skaya_Model_MapperBroker_PriorityStack
     */
    public function offsetExists($priorityOrMapperName)
    {
        if (is_string($priorityOrMapperName)) {
            return array_key_exists($priorityOrMapperName, $this->_mappersByNameRef);
        } else {
            return array_key_exists($priorityOrMapperName, $this->_mappersByPriority);
        }
    }

    /**
     * offsetGet()
     *
     * @param int|string $priorityOrMapperName
     * @return Skaya_Model_MapperBroker_PriorityStack
     */
    public function offsetGet($priorityOrMapperName)
    {
        if (!$this->offsetExists($priorityOrMapperName)) {
            throw new Skaya_Model_Exception('A mapper with priority ' . $priorityOrMapperName . ' does not exist.');
        }

        if (is_string($priorityOrMapperName)) {
            return $this->_mappersByNameRef[$priorityOrMapperName];
        } else {
            return $this->_mappersByPriority[$priorityOrMapperName];
        }
    }

    /**
     * offsetSet()
     *
     * @param int $priority
     * @param Skaya_Model_Mapper_Abstract $mapper
     * @return Skaya_Model_MapperBroker_PriorityStack
     */
    public function offsetSet($priority, $mapper)
    {
        $priority = (int) $priority;

        if (!$mapper instanceof Skaya_Model_Mapper_Interface) {
            throw new Skaya_Model_Exception('$mapper must extend Skaya_Model_Mapper_Abstract.');
        }

        if (array_key_exists($mapper->getName(), $this->_mappersByNameRef)) {
            // remove any object with the same name to retain BC compailitbility
            // @todo At ZF 2.0 time throw an exception here.
            $this->offsetUnset($mapper->getName());
        }

        if (array_key_exists($priority, $this->_mappersByPriority)) {
            $priority = $this->getNextFreeHigherPriority($priority);  // ensures LIFO
            trigger_error("A mapper with the same priority already exists, reassigning to $priority", E_USER_WARNING);
        }

        $this->_mappersByPriority[$priority] = $mapper;
        $this->_mappersByNameRef[$mapper->getName()] = $mapper;

        if ($priority == ($nextFreeDefault = $this->getNextFreeHigherPriority($this->_nextDefaultPriority))) {
            $this->_nextDefaultPriority = $nextFreeDefault;
        }

        krsort($this->_mappersByPriority);  // always make sure priority and LIFO are both enforced
        return $this;
    }

    /**
     * offsetUnset()
     *
     * @param int|string $priorityOrMapperName Priority integer or the mapper name
     * @return Skaya_Model_MapperBroker_PriorityStack
     */
    public function offsetUnset($priorityOrMapperName)
    {
        if (!$this->offsetExists($priorityOrMapperName)) {
            throw new Skaya_Model_Exception('A mapper with priority or name ' . $priorityOrMapperName . ' does not exist.');
        }

        if (is_string($priorityOrMapperName)) {
            $mapperName = $priorityOrMapperName;
            $mapper = $this->_mappersByNameRef[$mapperName];
            $priority = array_search($mapper, $this->_mappersByPriority, true);
        } else {
            $priority = $priorityOrMapperName;
            $mapperName = $this->_mappersByPriority[$priorityOrMapperName]->getName();
        }

        unset($this->_mappersByNameRef[$mapperName]);
        unset($this->_mappersByPriority[$priority]);
        return $this;
    }

    /**
     * return the count of mappers
     *
     * @return int
     */
    public function count()
    {
        return count($this->_mappersByPriority);
    }

    /**
     * Find the next free higher priority.  If an index is given, it will
     * find the next free highest priority after it.
     *
     * @param int $indexPriority OPTIONAL
     * @return int
     */
    public function getNextFreeHigherPriority($indexPriority = null)
    {
        if ($indexPriority == null) {
            $indexPriority = $this->_nextDefaultPriority;
        }

        $priorities = array_keys($this->_mappersByPriority);

        while (in_array($indexPriority, $priorities)) {
            $indexPriority++;
        }

        return $indexPriority;
    }

    /**
     * Find the next free lower priority.  If an index is given, it will
     * find the next free lower priority before it.
     *
     * @param int $indexPriority
     * @return int
     */
    public function getNextFreeLowerPriority($indexPriority = null)
    {
        if ($indexPriority == null) {
            $indexPriority = $this->_nextDefaultPriority;
        }

        $priorities = array_keys($this->_mappersByPriority);

        while (in_array($indexPriority, $priorities)) {
            $indexPriority--;
        }

        return $indexPriority;
    }

    /**
     * return the highest priority
     *
     * @return int
     */
    public function getHighestPriority()
    {
        return max(array_keys($this->_mappersByPriority));
    }

    /**
     * return the lowest priority
     *
     * @return int
     */
    public function getLowestPriority()
    {
        return min(array_keys($this->_mappersByPriority));
    }

    /**
     * return the mappers referenced by name
     *
     * @return array
     */
    public function getMappersByName()
    {
        return $this->_mappersByNameRef;
    }

}
