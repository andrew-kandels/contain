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

/**
 * Interface for a property's data type.
 *
 * @category    akandels
 * @package     contain
 * @copyright   Copyright (c) 2012 Andrew P. Kandels (http://andrewkandels.com)
 * @license     http://www.opensource.org/licenses/bsd-license.php BSD License
 */
interface TypeInterface
{
    /**
     * Parse a given input into a suitable value for the current data type.
     *
     * @param   mixed               Value to be set
     * @return  mixed               Internal value
     * @throws  Contain\Exception\InvalidArgumentException
     */
    public function parse($value);

    /**
     * Returns the internal value represented as a scalar value (non-object/array)
     * for purposes of debugging or export.
     *
     * @param   mixed       Internal value
     * @return  null
     * @throws  Contain\Exception\InvalidArgumentException
     */
    public function parseScalar($value);

    /**
     * The value to compare the internal value to which translates to empty or null.
     *
     * @return  mixed
     */
    public function getEmptyValue();

    /**
     * The value to compare the internal value to which translates to not being
     * set during hydration.
     *
     * @return  mixed
     */
    public function getUnsetValue();

    /**
     * Exports options to a JSON array for the compiler in order to reconstruct the 
     * type in compiled code.
     *
     * @return  string
     */
    public function serialize();

    /**
     * Exports options to a JSON array for the compiler in order to reconstruct the 
     * type in compiled code.
     *
     * @param   string
     * @return  $this
     */
    public function unserialize($input);
}
