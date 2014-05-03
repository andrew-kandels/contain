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
     * {@inheritDoc}
     */
    public function clearOptions()
    {
        $this->options = array(
            'className' => '',
        );
        return $this;
    }

    /**
     * {@inheritDoc}
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
     * {@inheritDoc}
     */
    public function parse($value)
    {
        if (!$value = $this->export($value)) {
            return $this->getEmptyValue();
        }

        $this->getType();

        return new Cursor($this->mapper, $value);
    }

    /**
     * {@inheritDoc}
     */
    public function export($value)
    {
        if ($value === $this->getUnsetValue()) {
            return $this->getUnsetValue();
        }

        if (!$value || $value === $this->getEmptyValue()) {
            return $this->getEmptyValue();
        }

        $type = $this->getType();

        if ($value instanceof Cursor) {
            return $value->export();
        }

        if ($value instanceof Traversable) {
            $value = iterator_to_array($value);
        }

        if (!is_array($value)) {
            throw new InvalidArgumentException('$value for ListEntityType should be an array or ContainMapper\Cursor');
        }

        foreach ($value as $index => $item) {
            if ($item instanceof EntityInterface) {
                $value[$index] = $type->export($item);
            }
        }

        return $value;
    }
}
