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

namespace Contain\Mapper\Driver\MongoDB;

use Contain\Mapper\Driver\ConnectionInterface;
use Contain\Mapper\Driver\DriverInterface;
use Contain\Exception\InvalidArgumentException;
use Contain\Entity\EntityInterface;
use Contain\Entity\Property\Type\EntityType;
use Contain\Entity\Property\Type\IntegerType;
use Contain\Entity\Property\Type\ListType;
use Contain\AbstractQuery;
use Exception;
use RuntimeException;
use MongoId;
use Contain\Entity\Property\Resolver;

/**
 * MongoDB Driver
 *
 * @category    akandels
 * @package     contain
 * @copyright   Copyright (c) 2012 Andrew P. Kandels (http://andrewkandels.com)
 * @license     http://www.opensource.org/licenses/bsd-license.php BSD License
 */
class MongoDB extends AbstractQuery implements DriverInterface
{
    /**
     * @var Contain\Mapper\Driver\ConnectionInterface
     */
    protected $connection;

    /**
     * @var string
     */
    protected $collectionName;

    /**
     * @var string
     */
    protected $databaseName;

    /**
     * @var MongoCollection
     */
    protected $collection;

    /**
     * @var string
     */
    protected $entityClass;

    /**
     * @var array
     */
    protected $options = array();

    /**
     * Constructor
     *
     * @param   string Entity namespace
     * @param   Contain\Mapper\Driver\ConnectionInterface
     * @param   string MongoDB Database Name
     * @param   string MongoDB Collection Name
     * @return  $this
     */
    public function __construct($entityClass, ConnectionInterface $connection, $database, $collection)
    {
        $this->entityClass    = $entityClass;
        $this->connection     = $connection;
        $this->databaseName   = $database;
        $this->collectionName = $collection;
    }

    /**
     * Retrieves the MongoCollection instance from Mongo.
     *
     * @return  MongoCollection
     */
    public function getCollection()
    {
        if (!$this->collection) {
            $this->collection = $this->connection
                                     ->getConnection()
                                     ->{$this->databaseName}
                                     ->{$this->collectionName};
        }

        return $this->collection;
    }

    /**
     * Hydrates an array of data into an entity object.
     *
     * @param   array|Traversable       Data key/value pairs
     * @return  EntityInterface
     */
    public function hydrateEntity($data = array())
    {
        if ($data instanceof Traversable) {
            $data = iterator_to_array($data);
        }

        if (!is_array($data)) {
            throw new InvalidArgumentException('$data must be an array or an instance of '
                . 'Traversable.'
            );
        }

        // default options
        $options = $this->getOptions(array(
            'ignoreErrors' => true,
            'autoExtend'   => false,
        ));

        $this->clear();

        // Mongo specific primary/unique column
        $id = null;
        if (isset($data['_id'])) {
            $id = $data['_id'];
            unset($data['_id']);
        }

        $entityClass  = $this->entityClass;
        $entity       = new $entityClass();
        $autoExtended = !empty($this->options['autoExtended']);
        $ignoreErrors = !isset($this->options['ignoreErrors']) ||
                        !empty($this->options['ignoreErrors']);

        $entity->fromArray($data, $ignoreErrors, $autoExtended);

        // Mongo id is saved as an extended property for internal tracking 
        // on update vs insert
        if ($id) {
            $entity->setExtendedProperty('_id', $id);
        }

        // remove any dirty flags as properties are all persisted at this point
        $entity->clean();

        return $entity;
    }

    /**
     * Gets the interal MongoId value for an entity (if set).
     *
     * @param   EntityInterface                 Entity to persist
     * @return  mixed|null
     */
    public function getId(EntityInterface $entity)
    {
        if ($id = $entity->getExtendedProperty('_id')) {
            return $id;
        }

        return null;
    }

    /**
     * Returns true if the object has been persisted to the data store 
     * at some point (though it may be dirty now).
     *
     * @param   EntityInterface                 Entity to persist
     * @return  boolean
     */
    public function isPersisted(EntityInterface $entity)
    {
        return (bool) $this->getId($entity);
    }

    /**
     * Increments a numerical property.
     *
     * @param   Contain\Entity\EntityInterface  Entity to persist
     * @param   string                          Query to resolve path to numeric property
     * @param   integer                         Amount to increment by
     * @return  $this
     */
    public function increment(EntityInterface $entity, $query, $inc)
    {
        if (!$this->isPersisted($entity)) {
            throw new InvalidArgumentException('Cannot increment properties as $entity '
                . 'has not been persisted.'
            );
        }

        $resolver = $this->resolve($entity, $query)
                         ->assertType('Contain\Entity\Property\Type\IntegerType');

        $setter = 'set' . ucfirst($resolver->getProperty());
        $resolver->getEntity()->$setter((int) $resolver->getValue() + (int) $inc);

        $entity->getEventManager()->trigger('update.pre', $entity);

        $this->getCollection()->update(
            array('_id' => $this->getId($entity)),
            array('$inc' => array($query => $inc)),
            $this->getOptions(array(
                'upsert' => false,
                'multiple' => false,
                'safe' => false,
                'fsync' => false,
                'timeout' => 60000, // 1 minute
            ))
        );

        $entity->getEventManager()->trigger('update.post', $entity);

        $resolver->getEntity()->clean($resolver->getProperty());

        return $this;
    }

    /**
     * Converts a property query to something the mapper can use to work with 
     * various levels of sub-properties and further descendents using the 
     * dot notation.
     *
     * @param   Contain\Entity\EntityInterface  Entity to persist
     * @param   string                          Query
     * @param   string                          Original query (for recursion debugging)
     * @return  Contain\Entity\Property\Resolver
     */
    public function resolve(EntityInterface $entity, $query)
    {
        $resolver = new Resolver($query);
        return $resolver->scan($entity);
    }

    /**
     * Appends one value to the end of a ListType, optionally if it doesn't 
     * exist only. In MongoDB this is an atomic operation.
     *
     * @param   Contain\Entity\EntityInterface  Entity to persist
     * @param   string                          Query to resolve which should point to a ListType
     * @param   mixed|array                     Value to append
     * @param   boolean                         Only add if it doesn't exist
     * @return  $this
     */
    public function push(EntityInterface $entity, $query, $value, $ifNotExists = false)
    {
        if (!$this->isPersisted($entity)) {
            throw new InvalidArgumentException('Cannot push to $entity as this is an update operation '
                . 'and $entity has not been persisted.'
            );
        }

        $resolver = $this->resolve($entity, $query);
        $resolver->assertType('Contain\Entity\Property\Type\ListType');

        if (count($value = $resolver->getType()->export($value)) != 1) {
            throw new InvalidArgumentException('Multiple values passed to ' . __METHOD__ . ' not allowed.');
        }

        $value  = $value[0];
        $method = $ifNotExists ? '$addToSet' : '$push';

        $this->getCollection()->update(
            array('_id' => $this->getId($entity)),
            array($method => array($query => $value)),
            $this->getOptions(array(
                'upsert' => false,
                'multiple' => false,
                'safe' => false,
                'fsync' => false,
                'timeout' => 60000, // 1 minute
            ))
        );

        return $this;
    }

    /**
     * Removes a value from a ListType. In MongoDB this is an atomic operation.
     *
     * @param   Contain\Entity\EntityInterface  Entity to persist
     * @param   string                          Query to resolve which should point to a ListType
     * @param   mixed|array                     Value to remove
     * @return  $this
     */
    public function pull(EntityInterface $entity, $query, $value)
    {
        if (!$this->isPersisted($entity)) {
            throw new InvalidArgumentException('Cannot remove from $entity as this is an update operation '
                . 'and $entity has not been persisted.'
            );
        }

        $resolver = $this->resolve($entity, $query);
        $resolver->assertType('Contain\Entity\Property\Type\ListType');

        if (count($value = $resolver->getType()->export($value)) != 1) {
            throw new InvalidArgumentException('Multiple values passed to ' . __METHOD__ . ' not allowed.');
        }

        $value = $value[0];

        $this->getCollection()->update(
            array('_id' => $this->getId($entity)),
            array('$pull' => array($query => $value)),
            $this->getOptions(array(
                'upsert' => false,
                'multiple' => false,
                'safe' => false,
                'fsync' => false,
                'timeout' => 60000, // 1 minute
            ))
        );

        return $this;
    }

    /**
     * Persists an entity in MongoDB.
     *
     * @param   EntityInterface                 Entity to persist
     * @return  $this
     */
    public function persist(EntityInterface $entity)
    {
        if ($id = $this->getId($entity)) {
            $this->update($entity);
        } else {
            $this->insert($entity);
        }

        $this->clear();

        // mark as properties as unmodified
        $entity->clean();

        return $this;
    }

    /**
     * Finds and hydrates a single entity from a search criteria.
     *
     * @param   array                   Search criteria
     * @return  Contain\Entity\EntityInterface|false
     */
    public function findOne(array $criteria = array())
    {
        $result = $this->getCollection()->findOne(
            $criteria,
            $this->getProperties()
        );

        if (!$result) {
            return false;
        }

        $result = $this->hydrateEntity($result);

        $this->clear();

        return $result;
    }

    /**
     * Finds a subset of entities by some condition and returns them in a 
     * array of hydrated entity objects.
     *
     * @param   array                   Search criteria
     * @return  EntityInterface[]
     */
    public function find(array $criteria = array())
    {
        // save for hydration
        $defaultOptions = array();

        $cursor = $this->getCollection()
            ->find(
                $criteria,
                $this->getProperties()
            );

        if ($this->sort !== null) {
            $cursor->sort($this->sort);
        }

        if ($this->limit !== null) {
            $cursor->limit($this->limit);
        }

        if ($this->skip !== null) {
            $cursor->skip($this->skip);
        }

        $result = array();

        foreach ($cursor as $data) {
            $this->options = $defaultOptions;
            $result[] = $this->hydrateEntity($data);
        }

        $this->clear();

        return $result;
    }

    /**
     * Deletes an entity.
     *
     * @param   Contain\Entity\EntityInterface
     * @return  $this
     */
    public function delete(EntityInterface $entity)
    {
        if (!$this->isPersisted($entity)) {
            throw new InvalidArgumentException('Cannot delete $entity '
                . 'as $entity has not been persisted.'
            );
        }

        $options = $this->getOptions(array(
            'justOne' => true,
            'safe'    => false,
            'fsync'   => false,
            'timeout' => 60000, // 1 minute
        ));

        $criteria = array(
            '_id' => $this->getId($entity),
        );

        $this->clear();

        $result = $this->getCollection()->remove($criteria, $options);

        return $this;
    }

    /**
     * Deletes a row by a condition.
     *
     * @param   array                   Search criteria
     * @return  $this
     */
    public function deleteBy(array $criteria)
    {
        $options = $this->getOptions(array(
            'justOne' => false,
            'safe'    => false,
            'fsync'   => false,
            'timeout' => 60000, // 1 minute
        ));

        $this->clear();

        $result = $this->getCollection()->remove($criteria, $options);

        return $this;
    }

    /**
     * Runs a raw update statement outside the scope of entities.
     *
     * @param   array               Criteria
     * @param   array               Fields to update
     * @return  $this
     */
    public function updateBy($criteria, $updates)
    {
        $this->getCollection()->update(
            $criteria,
            $updates,
            $this->getOptions(array(
                'upsert' => false,
                'multiple' => true,
                'safe' => false,
                'fsync' => false,
                'timeout' => 60000, // 1 minute
            ))
        );

        return $this;
    }

    /**
     * Rewrites the dirty() output from an entity into something
     * MongoDb can use in an update statement.
     *
     * @param   EntityInterface     Reference entity
     * @param   array               Dirty output
     * @return  array
     */
    protected function getUpdateCriteria(EntityInterface $entity)
    {
        $result = array();

        $dirty  = $entity->export($entity->dirty(), true);

        foreach ($dirty as $property => $value) {
            $type = $entity->type($property);

            // child entity
            if ($type instanceof EntityType) {
                $method = 'get' . ucfirst($property);
                $child  = $entity->$method();
                $sub    = $this->getUpdateCriteria($child);

                foreach ($sub as $subProperty => $subValue) {
                    $result[$property . '.' . $subProperty] = $subValue;
                }
            } else {
                $result[$property] = $value;
            }
        }

        return $result;
    }

    /**
     * Generates and sets a unique primary id for the entity, either
     * by using properties flagged as primary or simply generating a new 
     * MongoId object.
     *
     * @param   EntityInterface                 Entity to persist
     * @return  $this
     */
    protected function setId(EntityInterface $entity)
    {
        if ($properties = $entity->primary()) {
            $primaryKeys = array_keys($properties);
            $properties  = $entity->export($primaryKeys, true);

            foreach ($properties as $key => $value) {
                if (!is_scalar($value)) {
                    throw new RuntimeException('Primary id could not be generated '
                        . 'from \'' . $key . '\' property because the exported value '
                        .' is not a scalar.'
                    );
                }
            }

            if (count($properties) == 1) {
                $properties = array_values($properties);
                $primary = $properties[0];
            } else {
                $primary = implode('', array_values($properties));
            }
        } else {
            $primary = new MongoId();
            $primary = $primary->{'$id'};
        }

        if (!$primary) {
            throw new RuntimeException(
                'Primary id could not be established for $entity. Propert(y|ies) '
                . implode(', ', $primaryKeys) . ' are either empty or unset.'
            );
        }

        $entity->setExtendedProperty('_id', $primary);

        return $this;
    }

    /**
     * Inserts an entity into MongoDb and generates a unique id if 
     * one isn't set or can't be resolved.
     *
     * @param   EntityInterface                 Entity to persist
     * @return  $this
     */
    protected function insert(EntityInterface $entity)
    {
        $entity->getEventManager()->trigger('insert.pre', $entity);

        $this->setId($entity);
        $data        = $entity->export();
        $data['_id'] = $entity->getExtendedProperty('_id');

        $this->getCollection()->insert(
            $data,
            $this->getOptions(array(
                'safe'    => false,
                'fsync'   => false,
                'timeout' => 60000, // 1 minute
            ))
        );

        $entity->getEventManager()->trigger('insert.post', $entity);

        return $this;
    }

    /**
     * Updates a document already in MongoDb by running $sets for 
     * dirty properties.
     *
     * @param   EntityInterface                 Entity to persist
     * @return  $this
     */
    protected function update(EntityInterface $entity)
    {
        $entity->getEventManager()->trigger('update.pre', $entity);

        // if nothing is dirty, there's nothing to do
        if (!$update = $this->getUpdateCriteria($entity)) {
            return $this;
        }

        $this->getCollection()->update(
            array('_id' => $this->getId($entity)),
            array('$set' => $update),
            $this->getOptions(array(
                'upsert' => false,
                'multiple' => false,
                'safe' => false,
                'fsync' => false,
                'timeout' => 60000, // 1 minute
            ))
        );

        $entity->getEventManager()->trigger('update.post', $entity);

        return $this;
    }
}
