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
    public function getProperties($includeUnset = false);

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
     * Returns a unique identifier for this entity.
     *
     * @return  mixed
     */
    public function getPrimaryValue();

    /**
     * Returns a unique property for this entity.
     *
     * @return  mixed
     */
    public function getPrimaryName();
}
