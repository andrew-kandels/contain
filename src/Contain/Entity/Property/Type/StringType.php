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

/**
 * String Data Type
 *
 * @category    akandels
 * @package     contain
 * @copyright   Copyright (c) 2013 Andrew P. Kandels (http://andrewkandels.com)
 * @license     http://www.opensource.org/licenses/bsd-license.php BSD License
 */
class StringType implements TypeInterface
{
    /**
     * @var array
     */
    protected $options = array();

    /**
     * Constructor
     *
     * @return self
     */
    public function __construct()
    {
        $this->clearOptions();
    }

    /**
     * {@inheritDoc}
     */
    public function parse($value)
    {
        if ($value === $this->getEmptyValue()) {
            return $value;
        }

        if (!$value) {
            return $this->getUnsetValue();
        }

        if (is_string($value)) {
            return $value;
        }

        if (is_numeric($value)) {
            return (string) $value;
        }

        if (is_object($value) && method_exists($value, '__toString')) {
            return (string) $value;
        }

        throw new Exception\InvalidArgumentException('$value is invalid for string type');
    }

    /**
     * {@inheritDoc}
     */
    public function export($value)
    {
        $value = $this->parse($value);

        if ($this->getUnsetValue() === $value) {
            return $this->getUnsetValue();
        }

        return (string) $value;
    }

    /**
     * {@inheritDoc}
     */
    public function getEmptyValue()
    {
        return '';
    }

    /**
     * {@inheritDoc}
     */
    public function getUnsetValue()
    {
        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function getDirtyValue()
    {
        return uniqid('', true);
    }

    /**
     * {@inheritDoc}
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Returns a single option for this type by name.
     *
     * @param string $name Option name
     * @return array
     */
    public function getOption($name)
    {
        return isset($this->options[$name]) ? $this->options[$name] : null;
    }

    /**
     * {@inheritDoc}
     */
    public function setOptions($options)
    {
        if (!is_array($options) && !$options instanceof \Traversable) {
            throw new Exception\InvalidArgumentException('$options argument must be an array or '
                . 'an instance of Traversable.'
            );
        }

        foreach ($options as $name => $value) {
            $this->setOption($name, $value);
        }

        return $this;
    }

    /**
     * Sets a specific option for this type.
     *
     * @param   string $name
     * @param   mixed  $value
     *
     * @return self
     */
    public function setOption($name, $value)
    {
        if (isset($this->options[$name])) {
            $this->options[$name] = $value;
        }

        return $this;
    }

    /**
     * Unsets an option.
     *
     * @param string $name
     *
     * @return self
     */
    public function removeOption($name)
    {
        if (isset($this->options[$name])) {
            unset($this->options[$name]);
        }

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function clearOptions()
    {
        $this->options = array();
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getValidators()
    {
        return array();
    }
}
