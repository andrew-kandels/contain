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
 * A single setting name/value pair.
 *
 * @category    akandels
 * @package     contain
 * @copyright   Copyright (c) 2013 Andrew P. Kandels (http://andrewkandels.com)
 * @license     http://www.opensource.org/licenses/bsd-license.php BSD License
 */
class Setting extends AbstractDefinition
{
    /**
     * Sets up the meta-data for the entity.
     *
     * @return  void
     */
    public function setUp()
    {
        $this->registerTarget(AbstractDefinition::FILTER, __DIR__ . '/../Filter')
             ->registerTarget(AbstractDefinition::FORM, __DIR__ . '/../Form')
             ->registerTarget(AbstractDefinition::ENTITY, __DIR__ . '/..');

        $this->setProperty('name', 'string', array(
            'required' => true,
            'options' => array(
                'label' => 'Name',
            ),
        ));

        $this->setProperty('value', 'mixed', array(
            'options' => array(
                'label' => 'Value',
            ),
        ));
    }
}
