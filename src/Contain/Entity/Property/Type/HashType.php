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
use Contain\Entity\Property\Type\EntityType;

/**
 * Hash table
 *
 * @category    akandels
 * @package     contain
 * @copyright   Copyright (c) 2013 Andrew P. Kandels (http://andrewkandels.com)
 * @license     http://www.opensource.org/licenses/bsd-license.php BSD License
 */
class HashType extends StringType
{
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

        if (!is_array($value)) {
            throw new InvalidArgumentException('$value must be an array');
        }

        foreach ($value as $key => $innerValue) {
            if (is_array($innerValue)) {
                foreach ($innerValue as $subKey => $subValue) {
                    if (!is_scalar($subValue)) {
                        throw new InvalidArgumentException('All keys in $value must be scalar '
                            . 'or simple arrays'
                        );
                    }
                }
                continue;
            }

            if (!is_scalar($innerValue)) {
                throw new InvalidArgumentException('All keys in $value must be scalar or simple arrays');
            }
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
        return $this->parse($value);
    }

    /**
     * The value assigned when the property is unset.
     *
     * @return  array
     */
    public function getUnsetValue()
    {
        return null;
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

    /**
     * A valid value that represents a dirty state (would never be equal to the actual 
     * value but also isn't empty or unset). 
     *
     * @return  mixed
     */
    public function getDirtyValue()
    {
        return array(
            'dirty' => uniqid(''),
        );
    }
}
