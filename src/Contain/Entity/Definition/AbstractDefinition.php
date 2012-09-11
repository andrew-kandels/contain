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

namespace Contain\Entity\Definition;

use Contain\Exception\InvalidArgumentException;
use Contain\Exception\RuntimeException;
use Contain\Entity\Property\Property;

/**
 * Defines the behavior of an entity.
 *
 * @category    akandels
 * @package     contain
 * @copyright   Copyright (c) 2012 Andrew P. Kandels (http://andrewkandels.com)
 * @license     http://www.opensource.org/licenses/bsd-license.php BSD License
 */
abstract class AbstractDefinition
{
    /**
     * @var string
     */
    const PROPERTY_NAME_VALID = '/^[a-z][a-z0-9\-_]*$/i';

    /**
     * @var string
     */
    const ENTITY = 'entity';

    /**
     * @var string
     */
    const FILTER = 'filter';

    /**
     * @var array
     */
    protected $properties = array();

    /**
     * @var integer
     */
    protected $position = 0;

    /**
     * @var array
     */
    protected $options = array(
        'iteration' => true,
        'events'    => false,
    );

    /**
     * @var array
     */
    protected $import = array();

    /**
     * @var array
     */
    protected $registeredMethods = array();

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $targets = array(
        'entity' => '',
        'filter' => '',
    );

    /** 
     * @var string
     */
    protected $parentClass;

    /**
     * @var array
     */
    protected $implementors = array();

    /**
     * Constructor
     *
     * @return  $this
     */
    public function __construct()
    {
        $this->setUp();
    }

    /**
     * Called when an entity itself is instantiated. Used to register 
     * events.
     *
     * @return  $this
     */
    public function init()
    {
    }

    /**
     * Registers a new property and returns the property object which can 
     * be invoked directly for additional options or passed as a third
     * argument.
     *
     * @param   string              Name of the property
     * @param   string              Data type (string, integer, etc.)
     * @param   array|Traversable   Options
     * @return  Contain\Entity\Property
     */
    public function setProperty($name, $type, $options = null)
    {
        if (!preg_match(self::PROPERTY_NAME_VALID, $name)) {
            throw new InvalidArgumentException('Property $name does not match allowed: '
                . self::PROPERTY_NAME_VALID . '.'
            );
        }

        $this->removeProperty($name);

        $obj = new Property($type, $options);
        $this->properties[$name] = $obj;

        return $obj;
    }

    /**
     * Clones an instantiated property presumably from another entity.
     *
     * @param   string              Name of the property
     * @param   Contain\Entity\Property\Property
     * @return  Contain\Entity\Property\Property
     */
    public function cloneProperty($name, Property $property)
    {
        $this->removeProperty($name);
        $this->properties[$name] = clone $property;

        return $property;
    }

    /**
     * Returns all of the entity's properties.
     *
     * @return  Contain\Entity\Property[]
     */
    public function getProperties()
    {
        return $this->properties;
    }

    /**
     * Finds a property object by its registered name.
     *
     * @param   string              Name of the property
     * @return  Contain\Entity\Property
     */
    public function getProperty($name)
    {
        if (isset($this->properties[$name])) {
            return $this->properties[$name];
        }

        throw new InvalidArgumentException('$name is not a registered property');
    }

    /**
     * Checks to see if a property has been registered under a given name.
     *
     * @param   string              Name of the property
     * @return  boolean
     */
    public function hasProperty($name)
    {
        return isset($this->properties[$name]);
    }

    /**
     * Unsets a property.
     *
     * @param   string              Name of the property
     * @return  $this
     */
    public function removeProperty($name)
    {
        unset($this->properties[$name]);
        return $this;
    }

    /**
     * Sets the target path for the compiler of a given item, of 
     * which the valid options include:
     *
     * 1) entity: The compiled entity object
     * 2) filter: The Zend\InputFilter\InputFilter implementation 
     *            for validation and data sanitizing.
     *
     * @param   string                  Target key (see above, e.g.: entity)
     * @param   string                  File system path
     * @return  $this
     */
    public function registerTarget($target, $path)
    {
        if (!isset($this->targets[$target])) {
            throw new InvalidArgumentException(
                "'$target' is not a valid key, valid options are: "
                . implode(', ', array_keys($this->targets)) . '.'
            );
        }

        $this->targets[$target] = $path;

        return $this;
    }

    /**
     * Returns an array of target keys and their paths (see setTarget()
     * for a list of valid keys and their definitions).
     *
     * @return  array
     */
    public function getTargets()
    {
        return $this->targets;
    }

    /**
     * Gets the target path for a given target key (see setTarget()
     * for a list of valid keys and their definitions).
     *
     * @param   string                  Target key (e.g.: entity)
     * @return  string
     */
    public function getTarget($target)
    {
        if (!isset($this->targets[$target])) {
            throw new InvalidArgumentException(
                "'$target' is not a valid key, valid options are: "
                . implode(', ', array_keys($this->targets)) . '.'
            );
        }

        return $this->targets[$target];
    }

    /**
     * Imports the properties and most behaviors from another definition.
     *
     * @param   Contain\Entity\Definition\AbstractDefinition
     * @return  $this
     */
    public function import($definition)
    {
        if (!$definition instanceof AbstractDefinition) {
            if (!is_string($definition)) {
                throw new InvalidArgumentException('$definition must implement '
                    . 'Contain\Entity\Definition\AbstractDefinition.'
                );
            }

            if (strpos('\\', $definition) === false) {
                $def = 'Contain\Entity\Definition\\' . $definition;
            }

            $definition = new $definition();

            if (!$definition instanceof AbstractDefinition) {
                throw new InvalidArgumentException('$definition does not implement '
                    . 'Contain\Entity\Definition\AbstractDefinition.'
                );
            }
        }

        foreach ($definition->getProperties() as $name => $property) {
            $this->cloneProperty($name, $property);
        }

        $this->import[] = $definition;

        return $this;
    }

    /**
     * Returns the definition classes this definition imports.
     *
     * @return  Contain\Entity\Definition\AbstractDefinition[]
     */
    public function getImports()
    {
        return $this->import;
    }

    /**
     * Registers a method of the definition class that should be compiled into the end entity.
     *
     * @param   string              Method name
     * @return  $this
     */
    public function registerMethod($method)
    {
        if (!method_exists($this, $method)) {
            throw new InvalidArgumentException('$method is not a valid method of the definition class.');
        }

        $this->registeredMethods[] = $method;
        return $this;
    }

    /**
     * Returns all methods registered for the definition.
     * 
     * @return  array
     */
    public function getRegisteredMethods()
    {
        $result = array();

        foreach ($this->registeredMethods as $method) {
            $result[] = array($this, $method);
        }

        foreach ($this->import as $definition) {
            foreach ($definition->getRegisteredMethods() as $method) {
                $result[] = $method;
            }
        }

        return $result;
    }

    /**
     * Sets the entity name, defaults to the name of the definition class.
     *
     * @param   string                  Name
     * @return  $this
     */
    public function setName($name)
    {
        $this->name = (string) $name;
        return $this;
    }

    /**
     * Gets the entity name, defaults to the name of the definition class.
     *
     * @return  string
     */
    public function getName()
    {
        if ($this->name) {
            return $this->name;
        }

        return ($this->name = substr(__CLASS__, strrpos('\\', __CLASS__) + 1));
    }

    /** 
     * Sets the parent class the compiled entity will extend.
     *
     * @param   string
     * @return  $this
     */
    public function setParentClass($className)
    {
        $this->parentClass = $className;
        return $this;
    }

    /** 
     * Gets the parent class the compiled entity will extend.
     *
     * @return  string
     */
    public function getParentClass()
    {
        return $this->parentClass;
    }

    /** 
     * Registers an interface the compiled entity will implement.
     *
     * @param   array
     * @return  $this
     */
    public function registerImplementor($implementor)
    {
        $this->implementors[] = $implementor;
        return $this;
    }

    /** 
     * Sets the interfaces the compiled entity will implement.
     *
     * @param   array
     * @return  $this
     */
    public function setImplementors(array $implementors)
    {
        $this->implementors = $implementors;
        return $this;
    }

    /** 
     * Gets the interfaces the compiled entity will implement.
     *
     * @return  array
     */
    public function getImplementors()
    {
        return $this->implementors;
    }

    /**
     * Set entity level options.
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
     * Sets the value for an entity's option.
     *
     * @param   string              Option name
     * @param   mixed               Option value
     * @return  $this
     */
    public function setOption($name, $value)
    {
        if (!isset($this->options[$name])) {
            throw new InvalidArgumentException(
                "'$name' is not a valid option. Valid options are: " 
                . implode(', ', array_keys($this->options)) . '.'
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
}
