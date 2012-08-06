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

use InvalidArgumentException;
use Contain\Entity\EntityInterface;
use Contain\Entity\Property\Type\EntityType;
use Contain\Entity\Property\Type\ListType;

/**
 * Represents a query for a property in a hierarchy and its solution.
 *
 * @category    akandels
 * @package     contain
 * @copyright   Copyright (c) 2012 Andrew P. Kandels (http://andrewkandels.com)
 * @license     http://www.opensource.org/licenses/bsd-license.php BSD License
 */
class Resolver
{
    /**
     * @var string
     */
    protected $query;

    /**
     * @var Contain\Entity\EntityInterface;
     */
    protected $entity;

    /**
     * @var mixed
     */
    protected $value;

    /**
     * @var Contain\Entity\Property\Type\TypeInterface
     */
    protected $type;

    /**
     * @var string
     */
    protected $property;

    /**
     * @var Contain\Entity\Property\Resolver[]
     */
    protected $steps = array();

    /**
     * Constructor
     *
     * @param   string                      Query
     * @return  $this
     */
    public function __construct($query)
    {
        if (!$query || !is_string($query)) {
            throw new InvalidArgumentException('Resolve failed with invalid or non-existent query.');
        }

        $this->query = $query;
    }

    /**
     * Recursively scans an entity using the query passed to the 
     * constructor by parsing the dot notation and scanning properties
     * and sub-properties.
     *
     * @param   Network\Entity\EntityInterface              Entity
     * @param   string                                      Recursive query
     * @return  $this
     * @throws  InvalidArgumentException
     */
    public function scan(EntityInterface $entity, $query = null)
    {
        if ($query === null) {
            $query = $this->query;
        }

        $parts = explode('.', $query);
        $this->property = array_shift($parts);
        $this->entity   = $entity;
        $this->value    = $this->lookupProperty($this->entity, $this->property);
        $this->type     = $this->entity->type($this->property);

        if (!$parts) {
            return $this;
        }

        if ($type instanceof ListType) {
            $part    = array_shift($parts);
            $subType = $this->type->getType();

            if (!preg_match('/^[0-9]+$/', $part)) {
                throw new InvalidArgumentException('Resolve failed with \'' . $this->query . '\' '
                    . 'because \'' . $this->property . '\' descends Contain\Entity\Property\Type\ListType '
                    . 'and may only be traversed with numeric indexes.'
                );
            }

            $index = (int) $part;

            if (!isset($this->value[$index])) {
                throw new InvalidArgumentException('Resolve failed with \'' . $this->query . '\' '
                    . 'because index ' . $index . ' is not set in \'' . $this->property . '\'.'
                );
            }

            $nestedValue = $this->value[$index];

            if ($parts && $subType instanceof EntityType) {
                $loggedStep = clone $this;
                $loggedStep->clearSteps();
                $this->steps[] = $loggedStep;
                return $this->scan($nestedValue, implode('.', $parts));
            }
            
            if ($parts) {
                throw new InvalidArgumentException('Resolve failed with \'' . $this->query . '\', '
                    . 'cannot descend into a list unless it contains elements that implement '
                    . 'Contain\Entity\Property\Type\EntityType.'
                );
            }

            $this->type  = $subType;
            $this->value = $nestedValue;

            return $this;
        }

        if ($type instanceof EntityType) {
            $loggedStep = clone $this;
            $loggedStep->clearSteps();
            $this->steps[] = $loggedStep;
            return $this->scan($value, implode('.', $parts));
        }

        throw new InvalidArgumentException('Resolve failed with \'' . $this->query . '\' '
            . 'at: \'' . $part . '\' because \'' . $this->property . '\' is not a type that can be '
            . 'traversed.'
        );
    }

    /**
     * Verifies a property exists and returns the value.
     *
     * @param   Network\Entity\EntityInterface              Entity
     * @param   string                                      Property
     * @return  mixed
     */
    protected function lookupProperty(EntityInterface $entity, $property)
    {
        if (!$entity->hasProperty($property)) {
            throw new InvalidArgumentException('Resolve failed with query \'' 
                . $this->query . '\', $entity '
                . 'does not have a \'' . $property . '\' property.'
            );
        }

        $method = 'get' . ucfirst($property);

        return $entity->$method();
    }

    /**
     * Returns the entity owner of the matched property.
     *
     * @return  Contain\Entity\EntityInterface
     */
    public function getEntity()
    {
        return $this->entity;
    }

    /**
     * Returns the type of the matched property.
     *
     * @return  Contain\Entity\Property\Type\TypeInterface
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Returns the current value of the matched property of its
     * parent entity.
     *
     * @return  mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Returns the matched property.
     *
     * @return  string
     */
    public function getProperty()
    {
        return $this->property;
    }

    /**
     * Return the steps of Resolver instances in order of how they found
     * the property.
     *
     * @return  Contain\Property\Resolver[]
     */
    public function getSteps()
    {
        return $this->steps;
    }

    /**
     * Clears the steps which record how a property was found.
     *
     * @return  $this
     */
    public function clearSteps()
    {
        $this->steps = array();
        return $this;
    }
}
