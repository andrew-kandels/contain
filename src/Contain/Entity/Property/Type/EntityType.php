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
     * Gets a new instance of the entity in a clean state.
     *
     * @param array|\Traversable|null $properties Optional properties
     *
     * @return \Contain\Entity\EntityInterface
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
     * {@inheritDoc}
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
     * {@inheritDoc}
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
     * {@inheritDoc}
     */
    public function getEmptyValue()
    {
        return array();
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
    public function getDirtyValue()
    {
        return array(
            '_rnd' => uniqid('', true),
        );
    }
}
