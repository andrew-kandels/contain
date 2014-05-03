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

use Contain\Entity\EntityInterface;
use Contain\Entity\Exception\InvalidArgumentException;
use Contain\Entity\Exception\RuntimeException;

/**
 * List of like-value items.
 *
 * @category    akandels
 * @package     contain
 * @copyright   Copyright (c) 2013 Andrew P. Kandels (http://andrewkandels.com)
 * @license     http://www.opensource.org/licenses/bsd-license.php BSD License
 */
class ListType extends StringType
{
    /**
     * {@inheritDoc}
     */
    public function clearOptions()
    {
        $this->options = array(
            'type' => '',
            'className' => '',
        );
        return $this;
    }

    /**
     * @return TypeInterface
     *
     * @throws InvalidArgumentException
     */
    public function getType()
    {
        if (!$type = $this->getOption('type')) {
            throw new RuntimeException('$value is invalid because no type has been set.');
        }

        if ($type == 'Contain\Entity\EntityInterface') {
            return $type;
        }

        if (is_object($type) && !$type instanceof TypeInterface) {
            throw new InvalidArgumentException("Object passed to type option must implement "
                . 'Contain\Entity\Property\Type\TypeInterface.'
            );
        }

        if (!is_object($type)) {
            if (strpos($type, '\\') === false) {
                $type = 'Contain\Entity\Property\Type\\' . ucfirst($type) . 'Type';
            }

            $type = new $type();

            if (!$type instanceof TypeInterface) {
                throw new InvalidArgumentException("Type option '$type' must implement "
                    . 'Contain\Entity\Property\Type\TypeInterface.'
                );
            }
        }

        if ($type instanceof EntityType) {
            if (!$className = $this->getOption('className')) {
                throw new InvalidArgumentException('$type of entity must specify a className '
                    . 'option that points to a class that implements '
                    . 'Contain\Entity\EntityInterface.'
                );
            }
            $type->setOption('className', $className);
        }

        if ($type instanceof ListType) {
            throw new InvalidArgumentException('$type may not be a nested instance of '
                . 'Contain\Entity\Property\Type\ListType.'
            );
        }

        return $type;
    }

    /**
     * {@inheritDoc}
     */
    public function parse($value)
    {
        if (!$value) {
            return $this->getUnsetValue();
        }

        $type = $this->getType();

        if ($type == 'Contain\Entity\EntityInterface') {
            if (!$value instanceof $type) {
                throw new InvalidArgumentException('$value must implement '
                    . 'Contain\Entity\EntityInterface.'
                );
            } else {
                return $value;
            }
        }

        if (!is_array($value)) {
            $value = array($value);
        }

        foreach ($value as $index => $innerValue) {
            $value[$index] = $type->parse($innerValue);
        }

        return $value;
    }

    /**
     * {@inheritDoc}
     */
    public function export($value)
    {
        $type = $this->getType();

        // @todo find a better way to deal with interfaces?
        if ($type == 'Contain\Entity\EntityInterface') {
            if (!$value instanceof $type) {
                throw new InvalidArgumentException('$value must implement '
                    . 'Contain\Entity\EntityInterface.'
                );
            } else {
                return $value->export();
            }
        }

        if (!$value = $this->parse($value)) {
            return $this->getUnsetValue();
        }

        foreach ($value as $index => $item) {
            if ($item instanceof EntityInterface) {
                $value[$index] = $item->export();
            } else {
                $value[$index] = $type->export($item);
            }
        }

        return $value;
    }

    /**
     * {@inheritDoc}
     */
    public function getUnsetValue()
    {
        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function getEmptyValue()
    {
        return array();
    }

    /**
     * {@inheritDoc}
     */
    public function getDirtyValue()
    {
        return array(
            $this->getType()->getDirtyValue(),
        );
    }
}
