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

namespace Contain\Entity;

use Contain\Entity\Exception;
use Traversable;
use Zend\EventManager\Event;
use Zend\EventManager\EventManager;

/**
 * Abstract Entity
 *
 * @category    akandels
 * @package     contain
 * @copyright   Copyright (c) 2012 Andrew P. Kandels (http://andrewkandels.com)
 * @license     http://www.opensource.org/licenses/bsd-license.php BSD License
 */
abstract class AbstractEntity implements EntityInterface
{
    /**
     * @var array
     */
    protected $properties = array();

    /**
     * @var Zend\EventManager\EventManager
     */
    protected $eventManager;

    /**
     * @var array
     */
    protected $extendedProperties = array();

    /**
     * @var boolean
     */
    protected $isPersisted = false;

    /**
     * Constructor
     *
     * @param   array|Traversable               Properties
     * @return  $this
     */
    public function __construct($properties = null)
    {
        $this->init();

        if ($properties) {
            $className = __CLASS__;
            if (is_object($properties) && $properties instanceof $className) {
                $this->fromArray($properties->export());
            } else {
                $this->fromArray($properties);
            }
        }
    }

    /**
     * Placeholder for initializing events or other basic functionaliy.
     *
     * @return $this
     */
    public function init()
    {
        return $this;
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

    /**
     * 'property.get' event that is fired when a property is accessed.
     *
     * @param   string              Property name
     * @param   mixed               Current Value
     * @param   boolean             Is the value presently set?
     * @return  mixed|null
     */
    public function onEventGetter($property, $currentValue, $isValueSet)
    {
        $eventManager = $this->getEventManager();

        $argv = $eventManager->prepareArgs(array('property' => array(
            'property'      => $property,
            'currentValue'  => $currentValue,
            'isSet'         => $isValueSet,
        )));

        $eventManager->trigger('property.get', $this, $argv);

        if (isset($argv['property']['value'])) {
            return $argv['property']['value'];
        }

        return $currentValue;
    }

    /**
     * 'property.set' event when a property is being set.
     *
     * @param   string              Property name
     * @param   mixed               Current Value
     * @param   mixed               New Value
     * @param   boolean             Is the value presently set
     * @return  mixed|null
     */
    public function onEventSetter($property, $currentValue, $newValue, $isValueSet)
    {
        $eventManager = $this->getEventManager();

        $argv = $eventManager->prepareArgs(array('property' => array(
            'property'      => $property,
            'currentValue'  => $currentValue,
            'isSet'         => $isValueSet,
            'value'         => $newValue,
        )));

        $eventManager->trigger('property.set', $this, $argv);

        return $argv['property']['value'];
    }

    /**
     * Fetches an extended property which can be set at anytime.
     *
     * @param   string                  Extended property name
     * @return  mixed
     */
    public function getExtendedProperty($name)
    {
        return $this->onEventGetter(
            $name,
            isset($this->extendedProperties[$name]) ? $this->extendedProperties[$name] : null,
            isset($this->extendedProperties[$name])
        );
    }

    /**
     * Fetches all extended properties.
     *
     * @return  array
     */
    public function getExtendedProperties()
    {
        $result = array();

        foreach ($this->extendedProperties as $name => $value) {
            $result[$name] = $this->getExtendedProperty($name); // fire the event
        }

        return $result;
    }

    /**
     * Injects an extended property which can be set at anytime.
     *
     * @param   string                  Extended property name
     * @param   mixed                   Value to set
     * @return  $this
     */
    public function setExtendedProperty($name, $value)
    {
        $this->extendedProperties[$name] = $this->onEventSetter(
            $name,
            isset($this->extendedProperties[$name]) ? $this->extendedProperties[$name] : null,
            $value,
            isset($this->extendedProperties[$name])
        );

        return $this;
    }

    /**
     * Returns an array of the columns flagged as primary as the
     * key(s) and the current values for the keys as the property
     * values.
     *
     * @return  array(primary => value)
     */
    public function primary()
    {
        $primary = array();

        foreach ($this->properties as $name => $options) {
            $property = $this->property($name);
            if ($property->getOption('primary')) {
                $primary[$name] = $property->getValue();
            }
        }

        return $primary;
    }

    /**
     * Unsets one, some or all properties.
     *
     * @param   string|array|Traversable|null       Propert(y|ies)
     * @return  $this
     */
    public function clear($property = null)
    {
        if (!$property) {
            foreach ($this->properties as $name => $options) {
                $this->property($name)->clear();
            }

            return $this;
        }

        if (is_array($property) || $property instanceof Traversable) {
            foreach ($property as $name) {
                $this->clear($name);
            }

            return $this;
        }

        if ($property = $this->property($property)) {
            $property->clear();
        }

        return $this;
    }

    /**
     * Marks a changed property (or all properties by default) as clean,
     * or unmodified.
     *
     * @param   string|Contain\Entity\Property\Property|array|Traversable|null
     * @return  $this
     */
    public function clean($property = null)
    {
        if (!$property) {
            foreach ($this->properties as $name => $options) {
                $this->property($name)->clean();
            }

            return $this;
        }

        if (is_array($property) || $property instanceof Traversable) {
            foreach ($property as $name) {
                $this->clean($name);
            }

            return $this;
        }

        if ($property = $this->property($property)) {
            $property->clean();
        }

        return $this;
    }

    /**
     * Returns dirty, modified properties and their current values.
     *
     * @return  array
     */
    public function dirty()
    {
        $dirty = array();

        foreach ($this->properties as $name => $options) {
            if ($this->property($name)->isDirty()) {
                $dirty[] = $name;
            }
        }

        return $dirty;
    }

    /**
     * Marks a property as dirty.
     *
     * @param   string                      Property name
     * @return  $this
     */
    public function markDirty($name)
    {
        if ($property = $this->property($name)) {
            $property->setDirty();
        }

        return $this;
    }

    /**
     * Gets the property type for a given property.
     *
     * @param   string|Contain\Entity\Property\Property
     * @return  Contain\Entity\Property\Type\TypeInterface
     */
    public function type($name)
    {
        if ($property = $this->property($name)) {
            return $property->getType()->setOptions($property->getOptions());
        }

        return null;
    }

    /**
     * Gets an array of all the entity's properties.
     *
     * @param   boolean             Include unset properties
     * @return  array
     */
    public function properties($includeUnset = false)
    {
        $result = array();

        foreach ($this->properties as $name => $options) {
            if ($includeUnset || !$this->property($name)->isUnset()) {
                $result[] = $name;
            }
        }

        return $result;
    }

    /**
     * Returns an array of all the entity properties.
     *
     * @param   boolean             Include unset properties
     * @return  array
     */
    public function toArray($includeUnset = false)
    {
        $result = array();

        foreach ($this->properties as $name => $options) {
            $property = $this->property($name);
            if ($includeUnset || !$property->isUnset()) {
                $result[$name] = $property->getValue();
            }
        }

        return $result;
    }

    /**
     * Hydrates entity properties from an array.
     *
     * @param   array|Traversable   Property key/value pairs
     * @return  $this
     */
    public function fromArray($properties)
    {
        if (!is_array($properties) && !$properties instanceof Traversable) {
            throw new Exception\InvalidArgumentException('$properties must be an array or an instance of Traversable.');
        }

        foreach ($properties as $key => $value) {
            if ($property = $this->property($key)) {
                $property->setValue($value);
            }
        }

        return $this;
    }

    /**
     * Returns an array of all the entity properties
     * as an array of string-converted values (no objects).
     *
     * @param   Traversable|array|null              Properties
     * @param   boolean             Include unset properties
     * @return  array
     */
    public function export($includeProperties = null, $includeUnset = false)
    {
        $result = array();

        if ($includeProperties) {
            if ($includeProperties instanceof Traversable) {
                $result = array();
                foreach ($includeProperties as $property) {
                    $result[] = $property;
                }
                $includeProperties = $result;
            } elseif (is_string($includeProperties)) {
                $includeProperties = array($includeProperties);
            } elseif (!is_array($includeProperties)) {
                throw new Exception\InvalidArgumentException('$includeProperties must be null, '
                    . 'a single property, or an array or Traversable object of '
                    . 'properties to export.'
                );
            }
        } else {
            $includeProperties = null;
        }

        foreach ($this->properties as $name => $options) {
            $property = $this->property($name);
            if ($includeProperties && !in_array($name, $includeProperties)) {
                continue;
            }

            if ($includeUnset || !$property->isUnset()) {
                $result[$name] = $property->export();
            }
        }

        return $result;
    }

    /**
     * Fetches the current value for a property.
     *
     * @return  mixed
     */
    public function get($name)
    {
        if ($property = $this->property($name)) {
            return $this->onEventGetter(
                $name,
                $property->getValue(),
                !$property->isUnset()
            );
        }

        return null;
    }

    /**
     * Sets the value of a property.
     *
     * @param   mixed                   Value
     * @return  $this
     */
    public function set($name, $value)
    {
        if ($property = $this->property($name)) {
            $value = $this->onEventSetter(
                $name,
                $property->getValue(),
                $value,
                !$property->isUnset()
            );

            $property->setValue($value);
            return $this;
        }

        return $this;
    }

    /**
     * Retrieves messages with property indexes for validation errors
     * from the last invokation of the isValid method.
     *
     * @return  array
     */
    public function messages()
    {
        return !empty($this->messages) ? $this->messages : array();
    }

    /**
     * Filters and validates some or all properties of the entity. If false,
     * additional messages can be retrieved by invoking the messages method.
     *
     * @param   string|array|Traversable            Properties to filter/validate (omit for all)
     * @return  boolean
     */
    public function isValid($properties = array())
    {
        $this->messages = array();

        if (empty($this->inputFilter)) {
            throw new Exception\RuntimeException('validate failed as no filter class is set in the entity');
        }

        if (!class_exists($this->inputFilter)) {
            throw new Exception\RuntimeException('validate failed as filter class "' . $this->filter . '" does not exist');
        }

        if (is_string($properties)) {
            $properties = array($properties);
        } elseif ($properties instanceof Traversable) {
            $properties = iterator_to_array($properties);
        } elseif (!is_array($properties)) {
            throw new Exception\InvalidArgumentException('$properties must be an array, string, or instance of Traversable');
        }

        $filter = new $this->inputFilter();
        $filter->setData($this->export($properties ? $properties : null));

        $filter->isValid();

        $this->messages = $filter->getMessages();
        $values         = $filter->getValues();

        foreach ($this->messages as $index => $values) {
            if ($properties && !in_array($index, $properties)) {
                unset($this->messages[$index]);
                unset($values[$index]);
            }
        }

        // update properties with filtered values
        foreach ($values as $index => $value) {
            $this->set($index, $value);
        }

        return !(boolean) $this->messages;
    }

    /**
     * Returns true if the entity has been persisted into a data store.
     *
     * @return  boolean
     */
    public function isPersisted()
    {
        return $this->isPersisted;
    }

    /**
     * Flags the entity as being persisted.
     *
     * @param   boolean                 Value
     * @return  $this
     */
    public function persisted($value = true)
    {
        $this->isPersisted = (boolean) $value;
        return $this;
    }

    /**
     * Retrieve property meta-data ensuring indexes are set.
     *
     * @param   string          Property name
     * @return  array|false
     */
    public function property($name)
    {
        $propertyName = '_' . $name;

        if (isset($this->$propertyName)) {
            return $this->$propertyName;
        }

        if (!isset($this->properties[$name])) {
            return false;
        }

        $options = isset($this->properties[$name]['options'])
            ? $this->properties[$name]['options']
            : array();

        $this->$propertyName = new Property\Property(
            $this->properties[$name]['type'],
            $options
        );

        return $this->$propertyName;
    }

    /**
     * Magic method for handling get and set methods on an entity that
     * aren't explicitly defined (which would be ideal).
     *
     * If compiled (recommended), the methods will be declared explicitly leaving this
     * method unused for better performance / code completion support.
     *
     * @param   string              Method
     * @param   array               Variable arguments
     * @return  mixed
     */
    public function __call($method, $args)
    {
        if (preg_match('/^(has|get|set)(.+)$/', $method, $matches)) {
            $property = strtolower($matches[2][0]) . substr($matches[2], 1);

            if ($prop = $this->property($property)) {
                if ($matches[1] == 'has') {
                    if ($prop->isUnset() || $prop->isEmpty()) {
                        return false;
                    }

                    return true;
                }

                if ($matches[1] == 'get') {
                    return $this->get($property);
                }

                return call_user_func_array(array($this, 'set'), array_merge(
                    array($property),
                    $args
                ));
            } else {
                throw new Exception\InvalidArgumentException("'$property' is not a valid "
                    . 'property of ' . get_class($this) . '.'
                );
            }
        }

        throw new Exception\InvalidArgumentException("'$method' is not a valid "
            . 'method for ' . get_class($this) . '.'
        );
    }
}
