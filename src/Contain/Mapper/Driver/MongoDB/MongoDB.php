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
use Exception;
use RuntimeException;
use MongoId;

/**
 * Contain's MongoDB Driver
 *
 * @category    akandels
 * @package     contain
 * @copyright   Copyright (c) 2012 Andrew P. Kandels (http://andrewkandels.com)
 * @license     http://www.opensource.org/licenses/bsd-license.php BSD License
 */
class MongoDB implements DriverInterface
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
     * @var array
     */
    protected $select = array();

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
     * @param   array                   Data key/value pairs
     * @return  EntityInterface
     */
    protected function hydrateEntity(array $data = array())
    {
        // default options
        $options = $this->getOptions(array(
            'ignoreErrors' => true,
            'autoExtend'   => false,
        ));

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
     * Sets a driver option. Which are available depends on the operation.
     *
     * @param   string              Option Name
     * @param   mixed               Option Value
     * @return  $this
     */
    public function setOption($name, $value)
    {
        $this->options[$name] = $value;
        return $this;
    }

    /**
     * Retrieves options (if set) by available keys.
     *
     * @param   array           Options
     * @return  array
     */
    protected function getOptions(array $defaults = array())
    {
        $result = array();
        foreach ($defaults as $name => $value) {
            $result[$name] = $value;
            if (isset($this->options[$name])) {
                $result[$name] = $this->options[$name];
            }
        }

        // reset for the next call
        $this->options = array();

        return $result;
    }

    /**
     * Selects which fields to query on the next call to a fetching
     * method (findOne, find, etc.).
     *
     * @param   Traversable|array|string                Field(s)
     * @return  $this
     */
    public function select($fields = array())
    {
        $this->select = array();

        if (is_array($fields) || $fields instanceof Traversable) {
            foreach ($fields as $field) {
                $this->select[] = $field;
            }
        } elseif (is_string($fields)) {
            $this->select[] = $fields;
        } else {
            throw new InvalidArgumentException('$fields should be an array, instance of '
                . 'Traversable or a single property name.'
            );
        }

        return $this;
    }

    /**
     * Returns a list of properties to retrieve when querying for
     * an entity.
     *
     * @return  array
     */
    protected function getSelect()
    {
        $select = $this->select;
        $this->select = array();
        return $select;
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
        if ($properties = $entity->getPrimary()) {
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

        // mark as properties as unmodified
        $entity->clean();

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
     * Rewrites the getDirty() output from an entity into something
     * MongoDb can use in an update statement.
     *
     * @param   EntityInterface     Reference entity
     * @param   array               Dirty output
     * @return  array
     */
    protected function getUpdateCriteria(EntityInterface $entity)
    {
        $result = array();

        $dirty  = $entity->export($entity->getDirty());

        foreach ($dirty as $property => $value) {
            // child entity
            if (is_array($value)) {
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
            $this->getSelect()
        );

        if (!$result) {
            return false;
        }

        return $this->hydrateEntity($result);
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

        $options = $this->getOptions(array(
            'limit' => 50,
        ));

        $cursor = $this->getCollection()->find(
            $criteria,
            $this->getSelect()
        );

        $result = array();

        $index = 0;
        foreach ($cursor as $data) {
            if (++$index >= $options['limit']) {
                break;
            }

            $this->options = $defaultOptions;
            $result[] = $this->hydrateEntity($data);
        }

        return $result;
    }

    /**
     * Deletes a row by a condition.
     *
     * @param   array                   Search criteria
     * @return  $this
     */
    public function delete(array $criteria)
    {
        $options = $this->getOptions(array(
            'justOne' => true,
            'safe'    => false,
            'fsync'   => false,
            'timeout' => 60000, // 1 minute
        ));
        $result = $this->getCollection()->remove($criteria, $options);
        return $this;
    }
}
