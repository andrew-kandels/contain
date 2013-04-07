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
use MongoDate; // if available
use DateTime;

/**
 * DateTime Data Type
 *
 * @category    akandels
 * @package     contain
 * @copyright   Copyright (c) 2013 Andrew P. Kandels (http://andrewkandels.com)
 * @license     http://www.opensource.org/licenses/bsd-license.php BSD License
 */
class DateTimeType extends StringType
{
    /**
     * Clears options.
     *
     * @return  $this
     */
    public function clearOptions()
    {
        $this->options = array('dateFormat' => 'Y-m-d H:i:s');
        return $this;
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

        if ($value instanceof DateTime) {
            return clone $value;
        }

        if ($value instanceof MongoDate) {
            list($microseconds, $seconds) = explode(' ', (string) $value);
            $result = new DateTime();
            $result->setTimestamp($seconds);
            return $result;
        }

        if (is_string($value)) {
            return new DateTime($value);
        }

        if (is_integer($value)) {
            $obj = new DateTime();
            $obj->setTimestamp($value);
            return $obj;
        }

        throw new Exception\InvalidArgumentException('$value is invalid for date type');
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
        if (!$value) {
            return $this->getUnsetValue();
        }

        if ($value instanceof DateTime) {
            return $value->format($this->getOption('dateFormat'));
        }

        if ($value instanceof MongoDate) {
            return date($this->getOption('dateFormat'), $value->sec);
        }

        if (is_string($value)) {
            return date($this->getOption('dateFormat'), strtotime($value));
        }

        if (is_integer($value)) {
            return date($this->getOption('dateFormat'), $value);
        }

        throw new Exception\InvalidArgumentException('$value is invalid for date type');
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

    /**
     * A valid value that represents a dirty state (would never be equal to the actual 
     * value but also isn't empty or unset). 
     *
     * @return  mixed
     */
    public function getDirtyValue()
    {
        return sprintf('%04d-%02d-%02d %02d:%02d:%02d',
            rand(1970, 1990),
            rand(1, 12),
            rand(1, 28),
            rand(0, 23),
            rand(0, 59),
            rand(0, 59)
        );
    }
}
