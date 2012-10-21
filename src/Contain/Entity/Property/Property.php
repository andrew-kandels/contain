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

namespace Contain\Entity\Property;

use Contain\Entity\Property\Type\AbstractType;
use Contain\Entity\Property\Type\EntityType;
use Contain\Entity\Property\Type\BooleanType;
use Contain\Entity\Property\Type\TypeInterface;
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
     * @var Contain\Entity\Property\Type\AbstractType
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

        $this->unsetValue   = $this->getType()->getUnsetValue();
        $this->emptyValue   = $this->getType()->getEmptyValue();
        $this->currentValue = $this->unsetValue;

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
        $this->currentValue = $this->getType()->parse($value);
        return $this;
    }

    /**
     * Gets the value for this property.
     *
     * @return  mixed
     */
    public function getValue()
    {
        return $this->currentValue;
    }

    /**
     * Returns true if the property has an unset value.
     *
     * @return  boolean
     */
    public function isUnset()
    {
        if ($this->getType() instanceof BooleanType) {
            return $this->currentValue === $this->getType()->getUnsetValue();
        }

        return $this->getType()->export($this->currentValue) ===
               $this->getType()->export($this->getType()->getUnsetValue());
    }

    /**
     * Returns true if the property has an empty value.
     *
     * @return  boolean
     */
    public function isEmpty()
    {
        if ($this->getType() instanceof BooleanType) {
            return $this->currentValue === $this->getType()->getEmptyValue();
        }

        return $this->getType()->export($this->currentValue) ===
               $this->getType()->export($this->getType()->getEmptyValue());
    }

    /**
     * Clears a property, setting it to an unset state.
     *
     * @return  $this
     */
    public function clear()
    {
        $this->currentValue = $this->getType()->getUnsetValue();
        return $this;
    }

    /**
     * Sets a property to its empty value.
     *
     * @return  $this
     */
    public function setEmpty()
    {
        $this->currentValue = $this->getType()->getEmptyValue();
        return $this;
    }

    /**
     * Sets a property as dirty.
     * @todo think of a better way to do this than a dummy hash
     *
     * @return  $this
     */
    public function setDirty()
    {
        $this->persistedValue = uniqid(true, '');
        return $this;
    }

    /**
     * Marks the current value as having been persisted for the sake of
     * dirty tracking.
     *
     * @return  $this
     */
    public function clean()
    {
        $this->persistedValue = $this->getType()->export($this->currentValue);

        if ($this->getType() instanceof EntityType && $this->currentValue) {
            $this->currentValue->clean();
        }

        return $this;
    }

    /**
     * Exports a serializable version of the current value.
     *
     * @return  mixed
     */
    public function export()
    {
        return $this->getType()->export($this->currentValue);
    }

    /**
     * Returns true if the current value of the property differs from its
     * last persisted value.
     *
     * @return  boolean
     */
    public function isDirty()
    {
        return $this->getType()->export($this->currentValue) !== $this->persistedValue;
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
                $newType = new EntityType();
                $newType->setOptions(array('className' => $type));
                $type = $newType;
            } elseif (is_subclass_of($type, '\Contain\Entity\Property\Type\TypeInterface')) {
                $type = new $type();
            }
        }

        if (!$type instanceof TypeInterface) {
            if (is_string($type) && strpos($type, '\\') === false) {
                $type = 'Contain\Entity\Property\Type\\' . ucfirst($type) . 'Type';
            }

            if (!class_exists($type)) {
                throw new \Contain\Entity\Exception\InvalidArgumentException('Type \''
                    . $type . '\' does not exist.'
                );
            }

            $type = new $type();

            if (!$type instanceof TypeInterface) {
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
