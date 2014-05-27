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

namespace Contain;

use Iterator;
use Traversable;
use Closure;

/**
 * Cursor for slow hydration of iterable entity models.
 *
 * @category    akandels
 * @package     contain
 * @copyright   Copyright (c) 2012 Andrew P. Kandels (http://andrewkandels.com)
 * @license     http://www.opensource.org/licenses/bsd-license.php BSD License
 */
class Cursor implements Iterator
{
    /**
     * @var string
     */
    protected $baseClass;

    /**
     * Internal cursor to iterate
     *
     * @var array|Iterator|Traversable
     */
    protected $cursor;

    /**
     * @var integer
     */
    protected $position;

    /**
     * Re-usable entity model
     *
     * @var \Contain\Entity\EntityInterface
     */
    protected $entity;

    /**
     * @var Callable
     */
    protected $hydrator;

    /**
     * @var array
     */
    protected $options = array();

    /**
     * Constructor
     *
     * @param \Contain\Entity\EntityInterface|string $entity
     * @param array|\Iterator $cursor
     */
    public function __construct($entity, $cursor, $hydrator = null)
    {
        if (! ($cursor instanceof Iterator || $cursor instanceof Traversable || is_array($cursor))) {
            throw new Exception\InvalidArgumentException('Cursor expects $cursor argument to be iterable/traversable');
        }

        $this->hydrator = $hydrator;
        $this->cursor   = $cursor;

        if ($entity instanceof Entity\EntityInterface) {
            $this->entity    = $entity;
            $this->baseClass = get_class($entity);
            return;
        }

        if (is_string($entity) && is_subclass_of($entity, 'Contain\Entity\EntityInterface')) {
            $this->baseClass = $entity;
            return;
        }

        throw new Exception\InvalidArgumentException('Cursor expects $entity to be an instance of Contain\Entity\EntityInterface or '
            . 'a class name that implements it.'
        );
    }

    /**
     * Exports the cursor as a plain array, never even hydrating it if it can be avoided.
     *
     * @return array
     */
    public function export()
    {
        $return = array();

        foreach ($this->cursor as $item) {
            if (is_object($item)) {
                $item = $item->export();
            }

            $return[] = $item;
        }

        return $return;
    }

    /**
     * Exports the cursor as a hydrated array.
     *
     * @return \Contain\Entity\EntityInterface[]
     */
    public function toArray()
    {
        $return = array();

        // disable object re-use
        $this->entity = false;

        foreach ($this as $item) {
            $return[] = $item;
        }

        // re-enable it
        $this->entity = null;

        return $return;
    }

    /**
     * Rewind iterator
     *
     * @return  void
     */
    public function rewind()
    {
        if ($this->cursor instanceof Iterator) {
            $this->cursor->rewind();
        }

        $this->position = 0;
    }

    /**
     * Returns a count of items.
     *
     * @return  integer
     */
    public function count()
    {
        if (is_array($this->cursor)) {
            return count($this->cursor);
        }

        return iterator_count($this->cursor);
    }

    /**
     * Hydrates an entity into an object, re-using the previous object if it can.
     *
     * @param   mixed $data
     * @return  mixed
     */
    protected function hydrate($data)
    {
        if (!is_array($data)) {
            $data = iterator_to_array($data);
        }

        $entity = null;

        if ($this->entity) {
            $entity = $this->entity;
            $entity->reset()->fromArray($data);
        }

        if (!$entity) {
            $entity = new $this->baseClass($data);
        }

        $entity->clean()->trigger('hydrate.post');

        if (is_callable($this->hydrator)) {
            call_user_func($this->hydrator, $entity, $data);
        }

        if ($this->entity !== false) {
            $this->entity = $entity;
        }

        return $entity;
    }

    /**
     * Get current item
     *
     * @return  mixed
     */
    public function current()
    {
        if ($this->cursor instanceof Iterator) {
            $entity = $this->cursor->current();
        } elseif (!empty($this->options['reverse'])) {
            $entity = $this->cursor[$this->count() - $this->position - 1];
        } else {
            $entity = $this->cursor[$this->position];
        }

        return $this->hydrate($entity);
    }

    /**
     * Get current position
     *
     * @return  integer
     */
    public function key()
    {
        if ($this->cursor instanceof Iterator) {
            return $this->cursor->key();
        }

        return $this->position;
    }

    /**
     * Advances the iterator
     *
     * @return  void
     */
    public function next()
    {
        if ($this->cursor instanceof Iterator) {
            $this->cursor->next();

            return;
        }

        $this->position += 1;
    }

    /**
     * Checks if the current position is valid
     *
     * @return  boolean
     */
    public function valid()
    {
        if ($this->cursor instanceof Iterator) {
            return $this->cursor->valid();
        }

        return isset($this->cursor[$this->position]);
    }
}
