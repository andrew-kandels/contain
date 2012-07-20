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

namespace Contain\Mapper;

use Traversable;

/**
 * Mapper selector which defines fields to hydrate.
 *
 * @category    akandels
 * @package     contain
 * @copyright   Copyright (c) 2012 Andrew P. Kandels (http://andrewkandels.com)
 * @license     http://www.opensource.org/licenses/bsd-license.php BSD License
 */
class Selector
{
    /**
     * @var array
     */
    protected $select = array();

    /**
     * @var boolean
     */
    protected $append = false;

    /**
     * Constructor
     *
     * Defines the columns to select from the entity mapper.
     *
     * @param   array|Selector          Parameters or a Selector object
     * @param   boolean                 Appends parameters to any defaults instead of replacing
     * @param   array                   Default select parameters
     * @return  $self
     */
    public function __construct($input = null, $append = true, array $defaults = array())
    {
        $this->append = $append;

        // Caller passed the same instance, clone it and use their append setting
        if ($input instanceof Selector) {
            $this->select = $input->getSelect();
            $append = $input->getAppend();

        // Caller passed in an array, use it and do not append
        } elseif (is_array($input) || $input instanceof Traversable) {
            foreach ($input as $key => $value) {
                $this->add($key, $value);
            }
            $append = false;

        // Did not pass in any values, use defaults, easy by setting append flag
        } else {
            $append = true;
        }

        if ($append) {
            foreach ($defaults as $key => $value) {
                $this->add($key, $value);
            }
        }
    }

    /**
     * Adds a select.
     *
     * @param   string          Select or Join Entity
     * @param   string          Select or Join Entity Value
     * @return  $self
     */
    public function add($key, $value = null)
    {
        // straight select
        if ($value === null) {
            $this->select[] = $key;

        // straight select, numeric key
        } elseif (preg_match('/^[0-9]+$/', $key)) {
            $this->select[] = $value;

        // join entity select
        } else {
            if (!is_array($value)) {
                $value = array($value);
            }

            if (!isset($this->select[$key])) {
                $this->select[$key] = array();
            }

            foreach ($value as $val) {
                $this->select[$key][] = $val;
            }
        }

        return $this;
    }

    /**
     * Append flag
     *
     * @return  boolean
     */
    public function getAppend()
    {
        return $this->append;
    }

    /**
     * Returns the selects.
     *
     * @return  array
     */
    public function getSelect()
    {
        return ($this->select ? $this->select : array());
    }
}
