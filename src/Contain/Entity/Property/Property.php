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
use Traversable;
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
     * @var array
     */
    protected $options = array();

    /**
     * @var array
     */
    protected $validOptions = array(
        'defaultValue',
        'generated',
        'primary',
        'emptyValue',
        'required',
        'filters',
        'validators',
    );

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

            $type = new $type();

            if (!$type instanceof TypeInterface) {
                throw new InvalidArgumentException("'$type' does not implement "
                    . 'Contain\Entity\Property\Type\TypeInterface.'
                );
            }
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
     * Set entity options.
     *
     * @param   array|Traversable       Option name/value pairs
     * @return  $this
     */
    public function setOptions($options)
    {
        if (!is_array($options) && !$options instanceof Traversable) {
            throw new InvalidArgumentException(
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
        if (!in_array($name, $this->validOptions)) {
            throw new InvalidArgumentException(
                "'$name' is not a valid option. Valid options are: "
                . implode(', ', $this->validOptions) . '.'
            );
        }

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

    /**
     * Returns the unique identifier for the property.
     *
     * @return  string
     */
    public function getName()
    {
        return $this->name;
    }
}
