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

use Contain\Entity\Exception;
use Traversable;

/**
 * A child entity reference.
 *
 * @category    akandels
 * @package     contain
 * @copyright   Copyright (c) 2013 Andrew P. Kandels (http://andrewkandels.com)
 * @license     http://www.opensource.org/licenses/bsd-license.php BSD License
 */
class EntityType extends StringType
{
    /**
     * Clears options.
     *
     * @return self
     */
    public function clearOptions()
    {
        $this->options = array(
            'className' => '',
        );
        return $this;
    }

    /**
     * Gets a new instance of the entity in a clean state.
     *
     * @param   array|Traversable                       Optional properties
     * @return  Contain\Entity\EntityInterface
     */
    public function getInstance($properties = null)
    {
        if (!$type = $this->getOption('className')) {
            throw new Exception\RuntimeException('$value is invalid because no type has been set for type entity');
        }

        // @todo find a better way to deal with interfaces and uncompiled entity references
        if ($type == 'Contain\Entity\EntityInterface') {
            return null;
        }

        if (!class_exists($type)) {
            throw new Exception\InvalidArgumentException('getInstance attempting to create non-existing '
                . 'object "' . $type . '"'
            );
        }

        return new $type($properties);
    }

    /**
     * Parse a given input into a suitable value for the current data type.
     *
     * @param   mixed               Value to be set
     * @return  Contain\Entity\     Internal value
     * @throws  COntain\Exception\InvalidArgumentException
     */
    public function parse($value)
    {
        $value = $value ?: array();

        if (!$type = $this->getOption('className')) {
            throw new Exception\RuntimeException('$value is invalid because no type has been set for type entity');
        }

        if ($value instanceof $type) {
            return $this->getInstance($value->export());
        }

        if ($value instanceof Traversable) {
            $value = iterator_to_array($value);
        }

        if (is_array($value)) {
            return $this->getInstance($value);
        }

        throw new Exception\InvalidArgumentException('$value is not of '
            . 'type Contain\Property\Type\EntityType, an array, or an instance of Traversable.'
        );
    }

    /**
     * Returns the internal value represented as a string value
     * for purposes of debugging or export.
     *
     * @param   mixed       Internal value
     * @return  mixed
     * @throws  Contain\Exception\InvalidArgumentException
     */
    public function export($value)
    {
        if (!$value || $value === $this->getUnsetValue()) {
            return $value;
        }

        if ($value === $this->getEmptyValue()) {
            return $value;
        }

        if (!$type = $this->getOption('className')) {
            throw new Exception\RuntimeException('$value is invalid because no type has been set for type entity');
        }

        if ($value instanceof $type) {
            return $value->export();
        }

        if ($value instanceof Traversable) {
            return iterator_to_array($value);
        }

        if (is_array($value)) {
            return $value;
        }

        throw new Exception\InvalidArgumentException('$value is not of '
            . 'type Contain\Property\Type\EntityType, an array, or an instance of Traversable.'
        );
    }

    /**
     * The value to compare the internal value to which translates to empty or null.
     *
     * @return  mixed
     */
    public function getEmptyValue()
    {
        return array();
    }

    /**
     * The value to compare the internal value to which translates to not being set.
     *
     * @return  mixed
     */
    public function getUnsetValue()
    {
        return null;
    }

    /**
     * A valid value that represents a dirty state (would never be equal to the actual
     * value but also isn't empty or unset).
     *
     * @return  mixed
     */
    public function getDirtyValue()
    {
        return array(
            '_rnd' => uniqid('', true),
        );
    }
}
