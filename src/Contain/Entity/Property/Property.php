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

namespace Contain\Entity\Property;

use Contain\Entity\EntityInterface;
use Traversable;

/**
 * Represents a single property for an entity.
 *
 * @category    akandels
 * @package     contain
 * @copyright   Copyright (c) 2012 Andrew P. Kandels (http://andrewkandels.com)
 * @license     http://www.opensource.org/licenses/bsd-license.php BSD License
 */
class Property
{
    /**
     * @var Property\Type\AbstractType
     */
    protected $type;

    /**
     * @var mixed
     */
    protected $currentValue;

    /**
     * @var mixed
     */
    protected $persistedValue;

    /**
     * @var mixed
     */
    protected $unsetValue;

    /**
     * @var mixed
     */
    protected $emptyValue;

    /**
     * @var array
     */
    protected $options = array();

    /**
     * @var array
     */
    protected $validOptions = array(
        'defaultValue',
        'primary',
        'required',
        'filters',
        'validators',
    );

    /**
     * Constructs the property which needs be associated with a name
     * and a data type.
     *
     * @param   Contain\Entity\Property\Type\AbstractType|string
     * @param   array|Traversable                                   Options
     * @return  $this
     */
    public function __construct($type, $options = null)
    {
        if ($options) {
            $this->setOptions($options);
        }

        $this->setType($type);

        $this->unsetValue = $this->getType()->getUnsetValue();
        $this->emptyValue = $this->getType()->getEmptyValue();

        $this->setValue($this->unsetValue);
        $this->clean();
    }

    /**
     * Sets the value for this property.
     *
     * @param   mixed                   Value
     * @return  $this
     */
    public function setValue($value)
    {
        $this->currentValue = $this->getType()->export($value);
        return $this;
    }

    /**
     * Sets the value for the index of the value, assuming the value itself is an 
     * array. This is really only used internally for updating event callbacks in 
     * lists and cursors of entites and should probably not be used outside of 
     * the Contain internals.
     *
     * @param   integer                 Index
     * @param   mixed                   export() value
     * @return  $this
     * @throws  Contain\Entity\Exception\InvalidArgumentException
     */
    public function setValueAtIndex($index, $value)
    {
        if (!isset($this->currentValue[$index])) {
            throw new \Contain\Entity\Exception\InvalidArgumentException('$index invalid for current value');
        } 

        $this->currentValue[$index] = $value;

        return $this;
    }

    /**
     * Gets the value for the index of the value, assuming the value itself is an 
     * array. This is really only used internally for updating event callbacks in 
     * lists and cursors of entites and should probably not be used outside of 
     * the Contain internals.
     *
     * @param   integer                 Index
     * @return  mixed
     * @throws  Contain\Entity\Exception\InvalidArgumentException
     */
    public function getValueAtIndex($index)
    {
        if (!isset($this->currentValue[$index])) {
            throw new \Contain\Entity\Exception\InvalidArgumentException('$index invalid for current value');
        } 

        return $this->currentValue[$index];
    }

    /**
     * Gets the value for this property.
     *
     * @return  mixed
     */
    public function getValue()
    {
        $property = $this;

        // track changes to the entity so they persisted into the internal, export() value
        if ($this->getType() instanceof Type\EntityType) {
            // set as persisted, then change properties to update the entity's internal dirty() flags
            $value = $this->getType()->parse($this->persistedValue);
            $value->clean()->fromArray($this->currentValue);

            // changing any value should persist back to be stored in the property's serialized version
            $value->attach('change', function ($event) use ($property) {
                $property->setValue($event->getTarget());
            }, -1000);

            // cleaning any sub-entity property should clean the property's serialized version
            $value->attach('clean', function ($event) use ($property) {
                $property->clean($event->getParam('name'));
            }, -1000);

            // dirtying any sub-entity property should dirty the property's serialized version
            $value->attach('dirty', function ($event) use ($property) {
                $property->setDirty($event->getParam('name'));
            }, -1000);

        // track changes to each entity in the list and persist them back to this, the parent list
        } elseif ($this->getType() instanceof Type\ListEntityType) {
            $value = $this->getType()->parse($this->currentValue);

            if ($value instanceof \ContainMapper\Cursor) {
                $value->getEventManager()->attach('hydrate', function ($event) use ($property) {
                    $entity = $event->getTarget();
                    $index  = $event->getParam('index');

                    $entity->attach('change', function ($e) use ($index, $property) {
                        $property->setValueAtIndex($index, $e->getTarget()->export());
                    }, -1000);
                }, -1000);
            }

        // track changes to each entity in the list and persist them back to this, the parent list
        } elseif ($this->getType() instanceof Type\ListType) {
            $value = $this->getType()->parse($this->currentValue);

            if ($this->getType()->getOption('type') == 'entity') {
                foreach ($value as $index => $entity) {
                    $entity->attach('change', function ($event) use ($index, $property) {
                        $property->setValueAtIndex($index, $event->getTarget()->export());
                    }, -1000);
                }
            }

        } else {
            $value = $this->getType()->parse($this->currentValue);
        }

        return $value;
    }

    /**
     * Returns true if the property has an unset value.
     *
     * @return  boolean
     */
    public function isUnset()
    {
        if ($this->getType() instanceof Type\BooleanType) {
            return $this->getValue() === $this->getType()->getUnsetValue();
        }

        // lists can never be unset, just empty arrays so we can push onto them
        if ($this->getType() instanceof Type\ListType) {
            return false;
        }

        // entities can never be unset, just empty hashes we can push into
        if ($this->getType() instanceof Type\EntityType) {
            return false;
        }

        return $this->getType()->export($this->getValue()) ===
               $this->getType()->export($this->getType()->getUnsetValue());
    }

    /**
     * Returns true if the property has an empty value.
     *
     * @return  boolean
     */
    public function isEmpty()
    {
        if ($this->getType() instanceof Type\BooleanType) {
            return $this->getValue() === $this->getType()->getEmptyValue();
        }

        return $this->currentValue === $this->getType()->export($this->getType()->getEmptyValue());
    }

    /**
     * Clears a property, setting it to an unset state.
     *
     * @return  $this
     */
    public function clear()
    {
        $this->setValue($this->getType()->getUnsetValue());
        return $this;
    }

    /**
     * Sets a property to its empty value.
     *
     * @return  $this
     */
    public function setEmpty()
    {
        $this->setValue($this->getType()->getEmptyValue());
        return $this;
    }

    /**
     * Sets a property as dirty, which is tracked by the persisted value not equaling 
     * the current value of the property, which is ensured by making the persisted value
     * something one-of-a-kind.
     *
     * Note: Some types (entity) allow sub-properties to be individually cleaned, hence
     *       the optional argument.
     *
     * @return  $this
     */
    public function setDirty($subProperty = null)
    {
        if ($subProperty) {
            if (!$this->getType() instanceof Type\EntityType) {
                throw new \Contain\Entity\Exception\InvalidArgumentException('$subProperty invalid for this type');
            }

            if (!is_array($this->persistedValue)) {
                $this->persistedValue = array();
            }

            $this->persistedValue[$subProperty] = $this->getValue()->type($subProperty)->getDirtyValue();

            return $this;
        }

        $this->persistedValue = $this->getType()->getDirtyValue();

        return $this;
    }

    /**
     * Marks the current value as having been persisted for the sake of
     * dirty tracking.
     *
     * Note: Some types (entity) allow sub-properties to be individually cleaned, hence
     *       the optional argument.
     *
     * @param   string                          Sub-Property
     * @return  $this
     */
    public function clean($subProperty = null)
    {
        if ($subProperty) {
            if (!$this->getType() instanceof Type\EntityType) {
                throw new \Contain\Entity\Exception\InvalidArgumentException('$subProperty invalid for this type');
            }

            if (!is_array($this->persistedValue)) {
                $this->persistedValue = array();
            }

            if (!isset($this->currentValue[$subProperty])) {
                unset($this->persistedValue[$subProperty]);
                return $this;
            }

            $this->persistedValue[$subProperty] = $this->currentValue[$subProperty];

            return $this;
        }

        $this->persistedValue = $this->currentValue;

        return $this;
    }

    /**
     * Exports a serializable version of the current value.
     *
     * @return  mixed
     */
    public function export()
    {
        return $this->currentValue;
    }

    /**
     * Returns true if the current value of the property differs from its
     * last persisted value.
     *
     * @return  boolean
     */
    public function isDirty()
    {
        return $this->currentValue !== $this->persistedValue;
    }

    /**
     * Returns the value of this property when it was last persisted.
     *
     * @return  mixed
     */
    public function getPersistedValue()
    {
        return $this->getType()->parse($this->persistedValue);
    }

    /**
     * Sets the data type for the property.
     *
     * @param   Contain\Entity\Property\Type\AbstractType|string
     * @return  $this
     */
    public function setType($type)
    {
        if (is_string($type)) {
            if (is_subclass_of($type, '\Contain\Entity\EntityInterface')) {
                $newType = new Type\EntityType();
                $newType->setOptions(array('className' => $type));
                $type = $newType;
            } elseif (is_subclass_of($type, '\Contain\Entity\Property\Type\TypeInterface')) {
                $type = new $type();
            }
        }

        if (!$type instanceof Type\TypeInterface) {
            if (is_string($type) && strpos($type, '\\') === false) {
                $type = 'Contain\Entity\Property\Type\\' . ucfirst($type) . 'Type';
            }

            if (!class_exists($type)) {
                throw new \Contain\Entity\Exception\InvalidArgumentException('Type \''
                    . $type . '\' does not exist.'
                );
            }

            $type = new $type();

            if (!$type instanceof Type\TypeInterface) {
                throw new \Contain\Entity\Exception\InvalidArgumentException('$type does not implement '
                    . 'Contain\Entity\Property\Type\TypeInterface.'
                );
            }
        }

        $this->type = $type;

        $this->type->setOptions($this->options);

        return $this;
    }

    /**
     * Returns the type object which defines how the data type
     * behaves.
     *
     * @return Contain\Entity\Property\Type\AbstractType
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set entity options.
     *
     * @param   array|Traversable       Option name/value pairs
     * @return  $this
     */
    public function setOptions($options)
    {
        if (!is_array($options) && !$options instanceof Traversable) {
            throw new \Contain\Entity\Exception\InvalidArgumentException(
                '$options must be an instance of Traversable or an array.'
            );
        }

        foreach ($options as $name => $value) {
            $this->setOption($name, $value);
        }

        return $this;
    }

    /**
     * Sets the value for an entity property's option.
     *
     * @param   string              Option name
     * @param   mixed               Option value
     * @return  $this
     */
    public function setOption($name, $value)
    {
        $this->options[$name] = $value;

        return $this;
    }

    /**
     * Retrieves entity property's options as an array.
     *
     * @return  array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Retrieves entity property's property by name.
     *
     * @param   string              Option name
     * @return  array|null
     */
    public function getOption($name)
    {
        return isset($this->options[$name]) ? $this->options[$name] : null;
    }
}
