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
 * List of Like Values
 *
 * @category    akandels
 * @package     contain
 * @copyright   Copyright (c) 2012 Andrew P. Kandels (http://andrewkandels.com)
 * @license     http://www.opensource.org/licenses/bsd-license.php BSD License
 */
class ListType implements TypeInterface
{
    /**
     * @var Contain\Entity\Property\Type\TypeInterface
     */
    protected $type;

    /**
     * Sets the type each list item should adhere to.
     *
     * @param   Contain\Entity\Property\Type\TypeInterface
     * @return  $this
     */
    public function setType($type)
    {
        if (!$type instanceof TypeInterface) {
            if (!is_string($type) || !is_subclass_of($type, 'Contain\Entity\Property\Type\TypeInterface')) {
                throw new InvalidArgumentException('$type is not a valid, should extend '
                    . 'Contain\Entity\Property\Type\TypeInterface or be a FQCN to a class that does.'
                );
            }

            $type = new $type();
        }

        $this->type = $type;

        return $this;
    }

    /**
     * Gets the type each list item should adhere to.
     *
     * @return  Contain\Entity\Property\Type\TypeInterface
     */
    public function getType()
    {
        return $this->type;
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
        if ($this->options === null) {
            throw new RuntimeException('$value is invalid because no type has been set for '
                . 'the ' . __CLASS__ . ' data type.'
            );
        }

        if ($value instanceof Traversable) {
            $return = array();
            foreach ($value as $key => $val) {
                $return[$key] = $val;
            }
            $value = $return;
        }
            
        if (!is_array($value)) {
            throw new InvalidArgumentException('$value is invalid for type ' . __CLASS__);
        }

        foreach ($value as $key => $val) {
            $value[$key] = $this->getType()->parse($val);
        }

        return $value;
    }

    /**
     * Returns the internal value represented as a scalar value (non-object/array)
     * for purposes of debugging or export.
     *
     * @param   mixed       Internal value
     * @return  null
     * @throws  Contain\Exception\InvalidArgumentException
     */
    public function parseScalar($value)
    {
        $value = $this->parse($value);

        foreach ($value as $key => $val) {
            $value[$key] = $this->getType()->parseScalar($val);
        }

        return json_encode($value);
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
     * The value to compare the internal value to which translates to not being
     * set during hydration.
     *
     * @return  mixed
     */
    public function getUnsetValue()
    {
        return null;
    }

    /**
     * Exports options to a JSON array for the compiler in order to reconstruct the 
     * type in compiled code.
     *
     * @return  string
     */
    public function serialize()
    {
        return $this->type;
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
        $this->type = $input;
        return $this;
    }
}
