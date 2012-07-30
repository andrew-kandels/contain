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
use Contain\Mapper\Selector;
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
    protected $options = array(
        'limit' => 50,
    );

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
     * Finds and hydrates a single entity from a search criteria.
     *
     * @param   Contain\Maper\Selector|array|Traversable
     * @param   array                   Search criteria
     * @return  Contain\Entity\EntityInterface|false
     */
    public function find($select, array $criteria)
    {
        $result = $this->getCollection()->findOne(
            $criteria,
            $this->getSelectorFields($select)
        );

        if (!$result) {
            return false;
        }

        return $this->hydrateEntity($result);
    }

    /**
     * Deletes a row by a condition.
     *
     * @param   array                   Search criteria
     * @return  $this
     */
    public function delete(array $criteria, array $options = array())
    {
        $options += $this->options;
        $result = $this->getCollection()->remove($criteria, $options);
        return $this;
    }

    /**
     * Finds a subset of entities by some condition and returns them in a 
     * array of hydrated entity objects.
     *
     * @param   array                   Fields
     * @param   array                   Search criteria
     * @param   array                   Options
     * @return  EntityInterface[]
     */
    public function findSome($select, array $criteria, array $options = array())
    {
        $options += $this->options;

        $cursor = $this->getCollection()->find(
            $criteria,
            $this->getSelectorFields($select)
        );

        $result = array();

        $index = 0;
        foreach ($cursor as $data) {
            if (++$index == $options['limit']) {
                break;
            }

            $result[] = $this->hydrateEntity($data);
        }

        return $result;
    }

    /**
     * Converts a selector specification into something Mongo 
     * understands.
     *
     * @param   array|Traversable|Contain\Mapper\Selector
     * @return  array
     */
    protected function getSelectorFields($select)
    {
        if (!$select instanceof Selector) {
            $select = new Selector($select);
        }

        $select = $select->getSelect();

        $fields = array();

        foreach ($select as $field) {
            $fields[$field] = true;
        }

        return $fields;
    }

    /**
     * Hydrates an array of data into an entity object.
     *
     * @param   array                   Data key/value pairs
     * @return  EntityInterface
     */
    protected function hydrateEntity(array $data)
    {
        $id = null;
        if (isset($data['_id'])) {
            $id = $data['_id'];
            unset($data['_id']);
        }

        $entityClass = $this->entityClass;
        $entity      = new $entityClass();

        foreach ($data as $key => $value) {
            try {
                $entity->fromArray(array($key => $value));
            } catch (Exception $e) {
                // ignore single property failures, schema may have just changed
            }
        }

        if ($id) {
            $entity->setExtendedProperty('_id', $id);
        }

        return $entity;
    }

    /**
     * Inserts or updates a entity into the adapter's data storage
     * through the adapter's connection.
     *
     * @param   EntityInterface                 Entity to persist
     * @return  $this
     */
    public function save(EntityInterface $entity)
    {
        // use primary for id if set
        try {
            $primary = $entity->getPrimaryValue();
        } catch (RuntimeException $e) {
            $primary = null;
        }

        // update
        if ($id = $entity->getExtendedProperty('_id')) {
            $entity->getEventManager()->trigger('update.pre', $entity);

            $newValue = array('$set' => array());
            $values   = $entity->export();
            foreach ($values as $name => $value) {
                $newValue['$set'][$name] = $value;
            }

            if ($newValue['$set']) {
                $this->getCollection()->update(
                    array('_id' => $primary),
                    $newValue,
                    $this->getOptions(array(
                        'upsert',
                        'multiple',
                        'safe',
                        'fsync',
                        'timeout',
                    ))
                );

                $entity->getEventManager()->trigger('update.post', $entity);

                return $this;
            }
        }

        // insert
        $entity->getEventManager()->trigger('insert.pre', $entity);

        $data        = $entity->export();

        if (!$primary) {
            $primary = new MongoId();
        }

        $data['_id'] = $primary;
        $entity->setExtendedProperty('_id', $primary);

        $this->getCollection()->insert(
            $data,
            $this->getOptions(array(
                'safe',
                'fsync',
                'timeout',
            ))
        );

        $entity->getEventManager()->trigger('insert.post', $entity);

        return $this;
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
     * Retrieves a driver option.
     *
     * @param   string              Option name
     * @return  mixed
     */
    public function getOption($name)
    {
        return isset($this->options[$name]) ? $this->options[$name] : null;
    }

    /**
     * Retrieves options (if set) by available keys.
     *
     * @param   array           Options
     * @return  array
     */
    protected function getOptions(array $options)
    {
        $result = array();
        foreach ($options as $name) {
            if (isset($this->options[$name])) {
                $result[$name] = $this->options[$name];
            }
        }

        return $result;
    }
}
