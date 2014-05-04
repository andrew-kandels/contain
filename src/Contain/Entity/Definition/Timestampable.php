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

use DateTime;

/**
 * Mixin for entities with createdAt/updatedAt timestamps.
 *
 * @category    akandels
 * @package     contain
 * @copyright   Copyright (c) 2013 Andrew P. Kandels (http://andrewkandels.com)
 * @license     http://www.opensource.org/licenses/bsd-license.php BSD License
 */
class Timestampable extends AbstractDefinition
{
    /**
     * Sets up the entity properties.
     *
     * @return self
     */
    public function setUp()
    {
        $this->setProperty('createdAt', 'dateTime')
             ->setOption('required', true);

        $this->setProperty('updatedAt', 'dateTime')
             ->setOption('required', true);
    }

    public function init()
    {
        $this->attach('insert.pre', function ($e) {
            $entity = $e->getTarget();
            $now    = new \DateTime('now');
            if (!$entity->getCreatedAt()) {
                $entity->setCreatedAt($now);
            }

            if (!$entity->getUpdatedAt()) {
               $entity->setUpdatedAt($now);
            }
        });

        $this->attach('update.pre', function ($e) {
            $entity = $e->getTarget();
            $now    = new \DateTime('now');
            if (!$entity->getUpdatedAt()) {
               $entity->setUpdatedAt($now);
            }
        });
    }
}
