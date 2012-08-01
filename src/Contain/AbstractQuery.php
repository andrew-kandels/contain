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

namespace Contain;

/**
 * Fetch/Query Unit of Work
 *
 * @category    akandels
 * @package     contain
 * @copyright   Copyright (c) 2012 Andrew P. Kandels (http://andrewkandels.com)
 * @license     http://www.opensource.org/licenses/bsd-license.php BSD License
 */
class AbstractQuery
{
    /**
     * @var integer
     */
    protected $limit;

    /**
     * @var integer
     */
    protected $defaultLimit;

    /**
     * @var integer
     */
    protected $skip;

    /**
     * @var array
     */
    protected $sort;

    /**
     * @var array
     */
    protected $options = array();

    /**
     * @var array
     */
    protected $properties = array();

    /**
     * Limits the results of any find/search operation from the mapper
     * to a maximum count.
     *
     * @param   integer             Number of Hydrated Entities
     * @return  $this
     */
    public function limit($num)
    {
        $this->limit = $num;
        return $this;
    }

    /**
     * Returns the maximum number of results to hydrate in a find/search
     * call.
     *
     * @return  integer             Number of hydrated entities (maximum)
     */
    public function getLimit()
    {
        return $this->limit !== null ? $this->limit : $this->defaultLimit;
    }

    /**
     * Skips a number of entities in any find/search operation.
     *
     * @param    integer             Number of entities to skip.
     * @return   $this
     */
    public function skip($num)
    {
        $this->skip = $num;
        return $this;
    }

    /**
     * Returns the number of results to skip when searching/finding.
     *
     * @return  integer             Number of entities to skip
     */
    public function getSkip()
    {
        return $this->skip;
    }

    /**
     * Configures how the results should be sorted in the next
     * find/search query.
     *
     * @param   array                Sort criteria
     * @return  $this
     */
    public function sort(array $criteria)
    {
        $this->sort = $criteria;
        return $this;
    }

    /**
     * Sets a mapper level option that will be passed to the next 
     * mapper method invokation.
     *
     * @param   string              Option Name
     * @param   mixed               Option Value
     * @return  $this
     */
    public function setOption($name, $value)
    {
        $this->options[$name] = $value;
        return $this;
    }

    /**
     * Sets mapper level options that will be passed to the next 
     * mapper method invokation.
     *
     * @param   string              Option Name
     * @param   mixed               Option Value
     * @return  $this
     */
    public function setOptions($options)
    {
        if (!is_array($options) && !$options instanceof Traversable) {
            throw new InvalidArgumentException('$options must be an array or an instance '
                . 'of Traversable.'
            );
        }

        foreach ($options as $name => $value) {
            $this->setOption($name, $value);
        }

        return $this;
    }

    /**
     * Pulls the mapper level options out of the stack in preparation
     * for a mapper method invokation and then clears the stack for the 
     * next.
     *
     * @param   array           Options
     * @return  array
     */
    protected function getOptions(array $defaults = array())
    {
        $result = array();
        foreach ($defaults as $name => $value) {
            $result[$name] = $value;
            if (isset($this->options[$name])) {
                $result[$name] = $this->options[$name];
            }
        }

        return $result;
    }

    /**
     * Resets internal query options and settings.
     *
     * @return  $this
     */
    public function clear()
    {
        $this->sort    = $this->limit = $this->skip = null;
        $this->options = $this->properties = array();

        return $this;
    }

    /**
     * Selects which properties to query and fill in the next mapper's
     * entity hydration.
     *
     * @param   Traversable|array|string                Properties to Select
     * @return  $this
     */
    public function properties($properties = array())
    {
        $this->properties = array();

        if (is_array($properties) || $properties instanceof Traversable) {
            foreach ($properties as $property) {
                $this->properties[] = $property;
            }
        } elseif (is_string($properties)) {
            $this->properties[] = $properties;
        } else {
            throw new InvalidArgumentException('$properties should be an array, instance of '
                . 'Traversable or a single property name.'
            );
        }

        return $this;
    }

    /**
     * Returns a list of properties to retrieve when the mapper
     * next hydrates an entity.
     *
     * @return  array
     */
    protected function getProperties()
    {
        return $this->properties;
    }
}
