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
 * A child miscellaneous object reference.
 *
 * @category    akandels
 * @package     contain
 * @copyright   Copyright (c) 2012 Andrew P. Kandels (http://andrewkandels.com)
 * @license     http://www.opensource.org/licenses/bsd-license.php BSD License
 */
class ObjectType extends StringType
{
    /**
     * Constructor
     *
     * @return  $this
     */
    public function __construct()
    {
        $this->options = array(
            'className' => '',
        );
    }

    /**
     * Parse a given input into a suitable value for the current data type.
     *
     * @param   mixed               Value to be set
     * @return  object              Internal value
     * @throws  Contain\Exception\InvalidArgumentException
     */
    public function parse($value)
    {
        if (!$value) {
            return $this->getUnsetValue();
        }

        if (!$type = $this->getOption('className')) {
            throw new RuntimeException('$value is invalid because no type has been set for '
                . 'the ' . __CLASS__ . ' data type.'
            );
        }

        if (!$value instanceof $type) {
            throw new InvalidArgumentException('Class \'' . get_class($value) . '\' is not of '
                . 'type \'' . $type . '\'.'
            );
        }

        return $value;
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
        if ($entity = $this->parse($value)) {
            return serialize($value);
        }

        return $this->getUnsetValue();
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
