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

namespace Contain\Entity;

use Contain\Entity\Exception;
use Contain\Entity\Property\Type;
use Closure;
use Contain\Event;
use Traversable;

/**
 * Abstract Entity
 *
 * @category    akandels
 * @package     contain
 * @copyright   Copyright (c) 2013 Andrew P. Kandels (http://andrewkandels.com)
 * @license     http://www.opensource.org/licenses/bsd-license.php BSD License
 */
abstract class AbstractEntity implements EntityInterface
{
    /**
     * @var array
     */
    protected $properties = array();

    /**
     * @var array
     */
    protected $events = array();

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
        $this->fromArray($properties);
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
     * 'property.get' event that is fired when a property is accessed.
     *
     * @param   string              Property name
     * @param   mixed               Current Value
     * @return  mixed|null
     */
    public function onEventGetter($name, $value)
    {
        $params = $this->trigger('property.get', array(
            'property'     => $this->property($name),
            'name'         => $name,
            'value'        => $value,
        ));

        return $params['value'];
    }

    /**
     * 'property.set' event when a property is being set.
     *
     * @param   string              Property name
     * @param   mixed               Current Value
     * @param   mixed               New Value
     * @return  mixed|null
     */
    public function onEventSetter($name, $currentValue, $newValue)
    {
        $params = $this->trigger('property.set', array(
            'property'      => $this->property($name),
            'name'          => $name,
            'currentValue'  => $currentValue,
            'value'         => $newValue,
        ));

        return $params['value'];
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
            isset($this->extendedProperties[$name]) ? $this->extendedProperties[$name] : null
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
     * Clears all extended properties.
     *
     * @return  $this
     */
    public function clearExtendedProperties($properties = null)
    {
        if ($properties && !is_array($properties) && !$properties instanceof Traversable) {
            throw new Exception\InvalidArgumentException('$properties must be an array or an instance of Traversable.');
        }

        if ($properties) {
            foreach ($properties as $property) {
                unset($this->extendedProperties[$property]);
            }
        } else {
            $this->extendedProperties = array();
        }

        return $this;
    }

    /**
     * Completely resets all properties and data for the entity.
     *
     * @return  $this
     */
    public function reset()
    {
        $this->events = array();
        $this->clearExtendedProperties()
             ->clear()
             ->persisted(false)
             ->clean();

        return $this;
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
                $this->clean($name);
            }

            return $this;
        }

        if (is_array($property) || $property instanceof Traversable) {
            foreach ($property as $name) {
                $this->clean($name);
            }

            return $this;
        }

        if (!$property = $this->property($name = $property)) {
            return $this;
        }

        $property->clean();
        $this->trigger('clean', array(
            'property' => $property,
            'name'     => $name,
        ));

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
        if (!$property = $this->property($name)) {
            return $this;
        }

        $property->setDirty();
        $this->trigger('dirty', array(
            'property' => $property,
            'name'     => $name,
        ));

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
    public function fromArray($properties = null)
    {
        if (!$properties) {
            return $this;
        }

        $className = __CLASS__;
        if (is_object($properties) && $properties instanceof $className) {
            $properties = $properties->export();
        }

        if (!is_array($properties) && !$properties instanceof Traversable) {
            throw new Exception\InvalidArgumentException('$properties must be an array or an instance of Traversable.');
        }

        foreach ($properties as $key => $value) {
            if ($property = $this->property($key)) {
                $this->set($key, $value);
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
                $property->getValue()
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
            $this->trigger('change', array(
                'property' => $property,
                'name'     => $name,
            ));

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
            throw new Exception\RuntimeException('validate failed as filter class "' . $this->inputFilter . '" does not exist');
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
     * Defines a new property for this entity.
     *
     * @param   string                      Name of property
     * @param   string                      Type (keyword or FQCN)
     * @param   array                       Options
     * @return  $this
     */
    public function define($property, $type, array $options = array())
    {
        $this->properties[$property] = array(
            'type' => $type,
            'options' => $options,
        );

        return $this;
    }

    /**
     * Retrieve property meta-data ensuring indexes are set. The property is managed through 
     * a Property class which is lazy-loaded on demand.
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
     * Fetches a list item by its numerical index position.
     *
     * @param   string                          Property name
     * @param   integer                         Index
     * @return  mixed|null                      Value or null if unset
     */
    public function at($name, $index)
    {
        if (!$property = $this->property($name)) {
            throw new Exception\RuntimeException('Specified $property does not exist');
        }

        if (!$property->getType() instanceof Type\ListType &&
            !$property->getType() instanceof Type\HashType) {
            throw new Exception\RuntimeException('indexOf failed as property type is not a list');
        }

        $value = $this->get($name);

        if ($value instanceof \ContainMapper\Cursor) {
            $value = $value->toArray();
        }

        return (isset($value[$index]) ? $value[$index] : null);
    }

    /**
     * Searches for a value and returns its index or FALSE if not found.
     *
     * @param   string                          Property name
     * @param   mixed                           Value to search for
     * @param   boolean                         Strict type checking
     * @return  integer|false
     */
    public function indexOf($name, $value, $strict = false)
    {
        if (!$property = $this->property($name)) {
            throw new Exception\RuntimeException('Specified $property does not exist');
        }

        if (!$property->getType() instanceof Type\ListType &&
            !$property->getType() instanceof Type\HashType) {
            throw new Exception\RuntimeException('indexOf failed as property type is not a list');
        }

        $arr   = $this->get($name) ?: array();
        $value = $property->getType()->getType()->parse($value);

        if ($property->getType() instanceof Type\ListEntityType) {
            if ($arr instanceof \ContainMapper\Cursor) {
                $arr = $arr->toArray();
            }

            if ($value instanceof \ContainMapper\Cursor) {
                $value = $value->toArray();
            }
        }

        return array_search($value, $arr, $strict);
    }

    /**
     * Prepends a value to a list property.
     *
     * @param   string                          Property name
     * @param   mixed                           Value to prepend
     * @return  $this
     */
    public function unshift($name, $value)
    {
        if (!$property = $this->property($name)) {
            throw new Exception\RuntimeException('Specified $property does not exist');
        }

        if (!$property->getType() instanceof Type\ListType) {
            throw new Exception\RuntimeException('unshift failed as property type is not a list');
        }

        $value = $property->getType()->getType()->parse($value);
        $arr   = $this->get($name) ?: array();

        if ($property->getType() instanceof Type\ListEntityType &&
            $arr instanceof \ContainMapper\Cursor) {
            $arr = $arr->toArray();
        }

        array_unshift($arr, $value);

        $this->set($name, $arr);

        return $this;
    }

    /**
     * Appends a value to a list property.
     *
     * @param   string                          Property name
     * @param   mixed                           Value to append
     * @return  $this
     */
    public function push($name, $value)
    {
        if (!$property = $this->property($name)) {
            throw new Exception\RuntimeException('Specified $property does not exist');
        }

        if (!$property->getType() instanceof Type\ListType) {
            throw new Exception\RuntimeException('push failed as property type is not a list');
        }

        $value = $property->getType()->getType()->parse($value);
        $arr   = $this->get($name) ?: array();

        if ($property->getType() instanceof Type\ListEntityType &&
            $arr instanceof \ContainMapper\Cursor) {
            $arr = $arr->toArray();
        }

        array_push($arr, $value);

        $this->set($name, $arr);

        return $this;
    }
  
    /**
     * Removes a property from the end of a list and returns it.
     *
     * @param   string                          Property name
     * @return  mixed                           List item (now removed)
     */
    public function pop($name)
    {
        if (!$property = $this->property($name)) {
            throw new Exception\RuntimeException('Specified $property does not exist');
        }

        if (!$property->getType() instanceof Type\ListType) {
            throw new Exception\RuntimeException('pop failed as property type is not a list');
        }

        $arr = $this->get($name) ?: array();

        if ($property->getType() instanceof Type\ListEntityType &&
            $arr instanceof \ContainMapper\Cursor) {
            $arr = $arr->toArray();
        }

        $return = array_pop($arr, $value);

        $this->set($name, $arr);

        return $return;
    }

    /**
     * Removes a property from the beginning of a list and returns it.
     *
     * @param   string                          Property name
     * @return  mixed                           List item (now removed)
     */
    public function shift($name)
    {
        if (!$property = $this->property($name)) {
            throw new Exception\RuntimeException('Specified $property does not exist');
        }

        if (!$property->getType() instanceof Type\ListType) {
            throw new Exception\RuntimeException('shift failed as property type is not a list');
        }

        $arr = $this->get($name) ?: array();

        if ($property->getType() instanceof Type\ListEntityType &&
            $arr instanceof \ContainMapper\Cursor) {
            $arr = $arr->toArray();
        }

        $return = array_shift($arr, $value);

        $this->set($name, $arr);

        return $return;
    }

    /**
     * Extracts a slice of the list.
     *
     * @param   string                          Property name
     * @param   integer                         Offset
     * @param   integer|null                    Length
     * @return  array
     */
    public function slice($name, $offset, $length = null)
    {
        if (!$property = $this->property($name)) {
            throw new Exception\RuntimeException('Specified $property does not exist');
        }

        if (!$property->getType() instanceof Type\ListType) {
            throw new Exception\RuntimeException('slice failed as property type is not a list');
        }

        $arr = $this->get($name) ?: array();

        if ($property->getType() instanceof Type\ListEntityType &&
            $arr instanceof \ContainMapper\Cursor) {
            $arr = $arr->toArray();
        }

        return array_slice($arr, $offset, $length);
    }

    /**
     * Merges the list with another array.
     *
     * @param   string                          Property name
     * @param   array                           Array to merge with
     * @param   boolean                         True if existing list is the source vs. target
     * @return  $this
     */
    public function merge($name, $arr, $source = true)
    {
        if (!$property = $this->property($name)) {
            throw new Exception\RuntimeException('Specified $property does not exist');
        }

        if (!$property->getType() instanceof Type\ListType) {
            throw new Exception\RuntimeException('merge failed as property type is not a list');
        }

        $source = $this->get($name);
        $target = $property->getType()->parse($arr);

        if ($property->getType() instanceof Type\ListEntityType) {
            if ($source instanceof \ContainMapper\Cursor) {
                $source = $source->toArray();
            }

            if ($target instanceof \ContainMapper\Cursor) {
                $target = $target->toArray();
            }
        }

        if ($source) {
            $arr = array_merge($source, $target);
        } else {
            $arr = array_merge($target, $source);
        }

        $this->set($name, $arr);

        return $this;
    }

    /**
     * Removes a single item from the list by value if it exists.
     *
     * @param   string                          Property name
     * @param   mixed                           Value to remove
     * @return  $this
     */
    public function remove($name, $value)
    {
        if (!$property = $this->property($name)) {
            throw new Exception\RuntimeException('Specified $property does not exist');
        }

        if (!$property->getType() instanceof Type\ListType) {
            throw new Exception\RuntimeException('remove failed as property type is not a list');
        }

        if (false === ($index = $this->indexOf($name, $value))) {
            return $this;
        }

        $arr = $this->get($name);
        unset($arr[$index]);
        $this->set($name, $arr);

        return $this;
    }

    /**
     * Adds an item to the list if it doesn't already exist.
     *
     * @param   string                          Property name
     * @param   mixed                           Value to add
     * @param   boolean                         True for prepend, false for append
     * @return  $this
     */
    public function add($name, $value, $prepend = true)
    {
        if (!$property = $this->property($name)) {
            throw new Exception\RuntimeException('Specified $property does not exist');
        }

        if (!$property->getType() instanceof Type\ListType) {
            throw new Exception\RuntimeException('add failed as property type is not a list');
        }

        if (false !== ($index = $this->indexOf($name, $value))) {
            return $this;
        }

        if ($prepend) {
            $this->push($name, $value);
        } else {
            $this->unshift($name, $value);
        }

        return $this;
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

    /**
     * Attaches a lightweight listener to the entity as a callback with an optional priority. 
     *
     * 2013-04-03: This no longer uses the ZF2 event manager as it's very expensive to create an EM
     * for each entity. Using a factory or service locator to create entities with a shared EM 
     * goes against my design for them being lightweight, fully self-contained, throw-away
     * value dumps. I may revisit this in the future and am open to other ideas; but performance
     * is key here and ZF2 was almost 10-20x slower in a recent implementation.
     *
     * @param   string                          Event name
     * @param   Closure|array                   Callback method/closure
     * @param   integer                         Priority
     * @return  $this
     */
    public function attach($event, $callback, $priority = 0)
    {
        if (!isset($this->events[$event])) {
            $this->events[$event] = array();
        }

        $this->events[$event][] = array(
            $priority,
            $callback,
        );

        return $this;
    }

    /** 
     * Triggers an event by name, invoking any callbacks registered with attach() in 
     * the order of their priority, passing an instance of Contain\Event which stores
     * the parameters and allows for short circuiting and other utility.
     *
     * @param   string                          Event name
     * @param   array                           Key/value Parameters
     * @return  array                           (Possibly) Modified Parameters
     */
    public function trigger($event, array $params = array())
    {
        if (!isset($this->events[$event])) {
            return $params;
        }

        $e      = new Event($this, $event, $params);
        $events = $this->events[$event];

        if (count($events) > 1) {
            usort($events, function ($a, $b) {
                return $a[0] > $b[0];
            });
        }

        foreach ($events as $item) {
            list($priority, $callback) = $item;

            if ($callback instanceof Closure) {
                $callback($e);
            } elseif (is_array($callback)) {
                call_user_func($callback, $e);
            }

            if (!$e->shouldPropogate()) {
                break;
            }
        }

        return $e->getParams();
    }

    /** 
     * Clears all event listeners attach()'d to an event.
     *
     * @param   string                          Event name
     * @return  $this
     */
    public function clearListeners($event)
    {
        unset($this->events[$event]);
        return $this;
    }
}
