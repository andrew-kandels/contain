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

namespace Contain\Entity;

/**
 * Contain Entity's Interface
 *
 * @category    akandels
 * @package     contain
 * @copyright   Copyright (c) 2012 Andrew P. Kandels (http://andrewkandels.com)
 * @license     http://www.opensource.org/licenses/bsd-license.php BSD License
 */
interface EntityInterface
{
    /**
     * Gets an array of all the entity's properties.
     *
     * @param   boolean             Include unset properties
     * @return  array
     */
    public function properties($includeUnset = false);

    /**
     * Returns true if a property exists for the entity (property does 
     * not need to be set however).
     *
     * @param   string                      Property name
     * @return  boolean
     */
    public function propertyExists($property);

    /**
     * Returns an array of all the entity properties
     *
     * @param   boolean             Include unset properties
     * @return  array
     */
    public function toArray($includeUnset = false);

    /**
     * Hydrates entity properties from an array.
     *
     * @param   array|Traversable   Property key/value pairs
     * @return  $this
     */
    public function fromArray($properties);

    /**
     * Returns an array of all the entity properties
     * as an array of string-converted values (no objects).
     *
     * @param   boolean                 Include unset properties
     * @return  array
     */
    public function export($includeUnset = false);

    /**
     * Returns an array of the columns flagged as primary as the 
     * key(s) and the current values for the keys as the property
     * values.
     *
     * @return  mixed
     */
    public function primary();

    /**
     * Unsets one, some or all properties.
     *
     * @param   string|array|Traversable|null       Propert(y|ies)
     * @return  $this
     */
    public function clear($property = null);

    /**
     * Marks a changed property (or all properties by default) as clean, 
     * or unmodified.
     *
     * @param   string|array|Traversable|null       Propert(y|ies)
     * @return  $this
     */
    public function clean($property = null);

    /**
     * Returns dirty, modified properties with their previous undirty
     * value (or a recursive array for child entities).
     *
     * @return  array
     */
    public function dirty();

    /**
     * Marks a property as dirty.
     *
     * @param   string                      Property name
     * @return  $this
     */
    public function markDirty($property);

    /**
     * Returns true if dirty, modified properties exist.
     *
     * @return  boolean
     */
    public function isDirty();

    /**
     * Gets the property type for a given property.
     *
     * @param   string          Property name
     * @return  Network\Entity\Property\Type\TypeInterface
     */
    public function type($property);
}
