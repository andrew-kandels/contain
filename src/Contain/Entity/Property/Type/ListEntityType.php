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
 * @copyright   Copyright (c) 2013 Andrew P. Kandels (http://andrewkandels.com)
 * @license     http://www.opensource.org/licenses/bsd-license.php BSD License
 * @link        http://andrewkandels.com/contain
 */

namespace Contain\Entity\Property\Type;

use Contain\Entity\Exception\InvalidArgumentException;
use Contain\Entity\Exception\RuntimeException;
use Contain\Entity\EntityInterface;
use ContainMapper\Cursor;
use ContainMapper\Mapper;
use Traversable;

/**
 * ContainMapper\Cursor list of entities which slow-hydrate for memory efficiency.
 * NOTE: The ListEntityType only starts being more CPU/memory performant when the count of
 *       sub-entities gets rather large (about 1,000 decent sized entities). Prior to that,
 *       it's generally faster and more efficent just to use the ListType with type = entity.
 *
 * @category    akandels
 * @package     contain
 * @copyright   Copyright (c) 2013 Andrew P. Kandels (http://andrewkandels.com)
 * @license     http://www.opensource.org/licenses/bsd-license.php BSD License
 */
class ListEntityType extends ListType
{
    /** 
     * Contain\Entity\Property\Type\EntityType
     */
    protected $entityType;

    /**
     * ContainMapper\Mapper
     */
    protected $mapper;

    /**
     * Clears options.
     *
     * @return  $this
     */
    public function clearOptions()
    {
        $this->options = array(
            'className' => '',
        );
        return $this;
    }

    /**
     * Resolves the list item type.
     *
     * @return  Entity\Property\Type\EntityType
     */
    public function getType()
    {
        if ($this->entityType) {
            return $this->entityType;
        }

        $type = new EntityType();

        if (!$className = $this->getOption('className')) {
            throw new InvalidArgumentException('$type of list entity must specify a className '
                . 'option that points to a class that implements '
                . 'Contain\Entity\EntityInterface.'
            );
        }
        $type->setOption('className', $className);

        $this->mapper = new Mapper($className);

        return ($this->entityType = $type);
    }

    /**
     * Parses an array of entities (or entity array values) into serialized
     * arrays to efficient store large numbers of objects.
     *
     * @param   mixed               Value to be set
     * @return  mixed               Internal value
     * @throws  Contain\Exception\InvalidArgumentException
     */
    public function parse($value)
    {
        $this->getType();

        if (!$value = $this->export($value)) {
            return $this->getEmptyValue();
        }

        return new Cursor($this->mapper, $value);
    }

    /**
     * Returns the internal value represented as a string value
     * for purposes of debugging or export.
     *
     * @param   mixed       Internal value
     * @return  string
     * @throws  Contain\Exception\InvalidArgumentException
     */
    public function export($value)
    {
        if (!$value) {
            return $this->getUnsetValue();
        }

        $type = $this->getType();

        if ($value instanceof Cursor) {
            $value = $value->export();
        }

        if (!is_array($value)) {
            throw new InvalidArgumentException('$value for ListEntityType should be an array or ContainMapper\Cursor');
        }

        foreach ($value as $index => $item) {
            if (!is_array($item)) {
                $value[$index] = $type->export($item);
            }
        }

        return $value;
    }
}
