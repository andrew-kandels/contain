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

namespace Contain\Entity\Property\Type;

use Contain\Entity\Exception\InvalidArgumentException;
use Contain\Entity\Exception\RuntimeException;
use Contain\Entity\EntityInterface;
use Contain\Entity\Property\Type\EntityType;

/**
 * List of like-value items.
 *
 * @category    akandels
 * @package     contain
 * @copyright   Copyright (c) 2012 Andrew P. Kandels (http://andrewkandels.com)
 * @license     http://www.opensource.org/licenses/bsd-license.php BSD License
 */
class ListType extends StringType
{
    /**
     * Constructor
     *
     * @return  $this
     */
    public function __construct()
    {
        $this->options = array(
            'type' => '',
            'className' => '',
        );
    }

    /**
     * Resolves the list item type.
     *
     * @return  Entity\Property\AbstractType
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
     * Parse a given input into a suitable value for the current data type.
     *
     * @param   mixed               Value to be set
     * @return  mixed               Internal value
     * @throws  COntain\Exception\InvalidArgumentException
     */
    public function parse($value)
    {
        if (!$value) {
            return $this->getUnsetValue();
        }

        $type = $this->getType();

        // @todo find a better way to deal with interfaces?
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

        if (defined('FUCK')) die(var_dump($value));
        foreach ($value as $index => $innerValue) {
            $value[$index] = $type->parse($innerValue);
        }

        return $value;
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
     * The value assigned when the property is unset.
     *
     * @return  array
     */
    public function getUnsetValue()
    {
        return array();
    }

    /**
     * The value to compare the internal value to which translates to empty or null.
     *
     * @return  array
     */
    public function getEmptyValue()
    {
        return array();
    }
}
