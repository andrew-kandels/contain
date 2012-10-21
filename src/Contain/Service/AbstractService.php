<?php
/**
 * Contain Project
 *
 * This source file is subject to the BSD license bundled with
 * this package in the LICENSE.txt file. It is also available
 * on the world-wide-web at http://www.opensource.org/licenses/bsd-license.php.
 * If you are unable to receive a copy of the license or have
 * questions concerning the terms, please send an email to
 * me@andrewkandels.com.
 *
 * @category    akandels
 * @package     contain
 * @author      Andrew Kandels (me@andrewkandels.com)
 * @copyright   Copyright (c) 2012 Andrew P. Kandels (http://andrewkandels.com)
 * @license     http://www.opensource.org/licenses/bsd-license.php BSD License
 * @link        http://andrewkandels.com/contain
 */

namespace Contain\Service;

use Contain\Mapper\Driver\DriverInterface;
use Contain\AbstractQuery;
use Zend\EventManager\Event;
use Zend\EventManager\EventManager;

/**
 * Abstract Service
 *
 * @category    akandels
 * @package     contain
 * @copyright   Copyright (c) 2012 Andrew P. Kandels (http://andrewkandels.com)
 * @license     http://www.opensource.org/licenses/bsd-license.php BSD License
 */
abstract class AbstractService extends AbstractQuery implements ServiceInterface
{
    /**
     * @var Zend\EventManager\EventManager
     */
    protected $eventManager;

    /**
     * Prepares a mapper for a method's invokation. Passes along
     * options, sort, limiting and other query specific
     * attributes.
     *
     * @param   Contain\Mapper\Driver       Mapper
     * @param   boolean                     Clears the query properties after prepping
     * @return  Contain\Mapper\Driver
     */
    public function prepare(AbstractQuery $query, $clearProperties = true)
    {
        if ($this->limit !== null) {
            $query->limit($this->limit);
        }

        if ($this->skip !== null) {
            $query->skip($this->skip);
        }

        if ($this->sort !== null) {
            $query->sort($this->sort);
        }

        $query->setOptions($this->getOptions())
              ->properties($this->getProperties());

        // reset above options to a blank state
        if ($clearProperties) {
            $this->clear();
        }

        return $query;
    }

    /**
     * Retrieves an instance of the Zend Framework event manager in order to
     * register or trigger events.
     *
     * @return  Zend\EventManager\EventManager
     */
    public function getEventManager()
    {
        if (!$this->eventManager) {
            $this->setEventManager(new EventManager());
        }

        return $this->eventManager;
    }

    /**
     * Retrieves an instance of the Zend Framework event manager in order to
     * register or trigger events.
     *
     * @param   Zend\EventManager\EventManager
     * @return  $this
     */
    public function setEventManager(EventManager $eventManager)
    {
        $this->eventManager = $eventManager;
        return $this;
    }
}
