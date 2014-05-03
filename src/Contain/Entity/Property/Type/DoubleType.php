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

/**
 * Double / Floating Point / Precision Data Type
 *
 * @category    akandels
 * @package     contain
 * @copyright   Copyright (c) 2013 Andrew P. Kandels (http://andrewkandels.com)
 * @license     http://www.opensource.org/licenses/bsd-license.php BSD License
 */
class DoubleType extends StringType
{
    /**
     * {@inheritDoc}
     */
    public function parse($value = null)
    {
        if ($value === 0) {
            return 0;
        }

        if (!$value) {
            return $this->getUnsetValue();
        }

        if (is_numeric($value)) {
            return (double) $value;
        }

        return doubleval(parent::parse($value));
    }

    /**
     * {@inheritDoc}
     */
    public function export($value)
    {
        return $this->parse($value);
    }

    /**
     * {@inheritDoc}
     */
    public function getEmptyValue()
    {
        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function getValidators()
    {
        return array(
            array('name' => 'Float'),
        );
    }
}
