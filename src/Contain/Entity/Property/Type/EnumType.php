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
     * @var array
     */
    protected $options = array();

    /**
     * Sets the available options for the list.
     *
     * @param   array               Options
     * @return  $this
     */
    public function setOptions(array $options)
    {
        $this->options = $options;
        return $this;
    }

    /**
     * Gets the available options for the list.
     *
     * @return  array
     */
    public function getOptions()
    {
        return $this->options;
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
        $value = parent::parse($value);

        if (in_array($value, $this->getOptions())) {
            return $value;
        }

        throw new InvalidArgumentException('$value is invalid for type ' . __CLASS__);
    }

    /**
     * Exports options to a JSON array for the compiler in order to reconstruct the 
     * type in compiled code.
     *
     * @return  string
     */
    public function serialize()
    {
        return json_encode($this->options);
    }

    /**
     * Exports options to a JSON array for the compiler in order to reconstruct the 
     * type in compiled code.
     *
     * @param   string
     * @return  $this
     */
    public function unserialize($input)
    {
        $this->options = json_decode($input);
        return $this;
    }
}
