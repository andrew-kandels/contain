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
use Contain\Entity\Property\Type\TypeInterface;
use Contain\Exception\InvalidArgumentException;
use Contain\Exception\RuntimeException;

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
     * @var string
     */
    protected $name;

    /**
     * @var Contain\Entity\Property\Type\AbstractType
     */
    protected $type;

    /**
     * @var boolean
     */
    protected $required = false;

    /**
     * @var boolean
     */
    protected $generated = false;

    /**
     * @var mixed
     */
    protected $defaultValue;

    /**
     * @var boolean
     */
    protected $primary = false;

    /**
     * Constructs the property which needs be associated with a name 
     * and a data type.
     *
     * @param   string                  Name of the property
     * @return  $this
     */
    public function __construct($name)
    {
        $this->name = $name;
    }

    /**
     * Sets the data type for the property.
     *
     * @param   Contain\Entity\Property\Type\AbstractType|string
     * @return  $this
     */
    public function setType($type)
    {
        if (!$type instanceof TypeInterface) {
            if (is_string($type) && strpos('\\', $type) === false) {
                $type = 'Contain\Entity\Property\Type\\' . ucfirst($type) . 'Type';
            }

            if (!is_subclass_of($type, 'Contain\Entity\Property\Type\TypeInterface')) {
                throw new InvalidArgumentException('$type should be an instance of '
                    . 'Contain\Entity\Property\Type\TypeInterface or the name of a class that is.'
                );
            }

            $type = new $type();
        }

        $this->type = $type;

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
        if (!$this->type) {
            throw new RuntimeException('No data type has been set for the property.');
        }

        return $this->type;
    }

    /** 
     * Gets the default value for the property.
     *
     * @return  mixed
     */
    public function getDefaultValue()
    {
        return $this->defaultValue;
    }

    /**
     * Sets a default value for a property.
     *
     * @param   mixed               Value
     * @return  $this
     */
    public function setDefaultValue($value)
    {
        $this->defaultValue = $this->getType()->parse($value);
        return $this;
    }

    /**
     * Returns the unique identifier for the property.
     *
     * @return  string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Defines whether the property requires a value be set.
     *
     * @param   boolean
     * @return  $this
     */
    public function setRequired($value = true)
    {
        $this->required = (bool) $value;
        return $this;
    }

    /**
     * Answers whether the property requires a value be set.
     *
     * @return bool
     */
    public function isRequired()
    {
        return $this->required;
    }

    /**
     * Is the property the result of an AUTO_INCREMENT or sequence?
     *
     * @param   boolean
     * @return  $this
     */
    public function setGenerated($value = true)
    {
        $this->generated = (bool) $value;
        return $this;
    }

    /**
     * Answers whether the property requires a value be set.
     *
     * @return bool
     */
    public function isGenerated()
    {
        return $this->generated;
    }

    /**
     * Sets whether the property is a primary key / UID.
     * 
     * @param   boolean
     * @return  $this
     */
    public function setPrimary($value = true)
    {
        $this->primary = (bool) $value;
        return $this;
    }

    /**
     * Answers whether the property is a primary key / UID.
     *
     * @return  boolean
     */
    public function isPrimary()
    {
        return $this->primary;
    }
}
