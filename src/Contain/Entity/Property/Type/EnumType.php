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

/**
 * Enumerated List Data Type
 *
 * @category    akandels
 * @package     contain
 * @copyright   Copyright (c) 2012 Andrew P. Kandels (http://andrewkandels.com)
 * @license     http://www.opensource.org/licenses/bsd-license.php BSD License
 */
class EnumType extends StringType
{
    /**
     * Constructor
     *
     * @return  $this
     */
    public function __construct()
    {
        $this->options['options'] = array();
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

        $value = parent::parse($value);

        if (in_array($value, $this->getOption('options'))) {
            return $value;
        }

        throw new InvalidArgumentException('$value is invalid for type ' . __CLASS__);
    }
}
