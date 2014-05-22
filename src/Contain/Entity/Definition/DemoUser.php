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

namespace Contain\Entity\Definition;

/**
 * Demo definition for a basic User entity model.
 *
 * @category    akandels
 * @package     contain
 * @copyright   Copyright (c) 2013 Andrew P. Kandels (http://andrewkandels.com)
 * @license     http://www.opensource.org/licenses/bsd-license.php BSD License
 */
class DemoUser extends AbstractDefinition
{
    /**
     * Sets up the entity properties.
     *
     * @return self
     */
    public function setUp()
    {
        $this
            ->registerTarget(AbstractDefinition::ENTITY, __DIR__ . '/..')
            ->import('Contain\Entity\Definition\Timestampable')
            ->registerMethod('name')
            ->setProperty('firstName', 'string', array('required' => true))
            ->setProperty('lastName',  'string', array('required' => true))
        ;
    }

    /**
     * Returns a concatenated first and last name, or whatever is presently set.
     *
     * @return  string
     */
    public function name()
    {
        return implode(' ', array_filter(array($this->getFirstName(), $this->getLastName()), function ($a) {
            return $a;
        }));
    }
}
