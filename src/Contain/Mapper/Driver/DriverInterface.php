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

namespace Contain\Mapper\Driver;

use Contain\Entity\EntityInterface;

/**
 * Contain Mapper Driver Interface
 *
 * @category    akandels
 * @package     contain
 * @copyright   Copyright (c) 2012 Andrew P. Kandels (http://andrewkandels.com)
 * @license     http://www.opensource.org/licenses/bsd-license.php BSD License
 */
interface DriverInterface
{
    /**
     * Hydrates an array of data into an entity object.
     *
     * @param   array                   Data key/value pairs
     * @return  EntityInterface
     */
    public function hydrateEntity($data = array());

    /**
     * Returns true if the object has been persisted to the data store 
     * at some point (though it may be dirty now).
     *
     * @param   EntityInterface                 Entity to persist
     * @return  boolean
     */
    public function isPersisted(EntityInterface $entity);

    /**
     * Increments a numerical property.
     *
     * @param   Contain\Entity\EntityInterface  Entity to persist
     * @param   string                          Query to resolve path to numeric property
     * @param   integer                         Amount to increment by
     * @return  $this
     */
    public function increment(EntityInterface $entity, $query, $inc);

    /**
     * Appends one or more values to the end of a ListType, optionally if they do or 
     * don't exist. In MongoDB this is an atomic operation.
     *
     * @param   Contain\Entity\EntityInterface  Entity to persist
     * @param   string                          Query to resolve which should point to a ListType
     * @param   mixed|array|Traversable         Value(s) to push
     * @param   boolean                         Only add if it doesn't exist
     * @return  $this
     */
    public function push(EntityInterface $entity, $query, $value, $ifNotExists = false);

    /**
     * Persists an entity in MongoDB.
     *
     * @param   EntityInterface                 Entity to persist
     * @return  $this
     */
    public function persist(EntityInterface $entity);

    /**
     * Finds and hydrates a single entity from a search criteria.
     *
     * @param   array                   Search criteria
     * @return  Contain\Entity\EntityInterface|false
     */
    public function findOne(array $criteria = array());

    /**
     * Finds a subset of entities by some condition and returns them in a 
     * array of hydrated entity objects.
     *
     * @param   array                   Search criteria
     * @return  EntityInterface[]
     */
    public function find(array $criteria = array());

    /**
     * Deletes a row by a condition.
     *
     * @param   array                   Search criteria
     * @return  $this
     */
    public function deleteBy(array $criteria);

    /**
     * Deletes an entity.
     *
     * @param   Contain\Entity\EntityInterface
     * @return  $this
     */
    public function delete(EntityInterface $entity);

    /**
     * Converts a property query to something the mapper can use to work with 
     * various levels of sub-properties and further descendents using the 
     * dot notation.
     *
     * @param   Contain\Entity\EntityInterface  Entity to persist
     * @param   string                          Query
     * @return  Contain\Entity\Property\Resolver
     */
    public function resolve(EntityInterface $entity, $query);
}
