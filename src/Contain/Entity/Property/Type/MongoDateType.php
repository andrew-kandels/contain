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

use Contain\Exception;
use MongoDate;

/**
 * (Object) MongoDate Type
 *
 * @category    akandels
 * @package     contain
 * @copyright   Copyright (c) 2013 Andrew P. Kandels (http://andrewkandels.com)
 * @license     http://www.opensource.org/licenses/bsd-license.php BSD License
 */
class MongoDateType extends DateTimeType
{
    /**
     * Parse a given input into a suitable value for the current data type.
     *
     * @param   mixed               Value to be set
     * @return  integer             Internal value
     * @throws  Contain\Exception\InvalidArgumentException
     */
    public function parse($value = null)
    {
        if ($value = parent::export($value)) {
            return new MongoDate(strtotime($value));
        }

        return $value;
    }
}
