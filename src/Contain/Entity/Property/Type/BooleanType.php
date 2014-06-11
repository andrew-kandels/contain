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

use Zend\Filter\Boolean;

/**
 * String Data Type
 *
 * @category    akandels
 * @package     contain
 * @copyright   Copyright (c) 2013 Andrew P. Kandels (http://andrewkandels.com)
 * @license     http://www.opensource.org/licenses/bsd-license.php BSD License
 */
class BooleanType extends StringType
{
    /**
     * {@inheritDoc}
     */
    public function parse($value)
    {
        if (class_exists('Zend\Filter\Boolean')) {
            $filter = new Boolean(Boolean::TYPE_ALL);
            return $filter->filter($value) ? '1' : '0';
        }

        return !in_array(strtolower($value), array(
            'no', '0', 'false', '', 'nein', 'net', 'off', 'n', 'f',
        ));
    }

    /**
     * {@inheritDoc}
     */
    public function export($value)
    {
        return $this->parse($value);
    }
}
