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

use Contain\Exception\InvalidArgumentException;
use DateTime;

/**
 * Date Data Type
 *
 * @category    akandels
 * @package     contain
 * @copyright   Copyright (c) 2013 Andrew P. Kandels (http://andrewkandels.com)
 * @license     http://www.opensource.org/licenses/bsd-license.php BSD License
 */
class DateType extends DateTimeType
{
    /**
     * Constructor
     *
     * @return  $this
     */
    public function __construct()
    {
        parent::__construct();
        $this->options['dateFormat'] = 'Y-m-d';
    }

    /**
     * A valid value that represents a dirty state (would never be equal to the actual 
     * value but also isn't empty or unset). 
     *
     * @return  mixed
     */
    public function getDirtyValue()
    {
        return sprintf('%04d-%02d-%02d',
            rand(1970, 1990),
            rand(1, 12),
            rand(1, 28)
        );
    }
}
