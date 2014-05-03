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
 * @copyright   Copyright (c) 2013 Andrew P. Kandels (http://andrewkandels.com)
 * @license     http://www.opensource.org/licenses/bsd-license.php BSD License
 * @link        http://andrewkandels.com/contain
 */

namespace Contain;

use InvalidArgumentException;
use Contain\Entity\EntityInterface;

/**
 * Lightweight Event Container
 *
 * @category    akandels
 * @package     contain
 * @copyright   Copyright (c) 2013 Andrew P. Kandels (http://andrewkandels.com)
 * @license     http://www.opensource.org/licenses/bsd-license.php BSD License
 */
class Event
{
    /**
     * @var string
     */
    protected $event;

    /**
     * @var array
     */
    protected $parameters = array();

    /**
     * @var boolean
     */
    protected $shouldPropogate = true;

    /**
     * @var Contain\Entity\EntityInterface
     */
    protected $target;

    /**
     * Constructor
     *
     * @param   array|Traversable               Properties
     * @return self
     */
    public function __construct(EntityInterface $entity, $event, array $params = array())
    {
        $this->entity     = $entity;
        $this->event      = $event;
        $this->parameters = $params;
    }

    /**
     * Stops event propogation and short circuits the execution of any
     * events that fall after this one.
     *
     * @return self
     */
    public function stopPropogation()
    {
        $this->shouldPropogate = false;
        return $this;
    }

    /**
     * Answers whether or not this event should continue to be propogated
     * to event listeners.
     *
     * @return  boolean
     */
    public function shouldPropogate()
    {
        return $this->shouldPropogate;
    }

    /**
     * Returns the event that was invoked.
     *
     * @return  string
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * Retrieves a parameter by name.
     *
     * @param   string                          Key
     * @return  mixed
     */
    public function getParam($name)
    {
        return isset($this->parameters[$name]) 
            ? $this->parameters[$name]
            : null;
    }

    /**
     * Retrieves a parameters.
     *
     * @return  array
     */
    public function getParams()
    {
        return $this->parameters;
    }

    /**
     * Sets a parameter by name.
     *
     * @param   string                          Key
     * @param   mixed                           Value
     * @return self
     * @throws  InvalidArgumentException
     */
    public function setParam($name, $value)
    {
        if (!isset($this->parameters[$name])) {
            throw new InvalidArgumentException('"' . $name . '" is not a valid key in the parameters array');
        }

        $this->parameters[$name] = $value;
        return $this;
    }

    /**
     * Sets parameters from a key/value array.
     *
     * @param   array                           Key/value pairs
     * @return self
     */
    public function setParams($params)
    {
        foreach ($params as $key => $value) {
            $this->setParam($key, $value);
        }

        return $this;
    }

    /**
     * Returns the entity target.
     *
     * @return  Contain\Entity\EntityInterface
     */
    public function getTarget()
    {
        return $this->entity;
    }
}
