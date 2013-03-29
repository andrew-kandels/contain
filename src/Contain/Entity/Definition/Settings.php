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

use Contain\Entity\Definition\AbstractDefinition;

/**
 * Template for a list of Setting key/value pairs with helper functions for
 * retrieving values quickly.
 *
 * @category    akandels
 * @package     contain
 * @copyright   Copyright (c) 2013 Andrew P. Kandels (http://andrewkandels.com)
 * @license     http://www.opensource.org/licenses/bsd-license.php BSD License
 */
class Settings extends AbstractDefinition
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
             ->registerTarget(AbstractDefinition::ENTITY, __DIR__ . '/..')
             ->registerMethod('getSetting')
             ->registerMethod('hasSetting')
             ->registerMethod('addSetting')
             ->registerMethod('removeSetting')
             ->registerMethod('setSetting');

        $this->setProperty('settings', 'list', array(
            'type' => 'entity',
            'className' => 'Contain\Entity\Setting',
        ));
    }

    /**
     * Gets a site setting value by name.
     *
     * @param   string                      Name
     * @return  mixed
     */
    public function getSetting($name)
    {
        if ($settings = $this->getSettings()) {
            foreach ($settings as $setting) {
                if ($setting->getName() == $name) {
                    return $setting->getValue();
                }
            }
        }

        return null;
    }

    /**
     * Sets a site setting.
     *
     * @param   string                      Name
     * @param   mixed                       Value
     * @return  $this
     */
    public function addSetting($name, $value)
    {
        return $this->setSetting($name, $value);
    }

    /**
     * Sets a site setting.
     *
     * @param   string                      Name
     * @param   mixed                       Value
     * @return  $this
     */
    public function setSetting($name, $value)
    {
        if ($settings = $this->getSettings()) {
            foreach ($settings as $setting) {
                if ($setting->getName() == $name) {
                    $setting->setValue($value);
                    return $this;
                }
            }
        } else {
            $settings = array();
        }

        $setting = new \Contain\Entity\Setting(array(
            'name' => $name,
            'value' => $value,
        ));

        $settings[] = $setting;
        $this->setSettings($settings);

        return $this;
    }

    /**
     * Verifies that a setting key exists.
     *
     * @param   string                      Name
     * @return  boolean
     */
    public function hasSetting($name)
    {
        if ($settings = $this->getSettings()) {
            foreach ($settings as $setting) {
                if ($setting->getName() == $name) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Removes a setting by key.
     *
     * @param   string                      Name
     * @return  $this
     */
    public function removeSetting($name)
    {
        if ($settings = $this->getSettings()) {
            foreach ($settings as $index => $setting) {
                if ($setting->getName() == $name) {
                    unset($settings[$index]);
                    break;
                }
            }
        }

        $this->setSettings(array_merge(array(), $settings));

        return $this;
    }
}
