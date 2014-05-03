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

/**
 * Enumerated List Data Type
 *
 * @category    akandels
 * @package     contain
 * @copyright   Copyright (c) 2013 Andrew P. Kandels (http://andrewkandels.com)
 * @license     http://www.opensource.org/licenses/bsd-license.php BSD License
 */
class EnumType extends StringType
{
    /**
     * Clears options.
     *
     * @return self
     */
    public function clearOptions()
    {
        $this->options = array('options' => array());
        return $this;
    }

    /**
     * Parse a given input into a suitable value for the current data type.
     *
     * @param   mixed               Value to be set
     * @return  mixed               Internal value
     * @throws  Contain\Exception\InvalidArgumentException
     */
    public function parse($value)
    {
        if (!$value) {
            return $this->getUnsetValue();
        }

        $value = parent::parse($value);

        // @todo backwards compatibility fix for ZF2 2.0.2, to be removed to
        // simply use value_options like ZF2 does
        $options = $this->getOption('options') ?: array();
        if (!empty($options['value_options'])) {
            $options = $options['value_options'];
        }

        if (in_array($value, $options) || isset($options[$value])) {
            return $value;
        }

        return $this->getUnsetValue();
    }

    /**
     * Validator configuration array to automatically include when building filters.
     *
     * @return  array
     */
    public function getValidators()
    {
        // @todo backwards compatibility fix for ZF2 2.0.2, to be removed to
        // simply use value_options like ZF2 does
        $options = $this->getOption('options') ?: array();
        if (!empty($options['value_options']) && is_array($options['value_options'])) {
            $options = $options['value_options'];
        }

        // associative array / forms
        if (!isset($options[0])) {
            $options = array_keys($options);
        }

        return array(
            array('name' => 'InArray', 'options' => array('haystack' => $options)),
        );
    }
}
