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

/**
 * Interface for a property's data type.
 *
 * @category    akandels
 * @package     contain
 * @copyright   Copyright (c) 2013 Andrew P. Kandels (http://andrewkandels.com)
 * @license     http://www.opensource.org/licenses/bsd-license.php BSD License
 */
interface TypeInterface
{
    /**
     * Parse a given input into a suitable value for the current data type.
     *
     * @param mixed $value Value to be set
     *
     * @return mixed Internal value
     *
     * @throws \Contain\Entity\Exception\InvalidArgumentException
     */
    public function parse($value);

    /**
     * Returns the internal value represented as a string value
     * for purposes of debugging or export.
     *
     * @param mixed $value Internal value
     *
     * @return string
     *
     * @throws \Contain\Entity\Exception\InvalidArgumentException
     */
    public function export($value);

    /**
     * The value to compare the internal value to which translates to empty or null.
     *
     * @return mixed
     */
    public function getEmptyValue();

    /**
     * The value to compare the internal value to which translates to not being
     * set during hydration.
     *
     * @return mixed
     */
    public function getUnsetValue();

    /**
     * A valid value that represents a dirty state (would never be equal to the actual 
     * value but also isn't empty or unset). 
     *
     * @return mixed
     */
    public function getDirtyValue();

    /**
     * Returns specific options for this type.
     *
     * @return array
     */
    public function getOptions();

    /**
     * Sets specific options for this type.
     *
     * @param array|\Traversable $options Option name/value pairs
     *
     * @return self
     */
    public function setOptions($options);

    /**
     * Validator configuration array to automatically include when building filters.
     *
     * @return array
     */
    public function getValidators();

    /**
     * Clears options
     *
     * @return self
     */
    public function clearOptions();
}
