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

use Closure;
use Contain\Entity\Exception;
use Contain\Entity\Property\Property;
use Contain\Entity\Property\Type;
use Contain\Event;
use Contain\Manager\TypeManager;
use ContainMapper\Cursor;
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
    protected $aliases = array();

    /**
     * @var \Contain\Entity\Property\Property[]
     */
    protected $properties = array();

    /**
     * @var \Contain\Entity\Property\Property
     */
    protected $property;

    /**
     * @var TypeManager
     */
    protected $typeManager;

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
     * @var string[]
     */
    protected $messages = array();

    /**
     * Constructor
     *
     * @param   array|Traversable               Properties
     * @return self
     */
    public function __construct($properties = null)
    {
        $this->init();
        $this->fromArray($properties);
    }

    /**
     * Gets or sets a type manager.
     *
     * @param TypeManager|null $manager
     *
     * @return TypeManager
     */
    public function typeManager(TypeManager $manager = null)
    {
        if ($manager) {
            $this->typeManager = $manager;
        }

        if (!$this->typeManager) {
            $this->typeManager = new TypeManager();
        }

        return $this->typeManager;
    }

    /**
     * Placeholder for initializing events or other basic functionaliy.
     *
     * @return self
     */
    public function init()
    {
        return $this;
    }

    /**
     * 'property.get' event that is fired when a property is accessed.
     *
     * @param   string $name  Property name
     * @param   mixed  $value Current Value
     *
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
     * @param string $name  Property name
     * @param mixed  $value New Value
     *
     * @return mixed|null
     */
    public function onEventSetter($name, $value)
    {
        $params = $this->trigger('property.set', array(
            'property'      => $this->property($name),
            'name'          => $name,
            'value'         => $value,
        ));

        return $params['value'];
    }

    /**
     * {@inheritDoc}
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
     * @param array|Traversable|null $properties
     *
     * @return self
     *
     * @throws Exception\RuntimeException
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
     * {@inheritDoc}
     */
    public function reset()
    {
        $this->events = array();
        $this->clearExtendedProperties()
             ->clear()
             ->persisted(false)
             ->clean();

        $this->init();

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function setExtendedProperty($name, $value)
    {
        $this->extendedProperties[$name] = $this->onEventSetter($name, $value);
        return $this;
    }

    /**
     * {@inheritDoc}
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
     * {@inheritDoc}
     */
    public function clear($property = null)
    {
        if (!$property) {
            foreach ($this->properties as $name => $options) {
                $this->property($name)->clear();
            }

            $this->trigger('change');
            return $this;
        }

        if (is_array($property) || $property instanceof Traversable) {
            foreach ($property as $name) {
                $this->clear($name);
            }

            return $this;
        }

        if (!$property = $this->property($name = $property)) {
            return $this;
        }

        $property->clear();
        $this->trigger('change');

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function clean($property = null)
    {
        if (!$property) {
            foreach ($this->properties as $name => $options) {
                $this->property($name)->clean();
            }
            $this->trigger('change');

            return $this;
        }

        if (is_array($property) || $property instanceof Traversable) {
            foreach ($property as $name) {
                $this->clean($name);
            }

            return $this;
        }

        if (!$property = $this->property($property)) {
            return $this;
        }

        $property->clean();
        $this->trigger('change');

        return $this;
    }

    /**
     * {@inheritDoc}
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
     * {@inheritDoc}
     */
    public function markDirty($name)
    {
        if (!$property = $this->property($name)) {
            return $this;
        }

        $property->setDirty();
        $this->trigger('change');

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function type($name)
    {
        if ($property = $this->property($name)) {
            return $property->getType()->setOptions($property->getOptions());
        }

        return null;
    }

    /**
     * {@inheritDoc}
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
     * {@inheritDoc}
     */
    public function toArray($includeUnset = false)
    {
        $result = array();

        foreach ($this->properties as $name => $options) {
            $property = $this->property($name);

            if ($property->getType() instanceof Type\EntityType) {
                $result[$name] = $property->getValue();
                continue;
            }

            if ($includeUnset || !$property->isUnset()) {
                $result[$name] = $property->getValue();
            }
        }

        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function fromArray($properties = null)
    {
        if (!$properties) {
            return $this;
        }

        $className = __CLASS__;
        if (is_object($properties) && $properties instanceof $className) {
            $properties = $properties->export(null, true);
        }

        if (!is_array($properties) && !$properties instanceof Traversable) {
            throw new Exception\InvalidArgumentException('$properties must be an array or an instance of Traversable.');
        }

        foreach ($properties as $key => $value) {
            if ($property = $this->property($key)) {
                $this->set($key, $value, false);
            }
        }

        $this->trigger('change');

        return $this;
    }

    /**
     * {@inheritDoc}
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
                $result[$name] = $property->getExport();
            }
        }

        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function get($name)
    {
        if ($property = $this->property($name)) {
            $value = $this->onEventGetter(
                $name,
                $property->getValue()
            );

            return $value;
        }

        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function set($name, $value, $fireChangeEvent = true)
    {
        if (!$property = $this->property($name)) {
            return $this;
        }

        $property->setValue($this->onEventSetter($name, $value));

        if ($fireChangeEvent) {
            $this->trigger('change');
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
     * @param string|array|Traversable $properties Properties to filter/validate (omit for all)
     * @return boolean
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
        $filteredValues = $filter->getValues();

        foreach ($this->messages as $index => $messages) {
            if ($properties && !in_array($index, $properties)) {
                unset($this->messages[$index]);
                unset($filteredValues[$index]);
            }
        }

        // update properties with filtered values
        foreach ($filteredValues as $index => $value) {
            $this->set($index, $value);
        }

        return !(boolean) $this->messages;
    }

    /**
     * {@inheritDoc}
     */
    public function isPersisted()
    {
        return $this->isPersisted;
    }

    /**
     * {@inheritDoc}
     */
    public function persisted($value = true)
    {
        $this->isPersisted = (boolean) $value;

        return $this;
    }

    /**
     * Defines a new property for this entity.
     *
     * @param string $property Name of property
     * @param string $type     Type (keyword or FQCN)
     * @param array  $options  Options
     *
     * @return self
     */
    public function define($property, $type, array $options = array())
    {
        $this->properties[$property] = array(
            'name'           => $property,
            'options'        => $options,
            'type'           => $type,
        );

        if (isset($options['defaultValue'])) {
            $type = $this->typeManager()->type($type, $options);
            $this->properties[$property]['currentValue'] = $type->export($options['defaultValue']);
        }

        return $this;
    }

    /**
     * Persist changes to a property into the internal array.
     *
     * @param Property $property
     *
     * @return self
     */
    public function saveProperty(Property $property)
    {
        $this->properties[$property->getName()] = $property->export();
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function property($name, Property $property = null)
    {
        if ($property) {
            $this->property = $property;
        }

        if (!isset($this->properties[$name])) {
            if (!isset($this->aliases[$name]) ||
                !isset($this->properties[$this->aliases[$name]])) {
                return false;
            }
            $name = $this->aliases[$name];
        }

        if (!$this->property) {
            $this->property = new Property();
            $this->property
                 ->setParent($this)
                 ->typeManager($this->typeManager());
        }

        if ($this->property->getName() == $name) {
            return $this->property;
        }

        return $this->property->import($this->properties[$name]);
    }

    /**
     * Fetches a list item by its numerical index position.
     *
     * @param string $name  Property name
     * @param int    $index Index
     * @param mixed  $item  Value to assign
     *
     * @return self
     *
     * @throws Exception\RuntimeException
     */
    public function put($name, $index, $item)
    {
        if (!$property = $this->property($name)) {
            throw new Exception\RuntimeException('Specified $property does not exist');
        }

        if (!$property->getType() instanceof Type\ListType &&
            !$property->getType() instanceof Type\HashType) {
            throw new Exception\RuntimeException('indexOf failed as property type is not a list');
        }

        $value = $this->get($name);

        if ($value instanceof Cursor) {
            $value = $value->toArray();
        }

        $value[$index] = $item;
        $this->set($name, $value);

        return $this;
    }

    /**
     * Fetches a list item by its numerical index position.
     *
     * @param string  $name  Property name
     * @param integer $index
     *
     * @return mixed|null Value or null if unset
     *
     * @throws Exception\RuntimeException
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

        if ($value instanceof Cursor) {
            $value = $value->toArray();
        }

        return (isset($value[$index]) ? $value[$index] : null);
    }

    /**
     * Searches for a value and returns its index or FALSE if not found.
     *
     * @param string $name   Property name
     * @param mixed  $value  Value to search for
     * @param bool   $strict Strict type checking
     *
     * @return integer|false
     *
     * @throws Exception\RuntimeException
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
            if ($arr instanceof Cursor) {
                $arr = $arr->toArray();
            }

            if ($value instanceof Cursor) {
                $value = $value->toArray();
            }
        }

        return array_search($value, $arr, $strict);
    }

    /**
     * Unsets an index in a hash/associative array.
     *
     * @param string $name  Property name
     * @param string $index Index name
     *
     * @return self
     *
     * @throws Exception\RuntimeException
     */
    public function unsetIndex($name, $index)
    {
        if (!$property = $this->property($name)) {
            throw new Exception\RuntimeException('Specified $property does not exist');
        }

        if (!$property->getType() instanceof Type\ListType &&
            !$property->getType() instanceof Type\HashType) {
            throw new Exception\RuntimeException('indexOf failed as property type is not a list');
        }

        $arr = $this->get($name) ?: array();

        if ($property->getType() instanceof Type\ListEntityType) {
            if ($arr instanceof Cursor) {
                $arr = $arr->toArray();
            }
        }

        unset($arr[$index]);

        $this->set($name, $arr);

        return $this;
    }

    /**
     * Prepends a value to a list property.
     *
     * @param string $name  Property name
     * @param mixed  $value Value to prepend
     *
     * @return self
     *
     * @throws Exception\RuntimeException
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
            $arr instanceof Cursor) {
            $arr = $arr->toArray();
        }

        array_unshift($arr, $value);

        $this->set($name, $arr);

        return $this;
    }

    /**
     * Appends a value to a list property.
     *
     * @param string $name  Property name
     * @param mixed  $value Value to append
     *
     * @return self
     *
     * @throws Exception\RuntimeException
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
            $arr instanceof Cursor) {
            $arr = $arr->toArray();
        }

        array_push($arr, $value);

        $this->set($name, $arr);

        return $this;
    }

    /**
     * Removes a property from the end of a list and returns it.
     *
     * @param string $name Property name
     *
     * @return mixed List item (now removed)
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
            $arr instanceof Cursor) {
            $arr = $arr->toArray();
        }

        $return = array_pop($arr);

        $this->set($name, $arr);

        return $return;
    }

    /**
     * Removes a property from the beginning of a list and returns it.
     *
     * @param string $name Property name
     *
     * @return mixed List item (now removed)
     *
     * @throws Exception\RuntimeException
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
            $arr instanceof Cursor) {
            $arr = $arr->toArray();
        }

        $return = array_shift($arr);

        $this->set($name, $arr);

        return $return;
    }

    /**
     * Extracts a slice of the list.
     *
     * @param string       $name   Property name
     * @param integer      $offset Offset
     * @param integer|null $length Length
     *
     * @return array
     *
     * @throws Exception\RuntimeException
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
            $arr instanceof Cursor) {
            $arr = $arr->toArray();
        }

        return array_slice($arr, $offset, $length);
    }

    /**
     * Merges the list with another array.
     *
     * @param string  $name   Property name
     * @param array   $arr    Array to merge with
     * @param boolean $source True if existing list is the source vs. target
     *
     * @return self
     *
     * @throws Exception\RuntimeException
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
            if ($source instanceof Cursor) {
                $source = $source->toArray();
            }

            if ($target instanceof Cursor) {
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
     * @param string $name  Property name
     * @param mixed  $value Value to remove
     *
     * @return self
     *
     * @throws Exception\RuntimeException
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
     * @param string $name    Property name
     * @param mixed  $value   Value to add
     * @param bool   $prepend True for prepend, false for append
     *
     * @return self
     *
     * @throws Exception\RuntimeException
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
     * @param string $method Method
     * @param array  $args   Variable arguments
     *
     * @return  mixed
     *
     * @throws Exception\RuntimeException
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
     * @param   string   $event    Event name
     * @param   callable $callback Callback method/closure
     * @param   integer  $priority Priority
     *
     * @return self
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
     * {@inheritDoc}
     *
     * Triggers an event by name, invoking any callbacks registered with attach() in
     * the order of their priority, passing an instance of Contain\Event which stores
     * the parameters and allows for short circuiting and other utility.
     *
     * @param string $event  Event name
     * @param array  $params Key/value Parameters
     *
     * @return array (Possibly) Modified Parameters
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
                return $a[0] < $b[0];
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
     * @param string $event Event name
     *
     * @return self
     */
    public function clearListeners($event)
    {
        unset($this->events[$event]);
        return $this;
    }
}
