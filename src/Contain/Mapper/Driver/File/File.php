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

namespace Contain\Mapper\Driver\File;

use Contain\Mapper\Driver\ConnectionInterface;
use Contain\Mapper\Driver\DriverInterface;
use Contain\Mapper\Exception\InvalidArgumentException;
use Contain\Mapper\Exception\RuntimeException;
use Contain\Entity\EntityInterface;
use Contain\Entity\Property\Type\EntityType;
use Contain\AbstractQuery;
use DirectoryIterator;
use Exception;
use Contain\Entity\Property\Resolver;

/**
 * File/Directory Driver
 *
 * @category    akandels
 * @package     contain
 * @copyright   Copyright (c) 2012 Andrew P. Kandels (http://andrewkandels.com)
 * @license     http://www.opensource.org/licenses/bsd-license.php BSD License
 */
class File extends AbstractQuery
{
    /**
     * @var Contain\Mapper\Driver\ConnectionInterface
     */
    protected $connection;

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
     * @return  $this
     */
    public function __construct($entityClass, ConnectionInterface $connection)
    {
        $this->entityClass    = $entityClass;
        $this->connection     = $connection;
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

        $entityClass  = $this->entityClass;
        $entity       = new $entityClass();
        $autoExtended = !empty($this->options['autoExtended']);
        $ignoreErrors = !isset($this->options['ignoreErrors']) ||
                        !empty($this->options['ignoreErrors']);

        $entity->fromArray($data, $ignoreErrors, $autoExtended);

        // remove any dirty flags as properties are all persisted at this point
        $entity->clean();

        return $entity;
    }

    /**
     * Persists an entity in MongoDB.
     *
     * @param   EntityInterface                 Entity to persist
     * @return  $this
     */
    public function persist(EntityInterface $entity)
    {
        $fileName = sprintf('%s/%s.ent',
            $this->connection->getConnection(),
            implode('-', array_values($entity->primary()))
        );

        if (!$handle = fopen($fileName, 'wt')) {
            throw new RuntimeException("Unable to open '$fileName' for writing.");
        }

        fputs($handle, json_encode($entity->export()));
        fclose($handle);

        $this->clear();

        // mark as properties as unmodified
        $entity->clean();

        return $this;
    }

    /**
     * Finds an entity by primary key.
     *
     * @param   string                  Primary key value
     * @return  Contain\Entity\EntityInterface|false
     */
    public function find($slug)
    {
        $fileName = sprintf('%s/%s.ent',
            $this->connection->getConnection(),
            $slug
        );

        if (!file_exists($fileName)) {
            return false;
        }

        $entity = $this->hydrateEntity(json_decode(file_get_contents($fileName), true));

        $this->clear();

        return $entity;
    }

    /**
     * Finds all entities.
     *
     * @return  Contain\Entity\EntityInterface[]
     */
    public function findAll()
    {
        $iterator = new DirectoryIterator($this->connection->getConnection());
        $results  = array();

        foreach ($iterator as $item) {
            if ($item->isFile() && preg_match('/\.ent$/', $item->getFilename())) {
                $entity = $this->hydrateEntity(json_decode(file_get_contents($item->getPathname()), true));

                if ($entity) {
                    $results[] = $entity;
                }
            }
        }

        return $results;
    }

    /**
     * Deletes an entity.
     *
     * @param   Contain\Entity\EntityInterface
     * @return  $this
     */
    public function delete(EntityInterface $entity)
    {
        $fileName = sprintf('%s/%s.ent',
            $this->connection->getConnection(),
            implode('-', array_values($entity->primary()))
        );

        if (file_exists($fileName)) {
            unlink($fileName);
        }

        return $this;
    }
}
