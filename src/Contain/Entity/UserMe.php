<?php

namespace Contain\Entity;

use Contain\Entity\Definition\AbstractDefinition;
use Contain\Exception\InvalidArgumentException;
use Traversable;

class User
{
    /**
     * @var integer
     */
    protected $id;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var enum
     */
    protected $status;

    /**
     * @var Contain\Entity\Definition\User()
     */
    protected $_definition;

    /**
     * @var array
     */
    protected $_validProperties;

    /**
     * Constructor
     *
     * @param   array|Traversable               Properties
     * @return  $this
     */
    public function __construct($properties)
    {
        $this->_definition      = new \Contain\Entity\Definition\User();
        $this->_definition->setUp();
        $this->_validProperties = $this->_definition->getValidProperties();

        if ($properties) {
            $this->import($properties);
        }
    }

    /**
     * Sets the value of one of the entity's properties.
     *
     * @param   string              Property name
     * @param   mixed               Value
     * @param   boolean             Check validity
     * @return  $this
     */
    protected function setProperty($property, $value, $checkValidity = true)
    {
        if ($checkValidity && !in_array($property, $this->_validProperties)) {
            if (!$this->_definition->hasExtended()) {
                throw new InvalidArgumentException("No '$property' property exists in the entity definition.");
            }
        } else {
            $value = $this->_definition->getProperty($property)
                                       ->getType()
                                       ->parse($value);
        }

        $this->$property = $value;

        return $this;
    }

    /**
     * Imports the entity's properties from an array or instance of
     * Traversable.
     *
     * @param   array
     * @return  $this
     */
    public function import($properties)
    {
        if (!is_array($properties) && !$properties instanceof Traversable) {
            throw new InvalidArgumentException('$properties must be an array or an instance of Traversable.');
        }

        foreach ($properties as $name => $value) {
            $this->setProperty($name, $value);
        }

        return $this;
    }

    /**
     * Exports all of the entity's properties as an array.
     *
     * @return  array
     */
    public function export()
    {
        $properties = get_object_vars($this);

        $result = array();

        foreach ($properties as $property => $value) {
            if ($property[0] != '_') {
                $result[$property] = $value;
            }
        }

        return $result;
    }

    /**
     * Setter for the 'id' property.
     *
     * @param   integer                     Value
     * @return  $this
     */
    public function setId($value)
    {
        return $this->setProperty('id', $value, false);
    }

    /**
     * Getter for the 'id' property.
     *
     * @return  integer
     */
    public function getId($value)
    {
        return $this->id;
    }

    /**
     * Setter for the 'name' property.
     *
     * @param   string                      Value
     * @return  $this
     */
    public function setName($value)
    {
        return $this->setProperty('name', $value, false);
    }

    /**
     * Getter for the 'name' property.
     *
     * @return  string
     */
    public function getName($value)
    {
        return $this->name;
    }

    /**
     * Setter for the 'status' property.
     *
     * @param   string                      Value
     * @return  $this
     */
    public function setStatus($value)
    {
        return $this->setProperty('status', $value, false);
    }

    /**
     * Getter for the 'status' property.
     *
     * @return  string
     */
    public function getStatus($value)
    {
        return $this->status;
    }

    /**
     * Setter for the 'createdAt' property.
     *
     * @param   datetime                    Value
     * @return  $this
     */
    public function setCreatedAt($value)
    {
        return $this->setProperty('createdAt', $value, false);
    }

    /**
     * Getter for the 'createdAt' property.
     *
     * @return  string
     */
    public function getCreatedAt($value)
    {
        return $this->createdAt;
    }

    /**
     * Setter for the 'createdAt' property.
     *
     * @param   datetime                    Value
     * @return  $this
     */
    public function setUpdatedAt($value)
    {
        return $this->setProperty('updatedAt', $value, false);
    }

    /**
     * Getter for the 'createdAt' property.
     *
     * @return  string
     */
    public function getUpdatedAt($value)
    {
        return $this->updatedAt;
    }
}
