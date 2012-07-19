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

use Contain\Exception\InvalidArgumentException;
use Contain\Exception\RuntimeException;
use Traversable;

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
            'json' => true,
        );
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

        if (!$type = $this->getOption('type')) {
            throw new RuntimeException('$value is invalid because no type has been set for '
                . 'the ' . __CLASS__ . ' data type.'
            );
        }

        if (!$type instanceof TypeInterface) {
            if (is_string($type) && strpos('/', $type) === false) {
                $type = 'Contain\Entity\Property\Type\\' . ucfirst($type) . 'Type';
            }

            if (!is_string($type) || !is_subclass_of($type, 'Contain\Entity\Property\Type\TypeInterface')) {
                throw new InvalidArgumentException("Type '$type' is not valid. Should extend "
                    . 'Contain\Entity\Property\Type\TypeInterface or be a FQCN to a class that does.'
                );
            }

            $this->options['type'] = $type = new $type();
        }

        if ($this->getOption('json') && is_string($value)) {
            $value = json_decode($value);
        }

        if (is_array($value) || $value instanceof Traversable) {
            $return = array();
            foreach ($value as $key => $val) {
                $return[] = $type->parse($val);
            }
            $value = $return;
        } else {
            throw new InvalidArgumentException('$value is invalid for type ' . __CLASS__);
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
    public function parseString($value)
    {
        return $this->getOption('json') && $value
            ? json_encode($this->parse($value))
            : $this->getUnsetValue();
    }

    /**
     * The value to compare the internal value to which translates to empty or null.
     *
     * @return  mixed
     */
    public function getEmptyValue()
    {
        return false;
    }
}
